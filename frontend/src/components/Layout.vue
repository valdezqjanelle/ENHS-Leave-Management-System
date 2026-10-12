<template>
  <div class="app-shell">
    <!-- Mobile Menu Toggle (hamburger only — hidden once sidebar is open) -->
    <button
      v-if="!sidebarOpen"
      @click="sidebarOpen = true"
      class="mobile-toggle lg:hidden fixed top-4 left-4 z-50 p-2 rounded-xl"
    >
      <Menu class="w-5 h-5 text-white" />
    </button>

    <!-- Overlay -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30"
    ></div>

    <!-- Sidebar -->
    <aside
      class="sidebar fixed lg:relative h-full w-64 z-40 transition-transform duration-300 flex flex-col"
      :class="
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      "
    >
      <div class="p-5 flex items-center justify-between border-b border-white/10">
        <div class="brand-block flex items-center gap-3">
          <img
            src="@/assets/logo.png"
            alt="ENHS Logo"
            class="brand-logo"
          />
          <div>
            <h1 class="text-lg font-bold text-white tracking-tight">ENHS</h1>
            <p class="text-xs text-white/70 font-medium">Leave Management</p>
          </div>
        </div>

        <!-- Close button lives inside the sidebar's own header, right-aligned -->
        <button
          v-if="sidebarOpen"
          @click="sidebarOpen = false"
          class="sidebar-close-btn lg:hidden p-2"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <nav
        class="sidebar-scroll mt-4 space-y-1 px-3 flex-1"
        @click="sidebarOpen = false"
      >
        <router-link to="/dashboard" class="nav-item">
          <LayoutDashboard class="icon" />
          Dashboard
        </router-link>

        <div v-if="currentUser.role === 'admin'">
          <button
            @click.stop="togglePersonnelMenu"
            class="nav-item w-full justify-between"
            :class="{ 'router-link-active': isPersonnelRouteActive }"
          >
            <span class="flex items-center gap-3">
              <Users class="icon" />
              Employee Management
            </span>

            <ChevronDown
              class="icon transition-transform duration-200"
              :class="{ 'rotate-180': personnelMenuOpen }"
            />
          </button>

          <!-- SUB-MENU -->
          <div
            v-show="personnelMenuOpen || isPersonnelRouteActive"
            class="pl-4 space-y-1 mt-1"
          >
            <router-link to="/employees" class="nav-subitem">
              Employees
            </router-link>

            <router-link to="/teaching-personnel" class="nav-subitem">
              Teaching Personnel
            </router-link>

            <router-link to="/teaching-setup" class="nav-subitem">
              Teaching Setup
            </router-link>

            <router-link to="/non-teaching-personnel" class="nav-subitem">
              Non-Teaching Personnel
            </router-link>
          </div>
        </div>

        <!-- Leave Application Dropdown -->
        <div v-if="currentUser.role === 'admin'">
          <button
            type="button"
            @click.stop="toggleLeaveApplicationMenu"
            class="nav-item w-full justify-between"
            :class="{ 'router-link-active': isLeaveApplicationRouteActive }"
            :aria-expanded="leaveApplicationMenuOpen"
            aria-controls="leave-application-menu"
          >
            <span class="flex items-center gap-3">
              <FilePlus class="icon" />
              Leave Application
            </span>

            <ChevronDown
              class="icon transition-transform duration-200"
              :class="{ 'rotate-180': leaveApplicationMenuOpen }"
            />
          </button>

          <div
            id="leave-application-menu"
            v-show="leaveApplicationMenuOpen"
            class="pl-4 space-y-1 mt-1"
          >
            <router-link to="/leave-application" class="nav-subitem">
              Apply Leave
            </router-link>

            <router-link to="/my-applications" class="nav-subitem">
              My Applications
            </router-link>
          </div>
        </div>

        <!-- Leave Management Dropdown -->
        <div v-if="currentUser.role === 'admin'">
          <button
            type="button"
            @click.stop="toggleLeaveManagementMenu"
            class="nav-item w-full justify-between"
            :class="{ 'router-link-active': isLeaveManagementRouteActive }"
            :aria-expanded="leaveManagementMenuOpen"
            aria-controls="leave-management-menu"
          >
            <span class="flex items-center gap-3">
              <FileCheck class="icon" />
              Leave Management
            </span>

            <ChevronDown
              class="icon transition-transform duration-200"
              :class="{ 'rotate-180': leaveManagementMenuOpen }"
            />
          </button>

          <div
            id="leave-management-menu"
            v-show="leaveManagementMenuOpen"
            class="pl-4 space-y-1 mt-1"
          >
            <router-link to="/admin-applications" class="nav-subitem">
              Leave Applications
            </router-link>

            <router-link to="/leave-credits" class="nav-subitem">
              Leave Credits
            </router-link>

            <router-link to="/leave-balances" class="nav-subitem">
              Leave Balances
            </router-link>
          </div>
        </div>

        <router-link
          v-if="currentUser.role === 'employee'"
          to="/leave-application"
          class="nav-item"
        >
          <FilePlus class="icon" />
          Apply
        </router-link>

        <router-link
          v-if="currentUser.role === 'employee'"
          to="/my-applications"
          class="nav-item"
        >
          <FileCheck class="icon" />
          My Applications
        </router-link>

        

        <router-link
          v-if="currentUser.role === 'admin'"
          to="/reports"
          class="nav-item"
        >
          <BarChart class="icon" />
          Reports
        </router-link>

        <router-link
          v-if="currentUser.role === 'employee'"
          to="/settings"
          class="nav-item"
        >
          <Settings class="icon" />
          Settings
        </router-link>

        <router-link
          v-if="currentUser.role === 'admin'"
          to="/admin-settings"
          class="nav-item"
        >
          <Settings class="icon" />
          Settings
        </router-link>
      </nav>

      <!--
        Profile + Logout pinned to the bottom of the sidebar.
        @click="sidebarOpen = false" lives here too, mirroring the
        <nav> handler above — this block lives outside <nav>, so
        without its own handler, clicking Profile (or Logout) would
        leave the sidebar open on mobile.
      -->
      <div
        class="px-3 pb-6 pt-2 border-t border-white/5 space-y-1"
        @click="sidebarOpen = false"
      >
        <router-link
          v-if="currentUser.role === 'employee'"
          to="/profile"
          class="nav-item profile-item"
        >
          <span class="profile-avatar">
            <UserCircle2 class="icon" />
          </span>
          Profile
        </router-link>

        <button @click="logout" class="nav-item logout-item w-full">
          <LogOut class="icon" />
          Logout
        </button>
      </div>
    </aside>

    <!-- MAIN -->
    <div class="main-shell flex flex-col flex-1 w-full min-w-0 overflow-hidden">
      <!-- HEADER -->
      <header class="topbar flex items-center justify-between px-6 py-4 pl-16 lg:pl-6">
        <div>
          <h2 class="font-semibold text-lg tracking-tight" style="color: var(--text);">
            {{ pageTitle }}
          </h2>
          <p v-if="currentUser.role === 'admin'" class="text-sm" style="color: var(--text-muted);">
            Administrative Portal
          </p>
          <p v-else class="text-sm" style="color: var(--text-muted);">
            Faculty Portal
          </p>
        </div>

        <div class="flex items-center gap-3">
          <!-- Dark Mode Toggle -->
          <button
            @click="toggleDarkMode"
            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
          >
            <Sun v-if="isDarkMode" class="w-5 h-5" style="color: var(--text);" />
            <Moon v-else class="w-5 h-5" style="color: var(--text-muted);" />
          </button>

          <div class="user-summary" aria-label="Current user">
            <span class="user-avatar">
              <img v-if="headerPhoto && !headerPhotoFailed" :src="headerPhoto" alt="" class="header-photo" @error="headerPhotoFailed = true" />
              <span v-else>{{ currentUser.name.charAt(0).toUpperCase() }}</span>
            </span>
            <span class="hidden sm:block">
              <strong>{{ currentUser.name }}</strong>
              <small>{{ currentUser.role }}</small>
            </span>
          </div>
        </div>
      </header>

      <!-- Shared notice for Admin and Employee pages -->
      <section
        v-if="showEnhancementNotice"
        class="enhancement-notice"
        aria-labelledby="enhancement-notice-title"
      >
        <div class="enhancement-notice-copy">
          <h3 id="enhancement-notice-title">System Enhancement Notice</h3>
          <p>
            We are continuously improving the system through interface refinements
            and additional features. You may continue using the available features
            while these enhancements are in progress.
          </p>
        </div>
        <button
          type="button"
          class="enhancement-notice-close"
          aria-label="Dismiss system enhancement notice"
          @click="dismissEnhancementNotice"
        >
          <X class="w-5 h-5" aria-hidden="true" />
        </button>
      </section>

      <!-- CONTENT -->
      <main class="content-panel flex-1 overflow-auto p-6 w-full">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { logout as logoutService } from "@/services/auth";
