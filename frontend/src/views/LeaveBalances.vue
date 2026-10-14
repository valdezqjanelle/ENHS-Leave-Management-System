<template>
  <div class="dashboard-shell min-h-screen p-8">
    <div class="dashboard-content w-full space-y-6">

      <!-- HEADER -->
      <div class="w-full p-6 rounded-lg border" style="border-color: var(--border); background: var(--surface);">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 class="text-2xl font-semibold" style="color: var(--text);">Leave Balances</h2>
            <p class="mt-1" style="color: var(--text-muted);">View and manage employee leave balances.</p>
          </div>

          <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <button
              @click="loadBalances"
              type="button"
              class="w-full sm:w-auto flex-shrink-0 px-4 py-2 rounded-lg font-medium transition whitespace-nowrap text-white"
              style="background: var(--primary);"
              title="Reload balances (picks up newly applied credits)"
            >
              Refresh
            </button>

            <button
              @click="toggleSort"
              type="button"
              class="w-full sm:w-auto px-4 py-2 text-xs font-medium border rounded-full transition whitespace-nowrap"
style="border-color: var(--border); color: var(--text);"
              :title="arranged ? 'Unsort employees' : 'Sort employees alphabetically (A to Z)'"
            >
              Sort
            </button>
          </div>
        </div>
      </div>

      <SchoolYearManager @processed="loadBalances" />

      <!-- TABLE -->
      <div class="table-card w-full overflow-hidden rounded-lg border" style="border-color: var(--border); background: var(--surface);">
        <div class="p-4 sm:p-6 flex flex-col sm:flex-row gap-3">
          <input
            v-model="searchQuery"
            type="search"
            aria-label="Search employees by name"
            placeholder="Search employee name..."
            class="balance-search flex-1 min-w-0 rounded-lg px-4 py-2"
            style="border-color: var(--border); background: var(--surface); color: var(--text);"
          />
          <button
            @click="searchQuery = ''"
            type="button"
            class="px-4 py-2 rounded-lg font-medium text-white"
            style="background: var(--text-muted);"
          >Clear</button>
        </div>

        <div class="table-wrapper w-full overflow-x-auto">
          <table class="leave-balance-table w-full">
            <thead class="text-left font-semibold" style="background: var(--surface-muted); color: var(--text);">
              <tr>
                <th class="employee-column px-6 py-4">Employee</th>
                <th class="balance-column px-6 py-4">Local Credits</th>
                <th class="balance-column px-6 py-4">Vacation Balance</th>
                <th class="balance-column px-6 py-4">Sick Balance</th>
                <th class="action-column px-6 py-4">Action</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="balance in displayedBalances"
                :key="balance.employee_id"
                class="balance-row"
                style="border-color: var(--border);"
              >
                <td class="px-6 py-5 font-medium employee-cell" style="color: var(--text);">
                  <div class="employee-name">
                    {{ balance.employee.last_name }}, {{ balance.employee.first_name }}
                  </div>
                </td>

                <td class="px-6 py-5">
                  <span :class="balanceColor(balance.service_credits)">
                    {{ formatNumber(balance.service_credits) }}
                  </span>
                  <span class="balance-label">{{ balanceLabel(balance.service_credits) }}</span>
                </td>

                <td class="px-6 py-5">
                  <span :class="balanceColor(balance.vacation_balance)">
                    {{ formatNumber(balance.vacation_balance) }}
                  </span>
                  <span class="balance-label">{{ balanceLabel(balance.vacation_balance) }}</span>
                </td>

                <td class="px-6 py-5">
                  <span :class="balanceColor(balance.sick_balance)">
                    {{ formatNumber(balance.sick_balance) }}
                  </span>
                  <span class="balance-label">{{ balanceLabel(balance.sick_balance) }}</span>
                </td>

                <td class="px-4 py-4 action-cell">
                  <div class="flex flex-wrap items-center justify-center gap-2">
                    <button
                      @click="openModal(balance)"
                      type="button"
                      class="btn-action edit-button"
                    >
                      Edit Balance
                    </button>
                    <button @click="showHistory(balance)" type="button" class="btn-action history-button">History</button>

                    <button
                      @click="deleteBalance(balance.employee_id)"
                      type="button"
                      class="btn-action clear-button"
                    >
                      Clear Balance
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="displayedBalances.length === 0">
                <td colspan="5" class="px-6 py-12 text-center" style="color: var(--text-muted);">
                  {{ balancesLoading ? "Loading leave balances…" : balancesError || (searchQuery.trim() ? "No employees match your search." : "No leave balance records found.") }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="balance-pagination">
          <p class="pagination-summary" role="status" aria-live="polite" aria-atomic="true">
            <template v-if="balancesLoading">Loading leave balances…</template>
            <template v-else>
              Showing {{ paginationStart }}–{{ paginationEnd }} of {{ totalResults }} employees
              <span v-if="searchQuery.trim()"> (filtered from {{ balances.length }})</span>
            </template>
          </p>
          <nav class="pagination-controls" aria-label="Leave balances pagination">
            <button type="button" class="pagination-button" @click="previousPage"
              :disabled="balancesLoading || currentPage <= 1" aria-label="Previous page">
              Previous
            </button>
            <span class="pagination-page">
              {{ totalResults ? `Page ${currentPage} of ${totalPages}` : 'No results' }}
            </span>
            <button type="button" class="pagination-button" @click="nextPage"
              :disabled="balancesLoading || currentPage >= totalPages" aria-label="Next page">
              Next
            </button>
          </nav>
        </div>
      </div>

      <!-- EDIT MODAL -->
      <div
        v-if="showModal"
        class="modal-backdrop fixed inset-0 flex items-center justify-center z-50 p-4"
        @click.self="closeModal"
      >
        <div class="modal-card w-full max-w-lg p-6 rounded-lg border" style="border-color: var(--border); background: var(--surface);">
          <h3 class="text-xl font-semibold mb-5" style="color: var(--text);">Edit Leave Balance</h3>

          <div class="mb-4">
            <label class="block text-sm font-semibold mb-2" style="color: var(--text);">Employee</label>
            <input
              disabled
              :value="selectedBalance.employee.last_name + ', ' + selectedBalance.employee.first_name"
              class="balance-input w-full"
              style="border-color: var(--border); background: var(--surface-muted); color: var(--text);"
            />
          </div>

          <div class="mb-4">
            <label class="block text-sm font-semibold mb-2" style="color: var(--text);">Adjustment reason <span aria-hidden="true">*</span></label>
            <textarea v-model="adjustmentReason" maxlength="500" minlength="5" required rows="3" class="balance-input w-full" placeholder="Explain why these balances are being changed."></textarea>
          </div>

<div class="mb-4">
            <label class="block text-sm font-semibold text-white mb-2">Vacation Balance</label>
            <input
              v-model.number="selectedBalance.vacation_balance"
              type="number"
              step="0.001"
              min="0"
              class="balance-input w-full"
            />
          </div>

          <div class="mb-4">
            <label class="block text-sm font-semibold text-white mb-2">Sick Balance</label>
            <input
              v-model.number="selectedBalance.sick_balance"
              type="number"
              step="0.001"
              min="0"
              class="balance-input w-full"
            />
          </div>

          <div class="flex justify-end gap-3 mt-6">
            <button
              @click="closeModal"
              type="button"
              class="btn-action-lg cancel-button"
            >
              Cancel
            </button>

            <button
              @click="updateBalance"
              type="button"
              class="btn-action-lg save-button"
            >
              Save
            </button>
          </div>
        </div>
      </div>

      <div v-if="historyEmployee" class="modal-backdrop fixed inset-0 flex items-center justify-center z-50 p-4" @click.self="historyEmployee = null">
        <div class="neo-card modal-card w-full max-w-3xl p-6">
          <div class="flex justify-between items-center mb-4"><div><h3 class="text-xl font-bold text-white">Balance Adjustment History</h3><p class="text-gray-400">{{ historyEmployee.last_name }}, {{ historyEmployee.first_name }}</p></div><button type="button" class="btn-action-lg cancel-button" @click="historyEmployee = null">Close</button></div>
          <p v-if="historyLoading" class="text-gray-400 py-4">Loading history…</p>
          <p v-else-if="balanceHistory.length === 0" class="text-gray-400 py-4">No manual adjustments recorded.</p>
          <div v-else class="table-wrapper"><table class="leave-balance-table history-table"><thead><tr><th>When / administrator</th><th>Vacation</th><th>Sick</th><th>Reason</th></tr></thead><tbody><tr v-for="entry in balanceHistory" :key="entry.id"><td>{{ new Date(entry.created_at).toLocaleString() }}<br><span class="text-gray-400">{{ entry.administrator }}</span></td><td>{{ formatNumber(entry.vacation_before) }} → {{ formatNumber(entry.vacation_after) }}</td><td>{{ formatNumber(entry.sick_before) }} → {{ formatNumber(entry.sick_after) }}</td><td class="reason-cell">{{ entry.reason }}</td></tr></tbody></table></div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { confirmAction, notify } from "@/composables/useNotifications";
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import axios from "axios";
import SchoolYearManager from "@/components/SchoolYearManager.vue";
import { deleteBalance as deleteLeaveBalance } from "@/services/leaveBalance";

interface Employee {
  employee_id: number;
  first_name: string;
  last_name: string;
}

interface LeaveBalance {
  balance_id: number | null;
  employee_id: number;
  vacation_earned: number;
  sick_earned: number;
  vacation_balance: number;
  sick_balance: number;
  service_credits: number;
  used_leave: number;
  employee: Employee;
}

const balances = ref<LeaveBalance[]>([]);
const balancesLoading = ref(false);
const balancesError = ref("");
const arranged = ref(false);
const searchQuery = ref("");
const pageSize = 10;
const currentPage = ref(1);
const adjustmentReason = ref("");
const historyEmployee = ref<Employee | null>(null);
const balanceHistory = ref<any[]>([]);
const historyLoading = ref(false);

const toggleSort = () => {
  arranged.value = !arranged.value;
};

const filteredBalances = computed(() => {
  const terms = searchQuery.value.trim().toLowerCase().split(/[\s,]+/).filter(Boolean);
  const filtered = balances.value.filter((balance) => {
    const name = `${balance.employee?.first_name || ""} ${balance.employee?.last_name || ""}`.toLowerCase();
    return terms.every((term) => name.includes(term));
  });
  if (!arranged.value) return filtered;

  return [...filtered].sort((a, b) => {
    const aName = `${a.employee?.last_name || ""} ${a.employee?.first_name || ""}`
      .trim()
      .toLowerCase();

    const bName = `${b.employee?.last_name || ""} ${b.employee?.first_name || ""}`
      .trim()
      .toLowerCase();

    return aName.localeCompare(bName);
  });
});


const totalResults = computed(() => filteredBalances.value.length);
const totalPages = computed(() => Math.max(1, Math.ceil(totalResults.value / pageSize)));
const displayedBalances = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredBalances.value.slice(start, start + pageSize);
});
const paginationStart = computed(() => totalResults.value === 0 ? 0 : (currentPage.value - 1) * pageSize + 1);
const paginationEnd = computed(() => Math.min(currentPage.value * pageSize, totalResults.value));
const previousPage = () => {
  if (!balancesLoading.value && currentPage.value > 1) currentPage.value--;
};
const nextPage = () => {
  if (!balancesLoading.value && currentPage.value < totalPages.value) currentPage.value++;
};

