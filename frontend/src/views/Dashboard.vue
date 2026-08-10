<template>
  <div class="p-8 bg-gray-50 min-h-screen space-y-8">
    <div v-if="loading" class="text-center py-10 text-gray-500">
      Loading dashboard...
    </div>

    <!-- Dashboard -->
    <div v-else>
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Employees -->
        <div
          class="bg-white rounded-2xl shadow-md p-6 border border-blue-500 hover:shadow-lg transition"
        >
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
              <Users class="w-6 h-6 text-blue-600" />
            </div>

            <div class="ml-4">
              <h3 class="text-sm text-gray-700">Total Employees</h3>

              <p class="text-2xl font-semibold text-black">
                {{ totalEmployees }}
              </p>
            </div>
          </div>
        </div>

        <!-- Pending -->
        <div
          class="bg-white rounded-2xl shadow-md p-6 border border-orange-500 hover:shadow-lg transition"
        >
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg">
              <Clock class="w-6 h-6 text-yellow-600" />
            </div>

            <div class="ml-4">
              <h3 class="text-sm text-gray-700">Pending Leaves</h3>

              <p class="text-2xl font-semibold text-black">
                {{ pendingLeaves }}
              </p>
            </div>
          </div>
        </div>

        <!-- Approved -->
        <div
          class="bg-white rounded-2xl shadow-md p-6 border border-green-500 hover:shadow-lg transition"
        >
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircle class="w-6 h-6 text-green-600" />
            </div>

            <div class="ml-4">
              <h3 class="text-sm text-gray-700">Approved Leaves</h3>

              <p class="text-2xl font-semibold text-black">
                {{ approvedLeaves }}
              </p>
            </div>
          </div>
        </div>

        <!-- Disapproved -->
        <div
          class="bg-white rounded-2xl shadow-md p-6 border border-red-700 hover:shadow-lg transition"
        >
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
              <AlertCircle class="w-6 h-6 text-red-600" />
            </div>

            <div class="ml-4">
              <h3 class="text-sm text-gray-700">Disapproved Leaves</h3>

              <p class="text-2xl font-semibold text-black">
                {{ disapprovedLeaves }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Applications -->

      <div
        class="bg-white rounded-2xl shadow-md p-6 border border-violet-900 hover:shadow-lg transition"
      >
        <h3 class="text-lg font-semibold mb-4 text-gray-700">
          Recent Leave Applications
        </h3>

        <div v-if="recentApplications.length" class="space-y-3">
          <div
            v-for="app in recentApplications"
            :key="app.id"
            class="flex justify-between items-center bg-gray-50 p-4 rounded-lg"
          >
            <div class="flex items-center">
              <div
                class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center mr-4"
              >
                {{ app.employee?.charAt(0) }}
              </div>

              <div>
                <p class="font-medium text-black">
                  {{ app.employee }}
                </p>

                <p class="text-sm text-gray-700">
                  {{ app.leave_type }}
                </p>
              </div>
            </div>

            <div class="text-right">
              <p class="text-sm text-gray-900">
                {{ app.status }}
              </p>

              <p class="text-xs text-gray-700">
                {{ app.date }}
              </p>
            </div>
          </div>
        </div>

        <p v-else class="text-gray-600">No recent applications</p>
      </div>

      <!-- Leave Requests Needing Action -->

      <div
        class="bg-white rounded-2xl shadow-md p-6 border border-red-900 hover:shadow-lg transition"
      >
        <h3 class="text-lg font-semibold mb-4 text-gray-700">
          Leave Requests Needing Action
        </h3>

        <div v-if="pendingRequests.length" class="space-y-3">
          <div
            v-for="request in pendingRequests"
            :key="request.id"
            class="flex justify-between items-center bg-gray-50 p-4 rounded-lg"
          >
            <div>
              <p class="font-medium">
                {{ request.employee }}
              </p>

              <p class="text-sm text-gray-500">
                {{ request.leave_type }}
                • {{ request.days }} days
              </p>
            </div>

            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">
              Review
            </button>
          </div>
        </div>

        <p v-else class="text-gray-600">No pending requests</p>
      </div>

      <!-- Department -->

      <div
        class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition"
      >
        <h3 class="font-semibold mb-4 text-gray-700">Leave By Department</h3>

        <div
          v-for="dept in leaveByDepartment"
          :key="dept.department"
          class="flex justify-between py-2 text-black"
        >
          <div
            v-for="dept in leaveByDepartment"
            :key="dept.department"
            class="flex justify-between items-center bg-gray-50 rounded-xl p-4"
          >
            <div>
              <p class="font-semibold text-gray-800">
                {{ dept.department }}
              </p>

              <p class="text-sm text-gray-500">Leave Requests</p>
            </div>

            <div class="text-2xl font-bold text-blue-600">
              {{ dept.count }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Leave Application Status Chart + Requests Needing Action -->

    <div class="grid lg:grid-cols-2 gap-6">
      <!-- Leave Application Status Chart -->

      <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800">
          Leave Application Status
        </h3>

        <p class="text-sm text-gray-500 mb-6">
          Overview of submitted leave requests
        </p>

        <div class="space-y-4">
          <!-- Approved -->

          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm text-gray-600"> Approved </span>

              <span class="text-sm font-semibold text-black">
                {{ statusChartData.approved }}
              </span>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-3">
              <div
                class="bg-green-500 h-3 rounded-full transition-all duration-500"
                :style="{
                  width: calculatePercentage(statusChartData.approved) + '%',
                }"
              ></div>
            </div>
          </div>

          <!-- Pending -->

          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm text-gray-600"> Pending </span>

              <span class="text-sm font-semibold text-black">
                {{ statusChartData.pending }}
              </span>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-3">
              <div
                class="bg-yellow-500 h-3 rounded-full"
                :style="{
                  width: calculatePercentage(statusChartData.pending) + '%',
                }"
              ></div>
            </div>
          </div>

          <!-- Disapproved -->

          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm text-gray-600"> Disapproved </span>

              <span class="text-sm font-semibold text-black">
                {{ statusChartData.disapproved }}
              </span>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-3">
              <div
                class="bg-red-500 h-3 rounded-full"
                :style="{
                  width: calculatePercentage(statusChartData.disapproved) + '%',
                }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Leave Requests Needing Action -->

      <div
        class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition"
      >
        <h3 class="font-semibold mb-4 text-gray-700">
          Leave Requests Needing Action
        </h3>

        <div v-if="pendingRequests.length" class="space-y-3">
          <div
            v-for="request in pendingRequests"
            :key="request.id"
            class="flex justify-between items-center bg-gray-50 p-4 rounded-lg"
          >
            <div>
              <p class="font-medium text-black">
                {{ request.employee }}
              </p>

              <p class="text-sm text-gray-500">
                {{ request.leave_type }}
              </p>
            </div>

            <button class="bg-blue-600 text-white px-3 py-1 rounded-lg text-sm">
              Review
            </button>
          </div>
        </div>

        <p v-else class="text-gray-600">No pending requests</p>
      </div>
    </div>

    <!-- Leave Types -->

    <div
      class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition"
    >
      <h3 class="font-semibold mb-4 text-gray-700">Leave By Type</h3>

      <div class="grid md:grid-cols-3 gap-4">
        <div
          v-for="leave in leaveByType"
          :key="leave.name"
          class="bg-gray-50 p-5 rounded-xl border"
        >
          <p class="text-xs text-gray-500 truncate">
            {{ leave.name }}
          </p>

          <p class="text-3xl font-bold text-blue-600 mt-2">
            {{ leave.count }}
          </p>
        </div>
      </div>
    </div>

    <!-- Recent Activities -->

    <div
      class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition"
    >
      <h3 class="text-lg font-semibold mb-4 text-gray-700">
        Recent Activities
      </h3>

      <div class="space-y-4">
        <div
          v-for="activity in recentActivities"
          :key="activity.id"
          class="flex items-start"
        >
          <div
            class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3"
          >
            ✓
          </div>

          <div>
            <p class="text-sm text-black">
              {{ activity.message }}
            </p>

            <p class="text-xs text-gray-700">
              {{ activity.time }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->

    <div
      class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition"
    >
      <h3 class="text-lg font-semibold mb-4 text-gray-700">Quick Actions</h3>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button
        @click="goToEmployees"
          class="p-4 bg-blue-400 rounded-lg hover:bg-blue-300 text-white font-extrabold"
        >
          Create Employee Account
        </button>

        <button
        @click="goToLeaveRequests"
          class="p-4 bg-green-400 rounded-lg hover:bg-green-100 text-white font-extrabold"
        >
          Review Leave Requests
        </button>

        <button
        @click="goToReports"
          class="p-4 bg-purple-400 rounded-lg hover:bg-purple-100 text-white font-extrabold"
        >
          Generate Reports
        </button>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted } from "vue";