import api from "@/services/api";

import {
  LayoutDashboard,
  FilePlus,
  FileCheck,
  Calendar,
  Users,
  BarChart,
  Settings,
  Search,
  Bell,
  LogOut,
  Menu,
  X,
  Scale,
  UserCircle2,
  ChevronDown,
  GraduationCap,
  Sun,
  Moon,
} from "lucide-vue-next";

const route = useRoute();
const router = useRouter();

// Dismissal persists across page navigation and refresh in this browser tab.
// Logout clears it so the notice appears again on the next login.
const enhancementNoticeKey = "els-enhancement-notice-dismissed";
const noticeDismissed = ref(false);
try {
  noticeDismissed.value = sessionStorage.getItem(enhancementNoticeKey) === "true";
} catch {
  // In-memory dismissal still works when browser storage is unavailable.
}

const showEnhancementNotice = computed(() => {
  return Boolean(localStorage.getItem("token")) && !noticeDismissed.value;
});

const dismissEnhancementNotice = () => {
  noticeDismissed.value = true;
  try {
    sessionStorage.setItem(enhancementNoticeKey, "true");
  } catch {
    // Keep the notice dismissed for the current mounted layout.
  }
};

const sidebarOpen = ref(false);
const personnelMenuOpen = ref(false);
const leaveApplicationMenuOpen = ref(false);
const leaveManagementMenuOpen = ref(false);

