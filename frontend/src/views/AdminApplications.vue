<template>
  <div
    class="w-full max-w-[1400px] mx-auto px-2 sm:px-3 md:px-4 lg:px-6 py-4 dashboard-shell"
  >
    <div class="bg-white rounded-lg shadow neo-card w-full min-w-0">
      <!-- Header -->
      <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-white">
          Submitted Leave Applications
        </h2>

        <p class="text-sm text-white mt-1">
          Review and manage faculty leave applications
        </p>
      </div>

      <!-- Filter Tabs -->
      <div class="px-4 sm:px-6 py-3 border-b border-gray-200 overflow-x-auto">
        <div class="flex space-x-2 sm:space-x-4 min-w-max">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="
              activeTab = tab.key;
              tab.key === 'deleted'
                ? getDeletedApplications()
                : loadApplications();
            "
            :class="[
              'px-3 sm:px-4 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap',
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
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white bg-[#0B1420]"
              />
            </div>

            <!-- Leave Type -->
            <select
              v-model="filterType"
              class="w-full lg:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white bg-[#0B1420]"
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

            <!-- Clear -->
            <button
              @click="
                searchQuery = '';
                filterType = '';
                activeTab = 'all';
              "
              class="w-full lg:w-auto px-4 py-2 border border-gray-300 text-white rounded-lg hover:bg-gray-50 hover:text-gray-800 transition whitespace-nowrap"
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
                    {{ application.employee.last_name }},
                    {{ application.employee.first_name }}
                    {{ application.employee.middle_name }}
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

                    {{ application.employee.department }}
                  </div>

                  <div class="min-w-0 break-words">
                    <span class="font-medium"> Position: </span>

                    {{ application.employee.position }}
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
                      class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded max-w-full break-all"
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
                <!-- ===================================================== -->
                <!-- NORMAL APPLICATIONS -->
                <!-- ===================================================== -->

                <template v-if="activeTab !== 'deleted'">
                  <!-- View -->
                  <button
                    @click="viewApplication(application)"
                    class="px-3 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition whitespace-nowrap"
                  >
                    View Details
                  </button>

                  <!-- Approve -->
                  <button
                    v-if="application.final_status?.toLowerCase() === 'pending'"
                    @click="openApprovalModal(application)"
                    class="px-3 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition whitespace-nowrap"
                  >
                    Approve
                  </button>

                  <button
                    v-if="application.final_status?.toLowerCase() === 'pending'"
                    @click="rejectApplication(application.leave_id)"
                    class="px-3 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700 transition whitespace-nowrap"
                  >
                    Reject
                  </button>

                  <!-- Delete -->
                  <button
                    @click="deleteLeaveApplicationById(application.leave_id)"
                    class="px-3 py-2 text-sm bg-gray-600 text-white rounded hover:bg-gray-700 transition whitespace-nowrap"
                  >
                    Delete
                  </button>
                </template>

                <!-- ===================================================== -->
                <!-- REMOVED APPLICATIONS -->
                <!-- ===================================================== -->

                <template v-else>
                  <button
                    @click="restoreLeaveApplicationById(application.leave_id)"
                    class="px-3 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition whitespace-nowrap"
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
            <!-- Pagination Information -->
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

            <!-- Pagination Controls -->
            <div class="flex items-center gap-2 flex-shrink-0">
              <!-- Previous -->
              <button
                @click="previousPage"
                :disabled="currentPage === 1"
                class="px-3 py-2 text-sm rounded-lg border transition"
                :class="
                  currentPage === 1
                    ? 'text-gray-600 bg-slate-800/50 border-slate-800 cursor-not-allowed'
                    : 'text-white bg-slate-800 border-slate-700 hover:bg-slate-700'
                "
              >
                &lt;
              </button>

              <!-- Page Number -->
              <span
                class="min-w-[40px] text-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg"
              >
                {{ currentPage }}
              </span>

              <!-- Next -->
              <button
                @click="nextPage"
                :disabled="currentPage === totalPages"
                class="px-3 py-2 text-sm rounded-lg border transition"
                :class="
                  currentPage === totalPages
                    ? 'text-gray-600 bg-slate-800/50 border-slate-800 cursor-not-allowed'
                    : 'text-white bg-slate-800 border-slate-700 hover:bg-slate-700'
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
          <div class="border border-gray-300 p-4 min-w-0">
            <h4 class="text-sm font-bold mb-3 text-white">
              Applicant Information
            </h4>

            <div
              class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-white"
            >
              <div class="break-words">
                <strong>Office:</strong>
                {{ selectedApplication.employee.department }}
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
          <div class="border border-gray-300 p-4 min-w-0">
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
            class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t"
          >
            <button
              @click="downloadApplication(selectedApplication)"
              class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 whitespace-nowrap"
            >
              Download PDF
            </button>

            <button
              v-if="
                selectedApplication.final_status?.toLowerCase() === 'pending'
              "
              @click="openApprovalModal(selectedApplication)"
              class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 whitespace-nowrap"
            >
              Approve
            </button>
            <button
              v-if="
                selectedApplication.final_status?.toLowerCase() === 'pending'
              "
              @click="rejectApplication(selectedApplication.leave_id)"
              class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 whitespace-nowrap"
            >
              Reject
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ====================================================== -->
  <!-- APPROVAL / DEDUCTION MODAL -->
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
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-black"
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
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-black"
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
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-black"
          />

          <p class="text-xs text-white mt-1">
            Enter the number of days to deduct from Sick Leave.
          </p>
        </div>

        <!-- Total -->
        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
          <div class="flex justify-between text-sm gap-3">
            <span class="text-gray-800"> Days applied: </span>

            <span class="font-medium text-gray-800 text-right">
              {{ approvalApplication?.number_of_days ?? 0 }}
              day(s)
            </span>
          </div>

          <div class="flex justify-between text-sm mt-1 gap-3">
            <span class="text-gray-800"> Total deduction: </span>

            <span class="font-semibold text-gray-800 text-right">
              {{
                vacationDeductDays + sickDeductDays + serviceCreditsDeductDays
              }}

              day(s)
            </span>
          </div>

          <p
            v-if="
              approvalApplication &&
              vacationDeductDays + sickDeductDays + serviceCreditsDeductDays >
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
          @click="showApprovalModal = false"
          class="w-full sm:w-auto px-4 py-2 bg-red-900 text-white rounded-lg hover:bg-red-800 whitespace-nowrap"
        >
          Cancel
        </button>

        <button
          @click="confirmApproval"
          class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 whitespace-nowrap"
        >
          Confirm Approval
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

/* =========================================================
   PAGINATION
========================================================= */

const currentPage = ref(1);

const itemsPerPage = 5;

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
    countClass: "bg-green-100 text-green-800",
  },
];

