<template>
  <div class="dashboard-shell min-h-screen p-6 space-y-6">

   

<!-- =============================== -->
<!-- FILTER AND SEARCH -->
<!-- =============================== -->

<div class="neo-card p-5">
  <div class="flex flex-col lg:flex-row gap-4">

    <!-- Search Input -->
    <div class="flex-1">
      <div class="relative">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 w-5 h-5 pointer-events-none z-10" />
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search ID, leave type, status, date..."
          class="dark-input w-full !pl-10 py-2" 
        />
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-3">
      <select v-model="filterStatus" class="dark-input sm:w-44">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="disapproved">Disapproved</option>
      </select>

      <select v-model="filterType" class="dark-input sm:w-48">
        <option value="">All Types</option>
        <option value="vacation">Vacation Leave</option>
        <option value="sick">Sick Leave</option>
        <option value="study">Study Leave</option>
        <option value="special">Special Leave</option>
        <option value="mandatory">Mandatory/Forced Leave</option>
      </select>

      <button 
        @click="clearFilters"
        class="px-4 py-2 text-sm border border-slate-700 text-slate-300 rounded-lg hover:bg-slate-800 transition"
      >
        Clear
      </button>
    </div>

  </div>
</div>

<!-- Search Result Counter -->
<div v-if="searchQuery || filterStatus || filterType" class="mt-3 text-sm text-slate-500">
  {{ filteredApplications.length }} application(s) found
