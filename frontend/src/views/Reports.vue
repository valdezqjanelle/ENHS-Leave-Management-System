<template>
  <div class="dashboard-shell min-h-screen p-4 sm:p-6 lg:p-8">
    <div class="w-full space-y-6">
<!-- hi -->
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
              'report-type-button rounded-xl border p-4 text-left transition-all duration-200',
              selectedReportType === reportType.id
                ? 'selected-report'
                : 'unselected-report'
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

      <div v-if="selectedReportType === 'leave-summary'" class="neo-card w-full p-5 sm:p-6">

        <div class="mb-5">
          <h3 class="text-lg font-semibold text-white">
            Date Range
          </h3>

          <p class="mt-1 text-sm text-gray-400">
            Show leave applications whose leave dates overlap this period.
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
              class="report-input w-full"
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
              class="report-input w-full"
            />
          </div>


          <!-- Quick Select -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-300">
              Quick Select
            </label>

            <select
              :value="activeQuickRange"
              @change="setQuickDateRange"
              class="report-input w-full"
            >
              <option value="">
                All Dates / Custom Range
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
      <!-- LEAVE SUMMARY FILTERS -->
      <!-- ========================================================= -->

      <div v-if="selectedReportType === 'leave-summary'" class="neo-card report-filters-card w-full p-5 sm:p-6">

        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h3 class="text-lg font-semibold text-white">
              Filters
            </h3>

            <p class="mt-1 text-sm text-gray-400">
              Narrow the applications counted in the cards, table, charts and export below.
            </p>
          </div>

          <button
            type="button"
            class="secondary-button rounded-lg px-4 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="!hasSummaryFilters"
            @click="clearSummaryFilters"
          >
            Clear filters
          </button>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

          <div>
            <label for="summary-search" class="mb-2 block text-sm font-medium text-gray-300">
              Employee
            </label>

            <input
              id="summary-search"
              v-model="summarySearchInput"
              type="search"
              maxlength="100"
              autocomplete="off"
              placeholder="Search employee name"
              class="report-input w-full"
            />
          </div>

          <div>
            <label for="summary-department" class="mb-2 block text-sm font-medium text-gray-300">
              Department
            </label>

            <select id="summary-department" v-model="summaryDepartment" class="report-input w-full">
              <option value="">All departments</option>
              <option value="unassigned">Unassigned</option>
              <option v-for="option in departmentOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>

          <div>
            <label for="summary-status" class="mb-2 block text-sm font-medium text-gray-300">
              Application Status
            </label>

            <select id="summary-status" v-model="summaryStatus" class="report-input w-full">
              <option value="">All statuses</option>
              <option value="approved">Approved</option>
              <option value="pending">Pending</option>
              <option value="disapproved">Disapproved</option>
            </select>
          </div>

          <div>
            <label for="summary-leave-type" class="mb-2 block text-sm font-medium text-gray-300">
              Leave Type
            </label>

            <select id="summary-leave-type" v-model="summaryLeaveType" class="report-input w-full">
              <option value="">All leave types</option>
              <option v-for="option in leaveTypeOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>

        </div>

        <p v-if="filterOptionsError" role="status" class="mt-3 text-sm text-gray-400">
          {{ filterOptionsError }} Reload the page to try again.
        </p>
      </div>


      <!-- ========================================================= -->
      <!-- LEAVE CREDITS FILTERS -->
      <!-- ========================================================= -->

      <div v-else-if="selectedReportType === 'leave-credits'" class="neo-card report-filters-card w-full p-5 sm:p-6">

        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <h3 class="text-lg font-semibold text-white">
              Filters
            </h3>

            <p class="mt-1 text-sm text-gray-400">
              Leave Credits is a current balance snapshot, so date filters do not apply. These filters only narrow which employees are listed and totaled.
            </p>
          </div>

          <button
            type="button"
            class="secondary-button rounded-lg px-4 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="!hasCreditFilters"
            @click="clearCreditFilters"
          >
            Clear filters
          </button>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

          <div>
            <label for="credit-search" class="mb-2 block text-sm font-medium text-gray-300">
              Employee
            </label>

            <input
              id="credit-search"
              v-model="creditFilters.search"
              type="search"
              maxlength="100"
              autocomplete="off"
              placeholder="Search employee name"
              class="report-input w-full"
            />
          </div>

          <div>
            <label for="credit-department" class="mb-2 block text-sm font-medium text-gray-300">
              Department
            </label>

            <select id="credit-department" v-model="creditFilters.department" class="report-input w-full">
              <option value="">All departments</option>
              <option value="unassigned">Unassigned</option>
              <option v-for="option in departmentOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>

          <div>
            <label for="credit-status" class="mb-2 block text-sm font-medium text-gray-300">
              Employee Status
            </label>

            <select id="credit-status" v-model="creditFilters.status" class="report-input w-full">
              <option value="">Active and inactive</option>
              <option value="active">Active only</option>
              <option value="inactive">Inactive / resigned only</option>
            </select>
          </div>

          <div>
            <label for="credit-balance" class="mb-2 block text-sm font-medium text-gray-300">
              Remaining Balance
            </label>

            <select id="credit-balance" v-model="creditFilters.balance" class="report-input w-full">
              <option value="">All balances</option>
              <option value="with-balance">Has remaining days</option>
              <option value="zero">Zero remaining days</option>
              <option value="no-record">No balance record</option>
            </select>
          </div>

        </div>

        <p v-if="filterOptionsError" role="status" class="mt-3 text-sm text-gray-400">
          {{ filterOptionsError }} Reload the page to try again.
        </p>
      </div>


      <!-- ========================================================= -->
      <!-- REPORT DISPLAY -->
      <!-- ========================================================= -->

      <p v-if="loading" class="text-sm text-gray-400">Loading report...</p>
      <div v-if="reportError" role="alert" class="neo-card w-full p-4 text-red-400">
        {{ reportError }}
      </div>

      <div class="neo-card w-full overflow-hidden">

        <!-- Report Header -->
        <div
          class="report-header border-b px-5 py-5 sm:px-6"
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
                class="secondary-button flex-1 rounded-lg px-4 py-2 text-sm font-medium transition sm:flex-none"
              >
                Export
              </button>

              <button
                @click="printReport"
                class="secondary-button flex-1 rounded-lg px-4 py-2 text-sm font-medium transition sm:flex-none"
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
            <p v-if="summaryBackendWarning" role="alert" class="approved-days-warning mb-4 rounded-lg p-4">{{ summaryBackendWarning }}</p>


            <!-- ===================================================== -->
            <!-- LEAVE SUMMARY OVERVIEW -->
            <!-- ===================================================== -->

            <div class="mb-6">
              <label class="summary-filter flex items-center gap-2 text-sm text-gray-300 mb-4">
                <input v-model="includeInactive" type="checkbox" class="h-4 w-4" />
                Include inactive/resigned and archived employees
              </label>
              <div class="summary-context mb-4 text-sm text-gray-400 space-y-1">
                <p><strong>Report period:</strong> {{ summaryPeriod }}</p>
                <p><strong>Employee scope:</strong> {{ summaryFilters.include_inactive ? 'All linked employees, including inactive and archived' : 'Currently active, non-archived employees' }}</p>
                <p v-if="summaryGeneratedAt"><strong>Generated:</strong> {{ new Date(summaryGeneratedAt).toLocaleString('en-PH') }}</p>
                <p>Applications whose leave dates overlap the selected period. Day totals include full requested days of matching applications; they are not credits deducted or days restricted to that period.</p>
                <p>Departments reflect current employee assignments. Non-deleted applications only; missing links are shown as Unassigned.</p>
                <p><strong>Filters:</strong> {{ summaryFilterText }}</p>
                <p><strong>Matching applications:</strong> {{ leaveTotals.applications ?? 0 }} in {{ leaveSummaryData.length }} {{ leaveSummaryData.length === 1 ? 'department' : 'departments' }}</p>
              </div>
              <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 mb-4">
                <div class="summary-panel rounded-xl p-4"><p class="text-sm text-gray-400">Total Applications</p><p class="text-2xl font-semibold text-white">{{ leaveTotals.applications ?? 0 }}</p></div>
                <div class="summary-panel rounded-xl p-4"><p class="text-sm text-gray-400">Approved</p><p class="text-2xl font-semibold text-white">{{ leaveTotals.approved ?? 0 }}</p></div>
                <div class="summary-panel rounded-xl p-4"><p class="text-sm text-gray-400">Pending</p><p class="text-2xl font-semibold text-white">{{ leaveTotals.pending ?? 0 }}</p></div>
                <div class="summary-panel rounded-xl p-4"><p class="text-sm text-gray-400">Disapproved</p><p class="text-2xl font-semibold text-white">{{ leaveTotals.disapproved ?? 0 }}</p></div>
              </div>
              <p class="text-sm text-gray-400 mb-4">Total Requested Days: <strong>{{ formatSummaryDays(leaveTotals.total_days) }}</strong> · Requested Days of Approved Applications: <strong>{{ formatApprovedDays(leaveTotals.approved_days) }}</strong></p>
              <p v-if="Number(leaveTotals.other || 0) > 0" class="text-sm text-gray-400 mb-4">Other/unrecognized status: {{ leaveTotals.other }} application(s). Review these records; they are included in the total.</p>
              <h5 class="text-sm font-semibold uppercase tracking-wide text-gray-300 mb-3">Leave Summary by Department</h5>
              <div class="summary-table-wrapper">
                <table class="department-summary-table">
                  <thead>
                    <tr>
                      <th v-for="column in summaryColumns" :key="column.key" scope="col" :aria-sort="ariaSort(summarySort, column.key)">
                        <button type="button" class="sort-button" :title="'Sort by ' + column.label" @click="toggleSummarySort(column.key)">
                          <span>{{ column.label }}</span><span class="sort-mark" :class="{ active: summarySort?.key === column.key }" aria-hidden="true">{{ sortMark(summarySort, column.key) }}</span>
                        </button>
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="dept in sortedSummaryRows" :key="dept.department_id ?? dept.department">
                      <td>{{ dept.department }}</td><td>{{ dept.total }}</td><td>{{ dept.approved }}</td><td>{{ dept.pending }}</td><td>{{ dept.disapproved }}</td><td v-if="Number(leaveTotals.other || 0) > 0">{{ dept.other ?? 0 }}</td><td>{{ formatSummaryDays(dept.total_days) }}</td><td>{{ formatApprovedDays(dept.approved_days) }}</td>
                    </tr>
                    <tr v-if="!leaveSummaryData.length">
                      <td :colspan="summaryColumns.length" class="empty-cell">
                        {{ hasSummaryFilters ? 'No applications match the selected period, employee scope and filters.' : 'No applications match the selected period and employee scope.' }}
                        <button v-if="hasSummaryFilters" type="button" class="link-button" @click="clearSummaryFilters">Clear filters</button>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot><tr><th>Grand Total</th><td>{{ leaveTotals.applications ?? 0 }}</td><td>{{ leaveTotals.approved ?? 0 }}</td><td>{{ leaveTotals.pending ?? 0 }}</td><td>{{ leaveTotals.disapproved ?? 0 }}</td><td v-if="Number(leaveTotals.other || 0) > 0">{{ leaveTotals.other }}</td><td>{{ formatSummaryDays(leaveTotals.total_days) }}</td><td>{{ formatApprovedDays(leaveTotals.approved_days) }}</td></tr></tfoot>
                </table>
              </div>
            </div>

            <!-- STATUS + LEAVE TYPE -->
            <!-- ===================================================== -->

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

              <!-- =================================================== -->
              <!-- STATUS -->
              <!-- =================================================== -->

              <div class="summary-panel rounded-xl p-5 sm:p-6">

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

                    <div class="status-track h-2.5 w-full overflow-hidden rounded-full">

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

                    <div class="status-track h-2.5 w-full overflow-hidden rounded-full">

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

                    <div class="status-track h-2.5 w-full overflow-hidden rounded-full">

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

              <div class="summary-panel rounded-xl p-5 sm:p-6">

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
                    class="leave-type-item rounded-xl p-4 transition"
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
                  class="empty-report-state rounded-xl px-4 py-8 text-center"
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
        <!-- LEAVE CREDITS -->
        <!-- ========================================================= -->

        <template v-else-if="selectedReportType === 'leave-credits'">

          <div class="p-5 sm:p-6">

            <p class="mb-4 text-sm text-gray-400">Current balance snapshot · Loaded: {{ creditsLoadedAt }}. Values are in days. Local credits are remaining credits after deductions; accumulated earned credits are shown in the Leave Credits module. Used Leave is the overall recorded usage, not only local credits. Date filters do not apply to this snapshot. Employee scope: all non-archived employees, including inactive/resigned; archived employees are not listed, and the inactive/archived option applies to Leave Summary only.</p>
            <p class="mb-4 text-sm text-gray-400"><strong>Filters:</strong> {{ creditFilterText }} · <strong>Matching employees:</strong> {{ creditTotals.employees }} of {{ creditsData.length }}</p>
            <p v-if="Number(creditTotals.employees_without_record || 0) > 0" role="status" class="mb-4 text-sm text-gray-400">{{ creditTotals.employees_without_record }} of {{ creditTotals.employees }} employee(s) in this view have no leave balance record yet and are shown as "No record". This is different from a recorded balance of 0.000, and they add nothing to the totals below.</p>

            <!-- Summary Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

              <!-- Total Employees -->
              <div class="credit-summary-card credit-blue rounded-xl p-4">

                <h4 class="mb-2 text-sm font-semibold">
                  Total Employees
                </h4>

                <p class="text-2xl font-bold text-white">
                  {{ creditTotals.employees ?? 0 }}
                </p>

                <p class="mt-1 text-xs">
                  Employees matching the filters
                </p>

              </div>


              <!-- Vacation Earned -->
              <div class="credit-summary-card credit-green rounded-xl p-4">

                <h4 class="mb-2 text-sm font-semibold">
                  Remaining Vacation Days
                </h4>

                <p class="text-2xl font-bold text-white">
                  {{ formatNumber(creditTotals.vacation_balance) }}
                </p>

                <p class="mt-1 text-xs">
                  Vacation days currently available
                </p>

              </div>


              <!-- Sick Earned -->
              <div class="credit-summary-card credit-yellow rounded-xl p-4">

                <h4 class="mb-2 text-sm font-semibold">
                  Remaining Sick Days
                </h4>

                <p class="text-2xl font-bold text-white">
                  {{ formatNumber(creditTotals.sick_balance) }}
                </p>

                <p class="mt-1 text-xs">
                  Sick days currently available
                </p>

              </div>


              <!-- Used Leave -->
              <div class="credit-summary-card credit-purple rounded-xl p-4">

                <h4 class="mb-2 text-sm font-semibold">
                  Remaining Local Credits
                </h4>

                <p class="text-2xl font-bold text-white">
                  {{ formatNumber(creditTotals.service_credits) }}
                </p>

                <p class="mt-1 text-xs">
                  Local credit days currently available
                </p>

              </div>

            </div>


            <!-- Employee Leave Balances -->
            <div class="table-controls mb-3">

              <p class="text-sm text-gray-400" role="status" aria-live="polite">
                <template v-if="creditPageInfo.total">
                  Showing <strong>{{ creditPageInfo.from }}&ndash;{{ creditPageInfo.to }}</strong> of <strong>{{ creditPageInfo.total }}</strong> matching {{ creditPageInfo.total === 1 ? 'employee' : 'employees' }}
                </template>
                <template v-else>
                  <strong>0</strong> matching employees
                </template>
                <template v-if="hasCreditFilters"> (out of {{ creditsData.length }})</template>
              </p>

              <label for="credit-page-size" class="flex items-center gap-2 text-sm text-gray-400">
                Rows per page
                <select id="credit-page-size" v-model.number="creditPageSize" class="report-input">
                  <option v-for="size in creditPageSizes" :key="size" :value="size">
                    {{ size === 0 ? 'All' : size }}
                  </option>
                </select>
              </label>

            </div>

            <div class="overflow-x-auto">

              <table class="report-table w-full min-w-[900px]">

                <thead>

                  <tr>

                    <th
                      v-for="column in creditColumns"
                      :key="column.key"
                      scope="col"
                      :aria-sort="ariaSort(creditSort, column.key)"
                      class="px-6 py-3 text-xs font-medium uppercase"
                      :class="column.numeric ? 'numeric-cell' : 'text-left'"
                    >
                      <button
                        type="button"
                        class="sort-button"
                        :title="'Sort by ' + column.label"
                        @click="toggleCreditSort(column.key)"
                      >
                        <span>{{ column.label }}</span>
                        <span class="sort-mark" :class="{ active: creditSort?.key === column.key }" aria-hidden="true">{{ sortMark(creditSort, column.key) }}</span>
                      </button>
                    </th>

                  </tr>

                </thead>


                <tbody>

                  <tr
                    v-for="employee in sortedCreditRows"
                    :key="employee.employee_id"
                    class="transition"
                    :class="{ 'off-page': !visibleCreditIds.has(employee.employee_id) }"
                  >

                    <td class="employee-name-cell px-6 py-4 text-sm font-medium">
                      {{ employee.employee_name }}
                      <span
                        v-if="String(employee.employment_status).toLowerCase() === 'inactive'"
                        class="status-tag"
                      >Inactive</span>
                    </td>

                    <td class="px-6 py-4 text-sm">
                      {{ employee.department_name || employee.department || "Unknown" }}
                    </td>

                    <td class="numeric-cell px-6 py-4 text-sm" :class="{ 'no-record-cell': employee.has_balance_record === false }">
                      {{ formatCredit(employee, 'service_credits') }}
                    </td>

                    <td class="numeric-cell px-6 py-4 text-sm" :class="{ 'no-record-cell': employee.has_balance_record === false }">
                      {{ formatCredit(employee, 'vacation_balance') }}
                    </td>

                    <td class="numeric-cell px-6 py-4 text-sm" :class="{ 'no-record-cell': employee.has_balance_record === false }">
                      {{ formatCredit(employee, 'sick_balance') }}
                    </td>

                    <td class="numeric-cell px-6 py-4 text-sm font-semibold" :class="{ 'no-record-cell': employee.has_balance_record === false }">
                      {{ formatCredit(employee, 'total_available') }}
                    </td>

                    <td class="numeric-cell px-6 py-4 text-sm" :class="{ 'no-record-cell': employee.has_balance_record === false }">
                      {{ formatCredit(employee, 'used_leave') }}
                    </td>

                  </tr>


                  <tr v-if="sortedCreditRows.length === 0">

                    <td
                      colspan="7"
                      class="empty-cell px-6 py-8 text-center text-gray-500"
                    >
                      {{ hasCreditFilters && creditsData.length ? 'No employees match the current filters.' : 'No leave credit records found.' }}
                      <button v-if="hasCreditFilters" type="button" class="link-button" @click="clearCreditFilters">Clear filters</button>
                    </td>

                  </tr>

                </tbody>

                <tfoot>
                  <tr class="font-semibold">
                    <th class="px-6 py-4 text-left" colspan="2">
                      Grand Total
                      <span class="block text-xs font-normal">All {{ creditTotals.employees }} matching {{ creditTotals.employees === 1 ? 'employee' : 'employees' }}, not just this page</span>
                    </th>
                    <td class="numeric-cell px-6 py-4">{{ formatNumber(creditTotals.service_credits) }}</td>
                    <td class="numeric-cell px-6 py-4">{{ formatNumber(creditTotals.vacation_balance) }}</td>
                    <td class="numeric-cell px-6 py-4">{{ formatNumber(creditTotals.sick_balance) }}</td>
                    <td class="numeric-cell px-6 py-4">{{ formatNumber(creditTotals.total_available) }}</td>
                    <td class="numeric-cell px-6 py-4">{{ formatNumber(creditTotals.used_leave) }}</td>
                  </tr>
                </tfoot>
              </table>

            </div>

            <nav
              v-if="creditPageInfo.pageCount > 1"
              class="table-controls mt-3"
              aria-label="Leave credits pagination"
            >
              <p class="text-sm text-gray-400">
                Page {{ creditPageInfo.page }} of {{ creditPageInfo.pageCount }}
              </p>

              <div class="flex gap-2">
                <button
                  type="button"
                  class="secondary-button rounded-lg px-4 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="creditPageInfo.page <= 1"
                  @click="creditPage = creditPageInfo.page - 1"
                >
                  Previous
                </button>

                <button
                  type="button"
                  class="secondary-button rounded-lg px-4 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="creditPageInfo.page >= creditPageInfo.pageCount"
                  @click="creditPage = creditPageInfo.page + 1"
                >
                  Next
                </button>
              </div>
            </nav>

          </div>

        </template>

      </div>

    </div>
  </div>
