<template>
  <div
    class="w-full max-w-[1400px] mx-auto px-2 sm:px-3 md:px-4 lg:px-6 py-4 dashboard-shell"
  >
    <div class="bg-white rounded-lg shadow neo-card w-full min-w-0">
      <!-- Header -->
      <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-[var(--text)]">
          Submitted Leave Applications
        </h2>

        <p class="text-sm text-[var(--text-muted)] mt-1">
          Review and manage faculty leave applications
        </p>
      </div>

      <!-- Filter Tabs -->
      <div class="px-4 sm:px-6 py-3 border-b border-gray-200 overflow-x-auto">
        <div class="flex space-x-2 sm:space-x-4 min-w-max">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="handleTabChange(tab.key)"
            :class="[
              'px-3 sm:px-4 py-2 text-sm font-medium rounded-full transition-colors whitespace-nowrap',
              activeTab === tab.key
                ? 'bg-blue-100 text-blue-700'
                : 'text-white hover:text-gray-700 hover:bg-gray-100',
            ]"
          >
            {{ tab.label }}

            <span
              :class="['ml-2 px-2 py-1 text-xs rounded-full', tab.countClass]"
            >
              {{ getTabCount(tab.key) }}
            </span>
          </button>
        </div>
      </div>

      <!-- Applications List -->
      <div class="p-4 sm:p-6 min-w-0">
        <!-- Search and Filters -->
        <div class="mb-6">
          <div class="flex flex-col lg:flex-row gap-3">
            <!-- Search -->
            <div class="flex-1 min-w-0">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search employee, leave type, status, or ID..."
                class="w-full px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 text-white bg-[#0B1420]"
              />
            </div>

            <!-- Leave Type -->
            <select
              v-model="filterType"
              class="w-full lg:w-auto px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 text-white bg-[#0B1420]"
            >
              <option value="">All Leave Types</option>
              <option value="vacation">Vacation Leave</option>
              <option value="sick">Sick Leave</option>
              <option value="maternity">Maternity Leave</option>
              <option value="paternity">Paternity Leave</option>
              <option value="study">Study Leave</option>
              <option value="special">Special Leave</option>
              <option value="mandatory">Mandatory/Forced Leave</option>
            </select>

            <!-- Sort -->
            <button
              @click="toggleSort"
              type="button"
              class="w-full lg:w-auto px-4 py-2 text-xs font-medium border border-gray-300 text-[var(--text)] rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition whitespace-nowrap"
              :title="arranged ? 'Unsort applications' : 'Sort applications alphabetically (A to Z)'"
            >
              Sort
            </button>

            <!-- Clear -->
            <button
              @click="
                searchQuery = '';
                filterType = '';
                activeTab = 'all';
              "
              class="w-full lg:w-auto px-4 py-2 text-xs font-medium border border-gray-300 text-[var(--text)] rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition whitespace-nowrap"
            >
              Clear
            </button>
          </div>

          <!-- Search Result Count -->
          <div
            v-if="searchQuery || filterType"
            class="mt-2 text-sm text-gray-400"
          >
            {{ filteredApplications.length }} application(s) found
          </div>
        </div>

        <!-- No Applications -->
        <div v-if="filteredApplications.length === 0" class="text-center py-8">
          <div class="mx-auto h-12 w-12 text-white">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
          </div>

          <h3 class="mt-2 text-sm font-medium text-white">
            No applications found
          </h3>

          <p class="mt-1 text-sm text-white">
            No leave applications have been submitted yet.
          </p>
        </div>

        <!-- Applications -->
        <div v-else class="space-y-4">
          <div
            v-for="application in displayedApplications"
            :key="application.leave_id"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow neo-card min-w-0"
          >
            <div
              class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4"
            >
              <!-- Application Information -->
              <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                  <h3 class="text-lg font-medium text-white break-words">
                    {{ getEmployeeName(application.employee) }}
                  </h3>

                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap',
                      getStatusClass(application.final_status),
                    ]"
                  >
                    {{ application.final_status }}
                  </span>
                </div>

                <!-- Application Details -->
                <div
                  class="mt-2 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 text-sm text-white"
                >
                  <div class="min-w-0 break-words">
                    <span class="font-medium"> Office: </span>
                    {{ application.employee?.department_name ?? "Not available" }}
                  </div>

                  <div class="min-w-0 break-words">
                    <span class="font-medium"> Position: </span>
                    {{ application.employee?.position ?? "Not available" }}
                  </div>

                  <div class="min-w-0 break-words">
                    <span class="font-medium"> Date Filed: </span>
                    {{ formatDate(application.date_filed) }}
                  </div>

                  <div class="min-w-0 break-words">
                    <span class="font-medium"> Leave Type: </span>
                    {{ getLeaveType(application.leave_type) }}
                  </div>

                  <div class="min-w-0 break-words">
                    <span class="font-medium"> Days Applied: </span>
                    {{ application.number_of_days }}
                  </div>
                </div>

                <!-- Attachments -->
                <div
                  v-if="
                    application.attachments &&
                    application.attachments.length > 0
                  "
                  class="mt-3"
                >
                  <span class="text-sm font-medium text-white">
                    Attachments:
                  </span>

                  <div class="flex flex-wrap gap-2 mt-1">
                    <span
                      v-for="(attachment, index) in application.attachments"
                      :key="index"
                      class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full max-w-full break-all"
                    >
                      <svg
                        class="w-3 h-3 mr-1 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                        />
                      </svg>

                      {{ attachment.name }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div
                class="flex flex-row flex-wrap gap-2 xl:flex-col xl:ml-4 w-full xl:w-auto xl:flex-shrink-0"
              >
                <template v-if="activeTab !== 'deleted'">
                  <!-- View -->
                  <button
                    @click="viewApplication(application)"
                    class="btn-action bg-blue-600 hover:bg-blue-700"
                  >
                    View Details
                  </button>

                  <!-- Approve -->
                  <button
                    v-if="application.final_status?.toLowerCase() === 'pending'"
                    @click="openApprovalModal(application)"
                    class="btn-action bg-green-600 hover:bg-green-700"
                  >
                    Approve
                  </button>

                  <!-- Reject -->
                  <button
                    v-if="application.final_status?.toLowerCase() === 'pending'"
                    @click="openRejectModal(application.leave_id)"
                    class="btn-action bg-red-600 hover:bg-red-700"
                  >
                    Reject
                  </button>

                  <!-- Delete -->
                  <button
                    @click="deleteLeaveApplicationById(application.leave_id)"
                    class="btn-action bg-gray-600 hover:bg-gray-700"
                  >
                    Delete
                  </button>
                </template>

                <template v-else>
                  <button
                    @click="restoreLeaveApplicationById(application.leave_id)"
                    class="btn-action bg-green-600 hover:bg-green-700"
                  >
                    Restore
                  </button>
                </template>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div
            v-if="filteredApplications.length > 0"
            class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 pt-5 border-t border-slate-700"
          >
            <div class="text-sm text-gray-400 text-center sm:text-left">
              Showing
              <span class="font-medium text-white">
                {{ paginationStart }}
              </span>
              -
              <span class="font-medium text-white">
                {{ paginationEnd }}
              </span>
              of
              <span class="font-medium text-white">
                {{ filteredApplications.length }}
              </span>
              applications
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
              <button
                @click="previousPage"
                :disabled="currentPage === 1"
                class="w-8 h-8 flex items-center justify-center text-xs rounded-full border transition"
                :class="
                  currentPage === 1
                    ? 'text-gray-400 bg-gray-100 border-gray-200 cursor-not-allowed'
                    : 'text-[#0F2742] bg-white border-gray-300 hover:bg-gray-100'
                "
              >
                &lt;
              </button>

              <span
                class="w-8 h-8 flex items-center justify-center text-xs font-medium text-white bg-blue-600 rounded-full"
              >
                {{ currentPage }}
              </span>

              <button
                @click="nextPage"
                :disabled="currentPage === totalPages"
                class="w-8 h-8 flex items-center justify-center text-xs rounded-full border transition"
                :class="
                  currentPage === totalPages
                    ? 'text-gray-400 bg-gray-100 border-gray-200 cursor-not-allowed'
                    : 'text-[#0F2742] bg-white border-gray-300 hover:bg-gray-100'
                "
              >
                &gt;
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ====================================================== -->
    <!-- APPLICATION DETAIL MODAL -->
    <!-- ====================================================== -->

    <div
      v-if="showDetailModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto w-full h-full z-50 p-2 sm:p-4"
    >
      <div
        class="relative mx-auto my-4 sm:my-8 p-4 sm:p-5 border w-full sm:w-11/12 max-w-4xl shadow-lg rounded-lg bg-white max-h-[calc(100vh-2rem)] sm:max-h-[90vh] overflow-y-auto neo-card"
      >
        <div class="flex justify-between items-center mb-4 gap-3">
          <h3 class="text-xl font-medium text-white">
            Leave Application Details
          </h3>

          <button
            @click="showDetailModal = false"
            class="text-white hover:text-gray-600 flex-shrink-0"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <div v-if="selectedApplication" class="space-y-6">
          <!-- Applicant Information -->
          <div class="border border-gray-300 p-4 min-w-0 modal-section">
            <h4 class="text-sm font-bold mb-3 text-white">
              Applicant Information
            </h4>

            <div
              class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-white"
            >
              <div class="break-words">
                <strong>Office:</strong>
                {{ selectedApplication.employee.department_name }}
              </div>

              <div class="break-words">
                <strong>Name:</strong>
                {{ selectedApplication.employee.employee_last_name }},
                {{ selectedApplication.employee.employee_first_name }}
                {{ selectedApplication.employee.employee_middle_name }}
              </div>

              <div class="break-words">
                <strong>Date of Filing:</strong>
                {{ formatDate(selectedApplication.date_filed) }}
              </div>

              <div class="break-words">
                <strong>Position:</strong>
                {{ selectedApplication.employee.position }}
              </div>

              <div class="break-words">
                <strong>Salary:</strong>
                {{ selectedApplication.employee.salary }}
              </div>

              <div class="break-words">
                <strong>Status:</strong>

                <span :class="getStatusClass(selectedApplication.final_status)">
                  {{ selectedApplication.final_status }}
                </span>
              </div>
            </div>
          </div>

          <!-- Leave Details -->
          <div class="border border-gray-300 p-4 min-w-0 modal-section">
            <h4 class="text-sm font-bold mb-3 text-white">Leave Details</h4>

            <div class="space-y-2 text-sm text-white">
              <div class="break-words">
                <strong>Type of Leave:</strong>
                {{ getLeaveType(selectedApplication.leave_type) }}
              </div>

              <div class="break-words">
                <strong>Number of Days:</strong>
                {{ selectedApplication.number_of_days }}
              </div>

              <div class="break-words">
                <strong>Inclusive Dates:</strong>
                {{ formatDate(selectedApplication.start_date) }}
                -
                {{ formatDate(selectedApplication.end_date) }}
              </div>

              <div class="break-words">
                <strong>Commutation:</strong>

                {{
                  selectedApplication.commutation
                    ? "Requested"
                    : "Not Requested"
                }}
              </div>

              <div class="break-words">
                <strong>Applicant Signature:</strong>

                {{ selectedApplication.employee.employee_first_name }}
                {{ selectedApplication.employee.employee_last_name }}
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div
            class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-gray-200"
          >
            <button
              @click="downloadApplication(selectedApplication)"
              class="btn-action-lg bg-green-600 hover:bg-green-700"
            >
              Download PDF
            </button>

            <button
              v-if="
                selectedApplication.final_status?.toLowerCase() === 'pending'
              "
              @click="openApprovalModal(selectedApplication)"
              class="btn-action-lg bg-green-600 hover:bg-green-700"
            >
              Approve
            </button>

            <button
              v-if="
                selectedApplication.final_status?.toLowerCase() === 'pending'
              "
              @click="openRejectModal(selectedApplication.leave_id)"
              class="btn-action-lg bg-red-600 hover:bg-red-700"
            >
              Reject
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ====================================================== -->
  <!-- PRIMARY LEAVE APPROVAL MODAL -->
  <!-- ====================================================== -->

  <div
    v-if="showPrimaryApprovalModal"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-[60] p-3 sm:p-4 overflow-y-auto"
  >
    <div
      class="bg-white rounded-lg shadow-xl w-full max-w-lg p-4 sm:p-6 neo-card max-h-[95vh] overflow-y-auto"
    >
      <h3 class="text-lg font-semibold text-white">
        Approve {{ primaryLeaveLabel }}
      </h3>

      <p class="text-sm text-gray-300 mt-2">
        {{ getEmployeeName(approvalApplication?.employee) }} applied for
        <strong>{{ approvalApplication?.number_of_days ?? 0 }}</strong>
        day(s) of {{ primaryLeaveLabel }}.
      </p>

      <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="balance-summary-card">
          <span>Vacation Leave</span>
          <strong>
            {{ formatBalance(employeeBalance.vacation_balance) }} days
          </strong>
        </div>

        <div class="balance-summary-card">
          <span>Sick Leave</span>
          <strong>
            {{ formatBalance(employeeBalance.sick_balance) }} days
          </strong>
        </div>

        <div class="balance-summary-card">
          <span>Service Credits</span>
          <strong>
            {{ formatBalance(employeeBalance.service_credits) }} days
          </strong>
        </div>
      </div>

      <p v-if="isLoadingBalance" class="text-sm text-blue-600 mt-3">
        Loading current balances...
      </p>

      <p v-else-if="balanceLoadError" class="text-sm text-red-600 mt-3">
        {{ balanceLoadError }}
      </p>

      <div class="mt-5 rounded-xl border border-blue-200 bg-blue-50 p-4">
        <div class="flex justify-between gap-3 text-sm text-[#0F2742]">
          <span>{{ primaryLeaveLabel }} deduction</span>

          <strong>
            {{ approvalApplication?.number_of_days ?? 0 }} day(s)
          </strong>
        </div>

        <div
          class="flex justify-between gap-3 text-sm text-[#0F2742] mt-2"
        >
          <span>Projected {{ primaryLeaveLabel }} balance</span>

          <strong
            :class="
              projectedPrimaryBalance < 0
                ? 'text-red-600'
                : 'text-green-600'
            "
          >
            {{ formatBalance(projectedPrimaryBalance) }} day(s)
          </strong>
        </div>

        <p
          v-if="projectedPrimaryBalance < 0"
          class="text-xs text-red-600 mt-2"
        >
          Insufficient {{ primaryLeaveLabel }} balance. Use Split Deduction or
          approve without deduction.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-6">
        <button
          @click="approvePrimaryWithDeduction"
          :disabled="isLoadingBalance || projectedPrimaryBalance < 0"
          class="btn-action-lg bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Approve and Deduct {{ primaryLeaveShortLabel }}
        </button>

        <button
          @click="approveWithoutDeduction"
          class="btn-action-lg bg-blue-600 hover:bg-blue-700"
        >
          Approve Without Deduction
        </button>

        <button
          @click="openSplitDeduction"
          class="btn-action-lg bg-amber-600 hover:bg-amber-700"
        >
          Use Split Deduction
        </button>

        <button
          @click="closeApprovalModals"
          class="btn-action-lg bg-gray-600 hover:bg-gray-700"
        >
          Cancel
        </button>
      </div>
    </div>
  </div>

  <!-- ====================================================== -->
  <!-- APPROVAL / SPLIT DEDUCTION MODAL -->
  <!-- ====================================================== -->

  <div
    v-if="showApprovalModal"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-[60] p-3 sm:p-4 overflow-y-auto"
  >
    <div
      class="bg-white rounded-lg shadow-xl w-full max-w-md p-4 sm:p-6 neo-card max-h-[95vh] overflow-y-auto"
    >
      <h3 class="text-lg font-semibold text-white">
        Approve Leave Application
      </h3>

      <p class="text-sm text-white mt-2 break-words">
        This leave application is for
        <strong>
          {{ approvalApplication?.number_of_days }}
        </strong>
        day(s).
      </p>

      <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="balance-summary-card">
          <span>Vacation Leave</span>
          <strong>
            {{ formatBalance(employeeBalance.vacation_balance) }} days
          </strong>
        </div>

        <div class="balance-summary-card">
          <span>Sick Leave</span>
          <strong>
            {{ formatBalance(employeeBalance.sick_balance) }} days
          </strong>
        </div>

        <div class="balance-summary-card">
          <span>Service Credits</span>
          <strong>
            {{ formatBalance(employeeBalance.service_credits) }} days
          </strong>
        </div>
      </div>

      <!-- Deduct Balance -->
      <div class="mt-5">
        <label class="block text-sm font-medium text-white mb-2">
          Deduct leave balance?
        </label>

        <div class="flex gap-4 flex-wrap">
          <label class="flex items-center gap-2 text-white">
            <input type="radio" value="yes" v-model="deductBalance" />
            <span>Yes</span>
          </label>

          <label class="flex items-center gap-2 text-white">
            <input type="radio" value="no" v-model="deductBalance" />
            <span>No</span>
          </label>
        </div>
      </div>

      <!-- Deduction Options -->
      <div v-if="deductBalance === 'yes'" class="mt-5 border-t pt-4">
        <h4 class="text-sm font-semibold text-white mb-3">
          Leave Balance Deduction
        </h4>

        <!-- Service Credits -->
        <div class="mt-4">
          <label class="block text-sm font-medium text-white mb-2">
            Service Credits Days to Deduct
          </label>

          <input
            v-model.number="serviceCreditsDeductDays"
            type="number"
            min="0"
            :max="approvalApplication?.number_of_days"
            step="0.5"
            class="w-full border border-gray-300 rounded-full px-3 py-2 text-[#0F2742] bg-white"
          />

          <p class="text-xs text-white mt-1">
            Enter the number of days to deduct from Service Credits.
          </p>
        </div>

        <!-- Vacation Leave -->
        <div class="mt-4">
          <label class="block text-sm font-medium text-white mb-2">
            Vacation Leave Days to Deduct
          </label>

          <input
            v-model.number="vacationDeductDays"
            type="number"
            min="0"
            :max="approvalApplication?.number_of_days"
            step="0.5"
            class="w-full border border-gray-300 rounded-full px-3 py-2 text-[#0F2742] bg-white"
          />

          <p class="text-xs text-white mt-1">
            Enter the number of days to deduct from Vacation Leave.
          </p>
        </div>

        <!-- Sick Leave -->
        <div class="mt-4">
          <label class="block text-sm font-medium text-white mb-2">
            Sick Leave Days to Deduct
          </label>

          <input
            v-model.number="sickDeductDays"
            type="number"
            min="0"
            :max="approvalApplication?.number_of_days"
            step="0.5"
            class="w-full border border-gray-300 rounded-full px-3 py-2 text-[#0F2742] bg-white"
          />

          <p class="text-xs text-white mt-1">
            Enter the number of days to deduct from Sick Leave.
          </p>
        </div>

        <!-- Total -->
        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
          <div class="flex justify-between text-sm gap-3">
            <span class="text-gray-800">
              Days applied:
            </span>

            <span class="font-medium text-gray-800 text-right">
              {{ approvalApplication?.number_of_days ?? 0 }}
              day(s)
            </span>
          </div>

          <div class="flex justify-between text-sm mt-1 gap-3">
            <span class="text-gray-800">
              Total deduction:
            </span>

            <span class="font-semibold text-gray-800 text-right">
              {{
                vacationDeductDays +
                sickDeductDays +
                serviceCreditsDeductDays
              }}
              day(s)
            </span>
          </div>

          <p
            v-if="
              approvalApplication &&
              vacationDeductDays +
                sickDeductDays +
                serviceCreditsDeductDays >
                approvalApplication.number_of_days
            "
            class="text-sm text-red-600 mt-2"
          >
            Total deduction cannot exceed the number of days applied.
          </p>
        </div>
      </div>

      <!-- Buttons -->
      <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mt-6">
        <button
          v-if="isPrimaryLeaveApplication(approvalApplication)"
          @click="backToPrimaryApproval"
          class="btn-action-lg bg-gray-600 hover:bg-gray-700"
        >
          Back
        </button>

        <button
          @click="closeApprovalModals"
          class="btn-action-lg bg-gray-600 hover:bg-gray-700"
        >
          Cancel
        </button>

        <button
          @click="confirmApproval"
          class="btn-action-lg bg-green-600 hover:bg-green-700"
        >
          Confirm Approval
        </button>
      </div>
    </div>
  </div>

  <!-- ====================================================== -->
  <!-- REJECTION MODAL -->
  <!-- ====================================================== -->

  <div
    v-if="showRejectModal"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-[70] p-4"
  >
    <div
      class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 neo-card"
    >
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-white">
          Disapprove Leave Application
        </h3>

        <button
          @click="cancelReject"
          class="text-white hover:text-gray-600"
          :disabled="isRejecting"
        >
          <svg
            class="w-6 h-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <p class="text-sm text-gray-500 mb-4">
        Please provide a reason for disapproving this leave application.
      </p>

      <textarea
        v-model="rejectionReason"
        rows="4"
        placeholder="Enter reason for disapproval..."
        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-[#0F2742] bg-white focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"
        :disabled="isRejecting"
      ></textarea>

      <p class="text-xs text-gray-500 mt-2">
        A reason is required before the application can be disapproved.
      </p>

      <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mt-6">
        <button
          @click="cancelReject"
          :disabled="isRejecting"
          class="btn-action-lg bg-gray-600 hover:bg-gray-700 disabled:opacity-50"
        >
          Cancel
        </button>

        <button
          @click="confirmReject"
          :disabled="isRejecting || !rejectionReason.trim()"
          class="btn-action-lg bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isRejecting ? "Disapproving..." : "Confirm Disapproval" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";

import { useRouter } from "vue-router";
import {
  deleteLeaveApplication,
  restoreLeaveApplication,
  getDeletedLeaveApplications,
  rejectLeaveApplication,
} from "@/services/leave";
import { getLeaveBalanceByEmployeeId } from "@/services/leaveBalance";

import axios from "axios";

interface Attachment {
  name: string;
  size: number;
  file?: File;
}

interface LeaveApplication {
  leave_id: number;
  employee_id: number;
  leave_type_id: number;

  date_filed: string;
  start_date: string;
  end_date: string;

  number_of_days: number;

  commutation: string;
  reason: string;

  final_status: string;
  recommendation_status: string;

  employee: any;
  leave_type: any;
  attachments: any[];
}

const router = useRouter();

/* =========================================================
   APPLICATION DATA
========================================================= */

const applications = ref<LeaveApplication[]>([]);
const deletedApplications = ref<LeaveApplication[]>([]);

const activeTab = ref("all");
const searchQuery = ref("");
const filterType = ref("");
const arranged = ref(false);

/* =========================================================
   MODALS
========================================================= */

const showDetailModal = ref(false);
const selectedApplication = ref<LeaveApplication | null>(null);

const showApprovalModal = ref(false);
const showPrimaryApprovalModal = ref(false);

const approvalApplication = ref<LeaveApplication | null>(null);

/* =========================================================
   APPROVAL / DEDUCTION
========================================================= */

const deductBalance = ref<"yes" | "no">("yes");

const vacationDeductDays = ref(0);
const sickDeductDays = ref(0);
const serviceCreditsDeductDays = ref(0);

const employeeBalance = ref({
  vacation_balance: 0,
  sick_balance: 0,
  service_credits: 0,
});

const isLoadingBalance = ref(false);
const balanceLoadError = ref("");

const primaryDeductionType = computed<"vacation" | "sick" | null>(() => {
  if (isVacationApplication(approvalApplication.value)) return "vacation";
  if (isSickApplication(approvalApplication.value)) return "sick";
  return null;
});

const primaryLeaveLabel = computed(() =>
  primaryDeductionType.value === "sick"
    ? "Sick Leave"
    : "Vacation Leave",
);

const primaryLeaveShortLabel = computed(() =>
  primaryDeductionType.value === "sick"
    ? "Sick"
    : "Vacation",
);

const projectedPrimaryBalance = computed(() => {
  const daysApplied =
    Number(approvalApplication.value?.number_of_days) || 0;

  const currentBalance =
    primaryDeductionType.value === "sick"
      ? employeeBalance.value.sick_balance
      : employeeBalance.value.vacation_balance;

  return Number(currentBalance) - daysApplied;
});

/* =========================================================
   PAGINATION
========================================================= */

const currentPage = ref(1);
const itemsPerPage = 5;

const toggleSort = () => {
  arranged.value = !arranged.value;
};

/* =========================================================
   TABS
========================================================= */

const tabs = [
  {
    key: "all",
    label: "All Applications",
    countClass: "bg-gray-100 text-gray-800",
  },
  {
    key: "pending",
    label: "Pending",
    countClass: "bg-yellow-100 text-yellow-800",
  },
  {
    key: "approved",
    label: "Approved",
    countClass: "bg-green-100 text-green-800",
  },
  {
    key: "disapproved",
    label: "Disapproved",
    countClass: "bg-red-100 text-red-800",
  },
  {
    key: "deleted",
    label: "Removed Applications",
    countClass: "bg-gray-100 text-gray-800",
  },
];

const handleTabChange = async (tabKey: string) => {
  activeTab.value = tabKey;
  currentPage.value = 1;

  if (tabKey === "deleted") {
    await getDeletedApplications();
  }
};

/* =========================================================
   FILTERED APPLICATIONS
========================================================= */

const filteredApplications = computed(() => {
  const search = searchQuery.value.trim().toLowerCase();

  let result;

  if (activeTab.value === "deleted") {
    result = deletedApplications.value.filter((app) => {
      const leaveType =
        app.leave_type?.leave_type_name?.toLowerCase() || "";

      const employeeName = `
        ${app.employee?.first_name || ""}
        ${app.employee?.middle_name || ""}
        ${app.employee?.last_name || ""}
      `.toLowerCase();

      const matchesSearch =
        search === "" ||
        employeeName.includes(search) ||
        leaveType.includes(search) ||
        String(app.leave_id).includes(search);

      const matchesType =
        filterType.value === "" ||
        leaveType.includes(filterType.value.toLowerCase());

      return matchesSearch && matchesType;
    });
  } else {
    result = applications.value.filter((app) => {
      const status = app.final_status?.toLowerCase() || "";

      const leaveType =
        app.leave_type?.leave_type_name?.toLowerCase() || "";

      const employeeName = `
        ${app.employee?.first_name || ""}
        ${app.employee?.middle_name || ""}
        ${app.employee?.last_name || ""}
      `.toLowerCase();

      const matchesTab =
        activeTab.value === "all" ||
        status === activeTab.value;

      const matchesSearch =
        search === "" ||
        employeeName.includes(search) ||
        leaveType.includes(search) ||
        status.includes(search) ||
        String(app.leave_id).includes(search);

      const matchesType =
        filterType.value === "" ||
        leaveType.includes(filterType.value.toLowerCase());

      return matchesTab && matchesSearch && matchesType;
    });
  }

  // Sort alphabetically if arranged is true
  if (arranged.value) {
    return [...result].sort((a, b) => {
      const aName = `${a.employee?.last_name || ""} ${a.employee?.first_name || ""}`
        .trim()
        .toLowerCase();
      const bName = `${b.employee?.last_name || ""} ${b.employee?.first_name || ""}`
        .trim()
        .toLowerCase();
      return aName.localeCompare(bName);
    });
  }

  return result;
});

/* =========================================================
   TOTAL PAGES
========================================================= */

const totalPages = computed(() => {
  return Math.max(
    1,
    Math.ceil(filteredApplications.value.length / itemsPerPage),
  );
});

/* =========================================================
   DISPLAYED APPLICATIONS
========================================================= */

const displayedApplications = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;

  return filteredApplications.value.slice(start, end);
});