</div>


  <!-- =============================== -->
  <!-- APPLICATION HISTORY -->
  <!-- =============================== -->

  <div class="neo-card overflow-hidden">

    <div
      class="px-6 py-5 border-b border-slate-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">

      <div>
        <h3 class="text-lg font-semibold text-white">
          Application History
        </h3>

        <p class="text-sm text-slate-400 mt-1">
          Showing {{ displayedApplications.length }} of
          {{ filteredApplications.length }} application(s)
        </p>
      </div>

      <button v-if="applications.length > 15" @click="showAllApplications = !showAllApplications"
        class="px-4 py-2 text-sm border border-blue-500 text-blue-400 rounded-lg hover:bg-blue-500/10 transition">
        {{
          showAllApplications
            ? "Show Recent 15"
            : `Show All Applications (${filteredApplications.length})`
        }}
      </button>

    </div>


    <div class="overflow-x-auto">

      <table class="w-full">

        <!-- Table Header -->
        <thead class="bg-[#0D1725]">

          <tr>

            <th class="table-header">
              Leave Type
            </th>

            <th class="table-header">
              Duration
            </th>

            <th class="table-header">
              Filed Date
            </th>

            <th class="table-header">
              Status
            </th>

            <th class="table-header">
              Actions
            </th>

          </tr>

        </thead>


        <!-- Table Body -->
        <tbody class="divide-y divide-slate-800">

          <tr v-for="application in displayedApplications" :key="application.leave_id"
            class="hover:bg-slate-800/40 transition">

            


            <!-- Leave Type -->
            <td class="table-cell text-slate-300">
              {{ application.leave_type.leave_type_name }}
            </td>


            <!-- Duration -->
            <td class="table-cell text-slate-300">
              {{
                formatDateRange(
                  application.start_date,
                  application.end_date
                )
              }}
            </td>


            <!-- Filed -->
            <td class="table-cell text-slate-300">
              {{ formatDate(application.date_filed) }}
            </td>


            <!-- Status -->
            <td class="table-cell">

              <span class="px-3 py-1.5 text-xs font-semibold rounded-full"
                :class="getStatusClass(application.final_status)">
                {{ application.final_status }}
              </span>

            </td>


            <!-- Actions -->
            <td class="table-cell">

              <div class="flex items-center gap-3">

                <button @click="viewDetails(application)"
                  class="btn-action bg-blue-600 hover:bg-blue-700">
                  View Details
                </button>

                <button @click="viewForm(application)"
                  class="btn-action bg-yellow-600 hover:bg-yellow-700">
                  View Form
                </button>

                <button v-if="application.status === 'Approved'" @click="downloadForm(application)"
                  class="text-green-400 hover:text-green-300 font-medium transition">
                  Download
                </button>

              </div>

            </td>

          </tr>


          <!-- Empty State -->
          <tr v-if="displayedApplications.length === 0">

            <td colspan="6" class="px-6 py-12 text-center">

              <FileText class="w-10 h-10 mx-auto text-slate-600 mb-3" />

              <p class="text-slate-400">
                No leave applications found.
              </p>

            </td>

          </tr>

        </tbody>

      </table>

    </div>

  </div>


  <!-- =============================== -->
  <!-- APPLICATION DETAILS MODAL -->
  <!-- =============================== -->

  <div v-if="application"
    class="fixed inset-0 bg-black/70 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-6">

    <div class="relative w-full max-w-3xl shadow-2xl rounded-2xl bg-[#111D2E] border border-slate-700">

      <!-- Modal Header -->
      <div class="flex justify-between items-center px-6 py-5 border-b border-slate-700">

        <div>

          <h3 class="text-xl font-semibold text-white">
            Application Details
          </h3>

          <p class="text-sm text-slate-400 mt-1">
            Application #{{ application.leave_id }}
          </p>

        </div>


        <button @click="application = null" class="text-slate-500 hover:text-white transition">
          <X class="w-6 h-6" />
        </button>

      </div>


      <!-- Modal Body -->
      <div class="p-6 space-y-6">


        <!-- Application Information -->
        <div class="modal-section">

          <h4 class="modal-title">
            Application Information
          </h4>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div>
              <p class="modal-label">
                Leave Type
              </p>

              <p class="modal-value">
                {{ application.leave_type.leave_type_name }}
              </p>
            </div>


            <div>
              <p class="modal-label">
                Duration
              </p>

              <p class="modal-value">
                {{
                  formatDateRange(
                    application.start_date,
                    application.end_date
                  )
                }}
              </p>
            </div>


            <div>
              <p class="modal-label">
                Filed Date
              </p>

              <p class="modal-value">
                {{ formatDate(application.date_filed) }}
              </p>
            </div>


            <div>
              <p class="modal-label">
                Status
              </p>

              <span class="px-3 py-1.5 text-xs font-semibold rounded-full inline-block"
                :class="getStatusClass(application.final_status)">
                {{ application.final_status }}
              </span>
            </div>

          </div>

        </div>


        <!-- Supporting Documents -->
        <div v-if="
          application.attachments &&
          application.attachments.length > 0
        " class="modal-section">

          <h4 class="modal-title">
            Supporting Documents
          </h4>

          <div class="space-y-2">

            <div v-for="(file, index) in application.attachments" :key="index"
              class="flex items-center justify-between p-3 bg-[#0F1A2A] border border-slate-700 rounded-lg">

              <div class="flex items-center min-w-0">

                <FileText class="w-5 h-5 text-slate-500 mr-3 flex-shrink-0" />

                <span class="text-sm text-slate-300 truncate">
                  {{ file.name }}
                </span>

              </div>


              <button class="text-blue-400 hover:text-blue-300 text-sm ml-4">
                Download
              </button>

            </div>

          </div>

        </div>


        <!-- Status Timeline -->
        <div class="modal-section">

          <h4 class="modal-title">
            Status Timeline
          </h4>


          <div class="space-y-5">

            <!-- Submitted -->
            <div class="flex items-start">

              <div class="w-3 h-3 bg-blue-500 rounded-full mt-1.5 mr-3 flex-shrink-0"></div>

              <div>

                <p class="text-sm font-medium text-white">
                  Application Submitted
                </p>

                <p class="text-xs text-slate-500 mt-1">
                  {{ formatDate(application.date_filed) }}
                </p>

              </div>

            </div>


            <!-- Review -->
            <div v-if="application.final_status !== 'pending'" class="flex items-start">

              <div class="w-3 h-3 bg-yellow-500 rounded-full mt-1.5 mr-3 flex-shrink-0"></div>

              <div>

                <p class="text-sm font-medium text-white">
                  Under Review
                </p>

                <p class="text-xs text-slate-500 mt-1">
                  {{
                    formatDate(
                      application.reviewDate ||
                      application.date_filed
                    )
                  }}
                </p>

              </div>

            </div>


            <!-- Approved -->
            <div v-if="application.final_status === 'approved'" class="flex items-start">

              <div class="w-3 h-3 bg-green-500 rounded-full mt-1.5 mr-3 flex-shrink-0"></div>

              <div>

                <p class="text-sm font-medium text-white">
                  Application Approved
                </p>

                <p class="text-xs text-slate-500 mt-1">
                  {{
                    formatDate(
                      application.approvedDate ||
                      application.date_filed
                    )
                  }}
                </p>

              </div>

            </div>


            <!-- Disapproved -->
            <div v-if="application.final_status === 'disapproved'" class="flex items-start">

              <div class="w-3 h-3 bg-red-500 rounded-full mt-1.5 mr-3 flex-shrink-0"></div>

              <div>

                <p class="text-sm font-medium text-white">
                  Application Disapproved
                </p>

                <p class="text-xs text-slate-500 mt-1">
                  {{
                    formatDate(
                      application.rejectedDate ||
                      application.date_filed
                    )
                  }}
                </p>

              </div>

            </div>

          </div>

        </div>

      </div>


      <!-- Modal Footer -->
      <div class="px-6 py-4 border-t border-slate-700 flex justify-end">

        <button @click="application = null"
          class="px-5 py-2.5 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition text-sm">
          Close
        </button>

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

  final_status:
  | "pending"
  | "approved"
  | "disapproved"
  | "on pr";

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