// Search the full dataset first, then show at most ten matching employees.
watch([searchQuery, arranged], () => { currentPage.value = 1; });
// Keep the current page valid when a refresh changes the number of records.
watch(totalPages, (pages) => { currentPage.value = Math.min(currentPage.value, pages); });

const showModal = ref(false);

const selectedBalance = ref<LeaveBalance>({
  balance_id: null,
  employee_id: 0,
  vacation_earned: 0,
  sick_earned: 0,
  vacation_balance: 0,
  sick_balance: 0,
  service_credits: 0,
  used_leave: 0,
  employee: {
    employee_id: 0,
    first_name: "",
    last_name: ""
  },
});

const API_BASE = "https://enhs-leave-management-system.onrender.com/api";

const loadBalances = async () => {
  balancesLoading.value = true;
  balancesError.value = "";
  try {
    const token = localStorage.getItem("token");

    const response = await axios.get(`${API_BASE}/leave-balances`, {
      headers: {
        Authorization: `Bearer ${token}`
      },
    });

    const data = Array.isArray(response.data) ? response.data : [];

    balances.value = data.map((balance: any) => ({
      balance_id: balance.balance_id ?? null,
      employee_id: Number(balance.employee_id),
      vacation_earned: Number(balance.vacation_earned ?? 0),
      sick_earned: Number(balance.sick_earned ?? 0),
      vacation_balance: Number(balance.vacation_balance ?? 0),
      sick_balance: Number(balance.sick_balance ?? 0),
      service_credits: Number(balance.service_credits ?? 0),
      used_leave: Number(balance.used_leave ?? 0),

      employee: {
        employee_id: Number(
          balance.employee?.employee_id ?? balance.employee_id
        ),
        first_name: balance.employee?.first_name ?? "",
        last_name: balance.employee?.last_name ?? "",
      },
    }));
  } catch (error: any) {
    console.error(
      "Failed loading balances:",
      error.response?.data || error
    );
    balancesError.value = "Unable to load leave balances. Use Refresh to try again.";
  } finally {
    balancesLoading.value = false;
  }
};

