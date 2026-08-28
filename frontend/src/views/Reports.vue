<template>
  <div class="dashboard-shell min-h-screen p-4 sm:p-6 lg:p-8">
    <div class="w-full space-y-6">

      <!-- ========================================================= -->
      <!-- HEADER -->
      <!-- ========================================================= -->

      <div class="neo-card w-full p-5 sm:p-6">
        <div
          class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
          <div>
            <h2 class="text-xl font-semibold text-white">
              Reports
            </h2>

            <p class="mt-1 text-sm text-gray-400">
              Generate and view various reports
            </p>
          </div>

      
        </div>
      </div>


      <!-- ========================================================= -->
      <!-- REPORT TYPE SELECTION -->
      <!-- ========================================================= -->

      <div class="neo-card w-full p-5 sm:p-6">

        <div class="mb-5">
          <h3 class="text-lg font-semibold text-white">
            Select Report Type
          </h3>

          <p class="mt-1 text-sm text-gray-400">
            Choose the type of report you want to view.
          </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

          <button
            v-for="reportType in reportTypes"
            :key="reportType.id"
            @click="selectedReportType = reportType.id"
            :class="[
              'rounded-xl border p-4 text-left transition-all duration-200',
              selectedReportType === reportType.id
                ? 'border-blue-500 bg-blue-500/10 shadow-lg shadow-blue-500/5'
                : 'border-slate-700 bg-[#0D1724] hover:border-slate-600 hover:bg-[#101C2B]'
            ]"
          >

            <div class="flex flex-col items-center">

              <component
                :is="reportType.icon"
                class="mb-3 h-8 w-8"
                :class="reportType.iconColor"
              />

              <h4 class="text-center font-medium text-white">
                {{ reportType.name }}
              </h4>

              <p class="mt-1 text-center text-sm leading-5 text-gray-400">
                {{ reportType.description }}
              </p>

            </div>

          </button>

        </div>
      </div>


      <!-- ========================================================= -->
      <!-- DATE RANGE SELECTION -->
      <!-- ========================================================= -->

      <div class="neo-card w-full p-5 sm:p-6">

        <div class="mb-5">
          <h3 class="text-lg font-semibold text-white">
            Date Range
          </h3>

          <p class="mt-1 text-sm text-gray-400">
            Select the reporting period.
          </p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

          <!-- Start Date -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-300">
              Start Date
            </label>

            <input
              v-model="dateRange.start"
              type="date"
              class="w-full rounded-lg border border-slate-700 bg-[#0B1420] px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>


          <!-- End Date -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-300">
              End Date
            </label>

            <input
              v-model="dateRange.end"
              type="date"
              class="w-full rounded-lg border border-slate-700 bg-[#0B1420] px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>


          <!-- Quick Select -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-300">
              Quick Select
            </label>

            <select
              @change="setQuickDateRange"
              class="w-full rounded-lg border border-slate-700 bg-[#0B1420] px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
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


      <!-- ========================================================= -->
      <!-- REPORT DISPLAY -->
      <!-- ========================================================= -->

      <div class="neo-card w-full overflow-hidden">

        <!-- Report Header -->
        <div
          class="border-b border-slate-800 px-5 py-5 sm:px-6"
        >

          <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
          >

            <div>
              <h3 class="text-lg font-semibold text-white">
                {{ getCurrentReportName() }}
              </h3>

              <p class="mt-1 text-sm text-gray-400">
                Report results and statistics
              </p>
            </div>

            <div class="flex w-full gap-2 sm:w-auto">

              <button
                @click="exportReport"
                class="flex-1 rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-600 sm:flex-none"
              >
                Export
              </button>

              <button
                @click="printReport"
                class="flex-1 rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-600 sm:flex-none"
              >
                Print
              </button>

            </div>

          </div>

        </div>


        <!-- ========================================================= -->
        <!-- LEAVE SUMMARY REPORT -->
        <!-- ========================================================= -->

        <template v-if="selectedReportType === 'leave-summary'">

          <div class="leave-summary p-5 sm:p-6">

            <!-- ===================================================== -->
            <!-- LEAVE SUMMARY OVERVIEW -->
            <!-- ===================================================== -->

            <div class="mb-6">

              


              <!-- Department Summary -->
              <div class="department-section">

                <div class="mb-3 flex items-center justify-between">
                  <h5 class="text-sm font-semibold uppercase tracking-wide text-gray-300">
                    Leave By Department
                  </h5>

                  <span class="text-xs text-gray-500">
                    Total Requests
                  </span>
                </div>


                <div
                  v-if="leaveByDepartment.length"
                  class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3"
                >

                  <div
                    v-for="dept in leaveByDepartment"
                    :key="dept.department"
                    class="department-item flex min-h-[82px] items-center justify-between rounded-xl border border-slate-700/80 bg-[#0D1724] px-4 py-3.5 transition hover:border-slate-600 hover:bg-[#101C2B]"
                  >

                    <div class="min-w-0 pr-4">
                      <p class="truncate text-sm font-semibold text-white">
                        {{ dept.department }}
                      </p>

                      <p class="mt-1 text-xs text-gray-500">
                        Leave Requests
                      </p>
                    </div>

                    <div class="shrink-0">
                      <span class="text-2xl font-bold text-white">
                        {{ dept.count }}
                      </span>
                    </div>

                  </div>

                </div>

                <div
                  v-else
                  class="rounded-xl border border-dashed border-slate-700 bg-[#0D1724] px-4 py-8 text-center"
                >
                  <p class="text-sm text-gray-400">
                    No department data available.
                  </p>
                </div>

              </div>

            </div>


            <!-- ===================================================== -->
            <!-- STATUS + LEAVE TYPE -->
            <!-- ===================================================== -->

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

              <!-- =================================================== -->
              <!-- STATUS -->
              <!-- =================================================== -->

              <div class="summary-panel rounded-xl border border-slate-700/80 bg-[#0D1724] p-5 sm:p-6">

                <div class="mb-5">
                  <h3 class="text-base font-semibold text-white">
                    Leave Application Status
                  </h3>

                  <p class="mt-1 text-sm text-gray-400">
                    Overview of submitted leave requests.
                  </p>
                </div>


                <div class="space-y-5">

                  <!-- Approved -->
                  <div>

                    <div class="mb-2 flex items-center justify-between">

                      <span class="text-sm font-medium text-gray-300">
                        Approved
                      </span>

                      <span class="text-sm font-semibold text-green-400">
                        {{ statusChartData.approved }}
                      </span>

                    </div>

                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-700">

                      <div
                        class="h-2.5 rounded-full bg-green-500 transition-all duration-500"
                        :style="{
                          width:
                            calculatePercentage(statusChartData.approved) + '%'
                        }"
                      ></div>

                    </div>

                  </div>


                  <!-- Pending -->
                  <div>

                    <div class="mb-2 flex items-center justify-between">

                      <span class="text-sm font-medium text-gray-300">
                        Pending
                      </span>

                      <span class="text-sm font-semibold text-yellow-400">
                        {{ statusChartData.pending }}
                      </span>

                    </div>

                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-700">

                      <div
                        class="h-2.5 rounded-full bg-yellow-500 transition-all duration-500"
                        :style="{
                          width:
                            calculatePercentage(statusChartData.pending) + '%'
                        }"
                      ></div>

                    </div>

                  </div>


                  <!-- Disapproved -->
                  <div>

                    <div class="mb-2 flex items-center justify-between">

                      <span class="text-sm font-medium text-gray-300">
                        Disapproved
                      </span>

                      <span class="text-sm font-semibold text-red-400">
                        {{ statusChartData.disapproved }}
                      </span>

                    </div>

                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-700">

                      <div
                        class="h-2.5 rounded-full bg-red-500 transition-all duration-500"
                        :style="{
                          width:
                            calculatePercentage(statusChartData.disapproved) + '%'
                        }"
                      ></div>

                    </div>

                  </div>

                </div>

              </div>


              <!-- =================================================== -->
              <!-- LEAVE BY TYPE -->
              <!-- =================================================== -->

              <div class="summary-panel rounded-xl border border-slate-700/80 bg-[#0D1724] p-5 sm:p-6">

                <div class="mb-5">
                  <h3 class="text-base font-semibold text-white">
                    Leave By Type
                  </h3>

                  <p class="mt-1 text-sm text-gray-400">
                    Breakdown of leave requests by leave category.
                  </p>
                </div>


                <div
                  v-if="leaveByType.length"
                  class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-2"
                >

                  <div
                    v-for="leave in leaveByType"
                    :key="leave.name"
                    class="leave-type-item rounded-xl border border-slate-700 bg-[#111D2E] p-4 transition hover:border-slate-600"
                  >

                    <p
                      class="truncate text-xs font-medium uppercase tracking-wide text-gray-400"
                      :title="leave.name"
                    >
                      {{ leave.name }}
                    </p>

                    <p class="mt-2 text-2xl font-bold text-white">
                      {{ leave.count }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                      Requests
                    </p>

                  </div>

                </div>

                <div
                  v-else
                  class="rounded-xl border border-dashed border-slate-700 bg-[#111D2E] px-4 py-8 text-center"
                >
                  <p class="text-sm text-gray-400">
                    No leave type data available.
                  </p>
                </div>

              </div>

            </div>

          </div>

        </template>


    


        <!-- ========================================================= -->
        <!-- EMPLOYEE PERFORMANCE -->
        <!-- ========================================================= -->

        <template v-else-if="selectedReportType === 'faculty-performance'">

          <div class="p-5 sm:p-6">

            <div class="mb-5">

  

            </div>


            <div class="overflow-x-auto">

              <table class="w-full min-w-[1000px]">

                <thead class="bg-[#0B1420]">

                  <tr>

                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase text-gray-400">
                      Employee
                    </th>

                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase text-gray-400">
                      Department
                    </th>

                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase text-gray-400">
                      Position
                    </th>

                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase text-gray-400">
                      Employment Status
                    </th>

                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase text-gray-400">
                      Vacation Balance
                    </th>

                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase text-gray-400">
                      Sick Balance
                    </th>

                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase text-gray-400">
                      Used Leave
                    </th>

                  </tr>

                </thead>


                <tbody class="divide-y divide-slate-800">

                  <tr
                    v-for="employee in employeeData"
                    :key="employee.employee_id"
                    class="transition hover:bg-white/5"
                  >

                    <td class="px-6 py-4 text-white">
                      {{ employee.employee_name }}
                    </td>

                    <td class="px-6 py-4 text-white">
                      {{ employee.department }}
                    </td>

                    <td class="px-6 py-4 text-white">
                      {{ employee.position?.name || employee.position || "-" }}
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


                  <tr v-if="employeeData.length === 0">

                    <td
                      colspan="7"
                      class="px-6 py-8 text-center text-gray-500"
                    >
                      No employee records found.
                    </td>

                  </tr>

                </tbody>

              </table>

            </div>

          </div>

        </template>


        <!-- ========================================================= -->
        <!-- LEAVE CREDITS -->
        <!-- ========================================================= -->

        <template v-else-if="selectedReportType === 'leave-credits'">

          <div class="p-5 sm:p-6">

            <!-- Summary Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

              <!-- Total Employees -->
              <div class="rounded-xl border border-blue-500/20 bg-blue-500/10 p-4">

                <h4 class="mb-2 text-sm font-semibold text-blue-400">
                  Total Employees
                </h4>

                <p class="text-2xl font-bold text-white">
                  {{ leaveTotals.employees ?? 0 }}
                </p>

                <p class="mt-1 text-xs text-blue-400">
                  Employees with leave balances
                </p>

              </div>


              <!-- Vacation Earned -->
              <div class="rounded-xl border border-green-500/20 bg-green-500/10 p-4">

                <h4 class="mb-2 text-sm font-semibold text-green-400">
                  Total Vacation Earned
                </h4>

                <p class="text-2xl font-bold text-white">
                  {{ leaveTotals.vacation_earned ?? 0 }}
                </p>

                <p class="mt-1 text-xs text-green-400">
                  Vacation leave credits earned
                </p>

              </div>


              <!-- Sick Earned -->
              <div class="rounded-xl border border-yellow-500/20 bg-yellow-500/10 p-4">

                <h4 class="mb-2 text-sm font-semibold text-yellow-400">
                  Total Sick Earned
                </h4>

                <p class="text-2xl font-bold text-white">
                  {{ leaveTotals.sick_earned ?? 0 }}
                </p>

                <p class="mt-1 text-xs text-yellow-400">
                  Sick leave credits earned
                </p>

              </div>


              <!-- Used Leave -->
              <div class="rounded-xl border border-purple-500/20 bg-purple-500/10 p-4">

                <h4 class="mb-2 text-sm font-semibold text-purple-400">
                  Total Used Leave
                </h4>

                <p class="text-2xl font-bold text-white">
                  {{ leaveTotals.used_leave ?? 0 }}
                </p>

                <p class="mt-1 text-xs text-purple-400">
                  Total leave days used
                </p>

              </div>

            </div>


            <!-- Employee Leave Balances -->
            <div class="overflow-x-auto">

              <table class="w-full min-w-[700px]">

                <thead class="bg-[#0B1420]">

                  <tr>

                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase text-gray-400">
                      Employee
                    </th>

                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase text-gray-400">
                      Department
                    </th>

                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase text-gray-400">
                      Vacation Balance
                    </th>

                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase text-gray-400">
                      Sick Balance
                    </th>

                    <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-medium uppercase text-gray-400">
                      Used Leave
                    </th>

                  </tr>

                </thead>


                <tbody class="divide-y divide-slate-800">

                  <tr
                    v-for="employee in creditsData"
                    :key="employee.employee_id"
                    class="transition hover:bg-white/5"
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

        </template>

      </div>

    </div>
  </div>
</template>


<script setup lang="ts">

import {
  ref,
  onMounted,
  nextTick,
  watch,
  computed
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
  BarController,
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
  BarController,
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




const employeeData = ref<any[]>([])

const totalEmployees = ref(0)

const creditsData = ref<any[]>([])

const leaveTotals = ref<any>({})

const loading = ref(false)


/*
|--------------------------------------------------------------------------
| Computed Leave Summary Data
|--------------------------------------------------------------------------
*/

const leaveByDepartment = computed(() => {

  return leaveSummaryData.value.map((item: any) => ({
    department: item.department ?? 'Unknown',
    count: Number(
      item.total ??
      item.count ??
      0
    )
  }))

})


const leaveByType = computed(() => {

  const totals: Record<string, number> = {}

  leaveSummaryData.value.forEach((dept: any) => {

    if (!dept.leave_types) {
      return
    }

    Object.entries(dept.leave_types).forEach(
      ([type, count]) => {

        totals[type] =
          (totals[type] || 0) +
          Number(count)

      }
    )

  })

  return Object.entries(totals).map(
    ([name, count]) => ({
      name,
      count
    })
  )

})


const statusChartData = computed(() => {

  return {

    approved:
      leaveSummaryData.value.reduce(
        (sum: number, item: any) =>
          sum + Number(item.approved ?? 0),
        0
      ),

    pending:
      leaveSummaryData.value.reduce(
        (sum: number, item: any) =>
          sum + Number(item.pending ?? 0),
        0
      ),

    disapproved:
      leaveSummaryData.value.reduce(
        (sum: number, item: any) =>
          sum + Number(item.disapproved ?? 0),
        0
      )

  }

})


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
| Percentage Helper
|--------------------------------------------------------------------------
*/

function calculatePercentage(value: number) {

  const total =
    statusChartData.value.approved +
    statusChartData.value.pending +
    statusChartData.value.disapproved

  if (!total) {
    return 0
  }

  return Math.round(
    (value / total) * 100
  )

}


/*
|--------------------------------------------------------------------------
| Load Leave Summary
|--------------------------------------------------------------------------
*/

async function loadLeaveSummary() {

  loading.value = true

  try {

    const response =
      await getLeaveSummary()

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
      (item: any) =>
        item.department
    )

  const departmentTotals =
    leaveSummaryData.value.map(
      (item: any) =>
        Number(item.total)
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
  | Chart Colors
  |--------------------------------------------------------------------------
  */

  const chartColors = [

    '#22c55e',
    '#ef4444',
    '#3b82f6',
    '#f59e0b',
    '#a855f7',
    '#06b6d4'

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
          Object.keys(
            leaveTypeTotals
          ),

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

  let data: any[] = []

  let filename =
    'report.csv'


  switch (
    selectedReportType.value
  ) {

    case 'leave-summary':

      data =
        leaveSummaryData.value.map(
          (item: any) => ({

            Department:
              item.department ?? '',

            'Total Leaves':
              item.total ?? 0,

            Approved:
              item.approved ?? 0,

            Pending:
              item.pending ?? 0,

            Disapproved:
              item.disapproved ?? 0

          })
        )

      filename =
        'leave-summary-report.csv'

      break


    case 'leave-credits':

      data =
        creditsData.value.map(
          (employee: any) => ({

            Employee:
              employee.employee_name ?? '',

            Department:
              employee.department ?? '',

            'Vacation Balance':
              employee.vacation_balance ?? 0,

            'Sick Balance':
              employee.sick_balance ?? 0,

            'Used Leave':
              employee.used_leave ?? 0

          })
        )

      filename =
        'leave-credits-report.csv'

      break


    case 'faculty-performance':

      data =
        employeeData.value.map(
          (employee: any) => ({

            Employee:
              employee.employee_name ?? '',

            Department:
              employee.department ?? '',

            Position:
              employee.position?.name ?? employee.position ?? '',

            'Employment Status':
              employee.employment_status ?? '',

            'Vacation Balance':
              employee.vacation_balance ?? 0,

            'Sick Balance':
              employee.sick_balance ?? 0,

            'Used Leave':
              employee.used_leave ?? 0

          })
        )

      filename =
        'employee-performance-report.csv'

      break

  }


  /*
  |--------------------------------------------------------------------------
  | Check Data
  |--------------------------------------------------------------------------
  */

  if (data.length === 0) {

    alert(
      'No data available to export.'
    )

    return

  }


  /*
  |--------------------------------------------------------------------------
  | Get Headers
  |--------------------------------------------------------------------------
  */

  const headers =
    Object.keys(data[0])


  /*
  |--------------------------------------------------------------------------
  | Convert To CSV
  |--------------------------------------------------------------------------
  */

  const csvRows = [

    headers.join(','),

    ...data.map((row) =>

      headers
        .map((header) => {

          const value =
            row[header] ?? ''

          return `"${String(value).replace(
            /"/g,
            '""'
          )}"`

        })
        .join(',')

    )

  ]


  const csvContent =
    '\uFEFF' +
    csvRows.join('\n')


  /*
  |--------------------------------------------------------------------------
  | Create Download
  |--------------------------------------------------------------------------
  */

  const blob =
    new Blob(
      [csvContent],
      {
        type:
          'text/csv;charset=utf-8;'
      }
    )


  const url =
    URL.createObjectURL(blob)


  const link =
    document.createElement('a')


  link.href =
    url

  link.download =
    filename


  document.body.appendChild(
    link
  )

  link.click()

  document.body.removeChild(
    link
  )


  URL.revokeObjectURL(
    url
  )

}


/*
|--------------------------------------------------------------------------
| Print
|--------------------------------------------------------------------------
*/

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


      case 'attendance':

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
    0 10px 22px rgba(15, 23, 42, 0.04);
  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;
}


.neo-card:hover {
  box-shadow:
    0 14px 26px rgba(15, 23, 42, 0.06);
}


/* =========================================================
   LEAVE SUMMARY
   ========================================================= */

.leave-summary {
  width: 100%;
}


/* Prevent nested summary panels from behaving like large cards */

.summary-panel {
  min-width: 0;
}


/* Department cards */

.department-item {
  min-width: 0;
}


/* Leave type cards */

.leave-type-item {
  min-width: 0;
}


/* Typography */

.neo-card h3,
.neo-card h4,
.neo-card h5,
.neo-card p,
.neo-card span,
.neo-card button,
.neo-card label {
  letter-spacing: -0.01em;
}


/* Charts */

canvas {
  max-width: 100%;
}


/* =========================================================
   RESPONSIVE SPACING
   ========================================================= */

@media (max-width: 640px) {

  .dashboard-shell {
    padding: 1rem;
  }

  .neo-card {
    border-radius: 1.1rem;
  }

}


/* =========================================================
   PRINT
   ========================================================= */

@media print {

  .dashboard-shell {
    background: white !important;
  }

  .neo-card,
  .summary-panel,
  .department-item,
  .leave-type-item {
    background: white !important;
    border: 1px solid #ddd !important;
    box-shadow: none !important;
  }

  .neo-card h3,
  .neo-card h4,
  .neo-card h5,
  .neo-card p,
  .neo-card span,
  .neo-card td,
  .neo-card th {
    color: black !important;
  }

}

</style>