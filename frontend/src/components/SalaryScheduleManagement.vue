<template>
  <section class="neo-card p-6 space-y-5">
    <div class="page-heading">
      <div>
        <h2 class="text-xl font-semibold text-[var(--text)]">
          Salary Schedules
        </h2>
        <p class="hint mt-1">
          View existing salary rates or prepare a new schedule.
        </p>
      </div>
      <button
        type="button"
        class="primary"
        :disabled="busy || loading"
        @click="openCreate"
      >
        <Plus :size="16" aria-hidden="true" /> Create New Salary Schedule
      </button>
    </div>
    <div class="schedule-notice">
      <Info :size="18" aria-hidden="true" />
      <p>
        Existing rates are loaded from your database. New rates must be saved as
        a draft, then reviewed and activated. Activation does not automatically
        change employees’ recorded salaries.
      </p>
    </div>
    <p v-if="error" class="error" role="alert">{{ error }}</p>
    <form
      v-if="showCreate"
      class="panel grid gap-4 sm:grid-cols-2"
      @submit.prevent="createDraft"
    >
      <div class="sm:col-span-2">
        <h3 class="font-semibold">Create New Salary Schedule</h3>
        <p class="hint mt-1">
          Start with a copy of the existing rates, then update the amounts in
          your draft.
        </p>
      </div>
      <label
        >New schedule name<input
          v-model="newForm.name"
          required
          maxlength="100"
          :disabled="busy"
          placeholder="Example: Salary Schedule 2027"
      /></label>
      <label
        >Effective date<input
          v-model="newForm.effective_date"
          type="date"
          required
          :disabled="busy"
      /></label>
      <label
        >Source / reference<input
          v-model="newForm.source_reference"
          maxlength="500"
          :disabled="busy"
          placeholder="Approved issuance or agency reference"
      /></label>
      <label
        >Copy rates from<select v-model="newForm.copy_from_id" :disabled="busy">
          <option value="">Current active schedule</option>
          <option v-for="s in schedules" :key="s.id" :value="s.id">
            {{ s.name }} ({{ s.status }})
          </option>
        </select></label
      >
      <div class="sm:col-span-2 flex flex-wrap gap-2">
        <button type="submit" class="primary" :disabled="busy">
          {{ busy ? "Creating…" : "Create Saved Draft" }}</button
        ><button
          type="button"
          class="secondary"
          :disabled="busy"
          @click="cancelCreate"
        >
          Cancel
        </button>
      </div>
    </form>
    <label
      >Existing schedules and saved drafts<select
        :value="selected?.schedule.id ?? ''"
        :disabled="busy || loading"
        @change="changeSelection"
      >
        <option value="">Select schedule</option>
        <option v-for="s in schedules" :key="s.id" :value="s.id">
          {{ s.name }} ·
          {{
            s.status === "draft"
              ? "Saved draft"
              : s.status === "active"
                ? "Currently active"
                : "Archived"
          }}
          · {{ s.effective_date || "Original date unrecorded" }} ·
          {{ s.rate_count }} rates
        </option>
      </select></label
    >
    <p v-if="loading" class="hint">Loading schedules…</p>
    <p v-if="!loading && !schedules.length" class="hint">
      No schedules found. Existing database rates appear here after the salary
      management migration has run.
    </p>
    <div v-if="selected" class="space-y-4">
      <div class="flex flex-wrap gap-3 items-center">
        <h3 class="font-semibold text-[var(--text)]">
          {{ selected.schedule.name }}
        </h3>
        <span class="badge">{{ selected.schedule.status }}</span
        ><span
          v-if="editable"
          class="save-status"
          :class="dirty ? 'unsaved' : 'saved'"
          >{{ dirty ? "Unsaved changes" : "Saved draft" }}</span
        >
      </div>
      <div v-if="editable" class="draft-toolbar">
        <div>
          <strong>{{
            dirty
              ? "Your changes are not saved yet."
              : "This draft is saved in the database."
          }}</strong>
          <p class="hint mt-1">
            {{
              dirty
                ? "Save your draft before leaving. Blank steps can be completed later; all steps are required before activation."
                : "You can reopen it from the schedule list anytime. Preview and activate it when ready."
            }}
          </p>
        </div>
        <button
          type="button"
          class="primary"
          :disabled="busy || !dirty"
          @click="saveDraft"
        >
          <Save :size="16" aria-hidden="true" />
          {{ busy ? "Please wait…" : "Save Changes" }}
        </button>
      </div>
      <div v-if="editable && saveMessage" class="save-feedback" :class="saveFailed ? 'error' : 'save-success'" role="status" aria-live="polite">{{ saveMessage }}</div>
      <p v-if="!editable" class="hint">
        This schedule is read-only. Create a draft to change its rates.
      </p>
      <div class="grid gap-4 sm:grid-cols-2">
        <label
          >Schedule name<input
            v-model="selected.schedule.name"
            maxlength="100"
            :disabled="busy || !editable"
        /></label>
        <label
          >Effective date<input
            v-model="selected.schedule.effective_date"
            type="date"
            :disabled="busy || !editable"
        /></label>
        <label class="sm:col-span-2"
          >Source / reference<input
            v-model="selected.schedule.source_reference"
            maxlength="500"
            :disabled="busy || !editable"
        /></label>
      </div>
      <div v-if="editable" class="flex flex-wrap gap-2 items-end">
        <label
          >Salary grade<input
            v-model="newGrade"
            type="number"
            min="1"
            max="33"
            step="1"
            :disabled="busy" /></label
        ><button
          type="button"
          class="secondary"
          :disabled="busy"
          @click="addGrade"
        >
          <Plus :size="15" aria-hidden="true" /> Add Grade Row
        </button>
        <p class="hint">
          Click Save Changes to keep added rows. Blank steps can be
          completed later, before activation.
        </p>
      </div>
      <div v-if="unsupportedRates.length" class="error" role="alert">
        <p>
          This imported schedule has unsupported step numbers. Review the
          affected grade against the approved salary reference. In a draft,
          remove and re-add the grade to enter its eight correct step rates.
        </p>
        <button v-if="editable" type="button" class="secondary" :disabled="busy" @click="removeUnsupportedSteps">Remove Unsupported Step Rows</button>
        <p
          v-for="rate in unsupportedRates"
          :key="`${rate.salary_grade}-${rate.step}`"
        >
          SG {{ rate.salary_grade }} · Step {{ rate.step }} ·
          {{ money(rate.salary) }}
        </p>
      </div>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>Grade</th>
              <th v-for="step in 8" :key="step">Step {{ step }}</th>
              <th v-if="editable">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="grade in grades" :key="grade">
              <th>SG {{ grade }}</th>
              <td v-for="step in 8" :key="step">
                <input
                  v-if="editable"
                  :value="rateValue(grade, step)"
                  type="number"
                  min="0.01"
                  max="99999999.99"
                  step="0.01"
                  :aria-label="`Salary grade ${grade}, step ${step}`"
                  :disabled="busy"
                  @input="
                    setRate(
                      grade,
                      step,
                      ($event.target as HTMLInputElement).value,
                    )
                  "
                /><span v-else>{{ money(rateValue(grade, step)) }}</span>
              </td>
              <td v-if="editable">
                <button
                  type="button"
                  class="secondary"
                  :disabled="busy"
                  @click="removeGrade(grade)"
                >
                  Remove
                </button>
              </td>
            </tr>
            <tr v-if="!grades.length">
              <td :colspan="editable ? 10 : 9">No salary rates recorded.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="editable" class="flex flex-wrap gap-2">
        <button
          type="button"
          class="primary"
          :disabled="busy || !dirty"
          @click="saveDraft"
        >
          Save Changes</button
        ><button
          type="button"
          class="secondary"
          :disabled="busy || dirty"
          @click="previewDraft"
        >
          Preview activation</button
        ><button
          type="button"
          class="secondary"
          :disabled="busy"
          @click="reloadSelected"
        >
          Reload saved draft</button
        ><button
          type="button"
          class="danger"
          :disabled="busy"
          @click="deleteDraft"
        >
          Delete draft
        </button>
      </div>
      <div v-if="editable && saveMessage" class="save-feedback" :class="saveFailed ? 'error' : 'save-success'" role="status" aria-live="polite">{{ saveMessage }}</div>
      <div v-if="preview && !dirty" class="panel space-y-3">
        <h3 class="font-semibold text-[var(--text)]">Activation preview</h3>
        <p class="hint">
          {{ preview.rate_count }} rates · {{ preview.changes.length }} changes.
          Existing employee and admin salary records will not be rewritten.
        </p>
        <ul v-if="preview.errors.length" class="error list-disc pl-6">
          <li v-for="message in preview.errors" :key="message">
            {{ message }}
          </li>
        </ul>
        <div class="table-scroll">
          <table class="preview-table">
            <thead>
              <tr>
                <th>Grade</th>
                <th>Step</th>
                <th>Current rate</th>
                <th>Proposed rate</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="change in preview.changes"
                :key="`${change.salary_grade}-${change.step}`"
              >
                <td>{{ change.salary_grade }}</td>
                <td>{{ change.step }}</td>
                <td>{{ money(change.before) }}</td>
                <td>{{ money(change.after) }}</td>
              </tr>
              <tr v-if="!preview.changes.length">
                <td colspan="4">No rate changes.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <button
          type="button"
          class="primary"
          :disabled="busy || !preview.can_activate"
          @click="activate"
        >
          Activate schedule
        </button>
      </div>
    </div>
  </section>
