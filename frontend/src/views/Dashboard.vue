<template>
  <div class="dashboard-shell p-8 min-h-screen space-y-8">

    <!-- ========================================================= -->
    <!-- LOADING STATE -->
    <!-- ========================================================= -->
    <div
      v-if="loading"
      class="text-center py-10 text-white"
    >
      Loading dashboard...
    </div>

    <!-- ========================================================= -->
    <!-- ERROR STATE -->
    <!-- ========================================================= -->
    <div
      v-else-if="loadError"
      class="neo-card p-6 border-l-4 border-red-600 text-white space-y-2"
    >
      <h3 class="text-lg font-semibold">
        Couldn't load your dashboard
      </h3>

      <p class="text-sm text-gray-300">
        {{ loadError }}
      </p>

      <button
        @click="retryLoad"
        class="mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition"
      >
        Retry
      </button>
    </div>

    <!-- ========================================================= -->
    <!-- ADMIN DASHBOARD -->
    <!-- ========================================================= -->
    <div
      v-else-if="isAdmin"
      class="space-y-8"
    >

      <!-- ======================================================= -->
      <!-- STATISTICS CARDS -->
      <!-- ======================================================= -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Employees -->
        <div class="neo-card stats-card border-blue-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
              <Users class="w-6 h-6 text-blue-600" />
            </div>

            <div class="ml-5">
              <h3 class="text-sm text-white">
                Total Employees
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ totalEmployees }}
              </p>
            </div>
          </div>
        </div>

        <!-- Pending -->
        <div class="neo-card stats-card border-orange-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg">
              <Clock class="w-6 h-6 text-yellow-600" />
            </div>

            <div class="ml-4">
              <h3 class="text-sm text-white">
                Pending Leaves
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ pendingLeaves }}
              </p>
            </div>
          </div>
        </div>

        <!-- Approved -->
        <div class="neo-card stats-card border-green-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircle class="w-6 h-6 text-green-600" />
            </div>

            <div class="ml-4">
              <h3 class="text-sm text-white">
                Approved Leaves
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ approvedLeaves }}
              </p>
            </div>
          </div>
        </div>

        <!-- Disapproved -->
        <div class="neo-card stats-card border-red-700 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
              <AlertCircle class="w-6 h-6 text-red-600" />
            </div>

            <div class="ml-4">
              <h3 class="text-sm text-white">
                Disapproved Leaves
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ disapprovedLeaves }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================================================= -->
      <!-- RECENT APPLICATIONS / PENDING REQUESTS -->
      <!-- ======================================================= -->
      <div class="grid lg:grid-cols-2 gap-6">

        <!-- Recent Applications -->
        <div class="neo-card p-6">

          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-white">
              Recent Leave Applications
            </h3>

            <span
              class="text-xs px-3 py-1 rounded-full bg-blue-500/20 text-blue-300"
            >
              {{ recentApplications.length }}
            </span>
          </div>

          <div
            v-if="recentApplications.length"
            class="space-y-3"
          >
            <div
              v-for="app in recentApplications"
              :key="app.id"
              class="application-item flex justify-between items-center p-4 rounded-xl"
            >

              <div class="flex items-center min-w-0">

                <div
                  class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center mr-4 flex-shrink-0 font-bold"
                >
                  {{ getEmployeeInitial(app) }}
                </div>

                <div class="min-w-0">
                  <p class="font-medium text-white truncate">
                    {{ getEmployeeName(app) }}
                  </p>

                  <p class="text-sm text-gray-300 truncate">
                    {{ getLeaveType(app) }}
                  </p>
                </div>
              </div>

              <div class="text-right ml-4 flex-shrink-0">

                <span
                  :class="getStatusClass(getAppStatus(app))"
                  class="inline-block px-2 py-1 rounded-full text-xs font-semibold"
                >
                  {{ formatStatus(getAppStatus(app)) }}
                </span>

                <p class="text-xs text-gray-400 mt-1">
                  {{ app.date || app.created_at || "" }}
                </p>
              </div>
            </div>
          </div>

          <p
            v-else
            class="text-gray-400 text-sm"
          >
            No recent applications.
          </p>
        </div>

        <!-- Pending Requests -->
        <div class="neo-card p-6">

          <div class="flex justify-between items-center mb-4">

            <h3 class="text-lg font-semibold text-white">
              Leave Requests Needing Action
            </h3>

            <span
              v-if="pendingRequests.length"
              class="text-xs px-3 py-1 rounded-full bg-yellow-500/20 text-yellow-300"
            >
              {{ pendingRequests.length }} Pending
            </span>

          </div>

          <div
            v-if="pendingRequests.length"
            class="space-y-3"
          >

            <div
              v-for="request in pendingRequests"
              :key="request.id"
              class="request-item flex justify-between items-center p-4 rounded-xl"
            >

              <div class="min-w-0">

                <p class="font-medium text-white truncate">
                  {{ getEmployeeName(request) }}
                </p>

                <p class="text-sm text-gray-300">
                  {{ getLeaveType(request) }}

                  <span v-if="getDays(request) !== null">
                    • {{ getDays(request) }}
                    {{ getDays(request) === 1 ? "day" : "days" }}
                  </span>
                </p>

                <p
                  v-if="request.start_date || request.end_date"
                  class="text-xs text-gray-400 mt-1"
                >
                  {{ request.start_date || "" }}

                  <span
                    v-if="request.start_date && request.end_date"
                  >
                    to
                  </span>

                  {{ request.end_date || "" }}
                </p>

              </div>

              <button
                @click="reviewRequest(request)"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition flex-shrink-0 ml-4"
              >
                Review
              </button>

            </div>
          </div>

          <div
            v-else
            class="py-8 text-center"
          >
            <div
              class="w-12 h-12 mx-auto mb-3 rounded-full bg-green-500/10 flex items-center justify-center"
            >
              <CheckCircle class="w-6 h-6 text-green-400" />
            </div>

            <p class="text-white font-medium">
              No pending requests
            </p>

            <p class="text-gray-400 text-sm mt-1">
              All leave applications have been reviewed.
            </p>
          </div>

        </div>
      </div>

      <!-- ======================================================= -->
      <!-- LEAVE ANALYTICS -->
      <!-- ======================================================= -->
      <div class="neo-card p-6">

        <div class="flex justify-between items-center mb-6">

          <div>
            <h3 class="text-xl font-semibold text-white">
              Leave Analytics
            </h3>

            <p class="text-sm text-gray-400 mt-1">
              Overview of leave applications by type and department
            </p>
          </div>

        </div>

        <!-- ===================================================== -->
        <!-- CHARTS -->
        <!-- ===================================================== -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

          <!-- =================================================== -->
          <!-- PIE CHART -->
          <!-- =================================================== -->
          <div class="chart-card">

            <h4 class="text-lg font-semibold text-white mb-6">
              Leave by Type
            </h4>

            <div
              v-if="pieChartData.length"
              class="flex flex-col md:flex-row items-center justify-center gap-8"
            >

              <!-- PIE -->
              <div class="relative flex-shrink-0">

                <svg
                  viewBox="0 0 200 200"
                  class="w-64 h-64"
                >

                  <path
                    v-for="slice in pieChartData"
                    :key="slice.name"
                    :d="slice.path"
                    :fill="slice.color"
                    stroke="#0B1420"
                    stroke-width="2"
                  />

                  <!-- Center -->
                  <circle
                    cx="100"
                    cy="100"
                    r="45"
                    fill="#0B1420"
                  />

                  <text
                    x="100"
                    y="94"
                    text-anchor="middle"
                    fill="white"
                    font-size="20"
                    font-weight="700"
                  >
                    {{ totalLeaveByType }}
                  </text>

                  <text
                    x="100"
                    y="116"
                    text-anchor="middle"
                    fill="#94a3b8"
                    font-size="10"
                  >
                    TOTAL
                  </text>

                </svg>

              </div>

              <!-- LEGEND -->
              <div class="space-y-3 w-full md:w-auto">

                <div
                  v-for="item in pieChartData"
                  :key="item.name"
                  class="flex items-center justify-between gap-6"
                >

                  <div class="flex items-center min-w-0">

                    <span
                      class="w-3 h-3 rounded-full mr-3 flex-shrink-0"
                      :style="{ backgroundColor: item.color }"
                    ></span>

                    <span class="text-sm text-gray-300 truncate">
                      {{ item.name }}
                    </span>

                  </div>

                  <div class="text-right flex-shrink-0">

                    <span class="text-sm font-semibold text-white">
                      {{ item.value }}
                    </span>

                    <span class="text-xs text-gray-500 ml-1">
                      ({{ item.percentage }}%)
                    </span>

                  </div>

                </div>

              </div>

            </div>

            <!-- Empty -->
            <div
              v-else
              class="h-80 flex items-center justify-center text-gray-400"
            >
              No leave type data available.
            </div>

          </div>

          <!-- =================================================== -->
          <!-- BAR GRAPH -->
          <!-- =================================================== -->
          <div class="chart-card">

            <h4 class="text-lg font-semibold text-white mb-6">
              Leave by Department
            </h4>

            <div
              v-if="departmentChartData.length"
              class="department-chart"
            >

              <!-- Y AXIS -->
              <div class="chart-y-axis">

                <span>
                  {{ departmentMax }}
                </span>

                <span>
                  {{ Math.round(departmentMax * 0.75) }}
                </span>

                <span>
                  {{ Math.round(departmentMax * 0.5) }}
                </span>

                <span>
                  {{ Math.round(departmentMax * 0.25) }}
                </span>

                <span>0</span>

              </div>

              <!-- GRAPH -->
              <div class="chart-area">

                <!-- GRID -->
                <div class="chart-grid">
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                </div>

                <!-- BARS -->
                <div class="bars-container">

                  <div
                    v-for="department in departmentChartData"
                    :key="department.name"
                    class="bar-wrapper"
                  >

                    <div class="bar-value">
                      {{ department.value }}
                    </div>

                    <div
                      class="bar"
                      :style="{
                        height: `${department.percentage}%`
                      }"
                      :title="`${department.name}: ${department.value} leaves`"
                    ></div>

                    <div
                      class="bar-label"
                      :title="department.name"
                    >
                      {{ truncateDepartment(department.name) }}
                    </div>

                  </div>

                </div>

              </div>

            </div>

            <!-- Empty -->
            <div
              v-else
              class="h-80 flex items-center justify-center text-gray-400"
            >
              No department data available.
            </div>

          </div>

        </div>

        <!-- ===================================================== -->
        <!-- SUMMARY TABLE -->
        <!-- ===================================================== -->
        <div class="mt-8">

          <h4 class="text-lg font-semibold text-white mb-4">
            Department Summary
          </h4>

          <div class="summary-table">

            <table class="w-full">

              <thead>
                <tr>

                  <th>
                    Department
                  </th>

                  <th>
                    Total Leaves
                  </th>

                  <th>
                    Approved
                  </th>

                  <th>
                    Pending
                  </th>

                  <th>
                    Disapproved
                  </th>

                </tr>
              </thead>

              <tbody>

                <tr
                  v-for="dept in leaveSummaryData"
                  :key="dept.department"
                >

                  <td class="font-medium text-white">
                    {{ dept.department }}
                  </td>

                  <td class="text-white">
                    {{ dept.total }}
                  </td>

                  <td class="text-green-400">
                    {{ dept.approved }}
                  </td>

                  <td class="text-yellow-400">
                    {{ dept.pending }}
                  </td>

                  <td class="text-red-400">
                    {{ dept.disapproved }}
                  </td>

                </tr>

                <tr v-if="!leaveSummaryData.length">
                  <td
                    colspan="5"
                    class="text-center text-gray-400 py-8"
                  >
                    No department summary data available.
                  </td>
                </tr>

              </tbody>

            </table>

          </div>

        </div>

      </div>

      <!-- ======================================================= -->
      <!-- RECENT ACTIVITIES -->
      <!-- ======================================================= -->
      <div class="neo-card p-6">

        <h3 class="text-lg font-semibold mb-4 text-white">
          Recent Activities
        </h3>

        <div
          v-if="recentActivities.length"
          class="space-y-4"
        >

          <div
            v-for="activity in recentActivities"
            :key="activity.id"
            class="flex items-start"
          >

            <div
              class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3 text-green-700 font-bold flex-shrink-0"
            >
              ✓
            </div>

            <div>
              <p class="text-sm text-white">
                {{ activity.message }}
              </p>

              <p class="text-xs text-gray-400 mt-1">
                {{ activity.time }}
              </p>
            </div>

          </div>

        </div>

        <p
          v-else
          class="text-gray-400"
        >
          No recent activities.
        </p>

      </div>

      <!-- ======================================================= -->
      <!-- QUICK ACTIONS -->
      <!-- ======================================================= -->
      <div class="neo-card p-6">

        <h3 class="text-lg font-semibold mb-4 text-white">
          Quick Actions
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

          <button
            @click="goToEmployees"
            class="p-4 bg-blue-400 hover:bg-blue-300 rounded-lg text-black font-extrabold transition"
          >
            Create Employee Account
          </button>

          <button
            @click="goToLeaveRequests"
            class="p-4 bg-green-400 hover:bg-green-300 rounded-lg text-black font-extrabold transition"
          >
            Review Leave Requests
          </button>

          <button
            @click="goToReports"
            class="p-4 bg-purple-400 hover:bg-purple-300 rounded-lg text-black font-extrabold transition"
          >
            Generate Reports
          </button>

        </div>

      </div>

    </div>

    <!-- ========================================================= -->
    <!-- EMPLOYEE DASHBOARD -->
    <!-- ========================================================= -->
    <div
      v-else-if="isEmployee"
      class="space-y-8"
    >

      <!-- Welcome -->
      <div class="neo-card p-6 border-l-4 border-blue-500">

        <div class="flex justify-between items-start">

          <div>

            <h2 class="text-3xl font-bold text-white">
              Welcome, {{ employeeInfo.name }}!
            </h2>

            <p class="text-gray-300 mt-2">
              {{ employeeInfo.department_name }}

              <span
                v-if="employeeInfo.department_name && employeeInfo.position"
              >
                •
              </span>

              {{ employeeInfo.position }}
            </p>

          </div>

          <Briefcase class="w-12 h-12 text-blue-400" />

        </div>

      </div>

      <!-- Employee Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Total -->
        <div class="neo-card stats-card border-blue-500 p-6">

          <div class="flex items-center">

            <div class="p-3 bg-blue-100 rounded-lg">
              <FileCheck class="w-6 h-6 text-blue-600" />
            </div>

            <div class="ml-5">

              <h3 class="text-sm text-white">
                Total Applications
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ totalEmployeeApplications }}
              </p>

            </div>

          </div>

        </div>

        <!-- Pending -->
        <div class="neo-card stats-card border-orange-500 p-6">

          <div class="flex items-center">

            <div class="p-3 bg-yellow-100 rounded-lg">
              <Clock class="w-6 h-6 text-yellow-600" />
            </div>

            <div class="ml-4">

              <h3 class="text-sm text-white">
                Pending
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ totalPendingLeaves }}
              </p>

            </div>

          </div>

        </div>

        <!-- Approved -->
        <div class="neo-card stats-card border-green-500 p-6">

          <div class="flex items-center">

            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircle class="w-6 h-6 text-green-600" />
            </div>

            <div class="ml-4">

              <h3 class="text-sm text-white">
                Approved
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ totalApprovedLeaves }}
              </p>

            </div>

          </div>

        </div>

        <!-- Disapproved -->
        <div class="neo-card stats-card border-red-700 p-6">

          <div class="flex items-center">

            <div class="p-3 bg-red-100 rounded-lg">
              <AlertCircle class="w-6 h-6 text-red-600" />
            </div>

            <div class="ml-4">

              <h3 class="text-sm text-white">
                Disapproved
              </h3>

              <p class="text-2xl font-semibold text-white">
                {{ totalDisapprovedLeaves }}
              </p>

            </div>

          </div>

        </div>

      </div>

      <!-- My Applications / Upcoming -->
      <div class="grid lg:grid-cols-2 gap-6">

        <!-- My Applications -->
        <div class="neo-card p-6">

          <h3 class="text-lg font-semibold mb-4 text-white">
            My Leave Applications
          </h3>

          <div
            v-if="myApplications.length"
            class="space-y-3"
          >

            <div
              v-for="app in myApplications"
              :key="app.id"
              class="application-item p-4 rounded-lg"
            >

              <div class="flex justify-between items-start mb-2">

                <div>

                  <p class="font-medium text-white">
                    {{ getLeaveType(app) }}
                  </p>

                  <p class="text-sm text-gray-400">
                    {{ getDays(app) ?? 0 }} days
                  </p>

                </div>

                <span
                  :class="getStatusClass(getAppStatus(app))"
                  class="px-3 py-1 rounded text-sm font-semibold"
                >
                  {{ formatStatus(getAppStatus(app)) }}
                </span>

              </div>

              <p class="text-xs text-gray-500">

                {{ app.start_date || "" }}

                <span
                  v-if="app.start_date && app.end_date"
                >
                  to
                </span>

                {{ app.end_date || "" }}

              </p>

            </div>

          </div>

          <p
            v-else
            class="text-white"
          >
            No leave applications yet.
          </p>

        </div>

        <!-- Upcoming Leaves -->
        <div class="neo-card p-6">

          <h3 class="text-lg font-semibold mb-4 text-white">
            Upcoming Leaves
          </h3>

          <div
            v-if="upcomingLeaves.length"
            class="space-y-3"
          >

            <div
              v-for="leave in upcomingLeaves"
              :key="leave.id"
              class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500"
            >

              <p class="font-medium text-gray-800">
                {{ getLeaveType(leave) }}
              </p>

              <p class="text-sm text-gray-600 mt-1">

                {{ leave.start_date || "" }}

                <span
                  v-if="leave.start_date && leave.end_date"
                >
                  to
                </span>

                {{ leave.end_date || "" }}

              </p>

              <p class="text-xs text-gray-500 mt-1">
                {{ getDays(leave) ?? 0 }} days
              </p>

            </div>

          </div>

          <p
            v-else
            class="text-white"
          >
            No upcoming approved leaves.
          </p>

        </div>

      </div>

      <!-- Employee Status / Leave Type -->
      <div class="grid lg:grid-cols-2 gap-6">

        <!-- Status -->
        <div class="neo-card p-6">

          <h3 class="text-lg font-bold text-white mb-4">
            Leave Status Overview
          </h3>

          <div class="space-y-4">

            <!-- Approved -->
            <div>

              <div class="flex justify-between mb-1">

                <span class="text-sm text-white">
                  Approved
                </span>

                <span class="text-sm font-semibold text-white">
                  {{ employeeStatusChart.approved }}
                </span>

              </div>

              <div class="w-full bg-gray-700 rounded-full h-3">

                <div
                  class="bg-green-500 h-3 rounded-full"
                  :style="{
                    width:
                      calculateEmployeePercentage(
                        employeeStatusChart.approved
                      ) + '%'
                  }"
                ></div>

              </div>

            </div>

            <!-- Pending -->
            <div>

              <div class="flex justify-between mb-1">

                <span class="text-sm text-white">
                  Pending
                </span>

                <span class="text-sm font-semibold text-white">
                  {{ employeeStatusChart.pending }}
                </span>

              </div>

              <div class="w-full bg-gray-700 rounded-full h-3">

                <div
                  class="bg-yellow-500 h-3 rounded-full"
                  :style="{
                    width:
                      calculateEmployeePercentage(
                        employeeStatusChart.pending
                      ) + '%'
                  }"
                ></div>

              </div>

            </div>

            <!-- Disapproved -->
            <div>

              <div class="flex justify-between mb-1">

                <span class="text-sm text-white">
                  Disapproved
                </span>

                <span class="text-sm font-semibold text-white">
                  {{ employeeStatusChart.disapproved }}
                </span>

              </div>

              <div class="w-full bg-gray-700 rounded-full h-3">

                <div
                  class="bg-red-500 h-3 rounded-full"
                  :style="{
                    width:
                      calculateEmployeePercentage(
                        employeeStatusChart.disapproved
                      ) + '%'
                  }"
                ></div>

              </div>

            </div>

          </div>

        </div>

        <!-- Leave By Type -->
        <div class="neo-card p-6">

          <h3 class="font-semibold mb-4 text-white">
            Leave Applications by Type
          </h3>

          <div class="space-y-3">

            <div
              v-for="leave in employeeLeaveByType"
              :key="leave.name"
              class="leave-type-item p-4 rounded-lg"
            >

              <div class="flex justify-between items-center">

                <p class="text-sm font-medium text-white">
                  {{ leave.name }}
                </p>

                <p class="text-2xl font-bold text-blue-400">
                  {{ leave.count }}
                </p>

              </div>

            </div>

            <div
              v-if="employeeLeaveByType.length === 0"
              class="text-center text-gray-400 py-4"
            >
              No leave applications yet.
            </div>

          </div>

        </div>

      </div>

      <!-- Employee Activities -->
      <div class="neo-card p-6">

        <h3 class="text-lg font-semibold mb-4 text-white">
          Recent Activities
        </h3>

        <div
          v-if="employeeActivities.length"
          class="space-y-3"
        >

          <div
            v-for="activity in employeeActivities"
            :key="activity.id"
            class="border-l-4 border-blue-400 pl-4 py-2"
          >

            <p class="text-sm text-white">
              {{ activity.message }}
            </p>

            <p class="text-xs text-gray-400 mt-1">
              {{ activity.time }}
            </p>

          </div>

        </div>

        <p
          v-else
          class="text-white"
        >
          No recent activities.
        </p>

      </div>

      <!-- Employee Quick Actions -->
      <div class="neo-card p-6">

        <h3 class="text-lg font-semibold mb-4 text-white">
          Quick Actions
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <button
            @click="goToApply"
            class="p-4 bg-blue-400 hover:bg-blue-300 rounded-lg text-black font-extrabold transition"
          >
            Apply for Leave
          </button>

          <button
            @click="goToMyApplications"
            class="p-4 bg-green-400 hover:bg-green-300 rounded-lg text-black font-extrabold transition"
          >
            View My Applications
          </button>

        </div>

      </div>

    </div>

  </div>