// Dark mode
const isDarkMode = ref(localStorage.getItem('darkMode') === 'true');

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value;
  localStorage.setItem('darkMode', isDarkMode.value.toString());
  document.documentElement.classList.toggle('dark', isDarkMode.value);
};

// Initialize dark mode on mount
onMounted(() => {
  if (isDarkMode.value) {
    document.documentElement.classList.add('dark');
  }
});

const togglePersonnelMenu = () => {
  personnelMenuOpen.value = !personnelMenuOpen.value;
};

const toggleLeaveApplicationMenu = () => {
  leaveApplicationMenuOpen.value = !leaveApplicationMenuOpen.value;
};

const toggleLeaveManagementMenu = () => {
  leaveManagementMenuOpen.value = !leaveManagementMenuOpen.value;
};

const isPersonnelRouteActive = computed(() => {
  return [
    "/employees",
    "/teaching-personnel",
    "/teaching-setup",
    "/non-teaching-personnel",
  ].includes(route.path);
});

const isLeaveApplicationRouteActive = computed(() => {
  return [
    "/leave-application",
    "/my-applications",
  ].includes(route.path);
});

const isLeaveManagementRouteActive = computed(() => {
  return [
    "/admin-applications",
    "/leave-credits",
    "/leave-balances",
  ].includes(route.path);
});

watch(
  () => route.path,
  () => {
    if (isLeaveApplicationRouteActive.value) {
      leaveApplicationMenuOpen.value = true;
    }
    if (isLeaveManagementRouteActive.value) {
      leaveManagementMenuOpen.value = true;
    }
  },
  { immediate: true },
);
// Header uses profile names, never the email username.
interface HeaderUser {
  name: string;
  email: string;
  role: string;
}
const profileName = (value: any): string => {
  const first = typeof value?.first_name === "string" ? value.first_name.trim() : "";
  const last = typeof value?.last_name === "string" ? value.last_name.trim() : "";
  return [first, last].filter(Boolean).join(" ");
};
let storedUser: any = {};
try {
  storedUser = JSON.parse(localStorage.getItem("user") || "{}");
} catch {
  // A malformed cached value should not crash the layout.
}
const currentUser = ref<HeaderUser>({
  name: profileName(storedUser) || "User",
  email: storedUser?.email || "",
  role: storedUser?.role || "",
});