/* =========================================================
   PAGINATION START
========================================================= */

const paginationStart = computed(() => {
  if (filteredApplications.value.length === 0) {
    return 0;
  }

  return (currentPage.value - 1) * itemsPerPage + 1;
});

/* =========================================================
   PAGINATION END
========================================================= */

const paginationEnd = computed(() => {
  return Math.min(
    currentPage.value * itemsPerPage,
    filteredApplications.value.length,
  );
});

/* =========================================================
   NEXT PAGE
========================================================= */

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

/* =========================================================
   PREVIOUS PAGE
========================================================= */

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

/* =========================================================
   RESET PAGINATION WHEN FILTERING
========================================================= */

watch(
  [searchQuery, filterType, activeTab],
  () => {
    currentPage.value = 1;
  },
);

/* =========================================================
   TAB COUNT
========================================================= */

const getTabCount = (tabKey: string) => {
  if (tabKey === "all") {
    return applications.value.length;
  }

  if (tabKey === "deleted") {
    return deletedApplications.value.length;
  }

  return applications.value.filter(
    (app) =>
      app.final_status?.toLowerCase() === tabKey,
  ).length;
};

/* =========================================================
   STATUS CLASS
========================================================= */

const getStatusClass = (status: string) => {
  switch (status?.toLowerCase()) {
    case "pending":
      return "bg-yellow-100 text-yellow-800";

    case "approved":
      return "bg-green-100 text-green-800";

    case "disapproved":
      return "bg-red-100 text-red-800";

    default:
      return "bg-gray-100 text-gray-800";
  }
};