</template>

<script setup lang="ts">
import {
  ref,
  computed,
  onMounted,
} from "vue";

import {
  Users,
  Clock,
  CheckCircle,
  AlertCircle,
  FileCheck,
  Briefcase,
} from "lucide-vue-next";

import { isAxiosError } from "axios";

import { useRouter } from "vue-router";

import {
  getAdminDashboard,
  getEmployeeDashboard,
} from "@/services/dashboard";

import { getMyLeaves } from "@/services/leave";

import api from "@/services/api";

// ============================================================
// ROUTER
// ============================================================

const router = useRouter();

// ============================================================
// COMMON STATE
// ============================================================

const loading = ref(true);
const loadError = ref<string | null>(null);

const userRole = ref("employee");

// ============================================================
// ADMIN DATA
// ============================================================

const totalEmployees = ref(0);
const pendingLeaves = ref(0);
const approvedLeaves = ref(0);
const disapprovedLeaves = ref(0);

const recentApplications = ref<any[]>([]);
const pendingRequests = ref<any[]>([]);

const leaveByType = ref<any[]>([]);
const leaveByDepartment = ref<any[]>([]);

const statusChartData = ref({
  approved: 0,
  pending: 0,
  disapproved: 0,
});

const recentActivities = ref<any[]>([]);