const headerPhoto = ref('');
const headerPhotoFailed = ref(false);

const refreshHeaderName = async () => {
  const token = localStorage.getItem("token");
  if (!token) return;
  try {
    // Reuse existing profile endpoints to update already logged-in users too.
    const endpoint = currentUser.value.role === "admin" ? "/admin/profile" : "/profile";
    const response = await api.get(endpoint);
    if (localStorage.getItem("token") !== token) return;
    const body = response.data;
    const profile = body?.profile ?? body?.data?.profile ?? body?.data ?? body;
    headerPhoto.value = profile?.photo_url || '';
    headerPhotoFailed.value = false;
    const name = profileName(profile);
    if (!name) return;
    currentUser.value.name = name;
    let saved: any = {};
    try { saved = JSON.parse(localStorage.getItem("user") || "{}"); } catch {}
    localStorage.setItem("user", JSON.stringify({
      ...saved,
      first_name: profile.first_name,
      last_name: profile.last_name,
      name,
    }));
  } catch {
    // Keep the last known name if profile loading temporarily fails.
  }
};
let photoRefreshTimer: ReturnType<typeof setInterval> | undefined;
const refreshPhoto = () => { void refreshHeaderName(); };
onMounted(() => {
  refreshPhoto();
  window.addEventListener('els-profile-photo-updated', refreshPhoto);
  // Renew the signed URL while the authenticated layout remains open.
  photoRefreshTimer = setInterval(refreshPhoto, 30 * 60 * 1000);
});
onUnmounted(() => {
  window.removeEventListener('els-profile-photo-updated', refreshPhoto);
  if (photoRefreshTimer !== undefined) clearInterval(photoRefreshTimer);
});
watch(() => route.path, () => { void refreshHeaderName(); });

const pageTitle = computed(() => {
  const titles: Record<string, string> = {
    "/dashboard": "Dashboard",
    "/admin-applications": "Applications",
    "/employees": "Employees",
    "/teaching-personnel": "Teaching Personnel",
    "/teaching-setup": "Teaching Setup",
    "/non-teaching-personnel": "Non-Teaching Personnel",
    "/leave-credits": "Leave Credits",
    "/leave-balances": "Leave Balances",
    "/leave-application": "Apply Leave",
    "/my-applications": "My Applications",
    "/admin-settings": "Settings",

    "/attendance": "Attendance",
   
    "/reports": "Reports",
    "/settings": "Settings",
    "/profile": "Profile",
  };

  return titles[route.path] || "Dashboard";
});

const logout = async () => {
  try {
    await logoutService();
  } catch (error) {
    console.log("Logout API failed:", error);
  }

  try {
    sessionStorage.removeItem(enhancementNoticeKey);
  } catch {
    // Logout must continue even when browser storage is unavailable.
  }
  noticeDismissed.value = false;

  localStorage.removeItem("token");
  localStorage.removeItem("user");

  router.push("/login");
};
</script>

<style scoped>
.header-photo { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }

.enhancement-notice {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  flex-shrink: 0;
  padding: 1rem 1.5rem;
  background: var(--surface);
  color: var(--text);
  border-bottom: 1px solid var(--border);
  border-left: 4px solid var(--primary);
}

.enhancement-notice-copy {
  flex: 1;
  min-width: 0;
}

.enhancement-notice h3 {
  margin: 0 0 0.25rem;
  font-size: 0.875rem;
  font-weight: 700;
}

.enhancement-notice p {
  margin: 0;
  font-size: 0.8125rem;
  line-height: 1.5;
  color: var(--text-muted);
}

.enhancement-notice-close {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  min-width: 36px;
  min-height: 36px;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  color: var(--text);
  background: transparent;
  cursor: pointer;
}

.enhancement-notice-close:hover {
  background: var(--surface-muted);
}

.enhancement-notice-close:focus-visible {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}

@media (max-width: 640px) {
  .enhancement-notice {
    padding: 0.75rem 1rem;
    gap: 0.5rem;
  }
}

@media print {
  .enhancement-notice {
    display: none;
  }
}

.app-shell {
  display: flex;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  background: var(--app-bg);
  transition: background 0.3s ease;
}

