<template>
  <section class="neo-card p-6 space-y-5">
    <h2 class="text-xl font-semibold text-[var(--text)]">Salary Schedules</h2>
    <p class="hint">Create a draft, review its rates, then activate it on or after its effective date. Activation updates future salary lookups; existing employee and admin salary amounts remain unchanged.</p>
    <p v-if="error" class="error" role="alert">{{ error }}</p>
    <form class="panel grid gap-4 sm:grid-cols-2" @submit.prevent="createDraft">
      <label>New schedule name<input v-model="newForm.name" required maxlength="100" :disabled="busy" placeholder="Example: Salary Schedule 2027" /></label>
      <label>Effective date<input v-model="newForm.effective_date" type="date" required :disabled="busy" /></label>
      <label>Source / reference<input v-model="newForm.source_reference" maxlength="500" :disabled="busy" placeholder="Approved issuance or agency reference" /></label>
      <label>Copy rates from<select v-model="newForm.copy_from_id" :disabled="busy"><option value="">Current active schedule</option><option v-for="s in schedules" :key="s.id" :value="s.id">{{ s.name }} ({{ s.status }})</option></select></label>
      <div><button type="submit" class="primary" :disabled="busy">Create draft</button></div>
    </form>
    <label>View schedule<select :value="selected?.schedule.id ?? ''" :disabled="busy" @change="choose(Number(($event.target as HTMLSelectElement).value))"><option value="">Select schedule</option><option v-for="s in schedules" :key="s.id" :value="s.id">{{ s.name }} · {{ s.status }} · {{ s.effective_date || 'Original date unrecorded' }} · {{ s.rate_count }} rates</option></select></label>
    <p v-if="loading" class="hint">Loading schedules…</p>
    <div v-if="selected" class="space-y-4">
      <div class="flex flex-wrap gap-3 items-center"><h3 class="font-semibold text-[var(--text)]">{{ selected.schedule.name }}</h3><span class="badge">{{ selected.schedule.status }}</span><span v-if="dirty" class="hint">Unsaved changes</span></div>
      <p v-if="!editable" class="hint">This schedule is read-only. Create a draft to change its rates.</p>
      <div class="grid gap-4 sm:grid-cols-2">
        <label>Schedule name<input v-model="selected.schedule.name" maxlength="100" :disabled="busy || !editable" /></label>
        <label>Effective date<input v-model="selected.schedule.effective_date" type="date" :disabled="busy || !editable" /></label>
        <label class="sm:col-span-2">Source / reference<input v-model="selected.schedule.source_reference" maxlength="500" :disabled="busy || !editable" /></label>
      </div>
      <div v-if="editable" class="flex flex-wrap gap-2 items-end"><label>Salary grade<input v-model="newGrade" type="number" min="1" max="33" step="1" :disabled="busy" /></label><button type="button" class="secondary" :disabled="busy" @click="addGrade">Add grade</button></div>
      <div v-if="unsupportedRates.length" class="error" role="alert">
        <p>This imported schedule has unsupported step numbers. Review the affected grade against the approved salary reference. In a draft, remove and re-add the grade to enter its eight correct step rates.</p>
        <p v-for="rate in unsupportedRates" :key="`${rate.salary_grade}-${rate.step}`">SG {{ rate.salary_grade }} · Step {{ rate.step }} · {{ money(rate.salary) }}</p>
      </div>
      <div class="table-scroll"><table><thead><tr><th>Grade</th><th v-for="step in 8" :key="step">Step {{ step }}</th><th v-if="editable">Action</th></tr></thead><tbody>
        <tr v-for="grade in grades" :key="grade"><th>SG {{ grade }}</th><td v-for="step in 8" :key="step"><input v-if="editable" :value="rateValue(grade, step)" type="number" min="0.01" max="99999999.99" step="0.01" :aria-label="`Salary grade ${grade}, step ${step}`" :disabled="busy" @input="setRate(grade, step, ($event.target as HTMLInputElement).value)" /><span v-else>{{ money(rateValue(grade, step)) }}</span></td><td v-if="editable"><button type="button" class="secondary" :disabled="busy" @click="removeGrade(grade)">Remove</button></td></tr>
        <tr v-if="!grades.length"><td :colspan="editable ? 10 : 9">No salary rates recorded.</td></tr>
      </tbody></table></div>
      <div v-if="editable" class="flex flex-wrap gap-2"><button type="button" class="primary" :disabled="busy || !dirty" @click="saveDraft">Save draft</button><button type="button" class="secondary" :disabled="busy || dirty" @click="previewDraft">Preview activation</button><button type="button" class="secondary" :disabled="busy" @click="reloadSelected">Reload saved draft</button><button type="button" class="danger" :disabled="busy" @click="deleteDraft">Delete draft</button></div>
      <div v-if="preview && !dirty" class="panel space-y-3">
        <h3 class="font-semibold text-[var(--text)]">Activation preview</h3><p class="hint">{{ preview.rate_count }} rates · {{ preview.changes.length }} changes. Existing employee and admin salary records will not be rewritten.</p>
        <ul v-if="preview.errors.length" class="error list-disc pl-6"><li v-for="message in preview.errors" :key="message">{{ message }}</li></ul>
        <div class="table-scroll"><table class="preview-table"><thead><tr><th>Grade</th><th>Step</th><th>Current rate</th><th>Proposed rate</th></tr></thead><tbody><tr v-for="change in preview.changes" :key="`${change.salary_grade}-${change.step}`"><td>{{ change.salary_grade }}</td><td>{{ change.step }}</td><td>{{ money(change.before) }}</td><td>{{ money(change.after) }}</td></tr><tr v-if="!preview.changes.length"><td colspan="4">No rate changes.</td></tr></tbody></table></div>
        <button type="button" class="primary" :disabled="busy || !preview.can_activate" @click="activate">Activate schedule</button>
      </div>
    </div>
  </section>
