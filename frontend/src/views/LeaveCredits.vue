<template>
  <div class="dashboard-shell p-8 min-h-screen space-y-8">
    <div class="neo-card p-6">
      <h2 class="text-2xl font-bold text-white">Leave Credits</h2>

      <p class="text-gray-400 mt-1">
        Record earned leave credits for employees.
      </p>
    </div>

    <div class="neo-card p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="min-w-0">
          <label class="block text-sm font-medium text-white mb-2">
            Employee
          </label>

          <select v-model="form.employee_id" class="form-control">
            <option value="">Select Employee</option>

            <option
              v-for="employee in employees"
              :key="employee.employee_id"
              :value="employee.employee_id"
            >
              {{ employee.last_name }},
              {{ employee.first_name }}
            </option>
          </select>
        </div>

        <div class="min-w-0">
          <label class="block text-sm font-medium text-white mb-2">
            Credit Type
          </label>

          <select v-model="form.credit_type" class="form-control">
            <option value="">Select Credit Type</option>

            <option value="Service">Service Credits</option>

            <option value="Vacation">Vacation Leave</option>

            <option value="Sick">Sick Leave</option>
          </select>
        </div>

        <div class="min-w-0">
          <label class="block text-sm font-medium text-white mb-2">
            Activity Name
          </label>

          <input
            v-model="form.activity_name"
            type="text"
            class="form-control"
            placeholder="Example: Overtime Duty"
          />
        </div>

        <div class="min-w-0">
          <label class="block text-sm font-medium text-white mb-2">
            Hours Rendered
          </label>

          <input
            v-model="form.hours_rendered"
            type="number"
            min="0"
            step="0.25"
            class="form-control"
          />
        </div>

        <div class="min-w-0">
          <label class="block text-sm font-medium text-white mb-2">
            Equivalent Leave Days
          </label>

          <input
            v-model="form.equivalent_leave_days"
            type="number"
            min="0"
            step="0.25"
            class="form-control"
          />
        </div>
      </div>

      <div class="mt-6">
        <button @click="saveCredit" type="button" class="primary-button">
          Save Credit
        </button>
      </div>
    </div>

    <div class="neo-card p-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-semibold text-white">
            Leave Credit Records
          </h3>

          <p class="text-gray-400 text-sm mt-1">
            View and apply recorded leave credits.
          </p>
        </div>
      </div>

      <!-- Search and Sort -->
      <div class="mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search employee or activity..."
            class="flex-1 min-w-0 form-control"
          />

          <button
            @click="toggleSort"
            type="button"
            class="w-full sm:w-auto px-4 py-2 text-xs font-medium border border-gray-300 text-[var(--text)] rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition whitespace-nowrap"
            :title="arranged ? 'Unsort records' : 'Sort records alphabetically (A to Z)'"
          >
            Sort
          </button>

          <button
            @click="searchQuery = ''"
            class="w-full sm:w-auto px-4 py-2 text-xs font-medium border border-gray-300 text-[var(--text)] rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition whitespace-nowrap"
          >
            Clear
          </button>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="credit-table">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Credit Type</th>
              <th>Activity</th>
              <th>Hours Rendered</th>
              <th>Equivalent Days</th>
              <th>Date Recorded</th>
              <th>Status</th>
              <th class="action-column">Action</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="credit in filteredCredits" :key="credit.credits_id">
              <td class="employee-cell">
                <span class="employee-name">
                  {{ credit.employee?.last_name }},
                  {{ credit.employee?.first_name }}
                </span>
              </td>

              <td>
                <span
                  :class="{
                    'credit-service': credit.credit_type === 'Service',

                    'credit-vacation': credit.credit_type === 'Vacation',

                    'credit-sick': credit.credit_type === 'Sick',

                    'credit-other': ![
                      'Service',
                      'Vacation',
                      'Sick',
                    ].includes(credit.credit_type),
                  }"
                >
                  {{
                    credit.credit_type === "Vacation"
                      ? "Vacation Leave"
                      : credit.credit_type === "Sick"
                        ? "Sick Leave"
                        : credit.credit_type === "Service"
                          ? "Service Credits"
                          : credit.credit_type
                  }}
                </span>
              </td>

              <td class="table-primary">
                {{ credit.activity_name || "—" }}
              </td>

              <td>
                {{ Number(credit.hours_rendered || 0).toFixed(2) }}
              </td>

              <td>
                {{ Number(credit.equivalent_leave_days || 0).toFixed(2) }}
              </td>

              <td>
                {{ formatDate(credit.date_recorded) }}
              </td>

              <td>
                <span
                  :class="
                    credit.status === 'Applied'
                      ? 'status-applied'
                      : 'status-pending'
                  "
                >
                  {{ credit.status || "Pending" }}
                </span>
              </td>

              <td class="action-cell">
                <div class="action-buttons">
                  <button
                    v-if="credit.status !== 'Applied'"
                    @click="applyCredit(credit.credits_id)"
                    type="button"
                    class="apply-button"
                  >
                    Apply Credit
                  </button>

                  <span v-else class="applied-text">
                    Applied
                  </span>

                  <button
                    @click="removeCredit(credit.credits_id)"
                    type="button"
                    class="remove-button"
                  >
                    Remove
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredCredits.length === 0">
              <td colspan="8" class="empty-state">
                No leave credit records found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div
      v-if="showApplyModal"
      class="modal-overlay"
      @click.self="closeApplyModal"
    >
      <div class="modal-card">
        <h2 class="text-xl font-semibold text-white">
          Apply Leave Credit
        </h2>

        <p class="text-gray-300 mt-3">
          Choose where this credit should be applied. This will update the
          employee's leave balance.
        </p>

        <div v-if="selectedCredit" class="credit-details">
          <div class="detail-row">
            <span class="detail-label">
              Employee:
            </span>

            <span class="detail-value">
              {{ selectedCredit.employee?.last_name }},
              {{ selectedCredit.employee?.first_name }}
            </span>
          </div>

          <div class="detail-row">
            <span class="detail-label">
              Credit Type:
            </span>

            <span class="detail-value">
              {{ selectedCredit.credit_type }}
            </span>
          </div>

          <div class="detail-row">
            <span class="detail-label">
              Activity:
            </span>

            <span class="detail-value">
              {{ selectedCredit.activity_name }}
            </span>
          </div>

          <div class="detail-row">
            <span class="detail-label">
              Equivalent Days:
            </span>

            <span class="detail-value">
              {{ selectedCredit.equivalent_leave_days }}
            </span>
          </div>

          <div class="available-credit">
            <span class="font-medium text-gray-400">
              Available Credit:
            </span>

            {{ availableCreditDays.toFixed(2) }} days
          </div>
        </div>

        <div
          v-if="selectedCredit"
          class="mt-4 space-y-4 text-sm text-white"
        >
          <template v-if="selectedCredit.credit_type === 'Service'">
            <div>
              <label class="block font-medium text-white mb-2">
                Apply To
              </label>

              <select
                v-model="applyForm.leave_type"
                @change="handleApplyTypeChange"
                :disabled="applyForm.split"
                class="modal-form-control"
              >
                <option value="Service">
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

            <label
              v-if="applyForm.leave_type !== 'Service'"
              class="flex items-center gap-2 text-white"
            >
              <input
                v-model="applyForm.split"
                type="checkbox"
                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
              />

              Split Application
            </label>

            <div v-if="!applyForm.split">
              <label class="block font-medium text-white mb-2">
                Days to Apply
              </label>

              <input
                v-model.number="applyForm.days"
                type="number"
                min="0"
                step="0.25"
                class="modal-form-control"
              />
            </div>

            <div v-else class="space-y-3">
              <div>
                <label class="block font-medium text-white mb-2">
                  Vacation Leave Allocation
                </label>

                <input
                  v-model.number="applyForm.vacation_days"
                  type="number"
                  min="0"
                  step="0.25"
                  class="modal-form-control"
                />
              </div>

              <div>
                <label class="block font-medium text-white mb-2">
                  Sick Leave Allocation
                </label>

                <input
                  v-model.number="applyForm.sick_days"
                  type="number"
                  min="0"
                  step="0.25"
                  class="modal-form-control"
                />
              </div>
            </div>

            <div class="calculation-box">
              <div class="flex justify-between">
                <span class="text-gray-400">
                  Total Applied:
                </span>

                <span>
                  {{ totalApplied.toFixed(2) }} days
                </span>
              </div>

              <div class="flex justify-between">
                <span class="text-gray-400">
                  Remaining:
                </span>

                <span>
                  {{ remainingCredit.toFixed(2) }} days
                </span>
              </div>
            </div>
          </template>

          <template v-else>
            <div>
              <label class="block font-medium text-white mb-2">
                Apply To
              </label>

              <input
                :value="
                  selectedCredit.credit_type === 'Vacation'
                    ? 'Vacation Leave'
                    : 'Sick Leave'
                "
                type="text"
                readonly
                class="modal-form-control readonly-control"
              />
            </div>

            <div>
              <label class="block font-medium text-white mb-2">
                Days to Apply
              </label>

              <input
                :value="selectedCredit.equivalent_leave_days"
                type="number"
                readonly
                class="modal-form-control readonly-control"
              />
            </div>
          </template>

          <p
            v-if="validationMessage"
            class="validation-message"
            role="alert"
          >
            {{ validationMessage }}
          </p>
        </div>

        <div class="modal-actions">
          <button
            @click="closeApplyModal"
            type="button"
            class="cancel-button"
          >
            Cancel
          </button>

          <button
            @click="confirmApplyCredit"
            type="button"
            class="confirm-button"
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
  applyLeaveCredit,
  deleteLeaveCredit,
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

