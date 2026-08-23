<template>
  <div class="app-shell">
    <!-- Mobile Menu Toggle -->
    <button
      @click="sidebarOpen = !sidebarOpen"
      class="mobile-toggle lg:hidden fixed top-4 left-4 z-50 p-2 rounded-xl"
    >
      <Menu v-if="!sidebarOpen" class="w-5 h-5 text-gray-600" />
      <X v-else class="w-5 h-5 text-gray-600" />
    </button>

    <!-- Overlay -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30"
    ></div>

    <!-- Sidebar -->
    <aside
      class="sidebar fixed lg:relative h-full w-64 z-40 transition-transform duration-300"
      :class="
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      "
    >
      <div class="p-6">
        <h1 class="text-2xl font-bold text-blue-600">E-LMS</h1>
        <p class="text-white-600">System</p>
      </div>

      <nav class="mt-4 space-y-1 px-3">
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

        <router-link to="/attendance" class="nav-item">
          <Calendar class="icon" />
          Attendance
        </router-link>

        <router-link 
        v-if="currentUser.role === 'employee'"
        to="/records" 
        class="nav-item">
          <Users class="icon" />
          Records
        </router-link>

        <router-link to="/reports" class="nav-item">
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
        
        <button @click="logout" class="nav-item text-red-600 w-full">
          <LogOut class="icon" />
          Logout
        </button>
      </nav>
    </aside>

    <!-- MAIN -->
    <div class="main-shell flex flex-col flex-1 w-full min-w-0 overflow-hidden">
      <!-- HEADER -->
      <header class="topbar flex items-center justify-between px-6 py-4">
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
  background: #ffffff;
  border: 1px solid #e7edf7;
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
</style>
