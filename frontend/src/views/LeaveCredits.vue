<template>
  <div class="max-w-7xl mx-auto space-y-6 text-white">

    <!-- Header -->
    <div class="neo-card p-6">
      <h2 class="text-2xl font-bold text-white">
        Leave Credits
      </h2>

      <p class="text-white mt-1">
        Record earned leave credits for employees.
      </p>
    </div>

    <!-- Form -->
    <div class="neo-card p-6">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Employee -->
        <div>
          <label class="block text-sm font-medium  text-white  mb-2">
            Employee
          </label>

          <select
  v-model="form.employee_id"
  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-gray-900 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
>
            <option value="">
              Select Employee
            </option>

            <option
              v-for="employee in employees"
              :key="employee.employee_id"
              :value="employee.employee_id"
            >
              {{ employee.last_name }}, {{ employee.first_name }}
            </option>

          </select>
        </div>
<div>
  <label class="block text-sm font-medium text-white mb-2">
    Credit Type
  </label>

  <select
    v-model="form.credit_type"
    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-black bg-white"
  >
    <option value="">
      Select Credit Type
    </option>

    <option value="Vacation">
      Vacation Leave
    </option>

    <option value="Sick">
      Sick Leave
    </option>
  </select>
</div>

        <!-- Activity -->
        <div>
          <label class="block text-sm font-medium  text-white  mb-2">
            Activity Name
          </label>

         <input
  v-model="form.activity_name"
  type="text"
  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-black placeholder-gray-400 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
  placeholder="Example: Overtime Duty"
>
        </div>

        <!-- Hours -->
        <div>
          <label class="block text-sm font-medium  text-white  mb-2">
            Hours Rendered
          </label>

         <input
  v-model="form.hours_rendered"
  type="number"
  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-black bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
>
        </div>

        <!-- Leave Days -->
        <div>
          <label class="block text-sm font-medium  text-white  mb-2">
            Equivalent Leave Days
          </label>

        <input
  v-model="form.equivalent_leave_days"
  type="number"
  step="0.25"
  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-black bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
>
        </div>

      </div>

      <div class="mt-6">

        <button
          @click="saveCredit"
          class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg"
        >
          Save Credit
        </button>

      </div>

    </div>

    <!-- Table -->
    <div class="neo-card p-6">

      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-white">
          Leave Credit Records
        </h3>
      </div>

      <div class="overflow-x-auto">

        <table class="min-w-full">

    <thead class="bg-white text-black">

<tr>

<th class="px-4 py-3 text-left">Employee</th>

<th class="px-4 py-3 text-left">Credit Type</th>

<th class="px-4 py-3 text-left">Activity</th>

<th class="px-4 py-3 text-left">Hours</th>

<th class="px-4 py-3 text-left">Leave Days</th>

<th class="px-4 py-3 text-left">Status</th>

<th class="px-4 py-3 text-left">Date</th>

<th class="px-4 py-3 text-center">Action</th>

</tr>
</thead>

          <tbody>

            <tr
  v-for="credit in credits"
  :key="credit.credits_id"
  class="border-t hover:bg-gray-800 transition-colors duration-200 cursor-pointer"
>

<td class="px-4 py-3">
{{ credit.employee.last_name }},
{{ credit.employee.first_name }}
</td>

<td class="px-4 py-3">

<span
v-if="credit.credit_type=='vacation'"
class="text-blue-600 font-semibold"
>

Vacation

</span>

<span
v-else
class="text-green-600 font-semibold"
>

Sick

</span>

</td>

<td class="px-4 py-3">

{{ credit.activity_name }}

</td>

<td class="px-4 py-3">

{{ credit.hours_rendered }}

</td>

<td class="px-4 py-3">

{{ credit.equivalent_leave_days }}

</td>

<td class="px-4 py-3">

<span
v-if="credit.status=='Pending'"
class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs"
>

Pending

</span>

<span
v-else
class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs"
>

Applied

</span>

</td>

<td class="px-4 py-3">

{{ formatDate(credit.date_recorded) }}

</td>

<td class="px-4 py-3 text-center">

<button

v-if="credit.status=='Pending'"

@click="applyCredit(credit.credits_id)"

class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"

>

Apply Credit

</button>

<span
v-else
class="text-green-600 font-semibold"
>

✔ Applied

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
import { ref, onMounted } from "vue";

import {
  getEmployees,
  getLeaveCredits,
  addLeaveCredit,
  applyLeaveCredit
} from "@/services/leaveCredit";

interface Employee {
  employee_id: number;
  first_name: string;
  last_name: string;
}

interface LeaveCredit {
  credits_id: number;
  employee_id: number;
  credit_type: string;
  status: string;
  activity_name: string;
  hours_rendered: number;
  equivalent_leave_days: number;
  date_recorded: string;
  employee: Employee;
}

const employees = ref<Employee[]>([]);
const credits = ref<LeaveCredit[]>([]);

const form = ref({
  employee_id: "",
  activity_name: "",
  hours_rendered: "",
  equivalent_leave_days: "",
  credit_type: ""
});

const loadEmployees = async () => {
  try {
    employees.value = await getEmployees();
  } catch (error) {
    console.error("Failed to load employees.", error);
  }
};

const loadCredits = async () => {
  try {
    credits.value = await getLeaveCredits();
  } catch (error) {
    console.error("Failed to load leave credits.", error);
  }
};

const saveCredit = async () => {
  try {
    await addLeaveCredit(form.value);

    alert("Leave credit added successfully!");

    form.value = {
      employee_id: "",
      activity_name: "",
      hours_rendered: "",
      equivalent_leave_days: "",
      credit_type: ""
    };

    await loadCredits();

  } catch (error) {
    console.error(error);
    alert("Unable to save leave credit.");
  }
};

const applyCredit = async (id: number) => {
  try {

    await applyLeaveCredit(id);

    alert("Leave credit applied successfully!");

    await loadCredits();

  } catch (error) {

    console.error(error);

    alert("Unable to apply leave credit.");

  }
};


const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric"
  });
};

onMounted(() => {
  loadEmployees();
  loadCredits();
});
</script>

<style scoped>
.dashboard-shell {
  background: #080D14;
}

.neo-card {
  background: #111D2E;
  border: 1px solid #1E293B;
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
