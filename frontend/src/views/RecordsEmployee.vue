<template>
  <div class="records-shell p-8 min-h-screen space-y-6">
   

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

    <!-- employee stats -->

<div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">

    <!-- Total -->
    <div class="neo-card stats-card border-blue-500 p-3 md:p-6 aspect-square md:aspect-auto flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left">

      <div class="p-2 md:p-3 bg-blue-100 rounded-lg">
        <FileCheck class="w-5 h-5 md:w-6 md:h-6 text-blue-600" />
      </div>

      <div class="mt-2 md:mt-0 md:ml-5">

        <h3 class="text-xs md:text-sm text-white">
          Total Applications
        </h3>

        <p class="text-lg md:text-2xl font-semibold text-white">
          {{ totalEmployeeApplications }}
        </p>

      </div>

    </div>

    <!-- Pending -->
    <div class="neo-card stats-card border-orange-500 p-3 md:p-6 aspect-square md:aspect-auto flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left">

      <div class="p-2 md:p-3 bg-yellow-100 rounded-lg">
        <Clock class="w-5 h-5 md:w-6 md:h-6 text-yellow-600" />
      </div>

      <div class="mt-2 md:mt-0 md:ml-4">

        <h3 class="text-xs md:text-sm text-white">
          Pending
        </h3>

        <p class="text-lg md:text-2xl font-semibold text-white">
          {{ totalPendingLeaves }}
        </p>

      </div>

    </div>

    <!-- Approved -->
    <div class="neo-card stats-card border-green-500 p-3 md:p-6 aspect-square md:aspect-auto flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left">

      <div class="p-2 md:p-3 bg-green-100 rounded-lg">
        <CheckCircle class="w-5 h-5 md:w-6 md:h-6 text-green-600" />
      </div>

      <div class="mt-2 md:mt-0 md:ml-4">

        <h3 class="text-xs md:text-sm text-white">
          Approved
        </h3>

        <p class="text-lg md:text-2xl font-semibold text-white">
          {{ totalApprovedLeaves }}
        </p>

      </div>

    </div>

    <!-- Disapproved -->
    <div class="neo-card stats-card border-red-700 p-3 md:p-6 aspect-square md:aspect-auto flex flex-col md:flex-row items-center justify-center md:justify-start text-center md:text-left">

      <div class="p-2 md:p-3 bg-red-100 rounded-lg">
        <AlertCircle class="w-5 h-5 md:w-6 md:h-6 text-red-600" />
      </div>

      <div class="mt-2 md:mt-0 md:ml-4">

        <h3 class="text-xs md:text-sm text-white">
          Disapproved
        </h3>

        <p class="text-lg md:text-2xl font-semibold text-white">
          {{ totalDisapprovedLeaves }}
        </p>

      </div>

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
  </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import {
  FileCheck,
  Clock,
  CheckCircle,
  AlertCircle,
} from "lucide-vue-next";

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

// employee stats — counts across ALL leave applications, not just the
// 5 shown in the "Recent Leave Applications" table
const totalEmployeeApplications = ref(0);
const totalPendingLeaves = ref(0);
const totalApprovedLeaves = ref(0);
const totalDisapprovedLeaves = ref(0);

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

const normalizeStatus = (status: unknown): string => {
  if (status === null || status === undefined) {
    return "";
  }

  return String(status).trim().toLowerCase();
};

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

  const applications: LeaveApplication[] = Array.isArray(response.data)
    ? response.data
    : [];

  const sorted = [...applications].sort(
    (a: LeaveApplication, b: LeaveApplication) => {
      return (
        new Date(b.date_filed).getTime() - new Date(a.date_filed).getTime()
      );
    },
  );

  recentLeaves.value = sorted.slice(0, 5);

  // employee stats — derived from the full list, before slicing
  totalEmployeeApplications.value = applications.length;

  totalApprovedLeaves.value = applications.filter(
    (app) => normalizeStatus(app.final_status) === "approved",
  ).length;

  totalPendingLeaves.value = applications.filter((app) => {
    const status = normalizeStatus(app.final_status);
    return (
      status === "pending" ||
      status === "for approval" ||
      status === "for_approval"
    );
  }).length;

  totalDisapprovedLeaves.value = applications.filter((app) => {
    const status = normalizeStatus(app.final_status);
    return (
      status === "disapproved" ||
      status === "rejected" ||
      status === "denied"
    );
  }).length;
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
  switch (normalizeStatus(status)) {
    case "approved":
      return "bg-green-500/10 text-green-400";

    case "pending":
    case "for approval":
    case "for_approval":
      return "bg-yellow-500/10 text-yellow-400";

    case "disapproved":
    case "rejected":
    case "denied":
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