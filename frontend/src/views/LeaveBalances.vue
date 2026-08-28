<template>
  <div class="dashboard-shell min-h-screen p-8">
    <div class="dashboard-content w-full space-y-6">

      <!-- ===================================================== -->
      <!-- HEADER -->
      <!-- ===================================================== -->

      <div class="neo-card w-full p-6">
        <h2 class="text-2xl font-bold text-white">
          Leave Balances
        </h2>

        <p class="text-gray-400 mt-1">
          View and manage employee leave balances.
        </p>
      </div>

      <!-- ===================================================== -->
      <!-- TABLE -->
      <!-- ===================================================== -->

      <div class="neo-card table-card w-full overflow-hidden">

        <!--
          Horizontal scrolling is only a fallback for very narrow
          screens. On desktop/zoomed-out views the table remains
          full width and stretches with the available space.
        -->
        <div class="table-wrapper w-full overflow-x-auto">

          <table class="leave-balance-table w-full">

            <!-- ================================================= -->
            <!-- TABLE HEADER -->
            <!-- ================================================= -->

            <thead class="bg-[#0B1420]">

              <tr class="text-left text-white">

                <th class="employee-column px-6 py-4">
                  Employee
                </th>

                <th class="balance-column px-6 py-4">
                  Service Credits
                </th>

                <th class="balance-column px-6 py-4">
                  Vacation Balance
                </th>

                <th class="balance-column px-6 py-4">
                  Sick Balance
                </th>

                <th class="total-column px-6 py-4">
                  Total Available
                </th>

                <th class="used-column px-6 py-4">
                  Used Leave
                </th>

                <th class="action-column px-6 py-4">
                  Action
                </th>

              </tr>

            </thead>

            <!-- ================================================= -->
            <!-- TABLE BODY -->
            <!-- ================================================= -->

            <tbody>

              <tr
                v-for="balance in balances"
                :key="balance.balance_id"
                class="balance-row border-t border-slate-800 hover:bg-white/5 transition"
              >

                <!-- Employee -->

                <td class="px-6 py-5 font-medium text-white employee-cell">

                  <div class="employee-name">
                    {{ balance.employee.last_name }},
                    {{ balance.employee.first_name }}
                  </div>

                </td>

                <!-- Service Credits -->

                <td class="px-6 py-5">

                  <span
                    :class="balanceColor(balance.service_credits)"
                  >
                    {{ balance.service_credits }}
                  </span>

                </td>

                <!-- Vacation Balance -->

                <td class="px-6 py-5">

                  <span
                    :class="balanceColor(balance.vacation_balance)"
                  >
                    {{ balance.vacation_balance }}
                  </span>

                </td>

                <!-- Sick Balance -->

                <td class="px-6 py-5">

                  <span
                    :class="balanceColor(balance.sick_balance)"
                  >
                    {{ balance.sick_balance }}
                  </span>

                </td>

                <!-- Total Available -->

                <td
                  class="px-6 py-5 font-semibold text-blue-400"
                >
                  {{ totalBalance(balance) }}
                </td>

                <!-- Used Leave -->

                <td
                  class="px-6 py-5 text-white font-semibold"
                >
                  {{ balance.used_leave }}
                </td>

                <!-- Action -->

                <td class="px-6 py-5">

                  <button
                    @click="openModal(balance)"
                    class="edit-button bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition"
                  >
                    Edit Balance
                  </button>

                  <button
                    @click="deleteBalance(balance.employee_id)"
                    class="edit-button bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition ml-2"
                  >
                    Delete Balance
                  </button>

                </td>

              </tr>

              <!-- ================================================= -->
              <!-- EMPTY STATE -->
              <!-- ================================================= -->

              <tr v-if="balances.length === 0">

                <td
                  colspan="7"
                  class="px-6 py-12 text-center text-gray-400"
                >
                  No leave balance records found.
                </td>

              </tr>

            </tbody>

          </table>

        </div>
      </div>

      <!-- ===================================================== -->
      <!-- EDIT MODAL -->
      <!-- ===================================================== -->

      <div
        v-if="showModal"
        class="modal-backdrop fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
        @click.self="showModal = false"
      >

        <div class="neo-card modal-card w-full max-w-lg p-6">

          <!-- Modal Header -->

          <h3 class="text-xl font-bold text-white mb-5">
            Edit Leave Balance
          </h3>

          <!-- ================================================= -->
          <!-- EMPLOYEE -->
          <!-- ================================================= -->

          <div class="mb-4">

            <label
              class="block text-sm font-semibold text-white mb-2"
            >
              Employee
            </label>

            <input
              disabled
              :value="
                selectedBalance.employee.last_name +
                ', ' +
                selectedBalance.employee.first_name
              "
              class="w-full border border-slate-700 rounded-lg px-3 py-2 bg-[#0B1420] text-gray-400"
            />

          </div>

          <!-- ================================================= -->
          <!-- SERVICE CREDITS -->
          <!-- ================================================= -->

          <div class="mb-4">

            <label
              class="block text-sm font-semibold text-white mb-2"
            >
              Service Credits
            </label>

            <input
              v-model="selectedBalance.service_credits"
              type="number"
              step="0.25"
              min="0"
              class="balance-input w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

          </div>

          <!-- ================================================= -->
          <!-- VACATION EARNED -->
          <!-- ================================================= -->

          <div class="mb-4">

            <label
              class="block text-sm font-semibold text-white mb-2"
            >
              Vacation Earned
            </label>

            <input
              v-model="selectedBalance.vacation_earned"
              type="number"
              step="0.25"
              class="balance-input w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

          </div>

          <!-- ================================================= -->
          <!-- SICK EARNED -->
          <!-- ================================================= -->

          <div class="mb-4">

            <label
              class="block text-sm font-semibold text-white mb-2"
            >
              Sick Earned
            </label>

            <input
              v-model="selectedBalance.sick_earned"
              type="number"
              step="0.25"
              class="balance-input w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

          </div>

          <!-- ================================================= -->
          <!-- VACATION BALANCE -->
          <!-- ================================================= -->

          <div class="mb-4">

            <label
              class="block text-sm font-semibold text-white mb-2"
            >
              Vacation Balance
            </label>

            <input
              v-model="selectedBalance.vacation_balance"
              type="number"
              step="0.25"
              class="balance-input w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

          </div>

          <!-- ================================================= -->
          <!-- SICK BALANCE -->
          <!-- ================================================= -->

          <div class="mb-4">

            <label
              class="block text-sm font-semibold text-white mb-2"
            >
              Sick Balance
            </label>

            <input
              v-model="selectedBalance.sick_balance"
              type="number"
              step="0.25"
              class="balance-input w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

          </div>

          <!-- ================================================= -->
          <!-- BUTTONS -->
          <!-- ================================================= -->

          <div class="flex justify-end gap-3 mt-6">

            <button
              @click="showModal = false"
              class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition"
            >
              Cancel
            </button>

            <button
              @click="updateBalance"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
            >
              Save
            </button>

          </div>

        </div>

      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "axios";