const searchQuery = ref("");
const arranged = ref(false);

const showApplyModal = ref(false);
const selectedCredit = ref<LeaveCredit | null>(null);

const applyForm = ref({
  leave_type: "Vacation",
  days: 0,
  split: false,
  vacation_days: 0,
  sick_days: 0,
});

const validationMessage = ref("");

const form = ref({
  employee_id: "",
  activity_name: "",
  hours_rendered: "",
  equivalent_leave_days: "",
  credit_type: "",
});

const resetApplyForm = () => {
  applyForm.value = {
    leave_type: "Vacation",
    days: 0,
    split: false,
    vacation_days: 0,
    sick_days: 0,
  };

  validationMessage.value = "";
};

const handleApplyTypeChange = () => {
  applyForm.value.split = false;
  applyForm.value.vacation_days = 0;
  applyForm.value.sick_days = 0;
  applyForm.value.days = availableCreditDays.value;
};

const availableCreditDays = computed(() =>
  Number(selectedCredit.value?.equivalent_leave_days ?? 0),
);

const totalApplied = computed(() => {
  if (!selectedCredit.value) {
    return 0;
  }

  if (selectedCredit.value.credit_type !== "Service") {
    return availableCreditDays.value;
  }

  if (applyForm.value.split) {
    return (
      Number(applyForm.value.vacation_days || 0) +
      Number(applyForm.value.sick_days || 0)
    );
  }

  return Number(applyForm.value.days || 0);
});

