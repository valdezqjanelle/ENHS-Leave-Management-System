<template>
  <div class="dashboard-shell p-8 min-h-screen space-y-8">

    <!-- Header -->
    <div class="neo-card bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-semibold text-white">
            My Records
          </h2>

          <p class="text-sm text-white mt-1">
            View your employee information and leave balances.
          </p>
        </div>
      </div>
    </div>

    <!-- Profile -->
    <div class="neo-card rounded-lg shadow p-6">

      <div class="flex items-center gap-5">

        <div
          class="w-20 h-20 rounded-full bg-blue-600 text-white flex items-center justify-center text-3xl font-bold"
        >
          {{ initials }}
        </div>

        <div>

          <h3 class="text-xl font-semibold text-white">
            {{ fullName }}
          </h3>

          <p class="text-white">
            {{ employee.position }}
          </p>

          <span
            class="inline-block mt-2 px-3 py-1 rounded-full text-sm bg-green-100 text-green-700"
          >
            {{ employee.employment_status }}
          </span>

        </div>

      </div>

    </div>

    <!-- Leave Balance -->

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <div class="neo-card  rounded-lg shadow p-5">

        <h4 class="text-white text-sm">
          Vacation Leave
        </h4>

        <p class="text-3xl font-bold text-blue-600 mt-2">
          {{ leaveBalance.vacation_balance }}
        </p>

      </div>

      <div class="neo-card rounded-lg shadow p-5">

        <h4 class="text-white text-sm">
          Sick Leave
        </h4>

        <p class="text-3xl font-bold text-red-600 mt-2">
          {{ leaveBalance.sick_balance }}
        </p>

      </div>

      <div class="neo-card rounded-lg shadow p-5">

        <h4 class="text-white text-sm">
          Used Leave
        </h4>

        <p class="text-3xl font-bold text-green-600 mt-2">
          {{ leaveBalance.used_leave }}
        </p>

      </div>

    </div>

    <!-- Recent Leave Applications -->

<div class="neo-card rounded-lg shadow">

    <div class="px-6 py-4 border-b">

        <h3 class="text-lg font-semibold text-white">
            Recent Leave Applications
        </h3>

    </div>

    <div class="overflow-x-auto">

        <table class="min-w-full divide-y divide-gray-200">

            <thead class="bg-gray-50">

                <tr>

                    <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase">
                        Leave Type
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase">
                        Date Filed
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase">
                        Days
                    </th>

                      <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase">
                        Status
                    </th>

                </tr>

            </thead>

            <tbody class="neo-card divide-y divide-black">

                <tr
                    v-for="leave in recentLeaves"
                    :key="leave.leave_id"
                >

                    <td class="px-6 py-4 text-black font-medium">

                        {{ leave.leaveType?.leave_type_name }}

                    </td>

                    <td class="px-6 py-4 text-black ">

                        {{ leave.date_filed }}

                    </td>

                    <td class="px-6 py-4 text-black">

                        {{ leave.number_of_days }}

                    </td>

                    <td class="px-6 py-4 text-black">

                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold"
                            :class="statusClass(leave.final_status)"
                        >

                            {{ leave.final_status }}

                        </span>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

    <!-- Personal Information -->

    <div class="neo-card rounded-lg shadow">

      <div class="border-b px-6 py-4">

        <h3 class="text-lg font-semibold text-white">
          Personal Information
        </h3>

      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">

        <div>
          <label class="text-white text-sm">
            Employee Code
          </label>

          <p class="font-medium">
            {{ employee.employee_code }}
          </p>
        </div>

        <div>
          <label class="text-white text-sm">
            Email
          </label>

          <p class="font-medium">
            {{ employee.email }}
          </p>
        </div>

        <div>
          <label class="text-white text-sm">
            First Name
          </label>

          <p class="font-medium">
            {{ employee.first_name }}
          </p>
        </div>

        <div>
          <label class="text-white text-sm">
            Middle Name
          </label>

          <p class="font-medium">
            {{ employee.middle_name || '-' }}
          </p>
        </div>

        <div>
          <label class="text-white text-sm">
            Last Name
          </label>

          <p class="font-medium">
            {{ employee.last_name }}
          </p>
        </div>

        <div>
          <label class="text-white text-sm">
            Sex
          </label>

          <p class="font-medium">
            {{ employee.sex }}
          </p>
        </div>

        <div>
          <label class="text-white text-sm">
            Contact Number
          </label>

          <p class="font-medium">
            {{ employee.contact_number }}
          </p>
        </div>

      </div>

    </div>

    <!-- Employment -->

    <div class="neo-card rounded-lg shadow">

      <div class="border-b px-6 py-4">

        <h3 class="text-lg font-semibold text-white">
          Employment Information
        </h3>

      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">

        <div>
          <label class="text-white text-sm">
            Department
          </label>

          <p class="font-medium">
            {{ employee.department }}
          </p>
        </div>

        <div>
          <label class="text-whitetext-sm">
            Position
          </label>

          <p class="font-medium">
            {{ employee.position }}
          </p>
        </div>

        <div>
          <label class="text-white text-sm">
            Employee Category
          </label>

          <p class="font-medium">
            {{ employee.employee_category }}
          </p>
        </div>

        <div>
          <label class="text-white text-sm">
            Salary
          </label>

          <p class="font-medium">
            ₱{{ employee.salary }}
          </p>
        </div>

        <div>
          <label class="text-white text-sm">
            Date Hired
          </label>

          <p class="font-medium">
            {{ employee.date_hired }}
          </p>
        </div>

      </div>

    </div>

  </div>
