<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-semibold text-gray-800">My Records</h2>

          <p class="text-sm text-gray-500 mt-1">
            View your employee information and leave balances.
          </p>
        </div>
      </div>
    </div>

    <!-- Profile -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center gap-5">
        <div
          class="w-20 h-20 rounded-full bg-blue-600 text-white flex items-center justify-center text-3xl font-bold"
        >
          {{ initials }}
        </div>

        <div>
          <h3 class="text-xl font-semibold text-gray-800">
            {{ fullName }}
          </h3>

          <p class="text-gray-500">
            {{ employee.position }}
          </p>

          <span
            class="inline-block mt-2 px-3 py-1 rounded-full text-sm bg-green-100 text-green-700"
          >
            {{ employee.employment_status }}
          </span>
        </div>
      </div>
    </div>

    <!-- Leave Balance -->

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-lg shadow p-5">
        <h4 class="text-gray-500 text-sm">Vacation Leave</h4>

        <p class="text-3xl font-bold text-blue-600 mt-2">
          {{ leaveBalance.vacation_balance }}
        </p>
      </div>

      <div class="bg-white rounded-lg shadow p-5">
        <h4 class="text-gray-500 text-sm">Sick Leave</h4>

        <p class="text-3xl font-bold text-red-600 mt-2">
          {{ leaveBalance.sick_balance }}
        </p>
      </div>

      <div class="bg-white rounded-lg shadow p-5">
        <h4 class="text-gray-500 text-sm">Used Leave</h4>

        <p class="text-3xl font-bold text-green-600 mt-2">
          {{ leaveBalance.used_leave }}
        </p>
      </div>
    </div>

    <!-- Recent Leave Applications -->

    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-800">
          Recent Leave Applications
        </h3>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
              >
                Leave Type
              </th>

              <th
                class="px-6 py-3 text-left text-xs font-medium text-black uppercase"
              >
                Date Filed
              </th>

              <th
                class="px-6 py-3 text-left text-xs font-medium text-black uppercase"
              >
                Days
              </th>

              <th
                class="px-6 py-3 text-left text-xs font-medium text-black uppercase"
              >
                Status
              </th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="leave in recentLeaves" :key="leave.leave_id">
              <td class="px-6 py-4 text-black font-medium">
                {{ leave.leave_type?.leave_type_name || "-" }}
              </td>
              <td class="px-6 py-4 text-black">
                {{ formatDate(leave.date_filed) }}
              </td>

              <td class="px-6 py-4 text-black">
                {{ leave.number_of_days }}
              </td>

              <td class="px-6 py-4 text-black">
                <span
                  class="px-3 py-1 rounded-full text-xs font-semibold"
                  :class="statusClass(leave.final_status)"
                >
                  {{ leave.final_status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Personal Information -->

    <div class="bg-white rounded-lg shadow">
      <div class="border-b px-6 py-4">
        <h3 class="text-lg font-semibold text-black">Personal Information</h3>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
        <div>
          <label class="text-gray-500 text-sm"> Employee Code </label>

          <p class="font-medium text-black">
            {{ employee.employee_code }}
          </p>
        </div>

        <div>
          <label class="text-gray-500 text-sm"> Email </label>

          <p class="font-medium text-black">
            {{ employee.email }}
          </p>
        </div>

        <div>
          <label class="text-gray-500 text-sm"> First Name </label>

          <p class="font-medium text-black">
            {{ employee.first_name }}
          </p>
        </div>

        <div>
          <label class="text-gray-500 text-sm"> Middle Name </label>

          <p class="font-medium text-black">
            {{ employee.middle_name || "-" }}
          </p>
        </div>

        <div>
          <label class="text-gray-500 text-sm"> Last Name </label>

          <p class="font-medium text-black">
            {{ employee.last_name }}
          </p>
        </div>

        <div>
          <label class="text-gray-500 text-sm"> Sex </label>

          <p class="font-medium text-black">
            {{ employee.sex }}
          </p>
        </div>

        <div>
          <label class="text-gray-500 text-sm"> Contact Number </label>

          <p class="font-medium text-black">
            {{ employee.contact_number }}
          </p>
        </div>
      </div>
    </div>

    <!-- Employment -->

    <div class="bg-white rounded-lg shadow">
      <div class="border-b px-6 py-4">
        <h3 class="text-lg font-semibold text-black">Employment Information</h3>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
        <div>
          <label class="text-gray-500 text-sm"> Department </label>

          <p class="font-medium text-black">
            {{ employee.department }}
          </p>
        </div>

        <div>
          <label class="text-gray-500 text-sm"> Position </label>

          <p class="font-medium text-black">
            {{ employee.position }}
          </p>
        </div>

        <div>
          <label class="text-gray-900 text-sm"> Employee Category </label>

          <p class="font-medium text-black">
            {{ employee.employee_category }}
          </p>
        </div>

        <div>
          <label class="text-gray-500 text-sm"> Salary </label>

          <p class="font-medium text-black">₱{{ employee.salary }}</p>
        </div>

        <div>
          <label class="text-gray-500 text-sm"> Date Hired </label>

          <p class="font-medium text-black">
            {{ employee.date_hired }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const employee = ref({
  employee_id: "",
  employee_code: "",

  first_name: "",
  middle_name: "",
  last_name: "",

  sex: "",
  department: "",
  position: "",
  employee_category: "",

  salary: "",
  contact_number: "",
  employment_status: "",
  date_hired: "",

  email: "",
  role: "",
});

interface LeaveApplication {
  leave_id: number;
  date_filed: string;
  number_of_days: number;
  final_status: string;

  leave_type: {
    leave_type_name: string;
  };
}

const recentLeaves = ref<LeaveApplication[]>([]);
const leaveBalance = ref({
  vacation_balance: 0,
  sick_balance: 0,
  used_leave: 0,
  last_updated: "",
});

const fullName = computed(() => {
  return [
    employee.value.first_name,
    employee.value.middle_name,
    employee.value.last_name,
  ]
    .filter(Boolean)
    .join(" ");
});

const initials = computed(() => {
  return (
    employee.value.first_name.charAt(0) + employee.value.last_name.charAt(0)
  ).toUpperCase();
});

const loadProfile = async () => {
  const token = localStorage.getItem("token");

  const response = await axios.get("http://127.0.0.1:8000/api/my-profile", {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  employee.value = response.data;
};

const loadRecentLeaves = async () => {
  const token = localStorage.getItem("token");

  const response = await axios.get(
    "http://127.0.0.1:8000/api/my-leave-applications",
    {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    },
  );

  recentLeaves.value = response.data
    .sort((a: LeaveApplication, b: LeaveApplication) => {
      return (
        new Date(b.date_filed).getTime() - new Date(a.date_filed).getTime()
      );
    })
    .slice(0, 5);
};

const loadLeaveBalance = async () => {
  const token = localStorage.getItem("token");

  const response = await axios.get(
    "http://127.0.0.1:8000/api/my-leave-balance",
    {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    },
  );

  leaveBalance.value = response.data;
};

const formatDate = (dateString: string) => {
    if (!dateString) return '-'

    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const statusClass = (status: string) => {
  switch (status) {
    case "approved":
      return "bg-green-100 text-green-700";

    case "pending":
      return "bg-yellow-100 text-yellow-700";

    case "disapproved":
      return "bg-red-100 text-red-700";

    default:
      return "bg-gray-100 text-gray-700";
  }
};

onMounted(async () => {
  await loadProfile();

  await loadLeaveBalance();

  await loadRecentLeaves();
});
</script>