const showAllApplications = ref(false);

const application =
  ref<Application | null>(null);


const applications =
  ref<any[]>([]);

const loading = ref(true);

const viewForm = (application: any) => {
  console.log("EMPLOYEE VIEW FORM:", {
    leave_id: application.leave_id,
    leave_type: application.leave_type?.leave_type_name,
    start_date: application.start_date,
    end_date: application.end_date,
    date_filed: application.date_filed,
  });

  router.push(`/leave-print/${application.leave_id}`);
};

const loadApplications = async () => {

  try {

    applications.value =
      await getMyLeaves();

  } catch (error) {

    console.error(
      "Failed loading applications",
      error
    );

  } finally {

    loading.value = false;

  }

};


onMounted(() => {
  loadApplications();
});


const stats = computed(() => ({

  total:
    applications.value.length,

  approved:
    applications.value.filter(
      (app) =>
        app.final_status === "approved"
    ).length,

  pending:
    applications.value.filter(
      (app) =>
        app.final_status === "pending"
    ).length,

  disapproved:
    applications.value.filter(
      (app) =>
        app.final_status === "disapproved"
    ).length,

}));


const filteredApplications = computed(() => {

  const search = searchQuery.value.trim().toLowerCase();

  return applications.value
    .filter((app) => {

      const leaveType =
        app.leave_type?.leave_type_name?.toLowerCase() || "";

      const status =
        app.final_status?.toLowerCase() || "";

      const leaveId =
        String(app.leave_id || "").toLowerCase();

      const reason =
        app.reason?.toLowerCase() || "";

      const dateFiled =
        app.date_filed?.toLowerCase() || "";

      const startDate =
        app.start_date?.toLowerCase() || "";

      const endDate =
        app.end_date?.toLowerCase() || "";

      const vacationLocation =
        app.vacation_location?.toLowerCase() || "";

      const matchesSearch =
        search === "" ||
        leaveId.includes(search) ||
        leaveType.includes(search) ||
        status.includes(search) ||
        reason.includes(search) ||
        dateFiled.includes(search) ||
        startDate.includes(search) ||
        endDate.includes(search) ||
        vacationLocation.includes(search);

      const matchesStatus =
        filterStatus.value === "" ||
        status === filterStatus.value.toLowerCase();

      const matchesType =
        filterType.value === "" ||
        leaveType.includes(filterType.value.toLowerCase());

      return matchesSearch && matchesStatus && matchesType;

    })
    .sort((a, b) => {
      return (
        new Date(b.date_filed).getTime() -
        new Date(a.date_filed).getTime()
      );
    });

});


const displayedApplications = computed(() => {

  if (showAllApplications.value) {
    return filteredApplications.value;
  }

  return filteredApplications.value.slice(0, 15);

});


const clearFilters = () => {
  searchQuery.value = "";
  filterStatus.value = "";
  filterType.value = "";
};


const getStatusClass = (
  status: string
) => {

  switch (status) {

    case "approved":
      return "status-approved";

    case "pending":
      return "status-pending";

    case "disapproved":
      return "status-disapproved";

    case "on pr":
      return "status-pr";

    default:
      return "status-default";

  }

};


const formatDate = (
  dateString: string
) => {

  return new Date(
    dateString
  ).toLocaleDateString(
    "en-US",
    {
      year: "numeric",
      month: "short",
      day: "numeric",
    }
  );

};