</template>


<script setup lang="ts">
import { notify } from "@/composables/useNotifications";

import {
  ref,
  nextTick,
  watch,
  computed,
  onMounted,
  onBeforeUnmount
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

import api from '@/services/api'
import { getEmployees } from '@/services/employee'
import {
  buildSearchText,
  emptyCreditFilters,
  filterCreditRows,
  hasActiveCreditFilters,
  matchQuickRange,
  nextSort,
  paginate,
  quickDateRange,
  sortRows,
  type CreditFilters,
  type SortKind,
  type SortState
} from '@/utils/reportTable'


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

ChartJS.defaults.color = '#64748b'
ChartJS.defaults.borderColor = '#cbd8e8'


/*
|--------------------------------------------------------------------------
| Report State
|--------------------------------------------------------------------------
*/

const selectedReportType = ref('leave-summary')

const localDate = (date: Date) => [
  date.getFullYear(),
  String(date.getMonth() + 1).padStart(2, '0'),
  String(date.getDate()).padStart(2, '0')
].join('-')

const dateRange = ref({ start: '', end: '' })
const includeInactive = ref(true)
const summaryFilters = ref<{
  start_date: string | null; end_date: string | null; include_inactive: boolean
  department_id?: string | null; status?: string | null; leave_type_id?: number | null; search?: string | null
}>({ start_date: null, end_date: null, include_inactive: true })

/*
|--------------------------------------------------------------------------
| Leave Summary filters (applied by the API, so cards, tables, charts and
| exports all describe the same set of applications)
|--------------------------------------------------------------------------
*/

const summaryDepartment = ref('')
const summaryStatus = ref('')
const summaryLeaveType = ref('')
const summarySearchInput = ref('')
const summarySearch = ref('')
let summarySearchTimer: ReturnType<typeof setTimeout> | undefined

// Wait for a pause in typing before asking the API again.
watch(summarySearchInput, (value) => {
  clearTimeout(summarySearchTimer)
  summarySearchTimer = setTimeout(() => { summarySearch.value = value.trim() }, 300)
})
onBeforeUnmount(() => clearTimeout(summarySearchTimer))

const hasSummaryFilters = computed(() =>
  Boolean(summaryDepartment.value || summaryStatus.value || summaryLeaveType.value || summarySearchInput.value.trim()))

const clearSummaryFilters = () => {
  clearTimeout(summarySearchTimer)
  summaryDepartment.value = ''
  summaryStatus.value = ''
  summaryLeaveType.value = ''
  summarySearchInput.value = ''
  summarySearch.value = ''
}

const summaryParams = () => ({
  ...reportParams(),
  include_inactive: includeInactive.value ? 1 : 0,
  ...(summaryDepartment.value && { department_id: summaryDepartment.value }),
  ...(summaryStatus.value && { status: summaryStatus.value }),
  ...(summaryLeaveType.value && { leave_type_id: summaryLeaveType.value }),
  ...(summarySearch.value && { search: summarySearch.value })
})

/*
|--------------------------------------------------------------------------
| Filter options come from existing records (departments, leave types)
|--------------------------------------------------------------------------
*/

interface FilterOption { value: string; label: string }
const departmentOptions = ref<FilterOption[]>([])
const leaveTypeOptions = ref<FilterOption[]>([])
const filterOptionsError = ref('')

async function loadFilterOptions() {
  const [departments, leaveTypes] = await Promise.allSettled([api.get('/departments'), api.get('/leave-types')])
  filterOptionsError.value = ''

  if (departments.status === 'fulfilled' && Array.isArray(departments.value.data)) {
    const list: any[] = departments.value.data
    const nameCounts = new Map<string, number>()
    list.forEach((item) => {
      const key = String(item.department_name ?? '').trim().toLowerCase()
      nameCounts.set(key, (nameCounts.get(key) ?? 0) + 1)
    })
    departmentOptions.value = list.map((item) => {
      const name = String(item.department_name ?? '').trim() || `Department ${item.department_id}`
      const duplicate = (nameCounts.get(name.toLowerCase()) ?? 0) > 1 && item.level
      return { value: String(item.department_id), label: duplicate ? `${name} (${item.level})` : name }
    }).sort((a, b) => a.label.localeCompare(b.label))
  } else {
    filterOptionsError.value = 'Department options could not be loaded.'
  }

  if (leaveTypes.status === 'fulfilled' && Array.isArray(leaveTypes.value.data)) {
    leaveTypeOptions.value = leaveTypes.value.data
      .map((item: any) => ({ value: String(item.leave_type_id), label: String(item.leave_type_name ?? '').trim() || `Leave type ${item.leave_type_id}` }))
      .sort((a: FilterOption, b: FilterOption) => a.label.localeCompare(b.label))
  } else {
    filterOptionsError.value = [filterOptionsError.value, 'Leave type options could not be loaded.'].filter(Boolean).join(' ')
  }
}
onMounted(loadFilterOptions)

const optionLabel = (options: FilterOption[], value: unknown) =>
  options.find((option) => option.value === String(value))?.label ?? `#${value}`

const statusLabels: Record<string, string> = { approved: 'Approved', pending: 'Pending', disapproved: 'Disapproved' }

// Describes the filters the CURRENT results were actually produced with (echoed by the API).
const summaryFilterText = computed(() => {
  const applied = summaryFilters.value
  const parts: string[] = []
  if (applied.department_id) {
    parts.push(`Department: ${applied.department_id === 'unassigned' ? 'Unassigned' : optionLabel(departmentOptions.value, applied.department_id)}`)
  }
  if (applied.status) parts.push(`Status: ${statusLabels[applied.status] ?? applied.status}`)
  if (applied.leave_type_id) parts.push(`Leave type: ${optionLabel(leaveTypeOptions.value, applied.leave_type_id)}`)
  if (applied.search) parts.push(`Employee: "${applied.search}"`)
  return parts.length ? parts.join(' · ') : 'None'
})

/*
|--------------------------------------------------------------------------
| Quick date selection (whole calendar periods; see utils/reportTable.ts)
|--------------------------------------------------------------------------
*/

const activeQuickRange = computed(() => matchQuickRange(dateRange.value.start, dateRange.value.end))
const summaryGeneratedAt = ref('')
const summaryPeriod = computed(() => {
  const { start_date, end_date } = summaryFilters.value
  if (!start_date && !end_date) return 'All dates'
  return `${start_date || 'Any start date'} to ${end_date || 'Any end date'}`
})
const formatSummaryDays = (value: unknown) => {
  const number = Number(value ?? 0)
  return Number.isFinite(number) ? number.toFixed(3) : '0.000'
}
const formatApprovedDays = (value: unknown) => {
  if (value === undefined || value === null || String(value).trim() === '') return 'Unavailable'
  const number = Number(value)
  return Number.isFinite(number) && number >= 0 ? number.toFixed(3) : 'Unavailable'
}
const summaryBackendWarning = computed(() => {
  if (!summaryLoaded.value) return ''
  const missing = formatApprovedDays(leaveTotals.value.approved_days) === 'Unavailable'
    || leaveSummaryData.value.some((row: any) => formatApprovedDays(row.approved_days) === 'Unavailable')
  return missing ? 'The backend response is missing approved-day totals. Update ReportController.php on the backend used by this page. Approved days are unavailable; they are not zero.' : ''
})
const summaryLoaded = ref(false)
let summaryRequestVersion = 0
let reportRefreshVersion = 0

const reportParams = () => ({
  ...(dateRange.value.start && { start_date: dateRange.value.start }),
  ...(dateRange.value.end && { end_date: dateRange.value.end })
})

const reportError = ref('')


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
    iconColor: 'text-blue-500'
  },

  {
    id: 'leave-credits',
    name: 'Leave Credits',
    description: 'Credit usage and availability',
    icon: CreditCard,
    iconColor: 'text-yellow-500'
  }

]