</template>
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const employee = ref({
    employee_id: '',
    employee_code: '',

    first_name: '',
    middle_name: '',
    last_name: '',

    sex: '',
    department: '',
    position: '',
    employee_category: '',

    salary: '',
    contact_number: '',
    employment_status: '',
    date_hired: '',

    email: '',
    role: ''
})

interface LeaveApplication {
    leave_id: number
    date_filed: string
    number_of_days: number
    final_status: string

    leaveType: {
        leave_type_name: string
    }
}

const recentLeaves = ref<LeaveApplication[]>([])
const leaveBalance = ref({
    vacation_balance: 0,
    sick_balance: 0,
    used_leave: 0,
    last_updated: ''
})

const fullName = computed(() => {

    return [
        employee.value.first_name,
        employee.value.middle_name,
        employee.value.last_name
    ]
    .filter(Boolean)
    .join(' ')
})

const initials = computed(() => {

    return (
        employee.value.first_name.charAt(0) +
        employee.value.last_name.charAt(0)
    ).toUpperCase()

})

const loadProfile = async () => {

    const token = localStorage.getItem('token')

    const response = await axios.get(
        'http://127.0.0.1:8000/api/my-profile',
        {
            headers: {
                Authorization: `Bearer ${token}`
            }
        }
    )

    employee.value = response.data
}

const loadRecentLeaves = async () => {

    const token = localStorage.getItem('token')

    const response = await axios.get(

        'http://127.0.0.1:8000/api/my-leave-applications',

        {
            headers: {

                Authorization: `Bearer ${token}`

            }
        }

    )

    recentLeaves.value = response.data

}

const loadLeaveBalance = async () => {

    const token = localStorage.getItem('token')

    const response = await axios.get(
        'http://127.0.0.1:8000/api/my-leave-balance',
        {
            headers: {
                Authorization: `Bearer ${token}`
            }
        }
    )

    leaveBalance.value = response.data
}

const statusClass = (status:string) => {

    switch (status) {

        case 'approved':

            return 'bg-green-100 text-green-700'

        case 'pending':

            return 'bg-yellow-100 text-yellow-700'

        case 'disapproved':

            return 'bg-red-100 text-red-700'

        default:

            return 'bg-gray-100 text-gray-700'

    }

}

onMounted(async () => {

    await loadProfile()

    await loadLeaveBalance()

    await loadRecentLeaves()

})
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