const openModal = (balance: LeaveBalance) => {
  selectedBalance.value = {
    balance_id: balance.balance_id,
    employee_id: balance.employee_id,
    vacation_earned: Number(balance.vacation_earned ?? 0),
    sick_earned: Number(balance.sick_earned ?? 0),
    vacation_balance: Number(balance.vacation_balance ?? 0),
    sick_balance: Number(balance.sick_balance ?? 0),
    service_credits: Number(balance.service_credits ?? 0),
    used_leave: Number(balance.used_leave ?? 0),

    employee: {
      employee_id:
        balance.employee?.employee_id ?? balance.employee_id,
      first_name: balance.employee?.first_name ?? "",
      last_name: balance.employee?.last_name ?? "",
    },
  };

  showModal.value = true;
  adjustmentReason.value = "";
};

const closeModal = () => {
  showModal.value = false;
};

const updateBalance = async () => {
  try {
    const token = localStorage.getItem("token");

    const payload = {
      vacation_balance: Number(selectedBalance.value.vacation_balance ?? 0),
      sick_balance: Number(selectedBalance.value.sick_balance ?? 0),
      reason: adjustmentReason.value.trim(),
    };

    const values = [payload.vacation_balance, payload.sick_balance];

    if (
      values.some(
        (value) => !Number.isFinite(value) || value < 0
      )
    ) {
      notify(
        "All leave balance values must be valid numbers greater than or equal to zero."
      );
      return;
    }
    if (payload.reason.length < 5) { notify("Provide an adjustment reason of at least 5 characters."); return; }

    await axios.put(
      `${API_BASE}/leave-balances/${selectedBalance.value.employee_id}`,
      payload,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/json",
          "Content-Type": "application/json"
        }
      }
    );

    notify("Leave balance updated successfully.");

    closeModal();

    await loadBalances();
  } catch (error: any) {
    console.error(
      "Failed updating balance:",
      error.response?.data || error
    );

    const errors = error.response?.data?.errors;

    if (errors) {
      notify(Object.values(errors).flat().join("\n"));
      return;
    }

    notify(
      error.response?.data?.message ||
      "Failed updating balance."
    );
  }
};