// ============================================================
// EMPLOYEE DATA
// ============================================================

const employeeInfo = ref({
  name: "",
  email: "",
  department_name: "",
  level: "",
  position: "",
});

const myApplications = ref<any[]>([]);
const upcomingLeaves = ref<any[]>([]);

const employeeStatusChart = ref({
  approved: 0,
  pending: 0,
  disapproved: 0,
});

const employeeLeaveByType = ref<any[]>([]);
const employeeActivities = ref<any[]>([]);

const totalEmployeeApplications = ref(0);
const totalPendingLeaves = ref(0);
const totalApprovedLeaves = ref(0);
const totalDisapprovedLeaves = ref(0);

// ============================================================
// ROLE COMPUTED
// ============================================================

const isAdmin = computed(
  () => userRole.value.toLowerCase() === "admin"
);

const isEmployee = computed(
  () => userRole.value.toLowerCase() === "employee"
);

// ============================================================
// ERROR HANDLER
// ============================================================

const extractErrorMessage = (
  error: unknown,
  fallback: string
): string => {

  if (isAxiosError(error)) {
    return (
      error.response?.data?.error ||
      error.response?.data?.message ||
      fallback
    );
  }

  if (error instanceof Error) {
    return error.message;
  }

  return fallback;
};

// ============================================================
// STATUS HELPERS
// ============================================================