/*
|--------------------------------------------------------------------------
| Data
|--------------------------------------------------------------------------
*/

const leaveSummaryData = ref<any[]>([])

const creditsData = ref<any[]>([])
const creditsLoadedAt = ref('')

const leaveTotals = ref<any>({})

const loading = ref(false)


/*
|--------------------------------------------------------------------------
| Leave Summary table sorting (client-side; the API already applied the
| filters, and the Grand Total row always comes from the API totals)
|--------------------------------------------------------------------------
*/

const summarySort = ref<SortState | null>(null)
const summarySortKinds: Record<string, SortKind> = {
  department: 'text', total: 'number', approved: 'number', pending: 'number',
  disapproved: 'number', other: 'number', total_days: 'number', approved_days: 'number'
}
const summaryColumns = computed(() => [
  { key: 'department', label: 'Department', numeric: false },
  { key: 'total', label: 'Total Applications', numeric: true },
  { key: 'approved', label: 'Approved', numeric: true },
  { key: 'pending', label: 'Pending', numeric: true },
  { key: 'disapproved', label: 'Disapproved', numeric: true },
  ...(Number(leaveTotals.value.other || 0) > 0 ? [{ key: 'other', label: 'Other', numeric: true }] : []),
  { key: 'total_days', label: 'Requested Days', numeric: true },
  { key: 'approved_days', label: 'Requested Days of Approved Applications', numeric: true }
])
const sortedSummaryRows = computed(() =>
  sortRows(leaveSummaryData.value, summarySort.value, summarySortKinds, { tieBreak: 'department' }))