/* =========================================================
   LEAVE TYPE
========================================================= */

const getLeaveType = (leaveType: any) => {
  return leaveType?.leave_type_name ?? "Not specified";
};

const getEmployeeName = (employee: any) => {
  if (!employee) {
    return "Employee record unavailable";
  }

  const givenName = [
    employee.first_name,
    employee.middle_name,
  ]
    .filter(Boolean)
    .join(" ");

  return (
    [employee.last_name, givenName]
      .filter(Boolean)
      .join(", ") ||
    "Employee record unavailable"
  );
};

/* =========================================================
   DATE FORMAT
========================================================= */

const formatDate = (dateString: string) => {
  const date = new Date(dateString);

  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

/* =========================================================
   FILE SIZE
========================================================= */

const formatFileSize = (bytes: number) => {
  if (bytes === 0) {
    return "0 Bytes";
  }

  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];

  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return (
    parseFloat(
      (bytes / Math.pow(k, i)).toFixed(2),
    ) +
    " " +
    sizes[i]
  );
};

/* =========================================================
   VIEW APPLICATION
========================================================= */

const viewApplication = (
  application: LeaveApplication,
) => {
  router.push(
    `/leave-print/${application.leave_id}`,
  );
};

/* =========================================================
   DOWNLOAD APPLICATION
========================================================= */