const normalizeStatus = (
  status: unknown
): string => {

  if (
    status === null ||
    status === undefined
  ) {
    return "";
  }

  return String(status)
    .trim()
    .toLowerCase();
};

const formatStatus = (
  status: unknown
): string => {

  const normalized =
    normalizeStatus(status);

  if (!normalized) {
    return "Unknown";
  }

  return (
    normalized.charAt(0).toUpperCase() +
    normalized.slice(1)
  );
};

const getStatusClass = (
  status: unknown
): string => {

  const normalized =
    normalizeStatus(status);

  if (normalized === "approved") {
    return "bg-green-500/20 text-green-300";
  }

  if (
    normalized === "pending" ||
    normalized === "for approval" ||
    normalized === "for_approval"
  ) {
    return "bg-yellow-500/20 text-yellow-300";
  }

  if (
    normalized === "disapproved" ||
    normalized === "rejected" ||
    normalized === "denied"
  ) {
    return "bg-red-500/20 text-red-300";
  }

  return "bg-gray-500/20 text-gray-300";
};

// ============================================================
// STATUS FIELD RESOLVER
// ============================================================

const getAppStatus = (
  application: any
): string => {

  if (!application) {
    return "";
  }

  return (
    application.final_status ??
    application.status ??
    application.leave_status ??
    ""
  );
};

