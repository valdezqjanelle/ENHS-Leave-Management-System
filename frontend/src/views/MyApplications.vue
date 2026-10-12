<template>
  <div class="dashboard-shell min-h-screen p-6 space-y-6">

   

<!-- =============================== -->
<!-- FILTER AND SEARCH -->
<!-- =============================== -->

<div class="p-5 rounded-lg border" style="border-color: var(--border); background: var(--surface);">
  <div class="flex flex-col lg:flex-row gap-4">

    <!-- Search Input -->
    <div class="flex-1">
      <div class="relative">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 pointer-events-none z-10" style="color: var(--text-muted);" />
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search ID, leave type, status, date..."
          class="w-full !pl-10 py-2 border rounded-lg"
          style="border-color: var(--border); background: var(--surface); color: var(--text);"
        />
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-3">
      <select v-model="filterStatus" class="sm:w-44 border rounded-lg" style="border-color: var(--border); background: var(--surface); color: var(--text);">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="disapproved">Disapproved</option>
      </select>

      <select v-model="filterType" class="sm:w-48 border rounded-lg" style="border-color: var(--border); background: var(--surface); color: var(--text);">
        <option value="">All Types</option>
        <option value="vacation">Vacation Leave</option>
        <option value="sick">Sick Leave</option>
        <option value="study">Study Leave</option>
        <option value="special">Special Leave</option>
        <option value="mandatory">Mandatory/Forced Leave</option>
      </select>

      <button 
        @click="clearFilters"
        class="px-4 py-2 text-sm border rounded-lg transition"
        style="border-color: var(--border); color: var(--text);"
      >
        Clear
      </button>
    </div>

  </div>
</div>

<!-- Search Result Counter -->
<div v-if="searchQuery || filterStatus || filterType" class="mt-3 text-sm" style="color: var(--text-muted);">
  {{ filteredApplications.length }} application(s) found