const downloadApplication = (
  application: LeaveApplication,
) => {
  router.push(
    `/leave-print/${application.leave_id}`,
  );
};

/* =========================================================
   LEAVE TYPE CHECKS
========================================================= */

const isVacationApplication = (
  application: LeaveApplication | null,
) => {
  if (!application) return false;

  const code = String(
    application.leave_type?.code ?? "",
  ).toUpperCase();

  const name = String(
    application.leave_type?.leave_type_name ?? "",
  ).toLowerCase();

  return (
    code === "VL" ||
    name.includes("vacation")
  );
};

const isSickApplication = (
  application: LeaveApplication | null,
) => {
  if (!application) return false;

  const code = String(
    application.leave_type?.code ?? "",
  ).toUpperCase();

  const name = String(
    application.leave_type?.leave_type_name ?? "",
  ).toLowerCase();

  return (
    code === "SL" ||
    name.includes("sick")
  );
};

const isPrimaryLeaveApplication = (
  application: LeaveApplication | null,
) =>
  isVacationApplication(application) ||
  isSickApplication(application);

/* =========================================================
   LOAD EMPLOYEE BALANCE
========================================================= */

const loadEmployeeBalance = async (
  employeeId: number,
) => {
  isLoadingBalance.value = true;
  balanceLoadError.value = "";

  try {
    const balance =
      await getLeaveBalanceByEmployeeId(
        employeeId,
      );

    employeeBalance.value = {
      vacation_balance:
        Number(balance?.vacation_balance) || 0,

      sick_balance:
        Number(balance?.sick_balance) || 0,

      service_credits:
        Number(balance?.service_credits) || 0,
    };
  } catch (error: any) {
    employeeBalance.value = {
      vacation_balance: 0,
      sick_balance: 0,
      service_credits: 0,
    };

    balanceLoadError.value =
      error.response?.data?.message ??
      "Could not load current leave balances.";
  } finally {
    isLoadingBalance.value = false;
  }
};

