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
      <div class="p-6 flex items-center justify-between">
        <div class="brand-block">
          <h1 class="text-xl font-bold text-white">ENHS</h1>
          <p class="text-sm text-blue-100">Leave Management System</p>
        </div>

        <!-- Close button lives inside the sidebar's own header, right-aligned -->
        <button
          v-if="sidebarOpen"
          @click="sidebarOpen = false"
          class="sidebar-close-btn lg:hidden p-2 rounded-xl"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <nav
        class="mt-4 space-y-1 px-3 flex-1 overflow-y-auto"
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

        <!-- Leave Management Dropdown -->
        <div v-if="currentUser.role === 'admin'">
          <button
            @click.stop="toggleLeaveMenu"
            class="nav-item w-full justify-between"
            :class="{ 'router-link-active': isLeaveRouteActive }"
          >
            <span class="flex items-center gap-3">
              <FileCheck class="icon" />
              Leave Management
            </span>

            <ChevronDown
              class="icon transition-transform duration-200"
              :class="{ 'rotate-180': leaveMenuOpen }"
            />
          </button>

          <div
            v-show="leaveMenuOpen || isLeaveRouteActive"
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
          v-if="currentUser.role === 'employee'"
          to="/records"
          class="nav-item"
        >
          <Users class="icon" />
          Records
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
        <h2 class="text-blue-600 font-bold text-xl tracking-tight">
          {{ pageTitle }}
        </h2>

        <div class="user-summary" aria-label="Current user">
          <span class="user-avatar">{{ currentUser.name.charAt(0).toUpperCase() }}</span>
          <span class="hidden sm:block">
            <strong>{{ currentUser.name }}</strong>
            <small>{{ currentUser.role }}</small>
          </span>
        </div>
      </header>

      <!-- CONTENT -->
      <main class="content-panel flex-1 overflow-auto p-6 w-full">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { logout as logoutService } from "@/services/auth";

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
} from "lucide-vue-next";

const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(false);
const personnelMenuOpen = ref(false);
const leaveMenuOpen = ref(false);

const togglePersonnelMenu = () => {
  personnelMenuOpen.value = !personnelMenuOpen.value;
};

const toggleLeaveMenu = () => {
  leaveMenuOpen.value = !leaveMenuOpen.value;
};

const isPersonnelRouteActive = computed(() => {
  return [
    "/employees",
    "/teaching-personnel",
    "/teaching-setup",
    "/non-teaching-personnel",
  ].includes(route.path);
});

const isLeaveRouteActive = computed(() => {
  return [
    "/admin-applications",
    "/leave-credits",
    "/leave-balances",
  ].includes(route.path);
});
// Logged-in user
const user = JSON.parse(localStorage.getItem("user") || "{}");

const currentUser = ref({
  name: user.email ? user.email.split("@")[0] : "User",
  email: user.email || "",
  role: user.role || "",
});

