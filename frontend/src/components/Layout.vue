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
        <div>
          <h1 class="text-2xl font-bold text-blue-600">ELS</h1>
          <p class="text-white-600">System</p>
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

      <!--
        NOTE: @click="sidebarOpen = false" added on the <nav> wrapper.
        Clicks on any router-link inside bubble up to this handler,
        so tapping/choosing a nav item auto-closes the sidebar on
        mobile. Harmless on desktop since the sidebar's transform
        class forces translate-x-0 there regardless of sidebarOpen.
      -->
      <nav
        class="mt-4 space-y-1 px-3 flex-1 overflow-y-auto"
        @click="sidebarOpen = false"
      >
        <router-link to="/dashboard" class="nav-item">
          <LayoutDashboard class="icon" />
          Dashboard
        </router-link>

        <router-link
            v-if="currentUser.role === 'admin'"
            to="/employees"
            class="nav-item"
        >
            <Users class="icon" />
            Employees
        </router-link>
        
        <router-link
          v-if="currentUser.role === 'admin'"
          to="/admin-applications"
          class="nav-item"
        >
          <FileCheck class="icon" />
          Applications
        </router-link>

        <router-link
          v-if="currentUser.role === 'admin'"
          to="/leave-credits"
          class="nav-item"
        >
          <FilePlus class="icon" />
          Leave Credits
        </router-link>

        <router-link
          v-if="currentUser.role === 'admin'"
          to="/leave-balances"
          class="nav-item"
        >
          <Wallet class="icon" />
          Leave Balances
        </router-link>

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
        class="nav-item">
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
        class="nav-item">
          <Settings class="icon" />
          Settings
        </router-link>

         <router-link 
        v-if="currentUser.role === 'admin'"
        to="/admin-settings" 
        class="nav-item">
          <Settings class="icon" />
          Settings
        </router-link>
      </nav>

      <!--
        Profile + Logout pinned to the bottom of the sidebar.
        FIX: added @click="sidebarOpen = false" here too, mirroring
        the <nav> handler above — this block lives outside <nav>,
        so without its own handler, clicking Profile (or Logout)
        left the sidebar open on mobile.
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
        <h2 class="text-blue-600 font-bold text-xl tracking-tight">{{ pageTitle }}</h2>

        <div class="flex items-center gap-4">
          

          

          
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
  Wallet,
  Scale,
  UserCircle2,
} from "lucide-vue-next";

const route = useRoute();
const router = useRouter();

const sidebarOpen = ref(false);

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
  background: #010307;
}

.sidebar {
  background: #0B1628;
  box-shadow: 0 0 0 1px rgba(12, 29, 75, 0.15);
}

.main-shell {
  background: #080D14;
}

.topbar {
  background: #0B1628;
  border-bottom: 1px solid #080D14;
  box-shadow: 0 1px 0 rgba(148, 163, 184, 0.08);
}

.content-panel {
  background: #000000;
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
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.18), rgba(96, 165, 250, 0.09));
  box-shadow: inset 0 0 0 1px rgba(147, 197, 253, 0.22);
  color: #ffffff;
}

.icon {
  width: 18px;
  height: 18px;
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
</style>