/* =========================================================
   RESET DEDUCTION VALUES
========================================================= */

const resetDeductionValues = () => {
  deductBalance.value = "yes";
  serviceCreditsDeductDays.value = 0;
  vacationDeductDays.value = 0;
  sickDeductDays.value = 0;
};

/* =========================================================
   OPEN APPROVAL MODAL
========================================================= */

const openApprovalModal = async (
  application: LeaveApplication,
) => {
  approvalApplication.value = application;

  resetDeductionValues();

  showDetailModal.value = false;
  showApprovalModal.value = false;

  showPrimaryApprovalModal.value =
    isPrimaryLeaveApplication(application);

  if (!showPrimaryApprovalModal.value) {
    sickDeductDays.value =
      application.number_of_days;

    showApprovalModal.value = true;
  }

  await loadEmployeeBalance(
    application.employee_id,
  );
};

/* =========================================================
   OPEN SPLIT DEDUCTION
========================================================= */

const openSplitDeduction = () => {
  if (!approvalApplication.value) return;

  resetDeductionValues();

  if (
    isSickApplication(
      approvalApplication.value,
    )
  ) {
    sickDeductDays.value =
      approvalApplication.value.number_of_days;
  } else {
    vacationDeductDays.value =
      approvalApplication.value.number_of_days;
  }

  showPrimaryApprovalModal.value = false;
  showApprovalModal.value = true;
};