</div>


  <!-- =============================== -->
  <!-- APPLICATION HISTORY -->
  <!-- =============================== -->

  <div class="overflow-hidden rounded-lg border" style="border-color: var(--border); background: var(--surface);">

    <div
      class="px-6 py-5 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3"
      style="border-color: var(--border);"
    >

      <div>
        <h3 class="text-lg font-semibold" style="color: var(--text);">
          Application History
        </h3>

        <p class="text-sm mt-1" style="color: var(--text-muted);">
          Showing {{ displayedApplications.length }} of
          {{ filteredApplications.length }} application(s)
        </p>
      </div>

      <button v-if="applications.length > 15" @click="showAllApplications = !showAllApplications"
        class="px-4 py-2 text-sm border rounded-lg transition"
        style="border-color: var(--primary); color: var(--primary);"
      >
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
        <thead class="text-left font-semibold" style="background: var(--surface-muted); color: var(--text);">

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
        <tbody class="divide-y" style="border-color: var(--border);">

          <tr v-for="application in displayedApplications" :key="application.leave_id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">

            


            <!-- Leave Type -->
            <td class="table-cell" style="color: var(--text);">
              {{ application.leave_type.leave_type_name }}
            </td>


            <!-- Duration -->
            <td class="table-cell" style="color: var(--text);">
              {{
                formatDateRange(
                  application.start_date,
                  application.end_date
                )
              }}
            </td>


            <!-- Filed -->
            <td class="table-cell" style="color: var(--text);">
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
                  class="btn-action" style="background: var(--primary);">
                  View Details
                </button>

                <button @click="viewForm(application)"
                  class="btn-action" style="background: var(--warning);">
                  View Form
                </button>

              </div>

            </td>

          </tr>


          <!-- Empty State -->
          <tr v-if="displayedApplications.length === 0">

            <td colspan="6" class="px-6 py-12 text-center">

              <FileText class="w-10 h-10 mx-auto mb-3" style="color: var(--text-muted);" />

              <p style="color: var(--text-muted);">
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


        <button :disabled="savingEdit" @click="application = null" class="text-slate-500 hover:text-white transition">
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


        <div class="modal-section">
          <div class="flex justify-between items-center gap-3 mb-4">
            <h4 class="modal-title">Application Details</h4>
            <button v-if="canEditApplication && !editingApplication" type="button" class="btn-action" style="background: var(--primary);" @click="startApplicationEdit">Edit Application</button>
          </div>
          <p v-if="!canEditApplication" class="text-sm text-[var(--text-muted)] mb-3">Processed applications are read-only.</p>
          <dl v-if="!editingApplication" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="row in leaveDetailRows(application)" :key="row.label + row.value">
              <dt class="modal-label">{{ row.label }}</dt><dd class="modal-value whitespace-pre-line">{{ row.value }}</dd>
            </div>
          </dl>
          <form v-else class="application-edit-form" @submit.prevent="saveApplicationEdit">
            <fieldset :disabled="savingEdit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label>Leave Type<select v-model="editForm.leave_type_id" required><option v-for="type in editTypes" :key="type.leave_type_id" :value="type.leave_type_id">{{ type.leave_type_name }}</option></select></label>
              <label>Commutation<select v-model="editForm.commutation"><option value="">Not specified</option><option value="requested">Requested</option><option value="not requested">Not requested</option></select></label>
              <label>Start Date<input v-model="editForm.start_date" type="date" required /></label>
              <label>End Date<input v-model="editForm.end_date" type="date" :min="editForm.start_date" required /></label>
              <p class="md:col-span-2 text-sm text-[var(--text-muted)]">Working days are recalculated by the system when saved, excluding weekends and holidays.</p>
              <template v-if="editIsVacation">
                <label>Vacation Location Type<select v-model="editForm.vacation_location_type" required><option value="">Select</option><option value="within_philippines">Within Philippines</option><option value="abroad">Abroad</option></select></label>
                <label>Location<input v-model="editForm.vacation_location" required maxlength="500" /></label>
              </template>
              <template v-if="editIsSick">
                <label>Treatment<select v-model="editForm.sick_type" required><option value="">Select</option><option value="in_hospital">In Hospital</option><option value="out_patient">Out Patient</option></select></label>
                <label>Illness<input v-model="editForm.illness" required maxlength="1000" /></label>
              </template>
              <template v-if="editIsStudy">
                <label><input v-model="editForm.masters_degree" type="checkbox" /> Completion of master’s degree</label>
                <label><input v-model="editForm.board_exam_review" type="checkbox" /> Board examination review</label>
              </template>
              <label><input v-model="editForm.monetization" type="checkbox" /> Monetization</label>
              <label><input v-model="editForm.terminal_leave" type="checkbox" /> Terminal leave</label>
              <label class="md:col-span-2">Other Purpose<input v-model="editForm.other_purpose" maxlength="1000" /></label>
              <label class="md:col-span-2">Reason<textarea v-model="editForm.reason" rows="3" required maxlength="5000"></textarea></label>
              <div class="md:col-span-2">
                <p v-if="requirementsLoading">Loading document requirements…</p>
                <label v-for="requirement in editRequirements" :key="requirement.id" class="block mb-3">
                  {{ requirement.document_name }} <span v-if="hasExistingRequirement(requirement.id)">(already attached)</span>
                  <input type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" @change="selectEditDocument($event, requirement.id)" />
                </label>
                <p class="text-sm text-[var(--text-muted)]">Required documents are checked on save. Existing attachments remain; new uploads are added.</p>
              </div>
              <label class="md:col-span-2"><input v-model="editConfirmed" type="checkbox" required /> I confirm these changes are accurate and reaffirm my declaration for this application. I will reprint the updated form if needed.</label>
            </fieldset>
            <p v-if="editError" role="alert" class="text-red-600 mt-3">{{ editError }}</p>
            <div class="flex gap-3 mt-4">
              <button type="submit" :disabled="savingEdit || !editConfirmed || requirementsLoading" class="btn-action" style="background: var(--primary);">{{ savingEdit ? 'Saving…' : 'Save Changes' }}</button>
              <button type="button" :disabled="savingEdit" class="btn-action" style="background: var(--surface-muted); color: var(--text);" @click="cancelApplicationEdit">Cancel</button>
            </div>
          </form>
        </div>

        <!-- Supporting Documents -->
        <div class="modal-section">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
              <h4 class="text-base font-semibold text-white">
                Supporting Documents
              </h4>
              <p class="text-xs text-slate-400 mt-1">
                PDF, JPG, PNG, DOC, or DOCX; up to 10 MB per file.
              </p>
            </div>

            <button
              v-if="canAddAttachments"
              @click="toggleAttachmentForm"
              type="button"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium"
            >
              {{ showAttachmentForm ? "Cancel Upload" : "+ Add Documents" }}
            </button>
          </div>

          <div
            v-if="application.attachments?.length"
            class="space-y-2"
          >
            <div
              v-for="file in application.attachments"
              :key="file.attachment_id"
              class="flex items-center justify-between gap-3 p-3 bg-[#0F1A2A] border border-slate-700 rounded-lg"
            >
              <div class="flex items-center min-w-0">
                <FileText class="w-5 h-5 text-slate-500 mr-3 flex-shrink-0" />
                <span class="text-sm text-slate-300 truncate">
                  {{ file.file_name || file.name || "Supporting Document" }}
                </span>
              </div>

              <button
                @click="viewAttachment(file)"
                type="button"
                class="text-blue-400 hover:text-blue-300 text-sm font-medium ml-4"
              >
                View
              </button>
            </div>
          </div>

          <p v-else class="text-sm text-slate-400 py-2">
            No supporting documents attached yet.
          </p>

          <div
            v-if="showAttachmentForm && canAddAttachments"
            class="mt-4 p-4 border border-dashed border-slate-600 rounded-xl bg-[#0B1420]"
          >
            <input
              ref="attachmentInput"
              @change="handleAttachmentSelection"
              type="file"
              accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
              multiple
              class="block w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700"
            />

            <div v-if="selectedFiles.length" class="mt-4 space-y-2">
              <div
                v-for="(file, index) in selectedFiles"
                :key="`${file.name}-${file.lastModified}`"
                class="flex items-center justify-between gap-3 text-sm text-slate-300"
              >
                <span class="truncate">
                  {{ file.name }} ({{ formatFileSize(file.size) }})
                </span>
                <button
                  @click="removeSelectedFile(index)"
                  type="button"
                  class="text-red-400 hover:text-red-300"
                >
                  Remove
                </button>
              </div>
            </div>

            <p v-if="attachmentError" class="text-sm text-red-400 mt-3">
              {{ attachmentError }}
            </p>

            <div class="flex justify-end mt-4">
              <button
                @click="uploadAdditionalAttachments"
                :disabled="uploadingAttachments || selectedFiles.length === 0"
                type="button"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition text-sm font-medium"
              >
                {{ uploadingAttachments ? "Uploading..." : "Upload Documents" }}
              </button>
            </div>
          </div>

          <p v-if="!canAddAttachments" class="text-xs text-slate-500 mt-4">
            Additional documents can only be uploaded while the application is pending.
          </p>
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
            <div v-if="application.reviewed_at" class="flex items-start">

              <div class="w-3 h-3 bg-yellow-500 rounded-full mt-1.5 mr-3 flex-shrink-0"></div>

              <div>

                <p class="text-sm font-medium text-white">
                  Recorded Decision
                </p>

                <p class="text-xs text-slate-500 mt-1">
                  {{
                    formatDate(
                      application.reviewed_at
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
                      application.reviewed_at
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
                      application.reviewed_at
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
import { notify } from "@/composables/useNotifications";
import { ref, computed, onMounted, watch } from "vue";
import {
  addMyLeaveAttachments,
  downloadLeaveAttachment,
  getMyLeaves,
  getLeaveTypes,
  getDocumentRequirements,
  updateMyLeave,
} from "../services/leave";
import { useRouter } from "vue-router";

import {
  Search,
  FileText,
  CheckCircle,
  Clock,
  XCircle,
  X,
} from "lucide-vue-next";


interface Application extends Record<string, any> {
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
    attachment_id: number;
    file_name: string;
    name?: string;
    size?: number;
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
const showAttachmentForm = ref(false);
const selectedFiles = ref<File[]>([]);
const uploadingAttachments = ref(false);
const attachmentError = ref("");
const attachmentInput = ref<HTMLInputElement | null>(null);

const editingApplication = ref(false);
const savingEdit = ref(false);
const editError = ref('');
const editTypes = ref<any[]>([]);
const editRequirements = ref<any[]>([]);
const editDocumentFiles = ref<Record<number, File>>({});
const editConfirmed = ref(false);
const requirementsLoading = ref(false);
const editForm = ref<Record<string, any>>({});
const editFields = ['leave_type_id', 'start_date', 'end_date', 'reason', 'commutation', 'vacation_location_type', 'vacation_location', 'sick_type', 'illness', 'masters_degree', 'board_exam_review', 'monetization', 'terminal_leave', 'other_purpose'];
const canEditApplication = computed(() => Boolean(application.value && application.value.final_status?.toLowerCase() === 'pending' && !application.value.reviewed_at && !application.value.signatory_snapshot_locked));
const editedTypeName = computed(() => String(editTypes.value.find(type => Number(type.leave_type_id) === Number(editForm.value.leave_type_id))?.leave_type_name || application.value?.leave_type?.leave_type_name || '').toLowerCase());
const editIsVacation = computed(() => editedTypeName.value.includes('vacation'));
const editIsSick = computed(() => editedTypeName.value.includes('sick'));
const editIsStudy = computed(() => editedTypeName.value.includes('study'));
let requirementVersion = 0;
const loadEditRequirements = async () => {
  if (!editingApplication.value || !editForm.value.leave_type_id) return;
  const version = ++requirementVersion;
  requirementsLoading.value = true;
  try {
    // Backend makes the final working-day and required-document determination.
    const start = new Date(editForm.value.start_date);
    const end = new Date(editForm.value.end_date);
    const estimate = Math.max(1, Math.round((end.getTime() - start.getTime()) / 86400000) + 1);
    const result = await getDocumentRequirements(Number(editForm.value.leave_type_id), Number.isFinite(estimate) ? estimate : 1, editForm.value.start_date);
    if (version === requirementVersion) editRequirements.value = result;
  } catch {
    if (version === requirementVersion) { editRequirements.value = []; editError.value = 'Unable to load document requirements. Try changing the leave type again.'; }
  } finally { if (version === requirementVersion) requirementsLoading.value = false; }
};
const startApplicationEdit = async () => {
  if (!application.value || !canEditApplication.value) return;
  const record = application.value;
  editingApplication.value = true;
  editError.value = '';
  editConfirmed.value = false;
  editDocumentFiles.value = {};
  editForm.value = Object.fromEntries(editFields.map(key => [key, record[key] ?? '']));
  editForm.value.leave_type_id = record.leave_type_id;
  editForm.value.start_date = String(record.start_date).slice(0, 10);
  editForm.value.end_date = String(record.end_date).slice(0, 10);
  for (const key of ['masters_degree', 'board_exam_review', 'monetization', 'terminal_leave']) editForm.value[key] = Boolean(record[key]);
  try { editTypes.value = await getLeaveTypes(); } catch { editError.value = 'Unable to load leave types.'; }
  await loadEditRequirements();
};
const cancelApplicationEdit = () => {
  if (savingEdit.value) return;
  editingApplication.value = false;
  requirementVersion++;
  editDocumentFiles.value = {};
  editError.value = '';
};
watch(() => [editForm.value.leave_type_id, editForm.value.start_date, editForm.value.end_date], () => {
  if (editingApplication.value) { editError.value = ''; void loadEditRequirements(); }
});
watch(() => application.value?.leave_id, cancelApplicationEdit);
const selectEditDocument = (event: Event, id: number) => {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!file) { delete editDocumentFiles.value[id]; return; }
  if (file.size > 10 * 1024 * 1024) { editError.value = 'Maximum 10 MB per document.'; return; }
  editDocumentFiles.value[id] = file;
};
const hasExistingRequirement = (id: number) => application.value?.attachments?.some((file: any) => Number(file.leave_document_requirement_id) === Number(id));
const saveApplicationEdit = async () => {
  if (!application.value || savingEdit.value || !editConfirmed.value) return;
  savingEdit.value = true;
  editError.value = '';
  try {
    const payload = new FormData();
    for (const key of editFields) {
      const value = editForm.value[key];
      payload.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : String(value ?? ''));
    }
    payload.append('confirm_changes', '1');
    payload.append('original_updated_at', String(application.value.updated_at || ''));
    for (const [id, file] of Object.entries(editDocumentFiles.value)) payload.append(`document_attachments[${id}]`, file);
    const result = await updateMyLeave(Number(application.value.leave_id), payload);
    const updated = result.data;
    const index = applications.value.findIndex(item => Number(item.leave_id) === Number(updated.leave_id));
    if (index >= 0) applications.value[index] = updated;
    application.value = updated;
    editingApplication.value = false;
    editDocumentFiles.value = {};
    notify(result.message);
  } catch (error: any) {
    const errors = error.response?.data?.errors;
    editError.value = errors ? Object.values(errors).flat().join(' ') : error.response?.data?.message || 'Unable to save changes.';
  } finally { savingEdit.value = false; }
};
const leaveDetailRows = (record: Application): {label: string; value: string}[] => {
  const rows: {label: string; value: string}[] = [];
  const add = (label: string, value: any) => { if (value !== null && value !== undefined && value !== '') rows.push({label, value: String(value)}); };
  add('Requested working days', record.number_of_days);
  add('Reason', record.reason);
  add('Commutation', record.commutation ? String(record.commutation).replace(/_/g, ' ') : null);
  add('Vacation location type', record.vacation_location_type?.replace(/_/g, ' '));
  add('Vacation location', record.vacation_location);
  add('Treatment', record.sick_type?.replace(/_/g, ' '));
  add('Illness', record.illness);
  if (record.masters_degree) add('Study purpose', "Completion of master’s degree");
  if (record.board_exam_review) add('Study purpose', 'Board examination review');
  if (record.monetization) add('Other purpose', 'Monetization');
  if (record.terminal_leave) add('Other purpose', 'Terminal leave');
  add('Other purpose', record.other_purpose);
  add('Days with pay', record.days_with_pay);
  add('Days without pay', record.days_without_pay);
  add('Other approval', record.other_approval);
  add('Disapproval reason', record.disapproval_reason);
  add('Recorded remarks', record.admin_remarks);
  return rows;
};

const canAddAttachments = computed(() =>
  application.value?.final_status?.toLowerCase() === "pending"
);

const resetAttachmentForm = () => {
  showAttachmentForm.value = false;
  selectedFiles.value = [];
  attachmentError.value = "";

  if (attachmentInput.value) {
    attachmentInput.value.value = "";
  }
};

const toggleAttachmentForm = () => {
  if (showAttachmentForm.value) {
    resetAttachmentForm();
    return;
  }

  showAttachmentForm.value = true;
  attachmentError.value = "";
};

const handleAttachmentSelection = (event: Event) => {
  const input = event.target as HTMLInputElement;
  const files = Array.from(input.files ?? []);
  const allowedExtensions = /\.(pdf|jpe?g|png|docx?)$/i;
  const maximumSize = 10 * 1024 * 1024;

  attachmentError.value = "";

  if (files.length > 5) {
    attachmentError.value = "You can upload a maximum of 5 files at a time.";
    input.value = "";
    return;
  }

  const invalidFile = files.find(
    (file) => !allowedExtensions.test(file.name) || file.size > maximumSize
  );

  if (invalidFile) {
    attachmentError.value =
      "Each file must be PDF, JPG, PNG, DOC, or DOCX and no larger than 10 MB.";
    input.value = "";
    return;
  }

  selectedFiles.value = files;
};

const removeSelectedFile = (index: number) => {
  selectedFiles.value.splice(index, 1);

  if (selectedFiles.value.length === 0 && attachmentInput.value) {
    attachmentInput.value.value = "";
  }
};

const formatFileSize = (size: number) => {
  if (size < 1024 * 1024) {
    return `${(size / 1024).toFixed(1)} KB`;
  }

  return `${(size / (1024 * 1024)).toFixed(1)} MB`;
};

const uploadAdditionalAttachments = async () => {
  if (!application.value || selectedFiles.value.length === 0) {
    return;
  }

  uploadingAttachments.value = true;
  attachmentError.value = "";

  try {
    const response = await addMyLeaveAttachments(
      Number(application.value.leave_id),
      selectedFiles.value
    );

    const updatedApplication = response.data;
    application.value.attachments = updatedApplication.attachments ?? [];

    const applicationIndex = applications.value.findIndex(
      (item) => Number(item.leave_id) === Number(application.value?.leave_id)
    );

    if (applicationIndex !== -1) {
      applications.value[applicationIndex].attachments =
        updatedApplication.attachments ?? [];
    }

    resetAttachmentForm();
    notify(response.message || "Supporting documents uploaded successfully.");
  } catch (error: any) {
    const validationErrors = error.response?.data?.errors as
      | Record<string, string[]>
      | undefined;

    attachmentError.value =
      error.response?.data?.message ||
      Object.values(validationErrors ?? {}).flat().join(" ") ||
      "Unable to upload supporting documents.";
  } finally {
    uploadingAttachments.value = false;
  }
};

const viewAttachment = async (file: {
  attachment_id: number;
  file_name: string;
}) => {
  if (!application.value) {
    return;
  }

  const openedWindow = window.open("", "_blank");

  if (!openedWindow) {
    attachmentError.value = "Please allow pop-ups to view the document.";
    return;
  }

  openedWindow.opener = null;

  try {
    const blob = await downloadLeaveAttachment(
      Number(application.value.leave_id),
      Number(file.attachment_id)
    );
    const url = URL.createObjectURL(blob);
    openedWindow.location.href = url;

    window.setTimeout(() => URL.revokeObjectURL(url), 60000);
  } catch (error: any) {
    openedWindow.close();
    attachmentError.value =
      error.message || "Unable to open the supporting document.";
  }
};

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


onMounted(async () => {
  // Deep links from the dashboard: ?status=pending or ?open=<leave_id>
  const qs = new URLSearchParams(window.location.search);
  const status = qs.get("status");
  if (status && ["pending", "approved", "disapproved"].includes(status)) {
    filterStatus.value = status;
  }

  await loadApplications();

  const open = qs.get("open");
  if (open) {
    const target = applications.value.find(
      (a: any) => String(a.leave_id) === open,
    );
    if (target) viewDetails(target);
  }
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
  resetAttachmentForm();
  application.value = app;

};

</script>


<style scoped>
.application-edit-form label { color: var(--text); font-size: .875rem; }
.application-edit-form input:not([type=checkbox]), .application-edit-form select, .application-edit-form textarea { display: block; width: 100%; margin-top: .4rem; padding: .6rem .75rem; border: 1px solid var(--border); border-radius: .5rem; background: var(--surface-muted); color: var(--text); }
.application-edit-form button:disabled { opacity: .5; cursor: not-allowed; }


/* =========================================
   DASHBOARD BACKGROUND
========================================= */

.dashboard-shell {
  background: var(--app-bg);
  color: var(--text);
}


/* =========================================
   MAIN CARD
========================================= */

.neo-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1.4rem;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.12);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.neo-card:hover {
  box-shadow: 0 14px 26px rgba(15, 23, 42, 0.2);
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
  border: 1px solid var(--border);
  border-radius: 0.65rem;
  padding: 0.65rem 0.85rem;
  background: var(--surface);
  color: var(--text);
  font-size: 0.875rem;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.dark-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 2px var(--focus-ring);
}

.dark-input::placeholder {
  color: var(--text-muted);
}

.dark-input option {
  background: var(--surface);
  color: var(--text);
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
  color: var(--text-muted);
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
  background: var(--surface-muted);
}

.table-cell {
  padding: 1rem 1.5rem;
  white-space: nowrap;
  font-size: 0.875rem;
  color: var(--text);
}

/* Table rows/body should also follow the theme surface, since the
   template applies text-slate-300 utility classes directly on <td>. */
tbody {
  background: var(--surface);
}

tbody tr:hover {
  background: var(--surface-muted);
}


/* =========================================
   STATUS BADGES
========================================= */

.status-approved {
  background: rgba(34, 197, 94, 0.14);
  color: var(--success);
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.status-pending {
  background: rgba(234, 179, 8, 0.14);
  color: var(--warning);
  border: 1px solid rgba(234, 179, 8, 0.3);
}

.status-disapproved {
  background: rgba(239, 68, 68, 0.14);
  color: var(--danger);
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-pr {
  background: rgba(59, 130, 246, 0.14);
  color: var(--primary);
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.status-default {
  background: rgba(100, 116, 139, 0.14);
  color: var(--text-muted);
  border: 1px solid rgba(100, 116, 139, 0.3);
}


/* =========================================
   MODAL SECTIONS
========================================= */

.modal-section {
  background: var(--surface-muted);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 1.25rem;
}

.modal-title {
  color: var(--text);
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 1.25rem;
}

.modal-label {
  color: var(--text-muted);
  font-size: 0.75rem;
  margin-bottom: 0.3rem;
}

.modal-value {
  color: var(--text);
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