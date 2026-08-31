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
                :key="balance.employee_id"
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

                  <span :class="balanceColor(balance.service_credits)">
                    {{ formatNumber(balance.service_credits) }}
                  </span>

                </td>


                <!-- Vacation Balance -->

                <td class="px-6 py-5">

                  <span :class="balanceColor(balance.vacation_balance)">
                    {{ formatNumber(balance.vacation_balance) }}
                  </span>

                </td>


                <!-- Sick Balance -->

                <td class="px-6 py-5">

                  <span :class="balanceColor(balance.sick_balance)">
                    {{ formatNumber(balance.sick_balance) }}
                  </span>

                </td>


                <!-- Total Available -->

                <td class="px-6 py-5 font-semibold text-blue-400">

                  {{ totalBalance(balance) }}

                </td>


                <!-- Used Leave -->

                <td class="px-6 py-5 text-white font-semibold">

                  {{ formatNumber(balance.used_leave) }}

                </td>


                <!-- Action -->

                <td class="px-6 py-5">

                  <div class="flex items-center gap-2">

                    <button
                      @click="openModal(balance)"
                      type="button"
                      class="btn-action bg-blue-600 hover:bg-blue-700"
                    >
                      Edit Balance
                    </button>

                    <button
                      @click="deleteBalance(balance.employee_id)"
                      type="button"
                      class="btn-action bg-red-600 hover:bg-red-700"
                    >
                      Clear Balance
                    </button>

                  </div>

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
        @click.self="closeModal"
      >

        <div class="neo-card modal-card w-full max-w-lg p-6">

          <!-- Modal Header -->

          <h3 class="text-xl font-bold text-white mb-5">
            Edit Leave Balance
          </h3>


          <!-- Employee -->

          <div class="mb-4">

            <label class="block text-sm font-semibold text-white mb-2">
              Employee
            </label>

            <input
              disabled
              :value="
                selectedBalance.employee.last_name +
                ', ' +
                selectedBalance.employee.first_name
              "
              class="w-full border border-slate-700 rounded-full px-3 py-2 bg-[#0B1420] text-gray-400"
            />

          </div>


          <!-- Service Credits -->

          <div class="mb-4">

            <label class="block text-sm font-semibold text-white mb-2">
              Service Credits
            </label>

            <input
              v-model.number="selectedBalance.service_credits"
              type="number"
              step="0.25"
              min="0"
              class="balance-input w-full border border-slate-700 rounded-full px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

          </div>


          <!-- Vacation Earned -->

          <div class="mb-4">

            <label class="block text-sm font-semibold text-white mb-2">
              Vacation Earned
            </label>

            <input
              v-model.number="selectedBalance.vacation_earned"
              type="number"
              step="0.25"
              min="0"
              class="balance-input w-full border border-slate-700 rounded-full px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

          </div>


          <!-- Sick Earned -->

          <div class="mb-4">

            <label class="block text-sm font-semibold text-white mb-2">
              Sick Earned
            </label>

            <input
              v-model.number="selectedBalance.sick_earned"
              type="number"
              step="0.25"
              min="0"
              class="balance-input w-full border border-slate-700 rounded-full px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

          </div>


          <!-- Vacation Balance -->

          <div class="mb-4">

            <label class="block text-sm font-semibold text-white mb-2">
              Vacation Balance
            </label>

            <input
              v-model.number="selectedBalance.vacation_balance"
              type="number"
              step="0.25"
              min="0"
              class="balance-input w-full border border-slate-700 rounded-full px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

          </div>


          <!-- Sick Balance -->

          <div class="mb-4">

            <label class="block text-sm font-semibold text-white mb-2">
              Sick Balance
            </label>

            <input
              v-model.number="selectedBalance.sick_balance"
              type="number"
              step="0.25"
              min="0"
              class="balance-input w-full border border-slate-700 rounded-full px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

          </div>


          <!-- Buttons -->

          <div class="flex justify-end gap-3 mt-6">

            <button
              @click="closeModal"
              type="button"
              class="btn-action-lg bg-slate-700 hover:bg-slate-600"
            >
              Cancel
            </button>

            <button
              @click="updateBalance"
              type="button"
              class="btn-action-lg bg-blue-600 hover:bg-blue-700"
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


