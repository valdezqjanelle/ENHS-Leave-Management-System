<template>
  <div class="dashboard-shell p-8 min-h-screen space-y-8">
    <div v-if="loading" class="text-center py-10 text-white">
      Loading dashboard...
    </div>

    <!-- ERROR STATE -->
    <div
      v-else-if="loadError"
      class="neo-card p-6 border-l-4 border-red-600 text-white space-y-2"
    >
      <h3 class="text-lg font-semibold">Couldn't load your dashboard</h3>
      <p class="text-sm text-gray-300">{{ loadError }}</p>
      <button
        @click="retryLoad"
        class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm"
      >
        Retry
      </button>
    </div>

    <!-- ADMIN DASHBOARD -->
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
              <h3 class="text-sm text-white">Total Employees</h3>
              <p class="text-2xl font-semibold text-white">{{ totalEmployees }}</p>
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
              <h3 class="text-sm text-white">Pending Leaves</h3>
              <p class="text-2xl font-semibold text-white">{{ pendingLeaves }}</p>
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
              <h3 class="text-sm text-white">Approved Leaves</h3>
              <p class="text-2xl font-semibold text-white">{{ approvedLeaves }}</p>
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
              <h3 class="text-sm text-white">Disapproved Leaves</h3>
              <p class="text-2xl font-semibold text-white">{{ disapprovedLeaves }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Applications & Requests Needing Action -->
      <div class="grid lg:grid-cols-2 gap-6">
        <div class="neo-card p-6">
          <h3 class="text-lg font-semibold mb-4 text-white">Recent Leave Applications</h3>
          <div v-if="recentApplications.length" class="space-y-3">
            <div
              v-for="app in recentApplications"
              :key="app.id"
              class="neo-card flex justify-between items-center bg-gray-50 p-4 rounded-lg"
            >
              <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center mr-4">
                  {{ app.employee?.charAt(0) }}
                </div>
                <div>
                  <p class="font-medium text-white">{{ app.employee }}</p>
                  <p class="text-sm text-white">{{ app.leave_type }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm text-white">{{ app.status }}</p>
                <p class="text-xs text-white">{{ app.date }}</p>
              </div>
            </div>
          </div>
          <p v-else class="text-white">No recent applications</p>
        </div>

        <div class="neo-card p-6">
          <h3 class="text-lg font-semibold mb-4 text-white">Leave Requests Needing Action</h3>
          <div v-if="pendingRequests.length" class="space-y-3">
            <div
              v-for="request in pendingRequests"
              :key="request.id"
              class="neo-card flex justify-between items-center bg-gray-50 p-4 rounded-lg"
            >
              <div>
                <p class="font-medium text-white">{{ request.employee }}</p>
                <p class="text-sm text-white">{{ request.leave_type }} • {{ request.days }} days</p>
              </div>
              <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Review</button>
            </div>
          </div>
          <p v-else class="text-white">No pending requests</p>
        </div>
      </div>

      <!-- Leave by Department -->
      <div class="neo-card p-6">
        <h3 class="font-semibold mb-4 text-white">Leave By Department</h3>
        <div class="space-y-3">
          <div
            v-for="dept in leaveByDepartment"
            :key="dept.department"
            class="neo-card flex justify-between items-center bg-gray-50 rounded-xl p-4"
          >
            <div>
              <p class="font-semibold text-white">{{ dept.department }}</p>
              <p class="text-sm text-white">Leave Requests</p>
            </div>
            <div class="text-2xl font-bold text-white">{{ dept.count }}</div>
          </div>
        </div>
      </div>

      <!-- Status Chart & Leave by Type -->
      <div class="grid lg:grid-cols-2 gap-6">
        <div class="neo-card p-6">
          <h3 class="text-lg font-bold text-white">Leave Application Status</h3>
          <p class="text-sm text-white mb-6">Overview of submitted leave requests</p>
          <div class="space-y-4">
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm text-white">Approved</span>
                <span class="text-sm font-semibold text-white">{{ statusChartData.approved }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div
                  class="bg-green-500 h-3 rounded-full transition-all duration-500"
                  :style="{ width: calculatePercentage(statusChartData.approved) + '%' }"
                ></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm text-white">Pending</span>
                <span class="text-sm font-semibold text-white">{{ statusChartData.pending }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div
                  class="bg-yellow-500 h-3 rounded-full"
                  :style="{ width: calculatePercentage(statusChartData.pending) + '%' }"
                ></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm text-white">Disapproved</span>
                <span class="text-sm font-semibold text-white">{{ statusChartData.disapproved }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div
                  class="bg-red-500 h-3 rounded-full"
                  :style="{ width: calculatePercentage(statusChartData.disapproved) + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <div class="neo-card p-6">
          <h3 class="font-semibold mb-4 text-white">Leave By Type</h3>
          <div class="grid grid-cols-1 gap-4">
            <div
              v-for="leave in leaveByType"
              :key="leave.name"
              class="neo-card p-5 rounded-xl border"
            >
              <p class="text-xs text-white truncate">{{ leave.name }}</p>
              <p class="text-3xl font-bold text-white mt-2">{{ leave.count }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="neo-card p-6">
        <h3 class="text-lg font-semibold mb-4 text-white">Recent Activities</h3>
        <div class="space-y-4">
          <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start">
            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3">✓</div>
            <div>
              <p class="text-sm text-white">{{ activity.message }}</p>
              <p class="text-xs text-white">{{ activity.time }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="neo-card p-6">
        <h3 class="text-lg font-semibold mb-4 text-white">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <button @click="goToEmployees" class="p-4 bg-blue-400 rounded-lg hover:bg-blue-300 text-black font-extrabold">
            Create Employee Account
          </button>
          <button @click="goToLeaveRequests" class="p-4 bg-green-400 rounded-lg hover:bg-green-300 text-black font-extrabold">
            Review Leave Requests
          </button>
          <button @click="goToReports" class="p-4 bg-purple-400 rounded-lg hover:bg-purple-300 text-black font-extrabold">
            Generate Reports
          </button>
        </div>
      </div>
    </div>

    <!-- EMPLOYEE DASHBOARD -->
    <div v-else-if="isEmployee" class="space-y-8">
      <!-- Welcome Section -->
      <div class="neo-card p-6 border-l-4 border-blue-500">
        <div class="flex justify-between items-start">
          <div>
            <h2 class="text-3xl font-bold text-white">Welcome, {{ employeeInfo.name }}!</h2>
            <p class="text-gray-300 mt-2">{{ employeeInfo.department }} • {{ employeeInfo.position }}</p>
          </div>
          <Briefcase class="w-12 h-12 text-blue-400" />
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="neo-card stats-card border-blue-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
              <FileCheck class="w-6 h-6 text-blue-600" />
            </div>
            <div class="ml-5">
              <h3 class="text-sm text-white">Total Applications</h3>
              <p class="text-2xl font-semibold text-white">{{ totalEmployeeApplications }}</p>
            </div>
          </div>
        </div>

        <div class="neo-card stats-card border-orange-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg">
              <Clock class="w-6 h-6 text-yellow-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm text-white">Pending</h3>
              <p class="text-2xl font-semibold text-white">{{ totalPendingLeaves }}</p>
            </div>
          </div>
        </div>

        <div class="neo-card stats-card border-green-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircle class="w-6 h-6 text-green-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm text-white">Approved</h3>
              <p class="text-2xl font-semibold text-white">{{ totalApprovedLeaves }}</p>
            </div>
          </div>
        </div>

        <div class="neo-card stats-card border-red-700 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
              <AlertCircle class="w-6 h-6 text-red-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm text-white">Disapproved</h3>
              <p class="text-2xl font-semibold text-white">{{ totalDisapprovedLeaves }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- My Applications & Upcoming Leaves -->
      <div class="grid lg:grid-cols-2 gap-6">
        <div class="neo-card p-6">
          <h3 class="text-lg font-semibold mb-4 text-white">My Leave Applications</h3>
          <div v-if="myApplications.length" class="space-y-3">
            <div v-for="app in myApplications" :key="app.id" class="neo-card-50 p-4 rounded-lg">
              <div class="neo-card flex justify-between items-start mb-2">
                <div>
                  <p class="font-medium text-white">{{ app.leave_type }}</p>
                  <p class="text-sm text-gray-600">{{ app.days }} days</p>
                </div>
                <span
                  :class="[
                    'px-3 py-1 rounded text-sm font-semibold',
                    app.status === 'approved'
                      ? 'bg-green-200 text-green-800'
                      : app.status === 'pending'
                      ? 'bg-yellow-200 text-yellow-800'
                      : 'bg-red-200 text-red-800',
                  ]"
                >
                  {{ app.status }}
                </span>
              </div>
              <p class="text-xs text-gray-500">{{ app.start_date }} to {{ app.end_date }}</p>
            </div>
          </div>
          <p v-else class="text-white">No leave applications yet</p>
        </div>

        <div class="neo-card p-6">
          <h3 class="text-lg font-semibold mb-4 text-white">Upcoming Leaves</h3>
          <div v-if="upcomingLeaves.length" class="space-y-3">
            <div
              v-for="leave in upcomingLeaves"
              :key="leave.id"
              class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500"
            >
              <p class="font-medium text-gray-800">{{ leave.leave_type }}</p>
              <p class="text-sm text-gray-600 mt-1">{{ leave.start_date }} to {{ leave.end_date }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ leave.days }} days</p>
            </div>
          </div>
          <p v-else class="text-white">No upcoming approved leaves</p>
        </div>
      </div>

      <!-- Status Chart & Leave by Type -->
      <div class="grid lg:grid-cols-2 gap-6">
        <div class="neo-card p-6">
          <h3 class="text-lg font-bold text-white mb-4">Leave Status Overview</h3>
          <div class="space-y-4">
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm text-white">Approved</span>
                <span class="text-sm font-semibold text-white">{{ employeeStatusChart.approved }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div
                  class="bg-green-500 h-3 rounded-full"
                  :style="{ width: calculateEmployeePercentage(employeeStatusChart.approved) + '%' }"
                ></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm text-white">Pending</span>
                <span class="text-sm font-semibold text-white">{{ employeeStatusChart.pending }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div
                  class="bg-yellow-500 h-3 rounded-full"
                  :style="{ width: calculateEmployeePercentage(employeeStatusChart.pending) + '%' }"
                ></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm text-white">Disapproved</span>
                <span class="text-sm font-semibold text-white">{{ employeeStatusChart.disapproved }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div
                  class="bg-red-500 h-3 rounded-full"
                  :style="{ width: calculateEmployeePercentage(employeeStatusChart.disapproved) + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <div class="neo-card p-6">
          <h3 class="font-semibold mb-4 text-white">Leave Applications by Type</h3>
          <div class="space-y-3">
            <div
              v-for="leave in employeeLeaveByType"
              :key="leave.name"
              class="neo-card p-4 rounded-lg"
            >
              <div class="flex justify-between items-center">
                <p class="text-sm font-medium text-white">{{ leave.name }}</p>
                <p class="text-2xl font-bold text-blue-500">{{ leave.count }}</p>
              </div>
            </div>
            <div v-if="employeeLeaveByType.length === 0" class="text-center text-gray-400 py-4">
              No leave applications yet
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="neo-card p-6">
        <h3 class="text-lg font-semibold mb-4 text-white">Recent Activities</h3>
        <div v-if="employeeActivities.length" class="space-y-3">
          <div
            v-for="activity in employeeActivities"
            :key="activity.id"
            class="border-l-4 border-blue-400 pl-4 py-2"
          >
            <p class="text-sm text-white">{{ activity.message }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ activity.time }}</p>
          </div>
        </div>
        <p v-else class="text-white">No recent activities</p>
      </div>

      <!-- Quick Actions -->
      <div class="neo-card p-6">
        <h3 class="text-lg font-semibold mb-4 text-white">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <button @click="goToApply" class="p-4 bg-blue-400 rounded-lg hover:bg-blue-300 text-black font-extrabold transition">
            Apply for Leave
          </button>
          <button @click="goToMyApplications" class="p-4 bg-green-400 rounded-lg hover:bg-green-300 text-black font-extrabold transition">
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

// ===============================
// COMMON DATA
// ===============================
const router = useRouter();
const loading = ref(true);
const loadError = ref<string | null>(null);
const userRole = ref<string>("employee");

// ===============================
// ADMIN DASHBOARD DATA
// ===============================
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

// ===============================
// EMPLOYEE DASHBOARD DATA
// ===============================
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

// ===============================
// COMPUTED PROPERTIES
// ===============================
const isAdmin = computed(() => userRole.value === "admin");
const isEmployee = computed(() => userRole.value === "employee");

// ===============================
// HELPERS
// ===============================
const extractErrorMessage = (error: unknown, fallback: string): string => {
  if (isAxiosError(error)) {
    return error.response?.data?.error || error.response?.data?.message || fallback;
  }
  return fallback;
};

const calculatePercentage = (value: number) => {
  const total =
    statusChartData.value.approved +
    statusChartData.value.pending +
    statusChartData.value.disapproved;

  if (total === 0) return 0;
  return Math.round((value / total) * 100);
};

const calculateEmployeePercentage = (value: number) => {
  const total =
    employeeStatusChart.value.approved +
    employeeStatusChart.value.pending +
    employeeStatusChart.value.disapproved;

  if (total === 0) return 0;
  return Math.round((value / total) * 100);
};

// ===============================
// FETCH CURRENT USER
// ===============================
const getCurrentUser = async () => {
  const response = await api.get("/me");
  userRole.value = response.data.role || "employee";
};

// ===============================
// FETCH ADMIN DASHBOARD
// ===============================
const loadAdminDashboard = async () => {
  const response = await getAdminDashboard();
  const data = response.data;

  if (!data || !data.summary) {
    throw new Error("Unexpected response shape from /admin/dashboard");
  }

  totalEmployees.value = data.summary.totalEmployees || 0;
  pendingLeaves.value = data.summary.pendingLeaves || 0;
  approvedLeaves.value = data.summary.approvedLeaves || 0;
  disapprovedLeaves.value = data.summary.disapprovedLeaves || 0;
  recentApplications.value = data.recentApplications || [];
  leaveByType.value = data.leaveByType || [];
  leaveByDepartment.value = data.leaveByDepartment || [];
  statusChartData.value = data.statusChart || { approved: 0, pending: 0, disapproved: 0 };
  pendingRequests.value = data.pendingRequests || [];
  recentActivities.value = data.recentActivities || [];
};

// ===============================
// FETCH EMPLOYEE DASHBOARD
// ===============================
const loadEmployeeDashboard = async () => {
  const response = await getEmployeeDashboard();
  const data = response.data;

  if (!data || !data.summary) {
    throw new Error("Unexpected response shape from /employee/dashboard");
  }

  totalEmployeeApplications.value = data.summary.totalApplications || 0;
  totalPendingLeaves.value = data.summary.pendingLeaves || 0;
  totalApprovedLeaves.value = data.summary.approvedLeaves || 0;
  totalDisapprovedLeaves.value = data.summary.disapprovedLeaves || 0;

  employeeInfo.value = data.employee || { name: "", email: "", department: "", position: "" };
  myApplications.value = data.myApplications || [];
  upcomingLeaves.value = data.upcomingLeaves || [];
  employeeStatusChart.value = data.statusChart || { approved: 0, pending: 0, disapproved: 0 };
  employeeLeaveByType.value = data.leaveByType || [];
  employeeActivities.value = data.recentActivities || [];
};

// ===============================
// NAVIGATION FUNCTIONS
// ===============================
const goToEmployees = () => router.push("/employees");
const goToLeaveRequests = () => router.push("/admin-applications");
const goToReports = () => router.push("/reports");
const goToApply = () => router.push("/leave-application");
const goToMyApplications = () => router.push("/my-applications");

// ===============================
// INITIAL LOAD (+ retry)
// ===============================
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
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.neo-card:hover {
  box-shadow: 0 14px 26px rgba(15, 23, 42, 0.06);
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
</style>