<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
      <div
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
      >
        <div>
          <h2 class="text-xl font-semibold text-gray-800">
            My Leave Applications
          </h2>
          <p class="text-sm text-gray-600 mt-1">
            Track and manage your leave application status
          </p>
        </div>

        <div class="flex gap-2">
          <button
            @click="exportApplications"
            class="px-4 py-2 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700"
          >
            Export
          </button>
          <button
            @click="$router.push('/leave-application')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            New Application
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-blue-100 rounded-full">
            <FileText class="w-6 h-6 text-blue-600" />
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">
              Total Applications
            </h3>
            <p class="text-2xl font-semibold text-gray-900">
              {{ stats.total }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-green-100 rounded-full">
            <CheckCircle class="w-6 h-6 text-green-600" />
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Approved</h3>
            <p class="text-2xl font-semibold text-gray-900">
              {{ stats.approved }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-yellow-100 rounded-full">
            <Clock class="w-6 h-6 text-yellow-600" />
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Pending</h3>
            <p class="text-2xl font-semibold text-gray-900">
              {{ stats.pending }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-red-100 rounded-full">
            <XCircle class="w-6 h-6 text-red-600" />
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">Rejected</h3>
            <p class="text-2xl font-semibold text-gray-900">
              {{ stats.rejected }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter and Search -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
          <div class="relative">
            <Search
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5"
            />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by leave type, date range..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <div class="flex gap-2">
          <select
            v-model="filterStatus"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="on pr">On PR</option>
          </select>

          <select
            v-model="filterType"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Types</option>
            <option value="vacation">Vacation Leave</option>
            <option value="sick">Sick Leave</option>
            <option value="maternity">Maternity Leave</option>
            <option value="paternity">Paternity Leave</option>
            <option value="personal">Personal Leave</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Applications List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Application History</h3>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Application ID
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Leave Type
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Duration
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Filed Date
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Status
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="application in filteredApplications"
              :key="application.leave_id"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                #{{ application.leave_id }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ application.leave_type.leave_type_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{
                  formatDateRange(application.start_date, application.end_date)
                }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(application.date_filed) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-3 py-1 text-xs font-semibold rounded-full"
                  :class="getStatusClass(application.final_status)"
                >
                  {{ application.final_status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button
                  @click="viewDetails(application)"
                  class="text-blue-600 hover:text-blue-900 mr-3"
                >
                  View Details
                </button>
                <button
                  v-if="application.status === 'Approved'"
                  @click="downloadForm(application)"
                  class="text-green-600 hover:text-green-900"
                >
                  Download
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Application Details Modal -->
    <div
      v-if="application"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    >
      <div
        class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-lg bg-white"
      >
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-semibold text-gray-900">
            Application Details - #{{ application.leave_id }}
          </h3>
          <button
            @click="application = null"
            class="text-gray-400 hover:text-gray-600"
          >
            <X class="w-6 h-6" />
          </button>
        </div>

        <div class="space-y-6">
          <!-- Application Information -->
          <div class="bg-gray-50 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">
              Application Information
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-500">Leave Type</p>
                <p class="text-sm font-medium text-gray-900">
                  {{ application.leave_type.leave_type_name }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Duration</p>
                <p class="text-sm font-medium text-gray-900">
                  {{
                    formatDateRange(
                      application.start_date,
                      application.end_date,
                    )
                  }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Filed Date</p>
                <p class="text-sm font-medium text-gray-900">
                  {{ formatDate(application.date_filed) }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Status</p>
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="getStatusClass(application.final_status)"
                >
                  {{ application.final_status }}
                </span>
              </div>
            </div>
          </div>

          <!-- Supporting Documents -->
          <div
            v-if="application.attachments && application.attachments.length > 0"
            class="bg-gray-50 rounded-lg p-6"
          >
            <h4 class="text-lg font-semibold text-gray-800 mb-4">
              Supporting Documents
            </h4>
            <div class="space-y-2">
              <div
                v-for="(file, index) in application.attachments"
                :key="index"
                class="flex items-center justify-between p-2 bg-white rounded"
              >
                <span class="text-sm text-gray-700">{{ file.name }}</span>
                <button class="text-blue-600 hover:text-blue-800 text-sm">
                  Download
                </button>
              </div>
            </div>
          </div>

          <!-- Status Timeline -->
          <div class="bg-gray-50 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">
              Status Timeline
            </h4>
            <div class="space-y-3">
              <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium text-gray-900">
                    Application Submitted
                  </p>
                  <p class="text-xs text-gray-500">
                    {{ formatDate(application.date_filed) }}
                  </p>
                </div>
              </div>
              <div
                v-if="application.final_status !== 'pending'"
                class="flex items-center"
              >
                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium text-gray-900">Under Review</p>
                  <p class="text-xs text-gray-500">
                    {{
                      formatDate(
                        application.reviewDate || application.date_filed,
                      )
                    }}
                  </p>
                </div>
              </div>
              <div
                v-if="application.final_status === 'approved'"
                class="flex items-center"
              >
                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium text-gray-900">
                    Application Approved
                  </p>
                  <p class="text-xs text-gray-500">
                    {{
                      formatDate(
                        application.approvedDate || application.date_filed,
                      )
                    }}
                  </p>
                </div>
              </div>
              <div
                v-if="application.final_status === 'rejected'"
                class="flex items-center"
              >
                <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium text-gray-900">
                    Application Rejected
                  </p>
                  <p class="text-xs text-gray-500">
                    {{
                      formatDate(
                        application.rejectedDate || application.date_filed,
                      )
                    }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { getMyLeaves } from "../services/leave";
import { useRouter } from "vue-router";
import {
  Search,
  FileText,
  CheckCircle,
  Clock,
  XCircle,
  X,
} from "lucide-vue-next";

interface Application {
  leave_id: string;
  leave_type: {
    leave_type_name: string;
  };
  start_date: string;
  end_date: string;
  date_filed: string;
  final_status: "pending" | "approved" | "rejected" | "on pr";
  attachments?: Array<{
    name: string;
    size: number;
  }>;
  reviewDate?: string;
  approvedDate?: string;
  rejectedDate?: string;
}
const router = useRouter();
const searchQuery = ref("");
const filterStatus = ref("");
const filterType = ref("");
const application = ref<Application | null>(null);

// Mock data for faculty applications
const applications = ref<any[]>([]);
const loading = ref(true);

const loadApplications = async () => {
  try {
    applications.value = await getMyLeaves();
  } catch (error) {
    console.error("Failed loading applications", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadApplications();
});

const stats = computed(() => ({
  total: applications.value.length,
  approved: applications.value.filter((app) => app.final_status === "approved")
    .length,
  pending: applications.value.filter((app) => app.final_status === "pending")
    .length,
  rejected: applications.value.filter((app) => app.final_status === "rejected")
    .length,
}));

const filteredApplications = computed(() => {
  return applications.value.filter((application) => {
    const matchesSearch =
      searchQuery.value === "" ||
      application.leave_type.leave_type_name
        .toLowerCase()
        .includes(searchQuery.value.toLowerCase()) ||
      application.leave_id
        .toLowerCase()
        .includes(searchQuery.value.toLowerCase());

    const matchesStatus =
      filterStatus.value === "" ||
      application.final_status === filterStatus.value;
    const matchesType =
      filterType.value === "" ||
      application.leave_type.leave_type_name
        .toLowerCase()
        .includes(filterType.value.toLowerCase());

    return matchesSearch && matchesStatus && matchesType;
  });
});

const getStatusClass = (status: string) => {
  switch (status) {
    case "approved":
      return "bg-green-100 text-green-800"

    case "pending":
      return "bg-yellow-100 text-yellow-800"

    case "rejected":
      return "bg-red-100 text-red-800"

    case "on pr":
      return "bg-blue-100 text-blue-800"

    default:
      return "bg-gray-100 text-gray-800"
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

const formatDateRange = (startDate: string, endDate: string) => {
  const start = new Date(startDate).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
  });
  const end = new Date(endDate).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
  return `${start} - ${end}`;
};

const viewDetails = (app: Application) => {
  application.value = app;
};

const downloadForm = (application: Application) => {
  // Download form functionality
  console.log("Downloading form for application:", application.leave_id);
};

const exportApplications = () => {
  // Export applications functionality
  console.log("Exporting applications...");
};
</script>
