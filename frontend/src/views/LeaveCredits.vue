<template>
  <div class="dashboard-shell p-8 min-h-screen space-y-8">
    <div class="p-6 rounded-lg border" style="border-color: var(--border); background: var(--surface);">
      <h2 class="text-2xl font-semibold" style="color: var(--text);">Leave Credits</h2>

      <p class="mt-1" style="color: var(--text-muted);">
        Record earned leave credits for employees.
      </p>
    </div>

    <div class="p-6 rounded-lg border" style="border-color: var(--border); background: var(--surface);">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="min-w-0">
          <label class="block text-sm font-medium mb-2" style="color: var(--text);">
            Employee
          </label>

          <select v-model="form.employee_id" class="form-control" style="border-color: var(--border); background: var(--surface); color: var(--text);">
            <option value="">Select Employee</option>

            <option
              v-for="employee in employees"
              :key="employee.employee_id"
              :value="employee.employee_id"
            >
              {{ employee.last_name }}, {{ employee.first_name }}
            </option>
          </select>
        </div>

        <div class="min-w-0">
          <label class="block text-sm font-medium mb-2" style="color: var(--text);">
            Credit Type
          </label>

          <input value="Local Credits (service credit)" type="text" readonly class="form-control readonly-control" style="border-color: var(--border); background: var(--surface-muted); color: var(--text);" />
        </div>

        <div class="min-w-0">
          <label class="block text-sm font-medium mb-2" style="color: var(--text);">
            Activity Name
          </label>

          <input
            v-model="form.activity_name"
            type="text"
            class="form-control"
            maxlength="255"
            placeholder="Describe the activity or reason for the award"
            style="border-color: var(--border); background: var(--surface); color: var(--text);"
          />
        </div>

        <div class="min-w-0">
          <label class="block text-sm font-medium mb-2" style="color: var(--text);">
            Hours Rendered
          </label>

          <input
            v-model="renderedTime"
            type="number"
            min="0.0167"
            step="0.0001"
            class="form-control"
            placeholder="Enter time in hours (for example, 7.5)"
            style="border-color: var(--border); background: var(--surface); color: var(--text);"
          />
          <p v-if="conversionError" class="validation-message mt-1" style="color: var(--danger);">
            {{ conversionError }}
          </p>
        </div>

        <div class="min-w-0">
          <label class="block text-sm font-medium mb-2" style="color: var(--text);">
            Equivalent Leave Days
          </label>

          <input
            type="text"
            readonly
            step="0.001"
            placeholder="Calculated from rendered hours"
            :value="form.equivalent_leave_days"
            class="form-control"
            style="border-color: var(--border); background: var(--surface-muted); color: var(--text);"
          />
        </div>
      </div>

      <div class="mt-6">
        <button @click="applyCredit" type="button" class="primary-button" :disabled="submitting" style="background: var(--primary);">
          {{ submitting ? "Saving…" : "Record Credit" }}
        </button>
      </div>
    </div>

    <div class="p-6 rounded-lg border" style="border-color: var(--border); background: var(--surface);">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-semibold" style="color: var(--text);">Leave Credit Records</h3>

          <p class="text-sm mt-1" style="color: var(--text-muted);">
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
            style="border-color: var(--border); background: var(--surface); color: var(--text);"
          />

          <button
            @click="toggleSort"
            type="button"
            class="w-full sm:w-auto px-4 py-2 text-xs font-medium border rounded-full transition whitespace-nowrap"
            style="border-color: var(--border); color: var(--text);"
            :title="
              arranged
                ? 'Unsort records'
                : 'Sort records alphabetically (A to Z)'
            "
          >
            Sort
          </button>

          <button
            @click="searchQuery = ''"
            class="w-full sm:w-auto px-4 py-2 text-xs font-medium border rounded-full transition whitespace-nowrap"
            style="border-color: var(--border); color: var(--text);"
          >
            Clear
          </button>
        </div>
      </div>

      <p class="text-sm mb-4" style="color: var(--text-muted);">
        Accumulated earned credits from recorded activities. Remaining credits
        after leave deductions are shown in Leave Balances.
      </p>
      <div class="table-wrapper">
        <table class="credit-table">
          <thead class="text-left font-semibold" style="background: var(--surface-muted); color: var(--text);">
            <tr>
              <th>Employee</th>
              <th>Records</th>
              <th>Total Hours Rendered</th>
              <th>Accumulated Credits</th>
              <th>Latest Record</th>
              <th class="action-column">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="group in filteredCreditGroups" :key="group.employee_id" style="border-color: var(--border);">
              <td class="employee-cell" style="color: var(--text);">
                <span class="employee-name">{{ group.employee_name }}</span>
              </td>
              <td style="color: var(--text);">{{ group.records.length }}</td>
              <td style="color: var(--text);">{{ (group.hourUnits / 10000).toFixed(4) }}</td>
              <td style="color: var(--text);">
                <div
                  v-for="bucket in group.buckets"
                  :key="bucket.type"
                  class="credit-total-line"
                >
                  <span
                    :class="
                      bucket.type === 'Service'
                        ? 'credit-service'
                        : bucket.type === 'Vacation'
                          ? 'credit-vacation'
                          : bucket.type === 'Sick'
                            ? 'credit-sick'
                            : 'credit-other'
                    "
                    >{{ (bucket.dayUnits / 1000).toFixed(3) }} days</span
                  >
                  <span class="text-sm" style="color: var(--text-muted);">
                    {{
                      bucket.type === "Service"
                        ? " Local Credits"
                        : bucket.type === "Vacation"
                          ? " Vacation Leave"
                          : bucket.type === "Sick"
                            ? "Sick Leave"
                            : bucket.type
                    }}</span
                  >
                </div>
              </td>
              <td style="color: var(--text);">{{ formatDate(group.latestDate) }}</td>
              <td class="action-cell">
                <button
                  @click="selectedEmployeeId = group.employee_id"
                  type="button"
                  class="apply-button"
                  style="background: var(--primary);"
                >
                  View Details
                </button>
              </td>
            </tr>
            <tr v-if="filteredCreditGroups.length === 0">
              <td colspan="6" class="empty-state" style="color: var(--text-muted);">
                {{ creditsLoading ? "Loading leave credit records…" : creditsError || "No leave credit records found." }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div
      v-if="selectedEmployeeId !== null"
      class="modal-overlay"
      @click.self="selectedEmployeeId = null"
      @keydown.esc="selectedEmployeeId = null"
    >
      <div
        class="modal-card credit-history-modal rounded-lg border"
        style="border-color: var(--border); background: var(--surface);"
        role="dialog"
        aria-modal="true"
        aria-labelledby="credit-history-title"
        tabindex="-1"
      >
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
          <div>
            <h3
              id="credit-history-title"
              class="text-lg font-semibold"
              style="color: var(--text);"
            >
              Credit Activity History
            </h3>
            <p style="color: var(--text-muted);">{{ selectedEmployeeName }}</p>
          </div>
          <button
            type="button"
            class="cancel-button"
            style="background: var(--text-muted);"
            @click="selectedEmployeeId = null"
          >
            Close
          </button>
        </div>
        <p class="text-sm mb-4" style="color: var(--text-muted);">
          Each activity remains a separate record. Revoking an activity updates
          the accumulated summary.
        </p>
        <div class="table-wrapper">
          <table class="credit-table">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Credit Type</th>
                <th>Activity</th>
                <th>Time Rendered</th>
                <th>Equivalent Days</th>
                <th>Date Recorded</th>
                <th class="action-column">Action</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="credit in detailCredits" :key="credit.credits_id">
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
                      'credit-other': !['Service', 'Vacation', 'Sick'].includes(
                        credit.credit_type,
                      ),
                    }"
                  >
                    {{
                      credit.credit_type === "Vacation"
                        ? "Vacation Leave"
                        : credit.credit_type === "Sick"
                          ? "Sick Leave"
                          : credit.credit_type === "Service"
                            ? "Local Credits"
                            : credit.credit_type
                    }}
                  </span>
                </td>

                <td class="table-primary">
                  {{ credit.activity_name || "—" }}
                </td>

                <td>
                  {{ Number(credit.hours_rendered || 0).toFixed(4) }}
                </td>

                <td>
                  {{ Number(credit.equivalent_leave_days || 0).toFixed(3) }}
                </td>

                <td>
                  {{ formatDate(credit.date_recorded) }}
                </td>

                <td class="action-cell">
                  <div class="action-buttons">
                    <button
                      @click="revokeCredit(credit)"
                      type="button"
                      class="remove-button"
                      :disabled="revokingCreditId === credit.credits_id"
                    >
                      {{ revokingCreditId === credit.credits_id ? "Revoking…" : "Revoke Credits" }}
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="detailCredits.length === 0">
                <td colspan="7" class="empty-state">
                  No leave credit records found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { confirmAction, notify } from "@/composables/useNotifications";
