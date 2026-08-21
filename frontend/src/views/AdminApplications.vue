<template>
  <div class="max-w-7xl mx-auto p-4 dashboard-shell">
    <div class="bg-white rounded-lg shadow neo-card">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-white">
          Submitted Leave Applications
        </h2>
        <p class="text-sm text-white mt-1">
          Review and manage faculty leave applications
        </p>
      </div>

      <!-- Filter Tabs -->
      <div class="px-6 py-3 border-b border-gray-200">
        <div class="flex space-x-4">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
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
      <div class="p-6">
        <!-- Search and Filters -->
        <div class="mb-6">
          <div class="flex flex-col md:flex-row gap-3">
            <!-- Search -->
            <div class="flex-1">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search employee, leave type, status, or ID..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white"
              />
            </div>

            <!-- Leave Type -->
            <select
              v-model="filterType"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-black"
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
              class="px-4 py-2 border border-gray-300 text-white rounded-lg hover:bg-gray-50"
            >
              Clear
            </button>
          </div>
          <!-- Show All / Show Less -->
          <div
            v-if="filteredApplications.length > 5"
            class="flex justify-center mt-6"
          >
            <button
              @click="showAllApplications = !showAllApplications"
              class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
            >
              {{ showAllApplications ? "Show Less" : "Show All Applications" }}
            </button>
          </div>

          <div
            v-if="searchQuery || filterType"
            class="mt-2 text-sm text-gray-500"
          >
            {{ filteredApplications.length }} application(s) found
          </div>
        </div>
        <div v-if="filteredApplications.length === 0" class="text-center py-8">
          <div class="mx-auto h-12 w-12 text-white">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              ></path>
            </svg>
          </div>
          <h3 class="mt-2 text-sm font-medium text-white">
            No applications found
          </h3>
          <p class="mt-1 text-sm text-white">
            No leave applications have been submitted yet.
          </p>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="application in displayedApplications"
            :key="application.leave_id"
            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow neo-card"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-3">
                  <h3 class="text-lg font-medium text-white">
                    {{ application.employee.last_name }},
                    {{ application.employee.first_name
                    }}{{ application.employee.middle_name }}
                  </h3>
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      getStatusClass(application.final_status),
                    ]"
                  >
                    {{ application.final_status }}
                  </span>
                </div>

                <div
                  class="mt-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-white"
                >
                  <div>
                    <span class="font-medium">Office:</span>
                    {{ application.employee.department }}
                  </div>
                  <div>
                    <span class="font-medium">Position:</span>
                    {{ application.employee.position }}
                  </div>
                  <div>
                    <span class="font-medium">Date Filed:</span>
                    {{ formatDate(application.date_filed) }}
                  </div>
                  <div>
                    <span class="font-medium">Leave Type:</span>
                    {{ getLeaveType(application.leave_type) }}
                  </div>
                  <div>
                    <span class="font-medium">Days Applied:</span>
                    {{ application.number_of_days }}
                  </div>
                </div>

                <div
                  v-if="
                    application.attachments &&
                    application.attachments.length > 0
                  "
                  class="mt-3"
                >
                  <span class="text-sm font-medium text-white"
                    >Attachments:</span
                  >
                  <div class="flex flex-wrap gap-2 mt-1">
                    <span
                      v-for="(attachment, index) in application.attachments"
                      :key="index"
                      class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 text-white rounded"
                    >
                      <svg
                        class="w-3 h-3 mr-1"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                        ></path>
                      </svg>
                      {{ attachment.name }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="flex flex-col space-y-2 ml-4">
                <button
                  @click="viewApplication(application)"
                  class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                  View Details
                </button>
                <button
                  @click="downloadApplication(application)"
                  class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                >
                  Download PDF
                </button>
                <button
                  v-if="application.final_status?.toLowerCase() === 'pending'"
                  @click="openApprovalModal(application)"
                  class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                >
                  Approve
                </button>

                <button
                  v-if="application.final_status?.toLowerCase() === 'pending'"
                  @click="updateStatus(application.leave_id, 'rejected')"
                  class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700"
                >
                  Reject
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Application Detail Modal -->
    <div
      v-if="showDetailModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    >
      <div
        class="relative top-10 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto neo-card"
      >
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-medium text-white">
            Leave Application Details
          </h3>
          <button
            @click="showDetailModal = false"
            class="text-white hover:text-gray-600"
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
              ></path>
            </svg>
          </button>
        </div>

        <div v-if="selectedApplication" class="space-y-6">
          <!-- Applicant Information -->
          <div class="border border-gray-300 p-4">
            <h4 class="text-sm font-bold mb-3">Applicant Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <strong>Office:</strong>
                {{ selectedApplication.employee.department }}
              </div>
              <div>
                <strong>Name:</strong>
                {{ selectedApplication.employee.employee_last_name }},
                {{ selectedApplication.employee.employee_first_name }}
                {{ selectedApplication.employee.employee_middle_name }}
              </div>
              <div>
                <strong>Date of Filing:</strong>
                {{ formatDate(selectedApplication.date_filed) }}
              </div>
              <div>
                <strong>Position:</strong>
                {{ selectedApplication.employee.position }}
              </div>
              <div>
                <strong>Salary:</strong>
                {{ selectedApplication.employee.salary }}
              </div>
              <div>
                <strong>Status:</strong>
                <span
                  :class="getStatusClass(selectedApplication.final_status)"
                  >{{ selectedApplication.final_status }}</span
                >
              </div>
            </div>
          </div>

          <!-- Leave Details -->
          <div class="border border-gray-300 p-4">
            <h4 class="text-sm font-bold mb-3">Leave Details</h4>
            <div class="space-y-2 text-sm">
              <div>
                <strong>Type of Leave:</strong>
                {{ getLeaveType(selectedApplication.leave_type) }}
              </div>
              <div>
                <strong>Number of Days:</strong>
                {{ selectedApplication.number_of_days }}
              </div>
              <div>
                <strong>Inclusive Dates:</strong>
                {{ formatDate(selectedApplication.start_date) }}
                -
                {{ formatDate(selectedApplication.end_date) }}
              </div>
              <div>
                <strong>Commutation:</strong>
                {{
                  selectedApplication.commutation
                    ? "Requested"
                    : "Not Requested"
                }}
              </div>
              <div>
                <strong>Applicant Signature:</strong>
                {{ selectedApplication.employee.employee_first_name }}
                {{ selectedApplication.employee.employee_last_name }}
              </div>
            </div>
          </div>

          <!-- Attachments -->
          <div
            v-if="
              selectedApplication.attachments &&
              selectedApplication.attachments.length > 0
            "
            class="border border-gray-300 p-4"
          >
            <h4 class="text-sm font-bold mb-3">Supporting Documents</h4>
            <div class="space-y-2">
              <div
                v-for="(attachment, index) in selectedApplication.attachments"
                :key="index"
                class="flex items-center justify-between p-2 bg-gray-50 rounded"
              >
                <span class="text-sm"
                  >{{ attachment.name }} ({{
                    formatFileSize(attachment.size)
                  }})</span
                >
                <button class="text-blue-600 hover:text-blue-800 text-sm">
                  Download
                </button>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3 pt-4 border-t">
            <button
              @click="downloadApplication(selectedApplication)"
              class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            >
              Download PDF
            </button>
            <button
              v-if="selectedApplication.final_status === 'Pending'"
              @click="openApprovalModal(selectedApplication)"
              class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            >
              Approve
            </button>
            <button
              v-if="selectedApplication.final_status === 'Pending'"
              @click="updateStatus(selectedApplication.leave_id, 'rejected')"
              class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
            >
              Reject
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Approval / Deduction Modal -->
  <div
    v-if="showApprovalModal"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-[60]"
  >
    <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-md p-6 neo-card">
      <h3 class="text-lg font-semibold text-white">
        Approve Leave Application
      </h3>

      <p class="text-sm text-white mt-2">
        This leave application is for
        <strong>{{ approvalApplication?.number_of_days }}</strong>
        day(s).
      </p>

      <!-- Deduct? -->
      <div class="mt-5">
        <label class="block text-sm font-medium text-white mb-2">
          Deduct leave balance?
        </label>

        <div class="flex gap-4">
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
        <div>
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
          <div class="flex justify-between text-sm">
            <span class="text-white"> Days applied: </span>

            <span class="font-medium text-white">
              {{ approvalApplication?.number_of_days ?? 0 }} day(s)
            </span>
          </div>

          <div class="flex justify-between text-sm mt-1">
            <span class="text-white"> Total deduction: </span>

            <span class="font-semibold text-white">
              {{
                vacationDeductDays + sickDeductDays + serviceCreditsDeductDays
              }}
              day(s)
            </span>
          </div>

          <p
            v-if="
              approvalApplication &&
              vacationDeductDays + sickDeductDays >
                approvalApplication.number_of_days
            "
            class="text-sm text-red-600 mt-2"
          >
            Total deduction cannot exceed the number of days applied.
          </p>
        </div>
      </div>

      <!-- Buttons -->
      <div class="flex justify-end gap-3 mt-6">
        <button
          @click="showApprovalModal = false"
          class="px-4 py-2 bg-red-900 text-white rounded-lg hover:bg-gray-300"
        >
          Cancel
        </button>

        <button
          @click="confirmApproval"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
        >
          Confirm Approval
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import jsPDF from "jspdf";
import html2canvas from "html2canvas";

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
const applications = ref<LeaveApplication[]>([]);
const activeTab = ref("all");
const showDetailModal = ref(false);
const selectedApplication = ref<LeaveApplication | null>(null);

const showApprovalModal = ref(false);

const approvalApplication = ref<LeaveApplication | null>(null);

const deductBalance = ref<"yes" | "no">("yes");

const vacationDeductDays = ref(0);
const sickDeductDays = ref(0);
const serviceCreditsDeductDays = ref(0);
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
  { key: "rejected", label: "Rejected", countClass: "bg-red-100 text-red-800" },
];