// ============================================================
// EMPLOYEE NAME
// ============================================================

const getEmployeeName = (
  application: any
): string => {

  if (!application) {
    return "Unknown Employee";
  }

  if (
    typeof application.employee === "string"
  ) {
    return application.employee;
  }

  if (
    application.employee &&
    typeof application.employee === "object"
  ) {

    return (
      application.employee.name ||
      application.employee.full_name ||
      application.employee.employee_name ||
      "Unknown Employee"
    );
  }

  return (
    application.employee_name ||
    application.employeeName ||
    application.full_name ||
    application.name ||
    "Unknown Employee"
  );
};

// ============================================================
// EMPLOYEE INITIAL
// ============================================================

const getEmployeeInitial = (
  application: any
): string => {

  const name =
    getEmployeeName(application);

  if (
    !name ||
    name === "Unknown Employee"
  ) {
    return "?";
  }

  return name
    .charAt(0)
    .toUpperCase();
};

// ============================================================
// LEAVE TYPE
// ============================================================

const getLeaveType = (
  application: any
): string => {

  if (!application) {
    return "Leave";
  }

  if (
    typeof application.leave_type === "string"
  ) {
    return application.leave_type;
  }

  if (
    application.leave_type &&
    typeof application.leave_type === "object"
  ) {

    return (
      application.leave_type.leave_type_name ||
      application.leave_type.name ||
      application.leave_type.leave_name ||
      "Leave"
    );
  }

  return (
    application.leaveType ||
    application.leave_name ||
    application.type ||
    "Leave"
  );
};

// ============================================================
// DAYS
// ============================================================

const getDays = (
  application: any
): number | null => {

  if (!application) {
    return null;
  }

  const value =
    application.days ??
    application.number_of_days ??
    application.total_days ??
    application.duration;

  if (
    value !== null &&
    value !== undefined &&
    value !== ""
  ) {

    const numberValue =
      Number(value);

    if (
      !Number.isNaN(numberValue)
    ) {
      return numberValue;
    }
  }

  if (
    application.start_date &&
    application.end_date
  ) {

    const start =
      new Date(
        application.start_date
      ).getTime();

    const end =
      new Date(
        application.end_date
      ).getTime();

    if (
      !Number.isNaN(start) &&
      !Number.isNaN(end) &&
      end >= start
    ) {

      return Math.round(
        (end - start) /
          (1000 * 60 * 60 * 24)
      ) + 1;
    }
  }

  return null;
};

// ============================================================
// ADMIN PIE CHART
// ============================================================