import { computed, ref, onMounted, watch } from "vue";
import axios from "axios";

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

const form = ref({
  employee_id: "",
  activity_name: "",
  hours_rendered: "",
  equivalent_leave_days: "",
  credit_type: "Service",
});

/* ============================================================
   API BASE — used to keep the employee's leave balance in sync
   whenever a credit is recorded or revoked.
   ============================================================ */
const API_BASE = "https://enhs-leave-management-system.onrender.com/api";
const submitting = ref(false);
const creditsLoading = ref(false);
const creditsError = ref("");
const revokingCreditId = ref<number | null>(null);

const authHeaders = () => {
  const token = localStorage.getItem("token");
  return {
    Authorization: `Bearer ${token}`,
    Accept: "application/json",
    "Content-Type": "application/json",
  };
};

const renderedTime = ref<string | number>("");
const conversionError = ref("");
const conversionRows = ref<
  { unit: string; quantity: number; equivalent_days: string | number }[]
>([]);

const loadConversionReference = async () => {
  conversionRows.value = [];
  conversionError.value = "";
  try {
    const { data } = await axios.get(
      `${API_BASE}/leave-credits/conversion-reference`,
      { headers: authHeaders() },
    );
    if (!Array.isArray(data) || data.length === 0) {
      conversionError.value =
        "Conversion reference is empty. Run WorkTimeConversionSeeder on the deployed backend.";
      return;
    }
    const hourRow = data.find(
      (row) => row.unit === "hour" && Number(row.quantity) === 1,
    );
    const hasMinutes = Array.from(
      { length: 60 },
      (_, index) => index + 1,
    ).every((minute) =>
      data.some(
        (row) =>
          row.unit === "minute" &&
          Number(row.quantity) === minute &&
          Number.isFinite(Number(row.equivalent_days)),
      ),
    );
    if (
      !hourRow ||
      !Number.isFinite(Number(hourRow.equivalent_days)) ||
      !hasMinutes
    ) {
      conversionError.value =
        "Conversion reference is incomplete. Run WorkTimeConversionSeeder on the deployed backend.";
      return;
    }
    conversionRows.value = data;
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      console.error("Conversion reference request failed:", {
        status: error.response?.status,
        response: error.response?.data,
      });
      const message = error.response?.data?.message;
      conversionError.value =
        typeof message === "string"
          ? message
          : `Unable to load conversion reference (HTTP ${error.response?.status ?? "network error"}). Check backend logs.`;
    } else {
      console.error("Conversion reference failed:", error);
      conversionError.value = "Unable to read conversion reference.";
    }
  }
};

