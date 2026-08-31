<template>
  <div class="records-shell p-8 min-h-screen space-y-6">
    <!-- Header -->
    <div class="neo-card p-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-semibold text-white">Profile</h2>

          <p class="text-sm text-gray-400 mt-1">
            View your personal and employment information.
          </p>
        </div>
      </div>
    </div>

    <!-- Profile summary -->
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
            {{ formattedDateHired }}
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

const formattedDateHired = computed(() => {
  if (!employee.value.date_hired) return "-";

  const date = new Date(employee.value.date_hired);
  if (isNaN(date.getTime())) return employee.value.date_hired;

  const month = String(date.getUTCMonth() + 1).padStart(2, "0");
  const day = String(date.getUTCDate()).padStart(2, "0");
  const year = date.getUTCFullYear();

  return `${month}-${day}-${year}`;
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

onMounted(async () => {
  await loadProfile();
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

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {
  letter-spacing: -0.01em;
}
</style>