/* ============================================================
   TYPES
   ============================================================ */

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


/* ============================================================
   DATA
   ============================================================ */

const balances = ref<LeaveBalance[]>([]);

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
    last_name: "",
  },
});


/* ============================================================
   API BASE URL
   ============================================================ */

const API_BASE =
  "https://enhs-leave-management-system.onrender.com/api";


/* ============================================================
   LOAD BALANCES
   ============================================================ */

const loadBalances = async () => {

  try {

    const token = localStorage.getItem("token");

    const response = await axios.get(
      `${API_BASE}/leave-balances`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    const data = Array.isArray(response.data)
      ? response.data
      : [];

    balances.value = data.map((balance: any) => ({
      balance_id: balance.balance_id ?? null,

      employee_id: Number(balance.employee_id),

      vacation_earned:
        Number(balance.vacation_earned ?? 0),

      sick_earned:
        Number(balance.sick_earned ?? 0),

      vacation_balance:
        Number(balance.vacation_balance ?? 0),

      sick_balance:
        Number(balance.sick_balance ?? 0),

      service_credits:
        Number(balance.service_credits ?? 0),

      used_leave:
        Number(balance.used_leave ?? 0),

      employee: {
        employee_id:
          Number(
            balance.employee?.employee_id ??
            balance.employee_id
          ),

        first_name:
          balance.employee?.first_name ?? "",

        last_name:
          balance.employee?.last_name ?? "",
      },
    }));

  } catch (error: any) {

    console.error(
      "Failed loading balances:",
      error.response?.data || error
    );

  }

};


/* ============================================================
   OPEN EDIT MODAL
   ============================================================ */

const openModal = (balance: LeaveBalance) => {

  selectedBalance.value = {
    balance_id: balance.balance_id,

    employee_id: balance.employee_id,

    vacation_earned:
      Number(balance.vacation_earned ?? 0),

    sick_earned:
      Number(balance.sick_earned ?? 0),

    vacation_balance:
      Number(balance.vacation_balance ?? 0),

    sick_balance:
      Number(balance.sick_balance ?? 0),

    service_credits:
      Number(balance.service_credits ?? 0),

    used_leave:
      Number(balance.used_leave ?? 0),

    employee: {
      employee_id:
        balance.employee?.employee_id ??
        balance.employee_id,

      first_name:
        balance.employee?.first_name ?? "",

      last_name:
        balance.employee?.last_name ?? "",
    },
  };

  showModal.value = true;
};


/* ============================================================
   CLOSE MODAL
   ============================================================ */

const closeModal = () => {

  showModal.value = false;

};


/* ============================================================
   UPDATE BALANCE
   ============================================================ */

const updateBalance = async () => {

  try {

    const token = localStorage.getItem("token");

    /*
    |--------------------------------------------------------------------------
    | Normalize all values before sending.
    |--------------------------------------------------------------------------
    */

    const payload = {

      vacation_earned:
        Number(selectedBalance.value.vacation_earned ?? 0),

      sick_earned:
        Number(selectedBalance.value.sick_earned ?? 0),

      vacation_balance:
        Number(selectedBalance.value.vacation_balance ?? 0),

      sick_balance:
        Number(selectedBalance.value.sick_balance ?? 0),

      service_credits:
        Number(selectedBalance.value.service_credits ?? 0),

    };


    /*
    |--------------------------------------------------------------------------
    | Client-side validation
    |--------------------------------------------------------------------------
    */

    const values = Object.values(payload);

    if (
      values.some(
        (value) =>
          !Number.isFinite(value) ||
          value < 0
      )
    ) {

      alert(
        "All leave balance values must be valid numbers greater than or equal to zero."
      );

      return;

    }


    /*
    |--------------------------------------------------------------------------
    | Update backend
    |--------------------------------------------------------------------------
    */

    await axios.put(
      `${API_BASE}/leave-balances/${selectedBalance.value.employee_id}`,
      payload,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/json",
          "Content-Type": "application/json",
        },
      }
    );


    alert(
      "Leave balance updated successfully."
    );


    closeModal();

    await loadBalances();

  } catch (error: any) {

    console.error(
      "Failed updating balance:",
      error.response?.data || error
    );


    /*
    |--------------------------------------------------------------------------
    | Show actual Laravel validation error
    |--------------------------------------------------------------------------
    */

    const errors =
      error.response?.data?.errors;

    if (errors) {

      const messages = Object.values(errors)
        .flat()
        .join("\n");

      alert(messages);

      return;
    }


    alert(
      error.response?.data?.message ||
      "Failed updating balance."
    );

  }

};


