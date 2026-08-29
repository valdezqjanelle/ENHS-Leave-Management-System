<template>
  <div class="records-shell p-8 min-h-screen space-y-6">
    <!-- Header -->
    <div class="neo-card p-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-semibold text-white">My Records</h2>

          <p class="text-sm text-gray-400 mt-1">
            View your employee information and leave balances.
          </p>
        </div>
      </div>
    </div>

    <!-- Profile -->
    <div class="neo-card p-6">
      <div class="flex items-center gap-5">
        <div
          class="w-20 h-20 rounded-full bg-blue-500 text-white flex items-center justify-center text-3xl font-bold"
        >
          {{ initials }}
        </div>

        <div>
          <h3 class="text-xl font-semibold text-white">
            {{ fullName }}
          </h3>

          <p class="text-gray-400">
            {{ employee.position }}
          </p>

          <span
            class="inline-block mt-2 px-3 py-1 rounded-full text-sm bg-green-500/10 text-green-400"
          >
            {{ employee.employment_status }}
          </span>
        </div>
      </div>
    </div>

    <!-- Leave Balance -->

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
       <div class="neo-card stats-card border-blue-500 p-5">
        <h4 class="text-gray-400 text-sm">Service Credits</h4>

        <p class="text-3xl font-bold text-blue-400 mt-2">
          {{ leaveBalance.service_credits }}
        </p>
      </div>
      <div class="neo-card stats-card border-blue-500 p-5">
        <h4 class="text-gray-400 text-sm">Vacation Leave</h4>

        <p class="text-3xl font-bold text-blue-400 mt-2">
          {{ leaveBalance.vacation_balance }}
        </p>
      </div>

      <div class="neo-card stats-card border-red-700 p-5">
        <h4 class="text-gray-400 text-sm">Sick Leave</h4>

        <p class="text-3xl font-bold text-red-400 mt-2">
          {{ leaveBalance.sick_balance }}
        </p>
      </div>

      <div class="neo-card stats-card border-green-500 p-5">
        <h4 class="text-gray-400 text-sm">Used Leave</h4>

        <p class="text-3xl font-bold text-green-400 mt-2">
          {{ leaveBalance.used_leave }}
        </p>
      </div>
    </div>

    <!-- Recent Leave Applications -->

    <div class="neo-card">
      <div class="px-6 py-4 border-b border-[#1e293b]">
        <h3 class="text-lg font-semibold text-white">
          Recent Leave Applications
        </h3>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-[#1e293b]">
          <thead class="bg-[#0d1520]">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase"
              >
                Leave Type
              </th>

              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase"
              >
                Date Filed
              </th>

              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase"
              >
                Days
              </th>

              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase"
              >
                Status
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-[#1e293b]">
            <tr v-for="leave in recentLeaves" :key="leave.leave_id">
              <td class="px-6 py-4 text-white font-medium">
                {{ leave.leave_type?.leave_type_name || "-" }}
              </td>
              <td class="px-6 py-4 text-white">
                {{ formatDate(leave.date_filed) }}
              </td>

              <td class="px-6 py-4 text-white">
                {{ leave.number_of_days }}
              </td>

              <td class="px-6 py-4 text-white">
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

    <div class="neo-card">
      <div class="border-b border-[#1e293b] px-6 py-4">
        <h3 class="text-lg font-semibold text-white">Personal Information</h3>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
        <div>
          <label class="text-gray-400 text-sm"> Employee Code </label>

          <p class="font-medium text-white">
            {{ employee.employee_code }}
          </p>
        </div>

        <div>
          <label class="text-gray-400 text-sm"> Email </label>

          <p class="font-medium text-white">
            {{ employee.email }}
          </p>
        </div>

        <div>
          <label class="text-gray-400 text-sm"> First Name </label>

          <p class="font-medium text-white">
            {{ employee.first_name }}
          </p>
        </div>

        <div>
          <label class="text-gray-400 text-sm"> Middle Name </label>

          <p class="font-medium text-white">
            {{ employee.middle_name || "-" }}
          </p>
        </div>

        <div>
          <label class="text-gray-400 text-sm"> Last Name </label>

          <p class="font-medium text-white">
            {{ employee.last_name }}
          </p>
        </div>

        <div>
          <label class="text-gray-400 text-sm"> Sex </label>

          <p class="font-medium text-white">
            {{ employee.sex }}
          </p>
        </div>

        <div>
          <label class="text-gray-400 text-sm"> Contact Number </label>

          <p class="font-medium text-white">
            {{ employee.contact_number }}
          </p>
        </div>
      </div>
    </div>

    <!-- Employment -->

    <div class="neo-card">
      <div class="border-b border-[#1e293b] px-6 py-4">
        <h3 class="text-lg font-semibold text-white">Employment Information</h3>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
        <div>
          <label class="text-gray-400 text-sm"> Department </label>

          <p class="font-medium text-white">
            {{ employee.department_name }}
          </p>
        </div>

        <div>
          <label class="text-gray-400 text-sm"> Position </label>

          <p class="font-medium text-white">
            {{ employee.position }}
          </p>
        </div>

        <div>
          <label class="text-gray-400 text-sm"> Level </label>

          <p class="font-medium text-white">
            {{ employee.level }}
          </p>
        </div>

        <div>
          <label class="text-gray-400 text-sm"> Salary </label>

          <p class="font-medium text-white">₱{{ employee.salary }}</p>
        </div>

        <div>
          <label class="text-gray-400 text-sm"> Date Hired </label>

          <p class="font-medium text-white">
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
  department_id: null,
  department_name: "",
  position: "",
  level: "",

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
  service_credits: 0,
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

  const response = await axios.get("https://enhs-leave-management-system.onrender.com/api/my-profile", {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });

  employee.value = response.data;
};

const loadRecentLeaves = async () => {
  const token = localStorage.getItem("token");

  const response = await axios.get(
    "https://enhs-leave-management-system.onrender.com/api/my-leave-applications",
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
    "https://enhs-leave-management-system.onrender.com/api/my-leave-balance",
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
      return "bg-green-500/10 text-green-400";

    case "pending":
      return "bg-yellow-500/10 text-yellow-400";

    case "disapproved":
      return "bg-red-500/10 text-red-400";

    default:
      return "bg-gray-500/10 text-gray-400";
  }
};

onMounted(async () => {
  await loadProfile();

  await loadLeaveBalance();

  await loadRecentLeaves();
});
</script>

<style scoped>
.records-shell {
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