const chartColors = [
  "#3b82f6",
  "#22c55e",
  "#eab308",
  "#ef4444",
  "#a855f7",
  "#06b6d4",
  "#f97316",
  "#ec4899",
];

const totalLeaveByType = computed(() => {

  return leaveByType.value.reduce(
    (total, item) => {

      const value =
        Number(
          item.total ??
          item.count ??
          item.value ??
          item.leave_count ??
          0
        );

      return total + value;
    },
    0
  );
});

// ============================================================
// PIE CHART SVG HELPERS
// ============================================================

const polarToCartesian = (
  centerX: number,
  centerY: number,
  radius: number,
  angleInDegrees: number
) => {

  const angleInRadians =
    (angleInDegrees - 90) *
    Math.PI /
    180;

  return {
    x:
      centerX +
      radius *
        Math.cos(angleInRadians),

    y:
      centerY +
      radius *
        Math.sin(angleInRadians),
  };
};

const describeArc = (
  startAngle: number,
  endAngle: number
): string => {

  const start =
    polarToCartesian(
      100,
      100,
      95,
      endAngle
    );

  const end =
    polarToCartesian(
      100,
      100,
      95,
      startAngle
    );

  const largeArcFlag =
    endAngle - startAngle <= 180
      ? 0
      : 1;

  return [
    "M",
    100,
    100,
    "L",
    start.x,
    start.y,
    "A",
    95,
    95,
    0,
    largeArcFlag,
    0,
    end.x,
    end.y,
    "Z",
  ].join(" ");
};

// ============================================================
// PIE DATA
// ============================================================

const pieChartData = computed(() => {

  const total =
    totalLeaveByType.value;

  if (total <= 0) {
    return [];
  }

  let currentAngle = 0;

  return leaveByType.value
    .map((item, index) => {

      const value =
        Number(
          item.total ??
          item.count ??
          item.value ??
          item.leave_count ??
          0
        );

      if (value <= 0) {
        return null;
      }

      const percentage =
        (value / total) * 100;

      const angle =
        (value / total) * 360;

      const startAngle =
        currentAngle;

      const endAngle =
        currentAngle + angle;

      currentAngle =
        endAngle;

      return {
        name:
          item.name ??
          item.leave_type ??
          item.leave_type_name ??
          item.type ??
          "Unknown",

        value,

        percentage:
          Math.round(percentage),

        color:
          chartColors[
            index % chartColors.length
          ],

        path:
          describeArc(
            startAngle,
            endAngle
          ),
      };
    })
    .filter(Boolean) as any[];
});

// ============================================================
// DEPARTMENT DATA
// ============================================================

const getDepartmentValue = (
  item: any
): number => {

  return Number(
    item.total ??
    item.count ??
    item.value ??
    item.leave_count ??
    item.total_leaves ??
    0
  );
};

const getDepartmentName = (
  item: any
): string => {

  return String(
    item.department ??
    item.department_name ??
    item.name ??
    item.departmentName ??
    "Unknown"
  );
};

// ============================================================
// BAR CHART DATA
// ============================================================

const departmentMax = computed(() => {

  if (
    !leaveByDepartment.value.length
  ) {
    return 0;
  }

  return Math.max(
    ...leaveByDepartment.value.map(
      getDepartmentValue
    ),
    1
  );
});

const departmentChartData = computed(() => {

  const max =
    departmentMax.value;

  return leaveByDepartment.value
    .map((item) => {

      const value =
        getDepartmentValue(item);

      return {
        name:
          getDepartmentName(item),

        value,

        percentage:
          max > 0
            ? (value / max) * 100
            : 0,
      };
    })
    .sort(
      (a, b) =>
        b.value - a.value
    );
});

// ============================================================
// DEPARTMENT LABEL
// ============================================================

const truncateDepartment = (
  name: string
): string => {

  if (name.length <= 12) {
    return name;
  }

  return (
    name.substring(0, 11) +
    "…"
  );
};

// ============================================================
// DEPARTMENT SUMMARY
// ============================================================

const leaveSummaryData = computed(() => {

  return leaveByDepartment.value.map(
    (dept: any) => {

      const total =
        Number(
          dept.total ??
          dept.total_leaves ??
          dept.count ??
          0
        );

      return {
        department:
          getDepartmentName(dept),

        total,

        approved:
          Number(
            dept.approved ??
            dept.approved_leaves ??
            0
          ),

        pending:
          Number(
            dept.pending ??
            dept.pending_leaves ??
            0
          ),

        disapproved:
          Number(
            dept.disapproved ??
            dept.disapproved_leaves ??
            dept.rejected ??
            0
          ),
      };
    }
  );
});

// ============================================================
// PERCENTAGE
// ============================================================

const calculatePercentage = (
  value: number
) => {

  const total =
    statusChartData.value.approved +
    statusChartData.value.pending +
    statusChartData.value.disapproved;

  if (total === 0) {
    return 0;
  }

  return Math.round(
    (value / total) * 100
  );
};

const calculateEmployeePercentage = (
  value: number
) => {

  const total =
    employeeStatusChart.value.approved +
    employeeStatusChart.value.pending +
    employeeStatusChart.value.disapproved;

  if (total === 0) {
    return 0;
  }

  return Math.round(
    (value / total) * 100
  );
};

// ============================================================
// CURRENT USER
// ============================================================

const getCurrentUser = async () => {

  const response =
    await api.get("/me");

  userRole.value =
    String(
      response.data?.role ||
      "employee"
    ).toLowerCase();
};

// ============================================================
// LOAD ADMIN DASHBOARD
// ============================================================