</template>
<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { onBeforeRouteLeave, onBeforeRouteUpdate } from "vue-router";
import { Plus, Save, Info } from "lucide-vue-next";
import {
  salarySchedules,
  salarySchedule,
  createSalarySchedule,
  saveSalarySchedule,
  previewSalarySchedule,
  activateSalarySchedule,
  deleteSalaryDraft,
  compensationError,
  type ScheduleHeader,
  type ScheduleDetails,
  type SchedulePreview,
} from "@/services/compensation";
import { confirmAction, notify } from "@/composables/useNotifications";
const schedules = ref<ScheduleHeader[]>([]),
  selected = ref<ScheduleDetails | null>(null),
  preview = ref<SchedulePreview | null>(null);
const busy = ref(false),
  loading = ref(false),
  error = ref(""),
  baseline = ref(""),
  newGrade = ref<string | number>("");
const showCreate = ref(false),
  newFormBaseline = ref("");
const newForm = ref({
  name: "",
  effective_date: "",
  source_reference: "",
  copy_from_id: "" as string | number,
});
const creationDirty = computed(
  () =>
    showCreate.value && JSON.stringify(newForm.value) !== newFormBaseline.value,
);
const storageKey = () => {
  try {
    const user = JSON.parse(localStorage.getItem("user") || "{}");
    return `els.salary-schedule.selected.${user.user_id ?? user.id ?? "current"}`;
  } catch {
    return "els.salary-schedule.selected.current";
  }
};
const remember = (id: number | null) => {
  try {
    if (id) localStorage.setItem(storageKey(), String(id));
    else localStorage.removeItem(storageKey());
  } catch {
    /* Selection still works when storage is unavailable. */
  }
};
const rememberedId = () => {
  try {
    return Number(localStorage.getItem(storageKey()) || 0);
  } catch {
    return 0;
  }
};
const editable = computed(() => selected.value?.schedule.status === "draft");
const snapshot = () =>
  selected.value
    ? JSON.stringify({
        name: selected.value.schedule.name,
        effective_date: selected.value.schedule.effective_date,
        source_reference: selected.value.schedule.source_reference,
        rates: selected.value.rates,
      })
    : "";