/* =========================================================
   BACK TO PRIMARY APPROVAL
========================================================= */

const backToPrimaryApproval = () => {
  showApprovalModal.value = false;
  showPrimaryApprovalModal.value = true;
};

/* =========================================================
   CLOSE APPROVAL MODALS
========================================================= */

const closeApprovalModals = () => {
  showApprovalModal.value = false;
  showPrimaryApprovalModal.value = false;
  approvalApplication.value = null;
};

/* =========================================================
   APPROVE PRIMARY WITH DEDUCTION
========================================================= */

const approvePrimaryWithDeduction = async () => {
  const application =
    approvalApplication.value;

  if (
    !application ||
    !isPrimaryLeaveApplication(application)
  ) {
    return;
  }

  const daysApplied =
    Number(application.number_of_days) || 0;

  if (daysApplied <= 0) {
    alert(
      "The number of days applied must be greater than zero.",
    );

    return;
  }

  const isSick =
    isSickApplication(application);

  const currentBalance = isSick
    ? Number(
        employeeBalance.value.sick_balance,
      )
    : Number(
        employeeBalance.value.vacation_balance,
      );

  if (daysApplied > currentBalance) {
    alert(
      `Insufficient ${
        isSick
          ? "Sick Leave"
          : "Vacation Leave"
      } balance.`,
    );

    return;
  }

  await updateStatus(
    application.leave_id,
    "approved",
    {
      deduct_balance: true,

      vacation_deduct_days: isSick
        ? 0
        : daysApplied,

      sick_deduct_days: isSick
        ? daysApplied
        : 0,

      service_credits_deduct_days: 0,
    },
  );
};

/* =========================================================
   APPROVE WITHOUT DEDUCTION
========================================================= */

const approveWithoutDeduction = async () => {
  if (!approvalApplication.value) return;

  await updateStatus(
    approvalApplication.value.leave_id,
    "approved",
    {
      deduct_balance: false,
    },
  );
};

/* =========================================================
   FORMAT BALANCE
========================================================= */

const formatBalance = (
  value: unknown,
) => {
  const numberValue = Number(value);

  return Number.isFinite(numberValue)
    ? numberValue.toFixed(2)
    : "0.00";
};

/* =========================================================
   UPDATE STATUS
========================================================= */

const updateStatus = async (
  leaveId: number,
  status:
    | "approved"
    | "disapproved",

  deductionData: {
    deduct_balance?: boolean;
    service_credits_deduct_days?: number;
    vacation_deduct_days?: number;
    sick_deduct_days?: number;
  } = {},
) => {
  try {
    const token =
      localStorage.getItem("token");

    await axios.put(
      `https://enhs-leave-management-system.onrender.com/api/leave-applications/${leaveId}`,

      {
        final_status: status,
        ...deductionData,
      },

      {
        headers: {
          Authorization:
            `Bearer ${token}`,
        },
      },
    );

    showDetailModal.value = false;
    showApprovalModal.value = false;
    showPrimaryApprovalModal.value = false;

    await loadApplications();
  } catch (error: any) {
    console.error(error);

    alert(
      error.response?.data?.message ??
        "Failed to update leave application.",
    );
  }
};

/* =========================================================
   REJECTION
========================================================= */

const showRejectModal = ref(false);
const rejectionReason = ref("");
const rejectionLeaveId =
  ref<number | null>(null);

const isRejecting = ref(false);

const openRejectModal = (
  leaveId: number,
) => {
  rejectionLeaveId.value = leaveId;
  rejectionReason.value = "";
  showRejectModal.value = true;
};

const cancelReject = () => {
  showRejectModal.value = false;
  rejectionReason.value = "";
  rejectionLeaveId.value = null;
};

const confirmReject = async () => {
  if (!rejectionLeaveId.value) {
    return;
  }

  const reason =
    rejectionReason.value.trim();

  if (!reason) {
    alert(
      "Please enter a reason for disapproval.",
    );

    return;
  }

  try {
    isRejecting.value = true;

    await rejectLeaveApplication(
      rejectionLeaveId.value,
      reason,
    );

    alert(
      "Leave application disapproved successfully.",
    );

    showRejectModal.value = false;
    rejectionReason.value = "";
    rejectionLeaveId.value = null;

    showDetailModal.value = false;

    await loadApplications();
  } catch (error: any) {
    console.error(
      "Failed to reject leave application:",
      error,
    );

    console.error(
      "Response:",
      error.response?.data,
    );

    alert(
      error.response?.data?.message ??
        "Failed to reject leave application.",
    );
  } finally {
    isRejecting.value = false;
  }
};

/* =========================================================
   CONFIRM APPROVAL
========================================================= */