const searchQuery = ref("");
const filterType = ref("");
const showAllApplications = ref(false);

const filteredApplications = computed(() => {
  const search = searchQuery.value.trim().toLowerCase();

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

const displayedApplications = computed(() => {
  if (showAllApplications.value) {
    return filteredApplications.value;
  }

  return filteredApplications.value.slice(0, 5);
});

const getTabCount = (tabKey: string) => {
  if (tabKey === "all") {
    return applications.value.length;
  }

  return applications.value.filter(
    (app) => app.final_status.toLowerCase() === tabKey,
  ).length;
};

const getStatusClass = (status: string) => {
  switch (status.toLowerCase()) {
    case "pending":
      return "bg-yellow-100 text-yellow-800";

    case "approved":
      return "bg-green-100 text-green-800";

    case "rejected":
      return "bg-red-100 text-red-800";

    default:
      return "bg-gray-100 text-gray-800";
  }
};

const getLeaveType = (leaveType: any) => {
  return leaveType?.leave_type_name ?? "Not specified";
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const formatFileSize = (bytes: number) => {
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const viewApplication = (application: LeaveApplication) => {
  router.push(`/leave-print/${application.leave_id}`);
};

const downloadApplication = (application: LeaveApplication) => {
  router.push(`/leave-print/${application.leave_id}`);
};

const openApprovalModal = (application: LeaveApplication) => {
  approvalApplication.value = application;

  deductBalance.value = "yes";

  serviceCreditsDeductDays.value = 0;

  vacationDeductDays.value = 0;
  sickDeductDays.value = application.number_of_days;

  showApprovalModal.value = true;
};

const updateStatus = async (
  leaveId: number,
  status: "approved" | "rejected",
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
      `http://127.0.0.1:8000/api/leave-applications/${leaveId}`,
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

const loadApplications = async () => {
  try {
    const token = localStorage.getItem("token");

    const response = await axios.get(
      "http://127.0.0.1:8000/api/leave-applications",
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
    );

    applications.value = response.data;
  } catch (error) {
    console.error("Failed to load applications", error);
  }
};

onMounted(() => {
  loadApplications();
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