const dirty = computed(() => snapshot() !== baseline.value);
const grades = computed(() =>
  [
    ...new Set(selected.value?.rates.map((r) => Number(r.salary_grade)) ?? []),
  ].sort((a, b) => a - b),
);
const unsupportedRates = computed(
  () =>
    selected.value?.rates.filter(
      (r) => Number(r.step) < 1 || Number(r.step) > 8,
    ) ?? [],
);
const rateValue = (grade: number, step: number) =>
  selected.value?.rates.find(
    (r) => Number(r.salary_grade) === grade && Number(r.step) === step,
  )?.salary ?? "";
const money = (value: string | number | null) =>
  value === null || value === ""
    ? "—"
    : Number(value).toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
const adopt = (data: ScheduleDetails) => {
  saveMessage.value = ''; saveFailed.value = false
  selected.value = data;
  baseline.value = snapshot();
  preview.value = null;
  error.value = "";
  remember(data.schedule.id);
};
const loadList = async () => {
  schedules.value = await salarySchedules();
};
const confirmDiscard = async () =>
  await confirmAction({
    title: "Leave without saving?",
    message:
      "Your added grade rows or changed salary rates have not been saved. Stay on this page and click Save Changes to keep them.",
    confirmLabel: "Discard Changes",
    variant: "primary",
  });
