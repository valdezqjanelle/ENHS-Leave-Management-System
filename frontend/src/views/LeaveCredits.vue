<template>
  <div class="dashboard-shell p-8 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">

      <!-- Header -->
      <div class="neo-card p-6">
        <h2 class="text-2xl font-bold text-white">
          Leave Credits
        </h2>

        <p class="text-gray-400 mt-1">
          Record earned leave credits for employees.
        </p>
      </div>

      <!-- Form -->
      <div class="neo-card p-6">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- Employee -->
          <div>
            <label class="block text-sm font-medium text-white mb-2">
              Employee
            </label>

            <select
              v-model="form.employee_id"
              class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
              class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">
                Select Credit Type
              </option>

              <option value="Service Credits">
                Service Credits
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
            <label class="block text-sm font-medium text-white mb-2">
              Activity Name
            </label>

            <input
              v-model="form.activity_name"
              type="text"
              class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Example: Overtime Duty"
            >
          </div>

          <!-- Hours -->
          <div>
            <label class="block text-sm font-medium text-white mb-2">
              Hours Rendered
            </label>

            <input
              v-model="form.hours_rendered"
              type="number"
              class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>

          <!-- Leave Days -->
          <div>
            <label class="block text-sm font-medium text-white mb-2">
              Equivalent Leave Days
            </label>

            <input
              v-model="form.equivalent_leave_days"
              type="number"
              step="0.25"
              class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>

        </div>

        <div class="mt-6">

          <button
            @click="saveCredit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition"
          >
            Save Credit
          </button>

        </div>

      </div>

      <!-- Table -->
      <div class="neo-card">

        <div class="px-6 py-4 border-b border-slate-800">
          <h3 class="text-lg font-semibold text-white">
            Leave Credit Records
          </h3>
        </div>

        <div class="overflow-x-auto">

          <table class="min-w-full">

            <thead class="bg-[#0B1420] text-white">

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
                class="border-t border-slate-800 hover:bg-white/5 transition"
              >

                <td class="px-4 py-3 text-white">
                  {{ credit.employee.last_name }},
                  {{ credit.employee.first_name }}
                </td>

                <td class="px-4 py-3">
                  <span
                    v-if="credit.credit_type === 'Service Credits'"
                    class="text-purple-400 font-semibold"
                  >
                    Service Credits
                  </span>

                  <span
                    v-else-if="credit.credit_type === 'Vacation'"
                    class="text-blue-400 font-semibold"
                  >
                    Vacation
                  </span>

                  <span
                    v-else-if="credit.credit_type === 'Sick'"
                    class="text-green-400 font-semibold"
                  >
                    Sick
                  </span>

                  <span
                    v-else
                    class="text-gray-400 font-semibold"
                  >
                    {{ credit.credit_type }}
                  </span>
                </td>

                <td class="px-4 py-3 text-white">
                  {{ credit.activity_name }}
                </td>

                <td class="px-4 py-3 text-white">
                  {{ credit.hours_rendered }}
                </td>

                <td class="px-4 py-3 text-white">
                  {{ credit.equivalent_leave_days }}
                </td>

                <td class="px-4 py-3">
                  <span
                    v-if="credit.status=='Pending'"
                    class="bg-yellow-500/20 text-yellow-400 px-3 py-1 rounded-full text-xs"
                  >
                    Pending
                  </span>

                  <span
                    v-else
                    class="bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-xs"
                  >
                    Applied
                  </span>
                </td>

                <td class="px-4 py-3 text-white">
                  {{ formatDate(credit.date_recorded) }}
                </td>

                <td class="px-4 py-3 text-center">

                  <button
                    v-if="credit.status=='Pending'"
                    @click="applyCredit(credit.credits_id)"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition"
                  >
                    Apply Credit
                  </button>

                  <span
                    v-else
                    class="text-green-400 font-semibold"
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

    <!-- Apply Credit Modal -->
    <div
      v-if="showApplyModal"
      class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50"
    >
      <div class="neo-card w-full max-w-md p-6">

        <h2 class="text-xl font-semibold text-white">
          Apply Leave Credit
        </h2>

        <p class="text-gray-300 mt-3">
          Choose where this credit should be applied. This will update the
          employee's leave balance.
        </p>

        <!-- Credit Details -->
        <div
          v-if="selectedCredit"
          class="mt-4 bg-[#0B1420] border border-slate-800 rounded-lg p-4 space-y-2 text-sm text-white"
        >
          <div>
            <span class="font-medium text-gray-400">Employee:</span>
            {{ selectedCredit.employee.last_name }},
            {{ selectedCredit.employee.first_name }}
          </div>

          <div>
            <span class="font-medium text-gray-400">Credit Type:</span>
            {{ selectedCredit.credit_type }}
          </div>

          <div>
            <span class="font-medium text-gray-400">Activity:</span>
            {{ selectedCredit.activity_name }}
          </div>

          <div>
            <span class="font-medium text-gray-400">Equivalent Days:</span>
            {{ selectedCredit.equivalent_leave_days }}
          </div>

          <div>
            <span class="font-medium text-gray-400">Available Credit:</span>
            {{ availableCreditDays.toFixed(2) }} days
          </div>
        </div>

        <div v-if="selectedCredit" class="mt-4 space-y-4 text-sm text-white">
          <template v-if="selectedCredit.credit_type === 'Service Credits'">
            <div>
              <label class="block font-medium text-white mb-2">Apply To</label>
              <select
                v-model="applyForm.leave_type"
                @change="handleApplyTypeChange"
                :disabled="applyForm.split"
                class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-60"
              >
                <option value="Service Credits">Service Credits</option>
                <option value="Vacation">Vacation Leave</option>
                <option value="Sick">Sick Leave</option>
              </select>
            </div>

            <label
              v-if="applyForm.leave_type !== 'Service Credits'"
              class="flex items-center gap-2 text-white"
            >
              <input
                v-model="applyForm.split"
                type="checkbox"
                class="rounded border-slate-700 bg-[#0B1420] text-blue-600 focus:ring-blue-500"
              >
              Split Application
            </label>

            <div v-if="!applyForm.split">
              <label class="block font-medium text-white mb-2">Days to Apply</label>
              <input
                v-model.number="applyForm.days"
                type="number"
                min="0"
                step="0.25"
                class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
            </div>

            <div v-else class="space-y-3">
              <div>
                <label class="block font-medium text-white mb-2">Vacation Leave Allocation</label>
                <input
                  v-model.number="applyForm.vacation_days"
                  type="number"
                  min="0"
                  step="0.25"
                  class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>

              <div>
                <label class="block font-medium text-white mb-2">Sick Leave Allocation</label>
                <input
                  v-model.number="applyForm.sick_days"
                  type="number"
                  min="0"
                  step="0.25"
                  class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
            </div>

            <div class="border-t border-slate-800 pt-3 space-y-1">
              <div class="flex justify-between">
                <span class="text-gray-400">Total Applied:</span>
                <span>{{ totalApplied.toFixed(2) }} days</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Remaining:</span>
                <span>{{ remainingCredit.toFixed(2) }} days</span>
              </div>
            </div>
          </template>

          <template v-else>
            <div>
              <label class="block font-medium text-white mb-2">Apply To</label>
              <input
                :value="selectedCredit.credit_type === 'Vacation' ? 'Vacation Leave' : 'Sick Leave'"
                type="text"
                readonly
                class="w-full border border-slate-700 rounded-lg px-3 py-2 text-gray-400 bg-[#0B1420]"
              >
            </div>

            <div>
              <label class="block font-medium text-white mb-2">Days to Apply</label>
              <input
                :value="selectedCredit.equivalent_leave_days"
                type="number"
                readonly
                class="w-full border border-slate-700 rounded-lg px-3 py-2 text-gray-400 bg-[#0B1420]"
              >
            </div>
          </template>

          <p v-if="validationMessage" class="text-red-400" role="alert">
            {{ validationMessage }}
          </p>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button
            @click="closeApplyModal"
            class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition"
          >
            Cancel
          </button>

          <button
            @click="confirmApplyCredit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            Apply Credit
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from "vue";

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