.sidebar {
  background: var(--sidebar);
  box-shadow: 2px 0 18px rgba(11, 31, 56, 0.12);
  transition: background 0.3s ease;
}

.sidebar-scroll {
  overflow-y: auto;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.sidebar-scroll::-webkit-scrollbar {
  display: none;
}

.main-shell {
  background: var(--app-bg);
  transition: background 0.3s ease;
}

.topbar {
  min-height: 64px;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  box-shadow: 0 1px 3px rgba(11, 31, 56, 0.04);
  transition: background 0.3s ease, border-color 0.3s ease;
}

.topbar h2 {
  color: var(--text);
  font-weight: 600;
  letter-spacing: -0.01em;
}

.content-panel {
  background: var(--app-bg);
}

.mobile-toggle {
  background: var(--surface);
  border: 1px solid var(--border);
  box-shadow: 0 2px 8px rgba(11, 31, 56, 0.06);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 1rem;
  border-radius: 0.5rem;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
  transition: all 0.2s;
  margin: 0.125rem 0;
  letter-spacing: 0.005em;
  font-size: 0.875rem;
}

.nav-item:hover {
  background: rgba(22, 135, 232, 0.12);
  color: #ffffff;
  transform: translateX(1px);
}

.nav-item.router-link-active {
  background: rgba(22, 135, 232, 0.16);
  color: #ffffff;
  font-weight: 600;
  border-left: 3px solid var(--primary);
  padding-left: 13px;
}

.icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.brand-block {
  min-width: 0;
}

.brand-logo {
  width: 36px;
  height: 36px;
  object-fit: contain;
  border-radius: 8px;
  background: #ffffff;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.user-summary {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--text);
}

.user-avatar {
  display: inline-flex;
  width: 36px;
  height: 36px;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
  color: #ffffff;
  font-weight: 700;
  font-size: 0.875rem;
}

.user-summary strong,
.user-summary small {
  display: block;
  line-height: 1.25;
}

.user-summary strong {
  font-size: 0.875rem;
  font-weight: 600;
}

.user-summary small {
  margin-top: 0.125rem;
  color: var(--text-muted);
  font-size: 0.75rem;
  text-transform: capitalize;
}

@media (max-width: 640px) {
  .content-panel {
    padding: 1rem;
  }

  .topbar {
    padding-right: 1rem;
  }
}

/* ============================================================
   SIDEBAR CLOSE BUTTON
   ============================================================ */

.sidebar-close-btn {
  color: rgba(255, 255, 255, 0.8);
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  flex-shrink: 0;
  border-radius: 8px;
}

.sidebar-close-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
}

/* ============================================================
   PROFILE ITEM
   ============================================================ */

.profile-item {
  color: rgba(255, 255, 255, 0.85);
  background: rgba(22, 135, 232, 0.1);
  border: 1px solid rgba(22, 135, 232, 0.2);
  border-radius: 8px;
}

.profile-item:hover {
  background: rgba(22, 135, 232, 0.16);
  color: #ffffff;
  transform: translateX(1px);
}

.profile-avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 6px;
  background: rgba(22, 135, 232, 0.2);
  flex-shrink: 0;
}

.profile-avatar .icon {
  width: 14px;
  height: 14px;
}

/* ============================================================
   LOGOUT ITEM
   ============================================================ */

.logout-item {
  color: rgba(248, 113, 113, 0.9);
  background: rgba(248, 113, 113, 0.08);
  border: 1px solid rgba(248, 113, 113, 0.15);
  border-radius: 8px;
}

.logout-item:hover {
  background: rgba(248, 113, 113, 0.14);
  color: #fca5a5;
  transform: translateX(1px);
}

/* ============================================================
   NAV SUB-ITEMS
   ============================================================ */

.nav-subitem {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  color: rgba(255, 255, 255, 0.75);
  font-weight: 500;
  transition: all 0.2s;
  margin: 0.0625rem 0;
  font-size: 0.8125rem;
}

.nav-subitem:hover {
  background: rgba(22, 135, 232, 0.1);
  color: #ffffff;
}

.nav-subitem.router-link-active {
  background: rgba(22, 135, 232, 0.14);
  color: #ffffff;
  font-weight: 600;
  border-left: 2px solid var(--primary);
  padding-left: 14px;
}
</style>