const confirmApproval = async () => {
  if (!approvalApplication.value) {
    return;
  }

  const application =
    approvalApplication.value;

  if (deductBalance.value === "yes") {
    const serviceCreditsDays =
      Number(
        serviceCreditsDeductDays.value,
      ) || 0;

    const vacationDays =
      Number(
        vacationDeductDays.value,
      ) || 0;

    const sickDays =
      Number(
        sickDeductDays.value,
      ) || 0;

    const totalDeduction =
      vacationDays +
      sickDays +
      serviceCreditsDays;

    if (totalDeduction <= 0) {
      alert(
        "Please enter at least one day to deduct.",
      );

      return;
    }

    if (
      totalDeduction >
      application.number_of_days
    ) {
      alert(
        "The total deduction cannot be greater than the number of days applied.",
      );

      return;
    }

    await updateStatus(
      application.leave_id,
      "approved",
      {
        deduct_balance: true,

        service_credits_deduct_days:
          serviceCreditsDays,

        vacation_deduct_days:
          vacationDays,

        sick_deduct_days:
          sickDays,
      },
    );

    return;
  }

  await updateStatus(
    application.leave_id,
    "approved",
    {
      deduct_balance: false,
    },
  );
};

/* =========================================================
   LOAD APPLICATIONS
========================================================= */

const loadApplications = async () => {
  try {
    const token =
      localStorage.getItem("token");

    const response = await axios.get(
      "https://enhs-leave-management-system.onrender.com/api/leave-applications",
      {
        headers: {
          Authorization:
            `Bearer ${token}`,
        },
      },
    );

    applications.value =
      response.data;

    currentPage.value = 1;
  } catch (error) {
    console.error(
      "Failed to load applications",
      error,
    );
  }
};

/* =========================================================
   DELETE
========================================================= */

const deleteLeaveApplicationById =
  async (
    leaveId: number,
  ) => {
    if (
      !confirm(
        "Are you sure you want to delete this leave application?",
      )
    ) {
      return;
    }

    try {
      await deleteLeaveApplication(
        leaveId,
      );

      alert(
        "Leave application deleted successfully.",
      );

      await loadApplications();
    } catch (error: any) {
      console.error(
        "Failed to delete leave application",
        error,
      );

      alert(
        error.response?.data?.message ??
          "Failed to delete leave application.",
      );
    }
  };

/* =========================================================
   GET DELETED APPLICATIONS
========================================================= */

const getDeletedApplications =
  async () => {
    try {
      const response =
        await getDeletedLeaveApplications();

      deletedApplications.value =
        Array.isArray(response)
          ? response
          : Array.isArray(
                response?.data,
              )
            ? response.data
            : [];

      currentPage.value = 1;

      console.log(
        "DELETED APPLICATIONS:",
        deletedApplications.value,
      );
    } catch (error: any) {
      deletedApplications.value = [];

      console.error(
        "Failed to fetch deleted leave applications",
        error,
      );

      alert(
        error.response?.data?.message ??
          "Failed to load removed leave applications.",
      );
    }
  };

/* =========================================================
   RESTORE
========================================================= */

const restoreLeaveApplicationById =
  async (
    leaveId: number,
  ) => {
    if (
      !confirm(
        "Are you sure you want to restore this leave application?",
      )
    ) {
      return;
    }

    try {
      await restoreLeaveApplication(
        leaveId,
      );

      alert(
        "Leave application restored successfully.",
      );

      await getDeletedApplications();
      await loadApplications();
    } catch (error: any) {
      console.error(
        "Failed to restore leave application",
        error,
      );

      alert(
        error.response?.data?.message ??
          "Failed to restore leave application.",
      );
    }
  };

/* =========================================================
   ON MOUNTED
========================================================= */

onMounted(() => {
  loadApplications();
});
</script>

<style scoped>
/* =========================================================
   ENHS DASHBOARD COLOR THEME
   COLOR CHANGES ONLY
========================================================= */

.dashboard-shell {
  background: #f3f6fa;
  min-height: 100vh;
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;

  color: #0f2742;
}

/* =========================================================
   MAIN CARDS
========================================================= */

.neo-card {
  background: #ffffff;

  border: 1px solid #cbd5e1;

  border-radius: 1.4rem;

  box-shadow:
    0 10px 22px
    rgba(15, 39, 66, 0.08);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;

  min-width: 0;
}

.neo-card:hover {
  box-shadow:
    0 14px 26px
    rgba(15, 39, 66, 0.12);
}

/* =========================================================
   TEXT
========================================================= */

.dashboard-shell .text-white {
  color: #0f2742 !important;
}

.dashboard-shell .text-gray-300 {
  color: #475569 !important;
}

.dashboard-shell .text-gray-400 {
  color: #64748b !important;
}

.dashboard-shell .text-gray-500 {
  color: #64748b !important;
}

.dashboard-shell .text-gray-600 {
  color: #475569 !important;
}

.dashboard-shell .text-gray-700 {
  color: #334155 !important;
}

.dashboard-shell .text-gray-800 {
  color: #334155 !important;
}

/* =========================================================
   BORDER COLORS
========================================================= */

.dashboard-shell .border-gray-200 {
  border-color: #e2e8f0 !important;
}

.dashboard-shell .border-gray-300 {
  border-color: #cbd5e1 !important;
}

.dashboard-shell .border-slate-700 {
  border-color: #cbd5e1 !important;
}

.dashboard-shell .border-slate-800 {
  border-color: #e2e8f0 !important;
}

/* =========================================================
   SEARCH / SELECT
========================================================= */

.dashboard-shell .bg-\[\#0B1420\] {
  background: #ffffff !important;
  color: #0f2742 !important;
  border-color: #cbd5e1 !important;
}

.dashboard-shell input,
.dashboard-shell select,
.dashboard-shell textarea {
  color: #0f2742;
}

.dashboard-shell input::placeholder,
.dashboard-shell textarea::placeholder {
  color: #94a3b8;
}

.dashboard-shell select option {
  background: #ffffff;
  color: #0f2742;
}

/* =========================================================
   CLEAR BUTTON
========================================================= */

.dashboard-shell button.border-gray-300 {
  border-color: #cbd5e1;
}

.dashboard-shell button.border-gray-300:hover {
  background: #f1f5f9;
  color: #0f2742;
}

/* =========================================================
   TABLE / LIST BACKGROUNDS
========================================================= */

.dashboard-shell .bg-slate-800 {
  background: #ffffff !important;
}

.dashboard-shell .bg-slate-800\/50 {
  background: #f1f5f9 !important;
}

.dashboard-shell .bg-slate-700 {
  background: #e2e8f0 !important;
}

.dashboard-shell .hover\:bg-slate-700:hover {
  background: #cbd5e1 !important;
}

/* =========================================================
   APPLICATION CARDS
========================================================= */

.dashboard-shell
  .neo-card.border-gray-200 {
  background: #ffffff;
  border-color: #cbd5e1;
}