</template>
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { salarySchedules, salarySchedule, createSalarySchedule, saveSalarySchedule, previewSalarySchedule, activateSalarySchedule, deleteSalaryDraft, compensationError, type ScheduleHeader, type ScheduleDetails, type SchedulePreview } from '@/services/compensation'
import { confirmAction, notify } from '@/composables/useNotifications'
const schedules = ref<ScheduleHeader[]>([]), selected = ref<ScheduleDetails | null>(null), preview = ref<SchedulePreview | null>(null)
const busy = ref(false), loading = ref(false), error = ref(''), baseline = ref(''), newGrade = ref<string | number>('')
const newForm = ref({ name: '', effective_date: '', source_reference: '', copy_from_id: '' as string | number })
const editable = computed(() => selected.value?.schedule.status === 'draft')
const snapshot = () => selected.value ? JSON.stringify({ name: selected.value.schedule.name, effective_date: selected.value.schedule.effective_date, source_reference: selected.value.schedule.source_reference, rates: selected.value.rates }) : ''
const dirty = computed(() => snapshot() !== baseline.value)
const grades = computed(() => [...new Set(selected.value?.rates.map(r => Number(r.salary_grade)) ?? [])].sort((a, b) => a - b))
const unsupportedRates = computed(() => selected.value?.rates.filter(r => Number(r.step) < 1 || Number(r.step) > 8) ?? [])
const rateValue = (grade: number, step: number) => selected.value?.rates.find(r => Number(r.salary_grade) === grade && Number(r.step) === step)?.salary ?? ''
const money = (value: string | number | null) => value === null || value === '' ? '—' : Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const adopt = (data: ScheduleDetails) => { selected.value = data; baseline.value = snapshot(); preview.value = null; error.value = '' }
const loadList = async () => { schedules.value = await salarySchedules() }
const discard = async () => !dirty.value || await confirmAction({ title: 'Discard unsaved changes?', message: 'Your unsaved draft changes will be discarded.', confirmLabel: 'Discard', variant: 'primary' })
const choose = async (id: number) => {
  if (busy.value || !(await discard())) return
  busy.value = true; error.value = ''
  try { if (id) adopt(await salarySchedule(id)); else { selected.value = null; baseline.value = ''; preview.value = null } } catch (e) { error.value = compensationError(e) } finally { busy.value = false }
}
const createDraft = async () => {
  if (busy.value || !(await discard())) return
  busy.value = true; error.value = ''
  try { adopt(await createSalarySchedule({ ...newForm.value, name: newForm.value.name.trim(), copy_from_id: newForm.value.copy_from_id ? Number(newForm.value.copy_from_id) : null })); await loadList(); newForm.value.name = ''; notify('Salary schedule draft created.') } catch (e) { error.value = compensationError(e) } finally { busy.value = false }
}
const setRate = (grade: number, step: number, value: string) => {
  if (!selected.value || !editable.value || busy.value) return
  const row = selected.value.rates.find(r => Number(r.salary_grade) === grade && Number(r.step) === step)
  if (row) row.salary = value; else selected.value.rates.push({ salary_grade: grade, step, salary: value })
  preview.value = null
}
const addGrade = () => {
  const grade = Number(newGrade.value)
  if (!selected.value || !Number.isInteger(grade) || grade < 1 || grade > 33 || grades.value.includes(grade)) { error.value = 'Enter a new salary grade from 1 to 33.'; return }
  for (let step = 1; step <= 8; step++) selected.value.rates.push({ salary_grade: grade, step, salary: '' })
  newGrade.value = ''; preview.value = null; error.value = ''
}
const removeGrade = (grade: number) => { if (selected.value) selected.value.rates = selected.value.rates.filter(r => Number(r.salary_grade) !== grade); preview.value = null }
const saveDraft = async () => {
  if (!selected.value || busy.value) return
  busy.value = true; error.value = ''
  try { const s = selected.value; adopt(await saveSalarySchedule(s.schedule.id, { name: s.schedule.name.trim(), effective_date: s.schedule.effective_date, source_reference: s.schedule.source_reference, revision: s.schedule.revision, rates: s.rates })); await loadList(); notify('Draft saved. Preview before activation.') } catch (e) { error.value = compensationError(e) } finally { busy.value = false }
}
const previewDraft = async () => {
  if (!selected.value || busy.value || dirty.value) return
  busy.value = true; error.value = ''; preview.value = null
  try { preview.value = await previewSalarySchedule(selected.value.schedule.id, selected.value.schedule.revision) } catch (e) { error.value = compensationError(e) } finally { busy.value = false }
}
const reloadSelected = async () => { if (selected.value) await choose(selected.value.schedule.id) }
const activate = async () => {
  if (!selected.value || !preview.value?.can_activate || dirty.value || busy.value) return
  const id = selected.value.schedule.id, revision = selected.value.schedule.revision, activeId = preview.value.active_schedule_id
  busy.value = true
  try {
    if (!(await confirmAction({ title: 'Activate salary schedule?', message: `Activate ${selected.value.schedule.name}? Future salary lookups will use these rates. Existing recorded salary amounts will remain unchanged.`, confirmLabel: 'Activate', variant: 'primary' }))) return
    await activateSalarySchedule(id, revision, activeId); adopt(await salarySchedule(id)); await loadList(); notify('Salary schedule activated successfully.')
  } catch (e) { error.value = compensationError(e); preview.value = null } finally { busy.value = false }
}
const deleteDraft = async () => {
  if (!selected.value || busy.value || !editable.value) return
  busy.value = true
  try {
    if (!(await confirmAction({ title: 'Delete draft?', message: 'This deletes this salary draft and its rates. The active schedule remains unchanged.', confirmLabel: 'Delete draft', variant: 'primary' }))) return
    await deleteSalaryDraft(selected.value.schedule.id, selected.value.schedule.revision); selected.value = null; baseline.value = ''; preview.value = null; await loadList(); notify('Draft deleted.')
  } catch (e) { error.value = compensationError(e) } finally { busy.value = false }
}
onMounted(async () => { loading.value = true; try { await loadList(); const active = schedules.value.find(s => s.status === 'active'); if (active) adopt(await salarySchedule(active.id)) } catch (e) { error.value = compensationError(e) } finally { loading.value = false } })
</script>
<style scoped>
.panel { padding: 1.1rem; border: 1px solid var(--border); border-radius: .75rem; background: var(--surface-muted); }
label { display: grid; gap: .4rem; color: var(--text); font-size: .875rem; font-weight: 500; }
input,select { width: 100%; min-width: 0; padding: .65rem; border: 1px solid var(--border); border-radius: .5rem; color: var(--text); background: var(--surface); }
button { padding: .6rem .9rem; border-radius: .5rem; font-size: .85rem; font-weight: 600; }.primary { background: var(--primary); color: white; }.secondary { border: 1px solid var(--border); background: var(--surface); color: var(--text); }.danger { background: #b91c1c; color: white; }
button:disabled,input:disabled,select:disabled { opacity: .6; cursor: not-allowed; }button:focus-visible,input:focus-visible,select:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
.hint { font-size: .875rem; color: var(--text-muted); }.error { padding: .8rem; background: #fef2f2; color: #b91c1c; border-radius: .5rem; }.badge { background: var(--surface-muted); color: var(--text); border: 1px solid var(--border); padding: .3rem .6rem; border-radius: 999px; text-transform: capitalize; font-size: .8rem; }
.table-scroll { overflow: auto; max-height: 32rem; }table { width: 100%; min-width: 1120px; border-collapse: collapse; }th,td { color: var(--text); padding: .6rem; border-bottom: 1px solid var(--border); font-size: .85rem; text-align: left; }th { background: var(--surface-muted); }td input { min-width: 105px; }.preview-table { min-width: 500px; }
</style>