const remainingCredit = computed(
  () => availableCreditDays.value - totalApplied.value,
);

const filteredCredits = computed(() => {
  const search = searchQuery.value.trim().toLowerCase();
  
  let result = credits.value.filter((credit) => {
    const employeeName = `${credit.employee?.last_name || ""} ${credit.employee?.first_name || ""}`.toLowerCase();
    const activityName = credit.activity_name?.toLowerCase() || "";
    
    return employeeName.includes(search) || activityName.includes(search);
  });
  
  // Sort alphabetically if arranged is true
  if (arranged.value) {
    return [...result].sort((a, b) => {
      const aName = `${a.employee?.last_name || ""} ${a.employee?.first_name || ""}`.trim().toLowerCase();
      const bName = `${b.employee?.last_name || ""} ${b.employee?.first_name || ""}`.trim().toLowerCase();
      return aName.localeCompare(bName);
    });
  }
  
  return result;
});

const toggleSort = () => {
  arranged.value = !arranged.value;
};

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
        JSON.stringify(
          error.response?.data?.errors ??
            "Unable to save leave credit.",
        ),
    );
  }
};

const applyCredit = (id: number) => {
  const credit = credits.value.find(
    (credit) => credit.credits_id === id,
  );

  if (!credit) {
    return;
  }

  selectedCredit.value = credit;

  resetApplyForm();

  applyForm.value.leave_type = credit.credit_type;
  applyForm.value.days =
    Number(credit.equivalent_leave_days);

  applyForm.value.split = false;
  applyForm.value.vacation_days = 0;
  applyForm.value.sick_days = 0;

  showApplyModal.value = true;
};