const sortMark = (sort: SortState | null, key: string) =>
  sort?.key === key ? (sort.dir === 'asc' ? '▲' : '▼') : '↕'
const ariaSort = (sort: SortState | null, key: string) =>
  sort?.key === key ? (sort.dir === 'asc' ? 'ascending' : 'descending') : 'none'


/*
|--------------------------------------------------------------------------
| Leave Credits table: search, filters, sorting and pagination.
|
| /leave-balances already returns every employee, so these controls work on
| that list in the browser (no extra requests). Totals are always computed
| from ALL matching rows, never from the current page.
|--------------------------------------------------------------------------
*/

const creditFilters = ref<CreditFilters>(emptyCreditFilters())
const creditSort = ref<SortState | null>(null)
const creditPage = ref(1)
const creditPageSize = ref(25) // 0 = all
const creditPageSizes = [10, 25, 50, 100, 0]
const creditSortKinds: Record<string, SortKind> = {
  employee_name: 'text', department_name: 'text', service_credits: 'number',
  vacation_balance: 'number', sick_balance: 'number', total_available: 'number', used_leave: 'number'
}
const creditColumns = [
  { key: 'employee_name', label: 'Employee', numeric: false },
  { key: 'department_name', label: 'Department', numeric: false },
  { key: 'service_credits', label: 'Remaining Local Credits', numeric: true },
  { key: 'vacation_balance', label: 'Vacation Balance', numeric: true },
  { key: 'sick_balance', label: 'Sick Balance', numeric: true },
  { key: 'total_available', label: 'Total Remaining Days', numeric: true },
  { key: 'used_leave', label: 'Used Leave (Overall)', numeric: true }
]
const balanceFilterLabels: Record<string, string> = {
  'with-balance': 'Has remaining days', zero: 'Zero remaining days', 'no-record': 'No balance record'
}