/* =========================================================
   FILTERED APPLICATIONS
========================================================= */

const filteredApplications = computed(() => {
  const search = searchQuery.value.trim().toLowerCase();

  // =====================================================
  // DELETED APPLICATIONS
  // =====================================================

  if (activeTab.value === "deleted") {
    return deletedApplications.value.filter((app) => {
      const leaveType = app.leave_type?.leave_type_name?.toLowerCase() || "";

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
  }

  // =====================================================
  // NORMAL APPLICATIONS
  // =====================================================

  return applications.value.filter((app) => {
    const status = app.final_status?.toLowerCase() || "";

    const leaveType = app.leave_type?.leave_type_name?.toLowerCase() || "";

    const employeeName = `
        ${app.employee?.first_name || ""}
        ${app.employee?.middle_name || ""}
        ${app.employee?.last_name || ""}
      `.toLowerCase();

    const matchesTab = activeTab.value === "all" || status === activeTab.value;

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
    (app) => app.final_status?.toLowerCase() === tabKey,
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

  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

/* =========================================================
   VIEW APPLICATION
========================================================= */

const viewApplication = (application: LeaveApplication) => {
  router.push(`/leave-print/${application.leave_id}`);
};

/* =========================================================
   DOWNLOAD APPLICATION
========================================================= */

const downloadApplication = (application: LeaveApplication) => {
  router.push(`/leave-print/${application.leave_id}`);
};

/* =========================================================
   OPEN APPROVAL MODAL
========================================================= */

const openApprovalModal = (application: LeaveApplication) => {
  approvalApplication.value = application;

  deductBalance.value = "yes";

  serviceCreditsDeductDays.value = 0;

  vacationDeductDays.value = 0;

  sickDeductDays.value = application.number_of_days;

  showApprovalModal.value = true;
};

/* =========================================================
   UPDATE STATUS
========================================================= */

const updateStatus = async (
  leaveId: number,
  status: "approved" | "disapproved",

  deductionData: {
    deduct_balance?: boolean;

    service_credits_deduct_days?: number;

    vacation_deduct_days?: number;

    sick_deduct_days?: number;
  } = {},
) => {
  try {
    const token = localStorage.getItem("token");

    await axios.put(
      `https://enhs-leave-management-system.onrender.com/api/leave-applications/${leaveId}`,

      {
        final_status: status,

        ...deductionData,
      },

      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
    );

    showDetailModal.value = false;

    showApprovalModal.value = false;

    await loadApplications();
  } catch (error: any) {
    console.error(error);

    alert(
      error.response?.data?.message ?? "Failed to update leave application.",
    );
  }
};

const rejectApplication = async (leaveId: number) => {
  const reason = prompt("Enter reason for disapproval:");

  if (reason === null) {
    return;
  }

  try {
    await rejectLeaveApplication(leaveId, reason);

    alert("Leave application disapproved successfully.");

    showDetailModal.value = false;

    await loadApplications();
  } catch (error: any) {
    console.error("Failed to reject leave application:", error);

    console.error("Response:", error.response?.data);

    alert(
      error.response?.data?.message ?? "Failed to reject leave application.",
    );
  }
};

/* =========================================================
   CONFIRM APPROVAL
========================================================= */

const confirmApproval = async () => {
  if (!approvalApplication.value) {
    return;
  }

  const application = approvalApplication.value;

  if (deductBalance.value === "yes") {
    const serviceCreditsDays = Number(serviceCreditsDeductDays.value) || 0;

    const vacationDays = Number(vacationDeductDays.value) || 0;

    const sickDays = Number(sickDeductDays.value) || 0;

    const totalDeduction = vacationDays + sickDays + serviceCreditsDays;

    if (totalDeduction <= 0) {
      alert("Please enter at least one day to deduct.");

      return;
    }

    if (totalDeduction > application.number_of_days) {
      alert(
        "The total deduction cannot be greater than the number of days applied.",
      );

      return;
    }

    await updateStatus(application.leave_id, "approved", {
      deduct_balance: true,

      service_credits_deduct_days: serviceCreditsDays,

      vacation_deduct_days: vacationDays,

      sick_deduct_days: sickDays,
    });

    return;
  }

  await updateStatus(application.leave_id, "approved", {
    deduct_balance: false,
  });
};

/* =========================================================
   LOAD APPLICATIONS
========================================================= */

const loadApplications = async () => {
  try {
    const token = localStorage.getItem("token");

    const response = await axios.get(
      "https://enhs-leave-management-system.onrender.com/api/leave-applications",
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
    );

    applications.value = response.data;

    /*
     * Reset to page 1 after
     * refreshing the data.
     */
    currentPage.value = 1;
  } catch (error) {
    console.error("Failed to load applications", error);
  }
};

const deleteLeaveApplicationById = async (leaveId: number) => {
  if (!confirm("Are you sure you want to delete this leave application?")) {
    return;
  }

  try {
    await deleteLeaveApplication(leaveId);

    alert("Leave application deleted successfully.");

    await loadApplications();
  } catch (error: any) {
    console.error("Failed to delete leave application", error);

    alert(
      error.response?.data?.message ?? "Failed to delete leave application.",
    );
  }
};

const getDeletedApplications = async () => {
  try {
    const response = await getDeletedLeaveApplications();

    deletedApplications.value = Array.isArray(response) ? response : [];

    console.log("DELETED APPLICATIONS:", deletedApplications.value);
  } catch (error) {
    console.error("Failed to fetch deleted leave applications", error);
  }
};

const restoreLeaveApplicationById = async (leaveId: number) => {
  if (!confirm("Are you sure you want to restore this leave application?")) {
    return;
  }

  try {
    await restoreLeaveApplication(leaveId);

    alert("Leave application restored successfully.");

    await getDeletedApplications();

    await loadApplications();
  } catch (error: any) {
    console.error("Failed to restore leave application", error);

    alert(
      error.response?.data?.message ?? "Failed to restore leave application.",
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
.dashboard-shell {
  background: #080d14;
  min-height: 100vh;
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
}

.neo-card {
  background: #111d2e;
  border: 1px solid #1e293b;
  border-radius: 1.4rem;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;

  min-width: 0;
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

/* =========================================================
   RESPONSIVE / ZOOM BEHAVIOR
========================================================= */

/* Prevent children from forcing horizontal overflow */
.neo-card * {
  min-width: 0;
}

/* Allow long employee names, filenames, departments,
   positions, etc. to wrap instead of breaking layout */
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

/* =========================================================
   PAGINATION
========================================================= */

button:disabled {
  pointer-events: none;
}
</style>