const closeApplyModal = () => {
  showApplyModal.value = false;
  selectedCredit.value = null;
  resetApplyForm();
};

const confirmApplyCredit = async () => {
  if (!selectedCredit.value) {
    return;
  }

  const total = totalApplied.value;

  if (!Number.isFinite(total) || total <= 0) {
    validationMessage.value =
      "Total applied days must be greater than 0.";

    return;
  }

  if (total > availableCreditDays.value) {
    validationMessage.value =
      "Total applied days cannot exceed the available credit.";

    return;
  }

  validationMessage.value = "";

  try {
    await applyLeaveCredit({
      credits_id: selectedCredit.value.credits_id,
      leave_type: applyForm.value.leave_type,
      days: applyForm.value.split
        ? total
        : Number(applyForm.value.days),
      split: applyForm.value.split,
      vacation_days: Number(
        applyForm.value.vacation_days || 0,
      ),
      sick_days: Number(
        applyForm.value.sick_days || 0,
      ),
    });

    alert("Leave credit applied successfully!");

    closeApplyModal();

    await loadCredits();
  } catch (error: any) {
    console.error(
      "Failed to apply leave credit:",
      error.response?.data || error,
    );

    validationMessage.value =
      error.response?.data?.message ||
      "Unable to apply leave credit.";
  }
};

const removeCredit = async (id: number) => {
  const credit = credits.value.find(
    (credit) => credit.credits_id === id,
  );

  if (!credit) {
    return;
  }

  const employeeName =
    `${credit.employee?.last_name ?? ""}, ${credit.employee?.first_name ?? ""}`;

  const confirmed = window.confirm(
    `Are you sure you want to remove this leave credit?\n\n` +
      `Employee: ${employeeName}\n` +
      `Activity: ${credit.activity_name}\n` +
      `Credit: ${credit.equivalent_leave_days} days\n\n` +
      `This will only remove it from the active records.`,
  );

  if (!confirmed) {
    return;
  }

  try {
    await deleteLeaveCredit(id);

    alert("Leave credit removed successfully.");

    await loadCredits();
  } catch (error: any) {
    console.error(
      "Failed to remove leave credit:",
      error.response?.data || error,
    );

    alert(
      error.response?.data?.message ||
        "Unable to remove leave credit.",
    );
  }
};