const discard = async () =>
  !(dirty.value || creationDirty.value) || (await confirmDiscard());
const resetNewForm = () => {
  newForm.value = {
    name: "",
    effective_date: "",
    source_reference: "",
    copy_from_id: "",
  };
  newFormBaseline.value = JSON.stringify(newForm.value);
};
const openCreate = () => {
  if (busy.value || showCreate.value) return;
  resetNewForm();
  showCreate.value = true;
};
const cancelCreate = async () => {
  if (busy.value || (creationDirty.value && !(await confirmDiscard()))) return;
  showCreate.value = false;
  resetNewForm();
};
const changeSelection = async (event: Event) => {
  const input = event.target as HTMLSelectElement;
  const id = Number(input.value);
  await choose(id);
  input.value = String(selected.value?.schedule.id ?? "");
};
const choose = async (id: number) => {
  if (busy.value || !(await discard())) return;
  busy.value = true;
  error.value = "";
  try {
    if (id) adopt(await salarySchedule(id));
    else {
      selected.value = null;
      baseline.value = "";
      preview.value = null;
      remember(null);
    }
    showCreate.value = false;
    resetNewForm();
  } catch (e) {
    error.value = compensationError(e);
  } finally {
    busy.value = false;
  }
};
const createDraft = async () => {
  if (busy.value || (dirty.value && !(await confirmDiscard()))) return;
  busy.value = true;
  error.value = "";
  try {
    adopt(
      await createSalarySchedule({
        ...newForm.value,
        name: newForm.value.name.trim(),
        copy_from_id: newForm.value.copy_from_id
          ? Number(newForm.value.copy_from_id)
          : null,
      }),
    );
    showCreate.value = false;
    resetNewForm();
    await loadList();
    notify("New salary draft saved. Edit its rates, then Save Changes.");
  } catch (e) {
    error.value = compensationError(e);
  } finally {
    busy.value = false;
  }
};
const setRate = (grade: number, step: number, value: string) => {
  if (!selected.value || !editable.value || busy.value) return;
  const row = selected.value.rates.find(
    (r) => Number(r.salary_grade) === grade && Number(r.step) === step,
  );
  if (row) row.salary = value;
  else selected.value.rates.push({ salary_grade: grade, step, salary: value });
  preview.value = null;
};
const addGrade = () => {
  const grade = Number(newGrade.value);
  if (
    !selected.value ||
    !editable.value ||
    busy.value ||
    !Number.isInteger(grade) ||
    grade < 1 ||
    grade > 33 ||
    grades.value.includes(grade)
  ) {
    error.value = "Enter a new salary grade from 1 to 33.";
    return;
  }
  for (let step = 1; step <= 8; step++)
    selected.value.rates.push({ salary_grade: grade, step, salary: "" });
  newGrade.value = "";
  preview.value = null;
  error.value = "";
};
const removeGrade = (grade: number) => {
  if (!editable.value || busy.value) return;
  if (selected.value)
    selected.value.rates = selected.value.rates.filter(
      (r) => Number(r.salary_grade) !== grade,
    );
  preview.value = null;
};
const saveMessage = ref(''), saveFailed = ref(false)
const showSaveFailure = (message: string) => { error.value = message; saveMessage.value = message; saveFailed.value = true }
const removeUnsupportedSteps = async () => {
  if (!selected.value || !editable.value || busy.value) return
  if (!(await confirmAction({ title: 'Remove unsupported step rows?', message: 'Remove only rows outside Steps 1–8 from this draft? Review the remaining amounts against your approved salary reference. The active schedule is unchanged.', confirmLabel: 'Remove Rows', variant: 'primary' }))) return
  selected.value.rates = selected.value.rates.filter(rate => Number(rate.step) >= 1 && Number(rate.step) <= 8)
  preview.value = null; error.value = ''; saveMessage.value = 'Unsupported rows removed from the form. Review the rates and Save Changes.'; saveFailed.value = false
}
const saveDraft = async () => {
  if (!selected.value || busy.value || !editable.value) return;
  saveMessage.value = ''; saveFailed.value = false
  if (!selected.value.schedule.name.trim()) { showSaveFailure('Enter a schedule name before saving.'); return }
  if (!/^\d{4}-\d{2}-\d{2}$/.test(selected.value.schedule.effective_date || '')) { showSaveFailure('Select an effective date before saving.'); return }
  if (unsupportedRates.value.length) { showSaveFailure('This draft contains unsupported step rows. Use Remove Unsupported Step Rows above, review the rates, then save again.'); return }
  const rates = selected.value.rates.map(rate => ({ salary_grade: Number(rate.salary_grade), step: Number(rate.step), salary: rate.salary === null || String(rate.salary).trim() === '' ? null : String(rate.salary).trim() }))
  const invalid = rates.find(rate => rate.salary !== null && (!/^\d{1,8}(\.\d{1,2})?$/.test(rate.salary) || Number(rate.salary) <= 0))
  if (invalid) { showSaveFailure(`Enter a positive amount with at most two decimal places for SG ${invalid.salary_grade}, Step ${invalid.step}, or leave it blank to save an incomplete draft.`); return }
  busy.value = true;
  error.value = "";
  try {
    const s = selected.value;
    adopt(
      await saveSalarySchedule(s.schedule.id, {
        name: s.schedule.name.trim(),
        effective_date: s.schedule.effective_date,
        source_reference: s.schedule.source_reference,
        revision: s.schedule.revision,
        rates,
      }),
    );
    const incomplete = rates.filter(rate => rate.salary === null).length
    saveMessage.value = incomplete ? `Draft saved successfully. ${incomplete} step(s) still need amounts before activation.` : 'Draft saved successfully. Your changes will remain when you return.'
    notify(saveMessage.value)
    try { await loadList() } catch { saveMessage.value += ' The schedule list could not refresh; your draft was saved.' }
  } catch (e) {
    showSaveFailure(compensationError(e));
  } finally {
    busy.value = false;
  }
};
const previewDraft = async () => {
  if (!selected.value || busy.value || dirty.value) return;
  busy.value = true;
  error.value = "";
  preview.value = null;
  try {
    preview.value = await previewSalarySchedule(
      selected.value.schedule.id,
      selected.value.schedule.revision,
    );
  } catch (e) {
    error.value = compensationError(e);
  } finally {
    busy.value = false;
  }
};
const reloadSelected = async () => {
  if (selected.value) await choose(selected.value.schedule.id);
};
const activate = async () => {
  if (
    !selected.value ||
    !preview.value?.can_activate ||
    dirty.value ||
    busy.value
  )
    return;
  const id = selected.value.schedule.id,
    revision = selected.value.schedule.revision,
    activeId = preview.value.active_schedule_id;
  busy.value = true;
  try {
    if (
      !(await confirmAction({
        title: "Activate salary schedule?",
        message: `Activate ${selected.value.schedule.name}? Future salary lookups will use these rates. Existing recorded salary amounts will remain unchanged.`,
        confirmLabel: "Activate",
        variant: "primary",
      }))
    )
      return;
    await activateSalarySchedule(id, revision, activeId);
    adopt(await salarySchedule(id));
    await loadList();
    notify("Salary schedule activated successfully.");
  } catch (e) {
    error.value = compensationError(e);
    preview.value = null;
  } finally {
    busy.value = false;
  }
};
const deleteDraft = async () => {
  if (!selected.value || busy.value || !editable.value) return;
  busy.value = true;
  try {
    if (
      !(await confirmAction({
        title: "Delete draft?",
        message:
          "This deletes this salary draft and its rates. The active schedule remains unchanged.",
        confirmLabel: "Delete draft",
        variant: "primary",
      }))
    )
      return;
    await deleteSalaryDraft(
      selected.value.schedule.id,
      selected.value.schedule.revision,
    );
    selected.value = null;
    baseline.value = "";
    preview.value = null;
    remember(null);
    await loadList();
    const active = schedules.value.find((s) => s.status === "active");
    if (active) adopt(await salarySchedule(active.id));
    notify("Draft deleted.");
  } catch (e) {
    error.value = compensationError(e);
  } finally {
    busy.value = false;
  }
};
const allowNavigation = async () => {
  if (busy.value) return false;
  return await discard();
};
onBeforeRouteLeave(async (to) =>
  to.path === "/login" ? true : await allowNavigation(),
);
onBeforeRouteUpdate(async (to, from) => {
  if (
    to.path !== from.path ||
    to.query.section !== from.query.section ||
    to.query.tab !== from.query.tab
  )
    return await allowNavigation();
  return true;
});
const warnBeforeUnload = (event: BeforeUnloadEvent) => {
  if (dirty.value || creationDirty.value || busy.value) {
    event.preventDefault();
    event.returnValue = "";
  }
};
onMounted(async () => {
  window.addEventListener("beforeunload", warnBeforeUnload);
  loading.value = true;
  try {
    await loadList();
    const remembered = rememberedId();
    const initial =
      schedules.value.find((s) => Number(s.id) === remembered) ??
      schedules.value.find((s) => s.status === "active") ??
      schedules.value[0];
    if (initial) adopt(await salarySchedule(initial.id));
  } catch (e) {
    error.value = compensationError(e);
  } finally {
    loading.value = false;
  }
});
onBeforeUnmount(() =>
  window.removeEventListener("beforeunload", warnBeforeUnload),
);
</script>
<style scoped>
.page-heading {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}
.schedule-notice {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  padding: 0.9rem 1rem;
  color: var(--text-muted);
  background: var(--surface-muted);
  border: 1px solid var(--border);
  border-radius: 0.7rem;
  font-size: 0.875rem;
}
.schedule-notice svg {
  flex-shrink: 0;
  color: var(--primary);
}
.draft-toolbar {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 0.8rem;
  padding: 1rem;
  background: var(--surface-muted);
  border: 1px solid var(--border);
  border-radius: 0.7rem;
  color: var(--text);
  font-size: 0.875rem;
}
.save-status {
  padding: 0.3rem 0.65rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
}
.unsaved {
  color: var(--warning, #a16207);
  background: var(--surface-muted);
  border: 1px solid var(--border);
}
.saved {
  color: var(--success, #15803d);
  background: var(--surface-muted);
  border: 1px solid var(--border);
}
.save-feedback { padding: .85rem 1rem; border-radius: .65rem; font-size: .875rem; font-weight: 500; }.save-success { background: var(--surface-muted); color: var(--success, #15803d); border: 1px solid var(--border); }
.panel {
  padding: 1.1rem;
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  background: var(--surface-muted);
}
label {
  display: grid;
  gap: 0.4rem;
  color: var(--text);
  font-size: 0.875rem;
  font-weight: 500;
}
input,
select {
  width: 100%;
  min-width: 0;
  padding: 0.65rem;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  color: var(--text);
  background: var(--surface);
}
button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  padding: 0.6rem 0.9rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  font-weight: 600;
}
.primary {
  background: var(--primary);
  color: white;
}
.secondary {
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text);
}
.danger {
  background: #b91c1c;
  color: white;
}
button:disabled,
input:disabled,
select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
button:focus-visible,
input:focus-visible,
select:focus-visible {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}
.hint {
  font-size: 0.875rem;
  color: var(--text-muted);
}
.error {
  padding: 0.8rem;
  background: #fef2f2;
  color: #b91c1c;
  border-radius: 0.5rem;
}
.badge {
  background: var(--surface-muted);
  color: var(--text);
  border: 1px solid var(--border);
  padding: 0.3rem 0.6rem;
  border-radius: 999px;
  text-transform: capitalize;
  font-size: 0.8rem;
}
.table-scroll {
  overflow: auto;
  max-height: 32rem;
}
table {
  width: 100%;
  min-width: 1120px;
  border-collapse: collapse;
}
th,
td {
  color: var(--text);
  padding: 0.6rem;
  border-bottom: 1px solid var(--border);
  font-size: 0.85rem;
  text-align: left;
}
th {
  background: var(--surface-muted);
}
td input {
  min-width: 105px;
}
.preview-table {
  min-width: 500px;
}
</style>
