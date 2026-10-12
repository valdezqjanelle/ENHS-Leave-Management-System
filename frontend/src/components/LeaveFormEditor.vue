<template>
  <section class="leave-form-editor">
    <h2>EDIT APPLICATION FOR LEAVE</h2>
    <p>Application #{{ application.leave_id }} · Only your application fields can be changed. Official processing fields are read-only.</p>
    <div class="identity-section"><strong>Employee:</strong> {{ [application.employee?.first_name, application.employee?.last_name].filter(Boolean).join(' ') || 'Current employee' }}<br><strong>Date filed:</strong> {{ String(application.date_filed || '').slice(0, 10) }}</div>
    <p v-if="!canEditApplication" role="alert">This application has been processed and cannot be edited.</p>
          <form v-if="editingApplication" class="application-edit-form" @submit.prevent="saveApplicationEdit">
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
  </section>
</template>
<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { notify } from '@/composables/useNotifications';
import { previewWorkingDays } from '@/services/holiday';
import { getLeaveTypes, getDocumentRequirements, updateMyLeave } from '@/services/leave';
const props = defineProps<{ application: Record<string, any> }>();
const emit = defineEmits<{ saved: [application: Record<string, any>]; cancel: []; busy: [value: boolean] }>();
const application = ref<Record<string, any>>(props.application);
const applications = ref<any[]>([]);
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
    // Use the same working-day API as new applications; backend rechecks on save.
    const summary = await previewWorkingDays(editForm.value.start_date, editForm.value.end_date);
    const result = await getDocumentRequirements(Number(editForm.value.leave_type_id), Math.max(1, Number(summary.working_days || 0)), editForm.value.start_date);
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
  if (editForm.value.commutation === 'not_requested') editForm.value.commutation = 'not requested';
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
  emit('cancel');
};
watch(() => [editForm.value.leave_type_id, editForm.value.start_date, editForm.value.end_date], () => {
  if (editingApplication.value) { editError.value = ''; void loadEditRequirements(); }
});

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
    emit('saved', updated);
  } catch (error: any) {
    const errors = error.response?.data?.errors;
    editError.value = errors ? Object.values(errors).flat().join(' ') : error.response?.data?.message || 'Unable to save changes.';
  } finally { savingEdit.value = false; }
};

watch(savingEdit, value => emit('busy', value));
onMounted(startApplicationEdit);
</script>
<style scoped>
.leave-form-editor { background: #fff; color: #172033; border: 1px solid #cbd5e1; border-radius: .75rem; padding: 1.5rem; max-width: 896px; margin: 0 auto 1.5rem; }
h2 { font-weight: 700; font-size: 1.15rem; margin-bottom: .5rem; }
p { font-size: .875rem; line-height: 1.5; margin-bottom: .75rem; }
.identity-section { border-bottom: 1px solid #cbd5e1; padding-bottom: .75rem; margin-bottom: 1rem; font-size: .875rem; }
.application-edit-form label { color: #172033; font-size: .875rem; }
.application-edit-form input:not([type=checkbox]),.application-edit-form select,.application-edit-form textarea { display: block; width: 100%; margin-top: .4rem; padding: .6rem; border: 1px solid #94a3b8; border-radius: .4rem; background: #fff; color: #172033; }
.application-edit-form button { padding: .6rem 1rem; border-radius: .5rem; background: #2563eb !important; color: #fff !important; }
.application-edit-form button:disabled { opacity: .5; cursor: not-allowed; }
.text-\[var\(--text-muted\)\] { color: #475569; }
@media print { .leave-form-editor { display: none; } }
</style>