const formatDate = (date: string) => {
  if (!date) {
    return "—";
  }

  const parsedDate = new Date(date);

  if (Number.isNaN(parsedDate.getTime())) {
    return date;
  }

  return parsedDate.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

onMounted(() => {
  loadEmployees();
  loadCredits();
});
</script>

<style scoped>
/* ============================================================
   DASHBOARD BACKGROUND
   Matches the main Dashboard.vue theme
   ============================================================ */

.dashboard-shell {
  background: var(--app-bg);

  width: 100%;

  min-height: 100vh;
}

/* ============================================================
   CARDS
   Matches Dashboard.vue .neo-card
   ============================================================ */

.neo-card {
  background: var(--surface);

  border: 1px solid #cbd8e8;

  border-radius: 1rem;

  box-shadow:
    0 6px 18px rgba(23, 32, 51, 0.06);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;

  min-width: 0;
}

.neo-card:hover {
  box-shadow:
    0 10px 24px rgba(23, 32, 51, 0.09);
}

/* ============================================================
   TEXT
   Matches Dashboard.vue variable-based text colors
   ============================================================ */

.neo-card .text-white {
  color: var(--text) !important;
}

.neo-card .text-gray-300,
.neo-card .text-gray-400,
.neo-card .text-gray-500 {
  color: var(--text-muted) !important;
}

.neo-card button.text-white,
.neo-card a.text-white {
  color: #ffffff !important;
}

/* ============================================================
   FORM CONTROLS
   Light dashboard style
   ============================================================ */

.form-control {
  width: 100%;

  min-width: 0;

  border: 1px solid #c8d8eb;

  border-radius: 0.6rem;

  padding: 0.65rem 0.8rem;

  color: var(--text);

  background: var(--surface-muted);

  outline: none;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    background-color 0.2s ease;
}

.form-control::placeholder {
  color: var(--text-muted);
}

.form-control:focus {
  border-color: #7aa7e8;

  background: #ffffff;

  box-shadow:
    0 0 0 3px rgba(37, 99, 235, 0.12);
}

.form-control option {
  background: #ffffff;

  color: var(--text);
}

/* Dark mode overrides for dropdown options */
.dark .form-control option {
  background: var(--surface);

  color: var(--text);
}

/* ============================================================
   PRIMARY BUTTON
   Matches Dashboard primary buttons
   ============================================================ */

.primary-button {
  display: inline-flex;

  align-items: center;

  justify-content: center;

  min-height: 40px;

  padding: 0.5rem 1.5rem;

  background: var(--primary);

  color: #ffffff;

  border-radius: 0.5rem;

  font-weight: 600;

  white-space: nowrap;

  transition:
    background-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.primary-button:hover {
  background: var(--primary-hover);

  transform: translateY(-1px);
}

/* ============================================================
   TABLE
   Matches Dashboard summary-table colors
   ============================================================ */

.table-wrapper {
  width: 100%;

  max-width: 100%;

  overflow-x: auto;

  overflow-y: hidden;

  -webkit-overflow-scrolling: touch;
}

.credit-table {
  width: 100%;

  min-width: 850px;

  border-collapse: collapse;

  table-layout: auto;
}

.credit-table thead {
  background: var(--surface-muted);
}

.credit-table th {
  padding: 0.85rem 1rem;

  color: var(--text-muted);

  font-size: 0.72rem;

  font-weight: 600;

  text-align: left;

  text-transform: uppercase;

  letter-spacing: 0.05em;

  white-space: nowrap;

  border-bottom: 1px solid var(--border);
}

.credit-table tbody tr {
  border-top: 1px solid var(--border);

  transition:
    background-color 0.2s ease,
    border-color 0.2s ease;
}

.credit-table tbody tr:hover {
  background: #f3f7fc;
}

.credit-table td {
  padding: 0.9rem 1rem;

  vertical-align: middle;

  color: var(--text);

  font-size: 0.875rem;
}

.employee-cell {
  min-width: 150px;

  max-width: 220px;
}

.employee-name {
  display: block;

  color: var(--text);

  font-weight: 500;

  overflow-wrap: anywhere;
}

.table-primary {
  color: var(--text);

  overflow-wrap: anywhere;
}

/* ============================================================
   CREDIT TYPE COLORS
   Softened to match Dashboard palette
   ============================================================ */

.credit-service {
  color: #9333ea;

  font-weight: 600;

  white-space: nowrap;
}

.credit-vacation {
  color: #2563eb;

  font-weight: 600;

  white-space: nowrap;
}

.credit-sick {
  color: #16a34a;

  font-weight: 600;

  white-space: nowrap;
}

.credit-other {
  color: var(--text-muted);

  font-weight: 600;
}

/* ============================================================
   STATUS
   Matches Dashboard status colors
   ============================================================ */

.status-pending {
  display: inline-flex;

  align-items: center;

  justify-content: center;

  padding: 0.25rem 0.75rem;

  border-radius: 9999px;

  background: rgba(234, 179, 8, 0.14);

  color: #a16207;

  font-size: 0.75rem;

  font-weight: 600;

  white-space: nowrap;
}

.status-applied {
  display: inline-flex;

  align-items: center;

  justify-content: center;

  padding: 0.25rem 0.75rem;

  border-radius: 9999px;

  background: rgba(34, 197, 94, 0.14);

  color: #15803d;

  font-size: 0.75rem;

  font-weight: 600;

  white-space: nowrap;
}

/* ============================================================
   ACTION COLUMN
   ============================================================ */

.action-column {
  text-align: center !important;
}

.action-cell {
  text-align: center;

  white-space: nowrap;
}

.action-buttons {
  display: inline-flex;

  align-items: center;

  justify-content: center;

  gap: 0.5rem;

  flex-wrap: wrap;
}

/* ============================================================
   APPLY BUTTON
   ============================================================ */

.apply-button {
  display: inline-flex;

  align-items: center;

  justify-content: center;

  padding: 0.5rem 1rem;

  background: var(--primary);

  color: #ffffff;

  border-radius: 0.5rem;

  font-size: 0.875rem;

  font-weight: 600;

  white-space: nowrap;

  transition:
    background-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.apply-button:hover {
  background: var(--primary-hover);

  transform: translateY(-1px);
}

.applied-text {
  color: #15803d;

  font-weight: 600;

  white-space: nowrap;
}

/* ============================================================
   REMOVE BUTTON
   Dashboard-compatible red
   ============================================================ */

.remove-button {
  display: inline-flex;

  align-items: center;

  justify-content: center;

  padding: 0.5rem 1rem;

  background: #dc2626;

  color: #ffffff;

  border-radius: 0.5rem;

  font-size: 0.875rem;

  font-weight: 600;

  white-space: nowrap;

  transition:
    background-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.remove-button:hover {
  background: #b91c1c;

  transform: translateY(-1px);
}

/* ============================================================
   EMPTY STATE
   ============================================================ */

.empty-state {
  text-align: center;

  color: var(--text-muted) !important;

  padding: 2rem !important;
}

/* ============================================================
   MODAL OVERLAY
   ============================================================ */

.modal-overlay {
  position: fixed;

  inset: 0;

  z-index: 50;

  display: flex;

  align-items: center;

  justify-content: center;

  padding: 1rem;

  background: rgba(23, 32, 51, 0.55);

  overflow-y: auto;
}

/* ============================================================
   MODAL CARD
   Uses the same surface as Dashboard cards
   ============================================================ */

.modal-card {
  width: 100%;

  max-width: 28rem;

  max-height: calc(100vh - 2rem);

  overflow-y: auto;

  padding: 1.5rem;

  background: var(--surface);

  border: 1px solid #cbd8e8;

  border-radius: 1rem;

  box-shadow:
    0 20px 40px rgba(23, 32, 51, 0.18);
}

.modal-card .text-white {
  color: var(--text) !important;
}

.modal-card .text-gray-300,
.modal-card .text-gray-400 {
  color: var(--text-muted) !important;
}

/* ============================================================
   MODAL FORM CONTROLS
   ============================================================ */

.modal-form-control {
  width: 100%;

  border: 1px solid #c8d8eb;

  border-radius: 0.6rem;

  padding: 0.65rem 0.75rem;

  color: var(--text);

  background: var(--surface-muted);

  outline: none;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    background-color 0.2s ease;
}

.modal-form-control:focus {
  border-color: #7aa7e8;

  background: #ffffff;

  box-shadow:
    0 0 0 3px rgba(37, 99, 235, 0.12);
}

.modal-form-control option {
  background: #ffffff;

  color: var(--text);
}

/* Dark mode overrides for modal dropdown options */
.dark .modal-form-control option {
  background: var(--surface);

  color: var(--text);
}

.readonly-control {
  color: var(--text-muted);

  background: #f3f7fc;

  cursor: default;
}

/* ============================================================
   CREDIT DETAILS
   ============================================================ */

.credit-details {
  margin-top: 1rem;

  padding: 1rem;

  background: var(--surface-muted);

  border: 1px solid #c8d8eb;

  border-radius: 0.75rem;
}

.detail-row {
  display: flex;

  justify-content: space-between;

  align-items: flex-start;

  gap: 1rem;

  padding: 0.3rem 0;
}

.detail-label {
  color: var(--text-muted);

  font-weight: 500;

  flex-shrink: 0;
}

.detail-value {
  color: var(--text);

  text-align: right;

  overflow-wrap: anywhere;

  font-weight: 500;
}

.available-credit {
  margin-top: 0.75rem;

  padding-top: 0.75rem;

  border-top: 1px solid var(--border);

  color: var(--text);
}

/* ============================================================
   CALCULATION BOX
   ============================================================ */

.calculation-box {
  border-top: 1px solid var(--border);

  padding-top: 0.75rem;

  color: var(--text);
}

/* ============================================================
   VALIDATION
   ============================================================ */

.validation-message {
  color: #dc2626 !important;

  font-weight: 500;
}

/* ============================================================
   MODAL BUTTONS
   ============================================================ */

.modal-actions {
  display: flex;

  justify-content: flex-end;

  gap: 0.75rem;

  margin-top: 1.5rem;

  flex-wrap: wrap;
}

.cancel-button {
  padding: 0.5rem 1rem;

  background: #e2e8f0;

  color: #334155;

  border: 1px solid #cbd5e1;

  border-radius: 0.5rem;

  font-weight: 600;

  white-space: nowrap;

  transition:
    background-color 0.2s ease,
    transform 0.2s ease;
}

.cancel-button:hover {
  background: #cbd5e1;

  transform: translateY(-1px);
}

/* Dark mode overrides for buttons */
.dark .cancel-button {
  background: var(--surface-muted);

  color: var(--text);

  border-color: var(--border);
}

.dark .cancel-button:hover {
  background: var(--border);
}

.dark .readonly-control {
  background: var(--surface-muted);
}

.dark .remove-button {
  background: var(--danger);
}

.dark .remove-button:hover {
  background: #b91c1c;
}

/* Dark mode overrides for status badges */
.dark .status-pending {
  background: rgba(251, 191, 36, 0.15);

  color: var(--warning);
}

.dark .status-applied {
  background: rgba(74, 222, 128, 0.15);

  color: var(--success);
}

.dark .applied-text {
  color: var(--success);
}

/* Dark mode overrides for credit type colors */
.dark .credit-service {
  color: #a78bfa;
}

.dark .credit-vacation {
  color: var(--primary);
}

.dark .credit-sick {
  color: var(--success);
}

/* Dark mode overrides for action buttons */
.dark .apply-button {
  background: var(--primary);
}

.dark .apply-button:hover {
  background: var(--primary-hover);
}

.dark .primary-button {
  background: var(--primary);
}

.dark .primary-button:hover {
  background: var(--primary-hover);
}

.dark .confirm-button {
  background: var(--primary);
}

.dark .confirm-button:hover {
  background: var(--primary-hover);
}

/* Dark mode overrides for table */
.dark .credit-table tbody tr:hover {
  background: var(--surface-muted);
}

/* Dark mode overrides for validation message */
.dark .validation-message {
  color: var(--danger) !important;
}

/* Dark mode overrides for form controls */
.dark .form-control {
  background: var(--surface);

  border-color: var(--border);
}

.dark .form-control:focus {
  background: var(--surface);
}

.dark .modal-form-control {
  background: var(--surface);

  border-color: var(--border);
}

.dark .modal-form-control:focus {
  background: var(--surface);
}

.confirm-button {
  padding: 0.5rem 1rem;

  background: var(--primary);

  color: #ffffff;

  border-radius: 0.5rem;

  font-weight: 600;

  white-space: nowrap;

  transition:
    background-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.confirm-button:hover {
  background: var(--primary-hover);

  transform: translateY(-1px);
}

/* ============================================================
   GENERAL
   ============================================================ */

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {
  letter-spacing: -0.01em;
}

button {
  transition:
    background-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

/* ============================================================
   MOBILE
   ============================================================ */

@media (max-width: 768px) {
  .dashboard-shell {
    padding: 1.5rem;
  }

  .neo-card {
    border-radius: 1rem;
  }

  .neo-card.p-6 {
    padding: 1.25rem;
  }

  .modal-card {
    padding: 1.25rem;
  }
}

@media (max-width: 640px) {
  .dashboard-shell {
    padding: 1rem;
  }

  .neo-card.p-6 {
    padding: 1rem;
  }

  .primary-button {
    width: 100%;
  }

  .modal-actions {
    flex-direction: column;
  }

  .modal-actions button {
    width: 100%;
  }

  .detail-row {
    flex-direction: column;

    gap: 0.15rem;
  }

  .detail-value {
    text-align: left;
  }

  .action-buttons {
    flex-direction: column;

    width: 100%;
  }

  .action-buttons button {
    width: 100%;
  }
}
</style>