import { getAdminDashboard } from "@/services/dashboard";
import { useRouter } from "vue-router";
import {
Users,
Clock,
CheckCircle,
AlertCircle,
FileCheck
}
from "lucide-vue-next";
// ===============================
// ADMIN DASHBOARD DATA
// ===============================
const router = useRouter();
const totalEmployees = ref(0);

const totalApplications = ref(0);

const pendingLeaves = ref(0);

const approvedLeaves = ref(0);

const disapprovedLeaves = ref(0);
const loading = ref(true);

// Recent Leave Applications

const recentApplications = ref<any[]>([]);
const pendingRequests = ref<any[]>([]);

const recentActivities = ref<any[]>([]);

// Leave Summary

const leaveByType = ref<any[]>([]);

const leaveByDepartment = ref<any[]>([]);

// Chart Data

const statusChartData = ref({
  approved: 0,
  pending: 0,
  disapproved: 0,
});

// ===============================
// FETCH ADMIN DASHBOARD
// ===============================
const calculatePercentage = (value: number) => {
  const total =
    statusChartData.value.approved +
    statusChartData.value.pending +
    statusChartData.value.disapproved;

  if (total === 0) return 0;

  return Math.round((value / total) * 100);
};

const loadAdminDashboard = async () => {
  try {
    const response = await getAdminDashboard();

    const data = response.data;

    console.log("Dashboard Data:", data);

    totalEmployees.value = data.summary.totalEmployees;

    pendingLeaves.value = data.summary.pendingLeaves;

    approvedLeaves.value = data.summary.approvedLeaves;

    disapprovedLeaves.value = data.summary.disapprovedLeaves;

    recentApplications.value = data.recentApplications;

    leaveByType.value = data.leaveByType;

    leaveByDepartment.value = data.leaveByDepartment;

    statusChartData.value = data.statusChart;

    pendingRequests.value = data.pendingRequests || [];

    recentActivities.value = data.recentActivities || [];
  } catch (error) {
    console.error("Failed loading dashboard:", error);
  } finally {
    loading.value = false;
  }
};

const goToEmployees = () => {
  router.push("/employees");
};


const goToLeaveRequests = () => {
  router.push("/admin-applications");
};


const goToReports = () => {
  router.push("/reports");
};

// ===============================
// INITIAL LOAD
// ===============================

onMounted(() => {
  loadAdminDashboard();
});
</script>
