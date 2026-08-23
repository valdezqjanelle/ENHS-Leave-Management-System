```vue
<template>
  <div class="dashboard-shell p-8 min-h-screen">
    <div class="space-y-6">

      <!-- Header -->
      <div class="neo-card p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h2 class="text-xl font-semibold text-white">
              Reports
            </h2>

            <p class="text-sm text-gray-400 mt-1">
              Generate and view various reports
            </p>
          </div>

          <div class="flex gap-2">
            <button
              @click="generateCustomReport"
              class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
              Generate Report
            </button>
          </div>
        </div>
      </div>


      <!-- Report Type Selection -->
      <div class="neo-card p-6">

        <h3 class="text-lg font-semibold text-white mb-4">
          Select Report Type
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

          <button
            v-for="reportType in reportTypes"
            :key="reportType.id"
            @click="selectedReportType = reportType.id"
            :class="[
              'p-4 rounded-lg border-2 transition-all text-left',
              selectedReportType === reportType.id
                ? 'border-blue-500 bg-blue-500/10'
                : 'border-slate-700 hover:border-slate-500'
            ]"
          >

            <component
              :is="reportType.icon"
              class="w-8 h-8 mx-auto mb-2"
              :class="reportType.iconColor"
            />

            <h4 class="font-medium text-white text-center">
              {{ reportType.name }}
            </h4>

            <p class="text-sm text-gray-400 mt-1 text-center">
              {{ reportType.description }}
            </p>

          </button>

        </div>
      </div>


      <!-- Date Range Selection -->
      <div class="neo-card p-6">

        <h3 class="text-lg font-semibold text-white mb-4">
          Date Range
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

          <!-- Start Date -->
          <div>

            <label class="block text-sm font-medium text-gray-300 mb-2">
              Start Date
            </label>

            <input
              v-model="dateRange.start"
              type="date"
              class="w-full px-3 py-2 text-white bg-[#0B1420] border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

          </div>


          <!-- End Date -->
          <div>

            <label class="block text-sm font-medium text-gray-300 mb-2">
              End Date
            </label>

            <input
              v-model="dateRange.end"
              type="date"
              class="w-full px-3 py-2 text-white bg-[#0B1420] border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

          </div>


          <!-- Quick Select -->
          <div>

            <label class="block text-sm font-medium text-gray-300 mb-2">
              Quick Select
            </label>

            <select
              @change="setQuickDateRange"
              class="w-full px-3 py-2 text-white bg-[#0B1420] border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >

              <option value="">
                Custom Range
              </option>

              <option value="today">
                Today
              </option>

              <option value="week">
                This Week
              </option>

              <option value="month">
                This Month
              </option>

              <option value="quarter">
                This Quarter
              </option>

              <option value="year">
                This Year
              </option>

            </select>

          </div>

        </div>
      </div>


      <!-- Report Display -->
      <div class="neo-card">

        <!-- Report Header -->
        <div class="px-6 py-4 border-b border-slate-800">

          <div class="flex justify-between items-center">

            <h3 class="text-lg font-semibold text-white">
              {{ getCurrentReportName() }}
            </h3>

            <div class="flex gap-2">

              <button
                @click="exportReport"
                class="px-4 py-2 text-sm bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition"
              >
                Export
              </button>

              <button
                @click="printReport"
                class="px-4 py-2 text-sm bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition"
              >
                Print
              </button>

            </div>

          </div>

        </div>


        <!-- ========================================================= -->
        <!-- LEAVE SUMMARY REPORT -->
        <!-- ========================================================= -->

        <div
          v-if="selectedReportType === 'leave-summary'"
          class="p-6"
        >

          <!-- Charts -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            <!-- Leave by Type -->
            <div class="bg-[#0B1420] border border-slate-800 rounded-lg p-4">

              <h4 class="text-md font-semibold text-white mb-4">
                Leave by Type
              </h4>

              <div class="relative h-[320px]">
                <canvas ref="leaveTypeChart"></canvas>
              </div>

            </div>


            <!-- Leave by Department -->
            <div class="bg-[#0B1420] border border-slate-800 rounded-lg p-4">

              <h4 class="text-md font-semibold text-white mb-4">
                Leave by Department
              </h4>

              <div class="relative h-[320px]">
                <canvas ref="departmentChart"></canvas>
              </div>

            </div>

          </div>


          <!-- Summary Table -->
          <div class="overflow-x-auto">

            <table class="w-full">

              <thead class="bg-[#0B1420]">

                <tr>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Department
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Total Leaves
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Approved
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Pending
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Disapproved
                  </th>

                </tr>

              </thead>


              <tbody class="divide-y divide-slate-800">

                <tr
                  v-for="dept in leaveSummaryData"
                  :key="dept.department"
                  class="hover:bg-white/5 transition"
                >

                  <td class="px-6 py-4 text-sm font-medium text-white">
                    {{ dept.department }}
                  </td>

                  <td class="px-6 py-4 text-white text-sm">
                    {{ dept.total }}
                  </td>

                  <td class="px-6 py-4 text-sm text-green-400">
                    {{ dept.approved }}
                  </td>

                  <td class="px-6 py-4 text-sm text-yellow-400">
                    {{ dept.pending }}
                  </td>

                  <td class="px-6 py-4 text-sm text-red-400">
                    {{ dept.disapproved }}
                  </td>

                </tr>

              </tbody>

            </table>

          </div>

        </div>


        <!-- ========================================================= -->
        <!-- ATTENDANCE REPORT -->
        <!-- ========================================================= -->

        <div
          v-else-if="selectedReportType === 'attendance'"
          class="p-6"
        >

          <!-- Summary Cards -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

            <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4">

              <h4 class="text-md font-semibold text-green-400 mb-2">
                Overall Attendance Rate
              </h4>

              <p class="text-3xl font-bold text-white">
                94.5%
              </p>

              <p class="text-sm text-green-400 mt-1">
                ↑ 2.3% from last month
              </p>

            </div>


            <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4">

              <h4 class="text-md font-semibold text-yellow-400 mb-2">
                Late Arrivals
              </h4>

              <p class="text-3xl font-bold text-white">
                127
              </p>

              <p class="text-sm text-yellow-400 mt-1">
                ↓ 5.1% from last month
              </p>

            </div>


            <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">

              <h4 class="text-md font-semibold text-red-400 mb-2">
                Absenteeism Rate
              </h4>

              <p class="text-3xl font-bold text-white">
                3.2%
              </p>

              <p class="text-sm text-red-400 mt-1">
                ↓ 0.8% from last month
              </p>

            </div>

          </div>


          <!-- Attendance Trend Chart -->
          <div class="bg-[#0B1420] border border-slate-800 rounded-lg p-4 mb-6">

            <h4 class="text-md font-semibold text-white mb-4">
              Monthly Attendance Trend
            </h4>

            <canvas ref="attendanceTrendChart"></canvas>

          </div>


          <!-- Department Attendance Table -->
          <div class="overflow-x-auto">

            <table class="w-full">

              <thead class="bg-[#0B1420]">

                <tr>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Department
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Total Faculty
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Present Days
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Late Days
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Absent Days
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Attendance Rate
                  </th>

                </tr>

              </thead>


              <tbody class="divide-y divide-slate-800">

                <tr
                  v-for="dept in attendanceData"
                  :key="dept.department"
                  class="hover:bg-white/5 transition"
                >

                  <td class="px-6 py-4 text-sm font-medium text-white">
                    {{ dept.department }}
                  </td>

                  <td class="px-6 py-4 text-sm text-white">
                    {{ dept.totalFaculty }}
                  </td>

                  <td class="px-6 py-4 text-sm text-white">
                    {{ dept.presentDays }}
                  </td>

                  <td class="px-6 py-4 text-sm text-white">
                    {{ dept.lateDays }}
                  </td>

                  <td class="px-6 py-4 text-sm text-white">
                    {{ dept.absentDays }}
                  </td>

                  <td class="px-6 py-4 text-sm">

                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="getAttendanceRateClass(dept.attendanceRate)"
                    >
                      {{ dept.attendanceRate }}%
                    </span>

                  </td>

                </tr>

              </tbody>

            </table>

          </div>

        </div>


        <!-- ========================================================= -->
        <!-- EMPLOYEE PERFORMANCE -->
        <!-- ========================================================= -->

        <div
          v-else-if="selectedReportType === 'faculty-performance'"
          class="p-6"
        >

          <div class="mb-4">

            <h4 class="text-lg font-semibold text-white">
              Employee Performance Report
            </h4>

            <p class="text-sm text-gray-400">
              Total Employees: {{ totalEmployees }}
            </p>

          </div>


          <div class="overflow-x-auto">

            <table class="w-full">

              <thead class="bg-[#0B1420]">

                <tr>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Employee
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Department
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Position
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Employment Status
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Vacation Balance
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Sick Balance
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Used Leave
                  </th>

                </tr>

              </thead>


              <tbody class="divide-y divide-slate-800">

                <tr
                  v-for="employee in employeeData"
                  :key="employee.employee_id"
                  class="hover:bg-white/5 transition"
                >

                  <td class="px-6 py-4 text-white">
                    {{ employee.employee_name }}
                  </td>

                  <td class="px-6 py-4 text-white">
                    {{ employee.department }}
                  </td>

                  <td class="px-6 py-4 text-white">
                    {{ employee.position }}
                  </td>

                  <td class="px-6 py-4 text-white">
                    {{ employee.employment_status }}
                  </td>

                  <td class="px-6 py-4 text-white">
                    {{ employee.vacation_balance }}
                  </td>

                  <td class="px-6 py-4 text-white">
                    {{ employee.sick_balance }}
                  </td>

                  <td class="px-6 py-4 text-white">
                    {{ employee.used_leave }}
                  </td>

                </tr>

              </tbody>

            </table>

          </div>

        </div>


        <!-- ========================================================= -->
        <!-- LEAVE CREDITS -->
        <!-- ========================================================= -->

        <div
          v-else-if="selectedReportType === 'leave-credits'"
          class="p-6"
        >

          <!-- Summary Cards -->
          <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">

            <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">

              <h4 class="text-md font-semibold text-blue-400 mb-2">
                Total Employees
              </h4>

              <p class="text-2xl font-bold text-white">
                {{ leaveTotals.employees ?? 0 }}
              </p>

              <p class="text-sm text-blue-400 mt-1">
                Employees with leave balances
              </p>

            </div>


            <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4">

              <h4 class="text-md font-semibold text-green-400 mb-2">
                Total Vacation Earned
              </h4>

              <p class="text-2xl font-bold text-white">
                {{ leaveTotals.vacation_earned ?? 0 }}
              </p>

              <p class="text-sm text-green-400 mt-1">
                Vacation leave credits earned
              </p>

            </div>


            <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4">

              <h4 class="text-md font-semibold text-yellow-400 mb-2">
                Total Sick Earned
              </h4>

              <p class="text-2xl font-bold text-white">
                {{ leaveTotals.sick_earned ?? 0 }}
              </p>

              <p class="text-sm text-yellow-400 mt-1">
                Sick leave credits earned
              </p>

            </div>


            <div class="bg-purple-500/10 border border-purple-500/20 rounded-lg p-4">

              <h4 class="text-md font-semibold text-purple-400 mb-2">
                Total Used Leave
              </h4>

              <p class="text-2xl font-bold text-white">
                {{ leaveTotals.used_leave ?? 0 }}
              </p>

              <p class="text-sm text-purple-400 mt-1">
                Total leave days used
              </p>

            </div>

          </div>


          <!-- Employee Leave Balances -->
          <div class="overflow-x-auto">

            <table class="w-full">

              <thead class="bg-[#0B1420]">

                <tr>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Employee
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Department
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Vacation Balance
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Sick Balance
                  </th>

                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">
                    Used Leave
                  </th>

                </tr>

              </thead>


              <tbody class="divide-y divide-slate-800">

                <tr
                  v-for="employee in creditsData"
                  :key="employee.employee_id"
                  class="hover:bg-white/5 transition"
                >

                  <td class="px-6 py-4 text-sm font-medium text-white">
                    {{ employee.employee_name }}
                  </td>

                  <td class="px-6 py-4 text-sm text-white">
                    {{ employee.department }}
                  </td>

                  <td class="px-6 py-4 text-sm text-white">
                    {{ employee.vacation_balance }}
                  </td>

                  <td class="px-6 py-4 text-sm text-white">
                    {{ employee.sick_balance }}
                  </td>

                  <td class="px-6 py-4 text-sm text-white">
                    {{ employee.used_leave }}
                  </td>

                </tr>


                <tr v-if="creditsData.length === 0">

                  <td
                    colspan="5"
                    class="px-6 py-8 text-center text-gray-500"
                  >
                    No leave credit records found.
                  </td>

                </tr>

              </tbody>

            </table>

          </div>

        </div>

      </div>

    </div>
  </div>
</template>


<script setup lang="ts">

import {
  ref,
  onMounted,
  nextTick,
  watch
} from 'vue'

import {
  FileText,
  Users,
  TrendingUp,
  CreditCard
} from 'lucide-vue-next'

import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  LineElement,
  PointElement,
  PieController
} from 'chart.js'

import {
  getLeaveSummary,
  getLeaveCredits,
  getEmployeesReport
} from '@/services/report'


/*
|--------------------------------------------------------------------------
| Chart.js Registration
|--------------------------------------------------------------------------
*/

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  LineElement,
  PointElement,
  PieController
)


/*
|--------------------------------------------------------------------------
| Global Chart Defaults
|--------------------------------------------------------------------------
*/

ChartJS.defaults.color = '#94a3b8'
ChartJS.defaults.borderColor = '#1e293b'


/*
|--------------------------------------------------------------------------
| Report State
|--------------------------------------------------------------------------
*/

const selectedReportType = ref('leave-summary')

const dateRange = ref({

  start: new Date(
    new Date().getFullYear(),
    new Date().getMonth(),
    1
  )
    .toISOString()
    .split('T')[0],

  end: new Date()
    .toISOString()
    .split('T')[0]

})


/*
|--------------------------------------------------------------------------
| Report Types
|--------------------------------------------------------------------------
*/

const reportTypes = [

  {
    id: 'leave-summary',
    name: 'Leave Summary',
    description: 'Comprehensive leave statistics',
    icon: FileText,
    iconColor: 'text-blue-400'
  },

  {
    id: 'attendance',
    name: 'Attendance Report',
    description: 'Employee attendance patterns',
    icon: Users,
    iconColor: 'text-green-400'
  },

  {
    id: 'faculty-performance',
    name: 'Employee Performance',
    description: 'Individual performance metrics',
    icon: TrendingUp,
    iconColor: 'text-purple-400'
  },

  {
    id: 'leave-credits',
    name: 'Leave Credits',
    description: 'Credit usage and availability',
    icon: CreditCard,
    iconColor: 'text-yellow-400'
  }

]


/*
|--------------------------------------------------------------------------
| Data
|--------------------------------------------------------------------------
*/

const leaveSummaryData = ref<any[]>([])

const attendanceData = [

  {
    department: 'Computer Science',
    totalFaculty: 45,
    presentDays: 1205,
    lateDays: 89,
    absentDays: 31,
    attendanceRate: 95.2
  },

  {
    department: 'Mathematics',
    totalFaculty: 38,
    presentDays: 1012,
    lateDays: 76,
    absentDays: 28,
    attendanceRate: 94.8
  },

  {
    department: 'English',
    totalFaculty: 32,
    presentDays: 845,
    lateDays: 58,
    absentDays: 22,
    attendanceRate: 94.1
  },

  {
    department: 'Science',
    totalFaculty: 41,
    presentDays: 1089,
    lateDays: 92,
    absentDays: 35,
    attendanceRate: 93.5
  }

]


const employeeData = ref<any[]>([])
const totalEmployees = ref(0)

const creditsData = ref<any[]>([])

const leaveTotals = ref<any>({})

const loading = ref(false)


/*
|--------------------------------------------------------------------------
| Chart References
|--------------------------------------------------------------------------
*/

const leaveTypeChart =
  ref<HTMLCanvasElement | null>(null)

const departmentChart =
  ref<HTMLCanvasElement | null>(null)

const attendanceTrendChart =
  ref<HTMLCanvasElement | null>(null)


let leaveTypeInstance: ChartJS | null = null

let departmentInstance: ChartJS | null = null

let attendanceTrendInstance: ChartJS | null = null


/*
|--------------------------------------------------------------------------
| Load Leave Summary
|--------------------------------------------------------------------------
*/

async function loadLeaveSummary() {

  loading.value = true

  try {

    const response = await getLeaveSummary()

    leaveSummaryData.value =
      response.data.summary || []

    leaveTotals.value =
      response.data.totals || {}

    await nextTick()

    createLeaveCharts()

  } catch (error) {

    console.error(
      'Failed to load leave summary:',
      error
    )

  } finally {

    loading.value = false

  }

}


/*
|--------------------------------------------------------------------------
| Create Leave Charts
|--------------------------------------------------------------------------
*/

function createLeaveCharts() {

  if (
    !leaveTypeChart.value ||
    !departmentChart.value
  ) {
    return
  }


  /*
  |--------------------------------------------------------------------------
  | Destroy Existing Charts
  |--------------------------------------------------------------------------
  */

  leaveTypeInstance?.destroy()

  departmentInstance?.destroy()


  /*
  |--------------------------------------------------------------------------
  | Department Data
  |--------------------------------------------------------------------------
  */

  const departmentLabels =
    leaveSummaryData.value.map(
      (item: any) => item.department
    )

  const departmentTotals =
    leaveSummaryData.value.map(
      (item: any) => Number(item.total)
    )


  /*
  |--------------------------------------------------------------------------
  | Leave Type Data
  |--------------------------------------------------------------------------
  */

  const leaveTypeTotals:
    Record<string, number> = {}


  leaveSummaryData.value.forEach(
    (dept: any) => {

      if (!dept.leave_types) {
        return
      }

      Object.entries(
        dept.leave_types
      ).forEach(
        ([type, count]) => {

          leaveTypeTotals[type] =
            (leaveTypeTotals[type] || 0) +
            Number(count)

        }
      )

    }
  )


  /*
  |--------------------------------------------------------------------------
  | RED / GREEN / Additional Colors
  |--------------------------------------------------------------------------
  */

  const chartColors = [

    '#22c55e', // Green
    '#ef4444', // Red
    '#3b82f6', // Blue
    '#f59e0b', // Yellow
    '#a855f7', // Purple
    '#06b6d4', // Cyan

  ]


  /*
  |--------------------------------------------------------------------------
  | Leave By Type - Pie Chart
  |--------------------------------------------------------------------------
  */

  leaveTypeInstance = new ChartJS(
    leaveTypeChart.value,
    {

      type: 'pie',

      data: {

        labels:
          Object.keys(leaveTypeTotals),

        datasets: [

          {

            label: 'Leave Types',

            data:
              Object.values(
                leaveTypeTotals
              ),

            backgroundColor:
              chartColors.slice(
                0,
                Object.keys(
                  leaveTypeTotals
                ).length
              ),

            borderColor: '#0B1420',

            borderWidth: 3,

            hoverOffset: 8

          }

        ]

      },

      options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

          legend: {

            position: 'bottom',

            labels: {

              color: '#cbd5e1',

              padding: 18,

              usePointStyle: true,

              pointStyle: 'circle'

            }

          },

          tooltip: {

            backgroundColor: '#111D2E',

            titleColor: '#ffffff',

            bodyColor: '#cbd5e1',

            borderColor: '#334155',

            borderWidth: 1,

            padding: 12

          }

        }

      }

    }
  )


  /*
  |--------------------------------------------------------------------------
  | Leave By Department - Bar Chart
  |--------------------------------------------------------------------------
  */

  departmentInstance = new ChartJS(
    departmentChart.value,
    {

      type: 'bar',

      data: {

        labels:
          departmentLabels,

        datasets: [

          {

            label: 'Total Leaves',

            data:
              departmentTotals,

            /*
            |--------------------------------------------------------------------------
            | Green and Red Bar Colors
            |--------------------------------------------------------------------------
            */

            backgroundColor:
              departmentTotals.map(
                (value: number) => {

                  if (value >= 10) {
                    return '#ef4444'
                  }

                  return '#22c55e'

                }
              ),

            borderColor:
              departmentTotals.map(
                (value: number) => {

                  if (value >= 10) {
                    return '#dc2626'
                  }

                  return '#16a34a'

                }
              ),

            borderWidth: 1,

            borderRadius: 8,

            hoverBackgroundColor:
              departmentTotals.map(
                (value: number) => {

                  if (value >= 10) {
                    return '#f87171'
                  }

                  return '#4ade80'

                }
              )

          }

        ]

      },

      options: {

        responsive: true,

        maintainAspectRatio: false,

        scales: {

          x: {

            grid: {

              color:
                'rgba(148, 163, 184, 0.08)'

            },

            ticks: {

              color: '#94a3b8'

            }

          },

          y: {

            beginAtZero: true,

            grid: {

              color:
                'rgba(148, 163, 184, 0.08)'

            },

            ticks: {

              color: '#94a3b8',

              precision: 0

            }

          }

        },

        plugins: {

          legend: {

            labels: {

              color: '#cbd5e1'

            }

          },

          tooltip: {

            backgroundColor: '#111D2E',

            titleColor: '#ffffff',

            bodyColor: '#cbd5e1',

            borderColor: '#334155',

            borderWidth: 1,

            padding: 12

          }

        }

      }

    }
  )

}


/*
|--------------------------------------------------------------------------
| Load Leave Credits
|--------------------------------------------------------------------------
*/

async function loadLeaveCredits() {

  try {

    const response =
      await getLeaveCredits()

    creditsData.value =
      response.data.employees || []

    leaveTotals.value =
      response.data.totals || {}

  } catch (error) {

    console.error(
      'Failed to load leave credits:',
      error
    )

  }

}


/*
|--------------------------------------------------------------------------
| Load Employee Report
|--------------------------------------------------------------------------
*/

async function loadEmployeeReport() {

  try {

    const response =
      await getEmployeesReport()

    employeeData.value =
      response.data.employees || []

    totalEmployees.value =
      response.data.total_employees || 0

  } catch (error) {

    console.error(
      'Failed to load employee report:',
      error
    )

  }

}


/*
|--------------------------------------------------------------------------
| Report Name
|--------------------------------------------------------------------------
*/

const getCurrentReportName = () => {

  const report =
    reportTypes.find(
      r =>
        r.id ===
        selectedReportType.value
    )

  return report
    ? report.name
    : 'Report'

}


/*
|--------------------------------------------------------------------------
| Attendance Rate Styling
|--------------------------------------------------------------------------
*/

const getAttendanceRateClass = (
  rate: number
) => {

  if (rate >= 95) {

    return 'bg-green-500/20 text-green-400'

  }

  if (rate >= 90) {

    return 'bg-yellow-500/20 text-yellow-400'

  }

  return 'bg-red-500/20 text-red-400'

}


/*
|--------------------------------------------------------------------------
| Quick Date Range
|--------------------------------------------------------------------------
*/

const setQuickDateRange = (
  event: Event
) => {

  const select =
    event.target as HTMLSelectElement

  const value =
    select.value

  const today =
    new Date()

  let start =
    new Date()


  switch (value) {

    case 'today':

      start = today

      break


    case 'week':

      start.setDate(
        today.getDate() - 7
      )

      break


    case 'month':

      start = new Date(
        today.getFullYear(),
        today.getMonth(),
        1
      )

      break


    case 'quarter':

      start = new Date(
        today.getFullYear(),
        Math.floor(
          today.getMonth() / 3
        ) * 3,
        1
      )

      break


    case 'year':

      start = new Date(
        today.getFullYear(),
        0,
        1
      )

      break

  }


  if (value) {

    dateRange.value.start =
      start
        .toISOString()
        .split('T')[0]

    dateRange.value.end =
      today
        .toISOString()
        .split('T')[0]

  }

}


/*
|--------------------------------------------------------------------------
| Buttons
|--------------------------------------------------------------------------
*/

const generateCustomReport = () => {

  console.log(
    'Generate custom report'
  )

}


const exportReport = () => {

  console.log(
    'Export report'
  )

}


const printReport = () => {

  window.print()

}


/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

onMounted(() => {

  loadLeaveSummary()

})


/*
|--------------------------------------------------------------------------
| Report Type Watcher
|--------------------------------------------------------------------------
*/

watch(
  selectedReportType,
  async (value) => {

    switch (value) {

      case 'leave-summary':

        await loadLeaveSummary()

        break


      case 'leave-credits':

        await loadLeaveCredits()

        break


      case 'faculty-performance':

        await loadEmployeeReport()

        break

    }

  }
)

</script>


<style scoped>

.dashboard-shell {
  background: #080D14;
}


.neo-card {
  background: #111D2E;

  border: 1px solid #1E293B;

  border-radius: 1.4rem;

  box-shadow:
    0 10px 22px
    rgba(15, 23, 42, 0.04);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;
}


.neo-card:hover {

  box-shadow:
    0 14px 26px
    rgba(15, 23, 42, 0.06);

}


.stats-card {

  border-left:
    4px solid currentColor;

  padding:
    1.35rem;

}


.stats-card .p-3 {

  border-radius:
    0.9rem;

}


.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {

  letter-spacing:
    -0.01em;

}



canvas {

  max-width: 100%;

}


@media print {

  .dashboard-shell {

    background: white !important;

  }

  .neo-card {

    background: white !important;

    border:
      1px solid #ddd !important;

    box-shadow:
      none !important;

  }

  .neo-card h3,
  .neo-card h4,
  .neo-card p,
  .neo-card span,
  .neo-card td,
  .neo-card th {

    color: black !important;

  }

}

</style>