/* ============================================================
   CLEAR BALANCE
   ============================================================ */

const deleteBalance = async (
  employee_id: number
) => {

  const confirmed = confirm(
    "Are you sure you want to clear this leave balance?\n\n" +
    "Vacation, Sick, and Service Credit balances will be reset to zero."
  );

  if (!confirmed) {
    return;
  }


  try {

    await deleteLeaveBalance(employee_id);


    alert(
      "Leave balance cleared successfully."
    );


    await loadBalances();

  } catch (error: any) {

    console.error(
      "Failed clearing balance:",
      error.response?.data || error
    );


    alert(
      error.response?.data?.message ||
      "Failed clearing balance."
    );

  }

};


/* ============================================================
   TOTAL BALANCE
   ============================================================ */

const totalBalance = (
  balance: LeaveBalance
) => {

  /*
   * Total Available represents the employee's
   * actual leave balances.
   *
   * Service Credits are displayed separately because
   * they are not yet Vacation/Sick leave.
   */

  return (
    Number(balance.vacation_balance ?? 0) +
    Number(balance.sick_balance ?? 0)
  ).toFixed(2);

};


/* ============================================================
   FORMAT NUMBER
   ============================================================ */

const formatNumber = (
  value: number | string | null | undefined
) => {

  return Number(value ?? 0).toFixed(2);

};


/* ============================================================
   BALANCE COLOR
   ============================================================ */

const balanceColor = (
  value: number | string | null | undefined
) => {

  const amount = Number(value ?? 0);

  if (amount <= 0) {

    return "text-red-400 font-bold";

  }

  if (amount <= 5) {

    return "text-yellow-400 font-bold";

  }

  return "text-green-400 font-bold";

};


/* ============================================================
   INITIAL LOAD
   ============================================================ */

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

  overflow-x: auto;

  overflow-y: hidden;

  -webkit-overflow-scrolling: touch;
}


/* ============================================================
   TABLE
   ============================================================ */

.leave-balance-table {
  width: 100%;

  table-layout: auto;

  min-width: 1050px;

  border-collapse: collapse;
}


/* ============================================================
   TABLE COLUMNS
   ============================================================ */

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
  min-width: 190px;
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
  min-width: 220px;
}


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
   BUTTONS
   ============================================================ */

.btn-action {
  display: inline-flex;

  align-items: center;

  justify-content: center;

  padding:
    0.375rem
    0.85rem;

  font-size: 0.75rem;

  font-weight: 500;

  line-height: 1.25rem;

  color: #fff;

  border-radius: 9999px;

  white-space: nowrap;

  transition:
    background-color 0.15s ease,
    transform 0.1s ease;
}


.btn-action:hover {
  transform: translateY(-1px);
}


.btn-action:active {
  transform: scale(0.97);
}


.btn-action-lg {
  display: inline-flex;

  align-items: center;

  justify-content: center;

  padding:
    0.5rem
    1.1rem;

  font-size: 0.8rem;

  font-weight: 500;

  color: #fff;

  border-radius: 9999px;

  white-space: nowrap;

  transition:
    background-color 0.15s ease,
    transform 0.1s ease;
}


.btn-action-lg:hover {
  transform: translateY(-1px);
}


.btn-action-lg:active {
  transform: scale(0.98);
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