const hasCreditFilters = computed(() => hasActiveCreditFilters(creditFilters.value))
const filteredCreditRows = computed(() => filterCreditRows(creditsData.value, creditFilters.value))
const sortedCreditRows = computed(() =>
  sortRows(filteredCreditRows.value, creditSort.value, creditSortKinds, {
    tieBreak: 'employee_name',
    isMissing: (row: any) => row.has_balance_record === false
  }))
const creditPageInfo = computed(() => paginate(sortedCreditRows.value, creditPage.value, creditPageSize.value))
// Rows outside the current page stay in the DOM but are hidden on screen, so printing shows every match.
const visibleCreditIds = computed(() => new Set(creditPageInfo.value.items.map((row: any) => row.employee_id)))
const creditTotals = computed(() => summarizeCreditRows(filteredCreditRows.value))

const creditFilterText = computed(() => {
  const filters = creditFilters.value
  const parts: string[] = []
  if (filters.search.trim()) parts.push(`Employee: "${filters.search.trim()}"`)
  if (filters.department) {
    parts.push(`Department: ${filters.department === 'unassigned' ? 'Unassigned' : optionLabel(departmentOptions.value, filters.department)}`)
  }
  if (filters.status) parts.push(`Status: ${filters.status === 'active' ? 'Active' : 'Inactive'}`)
  if (filters.balance) parts.push(`Balance: ${balanceFilterLabels[filters.balance] ?? filters.balance}`)
  return parts.length ? parts.join(' · ') : 'None'
})