watch([renderedTime, conversionRows], () => {
  form.value.hours_rendered = "";
  form.value.equivalent_leave_days = "";
  if (renderedTime.value === "") return;
  const input = Number(renderedTime.value);
  if (!Number.isFinite(input) || input <= 0) return;
  // Fixed to hours - convert to minutes for calculation
  const totalMinutes = Math.round(input * 60);
  if (totalMinutes < 1) return;
  const wholeHours = Math.floor(totalMinutes / 60);
  const minutes = totalMinutes % 60;
  const hourRow = conversionRows.value.find(
    (row) => row.unit === "hour" && Number(row.quantity) === 1,
  );
  const minuteRow = conversionRows.value.find(
    (row) => row.unit === "minute" && Number(row.quantity) === minutes,
  );
  if (!hourRow || (minutes !== 0 && !minuteRow)) return;
  form.value.hours_rendered = (totalMinutes / 60).toFixed(4);
  form.value.equivalent_leave_days = (
    wholeHours * Number(hourRow.equivalent_days) +
    (minutes ? Number(minuteRow!.equivalent_days) : 0)
  ).toFixed(3);
});

interface CreditGroup {
  employee_id: number;
  employee_name: string;
  records: LeaveCredit[];
  hourUnits: number;
  buckets: { type: string; dayUnits: number }[];
  latestDate: string;
}

const selectedEmployeeId = ref<number | null>(null);

const creditGroups = computed<CreditGroup[]>(() => {
  const groups = new Map<number, CreditGroup>();
  for (const credit of credits.value) {
    const employeeId = Number(credit.employee_id);
    let group = groups.get(employeeId);
    if (!group) {
      const employee =
        credit.employee ||
        employees.value.find((e) => Number(e.employee_id) === employeeId);
      const name = employee
        ? `${employee.last_name}, ${employee.first_name}`
        : `Employee #${employeeId}`;
      group = {
        employee_id: employeeId,
        employee_name: name,
        records: [],
        hourUnits: 0,
        buckets: [],
        latestDate: "",
      };
      groups.set(employeeId, group);
    }
    group.records.push(credit);
    const hours = Number(credit.hours_rendered || 0);
    const days = Number(credit.equivalent_leave_days || 0);
    if (Number.isFinite(hours)) group.hourUnits += Math.round(hours * 10000);
    // Keep each transaction type separate; service, vacation, and sick awards are not fungible.
    const bucketType = credit.credit_type;
    let bucket = group.buckets.find((b) => b.type === bucketType);
    if (!bucket) {
      bucket = { type: bucketType, dayUnits: 0 };
      group.buckets.push(bucket);
    }
    if (Number.isFinite(days)) bucket.dayUnits += Math.round(days * 1000);
    if (credit.date_recorded && credit.date_recorded > group.latestDate)
      group.latestDate = credit.date_recorded;
  }
  return [...groups.values()];
});