const formatDateRange = (
  startDate: string,
  endDate: string
) => {

  const start =
    new Date(
      startDate
    ).toLocaleDateString(
      "en-US",
      {
        month: "short",
        day: "numeric",
      }
    );


  const end =
    new Date(
      endDate
    ).toLocaleDateString(
      "en-US",
      {
        month: "short",
        day: "numeric",
        year: "numeric",
      }
    );


  return `${start} - ${end}`;

};


const viewDetails = (
  app: Application
) => {

  application.value = app;

};


const downloadForm = (
  application: Application
) => {

  console.log(
    "Downloading form for application:",
    application.leave_id
  );

};

</script>


<style scoped>
/* =========================================
   DASHBOARD BACKGROUND
========================================= */

.dashboard-shell {
  background: #080D14;
}


/* =========================================
   MAIN CARD
========================================= */

.neo-card {
  background: #111D2E;

  border: 1px solid #1E293B;

  border-radius: 1.4rem;

  box-shadow:
    0 10px 22px rgba(15, 23, 42, 0.18);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;
}

.neo-card:hover {
  box-shadow:
    0 14px 26px rgba(15, 23, 42, 0.28);
}


/* =========================================
   STATISTICS CARDS
========================================= */

.stats-card {
  border-left: 4px solid currentColor;
}

.stats-card .p-3 {
  border-radius: 0.9rem;
}


/* =========================================
   INPUTS
========================================= */

.dark-input {
  border: 1px solid #334155;

  border-radius: 0.65rem;

  padding: 0.65rem 0.85rem;

  background: #0f1a2a;

  color: #f8fafc;

  font-size: 0.875rem;

  outline: none;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    background 0.2s ease;
}

.dark-input:focus {
  border-color: #3b82f6;

  box-shadow:
    0 0 0 2px rgba(59, 130, 246, 0.15);
}

.dark-input::placeholder {
  color: #64748b;
}

.dark-input option {
  background: #111D2E;

  color: #f8fafc;
}

.btn-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.375rem 0.85rem;
  font-size: 0.75rem;
  font-weight: 500;
  line-height: 1.25rem;
  color: #fff;
  border-radius: 9999px;
  white-space: nowrap;
  transition: background-color 0.15s ease, transform 0.1s ease;
}

.btn-action:active {
  transform: scale(0.97);
}
/* =========================================
   TABLE
========================================= */

.table-header {
  padding: 0.9rem 1.5rem;

  text-align: left;

  color: #64748b;

  font-size: 0.7rem;

  font-weight: 600;

  text-transform: uppercase;

  letter-spacing: 0.05em;

  white-space: nowrap;
}


.table-cell {
  padding: 1rem 1.5rem;

  white-space: nowrap;

  font-size: 0.875rem;
}


/* =========================================
   STATUS BADGES
========================================= */

.status-approved {
  background: rgba(34, 197, 94, 0.12);

  color: #4ade80;

  border: 1px solid rgba(34, 197, 94, 0.2);
}


.status-pending {
  background: rgba(234, 179, 8, 0.12);

  color: #facc15;

  border: 1px solid rgba(234, 179, 8, 0.2);
}


.status-disapproved {
  background: rgba(239, 68, 68, 0.12);

  color: #f87171;

  border: 1px solid rgba(239, 68, 68, 0.2);
}


.status-pr {
  background: rgba(59, 130, 246, 0.12);

  color: #60a5fa;

  border: 1px solid rgba(59, 130, 246, 0.2);
}


.status-default {
  background: rgba(100, 116, 139, 0.12);

  color: #94a3b8;

  border: 1px solid rgba(100, 116, 139, 0.2);
}


/* =========================================
   MODAL SECTIONS
========================================= */

.modal-section {
  background: #0D1827;

  border: 1px solid #263548;

  border-radius: 1rem;

  padding: 1.25rem;
}


.modal-title {
  color: #f8fafc;

  font-size: 1rem;

  font-weight: 600;

  margin-bottom: 1.25rem;
}


.modal-label {
  color: #64748b;

  font-size: 0.75rem;

  margin-bottom: 0.3rem;
}


.modal-value {
  color: #e2e8f0;

  font-size: 0.875rem;

  font-weight: 500;
}


/* =========================================
   GENERAL TYPOGRAPHY
========================================= */

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {
  letter-spacing: -0.01em;
}


/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 640px) {

  .table-header {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .table-cell {
    padding-left: 1rem;
    padding-right: 1rem;
  }

}
</style>