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
                    {{ getEmployeePosition(application.employee) }}
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

                      {{ attachment.file_name || attachment.name }}
                      <button
                        type="button"
                        class="ml-2 text-blue-700 underline"
                        @click.stop="openSupportingDocument(application, attachment)"
                      >
                        View
                      </button>
                    </span>
                  </div>
                </div>

                <div v-if="application.document_requirements_snapshot?.length" class="mt-4">
                  <h4 class="text-sm font-semibold text-white">Supporting Documents</h4>
                  <div class="overflow-x-auto mt-2">
                    <table class="w-full text-sm text-white">
                      <thead>
                        <tr class="border-b border-slate-500 text-left">
                          <th class="py-2 pr-4">Requirement</th>
                          <th class="py-2 pr-4">Status</th>
                          <th class="py-2">File</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="requirement in application.document_requirements_snapshot"
                          :key="requirement.id"
                          class="border-b border-slate-600"
                        >
                          <td class="py-2 pr-4">{{ requirement.document_name }}</td>
                          <td class="py-2 pr-4">
                            <span v-if="attachmentForRequirement(application, requirement.id)" class="text-emerald-300">Uploaded</span>
                            <span v-else-if="requirement.required" class="text-red-300">Missing</span>
                            <span v-else-if="requirement.status === 'optional'" class="text-slate-300">Optional</span>
                            <span v-else class="text-slate-300">Not Required</span>
                          </td>
                          <td class="py-2">
                            <button
                              v-if="attachmentForRequirement(application, requirement.id)"
                              type="button"
                              class="text-blue-300 underline break-all"
                              @click="openSupportingDocument(application, attachmentForRequirement(application, requirement.id))"
                            >
                              {{ attachmentForRequirement(application, requirement.id)?.file_name }}
                            </button>
                            <span v-else>—</span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div
                class="flex flex-row flex-wrap gap-2 xl:flex-col xl:ml-4 w-full xl:w-auto xl:flex-shrink-0"
              >
                <template v-if="activeTab !== 'deleted'">
                  <!-- View Details -->
                  <button
                    @click="viewDetails(application)"
                    class="btn-action bg-blue-600 hover:bg-blue-700"
                  >
                    View Details
                  </button>

                  <!-- View Form -->
                  <button
                    @click="viewForm(application)"
                    class="btn-action bg-amber-600 hover:bg-amber-700"
                  >
                    View Form
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
      v-if="showDetailModal && selectedApplication"
      class="detail-modal-backdrop fixed inset-0 overflow-y-auto w-full h-full z-50 p-2 sm:p-4"
    >
      <div
        class="relative mx-auto my-1 sm:my-3 w-full max-w-3xl shadow-lg rounded-lg bg-white neo-card flex flex-col max-h-[calc(100vh-1rem)] sm:max-h-[calc(100vh-2rem)]"
      >
        <!-- Header -->
        <div class="flex justify-between items-start gap-3 px-4 pt-3 pb-2 border-b border-gray-200">
          <div>
            <h3 class="text-lg font-semibold text-white leading-tight">
              Application Details
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">
              Application #{{ selectedApplication.leave_id }}
            </p>
          </div>

          <button
            @click="closeDetailModal"
            class="text-white hover:text-gray-600 flex-shrink-0"
            aria-label="Close"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Scrollable body -->
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-2.5">
          <!-- Application Information -->
          <div class="border border-gray-300 p-3 min-w-0 modal-section">
            <h4 class="text-sm font-bold mb-1.5 text-white">
              Application Information
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-sm text-white">
              <div class="break-words">
                <strong>Employee:</strong>
                {{ getEmployeeName(selectedApplication.employee) }}
              </div>

              <div class="break-words">
                <strong>Office:</strong>
                {{ selectedApplication.employee?.department_name ?? "Not available" }}
              </div>

              <div class="break-words">
                <strong>Position:</strong>
                {{ getEmployeePosition(selectedApplication.employee) }}
              </div>

              <div class="break-words">
                <strong>Filed Date:</strong>
                {{ formatDate(selectedApplication.date_filed) }}
              </div>

              <div class="break-words">
                <strong>Status:</strong>
                <span
                  :class="[
                    'px-2 py-0.5 text-xs font-medium rounded-full whitespace-nowrap',
                    getStatusClass(selectedApplication.final_status),
                  ]"
                >
                  {{ selectedApplication.final_status }}
                </span>
              </div>
            </div>
          </div>

          <!-- Leave Details -->
          <div class="border border-gray-300 p-3 min-w-0 modal-section">
            <h4 class="text-sm font-bold mb-1.5 text-white">
              Leave Details
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-sm text-white">
              <div class="break-words">
                <strong>Leave Type:</strong>
                {{ getLeaveType(selectedApplication.leave_type) }}
              </div>

              <div class="break-words">
                <strong>Duration:</strong>
                {{ formatDate(selectedApplication.start_date) }}
                -
                {{ formatDate(selectedApplication.end_date) }}
              </div>

              <div class="break-words">
                <strong>Days Applied:</strong>
                {{ selectedApplication.number_of_days }}
              </div>

              <div class="break-words">
                <strong>Commutation:</strong>
                {{
                  selectedApplication.commutation === "requested"
                    ? "Requested"
                    : "Not Requested"
                }}
              </div>

              <div
                v-for="row in getLeaveDetailRows(selectedApplication)"
                :key="row.label + row.value"
                class="break-words"
              >
                <strong>{{ row.label }}:</strong>
                {{ row.value }}
              </div>

              <div class="break-words whitespace-pre-line sm:col-span-2">
                <strong>Reason for Leave:</strong>
                {{ selectedApplication.reason || "Not provided" }}
              </div>

              <div
                v-if="selectedApplication.final_status?.toLowerCase() === 'disapproved' && selectedApplication.disapproval_reason"
                class="break-words sm:col-span-2"
              >
                <strong>Reason for Disapproval:</strong>
                {{ selectedApplication.disapproval_reason }}
              </div>
            </div>
          </div>

          <!-- Leave Credits Snapshot -->
          <div
            v-if="hasCreditsSnapshot(selectedApplication)"
            class="border border-gray-300 p-3 min-w-0 modal-section"
          >
            <h4 class="text-sm font-bold mb-1.5 text-white">
              Leave Credits
              <span
                v-if="selectedApplication.certification_as_of"
                class="text-xs font-normal text-gray-400 ml-1"
              >
                (as of {{ formatDate(selectedApplication.certification_as_of) }})
              </span>
            </h4>

            <div class="overflow-x-auto">
              <table class="w-full text-sm text-white">
                <thead>
                  <tr class="border-b border-slate-500 text-left">
                    <th class="py-1 pr-4"></th>
                    <th class="py-1 pr-4">Total Earned</th>
                    <th class="py-1 pr-4">Less this Application</th>
                    <th class="py-1">Balance</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-slate-600">
                    <td class="py-1 pr-4 font-medium">Vacation Leave</td>
                    <td class="py-1 pr-4">{{ formatCredit(selectedApplication.vacation_total_earned) }}</td>
                    <td class="py-1 pr-4">{{ formatCredit(selectedApplication.vacation_less_application) }}</td>
                    <td class="py-1">{{ formatCredit(selectedApplication.vacation_balance) }}</td>
                  </tr>
                  <tr class="border-b border-slate-600">
                    <td class="py-1 pr-4 font-medium">Sick Leave</td>
                    <td class="py-1 pr-4">{{ formatCredit(selectedApplication.sick_total_earned) }}</td>
                    <td class="py-1 pr-4">{{ formatCredit(selectedApplication.sick_less_application) }}</td>
                    <td class="py-1">{{ formatCredit(selectedApplication.sick_balance) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Recommendation and Approval -->
          <div class="border border-gray-300 p-3 min-w-0 modal-section">
            <h4 class="text-sm font-bold mb-1.5 text-white">
              Recommendation and Approval
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-sm text-white">
              <div class="break-words">
                <strong>Recommendation:</strong>
                {{ formatLabel(selectedApplication.recommendation_status) || "Pending" }}
              </div>

              <div
                v-if="hasValue(selectedApplication.recommendation_reason)"
                class="break-words"
              >
                <strong>Recommendation Reason:</strong>
                {{ selectedApplication.recommendation_reason }}
              </div>

              <div
                v-if="hasValue(selectedApplication.days_with_pay)"
                class="break-words"
              >
                <strong>Days with Pay:</strong>
                {{ selectedApplication.days_with_pay }}
              </div>

              <div
                v-if="hasValue(selectedApplication.days_without_pay)"
                class="break-words"
              >
                <strong>Days without Pay:</strong>
                {{ selectedApplication.days_without_pay }}
              </div>

              <div
                v-if="hasValue(selectedApplication.other_approval)"
                class="break-words"
              >
                <strong>Others:</strong>
                {{ selectedApplication.other_approval }}
              </div>

              <div
                v-if="hasValue(selectedApplication.admin_remarks)"
                class="break-words sm:col-span-2"
              >
                <strong>Admin Remarks:</strong>
                {{ selectedApplication.admin_remarks }}
              </div>
            </div>
          </div>

          <!-- Supporting Documents -->
          <div class="border border-gray-300 p-3 min-w-0 modal-section">
            <h4 class="text-sm font-bold mb-1.5 text-white">
              Supporting Documents
            </h4>

            <div
              v-if="selectedApplication.attachments?.length"
              class="space-y-1.5"
            >
              <div
                v-for="file in selectedApplication.attachments"
                :key="file.attachment_id"
                class="flex items-center justify-between gap-3 px-3 py-1.5 border border-gray-300 rounded-lg bg-white"
              >
                <span class="text-sm text-white truncate">
                  {{ file.file_name || file.name || "Supporting Document" }}
                </span>

                <button
                  type="button"
                  class="text-blue-600 hover:text-blue-700 text-sm font-medium flex-shrink-0"
                  @click="openSupportingDocument(selectedApplication, file)"
                >
                  View
                </button>
              </div>
            </div>

            <p v-else class="text-sm text-gray-400">
              No supporting documents attached.
            </p>

            <div
              v-if="selectedApplication.document_requirements_snapshot?.length"
              class="overflow-x-auto mt-2"
            >
              <table class="w-full text-sm text-white">
                <thead>
                  <tr class="border-b border-slate-500 text-left">
                    <th class="py-1 pr-4">Requirement</th>
                    <th class="py-1">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="requirement in selectedApplication.document_requirements_snapshot"
                    :key="requirement.id"
                    class="border-b border-slate-600"
                  >
                    <td class="py-1 pr-4">{{ requirement.document_name }}</td>
                    <td class="py-1">
                      <span v-if="attachmentForRequirement(selectedApplication, requirement.id)" class="text-green-600">Uploaded</span>
                      <span v-else-if="requirement.required" class="text-red-600">Missing</span>
                      <span v-else-if="requirement.status === 'optional'" class="text-gray-400">Optional</span>
                      <span v-else class="text-gray-400">Not Required</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Status Timeline -->
          <div class="border border-gray-300 p-3 min-w-0 modal-section">
            <h4 class="text-sm font-bold mb-1.5 text-white">
              Status Timeline
            </h4>

            <div class="flex flex-wrap gap-x-8 gap-y-2">
              <div class="flex items-start">
                <div class="w-2.5 h-2.5 bg-blue-600 rounded-full mt-1.5 mr-2 flex-shrink-0"></div>
                <div>
                  <p class="text-sm font-medium text-white leading-tight">Application Submitted</p>
                  <p class="text-xs text-gray-400">
                    {{ formatDate(selectedApplication.date_filed) }}
                  </p>
                </div>
              </div>

              <div
                v-if="selectedApplication.final_status?.toLowerCase() === 'approved'"
                class="flex items-start"
              >
                <div class="w-2.5 h-2.5 bg-green-600 rounded-full mt-1.5 mr-2 flex-shrink-0"></div>
                <p class="text-sm font-medium text-white leading-tight">Application Approved</p>
              </div>

              <div
                v-if="selectedApplication.final_status?.toLowerCase() === 'disapproved'"
                class="flex items-start"
              >
                <div class="w-2.5 h-2.5 bg-red-600 rounded-full mt-1.5 mr-2 flex-shrink-0"></div>
                <p class="text-sm font-medium text-white leading-tight">Application Disapproved</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer (always visible) -->
        <div
          class="flex flex-col sm:flex-row justify-end gap-2 px-4 py-3 border-t border-gray-200"
        >
          <button
            @click="viewForm(selectedApplication)"
            class="btn-action-lg bg-amber-600 hover:bg-amber-700"
          >
            View Form
          </button>

          <button
            v-if="selectedApplication.final_status?.toLowerCase() === 'pending'"
            @click="openApprovalModal(selectedApplication)"
            class="btn-action-lg bg-green-600 hover:bg-green-700"
          >
            Approve
          </button>

          <button
            v-if="selectedApplication.final_status?.toLowerCase() === 'pending'"
            @click="openRejectModal(selectedApplication.leave_id)"
            class="btn-action-lg bg-red-600 hover:bg-red-700"
          >
            Reject
          </button>

          <button
            @click="closeDetailModal"
            class="btn-action-lg bg-gray-600 hover:bg-gray-700"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ====================================================== -->
  <!-- APPROVAL MODAL -->
  <!-- ====================================================== -->

  <div
    v-if="showApprovalModal"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-[60] p-3 sm:p-4 overflow-y-auto"
  >
    <div
      class="bg-white rounded-lg shadow-xl w-full max-w-lg p-4 sm:p-6 neo-card max-h-[95vh] overflow-y-auto"
    >
      <h3 class="text-lg font-semibold text-white">
        Approve Leave Application
      </h3>

      <p class="text-sm text-gray-300 mt-2">
        {{ getEmployeeName(approvalApplication?.employee) }} applied for
        <strong>{{ approvalApplication?.number_of_days ?? 0 }}</strong>
        day(s) of leave.
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
          <span>Local Credits</span>
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

      <!-- Deduction Options -->
      <div class="mt-5 border-t pt-4">
        <h4 class="text-sm font-semibold text-white mb-3">
          Leave Balance Deduction
        </h4>

        <!-- Service Credits -->
        <div class="mt-4">
          <label class="block text-sm font-medium text-white mb-2">
            Local Credits Days to Deduct
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
            Enter the number of days to deduct from Local Credits.
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

          <div class="flex justify-between text-sm gap-3 mt-2">
            <span class="text-gray-800">
              Total to deduct:
            </span>

            <span class="font-medium text-gray-800 text-right">
              {{ (vacationDeductDays + sickDeductDays + serviceCreditsDeductDays).toFixed(3) }}
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
            Total deduction cannot exceed days applied.
          </p>
        </div>
      </div>

      <!-- Buttons -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-6">
        <button
          @click="confirmApproval"
          :disabled="(vacationDeductDays + sickDeductDays + serviceCreditsDeductDays) === 0"
          class="btn-action-lg bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Approve with Deduction
        </button>

        <button
          @click="approveWithoutDeduction"
          class="btn-action-lg bg-blue-600 hover:bg-blue-700"
        >
          Approve Without Deduction
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
import { confirmAction, notify } from "@/composables/useNotifications";
import { ref, computed, onMounted, watch } from "vue";

import { useRouter } from "vue-router";
import {
  deleteLeaveApplication,
  restoreLeaveApplication,
  getDeletedLeaveApplications,
  rejectLeaveApplication,
  downloadLeaveAttachment,
} from "@/services/leave";
import { getLeaveBalanceByEmployeeId } from "@/services/leaveBalance";
import { getPositions } from "@/services/employee";

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
  recommendation_reason?: string | null;
  disapproval_reason?: string | null;
  admin_remarks?: string | null;

  // Leave-type specific details
  vacation_location_type?: string | null;
  vacation_location?: string | null;
  sick_type?: string | null;
  illness?: string | null;
  masters_degree?: boolean | number | string | null;
  board_exam_review?: boolean | number | string | null;
  monetization?: boolean | number | string | null;
  terminal_leave?: boolean | number | string | null;
  other_purpose?: string | null;

  // Leave credits snapshot (CS Form 6, Part 7A)
  certification_as_of?: string | null;
  vacation_total_earned?: number | string | null;
  vacation_less_application?: number | string | null;
  vacation_balance?: number | string | null;
  sick_total_earned?: number | string | null;
  sick_less_application?: number | string | null;
  sick_balance?: number | string | null;

  // Approval details
  days_with_pay?: number | null;
  days_without_pay?: number | null;
  other_approval?: string | null;

  employee: any;
  leave_type: any;
  attachments: any[];
  document_requirements_snapshot?: Array<{
    id: number;
    document_name: string;
    required: boolean;
    status: "required" | "optional" | "not_required";
  }>;
}

const router = useRouter();

const attachmentForRequirement = (
  application: LeaveApplication,
  requirementId: number,
) => application.attachments?.find(
  (attachment: any) => Number(attachment.leave_document_requirement_id) === requirementId,
);

const openSupportingDocument = async (
  application: LeaveApplication,
  attachment: any,
) => {
  const viewer = window.open("about:blank", "_blank");
  try {
    const blob = await downloadLeaveAttachment(application.leave_id, attachment.attachment_id);
    const url = URL.createObjectURL(blob);
    if (viewer) {
      viewer.opener = null;
      viewer.location.href = url;
    } else {
      const download = document.createElement("a");
      download.href = url;
      download.download = attachment.file_name || "supporting-document";
      download.click();
    }
    window.setTimeout(() => URL.revokeObjectURL(url), 60_000);
  } catch (error: any) {
    viewer?.close();
    console.error("Failed to open supporting document:", error);
    notify(error.message || "Unable to open the supporting document.");
  }
};

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

const positions = ref<Array<{ id: number; name: string }>>([]);

const loadPositions = async () => {
  try {
    const result = await getPositions();
    positions.value = Array.isArray(result) ? result : [];
  } catch (error) {
    console.error("Failed to load positions", error);
  }
};

const getEmployeePosition = (employee: any): string => {
  const position = employee?.position;
  const name = typeof position === "string" ? position : position?.name;
  if (typeof name === "string" && name.trim()) return name;

  const positionId = Number(employee?.position_id);
  return positions.value.find((item) => Number(item.id) === positionId)?.name || "Not available";
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
   DETAIL MODAL HELPERS
========================================================= */

const hasValue = (value: unknown) =>
  value !== null && value !== undefined && String(value).trim() !== "";

const isTrue = (value: unknown) =>
  value === true || value === 1 || value === "1";

// "within_philippines" -> "Within Philippines"
const formatLabel = (value?: string | null) =>
  hasValue(value)
    ? String(value)
        .replace(/_/g, " ")
        .replace(/\b\w/g, (char) => char.toUpperCase())
    : "";

// Shows "-" instead of a misleading 0.000 when a credit value was not saved.
const formatCredit = (value: unknown) =>
  hasValue(value) && Number.isFinite(Number(value))
    ? Number(value).toFixed(3)
    : "-";

const hasCreditsSnapshot = (application: LeaveApplication) =>
  [
    application.vacation_total_earned,
    application.vacation_less_application,
    application.vacation_balance,
    application.sick_total_earned,
    application.sick_less_application,
    application.sick_balance,
  ].some(hasValue);

// Only the details that apply to this application are returned.
const getLeaveDetailRows = (application: LeaveApplication) => {
  const rows: Array<{ label: string; value: string }> = [];

  if (hasValue(application.vacation_location_type)) {
    rows.push({
      label: "Vacation Location",
      value: formatLabel(application.vacation_location_type),
    });
  }

  if (hasValue(application.vacation_location)) {
    rows.push({
      label: "Specified Location",
      value: String(application.vacation_location),
    });
  }

  if (hasValue(application.sick_type)) {
    rows.push({
      label: "Sick Leave Type",
      value: formatLabel(application.sick_type),
    });
  }

  if (hasValue(application.illness)) {
    rows.push({ label: "Illness", value: String(application.illness) });
  }

  if (isTrue(application.masters_degree)) {
    rows.push({
      label: "Study Leave",
      value: "Completion of Master's Degree",
    });
  }

  if (isTrue(application.board_exam_review)) {
    rows.push({
      label: "Study Leave",
      value: "BAR/Board Examination Review",
    });
  }

  if (isTrue(application.monetization)) {
    rows.push({
      label: "Other Purpose",
      value: "Monetization of Leave Credits",
    });
  }

  if (isTrue(application.terminal_leave)) {
    rows.push({ label: "Other Purpose", value: "Terminal Leave" });
  }

  if (hasValue(application.other_purpose)) {
    rows.push({
      label: "Other Purpose",
      value: String(application.other_purpose),
    });
  }

  return rows;
};

/* =========================================================
   VIEW DETAILS (opens the details modal)
========================================================= */

const viewDetails = (
  application: LeaveApplication,
) => {
  selectedApplication.value = application;
  showDetailModal.value = true;
};

const closeDetailModal = () => {
  showDetailModal.value = false;
  selectedApplication.value = null;
};

/* =========================================================
   VIEW FORM (opens the CS Form 6 print view)
========================================================= */

const viewForm = (
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

const resetDeductionValues = (application?: LeaveApplication) => {
  deductBalance.value = "yes";
  serviceCreditsDeductDays.value = 0;
  vacationDeductDays.value = 0;
  sickDeductDays.value = 0;

  // Automatically set deduction days based on leave type
  if (application) {
    const days = application.number_of_days;
    if (isVacationApplication(application)) {
      vacationDeductDays.value = days;
    } else if (isSickApplication(application)) {
      sickDeductDays.value = days;
    } else {
      // Other leave types use service credits
      serviceCreditsDeductDays.value = days;
    }
  }
};

/* =========================================================
   OPEN APPROVAL MODAL
========================================================= */

const openApprovalModal = async (
  application: LeaveApplication,
) => {
  approvalApplication.value = application;

  resetDeductionValues(application);

  showDetailModal.value = false;
  showApprovalModal.value = true;

  await loadEmployeeBalance(
    application.employee_id,
  );
};

/* =========================================================
   OPEN SPLIT DEDUCTION
========================================================= */

const openSplitDeduction = () => {
  if (!approvalApplication.value) return;

  resetDeductionValues(approvalApplication.value);

  showApprovalModal.value = true;
};

/* =========================================================
   CLOSE APPROVAL MODALS
========================================================= */

const closeApprovalModals = () => {
  showApprovalModal.value = false;
  approvalApplication.value = null;
};

/* =========================================================
   APPROVE PRIMARY WITH DEDUCTION
========================================================= */
const approvePrimaryWithDeduction = async () => {
  const application =
    approvalApplication.value;

  if (!application) {
    return;
  }

  try {
    await confirmApproval();
  } catch (error: any) {
    console.error(
      "Failed to approve with deduction:",
      error.response?.data || error,
    );
  }
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
    ? numberValue.toFixed(3)
    : "0.000";
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

    await loadApplications();
  } catch (error: any) {
    console.error(error);

    notify(
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
    notify(
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

    notify(
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

    notify(
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
      notify(
        "Please enter at least one day to deduct.",
      );

      return;
    }

    if (
      totalDeduction >
      application.number_of_days
    ) {
      notify(
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
      !(await confirmAction({
        title: "Delete leave application?",
        message: "Are you sure you want to delete this leave application?",
        confirmLabel: "Delete application",
      }))
    ) {
      return;
    }

    try {
      await deleteLeaveApplication(
        leaveId,
      );

      notify(
        "Leave application deleted successfully.",
      );

      await loadApplications();
    } catch (error: any) {
      console.error(
        "Failed to delete leave application",
        error,
      );

      notify(
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

      notify(
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
      !(await confirmAction({
        title: "Restore leave application?",
        message: "Are you sure you want to restore this leave application?",
        confirmLabel: "Restore application",
        variant: "primary",
      }))
    ) {
      return;
    }

    try {
      await restoreLeaveApplication(
        leaveId,
      );

      notify(
        "Leave application restored successfully.",
      );

      await getDeletedApplications();
      await loadApplications();
    } catch (error: any) {
      console.error(
        "Failed to restore leave application",
        error,
      );

      notify(
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
  loadPositions();
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

/* Dark, blurred backdrop behind the View Details modal
   (same look as the employee My Applications modal). */
.detail-modal-backdrop {
  background: rgba(0, 0, 0, 0.7);
  -webkit-backdrop-filter: blur(4px);
  backdrop-filter: blur(4px);
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