const showHistory = async (balance: LeaveBalance) => {
  historyEmployee.value = balance.employee;
  historyLoading.value = true;
  try {
    const response = await axios.get(`${API_BASE}/leave-balances/${balance.employee_id}/history`, { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } });
    balanceHistory.value = response.data?.data ?? [];
  } catch (error: any) {
    historyEmployee.value = null;
    notify(error.response?.data?.message || "Unable to load balance history.");
  } finally { historyLoading.value = false; }
};

const deleteBalance = async (employee_id: number) => {
  const confirmed = await confirmAction({
    title: "Clear leave balance?",
    message:
      "Are you sure you want to clear this leave balance?\n\n" +
      "Vacation, Sick, and Local Credit balances will be reset to zero.",
    confirmLabel: "Clear balance",
  });

  if (!confirmed) return;
  const reason = window.prompt("Reason for clearing vacation and sick balances:")?.trim();
  if (!reason || reason.length < 5) { notify("Enter a reason of at least 5 characters."); return; }

  try {
    await deleteLeaveBalance(employee_id, reason);

    notify("Leave balance cleared successfully.");

    await loadBalances();
  } catch (error: any) {
    console.error(
      "Failed clearing balance:",
      error.response?.data || error
    );

    notify(
      error.response?.data?.message ||
      "Failed clearing balance."
    );
    balancesError.value = "Unable to load leave balances. Use Refresh to try again.";
  } finally {
    balancesLoading.value = false;
  }
};

const formatNumber = (
  value: number | string | null | undefined
) =>
  Number(value ?? 0).toFixed(3);

/*
 * TABLE BALANCE COLORS
 *
 * 0       = Red
 * 1 - 10  = Dark Yellow
 * 11+     = Green
 */
const balanceColor = (
  value: number | string | null | undefined
) => {
  const amount = Number(value ?? 0);

  if (amount <= 0) return "balance-zero";
  if (amount <= 10) return "balance-low";
  return "balance-good";
};

const balanceLabel = (value: number | string | null | undefined) => {
  const amount = Number(value ?? 0);
  if (amount <= 0) return "None";
  if (amount <= 10) return "Low";
  return "Available";
};

