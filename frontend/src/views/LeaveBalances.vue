<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-2xl font-bold text-gray-800">Leave Balances</h2>

      <p class="text-gray-500 mt-1">View and manage employee leave balances.</p>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full">
        <thead class="bg-gray-100">
          <tr class="text-left text-gray-700">
            <th class="px-6 py-3">Employee</th>
            <th class="px-6 py-3">Vacation Balance</th>
            <th class="px-6 py-3">Sick Balance</th>
            <th class="px-6 py-3">Total Available</th>
            <th class="px-6 py-3">Used Leave</th>
            <th class="px-6 py-3">Action</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="balance in balances"
            :key="balance.balance_id"
            class="border-t hover:bg-gray-50"
          >
            <td class="px-6 py-4 font-medium text-gray-900">
              {{ balance.employee.last_name }},
              {{ balance.employee.first_name }}
            </td>

            <td class="px-6 py-4">
              <span :class="balanceColor(balance.vacation_balance)">
                {{ balance.vacation_balance }}
              </span>
            </td>

            <td class="px-6 py-4">
              <span :class="balanceColor(balance.sick_balance)">
                {{ balance.sick_balance }}
              </span>
            </td>

            <td class="px-6 py-4 font-semibold text-blue-700">
              {{ totalBalance(balance) }}
            </td>

            <td class="px-6 py-4 text-black font-semibold">
              {{ balance.used_leave }}
            </td>

            <td class="px-6 py-4">
              <button
                @click="openModal(balance)"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
              >
                Edit Balance
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- EDIT MODAL -->
    <!-- EDIT MODAL -->
<div
  v-if="showModal"
  class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
>

  <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">


    <h3 class="text-xl font-bold text-gray-900 mb-5">
      Edit Leave Balance
    </h3>


    <!-- Employee -->

    <div class="mb-4">

      <label class="block text-sm font-semibold text-gray-900 mb-2">
        Employee
      </label>

      <input
        disabled
        :value="
          selectedBalance.employee.last_name +
          ', ' +
          selectedBalance.employee.first_name
        "
        class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-900"
      >

    </div>



    <!-- Vacation Earned -->

    <div class="mb-4">

      <label class="block text-sm font-semibold text-gray-900 mb-2">
        Vacation Earned
      </label>

      <input
        v-model="selectedBalance.vacation_earned"
        type="number"
        step="0.25"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-gray-900 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >

    </div>




    <!-- Sick Earned -->

    <div class="mb-4">

      <label class="block text-sm font-semibold text-gray-900 mb-2">
        Sick Earned
      </label>

      <input
        v-model="selectedBalance.sick_earned"
        type="number"
        step="0.25"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-gray-900 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >

    </div>




    <!-- Vacation Balance -->

    <div class="mb-4">

      <label class="block text-sm font-semibold text-gray-900 mb-2">
        Vacation Balance
      </label>

      <input
        v-model="selectedBalance.vacation_balance"
        type="number"
        step="0.25"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-gray-900 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >

    </div>




    <!-- Sick Balance -->

    <div class="mb-4">

      <label class="block text-sm font-semibold text-gray-900 mb-2">
        Sick Balance
      </label>

      <input
        v-model="selectedBalance.sick_balance"
        type="number"
        step="0.25"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-gray-900 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >

    </div>




    <!-- Buttons -->

    <div class="flex justify-end gap-3 mt-6">


      <button
        @click="showModal=false"
        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-900 rounded-lg"
      >
        Cancel
      </button>



      <button
        @click="updateBalance"
        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg"
      >
        Save
      </button>


    </div>


  </div>

</div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "axios";

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

  used_leave: number;

  employee: Employee;
}

const balances = ref<LeaveBalance[]>([]);

const loadBalances = async () => {
  try {
    const token = localStorage.getItem("token");

    const response = await axios.get(
      "http://127.0.0.1:8000/api/leave-balances",
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
    );

    balances.value = response.data;
  } catch (error) {
    console.error("Failed loading balances", error);
  }
};

const showModal = ref(false);

const selectedBalance = ref<any>({
  employee: {},
});

const openModal = (balance: any) => {
  selectedBalance.value = JSON.parse(JSON.stringify(balance));

  showModal.value = true;
};

const updateBalance = async () => {
  try {
    const token = localStorage.getItem("token");

    await axios.put(
      `http://127.0.0.1:8000/api/leave-balances/${selectedBalance.value.employee_id}`,

      {
        vacation_earned: selectedBalance.value.vacation_earned,

        sick_earned: selectedBalance.value.sick_earned,

        vacation_balance: selectedBalance.value.vacation_balance,

        sick_balance: selectedBalance.value.sick_balance,
      },

      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
    );

    alert("Leave balance updated successfully");

    showModal.value = false;

    loadBalances();
  } catch (error) {
    console.error(error);

    alert("Failed updating balance");
  }
};

const totalBalance = (balance: any) => {
  return (
    Number(balance.vacation_balance) + Number(balance.sick_balance)
  ).toFixed(2);
};

const balanceColor = (value: number | string) => {
  const amount = Number(value);

  if (amount <= 0) return "text-red-600 font-bold";

  if (amount <= 5) return "text-yellow-600 font-bold";

  return "text-green-600 font-bold";
};

onMounted(() => {
  loadBalances();
});
</script>
