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