const loadAdminDashboard = async () => {

  const response =
    await getAdminDashboard();

  const data =
    response.data;

  if (
    !data ||
    !data.summary
  ) {

    throw new Error(
      "Unexpected response shape from /admin/dashboard"
    );
  }

  totalEmployees.value =
    Number(
      data.summary.totalEmployees
    ) || 0;

  pendingLeaves.value =
    Number(
      data.summary.pendingLeaves
    ) || 0;

  approvedLeaves.value =
    Number(
      data.summary.approvedLeaves
    ) || 0;

  disapprovedLeaves.value =
    Number(
      data.summary.disapprovedLeaves
    ) || 0;

  recentApplications.value =
    Array.isArray(
      data.recentApplications
    )
      ? data.recentApplications
      : [];

  const backendPendingRequests =
    Array.isArray(
      data.pendingRequests
    )
      ? data.pendingRequests
      : [];

  if (
    backendPendingRequests.length
  ) {

    pendingRequests.value =
      backendPendingRequests.filter(
        (request: any) => {

          const status =
            normalizeStatus(
              request.status ??
              request.final_status
            );

          return (
            status === "pending" ||
            status === "for approval" ||
            status === "for_approval"
          );
        }
      );

  } else {

    pendingRequests.value =
      recentApplications.value.filter(
        (application: any) => {

          const status =
            normalizeStatus(
              getAppStatus(application)
            );

          return (
            status === "pending" ||
            status === "for approval" ||
            status === "for_approval"
          );
        }
      );
  }

  leaveByType.value =
    Array.isArray(
      data.leaveByType
    )
      ? data.leaveByType
      : [];

  leaveByDepartment.value =
    Array.isArray(
      data.leaveByDepartment
    )
      ? data.leaveByDepartment
      : [];

  statusChartData.value =
    data.statusChart || {
      approved: 0,
      pending: 0,
      disapproved: 0,
    };

  recentActivities.value =
    Array.isArray(
      data.recentActivities
    )
      ? data.recentActivities
      : [];

  // DEBUGGING
  console.log(
    "ADMIN DASHBOARD DATA:",
    data
  );

  console.log(
    "LEAVE BY TYPE:",
    leaveByType.value
  );

  console.log(
    "LEAVE BY DEPARTMENT:",
    leaveByDepartment.value
  );

  console.log(
    "PIE CHART:",
    pieChartData.value
  );

  console.log(
    "DEPARTMENT CHART:",
    departmentChartData.value
  );
};

// ============================================================
// LOAD EMPLOYEE DASHBOARD
// ============================================================

const loadEmployeeDashboard =
  async () => {

    try {

      const response =
        await getEmployeeDashboard();

      const data =
        response.data;

      employeeInfo.value =
        data?.employee || {
          name: "",
          email: "",
          department_name: "",
          level: "",
          position: "",
        };

    } catch (error) {

      console.error(
        "Failed to load employee profile info",
        error
      );
    }

    const myLeaves =
      await getMyLeaves();

    const applications =
      Array.isArray(myLeaves)
        ? myLeaves
        : [];

    const sorted =
      [...applications].sort(
        (a, b) => {

          return (
            new Date(
              b.date_filed || 0
            ).getTime() -

            new Date(
              a.date_filed || 0
            ).getTime()
          );
        }
      );

    myApplications.value =
      sorted;

    totalEmployeeApplications.value =
      applications.length;

    totalApprovedLeaves.value =
      applications.filter(
        (app) =>
          normalizeStatus(
            getAppStatus(app)
          ) === "approved"
      ).length;

    totalPendingLeaves.value =
      applications.filter(
        (app) => {

          const status =
            normalizeStatus(
              getAppStatus(app)
            );

          return (
            status === "pending" ||
            status === "for approval" ||
            status === "for_approval"
          );
        }
      ).length;

    totalDisapprovedLeaves.value =
      applications.filter(
        (app) => {

          const status =
            normalizeStatus(
              getAppStatus(app)
            );

          return (
            status === "disapproved" ||
            status === "rejected" ||
            status === "denied"
          );
        }
      ).length;

    employeeStatusChart.value = {
      approved:
        totalApprovedLeaves.value,

      pending:
        totalPendingLeaves.value,

      disapproved:
        totalDisapprovedLeaves.value,
    };

    const today =
      new Date();

    today.setHours(
      0,
      0,
      0,
      0
    );

    upcomingLeaves.value =
      applications
        .filter((app) => {

          const status =
            normalizeStatus(
              getAppStatus(app)
            );

          const start =
            app.start_date
              ? new Date(
                  app.start_date
                )
              : null;

          return (
            status === "approved" &&
            start &&
            start.getTime() >=
              today.getTime()
          );
        })
        .sort(
          (a, b) =>
            new Date(
              a.start_date
            ).getTime() -
            new Date(
              b.start_date
            ).getTime()
        );

    const typeCounts =
      new Map<string, number>();

    applications.forEach(
      (app) => {

        const name =
          getLeaveType(app);

        typeCounts.set(
          name,
          (typeCounts.get(name) || 0) + 1
        );
      }
    );

    employeeLeaveByType.value =
      Array.from(
        typeCounts.entries()
      ).map(
        ([name, count]) => ({
          name,
          count,
        })
      );

    employeeActivities.value =
      sorted
        .slice(0, 5)
        .map(
          (app) => ({
            id:
              app.leave_id ||
              app.id,

            message:
              `${getLeaveType(app)} application ${formatStatus(
                getAppStatus(app)
              ).toLowerCase()}`,

            time:
              app.date_filed || "",
          })
        );
  };

// ============================================================
// REVIEW REQUEST
// ============================================================

const reviewRequest = (
  request: any
) => {

  if (request?.id) {

    router.push({
      path: "/admin-applications",
      query: {
        id: String(
          request.id
        ),
      },
    });

    return;
  }

  router.push(
    "/admin-applications"
  );
};

// ============================================================
// NAVIGATION
// ============================================================

const goToEmployees =
  () =>
    router.push(
      "/employees"
    );

const goToLeaveRequests =
  () =>
    router.push(
      "/admin-applications"
    );

const goToReports =
  () =>
    router.push(
      "/reports"
    );

