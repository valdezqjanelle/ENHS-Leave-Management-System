<template>
  <div class="dashboard-shell report-print-root min-h-screen">
    <div class="w-full space-y-4">
      <!-- Printed identification (hidden on screen) -->
      <header class="print-header">
        <p class="print-school">{{ SCHOOL_NAME }}</p>
        <h1 class="print-title">{{ reportDocument.title }}</h1>
        <dl class="print-meta">
          <div v-for="[label, value] in reportDocument.meta" :key="label">
            <dt>{{ label }}</dt>
            <dd>{{ value }}</dd>
          </div>
        </dl>
        <p v-for="note in reportDocument.notes" :key="note" class="print-note">{{ note }}</p>
      </header>

      <!-- ========================================================= -->
      <!-- PAGE INTRODUCTION (compact) + REPORT TYPE -->
      <!-- ========================================================= -->
      
      <div class="report-type-bar print-hide" role="group" aria-label="Report type">
        <button
          v-for="reportType in reportTypes"
          :key="reportType.id"
          type="button"
          :aria-pressed="selectedReportType === reportType.id"
          :class="['report-type-button', selectedReportType === reportType.id ? 'selected-report' : 'unselected-report']"
          @click="selectedReportType = reportType.id"
        >
          <component :is="reportType.icon" class="report-type-icon" :class="reportType.iconColor" aria-hidden="true" />
          <span class="report-type-text">
            <span class="report-type-name">{{ reportType.name }}</span>
            <span class="report-type-desc">{{ reportType.description }}</span>
          </span>
          <span v-if="selectedReportType === reportType.id" class="report-type-selected">
            <Check class="h-4 w-4" aria-hidden="true" />
            <span class="sr-only">Selected</span>
          </span>
        </button>
      </div>


      <!-- ========================================================= -->
      <!-- FILTERS: leave dates + applications (Leave Summary and Leave Application History) -->
      <!-- ========================================================= -->

      <div v-if="isApplicationReport" class="neo-card report-filters-card print-hide filters-card w-full">

        <div class="filters-head">
          <h3 class="text-base font-semibold text-white">Filters</h3>

          <div class="filters-actions">
            <label class="inline-check">
              <input v-model="includeInactive" type="checkbox" class="h-4 w-4" />
              Include inactive/resigned and archived employees
            </label>

            <button
              type="button"
              class="secondary-button rounded-lg px-3 py-1.5 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="!hasSummaryFilters"
              @click="clearSummaryFilters"
            >
              Clear filters
            </button>
          </div>
        </div>

        <div class="filters-groups">

          <fieldset class="filter-group">
            <legend>Leave dates <span>(applications overlapping this period)</span></legend>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
              <div>
                <label for="report-start" class="filter-label">Start date</label>
                <input id="report-start" v-model="dateRange.start" type="date" class="report-input w-full" />
              </div>

              <div>
                <label for="report-end" class="filter-label">End date</label>
                <input id="report-end" v-model="dateRange.end" type="date" class="report-input w-full" />
              </div>

              <div>
                <label for="report-quick" class="filter-label">Quick select</label>
                <select id="report-quick" :value="activeQuickRange" @change="setQuickDateRange" class="report-input w-full">
                  <option value="">All dates / custom</option>
                  <option value="today">Today</option>
                  <option value="week">This week</option>
                  <option value="month">This month</option>
                  <option value="quarter">This quarter</option>
                  <option value="year">This year</option>
                </select>
              </div>
            </div>
          </fieldset>

          <fieldset class="filter-group">
            <legend>Applications</legend>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 2xl:grid-cols-4">
              <div>
                <label for="summary-search" class="filter-label">Employee</label>
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
                <label for="summary-department" class="filter-label">Department</label>
                <select id="summary-department" v-model="summaryDepartment" class="report-input w-full">
                  <option value="">All departments</option>
                  <option value="unassigned">Unassigned</option>
                  <option v-for="option in departmentOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>

              <div>
                <label for="summary-leave-type" class="filter-label">Leave type</label>
                <select id="summary-leave-type" v-model="summaryLeaveType" class="report-input w-full">
                  <option value="">All leave types</option>
                  <option v-for="option in leaveTypeOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>

              <div>
                <label for="summary-status" class="filter-label">Status</label>
                <select id="summary-status" v-model="summaryStatus" class="report-input w-full">
                  <option value="">All statuses</option>
                  <option value="approved">Approved</option>
                  <option value="pending">Pending</option>
                  <option value="disapproved">Disapproved</option>
                </select>
              </div>
            </div>
          </fieldset>

        </div>

        <p v-if="filterOptionsError" role="status" class="mt-3 text-sm text-gray-400">
          {{ filterOptionsError }} Reload the page to try again.
        </p>
      </div>


      <!-- ========================================================= -->
      <!-- LEAVE CREDITS FILTERS -->
      <!-- ========================================================= -->

      <div v-else-if="selectedReportType === 'leave-credits'" class="neo-card report-filters-card print-hide w-full p-5 sm:p-6">

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

      <p v-if="loading" class="print-hide text-sm text-gray-400">Loading report...</p>
      <div v-if="reportError" role="alert" class="neo-card print-hide w-full p-4 text-red-400">
        {{ reportError }}
      </div>

      <div class="neo-card w-full overflow-hidden">

        <!-- Report Header: title, record count and the Export / Print actions -->
        <div class="report-header print-hide results-bar">

          <div class="min-w-0">
            <h3 class="text-base font-semibold text-white">
              {{ getCurrentReportName() }}
            </h3>

            <p class="text-sm text-gray-400" role="status" aria-live="polite">
              {{ resultSubtitle }}
            </p>
          </div>

          <div class="results-actions">
            <button type="button" @click="exportReport" class="secondary-button rounded-lg px-4 py-2 text-sm font-medium transition">
              Export CSV
            </button>

            <button type="button" @click="printReport" class="secondary-button rounded-lg px-4 py-2 text-sm font-medium transition">
              Print
            </button>
          </div>

        </div>


        <!-- ========================================================= -->
        <!-- LEAVE SUMMARY REPORT -->
        <!-- ========================================================= -->

        <template v-if="selectedReportType === 'leave-summary'">

          <div class="leave-summary p-4 sm:p-5">
            <p v-if="summaryBackendWarning" role="alert" class="approved-days-warning mb-4 rounded-lg p-4">{{ summaryBackendWarning }}</p>


            <!-- ===================================================== -->
            <!-- LEAVE SUMMARY OVERVIEW -->
            <!-- ===================================================== -->

            <div class="mb-4">
              <p class="summary-context print-hide mb-2 text-sm text-gray-400">
                <strong>Period:</strong> {{ summaryPeriod }} ·
                <strong>Employees:</strong> {{ summaryFilters.include_inactive ? 'including inactive and archived' : 'active, non-archived only' }} ·
                <strong>Filters:</strong> {{ summaryFilterText }} ·
                <strong>Matching:</strong> {{ leaveTotals.applications ?? 0 }} {{ Number(leaveTotals.applications ?? 0) === 1 ? 'application' : 'applications' }} in {{ leaveSummaryData.length }} {{ leaveSummaryData.length === 1 ? 'department' : 'departments' }}
                <template v-if="summaryGeneratedAt"> · <strong>Data as of:</strong> {{ formatGeneratedAt(new Date(summaryGeneratedAt)) }}</template>
              </p>
              <details class="report-notes print-hide mb-3">
                <summary>How these figures are calculated</summary>
                <p>Applications whose leave dates overlap the selected period. Requested days are the full requested days of every matching application; they are not credits deducted or days restricted to the period.</p>
                <p>Departments reflect current employee assignments. Non-deleted applications only; missing links are shown as Unassigned.</p>
              </details>
              <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 mb-3">
                <div class="summary-panel rounded-xl p-4"><p class="text-sm text-gray-400">Total Applications</p><p class="text-2xl font-semibold text-white">{{ leaveTotals.applications ?? 0 }}</p></div>
                <div class="summary-panel rounded-xl p-4"><p class="text-sm text-gray-400">Approved</p><p class="text-2xl font-semibold text-white">{{ leaveTotals.approved ?? 0 }}</p></div>
                <div class="summary-panel rounded-xl p-4"><p class="text-sm text-gray-400">Pending</p><p class="text-2xl font-semibold text-white">{{ leaveTotals.pending ?? 0 }}</p></div>
                <div class="summary-panel rounded-xl p-4"><p class="text-sm text-gray-400">Disapproved</p><p class="text-2xl font-semibold text-white">{{ leaveTotals.disapproved ?? 0 }}</p></div>
              </div>
              <p class="text-sm text-gray-400 mb-3">Total Requested Days: <strong>{{ formatSummaryDays(leaveTotals.total_days) }}</strong> · Requested Days of Approved Applications: <strong>{{ formatApprovedDays(leaveTotals.approved_days) }}</strong></p>
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


                <div v-if="Number(leaveTotals.applications || 0) > 0" class="space-y-5">

                  <!-- Approved -->
                  <div>

                    <div class="mb-2 flex items-center justify-between">

                      <span class="text-sm font-medium text-gray-300">
                        Approved
                      </span>

                      <span class="text-sm font-semibold text-green-400">
                        {{ statusChartData.approved }} <span class="status-pct">({{ statusPercent(statusChartData.approved) }})</span>
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
                        {{ statusChartData.pending }} <span class="status-pct">({{ statusPercent(statusChartData.pending) }})</span>
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
                        {{ statusChartData.disapproved }} <span class="status-pct">({{ statusPercent(statusChartData.disapproved) }})</span>
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

                <p v-if="Number(leaveTotals.other || 0) > 0" class="mt-4 text-sm text-gray-400">
                  Other/unrecognized status: <strong>{{ leaveTotals.other }}</strong> ({{ statusPercent(leaveTotals.other) }}) &mdash; included in the total, not in the three bars.
                </p>

                <div v-if="!Number(leaveTotals.applications || 0)" class="empty-report-state rounded-xl px-4 py-6 text-center">
                  <p class="text-sm text-gray-400">No applications match the current period and filters.</p>
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


            <!-- =================================================== -->
            <!-- APPLICATIONS FILED BY MONTH -->
            <!-- =================================================== -->

            <div class="summary-panel monthly-panel mt-4 rounded-xl p-4 sm:p-5">
              <div class="mb-3">
                <h3 class="text-base font-semibold text-white">Applications Filed by Month</h3>
                <p class="text-sm text-gray-400">
                  Counted by the month each application was filed, split by its <strong>current</strong> status. Earlier status changes are not stored, so this is not a history of approvals.
                </p>
              </div>

              <template v-if="monthlyTrend.length">
                <ul class="monthly-legend" aria-label="Legend">
                  <li><span class="swatch seg-approved"></span>Approved</li>
                  <li><span class="swatch seg-pending"></span>Pending</li>
                  <li><span class="swatch seg-disapproved"></span>Disapproved</li>
                  <li v-if="Number(leaveTotals.other || 0) > 0"><span class="swatch seg-other"></span>Other</li>
                </ul>

                <div class="monthly-chart" role="img" :aria-label="monthlyAriaLabel">
                  <div v-for="month in monthlyTrend" :key="month.month" class="month-col">
                    <span class="month-total">{{ month.submitted }}</span>
                    <div class="month-track">
                      <div class="month-bar" :style="{ height: monthBarHeight(month.submitted) }">
                        <span v-for="status in monthSegments" :key="status" v-show="Number(month[status] || 0) > 0" :class="['seg', 'seg-' + status]" :style="{ height: (Number(month[status] || 0) / Number(month.submitted) * 100) + '%' }"></span>
                      </div>
                    </div>
                    <span class="month-label">{{ monthLabel(month.month) }}</span>
                  </div>
                </div>

                <div class="monthly-table-wrap">
                  <table class="monthly-table">
                    <thead>
                      <tr>
                        <th scope="col">Month filed</th><th scope="col">Submitted</th><th scope="col">Approved</th><th scope="col">Pending</th><th scope="col">Disapproved</th><th v-if="Number(leaveTotals.other || 0) > 0" scope="col">Other</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="month in monthlyTrend" :key="month.month">
                        <th scope="row">{{ monthLabel(month.month) }}</th><td>{{ month.submitted }}</td><td>{{ month.approved }}</td><td>{{ month.pending }}</td><td>{{ month.disapproved }}</td><td v-if="Number(leaveTotals.other || 0) > 0">{{ month.other ?? 0 }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </template>

              <div v-else class="empty-report-state rounded-xl px-4 py-6 text-center">
                <p class="text-sm text-gray-400">No applications match the current period and filters, so there is no monthly trend to show.</p>
              </div>
            </div>
          </div>
          

        </template>


        <!-- ========================================================= -->
        <!-- LEAVE CREDITS -->
        <!-- ========================================================= -->

        <template v-else-if="selectedReportType === 'leave-credits'">

          <div class="p-5 sm:p-6">

            <p class="print-hide mb-4 text-sm text-gray-400">Current balance snapshot · Loaded: {{ creditsLoadedAt }}. Values are in days. Local credits are remaining credits after deductions; accumulated earned credits are shown in the Leave Credits module. Used Leave is the overall recorded usage, not only local credits. Date filters do not apply to this snapshot. Employee scope: all non-archived employees, including inactive/resigned; archived employees are not listed, and the inactive/archived option applies to Leave Summary only.</p>
            <p class="print-hide mb-4 text-sm text-gray-400"><strong>Filters:</strong> {{ creditFilterText }} · <strong>Matching employees:</strong> {{ creditTotals.employees }} of {{ creditsData.length }}</p>
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


        <!-- ========================================================= -->
        <!-- LEAVE APPLICATION HISTORY -->
        <!-- ========================================================= -->

        <template v-else-if="selectedReportType === 'leave-history'">

          <div class="leave-history p-4 sm:p-5">

            <p class="summary-context print-hide mb-2 text-sm text-gray-400">
              <strong>Period:</strong> {{ summaryPeriod }} ·
              <strong>Employees:</strong> {{ summaryFilters.include_inactive ? 'including inactive and archived' : 'active, non-archived only' }} ·
              <strong>Filters:</strong> {{ summaryFilterText }}
              <template v-if="summaryGeneratedAt"> · <strong>Data as of:</strong> {{ formatGeneratedAt(new Date(summaryGeneratedAt)) }}</template>
            </p>

            <details class="report-notes print-hide mb-3">
              <summary>What this report shows and does not show</summary>
              <p>One row per leave application whose leave dates overlap the selected period. <strong>Current Status</strong> is the application's status now; <strong>Status Last Changed</strong> is when it was last changed, when recorded.</p>
              <p>Not stored by the system, so not reported: a Division Office outcome, earlier status changes, and approval dates or signatories for each decision.</p>
            </details>

            <div class="mb-3 grid grid-cols-2 gap-3 lg:grid-cols-4">
              <div class="summary-panel rounded-xl p-4"><p class="text-sm text-gray-400">Matching Applications</p><p class="text-2xl font-semibold text-white">{{ historyTotals.records ?? 0 }}</p></div>
              <div class="summary-panel rounded-xl p-4"><p class="text-sm text-gray-400">Approved</p><p class="text-2xl font-semibold text-white">{{ historyTotals.approved ?? 0 }}</p></div>
              <div class="summary-panel rounded-xl p-4"><p class="text-sm text-gray-400">Pending</p><p class="text-2xl font-semibold text-white">{{ historyTotals.pending ?? 0 }}</p></div>
              <div class="summary-panel rounded-xl p-4"><p class="text-sm text-gray-400">Disapproved</p><p class="text-2xl font-semibold text-white">{{ historyTotals.disapproved ?? 0 }}</p></div>
            </div>
            <p class="mb-3 text-sm text-gray-400">Total Requested Days: <strong>{{ formatSummaryDays(historyTotals.requested_days) }}</strong> · Requested Days of Approved Applications: <strong>{{ formatApprovedDays(historyTotals.approved_days) }}</strong></p>
            <p v-if="Number(historyTotals.other || 0) > 0" class="mb-3 text-sm text-gray-400">Other/unrecognized status: {{ historyTotals.other }} application(s), included in the total.</p>

            <div class="table-controls mb-3">
              <p class="text-sm text-gray-400" role="status" aria-live="polite">
                <template v-if="historyPageInfo.total">
                  Showing <strong>{{ historyPageInfo.from }}&ndash;{{ historyPageInfo.to }}</strong> of <strong>{{ historyPageInfo.total }}</strong> matching {{ historyPageInfo.total === 1 ? 'application' : 'applications' }}
                </template>
                <template v-else><strong>0</strong> matching applications</template>
              </p>

              <label for="history-page-size" class="flex items-center gap-2 text-sm text-gray-400">
                Rows per page
                <select id="history-page-size" v-model.number="historyPageSize" class="report-input">
                  <option v-for="size in creditPageSizes" :key="size" :value="size">{{ size === 0 ? 'All' : size }}</option>
                </select>
              </label>
            </div>

            <div class="overflow-x-auto">
              <table class="report-table history-table w-full min-w-[1100px]">
                <thead>
                  <tr>
                    <th
                      v-for="column in historyColumns"
                      :key="column.key"
                      scope="col"
                      :aria-sort="column.sortable ? ariaSort(historySort, column.key) : undefined"
                      class="px-3 py-3 text-xs font-medium uppercase"
                      :class="column.numeric ? 'numeric-cell' : 'text-left'"
                    >
                      <button v-if="column.sortable" type="button" class="sort-button" :title="'Sort by ' + column.label" @click="toggleHistorySort(column.key)">
                        <span>{{ column.label }}</span><span class="sort-mark" :class="{ active: historySort?.key === column.key }" aria-hidden="true">{{ sortMark(historySort, column.key) }}</span>
                      </button>
                      <span v-else>{{ column.label }}</span>
                    </th>
                  </tr>
                </thead>

                <tbody>
                  <tr
                    v-for="row in sortedHistoryRows"
                    :key="row.leave_id"
                    :class="{ 'off-page': !visibleHistoryIds.has(row.leave_id) }"
                  >
                    <td class="employee-name-cell px-3 py-3 text-sm font-medium">
                      {{ row.employee_name }}
                      <span v-if="historyEmployeeTag(row)" class="status-tag">{{ historyEmployeeTag(row) }}</span>
                    </td>
                    <td class="px-3 py-3 text-sm">{{ row.department }}</td>
                    <td class="px-3 py-3 text-sm">{{ row.leave_type }}</td>
                    <td class="px-3 py-3 text-sm date-cell">{{ row.start_date || '—' }}</td>
                    <td class="px-3 py-3 text-sm date-cell">{{ row.end_date || '—' }}</td>
                    <td class="numeric-cell px-3 py-3 text-sm">{{ formatSummaryDays(row.requested_days) }}</td>
                    <td class="px-3 py-3 text-sm date-cell">{{ row.date_filed || '—' }}</td>
                    <td class="px-3 py-3 text-sm"><span :class="['status-pill', 'status-' + row.status]">{{ historyStatusLabels[row.status] ?? row.status }}</span></td>
                    <td class="px-3 py-3 text-sm date-cell">{{ statusChangedText(row.status_changed_at) || 'Not recorded' }}</td>
                    <td class="approval-cell px-3 py-3 text-sm">
                      <template v-if="approvalDetails(row).length">
                        <span v-for="detail in approvalDetails(row)" :key="detail" class="approval-line">{{ detail }}</span>
                      </template>
                      <span v-else class="no-record-cell">None recorded</span>
                    </td>
                  </tr>

                  <tr v-if="historyData.length === 0">
                    <td :colspan="historyColumns.length" class="empty-cell px-6 py-8 text-center text-gray-500">
                      {{ hasSummaryFilters ? 'No applications match the selected period, employee scope and filters.' : 'No applications match the selected period and employee scope.' }}
                      <button v-if="hasSummaryFilters" type="button" class="link-button" @click="clearSummaryFilters">Clear filters</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <nav v-if="historyPageInfo.pageCount > 1" class="table-controls mt-3" aria-label="Leave application history pagination">
              <p class="text-sm text-gray-400">Page {{ historyPageInfo.page }} of {{ historyPageInfo.pageCount }}</p>
              <div class="flex gap-2">
                <button type="button" class="secondary-button rounded-lg px-4 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-50" :disabled="historyPageInfo.page <= 1" @click="historyPage = historyPageInfo.page - 1">Previous</button>
                <button type="button" class="secondary-button rounded-lg px-4 py-2 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-50" :disabled="historyPageInfo.page >= historyPageInfo.pageCount" @click="historyPage = historyPageInfo.page + 1">Next</button>
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
  SCHOOL_NAME,
  buildCsvRows,
  downloadCsv,
  fileStamp,
  formatGeneratedAt,
  reportFilename,
  type ReportDocument
} from '@/utils/reportExport'

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
  CreditCard,
  ClipboardList,
  Check
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
    description: 'Totals by department, status and month',
    icon: FileText,
    iconColor: 'text-blue-500'
  },

  {
    id: 'leave-history',
    name: 'Leave Application History',
    description: 'Every application, with recorded decisions',
    icon: ClipboardList,
    iconColor: 'text-emerald-600'
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

// Applications filed per month, split by current status (Leave Summary only).
const monthlyTrend = ref<any[]>([])

// Leave Application History (shares the Leave Summary period and filters).
const historyData = ref<any[]>([])
const historyTotals = ref<any>({})
const isApplicationReport = computed(() => selectedReportType.value === 'leave-summary' || selectedReportType.value === 'leave-history')

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

/*
|--------------------------------------------------------------------------
| Leave Application History table (rows are already filtered by the API)
|--------------------------------------------------------------------------
*/

const historyStatusLabels: Record<string, string> = { approved: 'Approved', pending: 'Pending', disapproved: 'Disapproved', other: 'Other' }
const historyColumns = [
  { key: 'employee_name', label: 'Employee', numeric: false, sortable: true },
  { key: 'department', label: 'Department', numeric: false, sortable: true },
  { key: 'leave_type', label: 'Leave Type', numeric: false, sortable: true },
  { key: 'start_date', label: 'Leave Start', numeric: false, sortable: true },
  { key: 'end_date', label: 'Leave End', numeric: false, sortable: true },
  { key: 'requested_days', label: 'Requested Days', numeric: true, sortable: true },
  { key: 'date_filed', label: 'Date Filed', numeric: false, sortable: true },
  { key: 'status', label: 'Current Status', numeric: false, sortable: true },
  { key: 'status_changed_at', label: 'Status Last Changed', numeric: false, sortable: true },
  { key: 'approval_record', label: 'Recorded Approval Details', numeric: false, sortable: false }
]
const historySortKinds: Record<string, SortKind> = {
  employee_name: 'text', department: 'text', leave_type: 'text', start_date: 'text', end_date: 'text',
  requested_days: 'number', date_filed: 'text', status: 'text', status_changed_at: 'text'
}
const historySort = ref<SortState | null>(null)
const historyPage = ref(1)
const historyPageSize = ref(25) // 0 = all

const sortedHistoryRows = computed(() =>
  sortRows(historyData.value, historySort.value, historySortKinds, { tieBreak: 'employee_name' }))
const historyPageInfo = computed(() => paginate(sortedHistoryRows.value, historyPage.value, historyPageSize.value))
// Rows outside the current page stay in the DOM but are hidden on screen, so printing lists every match.
const visibleHistoryIds = computed(() => new Set(historyPageInfo.value.items.map((row: any) => row.leave_id)))
const toggleHistorySort = (key: string) => { historySort.value = nextSort(historySort.value, key) }
watch([historyData, historySort, historyPageSize], () => { historyPage.value = 1 })
watch(() => historyPageInfo.value.page, (page) => { if (page !== historyPage.value) historyPage.value = page })

const statusChangedText = (value: unknown) => {
  if (!value) return ''
  const date = new Date(String(value))
  return Number.isNaN(date.getTime()) ? '' : fileStamp(date)
}
const recommendationText = (row: any) =>
  row.recommendation_status ? `${historyStatusLabels[row.recommendation_status] ?? row.recommendation_status}` : ''
// Plain-language list of the approval details that were actually recorded for this application.
const approvalDetails = (row: any): string[] => {
  const details: string[] = []
  if (row.recommendation_status) details.push(`Recommendation: ${recommendationText(row)}`)
  if (row.recommendation_reason) details.push(`Recommendation reason: ${row.recommendation_reason}`)
  if (row.days_with_pay !== null && row.days_with_pay !== undefined) details.push(`Days with pay: ${row.days_with_pay}`)
  if (row.days_without_pay !== null && row.days_without_pay !== undefined) details.push(`Days without pay: ${row.days_without_pay}`)
  if (row.other_approval) details.push(`Other: ${row.other_approval}`)
  if (row.disapproval_reason) details.push(`Disapproval reason: ${row.disapproval_reason}`)
  if (row.admin_remarks) details.push(`Remarks: ${row.admin_remarks}`)
  return details
}
const historyEmployeeTag = (row: any) =>
  row.employee_state === 'archived' ? 'Archived' : row.employee_state === 'inactive' ? 'Inactive' : ''



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
    monthlyTrend.value = Array.isArray(response.data.monthly) ? response.data.monthly : []
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
| Load Leave Application History
|
| Same period, employee scope and filters as Leave Summary, so both reports
| (and their exports) describe the same applications.
|--------------------------------------------------------------------------
*/

async function loadLeaveHistory() {
  const version = ++summaryRequestVersion
  const response = await api.get('/reports/leave-history', { params: summaryParams() })
  if (version !== summaryRequestVersion || selectedReportType.value !== 'leave-history') return
  summaryFilters.value = response.data.filters || { start_date: null, end_date: null, include_inactive: true }
  summaryGeneratedAt.value = response.data.generated_at || ''
  historyData.value = Array.isArray(response.data.records) ? response.data.records : []
  historyTotals.value = response.data.totals || {}
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

const resultSubtitle = computed(() => {
  const count = (n: number, one: string, many: string) => `${n} ${n === 1 ? one : many}`
  switch (selectedReportType.value) {
    case 'leave-credits':
      return `${count(creditTotals.value.employees ?? 0, 'employee', 'employees')} · current balance snapshot`
    case 'leave-history':
      return `${count(Number(historyTotals.value.records ?? 0), 'application', 'applications')} · period ${summaryPeriod.value.toLowerCase()}`
    default:
      return `${count(Number(leaveTotals.value.applications ?? 0), 'application', 'applications')} in ${count(leaveSummaryData.value.length, 'department', 'departments')} · period ${summaryPeriod.value.toLowerCase()}`
  }
})

// Share of the matching applications, whole percent. Rounding can make the three shares total 99-101%.
const statusPercent = (count: unknown) => {
  const total = Number(leaveTotals.value.applications ?? 0)
  return total > 0 ? `${Math.round((Number(count || 0) / total) * 100)}%` : '0%'
}

// Monthly filed-applications chart (data: summary.monthly).
const monthSegments = computed(() => Number(leaveTotals.value.other || 0) > 0
  ? ['approved', 'pending', 'disapproved', 'other'] : ['approved', 'pending', 'disapproved'])
const monthlyMax = computed(() => Math.max(1, ...monthlyTrend.value.map((m: any) => Number(m.submitted || 0))))
const monthBarHeight = (submitted: unknown) => `${Math.max(2, (Number(submitted || 0) / monthlyMax.value) * 100)}%`
const monthLabel = (month: string) => {
  const [year, number] = String(month).split('-').map(Number)
  if (!year || !number) return String(month)
  return new Intl.DateTimeFormat('en-PH', { month: 'short', year: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(year, number - 1, 1)))
}
const monthlyAriaLabel = computed(() =>
  'Applications filed per month: ' + monthlyTrend.value.map((m: any) =>
    `${monthLabel(m.month)} ${m.submitted} (${m.approved} approved, ${m.pending} pending, ${m.disapproved} disapproved)`).join('; '))

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


/*
|--------------------------------------------------------------------------
| Administrative output (CSV export and print)
|
| One ReportDocument describes the report exactly as filtered on screen. It
| feeds both the CSV file and the printed header, so every output carries the
| same identification, period, scope, filters, totals and generation time.
|--------------------------------------------------------------------------
*/

// Time the output was produced (export click or print), not when the data loaded.
const outputAt = ref(new Date())

const summaryScopeText = computed(() => summaryFilters.value.include_inactive
  ? 'All linked employees, including inactive/resigned and archived'
  : 'Currently active, non-archived employees')

const reportDocument = computed<ReportDocument>(() => {
  const generated = formatGeneratedAt(outputAt.value)

  if (selectedReportType.value === 'leave-history') {
    const totals = historyTotals.value
    const showOther = Number(totals.other || 0) > 0
    return {
      title: 'Leave Application History Report',
      meta: [
        ['Reporting period', summaryPeriod.value],
        ['Employee scope', summaryScopeText.value],
        ['Filters', summaryFilterText.value],
        ['Matching applications', String(totals.records ?? 0)],
        ['Data as of', summaryGeneratedAt.value ? formatGeneratedAt(new Date(summaryGeneratedAt.value)) : 'Not loaded'],
        ['Generated', generated]
      ],
      notes: [
        'One row per leave application whose leave dates overlap the reporting period. Status is the current application status; earlier status changes are not stored, and "Status Last Changed" is the time of the most recent change when it was recorded.',
        'Approval details are shown only as recorded in the system. A Division Office outcome, approval dates and signatories per decision are not stored, so none is reported.'
      ],
      totals: [
        ['Matching applications', totals.records ?? 0],
        ['Approved applications', totals.approved ?? 0],
        ['Pending applications', totals.pending ?? 0],
        ['Disapproved applications', totals.disapproved ?? 0],
        ...(showOther ? [['Other/unrecognized status', totals.other] as [string, number]] : []),
        ['Requested days (all matching applications)', formatSummaryDays(totals.requested_days)],
        ['Requested days of approved applications', formatApprovedDays(totals.approved_days)]
      ],
      tables: [{
        title: 'Leave Applications',
        headers: ['Employee', 'Department', 'Leave Type', 'Leave Start', 'Leave End', 'Requested Days', 'Date Filed',
          'Current Status', 'Status Last Changed', 'Recommendation', 'Recommendation Reason', 'Days With Pay',
          'Days Without Pay', 'Other Approval Detail', 'Disapproval Reason', 'Remarks'],
        // Every matching application (not only the current page), in the order shown on screen.
        rows: sortedHistoryRows.value.map((row: any) => [
          historyEmployeeTag(row) ? `${row.employee_name} (${historyEmployeeTag(row)})` : row.employee_name,
          row.department, row.leave_type, row.start_date, row.end_date, formatSummaryDays(row.requested_days), row.date_filed,
          historyStatusLabels[row.status] ?? row.status, statusChangedText(row.status_changed_at),
          recommendationText(row), row.recommendation_reason, row.days_with_pay, row.days_without_pay,
          row.other_approval, row.disapproval_reason, row.admin_remarks
        ]),
        emptyMessage: hasSummaryFilters.value
          ? 'No applications match the selected period, employee scope and filters.'
          : 'No applications match the selected period and employee scope.'
      }]
    }
  }

  if (selectedReportType.value === 'leave-credits') {
    const totals = creditTotals.value
    return {
      title: 'Leave Credits Report',
      meta: [
        ['Report type', 'Current balance snapshot'],
        ['Balances loaded', creditsLoadedAt.value || 'Not loaded'],
        ['Reporting period', 'Not applicable (balances are current, not historical)'],
        ['Employee scope', 'All non-archived employees, including inactive/resigned'],
        ['Filters', creditFilterText.value],
        ['Employees listed', `${totals.employees ?? 0} of ${creditsData.value.length}`],
        ['Generated', generated]
      ],
      notes: [
        'Values are in days and reflect each employee\'s balance when loaded; no historical balances are shown.',
        'Total Remaining Days = remaining local credits + vacation balance + sick balance. Used Leave (Overall) is overall recorded usage and is not part of the remaining total.',
        'Employees with no balance record show "No record" and count as 0 in the totals.'
      ],
      totals: [
        ['Employees listed', totals.employees],
        ['Employees without a balance record', totals.employees_without_record],
        ['Remaining Local Credits', formatNumber(totals.service_credits)],
        ['Vacation Balance', formatNumber(totals.vacation_balance)],
        ['Sick Balance', formatNumber(totals.sick_balance)],
        ['Total Remaining Days', formatNumber(totals.total_available)],
        ['Used Leave (Overall)', formatNumber(totals.used_leave)]
      ],
      tables: [{
        title: 'Employee Leave Credits',
        headers: creditColumns.map((column) => column.label),
        // Every matching employee (not only the current page), in the order shown on screen.
        rows: sortedCreditRows.value.map((employee: any) => [
          String(employee.employment_status).toLowerCase() === 'inactive' ? `${employee.employee_name} (Inactive)` : employee.employee_name,
          employee.department_name || employee.department || 'Unknown',
          formatCredit(employee, 'service_credits'),
          formatCredit(employee, 'vacation_balance'),
          formatCredit(employee, 'sick_balance'),
          formatCredit(employee, 'total_available'),
          formatCredit(employee, 'used_leave')
        ]),
        footer: ['Grand Total', '', formatNumber(totals.service_credits), formatNumber(totals.vacation_balance),
          formatNumber(totals.sick_balance), formatNumber(totals.total_available), formatNumber(totals.used_leave)],
        emptyMessage: hasCreditFilters.value
          ? 'No employees match the current filters.'
          : 'No employee balances available.'
      }]
    }
  }

  const totals = leaveTotals.value
  const showOther = Number(totals.other || 0) > 0
  return {
    title: 'Leave Summary Report',
    meta: [
      ['Reporting period', summaryPeriod.value],
      ['Employee scope', summaryScopeText.value],
      ['Filters', summaryFilterText.value],
      ['Matching applications', `${totals.applications ?? 0} in ${leaveSummaryData.value.length} ${leaveSummaryData.value.length === 1 ? 'department' : 'departments'}`],
      ['Data as of', summaryGeneratedAt.value ? formatGeneratedAt(new Date(summaryGeneratedAt.value)) : 'Not loaded'],
      ['Generated', generated]
    ],
    notes: [
      'Includes applications whose leave dates overlap the reporting period. Requested Days are the full requested days of every matching application; Requested Days of Approved Applications counts only approved applications. Neither is credits deducted.',
      'Departments reflect current employee assignments; applications without a department link appear as Unassigned.'
    ],
    totals: [
      ['Total applications', totals.applications ?? 0],
      ['Approved applications', totals.approved ?? 0],
      ['Pending applications', totals.pending ?? 0],
      ['Disapproved applications', totals.disapproved ?? 0],
      ...(showOther ? [['Other/unrecognized status', totals.other] as [string, number]] : []),
      ['Requested days (all matching applications)', formatSummaryDays(totals.total_days)],
      ['Requested days of approved applications', formatApprovedDays(totals.approved_days)],
      ['Departments', leaveSummaryData.value.length]
    ],
    tables: [
      {
        title: 'Leave Summary by Department',
        headers: summaryColumns.value.map((column) => column.label),
        rows: sortedSummaryRows.value.map((item: any) => [
          item.department ?? 'Unassigned', item.total ?? 0, item.approved ?? 0, item.pending ?? 0, item.disapproved ?? 0,
          ...(showOther ? [item.other ?? 0] : []),
          formatSummaryDays(item.total_days), formatApprovedDays(item.approved_days)
        ]),
        footer: ['Grand Total', totals.applications ?? 0, totals.approved ?? 0, totals.pending ?? 0, totals.disapproved ?? 0,
          ...(showOther ? [totals.other] : []),
          formatSummaryDays(totals.total_days), formatApprovedDays(totals.approved_days)],
        emptyMessage: hasSummaryFilters.value
          ? 'No applications match the selected period, employee scope and filters.'
          : 'No applications match the selected period and employee scope.'
      },
      {
        title: 'Leave Summary by Leave Type',
        headers: ['Leave Type', 'Applications'],
        rows: leaveByType.value.map((leave) => [leave.name, leave.count]),
        footer: ['Total', leaveByType.value.reduce((sum, leave) => sum + Number(leave.count), 0)],
        emptyMessage: 'No leave type data available.'
      },
      {
        title: 'Applications Filed by Month (grouped by filing date; status is the current status)',
        headers: ['Month Filed', 'Submitted', 'Approved', 'Pending', 'Disapproved', ...(showOther ? ['Other'] : [])],
        rows: monthlyTrend.value.map((m: any) => [
          m.month, m.submitted, m.approved, m.pending, m.disapproved, ...(showOther ? [m.other ?? 0] : [])
        ]),
        footer: ['Total', ...(['submitted', 'approved', 'pending', 'disapproved'] as const).map((key) =>
          monthlyTrend.value.reduce((sum: number, m: any) => sum + Number(m[key] || 0), 0)),
          ...(showOther ? [monthlyTrend.value.reduce((sum: number, m: any) => sum + Number(m.other || 0), 0)] : [])],
        emptyMessage: 'No applications match the selected period and filters.'
      }
    ]
  }
})

const exportReport = () => {
  if (loading.value || reportError.value) return

  outputAt.value = new Date()
  const report = reportDocument.value
  const slugs: Record<string, string> = {
    'leave-summary': 'leave-summary-report',
    'leave-history': 'leave-application-history-report',
    'leave-credits': 'leave-credits-balances-report'
  }
  const primary = report.tables[0]
  const empty = !primary || primary.rows.length === 0

  downloadCsv(
    reportFilename(slugs[selectedReportType.value] ?? 'report', outputAt.value),
    buildCsvRows(report)
  )

  // The file still documents the period, scope and filters, so an empty result is exported rather than blocked.
  if (empty && primary) notify(`${primary.emptyMessage} The exported file lists no records.`)
}


/*
|--------------------------------------------------------------------------
| Print
|
| The page title doubles as the suggested file name for "Save as PDF". The
| print header is refreshed on beforeprint so Ctrl+P and the Print button
| produce the same output.
|--------------------------------------------------------------------------
*/

let savedTitle = ''
const prepareForPrint = () => {
  outputAt.value = new Date()
  savedTitle = document.title
  document.title = `${reportDocument.value.title} ${fileStamp(outputAt.value)}`
}
const restoreAfterPrint = () => { if (savedTitle) document.title = savedTitle; savedTitle = '' }

// Page size, margins and "Page X of Y" apply only while the Reports page is open.
const printPageCss = (orientation: 'portrait' | 'landscape') => `@page { size: A4 ${orientation}; margin: 14mm 12mm 16mm;
  @bottom-center { content: "Page " counter(page) " of " counter(pages); font-size: 8pt; color: #444; }
  @bottom-right { content: "${SCHOOL_NAME}"; font-size: 8pt; color: #444; } }`
let pageStyle: HTMLStyleElement | null = null
// The history table has ten columns, so it prints on a landscape page.
const printOrientation = computed(() => selectedReportType.value === 'leave-history' ? 'landscape' : 'portrait')
watch(printOrientation, (orientation) => { if (pageStyle) pageStyle.textContent = printPageCss(orientation) })

onMounted(() => {
  pageStyle = document.createElement('style')
  pageStyle.setAttribute('data-report-print-page', '')
  pageStyle.textContent = printPageCss(printOrientation.value)
  document.head.appendChild(pageStyle)
  window.addEventListener('beforeprint', prepareForPrint)
  window.addEventListener('afterprint', restoreAfterPrint)
})
onBeforeUnmount(() => {
  window.removeEventListener('beforeprint', prepareForPrint)
  window.removeEventListener('afterprint', restoreAfterPrint)
  pageStyle?.remove()
  restoreAfterPrint()
})

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
    if (isApplicationReport.value && dateRange.value.start && dateRange.value.end && dateRange.value.start > dateRange.value.end) {
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
        case 'leave-history':
          await loadLeaveHistory()
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
    padding: 0;
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


/* ---- Compact page hierarchy: intro, report type, grouped filters, results bar ---- */
.report-type-bar { display:grid; grid-template-columns:repeat(auto-fit, minmax(14rem, 1fr)); gap:.5rem; }
.report-type-button { display:flex; align-items:center; gap:.65rem; width:100%; padding:.6rem .85rem; border-radius:.75rem; text-align:left; cursor:pointer; }
.report-type-button:focus-visible { outline:2px solid #2563eb; outline-offset:2px; }
.report-type-icon { flex:none; width:1.5rem; height:1.5rem; }
.report-type-text { display:flex; flex-direction:column; min-width:0; flex:1; }
.report-type-name { font-weight:600; font-size:.9rem; color:var(--text); }
.report-type-desc { font-size:.75rem; color:var(--text-muted); line-height:1.25; }
.selected-report .report-type-name { color:#1d4ed8; }
.report-type-selected { flex:none; display:inline-flex; align-items:center; justify-content:center; width:1.35rem; height:1.35rem; border-radius:999px; background:#2563eb; color:#fff; }
.filters-card { padding:.85rem 1rem 1rem; }
.filters-head { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.5rem .75rem; margin-bottom:.65rem; }
.filters-actions { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem 1rem; }
.inline-check { display:inline-flex; align-items:center; gap:.5rem; font-size:.85rem; color:var(--text-muted); }
.filters-groups { display:grid; align-items:start; gap:.75rem; grid-template-columns:minmax(0,1fr); }
@media (min-width:1280px) { .filters-groups { grid-template-columns:minmax(0,5fr) minmax(0,7fr); } }
.filter-group { min-width:0; margin:0; padding:.6rem .75rem .75rem; border:1px solid var(--border, #cbd5e1); border-radius:.75rem; }
.filter-group legend { padding:0 .35rem; font-size:.72rem; font-weight:600; letter-spacing:.04em; text-transform:uppercase; color:var(--text-muted); }
.filter-group legend span { font-weight:400; text-transform:none; letter-spacing:0; }
.filter-label { display:block; margin-bottom:.25rem; font-size:.8rem; font-weight:500; color:var(--text-muted); }
.filter-group .report-input { padding:.45rem .65rem; }
.results-bar { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.5rem .75rem; padding:.75rem 1rem; border-bottom:1px solid var(--border, #cbd5e1); }
.results-actions { display:flex; gap:.5rem; }
.report-notes { font-size:.85rem; color:var(--text-muted); }
.report-notes summary { cursor:pointer; width:max-content; color:#2563eb; }
.report-notes summary:focus-visible { outline:2px solid #2563eb; outline-offset:2px; }
.report-notes p { margin:.35rem 0 0; }
.status-pct { font-weight:400; color:var(--text-muted); }

/* ---- Applications filed by month ---- */
.monthly-legend { display:flex; flex-wrap:wrap; gap:.25rem 1rem; margin:0 0 .6rem; padding:0; list-style:none; font-size:.8rem; color:var(--text-muted); }
.monthly-legend li { display:inline-flex; align-items:center; gap:.35rem; }
.swatch { width:.75rem; height:.75rem; border-radius:.2rem; display:inline-block; }
.seg-approved { background:#22c55e; } .seg-pending { background:#eab308; } .seg-disapproved { background:#ef4444; } .seg-other { background:#94a3b8; }
.monthly-chart { display:flex; align-items:flex-end; gap:.5rem; overflow-x:auto; padding-bottom:.25rem; }
.month-col { flex:1 0 2.75rem; display:flex; flex-direction:column; align-items:center; gap:.2rem; min-width:2.75rem; }
.month-total { font-size:.8rem; font-weight:600; font-variant-numeric:tabular-nums; color:var(--text); }
.month-track { display:flex; align-items:flex-end; width:100%; height:7.5rem; }
.month-bar { display:flex; flex-direction:column-reverse; width:70%; margin:0 auto; overflow:hidden; border-radius:.3rem .3rem 0 0; background:#dce6f2; }
.month-bar .seg { display:block; width:100%; }
.month-label { font-size:.7rem; color:var(--text-muted); white-space:nowrap; }
.monthly-table-wrap { margin-top:.75rem; max-height:13rem; overflow:auto; }
.monthly-table { width:100%; border-collapse:collapse; font-size:.8rem; color:var(--text); }
.monthly-table th, .monthly-table td { padding:.3rem .6rem; border-bottom:1px solid var(--border, #cbd5e1); text-align:right; font-variant-numeric:tabular-nums; }
.monthly-table th:first-child { text-align:left; font-weight:500; }
.monthly-table thead th { position:sticky; top:0; background:var(--surface-muted); font-weight:600; }

/* ---- Leave Application History ---- */
.history-table td { vertical-align:top; }
.date-cell { white-space:nowrap; font-variant-numeric:tabular-nums; }
.approval-cell { min-width:14rem; max-width:24rem; overflow-wrap:anywhere; }
.approval-line { display:block; }
.status-pill { display:inline-block; padding:.1rem .55rem; border-radius:999px; font-size:.75rem; font-weight:600; }
.status-approved { background:#dcfce7; color:#166534; }
.status-pending { background:#fef9c3; color:#854d0e; }
.status-disapproved { background:#fee2e2; color:#991b1b; }
.status-other { background:#e2e8f0; color:#475569; }
</style>
<!--
  Print layout. Not scoped because it has to reach the shared Layout shell (sidebar, top bar and the
  fixed-height app frame). The :has() selector limits every rule to the Reports page, so printing other
  pages is unchanged.
-->
<style>
.report-print-root .print-header { display: none; }

@media print {
  html:has(.report-print-root),
  html:has(.report-print-root) body,
  html:has(.report-print-root) #app {
    height: auto !important;
    overflow: visible !important;
    background: #fff !important;
    color: #000 !important;
  }
  html:has(.report-print-root) .app-shell,
  html:has(.report-print-root) .main-shell,
  html:has(.report-print-root) .content-panel {
    display: block !important;
    width: auto !important;
    height: auto !important;
    min-height: 0 !important;
    overflow: visible !important;
    padding: 0 !important;
    background: #fff !important;
  }
  html:has(.report-print-root) .sidebar,
  html:has(.report-print-root) .topbar,
  html:has(.report-print-root) .mobile-toggle,
  html:has(.report-print-root) .notification-stack,
  html:has(.report-print-root) .confirmation-backdrop { display: none !important; }

  .report-print-root { min-height: 0 !important; padding: 0 !important; background: #fff !important; }
  .report-print-root .print-hide { display: none !important; }
  .report-print-root .neo-card { overflow: visible !important; }
  .report-print-root .space-y-4 > * + * { margin-top: 10pt !important; }

  /* Report identification */
  .report-print-root .print-header { display: block; margin-bottom: 10pt; color: #000; font-size: 9pt; }
  .report-print-root .print-school { margin: 0; font-size: 10pt; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
  .report-print-root .print-title { margin: 2pt 0 6pt; font-size: 16pt; font-weight: 700; line-height: 1.2; }
  .report-print-root .print-meta { margin: 0 0 6pt; border-top: 1pt solid #000; border-bottom: 1pt solid #000; padding: 4pt 0; }
  .report-print-root .print-meta > div { display: flex; gap: 6pt; padding: 1pt 0; break-inside: avoid; }
  .report-print-root .print-meta dt { flex: 0 0 105pt; margin: 0; font-weight: 700; }
  .report-print-root .print-meta dd { flex: 1; margin: 0; }
  .report-print-root .print-note { margin: 2pt 0 0; font-size: 8pt; color: #333; }

  /* Tables: fit the page, repeat headers, keep rows whole, print the grand total once at the end */
  .report-print-root .overflow-x-auto,
  .report-print-root .summary-table-wrapper { overflow: visible !important; }
  .report-print-root table { width: 100% !important; min-width: 0 !important; table-layout: auto; border-collapse: collapse; font-size: 8.5pt; }
  .report-print-root thead { display: table-header-group; }
  .report-print-root tfoot { display: table-row-group; }
  .report-print-root tr { break-inside: avoid; page-break-inside: avoid; }
  .report-print-root th,
  .report-print-root td { padding: 3pt 5pt !important; border: .5pt solid #888 !important; background: #fff !important; color: #000 !important; overflow-wrap: break-word; }
  .report-print-root th { white-space: nowrap; }
  /* A <button> inside a header cell stops Chromium repeating the header row on later pages. */
  .report-print-root th .sort-button { display: contents !important; }
  .report-print-root .department-summary-table th { white-space: normal; }
  .report-print-root td:first-child { min-width: 60pt; }
  .report-print-root thead th,
  .report-print-root tfoot th,
  .report-print-root tfoot td { background: #e8e8e8 !important; font-weight: 700; }
  .report-print-root .report-table th:first-child,
  .report-print-root .report-table td:first-child { position: static !important; box-shadow: none !important; }
  .report-print-root h3, .report-print-root h4, .report-print-root h5 { break-after: avoid; }
  .report-print-root .summary-panel,
  .report-print-root .credit-summary-card,
  .report-print-root .leave-type-item { break-inside: avoid; }
  .report-print-root .status-track,
  .report-print-root .status-track > div { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .report-print-root .month-bar,
  .report-print-root .month-bar .seg,
  .report-print-root .swatch,
  .report-print-root .status-pill { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .report-print-root .monthly-chart { overflow: visible; break-inside: avoid; }
  .report-print-root .month-track { height: 5rem; }
  .report-print-root .monthly-table-wrap { max-height: none !important; overflow: visible !important; }
  .report-print-root .monthly-table th, .report-print-root .monthly-table td { padding: 2pt 5pt !important; font-size: 8pt; }
  .report-print-root .monthly-table thead th { position: static; }
  .report-print-root .history-table { font-size: 8pt; }
  .report-print-root .history-table .approval-cell { min-width: 0; max-width: none; }
  .report-print-root .date-cell { white-space: nowrap; }
  .report-print-root .status-pill { padding: 0; background: none !important; font-weight: 700; color: #000 !important; }
}
</style>