import { deleteBalance as deleteLeaveBalance } from "@/services/leaveBalance";

// ============================================================
// INTERFACES
// ============================================================

interface Employee {
  employee_id: number;
  first_name: string;
  last_name: string;
}

interface LeaveBalance {
  balance_id: number;
  employee_id: number;

  vacation_earned: number;
  sick_earned: number;

  vacation_balance: number;
  sick_balance: number;

  service_credits: number;

  used_leave: number;

  employee: Employee;
}

// ============================================================
// DATA
// ============================================================

const balances = ref<LeaveBalance[]>([]);


const showModal = ref(false);

const selectedBalance = ref<any>({
  employee: {
    first_name: "",
    last_name: "",
  },
});

// ============================================================
// LOAD BALANCES
// ============================================================

const loadBalances = async () => {
  try {
    const token = localStorage.getItem("token");

    const response = await axios.get(
      "https://enhs-leave-management-system.onrender.com/api/leave-balances",
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    balances.value = Array.isArray(response.data)
      ? response.data
      : [];

  } catch (error) {

    console.error(
      "Failed loading balances",
      error
    );

  }
};

// ============================================================
// OPEN EDIT MODAL
// ============================================================

const openModal = (balance: LeaveBalance) => {

  selectedBalance.value =
    JSON.parse(
      JSON.stringify(balance)
    );

  showModal.value = true;
};

// ============================================================
// UPDATE BALANCE
// ============================================================

const updateBalance = async () => {

  try {

    const token =
      localStorage.getItem("token");

    await axios.put(
      `https://enhs-leave-management-system.onrender.com/api/leave-balances/${selectedBalance.value.employee_id}`,
      {
        service_credits:
          selectedBalance.value.service_credits,

        vacation_earned:
          selectedBalance.value.vacation_earned,

        sick_earned:
          selectedBalance.value.sick_earned,

        vacation_balance:
          selectedBalance.value.vacation_balance,

        sick_balance:
          selectedBalance.value.sick_balance,
      },
      {
        headers: {
          Authorization:
            `Bearer ${token}`,
        },
      }
    );

    alert(
      "Leave balance updated successfully"
    );

    showModal.value = false;

    await loadBalances();

  } catch (error) {

    console.error(
      error
    );

    alert(
      "Failed updating balance"
    );

  }
};

const deleteBalance = async (
  employee_id: number
) => {

  if (
    !confirm(
      "Are you sure you want to delete this leave balance?"
    )
  ) {
    return;
  }

  try {

    await deleteLeaveBalance(
      employee_id
    );

    alert(
      "Leave balance deleted successfully"
    );

    await loadBalances();

  } catch (error) {

    console.error(
      error
    );

    alert(
      "Failed deleting balance"
    );

  }
};
// ============================================================
// TOTAL BALANCE
// ============================================================

const totalBalance = (
  balance: LeaveBalance
) => {

  return (
    Number(balance.vacation_balance) +
    Number(balance.sick_balance)
  ).toFixed(2);

};

// ============================================================
// BALANCE COLOR
// ============================================================

const balanceColor = (
  value: number | string
) => {

  const amount =
    Number(value);

  if (amount <= 0) {

    return "text-red-400 font-bold";

  }

  if (amount <= 5) {

    return "text-yellow-400 font-bold";

  }

  return "text-green-400 font-bold";

};

// ============================================================
// INITIAL LOAD
// ============================================================

onMounted(() => {

  loadBalances();

});
</script>

<style scoped>

/* ============================================================
   PAGE
   ============================================================ */

.dashboard-shell {
  background: #080d14;

  /*
   * IMPORTANT:
   * Do NOT give this page a max-width.
   *
   * The dashboard itself expands according to the available
   * sidebar/content width. This page now does the same.
   */
  width: 100%;
  min-width: 0;
}

/*
 * This is intentionally NOT max-w-7xl.
 *
 * It allows the content to stretch all the way across the
 * available dashboard area.
 */

.dashboard-content {
  width: 100%;
  min-width: 0;
}


/* ============================================================
   CARDS
   ============================================================ */

.neo-card {
  background: #111d2e;

  border: 1px solid #1e293b;

  border-radius: 1.4rem;

  box-shadow:
    0 10px 22px rgba(15, 23, 42, 0.04);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;

  /*
   * Critical for responsive layouts.
   */
  width: 100%;
  min-width: 0;
}

.neo-card:hover {
  box-shadow:
    0 14px 26px rgba(15, 23, 42, 0.06);
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

  /*
   * Only activates horizontal scrolling when the actual
   * viewport becomes too narrow.
   *
   * At normal desktop zoom and zoomed-out desktop views,
   * the table uses the full available width.
   */
  overflow-x: auto;
}


/* ============================================================
   LEAVE BALANCE TABLE
   ============================================================ */

.leave-balance-table {
  width: 100%;

  /*
   * AUTO is important.
   *
   * It allows the browser to distribute the available width
   * between columns instead of forcing a compressed layout.
   */
  table-layout: auto;

  /*
   * Prevents the table from becoming ridiculously narrow.
   * This is NOT a fixed desktop width.
   *
   * If the viewport has enough room, table = 100% width.
   * If the viewport is genuinely too small, wrapper scrolls.
   */
  min-width: 1050px;

  border-collapse: collapse;
}


/* ============================================================
   TABLE COLUMNS
   ============================================================ */

/*
 * These are minimum widths, not fixed widths.
 *
 * This means:
 *
 * Zoom OUT
 *   ↓
 * More viewport space
 *   ↓
 * Table stretches
 *   ↓
 * Columns expand naturally
 *
 * Zoom IN
 *   ↓
 * Less viewport space
 *   ↓
 * Table shrinks until minimum width
 *   ↓
 * Then horizontal scrolling protects the layout.
 */

.employee-column {
  min-width: 220px;
}

.balance-column {
  min-width: 150px;
}

.total-column {
  min-width: 155px;
}

.used-column {
  min-width: 130px;
}

.action-column {
  min-width: 170px;
}


/* ============================================================
   TABLE CELLS
   ============================================================ */

.leave-balance-table th,
.leave-balance-table td {
  vertical-align: middle;

  /*
   * Allows the table to distribute unused space.
   */
  white-space: nowrap;
}


/* ============================================================
   EMPLOYEE NAME
   ============================================================ */

.employee-name {
  white-space: nowrap;
}


/* ============================================================
   ROW
   ============================================================ */

.balance-row {
  transition:
    background-color 0.2s ease;
}


/* ============================================================
   BUTTON
   ============================================================ */

.edit-button {
  white-space: nowrap;

  transition:
    background-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.edit-button:hover {
  transform: translateY(-1px);
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

button:hover {
  transform: translateY(-1px);
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
   MODAL
   ============================================================ */

.modal-backdrop {
  overflow-y: auto;
}

.modal-card {
  max-height: calc(100vh - 2rem);
  overflow-y: auto;
}


/* ============================================================
   INPUTS
   ============================================================ */

.balance-input {
  min-width: 0;
}


/* ============================================================
   NARROW SCREEN
   ============================================================ */

@media (max-width: 900px) {

  .dashboard-shell {
    padding: 1.25rem;
  }

  .leave-balance-table {
    min-width: 1000px;
  }

}


/* ============================================================
   VERY SMALL SCREEN
   ============================================================ */

@media (max-width: 640px) {

  .dashboard-shell {
    padding: 1rem;
  }

  .neo-card {
    border-radius: 1rem;
  }

}

</style>