const goToApply =
  () =>
    router.push(
      "/leave-application"
    );

const goToMyApplications =
  () =>
    router.push(
      "/my-applications"
    );

// ============================================================
// LOAD
// ============================================================

const runLoad =
  async () => {

    loading.value = true;
    loadError.value = null;

    try {

      await getCurrentUser();

      if (isAdmin.value) {

        await loadAdminDashboard();

      } else {

        await loadEmployeeDashboard();
      }

    } catch (error) {

      console.error(
        "Dashboard loading error:",
        error
      );

      loadError.value =
        extractErrorMessage(
          error,
          "Something went wrong loading your dashboard. Please try again."
        );

    } finally {

      loading.value = false;
    }
  };

const retryLoad =
  () => {
    runLoad();
  };

onMounted(
  runLoad
);
</script>

<style scoped>
/* ============================================================
   DASHBOARD
   ============================================================ */

.dashboard-shell {
  background: #080d14;
  min-height: 100vh;
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
}

.neo-card:hover {
  box-shadow:
    0 14px 26px rgba(15, 23, 42, 0.08);
}

/* ============================================================
   STATISTICS
   ============================================================ */

.stats-card {
  border-left: 4px solid currentColor;
  padding: 1.35rem;
}

.stats-card .p-3 {
  border-radius: 0.9rem;
}

/* ============================================================
   CHART CARDS
   ============================================================ */

.chart-card {
  background: #0b1420;
  border: 1px solid #1e293b;
  border-radius: 1rem;
  padding: 1.5rem;
  min-height: 390px;
}

/* ============================================================
   PIE CHART
   ============================================================ */

.chart-card svg {
  overflow: visible;
}

.chart-card svg path {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}

.chart-card svg path:hover {
  opacity: 0.8;
}

/* ============================================================
   DEPARTMENT BAR GRAPH
   ============================================================ */

.department-chart {
  display: flex;
  width: 100%;
  height: 300px;
}

.chart-y-axis {
  width: 45px;
  height: 245px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: flex-end;
  padding-right: 10px;
  color: #64748b;
  font-size: 11px;
}

.chart-area {
  position: relative;
  flex: 1;
  height: 300px;
  border-bottom: 1px solid #334155;
}

.chart-grid {
  position: absolute;
  inset: 0 0 55px 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  pointer-events: none;
}

.chart-grid div {
  width: 100%;
  border-top: 1px dashed #263548;
}

.bars-container {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 250px;

  display: flex;
  align-items: flex-end;
  justify-content: space-around;
  gap: 12px;

  overflow-x: auto;
  overflow-y: hidden;
  padding: 0 12px;
}

.bar-wrapper {
  min-width: 55px;
  height: 250px;

  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;

  position: relative;
}

.bar {
  width: 38px;
  min-height: 3px;

  background: linear-gradient(
    to top,
    #2563eb,
    #60a5fa
  );

  border-radius:
    7px 7px 0 0;

  transition:
    height 0.5s ease,
    opacity 0.2s ease;

  position: relative;
  z-index: 2;
}

.bar:hover {
  opacity: 0.75;
}

.bar-value {
  position: absolute;
  bottom: calc(
    var(--bar-height, 0%) + 8px
  );

  color: white;
  font-size: 11px;
  font-weight: 700;

  margin-bottom: 4px;
}

.bar-label {
  position: absolute;
  bottom: -42px;

  width: 70px;

  text-align: center;

  color: #94a3b8;
  font-size: 10px;

  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* ============================================================
   SUMMARY TABLE
   ============================================================ */

.summary-table {
  overflow-x: auto;
  border: 1px solid #1e293b;
  border-radius: 0.9rem;
}

.summary-table table {
  min-width: 700px;
  border-collapse: collapse;
}

.summary-table thead {
  background: #0b1420;
}

.summary-table th {
  padding: 0.85rem 1.25rem;
  text-align: left;

  color: #94a3b8;

  font-size: 0.72rem;
  font-weight: 600;

  text-transform: uppercase;
  letter-spacing: 0.05em;

  border-bottom:
    1px solid #1e293b;
}

.summary-table td {
  padding: 1rem 1.25rem;

  font-size: 0.875rem;

  border-bottom:
    1px solid #1e293b;
}

.summary-table tbody tr {
  transition:
    background 0.2s ease;
}

.summary-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.04);
}

.summary-table tbody tr:last-child td {
  border-bottom: none;
}

/* ============================================================
   APPLICATION ITEMS
   ============================================================ */

.application-item {
  background: #172337;
  border: 1px solid #24344d;

  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}

.application-item:hover {
  background: #1b2a40;
  border-color: #334968;
  transform: translateY(-1px);
}

/* ============================================================
   PENDING REQUESTS
   ============================================================ */

.request-item {
  background: #172337;
  border: 1px solid #3b3b27;
  border-left: 4px solid #eab308;

  transition:
    background 0.2s ease,
    border-color 0.2s ease,
    transform 0.2s ease;
}

.request-item:hover {
  background: #1b2a40;
  border-color: #5b5a31;
  transform: translateY(-1px);
}

/* ============================================================
   LEAVE TYPE
   ============================================================ */

.leave-type-item {
  background: #172337;
  border: 1px solid #24344d;

  transition:
    background 0.2s ease,
    transform 0.2s ease;
}

.leave-type-item:hover {
  background: #1b2a40;
  transform: translateY(-1px);
}

/* ============================================================
   BUTTONS
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
   MOBILE
   ============================================================ */

@media (max-width: 768px) {

  .dashboard-shell {
    padding: 1rem;
  }

  .chart-card {
    padding: 1rem;
  }

  .chart-card svg {
    width: 220px;
    height: 220px;
  }

  .bars-container {
    justify-content: flex-start;
  }

  .bar-wrapper {
    min-width: 60px;
  }
}
</style>