const filteredCreditGroups = computed(() => {
  const search = searchQuery.value.trim().toLowerCase();
  const groups = creditGroups.value.filter(
    (group) =>
      group.employee_name.toLowerCase().includes(search) ||
      group.records.some((record) =>
        (record.activity_name || "").toLowerCase().includes(search),
      ),
  );
  // Search finds employees; totals always include their complete recorded history.
  return arranged.value
    ? [...groups].sort((a, b) => a.employee_name.localeCompare(b.employee_name))
    : groups;
});
const detailCredits = computed(() =>
  credits.value.filter(
    (credit) => Number(credit.employee_id) === selectedEmployeeId.value,
  ),
);
const selectedEmployeeName = computed(
  () =>
    creditGroups.value.find(
      (group) => group.employee_id === selectedEmployeeId.value,
    )?.employee_name || `Employee #${selectedEmployeeId.value}`,
);

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
  creditsLoading.value = true;
  creditsError.value = "";
  try {
    credits.value = await getLeaveCredits();
  } catch (error) {
    console.error("Failed to load leave credits.", error);
    creditsError.value = "Unable to load leave credit records. Try refreshing the page.";
  } finally {
    creditsLoading.value = false;
  }
};

const applyCredit = async () => {
  if (submitting.value) return;
  if (!form.value.employee_id || !form.value.activity_name.trim() || !form.value.equivalent_leave_days || Number(form.value.equivalent_leave_days) <= 0) {
    notify(conversionError.value || "Enter valid rendered hours or minutes.");
    return;
  }
  submitting.value = true;
  try {
    await addLeaveCredit(form.value);
    notify("Leave credit recorded and added to the employee balance.");
    renderedTime.value = "";

    form.value = {
      employee_id: "",
      activity_name: "",
      hours_rendered: "",
      equivalent_leave_days: "",
      credit_type: "",
    };
    form.value.credit_type = "Service";

    await loadCredits();
  } catch (error: any) {
    console.error("STATUS:", error.response?.status);
    console.error("SERVER RESPONSE:", error.response?.data);

    notify(
      error.response?.data?.message ??
        JSON.stringify(
          error.response?.data?.errors ?? "Unable to apply leave credit.",
        ),
    );
  } finally { submitting.value = false; }
};

const revokeCredit = async (credit: LeaveCredit) => {
  if (revokingCreditId.value !== null) return;
  if (
    !(await confirmAction({
      title: "Revoke leave credit?",
      message: "Revoking this credit removes its contribution from the employee’s applicable balance when permitted by the balance rules. Continue?",
      confirmLabel: "Revoke credit",
    }))
  ) {
    return;
  }

  revokingCreditId.value = credit.credits_id;
  try {
    await axios.delete(`${API_BASE}/leave-credits/${credit.credits_id}`, { headers: authHeaders() });

    notify("Leave credit revoked successfully.");
    await loadCredits();
  } catch (error: any) {
    console.error("Failed to revoke credit:", error);
    notify(error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join("\n") || "Unable to revoke leave credit.");
  } finally {
    revokingCreditId.value = null;
  }
};

const formatDate = (date: string) => {
  if (!date) return "—";
  const parsedDate = new Date(date);
  if (Number.isNaN(parsedDate.getTime())) return date;
  return parsedDate.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

onMounted(() => {
  loadConversionReference();
  loadEmployees();
  loadCredits();
});
</script>

<style scoped>
.modal-card.credit-history-modal {
  max-width: 72rem;
}
.credit-total-line + .credit-total-line {
  margin-top: 0.3rem;
}
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

  box-shadow: 0 6px 18px rgba(23, 32, 51, 0.06);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;

  min-width: 0;
}

.neo-card:hover {
  box-shadow: 0 10px 24px rgba(23, 32, 51, 0.09);
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

  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
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

  box-shadow: 0 20px 40px rgba(23, 32, 51, 0.18);
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

  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
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