const pageTitle = computed(() => {
  const titles: Record<string, string> = {
    "/dashboard": "Dashboard",
    "/admin-applications": "Applications",
    "/employees": "Employees",
    "/leave-credits": "Leave Credits",
    "/leave-balances": "Leave Balances",
    "/leave-application": "Apply Leave",
    "/admin-settings": "Settings",

    "/attendance": "Attendance",
    "/my-applications": "My Applications",
    "/records": "Records",
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

  localStorage.removeItem("token");
  localStorage.removeItem("user");

  router.push("/login");
};
</script>

<style scoped>
.app-shell {
  display: flex;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  background: var(--app-bg);
}

.sidebar {
  background: linear-gradient(180deg, #12355b 0%, #0e2b4b 100%);
  box-shadow: 2px 0 18px rgba(15, 47, 82, 0.14);
}

.main-shell {
  background: var(--app-bg);
}

.topbar {
  min-height: 72px;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  box-shadow: 0 2px 10px rgba(23, 32, 51, 0.05);
}

.content-panel {
  background: var(--app-bg);
}

.mobile-toggle,
.search-input,
.bell-wrap,
.user-pill {
  background: #020914;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
}

.nav-item {
  @apply flex items-center gap-3 px-4 py-3 rounded-xl text-white font-medium transition-all duration-200;
  margin: 0.3rem 0;
  letter-spacing: 0.01em;
}

.nav-item:hover {
  background: rgba(147, 197, 253, 0.12);
  color: #ffffff;
  transform: translateX(2px);
}

.nav-item.router-link-active {
  background: linear-gradient(
    135deg,
    rgba(59, 130, 246, 0.18),
    rgba(96, 165, 250, 0.09)
  );
  box-shadow: inset 0 0 0 1px rgba(147, 197, 253, 0.22);
  color: #ffffff;
}

.icon {
  width: 18px;
  height: 18px;
}

.brand-block {
  min-width: 0;
}

.user-summary {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  color: var(--text);
  text-transform: capitalize;
}

.user-summary strong,
.user-summary small {
  display: block;
  line-height: 1.25;
}

.user-summary strong {
  font-size: 0.875rem;
}

.user-summary small {
  margin-top: 0.15rem;
  color: var(--text-muted);
  font-size: 0.72rem;
}

.user-avatar {
  display: inline-flex;
  width: 38px;
  height: 38px;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  background: #dbeafe;
  color: #1e40af;
  font-weight: 800;
}

@media (max-width: 640px) {
  .content-panel {
    padding: 1rem;
  }

  .topbar {
    padding-right: 1rem;
  }
}

.search-input {
  border: 1px solid #dfeaf7;
  background: #f9fbff;
  color: #1f2937;
}

.search-input:focus {
  outline: none;
  border-color: rgba(59, 130, 246, 0.55);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08);
}

/* ============================================================
   SIDEBAR CLOSE BUTTON — sits at the right of the sidebar's own
   header row (next to "E-LMS"), only shown on mobile while open.
   ============================================================ */

.sidebar-close-btn {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  flex-shrink: 0;
}

.sidebar-close-btn:hover {
  background: rgba(255, 255, 255, 0.12);
}

/* ============================================================
   PROFILE — sits directly above Logout in the pinned footer
   section. The icon is wrapped in a circular badge (rounded-full)
   so it echoes the round avatar used on the Profile page itself,
   instead of the square icon treatment used by the other nav items.
   ============================================================ */

.profile-item {
  color: #93c5fd;
  background: rgba(59, 130, 246, 0.08);
  border: 1px solid rgba(59, 130, 246, 0.15);
}

.profile-item:hover {
  background: rgba(59, 130, 246, 0.16);
  color: #bfdbfe;
  transform: translateX(2px);
}

.profile-avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 9999px;
  background: rgba(59, 130, 246, 0.18);
  flex-shrink: 0;
}

.profile-avatar .icon {
  width: 16px;
  height: 16px;
}

/* ============================================================
   LOGOUT — pinned at bottom via the wrapping div in the template,
   styled red to signal a destructive/exit action. Declared after
   .nav-item's rules so it wins the color/hover overrides.
   ============================================================ */

.logout-item {
  color: #f87171;
  background: rgba(248, 113, 113, 0.08);
  border: 1px solid rgba(248, 113, 113, 0.15);
}

.logout-item:hover {
  background: rgba(248, 113, 113, 0.16);
  color: #fca5a5;
  transform: translateX(2px);
}

/* ============================================================
   NAV SUB-ITEMS — indented links inside the collapsible
   "Employee Management" submenu (Employees / Teaching Personnel /
   Non-Teaching Personnel).
   ============================================================ */

.nav-subitem {
  @apply flex items-center gap-3 px-4 py-2 rounded-lg text-white/80 text-sm font-medium transition-all duration-200;
  margin: 0.15rem 0;
}

.nav-subitem:hover {
  background: rgba(147, 197, 253, 0.1);
  color: #ffffff;
}

.nav-subitem.router-link-active {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.22), rgba(96, 165, 250, 0.1));
  color: #ffffff;
  font-weight: 600;
}
</style>
