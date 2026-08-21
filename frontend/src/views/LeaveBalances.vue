<template>
  <div class="dashboard-shell p-8 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">

      <!-- Header -->
      <div class="neo-card p-6">
        <h2 class="text-2xl font-bold text-white">Leave Balances</h2>
        <p class="text-gray-400 mt-1">
          View and manage employee leave balances.
        </p>
      </div>

      <!-- Table -->
      <div class="neo-card overflow-hidden">
        <table class="min-w-full">
          <thead class="bg-[#0B1420]">
            <tr class="text-left text-white">
              <th class="px-6 py-3">Employee</th>
              <th class="px-6 py-3">Service Credits</th>
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
              class="border-t border-slate-800 hover:bg-white/5 transition"
            >
              <!-- Employee -->
              <td class="px-6 py-4 font-medium text-white">
                {{ balance.employee.last_name }},
                {{ balance.employee.first_name }}
              </td>

              <!-- Service Credits -->
              <td class="px-6 py-4">
                <span :class="balanceColor(balance.service_credits)">
                  {{ balance.service_credits }}
                </span>
              </td>

              <!-- Vacation Balance -->
              <td class="px-6 py-4">
                <span :class="balanceColor(balance.vacation_balance)">
                  {{ balance.vacation_balance }}
                </span>
              </td>

              <!-- Sick Balance -->
              <td class="px-6 py-4">
                <span :class="balanceColor(balance.sick_balance)">
                  {{ balance.sick_balance }}
                </span>
              </td>

              <!-- Total Available -->
              <td class="px-6 py-4 font-semibold text-blue-400">
                {{ totalBalance(balance) }}
              </td>

              <!-- Used Leave -->
              <td class="px-6 py-4 text-white font-semibold">
                {{ balance.used_leave }}
              </td>

              <!-- Action -->
              <td class="px-6 py-4">
                <button
                  @click="openModal(balance)"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition"
                >
                  Edit Balance
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- EDIT MODAL -->
      <div
        v-if="showModal"
        class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50"
      >
        <div class="neo-card p-6 w-full max-w-lg">
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
              class="w-full border border-slate-700 rounded-lg px-3 py-2 bg-[#0B1420] text-gray-400"
            />
          </div>

          <!-- Service Credits -->
          <div class="mb-4">
            <label class="block text-sm font-semibold text-white mb-2">
              Service Credits
            </label>

            <input
              v-model="selectedBalance.service_credits"
              type="number"
              step="0.25"
              min="0"
              class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Vacation Earned -->
          <div class="mb-4">
            <label class="block text-sm font-semibold text-white mb-2">
              Vacation Earned
            </label>

            <input
              v-model="selectedBalance.vacation_earned"
              type="number"
              step="0.25"
              class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Sick Earned -->
          <div class="mb-4">
            <label class="block text-sm font-semibold text-white mb-2">
              Sick Earned
            </label>

            <input
              v-model="selectedBalance.sick_earned"
              type="number"
              step="0.25"
              class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Vacation Balance -->
          <div class="mb-4">
            <label class="block text-sm font-semibold text-white mb-2">
              Vacation Balance
            </label>

            <input
              v-model="selectedBalance.vacation_balance"
              type="number"
              step="0.25"
              class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Sick Balance -->
          <div class="mb-4">
            <label class="block text-sm font-semibold text-white mb-2">
              Sick Balance
            </label>

            <input
              v-model="selectedBalance.sick_balance"
              type="number"
              step="0.25"
              class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Buttons -->
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

const balances = ref<LeaveBalance[]>([]);

const showModal = ref(false);

const selectedBalance = ref<any>({
  employee: {},
});

const loadBalances = async () => {
  try {
    const token = localStorage.getItem("token");

    const response = await axios.get(
      "http://127.0.0.1:8000/api/leave-balances",
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    balances.value = response.data;
  } catch (error) {
    console.error("Failed loading balances", error);
  }
};

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
        service_credits: selectedBalance.value.service_credits,
        vacation_earned: selectedBalance.value.vacation_earned,
        sick_earned: selectedBalance.value.sick_earned,
        vacation_balance: selectedBalance.value.vacation_balance,
        sick_balance: selectedBalance.value.sick_balance,
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
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
    Number(balance.vacation_balance) +
    Number(balance.sick_balance)
  ).toFixed(2);
};

const balanceColor = (value: number | string) => {
  const amount = Number(value);

  if (amount <= 0) {
    return "text-red-400 font-bold";
  }

  if (amount <= 5) {
    return "text-yellow-400 font-bold";
  }

  return "text-green-400 font-bold";
};

onMounted(() => {
  loadBalances();
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