const clearCreditFilters = () => { creditFilters.value = emptyCreditFilters() }
const toggleCreditSort = (key: string) => { creditSort.value = nextSort(creditSort.value, key) }
const toggleSummarySort = (key: string) => { summarySort.value = nextSort(summarySort.value, key) }

// Any change to what is shown starts again from page 1; a page past the end is pulled back.
watch([creditFilters, creditSort, creditPageSize], () => { creditPage.value = 1 }, { deep: true })
watch(() => creditPageInfo.value.page, (page) => { if (page !== creditPage.value) creditPage.value = page })


/*
|--------------------------------------------------------------------------
| Computed Leave Summary Data
|--------------------------------------------------------------------------
*/

const leaveByDepartment = computed(() => {

  return leaveSummaryData.value.map((item: any) => ({
    department: item.department ?? 'Unassigned',
    count: Number(
      item.total ??
      item.count ??
      0
    ),
    days: Number(item.total_days ?? 0)
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
| Number Formatting Helper
| (matches LeaveBalances.vue so figures always agree)
|--------------------------------------------------------------------------
*/

const dayUnits = (value: unknown): number => {
  const number = Number(value ?? 0)
  if (!Number.isFinite(number)) throw new Error('Invalid leave balance value received from the API.')
  const units = Math.round(number * 1000)
  if (!Number.isSafeInteger(units)) throw new Error('Leave balance is outside the supported range.')
  return units
}
const formatNumber = (value: unknown) => (dayUnits(value) / 1000).toFixed(3)

// An employee with no leave balance record is not the same as a recorded 0.000 balance.
const formatCredit = (row: any, field: string) =>
  row.has_balance_record === false ? 'No record' : formatNumber(row[field])

const buildCreditRows = (employees: any[], balances: any[]) => {
  const balanceMap = new Map(balances.map((balance: any) => [Number(balance.employee_id), balance]))
  return employees.map((employee: any) => {
    const balance = balanceMap.get(Number(employee.employee_id)) as any
    const hasBalanceRecord = balance?.balance_id !== undefined && balance?.balance_id !== null
    const local = dayUnits(balance?.service_credits)
    const vacation = dayUnits(balance?.vacation_balance)
    const sick = dayUnits(balance?.sick_balance)
    const department = employee.department
    return {
      employee_id: employee.employee_id,
      department_id: employee.department_id ?? null,
      employment_status: employee.employment_status ?? '',
      search_text: buildSearchText(employee.first_name, employee.middle_name, employee.last_name, employee.extension_name),
      employee_name: [employee.last_name, employee.first_name].filter(Boolean).join(', ') || 'Unknown employee',
      department_name: employee.department_name || (typeof department === 'string' ? department : department?.department_name) || balance?.employee?.department_name || 'Unassigned',
      has_balance_record: hasBalanceRecord,
      service_credits: local / 1000,
      vacation_balance: vacation / 1000,
      sick_balance: sick / 1000,
      used_leave: dayUnits(balance?.used_leave) / 1000,
      total_available: (local + vacation + sick) / 1000
    }
  })
}
const summarizeCreditRows = (rows: any[]) => {
  const totals: Record<string, number> = {
    employees: rows.length,
    employees_without_record: rows.filter((row: any) => !row.has_balance_record).length
  }
  for (const field of ['service_credits', 'vacation_balance', 'sick_balance', 'used_leave', 'total_available']) {
    totals[field] = rows.reduce((sum: number, row: any) => sum + dayUnits(row[field]), 0) / 1000
  }
  return totals
}


/*
|--------------------------------------------------------------------------
| Percentage Helper
|--------------------------------------------------------------------------
*/

function calculatePercentage(value: number) {

  const total = Number(leaveTotals.value.applications ?? 0)

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
  const version = ++summaryRequestVersion
  try {

    const response =
      await api.get('/reports/leave-summary', { params: summaryParams() })

    if (version !== summaryRequestVersion || selectedReportType.value !== 'leave-summary') return
    summaryFilters.value = response.data.filters || { start_date: null, end_date: null, include_inactive: true }
    summaryGeneratedAt.value = response.data.generated_at || ''
    leaveSummaryData.value =
      response.data.summary || []

    leaveTotals.value =
      response.data.totals || {}
    summaryLoaded.value = true

    await nextTick()

    createLeaveCharts()

  } catch (error) {

    console.error(
      'Failed to load leave summary:',
      error
    )
    throw error

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

            borderColor: '#ffffff',

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

              color: '#64748b',

              padding: 18,

              usePointStyle: true,

              pointStyle: 'circle'

            }

          },

          tooltip: {

            backgroundColor: '#ffffff',

            titleColor: '#172033',

            bodyColor: '#64748b',

            borderColor: '#c8d8eb',

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
                'rgba(148, 163, 184, 0.18)'

            },

            ticks: {

              color: '#64748b'

            }

          },

          y: {

            beginAtZero: true,

            grid: {

              color:
                'rgba(148, 163, 184, 0.18)'

            },

            ticks: {

              color: '#64748b',

              precision: 0

            }

          }

        },

        plugins: {

          legend: {

            labels: {

              color: '#64748b'

            }

          },

          tooltip: {

            backgroundColor: '#ffffff',

            titleColor: '#172033',

            bodyColor: '#64748b',

            borderColor: '#c8d8eb',

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
|
| Pulls straight from the same /leave-balances endpoint that powers
| LeaveBalances.vue, so Service Credits / Vacation / Sick / Used Leave
| shown here always match the Leave Balances page exactly. Employees
| with no balance record yet still appear (defaulted to 0) so the list
| stays complete.
|--------------------------------------------------------------------------
*/

async function loadLeaveCredits(version: number) {
  const [response, employees] = await Promise.all([api.get('/leave-balances'), getEmployees()])
  if (!Array.isArray(response.data) || !Array.isArray(employees)) {
    throw new Error('Unexpected employee or leave balance response.')
  }
  const rows = buildCreditRows(employees, response.data)
  if (version !== reportRefreshVersion || selectedReportType.value !== 'leave-credits') return
  creditsData.value = rows
  creditsLoadedAt.value = new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' })
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
  const range = quickDateRange((event.target as HTMLSelectElement).value)

  // "All Dates / Custom Range" clears the period.
  dateRange.value = range ? { start: range.start, end: range.end } : { start: '', end: '' }
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
  if (loading.value || reportError.value) return

  let data: any[] = []

  let filename =
    'report.csv'


  switch (
    selectedReportType.value
  ) {

    case 'leave-summary': {
      data = sortedSummaryRows.value.map((item: any) => ({
        Department: item.department ?? 'Unassigned',
        'Total Applications': item.total ?? 0,
        Approved: item.approved ?? 0,
        Pending: item.pending ?? 0,
        Disapproved: item.disapproved ?? 0,
        Other: item.other ?? 0,
        'Requested Days': formatSummaryDays(item.total_days),
        'Approved Application Days': formatApprovedDays(item.approved_days)
      }))
      data.push({ Department: 'Grand Total',
        'Total Applications': leaveTotals.value.applications ?? 0,
        Approved: leaveTotals.value.approved ?? 0,
        Pending: leaveTotals.value.pending ?? 0,
        Disapproved: leaveTotals.value.disapproved ?? 0,
        Other: leaveTotals.value.other ?? 0,
        'Requested Days': formatSummaryDays(leaveTotals.value.total_days),
        'Approved Application Days': formatApprovedDays(leaveTotals.value.approved_days)
      })
      filename = 'leave-summary-report.csv'
      break
    }

    case 'leave-credits': {
      const exportRow = (employee: any) => ({
        Employee: employee.employee_name,
        Department: employee.department_name,
        'Remaining Local Credits': formatCredit(employee, 'service_credits'),
        'Vacation Balance': formatCredit(employee, 'vacation_balance'),
        'Sick Balance': formatCredit(employee, 'sick_balance'),
        'Total Remaining Days': formatCredit(employee, 'total_available'),
        'Used Leave (Overall)': formatCredit(employee, 'used_leave')
      })
      // Every matching employee (not only the current page), in the order shown on screen.
      if (sortedCreditRows.value.length === 0) {
        notify(hasCreditFilters.value ? 'No employee balances match the current filters.' : 'No employee balances available to export.')
        return
      }
      data = sortedCreditRows.value.map(exportRow)
      data.push(exportRow({ ...creditTotals.value, employee_name: 'Grand Total', department_name: '' }))
      filename = 'leave-credits-balances-report.csv'
      break
    }

  }


  /*
  |--------------------------------------------------------------------------
  | Check Data
  |--------------------------------------------------------------------------
  */

  if (data.length === 0) {

    notify(
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

    headers.map((header) => `"${header.replace(/"/g, '""')}"`).join(','),

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
    csvRows.join('\r\n')


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
  if (loading.value || reportError.value) return

  window.print()

}


/*
|--------------------------------------------------------------------------
| Refresh when the report or either date changes
|--------------------------------------------------------------------------
*/

watch(
  [selectedReportType, () => dateRange.value.start, () => dateRange.value.end, includeInactive,
    summaryDepartment, summaryStatus, summaryLeaveType, summarySearch],
  async () => {
    const version = ++reportRefreshVersion
    ++summaryRequestVersion
    reportError.value = ''
    if (selectedReportType.value === 'leave-summary' && dateRange.value.start && dateRange.value.end && dateRange.value.start > dateRange.value.end) {
      reportError.value = 'Start date must be on or before end date.'
      if (version === reportRefreshVersion) loading.value = false
      return
    }
    loading.value = true
    try {
      switch (selectedReportType.value) {
        case 'leave-summary':
          await loadLeaveSummary()
          break
        case 'leave-credits':
          await loadLeaveCredits(version)
          break
      }
    } catch (error) {
      console.error('Failed to load report:', error)
      if (version === reportRefreshVersion) reportError.value = 'Could not load the report. Please try again.'
    } finally {
      if (version === reportRefreshVersion) loading.value = false
    }
  },
  { immediate: true }
)

</script>


<style scoped>
/* Table controls: sortable headers, numeric alignment, pagination (Reports tables) */
.sort-button { display:inline-flex; align-items:center; gap:.35rem; padding:0; border:0; background:none; font:inherit; letter-spacing:inherit; text-transform:inherit; color:inherit; text-align:inherit; cursor:pointer; }
.sort-button:hover, .sort-button:focus-visible { color:#2563eb; }
.sort-button:focus-visible { outline:2px solid #7aa7e8; outline-offset:2px; border-radius:.25rem; }
.sort-mark { font-size:.65rem; opacity:.4; }
.sort-mark.active { opacity:1; color:#2563eb; }
.report-table .numeric-cell, .report-table th.numeric-cell { text-align:right; font-variant-numeric:tabular-nums; }
.report-table .numeric-cell .sort-button { justify-content:flex-end; }
.report-table .no-record-cell { color:var(--text-muted); font-style:italic; font-weight:400; }
.employee-name-cell { min-width:11rem; overflow-wrap:anywhere; }
.status-tag { display:inline-block; margin-left:.4rem; padding:.05rem .4rem; border-radius:999px; background:#e2e8f0; color:#475569; font-size:.7rem; font-weight:500; vertical-align:middle; }
.table-controls { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.75rem; }
.link-button { margin-left:.5rem; padding:0; border:0; background:none; color:#2563eb; font:inherit; text-decoration:underline; cursor:pointer; }
.empty-cell { text-align:center !important; }
.department-summary-table th .sort-button { text-transform:none; letter-spacing:normal; }
.department-summary-table th:not(:first-child) .sort-button { justify-content:flex-end; text-align:right; }
.department-summary-table td { font-variant-numeric:tabular-nums; }
/* Pages other than the current one are hidden on screen only, so printing lists every matching employee. */
@media screen { .report-table tr.off-page { display:none; } }
/* Keep the employee name visible while the wide table scrolls sideways on small screens. */
@media (max-width: 767px) {
  .report-table th:first-child, .report-table td:first-child { position:sticky; left:0; z-index:1; background:var(--surface); box-shadow:1px 0 0 var(--border); }
  .report-table thead th:first-child, .report-table tfoot th:first-child { background:var(--surface-muted); }
}
@media print {
  .report-filters-card, .table-controls, .sort-mark, .link-button { display:none !important; }
}

.approved-days-warning { color: #92400e; background: #fffbeb; border: 1px solid #fcd34d; }

.summary-table-wrapper { width:100%; overflow-x:auto; }
.department-summary-table { width:100%; border-collapse:collapse; color:var(--text); font-size:.875rem; }
.department-summary-table th, .department-summary-table td { padding:.8rem; border-bottom:1px solid var(--border, #cbd5e1); text-align:right; }
.department-summary-table th:first-child, .department-summary-table td:first-child { text-align:left; }
.department-summary-table thead, .department-summary-table tfoot { background:var(--surface-muted); font-weight:600; }
@media print {
  .summary-filter { display:none !important; }
  .summary-table-wrapper { overflow:visible; }
  .department-summary-table { font-size:9pt; }
  .department-summary-table th, .department-summary-table td { padding:5pt; }
  .department-summary-table tr { break-inside:avoid; }
}


/* =========================================================
   PAGE
   Dashboard theme
   ========================================================= */

.dashboard-shell {
  background: var(--app-bg);

  min-height: 100vh;
}


/* =========================================================
   MAIN CARDS
   ========================================================= */

.neo-card {
  background: var(--surface);

  border: 1px solid #cbd8e8;

  border-radius: 1rem;

  box-shadow:
    0 6px 18px rgba(23, 32, 51, 0.06);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;
}


.neo-card:hover {
  box-shadow:
    0 10px 24px rgba(23, 32, 51, 0.09);
}


/* =========================================================
   TEXT
   Dashboard text colors
   ========================================================= */

.neo-card .text-white {
  color: var(--text) !important;
}


.neo-card .text-gray-300,
.neo-card .text-gray-400,
.neo-card .text-gray-500 {
  color: var(--text-muted) !important;
}


/* =========================================================
   REPORT TYPE BUTTONS
   ========================================================= */

.report-type-button {
  border: 1px solid #c8d8eb;
}


.selected-report {
  border-color: #2563eb !important;

  background:
    linear-gradient(
      135deg,
      #eff6ff 0%,
      #ffffff 100%
    );

  box-shadow:
    0 8px 18px rgba(37, 99, 235, 0.08);
}


.selected-report:hover {
  border-color: #2563eb !important;

  background:
    linear-gradient(
      135deg,
      #eaf2ff 0%,
      #ffffff 100%
    );
}


.unselected-report {
  background: var(--surface-muted);

  border-color: #c8d8eb;
}


.unselected-report:hover {
  border-color: #9db9dc;

  background: #eef4fb;
}


/* =========================================================
   INPUTS
   ========================================================= */

.report-input {
  border: 1px solid #c8d8eb;

  border-radius: 0.6rem;

  background: var(--surface-muted);

  color: var(--text);

  padding:
    0.625rem
    0.75rem;

  font-size: 0.875rem;

  outline: none;

  transition:
    border-color 0.2s ease,
    background-color 0.2s ease,
    box-shadow 0.2s ease;
}


.report-input:focus {
  border-color: #7aa7e8;

  background: #ffffff;

  box-shadow:
    0 0 0 3px rgba(37, 99, 235, 0.12);
}


.report-input option {
  background: #ffffff;

  color: #172033;
}


/* =========================================================
   REPORT HEADER
   ========================================================= */

.report-header {
  border-color: var(--border);
}


/* =========================================================
   SECONDARY BUTTONS
   ========================================================= */

.secondary-button {
  background: #e2e8f0;

  color: #334155;
}


.secondary-button:hover {
  background: #cbd5e1;
}


/* =========================================================
   LEAVE SUMMARY
   ========================================================= */

.leave-summary {
  width: 100%;
}


/* =========================================================
   DEPARTMENT ITEMS
   Matches Dashboard application/summary cards
   ========================================================= */

.department-item {
  min-width: 0;

  background: var(--surface-muted);

  border: 1px solid #cfdaea;

  border-left: 3px solid #60a5fa;

  box-shadow:
    0 3px 10px rgba(23, 32, 51, 0.03);
}


.department-item:hover {
  background: #eef4fb;

  border-color: #b9c9dc;

  transform: translateY(-1px);
}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.empty-report-state {
  background: var(--surface-muted);

  border: 1px dashed #c8d8eb;
}


/* =========================================================
   SUMMARY PANELS
   ========================================================= */

.summary-panel {
  min-width: 0;

  background: var(--surface-muted);

  border: 1px solid #c8d8eb;

  box-shadow:
    0 3px 12px rgba(23, 32, 51, 0.03);
}


.summary-panel:hover {
  border-color: #b8cbe2;
}


/* =========================================================
   STATUS TRACK
   ========================================================= */

.status-track {
  background: #dce6f2;
}


/* =========================================================
   LEAVE TYPE ITEMS
   ========================================================= */

.leave-type-item {
  min-width: 0;

  background: var(--surface);

  border: 1px solid #cbd8e8;

  box-shadow:
    0 3px 10px rgba(23, 32, 51, 0.03);
}


.leave-type-item:hover {
  background: #eef4fb;

  border-color: #b9c9dc;

  transform: translateY(-1px);
}


/* =========================================================
   REPORT TABLES
   ========================================================= */

.report-table {
  border-collapse: collapse;
}


.report-table thead {
  background: var(--surface-muted);
}


.report-table th {
  color: var(--text-muted);

  border-bottom: 1px solid var(--border);

  letter-spacing: 0.05em;
}


.report-table tbody tr {
  border-bottom: 1px solid var(--border);

  transition:
    background-color 0.2s ease;
}


.report-table tbody tr:hover {
  background: #f3f7fc;
}


.report-table td {
  color: var(--text);
}


/* =========================================================
   LEAVE CREDIT SUMMARY CARDS
   Dashboard-style status colors
   ========================================================= */

.credit-summary-card {
  border: 1px solid;

  box-shadow:
    0 4px 12px rgba(23, 32, 51, 0.04);
}


/* Blue */

.credit-blue {
  background:
    linear-gradient(
      135deg,
      #ffffff 55%,
      #eff6ff 100%
    );

  border-color: #d6e4f7;
}


.credit-blue h4,
.credit-blue > p:last-child {
  color: #2563eb;
}


/* Green */

.credit-green {
  background:
    linear-gradient(
      135deg,
      #ffffff 55%,
      #f0fdf4 100%
    );

  border-color: #cce8d5;
}


.credit-green h4,
.credit-green > p:last-child {
  color: #16a34a;
}


/* Yellow */

.credit-yellow {
  background:
    linear-gradient(
      135deg,
      #ffffff 55%,
      #fffbeb 100%
    );

  border-color: #f1dfad;
}


.credit-yellow h4,
.credit-yellow > p:last-child {
  color: #a16207;
}


/* Purple */

.credit-purple {
  background:
    linear-gradient(
      135deg,
      #ffffff 55%,
      #faf5ff 100%
    );

  border-color: #e5d4f5;
}


.credit-purple h4,
.credit-purple > p:last-child {
  color: #9333ea;
}


/* =========================================================
   CHARTS
   ========================================================= */

canvas {
  max-width: 100%;
}


/* =========================================================
   TYPOGRAPHY
   ========================================================= */

.neo-card h3,
.neo-card h4,
.neo-card h5,
.neo-card p,
.neo-card span,
.neo-card button,
.neo-card label {
  letter-spacing: -0.01em;
}


/* =========================================================
   RESPONSIVE SPACING
   ========================================================= */

@media (max-width: 640px) {

  .dashboard-shell {
    padding: 1rem;
  }

  .neo-card {
    border-radius: 1rem;
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
  .leave-type-item,
  .credit-summary-card {
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