const showApplyModal = ref(false);
const selectedCredit = ref<LeaveCredit | null>(null);
const applyForm = ref({
  leave_type: "Vacation",
  days: 0,
  split: false,
  vacation_days: 0,
  sick_days: 0
});
const validationMessage = ref("");
const form = ref({
  employee_id: "",
  activity_name: "",
  hours_rendered: "",
  equivalent_leave_days: "",
  credit_type: ""
});

const resetApplyForm = () => {
  applyForm.value = {
    leave_type: "Vacation",
    days: 0,
    split: false,
    vacation_days: 0,
    sick_days: 0
  };
  validationMessage.value = "";
};

const handleApplyTypeChange = () => {
  if (applyForm.value.leave_type === "Service Credits") {
    applyForm.value.split = false;
  }
};

const availableCreditDays = computed(() =>
  Number(selectedCredit.value?.equivalent_leave_days ?? 0)
);

const totalApplied = computed(() => {
  if (!selectedCredit.value) return 0;
  if (selectedCredit.value.credit_type !== "Service Credits") {
    return availableCreditDays.value;
  }

  return applyForm.value.split
    ? Number(applyForm.value.vacation_days || 0) + Number(applyForm.value.sick_days || 0)
    : Number(applyForm.value.days || 0);
});

const remainingCredit = computed(() =>
  availableCreditDays.value - totalApplied.value
);

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
    console.log("Sending credit data:", form.value);

    await addLeaveCredit(form.value);

    alert("Leave credit added successfully!");

    form.value = {
      employee_id: "",
      activity_name: "",
      hours_rendered: "",
      equivalent_leave_days: "",
      credit_type: "",
    };

    await loadCredits();
  } catch (error: any) {
    console.error("STATUS:", error.response?.status);
    console.error("SERVER RESPONSE:", error.response?.data);

    alert(
      error.response?.data?.message ??
        JSON.stringify(error.response?.data?.errors ?? "Unable to save leave credit.")
    );
  }
};

const applyCredit = (id: number) => {
  const credit = credits.value.find(
    (credit) => credit.credits_id === id
  );

  if (!credit) return;

  selectedCredit.value = credit;
  resetApplyForm();
  if (credit.credit_type === "Vacation" || credit.credit_type === "Sick") {
    applyForm.value.leave_type = credit.credit_type;
    applyForm.value.days = credit.equivalent_leave_days;
  }
  showApplyModal.value = true;
};

const closeApplyModal = () => {
  showApplyModal.value = false;
  selectedCredit.value = null;
  resetApplyForm();
};

const confirmApplyCredit = async () => {
  if (!selectedCredit.value) return;

  const total = totalApplied.value;
  if (!Number.isFinite(total) || total <= 0) {
    validationMessage.value = "Total applied days must be greater than 0.";
    return;
  }

  if (total > availableCreditDays.value) {
    validationMessage.value = "Total applied days cannot exceed the available credit.";
    return;
  }

  try {
    await applyLeaveCredit({
      credits_id: selectedCredit.value.credits_id,
      leave_type: applyForm.value.leave_type,
      days: applyForm.value.split ? total : applyForm.value.days,
      split: applyForm.value.split,
      vacation_days: applyForm.value.vacation_days,
      sick_days: applyForm.value.sick_days
    });

    alert("Leave credit applied successfully!");

    closeApplyModal();

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