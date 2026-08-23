```vue
<template>
  <div class="dashboard-shell p-8 min-h-screen space-y-8">

    <!-- LOADING STATE -->
    <div v-if="loading" class="text-center py-10 text-white">
      Loading dashboard...
    </div>

    <!-- ERROR STATE -->
    <div
      v-else-if="loadError"
      class="neo-card p-6 border-l-4 border-red-600 text-white space-y-2"
    >
      <h3 class="text-lg font-semibold">
        Couldn't load your dashboard
      </h3>

      <p class="text-sm text-gray-300">
        {{ loadError }}
      </p>

      <button
        @click="retryLoad"
        class="mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition"
      >
        Retry
      </button>
    </div>

    <!-- ========================================================= -->
    <!-- ADMIN DASHBOARD -->
    <!-- ========================================================= -->
    <div v-else-if="isAdmin" class="space-y-8">

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Employees -->
        <div class="neo-card stats-card border-blue-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
              <Users class="w-6 h-6 text-blue-600" />
            </div>

            <div class="ml-5">
              <h3 class="text-sm text-white">
                Total Employees
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ totalEmployees }}
              </p>
            </div>
          </div>
        </div>

        <!-- Pending -->
        <div class="neo-card stats-card border-orange-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg">
              <Clock class="w-6 h-6 text-yellow-600" />
            </div>

            <div class="ml-4">
              <h3 class="text-sm text-white">
                Pending Leaves
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ pendingLeaves }}
              </p>
            </div>
          </div>
        </div>

        <!-- Approved -->
        <div class="neo-card stats-card border-green-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircle class="w-6 h-6 text-green-600" />
            </div>

            <div class="ml-4">
              <h3 class="text-sm text-white">
                Approved Leaves
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ approvedLeaves }}
              </p>
            </div>
          </div>
        </div>

        <!-- Disapproved -->
        <div class="neo-card stats-card border-red-700 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
              <AlertCircle class="w-6 h-6 text-red-600" />
            </div>

            <div class="ml-4">
              <h3 class="text-sm text-white">
                Disapproved Leaves
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ disapprovedLeaves }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================================================= -->
      <!-- RECENT APPLICATIONS & REQUESTS NEEDING ACTION -->
      <!-- ======================================================= -->
      <div class="grid lg:grid-cols-2 gap-6">

        <!-- Recent Applications -->
        <div class="neo-card p-6">

          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-white">
              Recent Leave Applications
            </h3>

            <span
              class="text-xs px-3 py-1 rounded-full bg-blue-500/20 text-blue-300"
            >
              {{ recentApplications.length }}
            </span>
          </div>

          <div
            v-if="recentApplications.length"
            class="space-y-3"
          >
            <div
              v-for="app in recentApplications"
              :key="app.id"
              class="application-item flex justify-between items-center p-4 rounded-xl"
            >

              <div class="flex items-center min-w-0">

                <div
                  class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center mr-4 flex-shrink-0 font-bold"
                >
                  {{ getEmployeeInitial(app) }}
                </div>

                <div class="min-w-0">
                  <p class="font-medium text-white truncate">
                    {{ getEmployeeName(app) }}
                  </p>

                  <p class="text-sm text-gray-300 truncate">
                    {{ getLeaveType(app) }}
                  </p>
                </div>
              </div>

              <div class="text-right ml-4 flex-shrink-0">

                <span
                  :class="getStatusClass(app.status)"
                  class="inline-block px-2 py-1 rounded-full text-xs font-semibold"
                >
                  {{ formatStatus(app.status) }}
                </span>

                <p class="text-xs text-gray-400 mt-1">
                  {{ app.date || app.created_at || "" }}
                </p>
              </div>
            </div>
          </div>

          <p
            v-else
            class="text-gray-400 text-sm"
          >
            No recent applications.
          </p>
        </div>

        <!-- ===================================================== -->
        <!-- LEAVE REQUESTS NEEDING ACTION -->
        <!-- ===================================================== -->
        <div class="neo-card p-6">

          <div class="flex justify-between items-center mb-4">

            <h3 class="text-lg font-semibold text-white">
              Leave Requests Needing Action
            </h3>

            <span
              v-if="pendingRequests.length"
              class="text-xs px-3 py-1 rounded-full bg-yellow-500/20 text-yellow-300"
            >
              {{ pendingRequests.length }} Pending
            </span>

          </div>

          <!-- PENDING REQUESTS -->
          <div
            v-if="pendingRequests.length"
            class="space-y-3"
          >

            <div
              v-for="request in pendingRequests"
              :key="request.id"
              class="request-item flex justify-between items-center p-4 rounded-xl"
            >

              <div class="min-w-0">

                <p class="font-medium text-white truncate">
                  {{ getEmployeeName(request) }}
                </p>

                <p class="text-sm text-gray-300">
                  {{ getLeaveType(request) }}

                  <span v-if="getDays(request) !== null">
                    • {{ getDays(request) }}
                    {{ getDays(request) === 1 ? "day" : "days" }}
                  </span>
                </p>

                <p
                  v-if="request.start_date || request.end_date"
                  class="text-xs text-gray-400 mt-1"
                >
                  {{ request.start_date || "" }}
                  <span v-if="request.start_date && request.end_date">
                    to
                  </span>
                  {{ request.end_date || "" }}
                </p>

              </div>

              <button
                @click="reviewRequest(request)"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition flex-shrink-0 ml-4"
              >
                Review
              </button>

            </div>
          </div>

          <!-- EMPTY STATE -->
          <div
            v-else
            class="py-8 text-center"
          >
            <div
              class="w-12 h-12 mx-auto mb-3 rounded-full bg-green-500/10 flex items-center justify-center"
            >
              <CheckCircle class="w-6 h-6 text-green-400" />
            </div>

            <p class="text-white font-medium">
              No pending requests
            </p>

            <p class="text-gray-400 text-sm mt-1">
              All leave applications have been reviewed.
            </p>
          </div>

        </div>
      </div>

      <!-- ======================================================= -->
      <!-- LEAVE BY DEPARTMENT -->
      <!-- ======================================================= -->
      <div class="neo-card p-6">

        <h3 class="font-semibold mb-4 text-white">
          Leave By Department
        </h3>

        <div
          v-if="leaveByDepartment.length"
          class="space-y-3"
        >

          <div
            v-for="dept in leaveByDepartment"
            :key="dept.department"
            class="department-item flex justify-between items-center rounded-xl p-4"
          >

            <div>
              <p class="font-semibold text-white">
                {{ dept.department }}
              </p>

              <p class="text-sm text-gray-400">
                Leave Requests
              </p>
            </div>

            <div class="text-2xl font-bold text-white">
              {{ dept.count }}
            </div>

          </div>

        </div>

        <p
          v-else
          class="text-gray-400"
        >
          No department data available.
        </p>

      </div>

      <!-- ======================================================= -->
      <!-- STATUS CHART & LEAVE BY TYPE -->
      <!-- ======================================================= -->
      <div class="grid lg:grid-cols-2 gap-6">

        <!-- Status Chart -->
        <div class="neo-card p-6">

          <h3 class="text-lg font-bold text-white">
            Leave Application Status
          </h3>

          <p class="text-sm text-gray-400 mb-6">
            Overview of submitted leave requests
          </p>

          <div class="space-y-4">

            <!-- Approved -->
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm text-white">
                  Approved
                </span>

                <span class="text-sm font-semibold text-white">
                  {{ statusChartData.approved }}
                </span>
              </div>

              <div class="w-full bg-gray-700 rounded-full h-3">
                <div
                  class="bg-green-500 h-3 rounded-full transition-all duration-500"
                  :style="{
                    width:
                      calculatePercentage(statusChartData.approved) + '%'
                  }"
                ></div>
              </div>
            </div>

            <!-- Pending -->
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm text-white">
                  Pending
                </span>

                <span class="text-sm font-semibold text-white">
                  {{ statusChartData.pending }}
                </span>
              </div>

              <div class="w-full bg-gray-700 rounded-full h-3">
                <div
                  class="bg-yellow-500 h-3 rounded-full transition-all duration-500"
                  :style="{
                    width:
                      calculatePercentage(statusChartData.pending) + '%'
                  }"
                ></div>
              </div>
            </div>

            <!-- Disapproved -->
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm text-white">
                  Disapproved
                </span>

                <span class="text-sm font-semibold text-white">
                  {{ statusChartData.disapproved }}
                </span>
              </div>

              <div class="w-full bg-gray-700 rounded-full h-3">
                <div
                  class="bg-red-500 h-3 rounded-full transition-all duration-500"
                  :style="{
                    width:
                      calculatePercentage(statusChartData.disapproved) + '%'
                  }"
                ></div>
              </div>
            </div>

          </div>
        </div>

        <!-- Leave By Type -->
        <div class="neo-card p-6">

          <h3 class="font-semibold mb-4 text-white">
            Leave By Type
          </h3>

          <div
            v-if="leaveByType.length"
            class="grid grid-cols-1 gap-4"
          >

            <div
              v-for="leave in leaveByType"
              :key="leave.name"
              class="leave-type-item p-5 rounded-xl border border-slate-700"
            >
              <p class="text-xs text-gray-400 truncate">
                {{ leave.name }}
              </p>

              <p class="text-3xl font-bold text-white mt-2">
                {{ leave.count }}
              </p>
            </div>

          </div>

          <p
            v-else
            class="text-gray-400 text-sm"
          >
            No leave type data available.
          </p>

        </div>
      </div>

      <!-- ======================================================= -->
      <!-- RECENT ACTIVITIES -->
      <!-- ======================================================= -->
      <div class="neo-card p-6">

        <h3 class="text-lg font-semibold mb-4 text-white">
          Recent Activities
        </h3>

        <div
          v-if="recentActivities.length"
          class="space-y-4"
        >

          <div
            v-for="activity in recentActivities"
            :key="activity.id"
            class="flex items-start"
          >

            <div
              class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3 text-green-700 font-bold flex-shrink-0"
            >
              ✓
            </div>

            <div>
              <p class="text-sm text-white">
                {{ activity.message }}
              </p>

              <p class="text-xs text-gray-400 mt-1">
                {{ activity.time }}
              </p>
            </div>

          </div>

        </div>

        <p
          v-else
          class="text-gray-400"
        >
          No recent activities.
        </p>

      </div>

      <!-- ======================================================= -->
      <!-- QUICK ACTIONS -->
      <!-- ======================================================= -->
      <div class="neo-card p-6">

        <h3 class="text-lg font-semibold mb-4 text-white">
          Quick Actions
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

          <button
            @click="goToEmployees"
            class="p-4 bg-blue-400 hover:bg-blue-300 rounded-lg text-black font-extrabold transition"
          >
            Create Employee Account
          </button>

          <button
            @click="goToLeaveRequests"
            class="p-4 bg-green-400 hover:bg-green-300 rounded-lg text-black font-extrabold transition"
          >
            Review Leave Requests
          </button>

          <button
            @click="goToReports"
            class="p-4 bg-purple-400 hover:bg-purple-300 rounded-lg text-black font-extrabold transition"
          >
            Generate Reports
          </button>

        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- EMPLOYEE DASHBOARD -->
    <!-- ========================================================= -->
    <div
      v-else-if="isEmployee"
      class="space-y-8"
    >

      <!-- Welcome -->
      <div class="neo-card p-6 border-l-4 border-blue-500">

        <div class="flex justify-between items-start">

          <div>
            <h2 class="text-3xl font-bold text-white">
              Welcome, {{ employeeInfo.name }}!
            </h2>

            <p class="text-gray-300 mt-2">
              {{ employeeInfo.department }}
              <span v-if="employeeInfo.department && employeeInfo.position">
                •
              </span>
              {{ employeeInfo.position }}
            </p>
          </div>

          <Briefcase class="w-12 h-12 text-blue-400" />

        </div>
      </div>

      <!-- Employee Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Total -->
        <div class="neo-card stats-card border-blue-500 p-6">

          <div class="flex items-center">

            <div class="p-3 bg-blue-100 rounded-lg">
              <FileCheck class="w-6 h-6 text-blue-600" />
            </div>

            <div class="ml-5">

              <h3 class="text-sm text-white">
                Total Applications
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ totalEmployeeApplications }}
              </p>

            </div>
          </div>
        </div>

        <!-- Pending -->
        <div class="neo-card stats-card border-orange-500 p-6">

          <div class="flex items-center">

            <div class="p-3 bg-yellow-100 rounded-lg">
              <Clock class="w-6 h-6 text-yellow-600" />
            </div>

            <div class="ml-4">

              <h3 class="text-sm text-white">
                Pending
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ totalPendingLeaves }}
              </p>

            </div>
          </div>
        </div>

        <!-- Approved -->
        <div class="neo-card stats-card border-green-500 p-6">

          <div class="flex items-center">

            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircle class="w-6 h-6 text-green-600" />
            </div>

            <div class="ml-4">

              <h3 class="text-sm text-white">
                Approved
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ totalApprovedLeaves }}
              </p>

            </div>
          </div>
        </div>

        <!-- Disapproved -->
        <div class="neo-card stats-card border-red-700 p-6">

          <div class="flex items-center">

            <div class="p-3 bg-red-100 rounded-lg">
              <AlertCircle class="w-6 h-6 text-red-600" />
            </div>

            <div class="ml-4">

              <h3 class="text-sm text-white">
                Disapproved
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ totalDisapprovedLeaves }}
              </p>

            </div>
          </div>
        </div>
      </div>

      <!-- My Applications & Upcoming Leaves -->
      <div class="grid lg:grid-cols-2 gap-6">

        <!-- My Applications -->
        <div class="neo-card p-6">

          <h3 class="text-lg font-semibold mb-4 text-white">
            My Leave Applications
          </h3>

          <div
            v-if="myApplications.length"
            class="space-y-3"
          >

            <div
              v-for="app in myApplications"
              :key="app.id"
              class="application-item p-4 rounded-lg"
            >

              <div class="flex justify-between items-start mb-2">

                <div>

                  <p class="font-medium text-white">
                    {{ getLeaveType(app) }}
                  </p>

                  <p class="text-sm text-gray-400">
                    {{ getDays(app) ?? 0 }} days
                  </p>

                </div>

                <span
                  :class="getStatusClass(app.status)"
                  class="px-3 py-1 rounded text-sm font-semibold"
                >
                  {{ formatStatus(app.status) }}
                </span>

              </div>

              <p class="text-xs text-gray-500">
                {{ app.start_date || "" }}
                <span v-if="app.start_date && app.end_date">
                  to
                </span>
                {{ app.end_date || "" }}
              </p>

            </div>
          </div>

          <p
            v-else
            class="text-white"
          >
            No leave applications yet.
          </p>

        </div>

        <!-- Upcoming Leaves -->
        <div class="neo-card p-6">

          <h3 class="text-lg font-semibold mb-4 text-white">
            Upcoming Leaves
          </h3>

          <div
            v-if="upcomingLeaves.length"
            class="space-y-3"
          >

            <div
              v-for="leave in upcomingLeaves"
              :key="leave.id"
              class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500"
            >

              <p class="font-medium text-gray-800">
                {{ getLeaveType(leave) }}
              </p>

              <p class="text-sm text-gray-600 mt-1">
                {{ leave.start_date || "" }}
                <span
                  v-if="leave.start_date && leave.end_date"
                >
                  to
                </span>
                {{ leave.end_date || "" }}
              </p>

              <p class="text-xs text-gray-500 mt-1">
                {{ getDays(leave) ?? 0 }} days
              </p>

            </div>
          </div>

          <p
            v-else
            class="text-white"
          >
            No upcoming approved leaves.
          </p>

        </div>
      </div>

      <!-- Employee Status & Leave Type -->
      <div class="grid lg:grid-cols-2 gap-6">

        <!-- Status -->
        <div class="neo-card p-6">

          <h3 class="text-lg font-bold text-white mb-4">
            Leave Status Overview
          </h3>

          <div class="space-y-4">

            <!-- Approved -->
            <div>

              <div class="flex justify-between mb-1">

                <span class="text-sm text-white">
                  Approved
                </span>

                <span class="text-sm font-semibold text-white">
                  {{ employeeStatusChart.approved }}
                </span>

              </div>

              <div class="w-full bg-gray-700 rounded-full h-3">

                <div
                  class="bg-green-500 h-3 rounded-full"
                  :style="{
                    width:
                      calculateEmployeePercentage(
                        employeeStatusChart.approved
                      ) + '%'
                  }"
                ></div>

              </div>
            </div>

            <!-- Pending -->
            <div>

              <div class="flex justify-between mb-1">

                <span class="text-sm text-white">
                  Pending
                </span>

                <span class="text-sm font-semibold text-white">
                  {{ employeeStatusChart.pending }}
                </span>

              </div>

              <div class="w-full bg-gray-700 rounded-full h-3">

                <div
                  class="bg-yellow-500 h-3 rounded-full"
                  :style="{
                    width:
                      calculateEmployeePercentage(
                        employeeStatusChart.pending
                      ) + '%'
                  }"
                ></div>

              </div>
            </div>

            <!-- Disapproved -->
            <div>

              <div class="flex justify-between mb-1">

                <span class="text-sm text-white">
                  Disapproved
                </span>

                <span class="text-sm font-semibold text-white">
                  {{ employeeStatusChart.disapproved }}
                </span>

              </div>

              <div class="w-full bg-gray-700 rounded-full h-3">

                <div
                  class="bg-red-500 h-3 rounded-full"
                  :style="{
                    width:
                      calculateEmployeePercentage(
                        employeeStatusChart.disapproved
                      ) + '%'
                  }"
                ></div>

              </div>
            </div>

          </div>
        </div>

        <!-- Leave By Type -->
        <div class="neo-card p-6">

          <h3 class="font-semibold mb-4 text-white">
            Leave Applications by Type
          </h3>

          <div class="space-y-3">

            <div
              v-for="leave in employeeLeaveByType"
              :key="leave.name"
              class="leave-type-item p-4 rounded-lg"
            >

              <div class="flex justify-between items-center">

                <p class="text-sm font-medium text-white">
                  {{ leave.name }}
                </p>

                <p class="text-2xl font-bold text-blue-400">
                  {{ leave.count }}
                </p>

              </div>

            </div>

            <div
              v-if="employeeLeaveByType.length === 0"
              class="text-center text-gray-400 py-4"
            >
              No leave applications yet.
            </div>

          </div>
        </div>
      </div>

      <!-- Employee Recent Activities -->
      <div class="neo-card p-6">

        <h3 class="text-lg font-semibold mb-4 text-white">
          Recent Activities
        </h3>

        <div
          v-if="employeeActivities.length"
          class="space-y-3"
        >

          <div
            v-for="activity in employeeActivities"
            :key="activity.id"
            class="border-l-4 border-blue-400 pl-4 py-2"
          >

            <p class="text-sm text-white">
              {{ activity.message }}
            </p>

            <p class="text-xs text-gray-400 mt-1">
              {{ activity.time }}
            </p>

          </div>
        </div>

        <p
          v-else
          class="text-white"
        >
          No recent activities.
        </p>

      </div>

      <!-- Employee Quick Actions -->
      <div class="neo-card p-6">

        <h3 class="text-lg font-semibold mb-4 text-white">
          Quick Actions
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <button
            @click="goToApply"
            class="p-4 bg-blue-400 hover:bg-blue-300 rounded-lg text-black font-extrabold transition"
          >
            Apply for Leave
          </button>

          <button
            @click="goToMyApplications"
            class="p-4 bg-green-400 hover:bg-green-300 rounded-lg text-black font-extrabold transition"
          >
            View My Applications
          </button>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { getAdminDashboard, getEmployeeDashboard } from "@/services/dashboard";
import api from "@/services/api";
import { useRouter } from "vue-router";

import {
  Users,
  Clock,
  CheckCircle,
  AlertCircle,
  FileCheck,
  Briefcase,
} from "lucide-vue-next";

import { isAxiosError } from "axios";

// ============================================================
// ROUTER / COMMON DATA
// ============================================================

const router = useRouter();

const loading = ref(true);
const loadError = ref<string | null>(null);

const userRole = ref<string>("employee");

// ============================================================
// ADMIN DATA
// ============================================================

const totalEmployees = ref(0);
const pendingLeaves = ref(0);
const approvedLeaves = ref(0);
const disapprovedLeaves = ref(0);

const recentApplications = ref<any[]>([]);

const pendingRequests = ref<any[]>([]);

const leaveByType = ref<any[]>([]);
const leaveByDepartment = ref<any[]>([]);

const statusChartData = ref({
  approved: 0,
  pending: 0,
  disapproved: 0,
});

const recentActivities = ref<any[]>([]);

// ============================================================
// EMPLOYEE DATA
// ============================================================

const employeeInfo = ref({
  name: "",
  email: "",
  department: "",
  position: "",
});

const myApplications = ref<any[]>([]);
const upcomingLeaves = ref<any[]>([]);

const employeeStatusChart = ref({
  approved: 0,
  pending: 0,
  disapproved: 0,
});

const employeeLeaveByType = ref<any[]>([]);
const employeeActivities = ref<any[]>([]);

const totalEmployeeApplications = ref(0);
const totalPendingLeaves = ref(0);
const totalApprovedLeaves = ref(0);
const totalDisapprovedLeaves = ref(0);

// ============================================================
// COMPUTED
// ============================================================

const isAdmin = computed(() => {
  return userRole.value.toLowerCase() === "admin";
});

const isEmployee = computed(() => {
  return userRole.value.toLowerCase() === "employee";
});

// ============================================================
// ERROR HANDLER
// ============================================================

const extractErrorMessage = (
  error: unknown,
  fallback: string
): string => {

  if (isAxiosError(error)) {
    return (
      error.response?.data?.error ||
      error.response?.data?.message ||
      fallback
    );
  }

  if (error instanceof Error) {
    return error.message;
  }

  return fallback;
};

// ============================================================
// STATUS HELPERS
// ============================================================

const normalizeStatus = (status: unknown): string => {
  if (status === null || status === undefined) {
    return "";
  }

  return String(status)
    .trim()
    .toLowerCase();
};

const formatStatus = (status: unknown): string => {
  const normalized = normalizeStatus(status);

  if (!normalized) {
    return "Unknown";
  }

  return normalized.charAt(0).toUpperCase() + normalized.slice(1);
};

const getStatusClass = (status: unknown): string => {

  const normalized = normalizeStatus(status);

  if (normalized === "approved") {
    return "bg-green-500/20 text-green-300";
  }

  if (
    normalized === "pending" ||
    normalized === "for approval" ||
    normalized === "for_approval"
  ) {
    return "bg-yellow-500/20 text-yellow-300";
  }

  if (
    normalized === "disapproved" ||
    normalized === "rejected" ||
    normalized === "denied"
  ) {
    return "bg-red-500/20 text-red-300";
  }

  return "bg-gray-500/20 text-gray-300";
};

// ============================================================
// APPLICATION DATA HELPERS
// These allow the dashboard to work with slightly different
// backend field names.
// ============================================================

const getEmployeeName = (application: any): string => {

  if (!application) {
    return "Unknown Employee";
  }

  if (typeof application.employee === "string") {
    return application.employee;
  }

  if (
    application.employee &&
    typeof application.employee === "object"
  ) {
    return (
      application.employee.name ||
      application.employee.full_name ||
      application.employee.employee_name ||
      "Unknown Employee"
    );
  }

  return (
    application.employee_name ||
    application.employeeName ||
    application.full_name ||
    application.name ||
    "Unknown Employee"
  );
};

const getEmployeeInitial = (application: any): string => {

  const name = getEmployeeName(application);

  if (!name || name === "Unknown Employee") {
    return "?";
  }

  return name.charAt(0).toUpperCase();
};

const getLeaveType = (application: any): string => {

  if (!application) {
    return "Leave";
  }

  if (typeof application.leave_type === "string") {
    return application.leave_type;
  }

  if (
    application.leave_type &&
    typeof application.leave_type === "object"
  ) {
    return (
      application.leave_type.name ||
      application.leave_type.leave_name ||
      "Leave"
    );
  }

  return (
    application.leaveType ||
    application.leave_name ||
    application.type ||
    "Leave"
  );
};

const getDays = (application: any): number | null => {

  if (!application) {
    return null;
  }

  const value =
    application.days ??
    application.number_of_days ??
    application.total_days ??
    application.duration;

  if (value === null || value === undefined || value === "") {
    return null;
  }

  const numberValue = Number(value);

  return Number.isNaN(numberValue)
    ? null
    : numberValue;
};

// ============================================================
// PERCENTAGE HELPERS
// ============================================================

const calculatePercentage = (value: number) => {

  const total =
    statusChartData.value.approved +
    statusChartData.value.pending +
    statusChartData.value.disapproved;

  if (total === 0) {
    return 0;
  }

  return Math.round((value / total) * 100);
};

const calculateEmployeePercentage = (value: number) => {

  const total =
    employeeStatusChart.value.approved +
    employeeStatusChart.value.pending +
    employeeStatusChart.value.disapproved;

  if (total === 0) {
    return 0;
  }

  return Math.round((value / total) * 100);
};

// ============================================================
// FETCH CURRENT USER
// ============================================================

const getCurrentUser = async () => {

  const response = await api.get("/me");

  userRole.value = String(
    response.data?.role || "employee"
  ).toLowerCase();
};

// ============================================================
// LOAD ADMIN DASHBOARD
// ============================================================

const loadAdminDashboard = async () => {

  const response = await getAdminDashboard();

  const data = response.data;

  if (!data || !data.summary) {
    throw new Error(
      "Unexpected response shape from /admin/dashboard"
    );
  }

  // ----------------------------------------------------------
  // SUMMARY
  // ----------------------------------------------------------

  totalEmployees.value =
    Number(data.summary.totalEmployees) || 0;

  pendingLeaves.value =
    Number(data.summary.pendingLeaves) || 0;

  approvedLeaves.value =
    Number(data.summary.approvedLeaves) || 0;

  disapprovedLeaves.value =
    Number(data.summary.disapprovedLeaves) || 0;

  // ----------------------------------------------------------
  // RECENT APPLICATIONS
  // ----------------------------------------------------------

  recentApplications.value =
    Array.isArray(data.recentApplications)
      ? data.recentApplications
      : [];

  // ----------------------------------------------------------
  // IMPORTANT FIX:
  //
  // Some backend responses may not contain pendingRequests.
  // In that case, derive pending requests from the applications
  // that are already returned.
  // ----------------------------------------------------------

  const backendPendingRequests =
    Array.isArray(data.pendingRequests)
      ? data.pendingRequests
      : [];

  if (backendPendingRequests.length > 0) {

    pendingRequests.value =
      backendPendingRequests.filter((request: any) => {

        const status = normalizeStatus(request.status);

        return (
          status === "pending" ||
          status === "for approval" ||
          status === "for_approval"
        );
      });

  } else {

    pendingRequests.value =
      recentApplications.value.filter((application: any) => {

        const status = normalizeStatus(
          application.status
        );

        return (
          status === "pending" ||
          status === "for approval" ||
          status === "for_approval"
        );
      });
  }

  // ----------------------------------------------------------
  // OTHER ADMIN DATA
  // ----------------------------------------------------------

  leaveByType.value =
    Array.isArray(data.leaveByType)
      ? data.leaveByType
      : [];

  leaveByDepartment.value =
    Array.isArray(data.leaveByDepartment)
      ? data.leaveByDepartment
      : [];

  statusChartData.value =
    data.statusChart || {
      approved: 0,
      pending: 0,
      disapproved: 0,
    };

  recentActivities.value =
    Array.isArray(data.recentActivities)
      ? data.recentActivities
      : [];
};

// ============================================================
// LOAD EMPLOYEE DASHBOARD
// ============================================================

const loadEmployeeDashboard = async () => {

  const response = await getEmployeeDashboard();

  const data = response.data;

  if (!data || !data.summary) {
    throw new Error(
      "Unexpected response shape from /employee/dashboard"
    );
  }

  totalEmployeeApplications.value =
    Number(data.summary.totalApplications) || 0;

  totalPendingLeaves.value =
    Number(data.summary.pendingLeaves) || 0;

  totalApprovedLeaves.value =
    Number(data.summary.approvedLeaves) || 0;

  totalDisapprovedLeaves.value =
    Number(data.summary.disapprovedLeaves) || 0;

  employeeInfo.value =
    data.employee || {
      name: "",
      email: "",
      department: "",
      position: "",
    };

  myApplications.value =
    Array.isArray(data.myApplications)
      ? data.myApplications
      : [];

  upcomingLeaves.value =
    Array.isArray(data.upcomingLeaves)
      ? data.upcomingLeaves
      : [];

  employeeStatusChart.value =
    data.statusChart || {
      approved: 0,
      pending: 0,
      disapproved: 0,
    };

  employeeLeaveByType.value =
    Array.isArray(data.leaveByType)
      ? data.leaveByType
      : [];

  employeeActivities.value =
    Array.isArray(data.recentActivities)
      ? data.recentActivities
      : [];
};

// ============================================================
// REVIEW PENDING REQUEST
// ============================================================

const reviewRequest = (request: any) => {

  if (request?.id) {

    router.push({
      path: "/admin-applications",
      query: {
        id: String(request.id),
      },
    });

    return;
  }

  router.push("/admin-applications");
};

// ============================================================
// NAVIGATION
// ============================================================

const goToEmployees = () => {
  router.push("/employees");
};

const goToLeaveRequests = () => {
  router.push("/admin-applications");
};

const goToReports = () => {
  router.push("/reports");
};

const goToApply = () => {
  router.push("/leave-application");
};

const goToMyApplications = () => {
  router.push("/my-applications");
};

// ============================================================
// INITIAL LOAD
// ============================================================

const runLoad = async () => {

  loading.value = true;
  loadError.value = null;

  try {

    await getCurrentUser();

    if (isAdmin.value) {
      await loadAdminDashboard();
    } else {
      await loadEmployeeDashboard();
    }

  } catch (error) {

    loadError.value = extractErrorMessage(
      error,
      "Something went wrong loading your dashboard. Please try again."
    );

  } finally {

    loading.value = false;
  }
};

const retryLoad = () => {
  runLoad();
};

onMounted(runLoad);
</script>

<style scoped>
.dashboard-shell {
  background: #080d14;
}

.neo-card {
  background: #111d2e;
  border: 1px solid #1e293b;
  border-radius: 1.4rem;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;
}

.neo-card:hover {
  box-shadow: 0 14px 26px rgba(15, 23, 42, 0.08);
}

.stats-card {
  border-left: 4px solid currentColor;
  padding: 1.35rem;
}

.stats-card .p-3 {
  border-radius: 0.9rem;
}

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {
  letter-spacing: -0.01em;
}

/* ============================================================
   APPLICATION ITEMS
   ============================================================ */

.application-item {
  background: #172337;
  border: 1px solid #24344d;
  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}

.application-item:hover {
  background: #1b2a40;
  border-color: #334968;
  transform: translateY(-1px);
}

/* ============================================================
   PENDING REQUEST ITEMS
   ============================================================ */

.request-item {
  background: #172337;
  border: 1px solid #3b3b27;
  border-left: 4px solid #eab308;
  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}

.request-item:hover {
  background: #1b2a40;
  border-color: #5b5a31;
  transform: translateY(-1px);
}

/* ============================================================
   DEPARTMENT ITEMS
   ============================================================ */

.department-item {
  background: #172337;
  border: 1px solid #24344d;
  transition:
    background 0.2s ease,
    transform 0.2s ease;
}

.department-item:hover {
  background: #1b2a40;
  transform: translateY(-1px);
}

/* ============================================================
   LEAVE TYPE
   ============================================================ */

.leave-type-item {
  background: #172337;
  border: 1px solid #24344d;
  transition:
    background 0.2s ease,
    transform 0.2s ease;
}

.leave-type-item:hover {
  background: #1b2a40;
  transform: translateY(-1px);
}

/* ============================================================
   BUTTONS
   ============================================================ */

button {
  transition:
    background-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

button:hover {
  transform: translateY(-1px);
}
</style>
```
