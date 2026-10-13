import api from './api'
import { clearSession } from './sessionTimeout'

export const login = async (email: string, password: string) => {
  const response = await api.post('/login', {
    email,
    password,
  })

  return response.data
}

export const logout = async () => {
  try {
    return (await api.post('/logout')).data
  } finally {
    clearSession()
  }
}
export const requestPasswordCode = async (email: string) => {
  return (await api.post('/forgot-password', { email })).data
}

export const verifyPasswordCode = async (email: string, code: string) => {
  return (await api.post('/forgot-password/verify', { email, code })).data
}

export const resetForgottenPassword = async (email: string, reset_token: string, password: string, password_confirmation: string) => {
  return (await api.post('/reset-password', { email, reset_token, password, password_confirmation })).data
}