/* Auto-reload balances whenever the tab regains focus so newly
   applied credits from LeaveCredits.vue show up immediately. */
const handleVisibility = () => {
  if (document.visibilityState === "visible") {
    loadBalances();
  }
};

onMounted(() => {
  loadBalances();
  document.addEventListener(
    "visibilitychange",
    handleVisibility
  );
});

onUnmounted(() => {
  document.removeEventListener(
    "visibilitychange",
    handleVisibility
  );
});
</script>

<style scoped>

.balance-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border);
  background: var(--surface);
}
.pagination-summary { color: var(--text-muted); font-size: .85rem; line-height: 1.5; }
.pagination-controls { display: flex; align-items: center; gap: .75rem; }
.pagination-page { color: var(--text-muted); font-size: .8rem; white-space: nowrap; }
.pagination-button {
  padding: .55rem .85rem;
  border: 1px solid var(--border);
  border-radius: .5rem;
  background: var(--surface-muted);
  color: var(--text);
  font-size: .8rem;
  font-weight: 600;
  cursor: pointer;
}
.pagination-button:hover:not(:disabled) { border-color: var(--primary); color: var(--primary); }
.pagination-button:disabled { opacity: .45; cursor: not-allowed; }
.pagination-button:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
@media (max-width: 640px) {
  .balance-pagination { padding: 1rem; flex-direction: column; align-items: stretch; }
  .pagination-controls { justify-content: space-between; }
}