.dashboard-shell
  .neo-card.border-gray-200:hover {
  background: #ffffff;
}

/* =========================================================
   PRIMARY BLUE
   Same blue language as Dashboard
========================================================= */

.dashboard-shell .bg-blue-600 {
  background-color: #2563eb !important;
}

.dashboard-shell .hover\:bg-blue-700:hover {
  background-color: #1d4ed8 !important;
}

.dashboard-shell .text-blue-600 {
  color: #2563eb !important;
}

.dashboard-shell .text-blue-700 {
  color: #1d4ed8 !important;
}

.dashboard-shell .bg-blue-100 {
  background-color: #eaf2ff !important;
}

.dashboard-shell .text-blue-300 {
  color: #3b82f6 !important;
}

.dashboard-shell .border-blue-400\/30 {
  border-color: #bfdbfe !important;
}

.dashboard-shell .bg-blue-500\/10 {
  background-color: #eff6ff !important;
}

/* =========================================================
   PENDING / YELLOW
========================================================= */

.dashboard-shell .bg-yellow-100 {
  background-color: #fff8e1 !important;
}

.dashboard-shell .text-yellow-800 {
  color: #b77900 !important;
}

/* =========================================================
   AMBER / ORANGE
========================================================= */

.dashboard-shell .bg-amber-600 {
  background-color: #e59a00 !important;
}

.dashboard-shell .hover\:bg-amber-700:hover {
  background-color: #c98200 !important;
}

/* =========================================================
   APPROVED / GREEN
========================================================= */

.dashboard-shell .bg-green-600 {
  background-color: #16a34a !important;
}

.dashboard-shell .hover\:bg-green-700:hover {
  background-color: #15803d !important;
}

.dashboard-shell .bg-green-100 {
  background-color: #e8f8ef !important;
}

.dashboard-shell .text-green-800 {
  color: #15803d !important;
}

.dashboard-shell .text-green-600 {
  color: #16a34a !important;
}

.dashboard-shell .text-green-300 {
  color: #22c55e !important;
}

/* =========================================================
   DISAPPROVED / RED
========================================================= */

.dashboard-shell .bg-red-600 {
  background-color: #dc2626 !important;
}

.dashboard-shell .hover\:bg-red-700:hover {
  background-color: #b91c1c !important;
}

.dashboard-shell .bg-red-100 {
  background-color: #fff0f0 !important;
}

.dashboard-shell .text-red-800 {
  color: #dc2626 !important;
}

.dashboard-shell .text-red-600 {
  color: #dc2626 !important;
}

.dashboard-shell .text-red-300 {
  color: #ef4444 !important;
}

/* =========================================================
   GRAY / NEUTRAL BUTTONS
========================================================= */

.dashboard-shell .bg-gray-600 {
  background-color: #64748b !important;
}

.dashboard-shell .hover\:bg-gray-700:hover {
  background-color: #475569 !important;
}

/* =========================================================
   OLD DARK BACKGROUND COLORS
   Completely removed visually
========================================================= */

.dashboard-shell .bg-\[\#080D14\] {
  background: #f3f6fa !important;
}

.dashboard-shell .bg-\[\#111D2E\] {
  background: #ffffff !important;
}

.dashboard-shell .bg-\[\#0F1A2A\] {
  background: #f8fafc !important;
}

.dashboard-shell .bg-\[\#0B1420\] {
  background: #ffffff !important;
}

/* =========================================================
   MODAL
========================================================= */

.dashboard-shell .modal-section {
  background: #f8fafc;
  border-color: #d8e1eb;
}

.dashboard-shell .bg-gray-600 {
  background-color: #64748b !important;
}

.dashboard-shell .bg-opacity-50 {
  --tw-bg-opacity: 0.45;
}

/* =========================================================
   BALANCE SUMMARY CARDS
========================================================= */

.balance-summary-card {
  display: flex;
  flex-direction: column;

  gap: 0.35rem;

  padding: 0.85rem;

  border: 1px solid #cbd5e1;

  border-radius: 0.75rem;

  background: #f8fafc;

  color: #475569;

  font-size: 0.75rem;
}

.balance-summary-card strong {
  color: #0f2742;
  font-size: 0.9rem;
}

/* =========================================================
   BUTTONS
========================================================= */

.btn-action {
  display: inline-flex;

  align-items: center;
  justify-content: center;

  padding: 0.375rem 0.85rem;

  font-size: 0.75rem;
  font-weight: 500;

  line-height: 1.25rem;

  color: #ffffff !important;

  border-radius: 9999px;

  white-space: nowrap;

  transition:
    background-color 0.15s ease,
    transform 0.1s ease;
}

.btn-action:active {
  transform: scale(0.97);
}

.btn-action-lg {
  display: inline-flex;

  align-items: center;
  justify-content: center;

  width: 100%;

  padding: 0.5rem 1.1rem;

  font-size: 0.8rem;
  font-weight: 500;

  color: #ffffff !important;

  border-radius: 9999px;

  white-space: nowrap;

  transition:
    background-color 0.15s ease,
    transform 0.1s ease;
}

@media (min-width: 640px) {
  .btn-action-lg {
    width: auto;
  }
}

.btn-action-lg:active {
  transform: scale(0.98);
}

/* =========================================================
   MODAL INPUTS
========================================================= */

.dashboard-shell textarea {
  background: #ffffff !important;
  color: #0f2742 !important;
}

.dashboard-shell
  input[type="number"] {
  background: #ffffff !important;
  color: #0f2742 !important;
  border-color: #cbd5e1 !important;
}

/* =========================================================
   PAGINATION
========================================================= */

.dashboard-shell
  button:disabled {
  pointer-events: none;
}

.dashboard-shell
  button:not(:disabled) {
  cursor: pointer;
}

/* =========================================================
   RESPONSIVE / ZOOM BEHAVIOR
========================================================= */

.neo-card * {
  min-width: 0;
}

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card div {
  overflow-wrap: anywhere;
}

/* =========================================================
   LARGE TABLET / HIGH ZOOM
========================================================= */

@media (max-width: 1023px) {
  .dashboard-shell {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }
}

/* =========================================================
   TABLET / HIGHER ZOOM
========================================================= */

@media (max-width: 767px) {
  .dashboard-shell {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }

  .neo-card {
    border-radius: 1rem;
  }
}

/* =========================================================
   SMALL PHONE / VERY HIGH ZOOM
========================================================= */

@media (max-width: 480px) {
  .dashboard-shell {
    padding-left: 0.25rem;
    padding-right: 0.25rem;
  }

  .neo-card {
    border-radius: 0.75rem;
  }
}
</style>