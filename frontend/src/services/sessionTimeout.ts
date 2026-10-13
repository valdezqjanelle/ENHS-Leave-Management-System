import axios, { type AxiosInstance } from 'axios'
import type { Router } from 'vue-router'

export const IDLE_TIMEOUT_MS = 10 * 60 * 1000
const ACTIVITY_KEY = 'els-last-activity'
const REASON_KEY = 'els-session-expired'
const API_BASE = 'https://enhs-leave-management-system.onrender.com/api'
const clients = new WeakSet<AxiosInstance>()
let activeRouter: Router | undefined
let lastHeartbeat = 0
let heartbeatBusy = false
let pendingHeartbeat: ReturnType<typeof setTimeout> | undefined

export function clearSession(): void {
  if (pendingHeartbeat) clearTimeout(pendingHeartbeat)
  pendingHeartbeat = undefined
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  localStorage.removeItem(ACTIVITY_KEY)
  localStorage.removeItem(REASON_KEY)
  sessionStorage.removeItem('els-enhancement-notice-dismissed')
}

export function startSession(token: string, user: unknown): void {
  // Store the activity before the token so other open tabs see a valid deadline.
  clearSession()
  localStorage.setItem(ACTIVITY_KEY, String(Date.now()))
  localStorage.setItem('user', JSON.stringify(user))
  localStorage.setItem('token', token)
  lastHeartbeat = Date.now()
}

export function hasExpiredNotice(): boolean {
  return Boolean(localStorage.getItem(REASON_KEY))
}
export function expiredSessionMessage(): string {
  return localStorage.getItem(REASON_KEY) === 'invalid'
    ? 'Your session is no longer valid. Please sign in again to continue.'
    : 'You have been signed out after 10 minutes of inactivity. Please sign in again to continue.'
}
export function dismissExpiredNotice(): void {
  localStorage.removeItem(REASON_KEY)
}

export function expireSession(expectedToken = localStorage.getItem('token'), reason = '1'): void {
  // A late response from the old session cannot log out a newly signed-in user.
  if (!expectedToken || localStorage.getItem('token') !== expectedToken) return
  clearSession()
  localStorage.setItem(REASON_KEY, reason)
  window.dispatchEvent(new Event('els-session-expired'))
  if (activeRouter) void activeRouter.replace('/login')
  // Best effort revocation; the backend independently enforces the deadline.
  void fetch(`${API_BASE}/logout`, {
    method: 'POST', headers: { Authorization: `Bearer ${expectedToken}`, Accept: 'application/json' },
    keepalive: true,
  }).catch(() => undefined)
}

export function checkSession(): boolean {
  const token = localStorage.getItem('token')
  if (!token) return false
  const value = localStorage.getItem(ACTIVITY_KEY)
  const last = Number(value)
  const now = Date.now()
  if (!value || !Number.isFinite(last) || last <= 0 || last > now || now - last >= IDLE_TIMEOUT_MS) {
    expireSession(token)
    return false
  }
  return true
}

export function installSessionInterceptors(client: AxiosInstance): void {
  if (clients.has(client)) return
  clients.add(client)
  client.interceptors.request.use(config => {
    const header = String(config.headers.Authorization || '')
    if (header.startsWith('Bearer ') && header.slice(7) === localStorage.getItem('token') && !checkSession()) {
      throw new axios.CanceledError('Session expired')
    }
    return config
  })
  client.interceptors.response.use(response => response, error => {
    const header = String(error.config?.headers?.Authorization || '')
    if (error.response?.status === 401 && header.startsWith('Bearer ')) {
      expireSession(header.slice(7), error.response?.data?.code === 'SESSION_EXPIRED' ? '1' : 'invalid')
    }
    return Promise.reject(error)
  })
}

function recordActivity(event: Event): void {
  if (!event.isTrusted || document.visibilityState !== 'visible' || !document.hasFocus()) return
  // Check BEFORE touching the timestamp: returning to an expired tab cannot revive it.
  if (!checkSession()) return
  const now = Date.now()
  const previous = Number(localStorage.getItem(ACTIVITY_KEY))
  if (now - previous >= 1000) localStorage.setItem(ACTIVITY_KEY, String(now))
  scheduleHeartbeat()
}

function scheduleHeartbeat(): void {
  if (pendingHeartbeat) return
  pendingHeartbeat = setTimeout(() => {
    pendingHeartbeat = undefined
    sendHeartbeat()
  }, Math.max(0, 15000 - (Date.now() - lastHeartbeat)))
}

function sendHeartbeat(): void {
  if (!checkSession()) return
  if (heartbeatBusy) { scheduleHeartbeat(); return }
  const token = localStorage.getItem('token')
  if (!token) return
  heartbeatBusy = true
  lastHeartbeat = Date.now()
  // A trailing heartbeat reports elapsed time since the actual interaction.
  // Its timer must not itself count as user activity.
  const idleMs = Math.max(0, Date.now() - Number(localStorage.getItem(ACTIVITY_KEY)))
  void fetch(`${API_BASE}/session/activity`, {
    method: 'POST', headers: { Authorization: `Bearer ${token}`, Accept: 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify({ idle_ms: idleMs }),
  }).then(async response => {
    if (response.status === 401) {
      const data = await response.json().catch(() => ({}))
      expireSession(token, data.code === 'SESSION_EXPIRED' ? '1' : 'invalid')
    }
    else if (!response.ok) lastHeartbeat = 0
  }).catch(() => { lastHeartbeat = 0 }).finally(() => { heartbeatBusy = false })
}

export function setupSessionTimeout(router: Router): () => void {
  activeRouter = router
  installSessionInterceptors(axios)
  const check = () => {
    checkSession()
    if (!localStorage.getItem('token') && router.currentRoute.value.matched.some(r => r.meta.requiresAuth)) {
      void router.replace('/login')
    }
  }
  const storage = (event: StorageEvent) => {
    if (event.key === 'token' || event.key === ACTIVITY_KEY || event.key === REASON_KEY || event.key === null) {
      if (hasExpiredNotice()) window.dispatchEvent(new Event('els-session-expired'))
      check()
    }
  }
  const events = ['pointerdown', 'pointermove', 'keydown', 'wheel', 'touchstart', 'touchmove', 'scroll']
  events.forEach(name => window.addEventListener(name, recordActivity, { passive: true, capture: true }))
  window.addEventListener('focus', check)
  window.addEventListener('pageshow', check)
  document.addEventListener('visibilitychange', check)
  window.addEventListener('storage', storage)
  const interval = window.setInterval(check, 1000)
  check()
  return () => {
    clearInterval(interval)
    if (pendingHeartbeat) clearTimeout(pendingHeartbeat)
    pendingHeartbeat = undefined
    events.forEach(name => window.removeEventListener(name, recordActivity, true))
    window.removeEventListener('focus', check)
    window.removeEventListener('pageshow', check)
    document.removeEventListener('visibilitychange', check)
    window.removeEventListener('storage', storage)
    activeRouter = undefined
  }
}