.balance-search {
  color: var(--text);
  background: var(--surface-muted);
  border: 1px solid var(--border, #c8d8eb);
}
.balance-search:focus {
  outline: 2px solid var(--primary, #2563eb);
  outline-offset: 1px;
}


/* ============================================================
   PAGE
   Dashboard reference theme
   ============================================================ */

.dashboard-shell {
  background: var(--app-bg);

  width: 100%;

  min-width: 0;

  min-height: 100vh;
}


.dashboard-content {
  width: 100%;

  min-width: 0;
}


/* ============================================================
   CARDS
   Matches Dashboard.vue
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

  width: 100%;

  min-width: 0;
}


.neo-card:hover {
  box-shadow:
    0 10px 24px rgba(23, 32, 51, 0.09);
}


/* ============================================================
   TEXT
   Matches Dashboard.vue
   ============================================================ */

.neo-card .text-white {
  color: var(--text) !important;
}

/*
 * Buttons (e.g. the Arrange button) should always keep white
 * label text, even inside a .neo-card where the rule above
 * would otherwise repaint text-white to the theme text color.
 */
.neo-card button.text-white {
  color: #ffffff !important;
}


.neo-card .text-gray-300,
.neo-card .text-gray-400,
.neo-card .text-gray-500 {
  color: var(--text-muted) !important;
}


/* ============================================================
   TABLE CARD
   ============================================================ */

.table-card {
  width: 100%;

  min-width: 0;
}


/* ============================================================
   TABLE WRAPPER
   ============================================================ */

.table-wrapper {
  width: 100%;

  min-width: 0;

  overflow-x: auto;

  overflow-y: hidden;

  -webkit-overflow-scrolling: touch;
}


/* ============================================================
   TABLE
   Dashboard-style light table
   ============================================================ */

.leave-balance-table {
  width: 100%;

  table-layout: fixed;

  min-width: 940px;

  border-collapse: collapse;
}


/* ============================================================
   TABLE HEADER
   ============================================================ */

.leave-balance-table thead {
  background: var(--surface-muted);
}


.leave-balance-table th {
  color: var(--text-muted) !important;

  font-size: 0.72rem;

  font-weight: 600;

  text-transform: uppercase;

  letter-spacing: 0.05em;

  border-bottom: 1px solid var(--border);
}


/* ============================================================
   TABLE BODY
   ============================================================ */

.leave-balance-table tbody tr {
  border-top: 1px solid var(--border);

  transition:
    background-color 0.2s ease;
}


.leave-balance-table tbody tr:hover {
  background: #f3f7fc;
}


.leave-balance-table td {
  color: var(--text);
}


/* ============================================================
   TABLE COLUMNS
   ============================================================ */

.employee-column {
  width: 23%;
}


.balance-column {
  width: 16%;
}


.action-column {
  width: 29%;

  text-align: center;
}


/* ============================================================
   TABLE CELLS
   ============================================================ */

.leave-balance-table th,
.leave-balance-table td {
  vertical-align: middle;

  white-space: nowrap;
}


/* ============================================================
   EMPLOYEE
   ============================================================ */

.employee-cell {
  min-width: 0;
}


.employee-name {
  color: var(--text);

  white-space: nowrap;
}


/* ============================================================
   TOTAL / USED VALUES
   ============================================================ */

/* TOTAL AVAILABLE = BLUE */

/* ============================================================
   BALANCE COLORS
   ============================================================ */

/* 0 = RED */
.balance-zero {
  color: #dc2626;

  font-weight: 700;
}


/* 1 - 10 = DARK YELLOW */
.balance-low {
  color: #e3890b;

  font-weight: 700;
}


/* 11+ = GREEN */
.balance-good {
  color: #16a34a;

  font-weight: 700;
}

.balance-label { display: block; color: var(--text-muted); font-size: 0.7rem; font-weight: 500; }
.history-button { background: #1e40af; }
.reason-cell { white-space: normal !important; min-width: 220px; }
.history-table { min-width: 780px; }


/* ============================================================
   BUTTONS
   ============================================================ */

.btn-action {
  display: inline-flex;

  align-items: center;

  justify-content: center;

  padding:
    0.5rem
    0.85rem;

  font-size: 0.75rem;

  font-weight: 600;

  line-height: 1.25rem;

  color: #ffffff;

  border-radius: 0.5rem;

  white-space: nowrap;

  transition:
    background-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}


.btn-action:hover {
  transform: translateY(-1px);
}


.btn-action:active {
  transform: scale(0.97);
}


/* ============================================================
   EDIT BUTTON
   Dashboard primary blue
   ============================================================ */

.edit-button {
  background: var(--primary);
}


.edit-button:hover {
  background: var(--primary-hover);
}


/* ============================================================
   CLEAR BUTTON
   Dashboard danger red
   ============================================================ */

.clear-button {
  background: #dc2626;
}


.clear-button:hover {
  background: #b91c1c;
}


/* ============================================================
   MODAL BACKDROP
   ============================================================ */

.modal-backdrop {
  background: rgba(23, 32, 51, 0.55);

  overflow-y: auto;
}


/* ============================================================
   MODAL
   ============================================================ */

.modal-card {
  max-height: calc(100vh - 2rem);

  overflow-y: auto;
}


/* ============================================================
   MODAL INPUTS
   ============================================================ */

.balance-input {
  min-width: 0;

  border: 1px solid #c8d8eb;

  border-radius: 0.6rem;

  padding:
    0.65rem
    0.8rem;

  color: var(--text);

  background: var(--surface-muted);

  outline: none;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    background-color 0.2s ease;
}


.balance-input::placeholder {
  color: var(--text-muted);
}


.balance-input:focus {
  border-color: #7aa7e8;

  background: #ffffff;

  box-shadow:
    0 0 0 3px rgba(37, 99, 235, 0.12);
}


.balance-input:disabled {
  color: var(--text-muted);

  background: #f3f7fc;

  cursor: not-allowed;
}


/* ============================================================
   MODAL BUTTONS
   ============================================================ */

.btn-action-lg {
  display: inline-flex;

  align-items: center;

  justify-content: center;

  padding:
    0.5rem
    1.1rem;

  font-size: 0.8rem;

  font-weight: 600;

  color: #ffffff;

  border-radius: 0.5rem;

  white-space: nowrap;

  transition:
    background-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}


.btn-action-lg:hover {
  transform: translateY(-1px);
}


.btn-action-lg:active {
  transform: scale(0.98);
}


/* ============================================================
   CANCEL
   Soft Dashboard gray
   ============================================================ */

.cancel-button {
  background: #e2e8f0;

  color: #334155;
}


.cancel-button:hover {
  background: #cbd5e1;
}


/* ============================================================
   SAVE
   Dashboard primary blue
   ============================================================ */

.save-button {
  background: var(--primary);
}


.save-button:hover {
  background: var(--primary-hover);
}


/* ============================================================
   GENERAL BUTTONS
   ============================================================ */

button {
  transition:
    background-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}


/* ============================================================
   TYPOGRAPHY
   ============================================================ */

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {
  letter-spacing: -0.01em;
}


/* ============================================================
   RESPONSIVE
   ============================================================ */

@media (max-width: 900px) {

  .dashboard-shell {
    padding: 1.25rem;
  }

  .leave-balance-table {
    min-width: 940px;
  }

}


@media (max-width: 640px) {

  .dashboard-shell {
    padding: 1rem;
  }

  .neo-card {
    border-radius: 1rem;
  }

}

</style>
