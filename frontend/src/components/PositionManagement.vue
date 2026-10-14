<template>
  <section class="neo-card p-6 space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-semibold text-[var(--text)]">Positions</h2>
        <p class="text-sm text-[var(--text-muted)] mt-1">
          Manage position names, personnel types and assigned salary grades.
        </p>
      </div>
      <button
        class="primary"
        type="button"
        :disabled="busy"
        @click="editPosition(null)"
      >
        Add position
      </button>
    </div>
    <p v-if="error && !editing" role="alert" class="error">{{ error }}</p>
    <div class="filter-panel">
      <label class="search-filter">Search positions
        <div class="search-wrapper"><Search :size="17" aria-hidden="true" /><input v-model="search" type="search" placeholder="Search code, position or salary grade…" /></div>
      </label>
      <label>Status<select v-model="status"><option value="all">All statuses</option><option value="active">Active</option><option value="inactive">Inactive</option></select></label>
      <label>Personnel type<select v-model="personnelType"><option value="all">All personnel types</option><option value="Teaching">Teaching</option><option value="Non-Teaching">Non-Teaching</option><option value="School Head">School Head</option></select></label>
      <button type="button" class="secondary clear-filters" :disabled="!hasFilters" @click="clearFilters"><RotateCcw :size="15" aria-hidden="true" /> Clear Filters</button>
    </div>
    <p v-if="loading" class="hint">Loading positions…</p>
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>Code</th>
            <th>Position</th>
            <th>Personnel type</th>
            <th>Grade</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="position in displayed" :key="position.id">
            <td>{{ position.code }}</td>
            <td>{{ position.name }}</td>
            <td>{{ position.type }}</td>
            <td>
              {{
                position.salary_grade
                  ? `SG ${position.salary_grade}`
                  : "No grade"
              }}
            </td>
            <td>
              <span
                class="status-badge"
                :class="
                  position.is_active ? 'active-status' : 'inactive-status'
                "
                >{{ position.is_active ? "Active" : "Inactive" }}</span
              >
            </td>
            <td>
              <div class="position-actions">
              <button
                type="button"
                class="edit-action edit-button"
                :disabled="busy"
                @click="editPosition(position)"
              >
                <PenLine :size="14" aria-hidden="true" /> Edit
              </button>
              <button type="button" class="status-action" :class="position.is_active ? 'deactivate-action' : 'activate-action'" :disabled="busy" @click="toggleStatus(position)"><Power :size="14" aria-hidden="true" /> {{ position.is_active ? 'Deactivate' : 'Activate' }}</button>
              </div>
            </td>
          </tr>
          <tr v-if="!loading && !filtered.length">
            <td colspan="6">No positions match your search or filters.</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div
      class="flex flex-wrap items-center justify-between gap-3 text-sm text-[var(--text-muted)]"
    >
      <span
        >{{ filtered.length }} position(s) · Page {{ page }} of
        {{ pages }}</span
      >
      <div class="flex gap-2">
        <button
          type="button"
          class="secondary"
          :disabled="page <= 1"
          @click="page--"
        >
          Previous</button
        ><button
          type="button"
          class="secondary"
          :disabled="page >= pages"
          @click="page++"
        >
          Next
        </button>
      </div>
    </div>
    <Teleport to="body">
      <div
        v-if="editing"
        class="position-modal-overlay"
        @click.self="closeEditor"
        @keydown="handleDialogKey"
      >
        <div
          ref="dialogElement"
          class="position-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="position-modal-title"
          aria-describedby="position-modal-description"
          tabindex="-1"
        >
          <form class="position-form" @submit.prevent="save">
            <div class="modal-heading">
              <div class="heading-content">
                <span class="heading-icon"
                  ><PenLine :size="20" aria-hidden="true"
                /></span>
                <div>
                  <h3 id="position-modal-title">
                    {{ editingId ? "Edit Position" : "Add Position" }}
                  </h3>
                  <p id="position-modal-description" class="hint">
                    {{
                      editingId
                        ? "Update the selected position details."
                        : "Enter the details for a new personnel position."
                    }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="close-button"
                aria-label="Close position dialog"
                :disabled="busy"
                @click="closeEditor"
              >
                <X :size="20" aria-hidden="true" />
              </button>
            </div>
            <p v-if="error" role="alert" class="error">{{ error }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <label
                >Position code<input
                  v-model="form.code"
                  required
                  maxlength="30"
                  placeholder="Example: ADAS4"
                  :disabled="busy"
              /></label>
              <label
                >Position name<input
                  v-model="form.name"
                  required
                  maxlength="255"
                  :disabled="busy"
              /></label>
              <label
                >Personnel type<select
                  v-model="form.type"
                  :disabled="busy || assigned > 0"
                >
                  <option>Teaching</option>
                  <option>Non-Teaching</option>
                  <option>School Head</option>
                </select></label
              >
              <label
                >Salary grade<input
                  v-model="form.salary_grade"
                  type="number"
                  min="1"
                  max="33"
                  step="1"
                  placeholder="Enter salary grade"
                  :disabled="busy || assigned > 0"
              /></label>
              <label class="status-toggle"
                ><input
                  v-model="form.is_active"
                  type="checkbox"
                  :disabled="busy"
                  class="status-checkbox"
                />
                Active position</label
              >
            </div>
            <p v-if="assigned" class="hint">
              This position is referenced by {{ assigned }} record(s). Its type
              and grade are locked; deactivate it to stop new assignments.
              Existing employee records remain.
            </p>
            <div class="modal-actions">
              <button type="submit" class="primary" :disabled="busy">
                {{ busy ? "Saving…" : "Save position" }}</button
              ><button
                type="button"
                class="secondary"
                :disabled="busy"
                @click="closeEditor"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </section>
</template>
<script setup lang="ts">
import {
  ref,
  computed,
  onMounted,
  onBeforeUnmount,
  nextTick,
  watch,
} from "vue";
import { PenLine, X, Search, RotateCcw, Power } from "lucide-vue-next";
import {
  managedPositions,
  savePosition,
  setPositionStatus,
  compensationError,
  type ManagedPosition,
} from "@/services/compensation";
import { confirmAction, notify } from "@/composables/useNotifications";
const emit = defineEmits<{ changed: [] }>();
const rows = ref<ManagedPosition[]>([]),
  search = ref(""),
  status = ref("all"),
  personnelType = ref("all"),
  page = ref(1);
const busy = ref(false),
  loading = ref(false),
  error = ref(""),
  editing = ref(false),
  editingId = ref<number | null>(null),
  assigned = ref(0);
const dialogElement = ref<HTMLElement | null>(null);
let returnFocus: HTMLElement | null = null;
let previousOverflow = "";
let scrollLocked = false;
const closeEditor = () => {
  if (!busy.value) editing.value = false;
};
const handleDialogKey = (event: KeyboardEvent) => {
  if (event.key === "Escape") {
    event.preventDefault();
    event.stopPropagation();
    closeEditor();
    return;
  }
  if (event.key !== "Tab" || !dialogElement.value) return;
  const elements = Array.from(
    dialogElement.value.querySelectorAll<HTMLElement>(
      'button:not(:disabled), input:not(:disabled), select:not(:disabled), [tabindex="0"]',
    ),
  ).filter((el) => el.getClientRects().length > 0);
  const first = elements[0],
    last = elements[elements.length - 1];
  if (!first || !last) {
    event.preventDefault();
    dialogElement.value.focus();
    return;
  }
  if (
    event.shiftKey &&
    (document.activeElement === first ||
      document.activeElement === dialogElement.value)
  ) {
    event.preventDefault();
    last.focus();
  } else if (
    !event.shiftKey &&
    (document.activeElement === last ||
      document.activeElement === dialogElement.value)
  ) {
    event.preventDefault();
    first.focus();
  }
};
watch(editing, async (open) => {
  if (open) {
    previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    scrollLocked = true;
    await nextTick();
    if (editing.value)
      (
        dialogElement.value?.querySelector<HTMLElement>(
          "input:not(:disabled)",
        ) ?? dialogElement.value
      )?.focus();
  } else {
    if (scrollLocked) {
      document.body.style.overflow = previousOverflow;
      scrollLocked = false;
    }
    await nextTick();
    if (returnFocus?.isConnected) returnFocus.focus();
  }
});
onBeforeUnmount(() => {
  if (scrollLocked) document.body.style.overflow = previousOverflow;
});
const form = ref({
  code: "",
  name: "",
  type: "Teaching",
  salary_grade: "" as string | number,
  is_active: true,
});
const filtered = computed(() =>
  rows.value.filter(
    (p) =>
      (status.value === "all" || p.is_active === (status.value === "active")) &&
      (personnelType.value === "all" || p.type === personnelType.value) &&
      `${p.code} ${p.name} ${p.type} SG ${p.salary_grade ?? ""}`
        .toLowerCase()
        .includes(search.value.trim().toLowerCase()),
  ),
);
const hasFilters = computed(() => search.value !== '' || status.value !== 'all' || personnelType.value !== 'all');
const clearFilters = () => { search.value = ''; status.value = 'all'; personnelType.value = 'all'; page.value = 1; };
const pages = computed(() =>
  Math.max(1, Math.ceil(filtered.value.length / 10)),
);
const displayed = computed(() =>
  filtered.value.slice((page.value - 1) * 10, page.value * 10),
);
watch([search, status, personnelType], () => {
  page.value = 1;
});
watch(pages, (n) => {
  page.value = Math.min(page.value, n);
});
const load = async () => {
  loading.value = true;
  try {
    rows.value = await managedPositions();
  } catch (e) {
    error.value = compensationError(e);
  } finally {
    loading.value = false;
  }
};
const editPosition = (p: ManagedPosition | null) => {
  returnFocus =
    document.activeElement instanceof HTMLElement
      ? document.activeElement
      : null;
  error.value = "";
  editing.value = true;
  editingId.value = p?.id ?? null;
  assigned.value = p?.assigned_records ?? 0;
  form.value = {
    code: p?.code ?? "",
    name: p?.name ?? "",
    type: p?.type ?? "Teaching",
    salary_grade: p?.salary_grade ?? "",
    is_active: p?.is_active ?? true,
  };
};
const save = async () => {
  if (busy.value) return;
  const data = {
    ...form.value,
    code: form.value.code.trim().toUpperCase(),
    name: form.value.name.trim(),
    salary_grade:
      form.value.salary_grade === "" ? null : Number(form.value.salary_grade),
  };
  busy.value = true;
  error.value = "";
  try {
    if (
      editingId.value &&
      !data.is_active &&
      !(await confirmAction({
        title: "Deactivate position?",
        message:
          "Existing employee assignments remain. This position will no longer be available for new assignments.",
        confirmLabel: "Deactivate",
        variant: "primary",
      }))
    )
      return;
    await savePosition(editingId.value, data);
    editing.value = false;
    await load();
    emit("changed");
    notify("Position saved successfully.");
  } catch (e) {
    error.value = compensationError(e);
  } finally {
    busy.value = false;
  }
};
const toggleStatus = async (position: ManagedPosition) => {
  if (busy.value) return;
  const active = !position.is_active;
  busy.value = true; error.value = '';
  try {
    if (!(await confirmAction({ title: active ? 'Activate position?' : 'Deactivate position?', message: active ? `Make ${position.name} available for new personnel assignments again?` : `Deactivate ${position.name}? It will no longer be available for new assignments. Existing employee records and accounts remain.`, confirmLabel: active ? 'Activate' : 'Deactivate', variant: 'primary' }))) return;
    const result = await setPositionStatus(position.id, active);
    position.is_active = result.data.is_active;
    emit('changed');
    notify(active ? 'Position activated successfully.' : 'Position deactivated successfully.');
  } catch (e) { error.value = compensationError(e); } finally { busy.value = false; }
};
onMounted(load);
</script>
<style scoped>
.position-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
.edit-action, .status-action { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; min-height: 36px; white-space: nowrap; border: 1px solid transparent; transition: background-color .2s, color .2s; }
.edit-action { background: var(--primary, #2563eb); color: #fff; border-color: var(--primary, #2563eb); }
.edit-action:hover:not(:disabled) { background: var(--primary-hover, #1d4ed8); border-color: var(--primary-hover, #1d4ed8); }
.status-action { background: var(--surface); }
.deactivate-action { color: var(--danger, #dc2626); border-color: var(--danger, #dc2626); }
.deactivate-action:hover:not(:disabled) { background: var(--danger, #dc2626); color: #fff; }
.activate-action { color: var(--success, #15803d); border-color: var(--success, #15803d); }
.activate-action:hover:not(:disabled) { background: var(--success, #15803d); color: #fff; }

.filter-panel { display: grid; grid-template-columns: minmax(220px, 1fr) minmax(140px, 180px) minmax(170px, 220px) auto; gap: 1rem; align-items: end; padding: 1rem; background: var(--surface-muted); border: 1px solid var(--border); border-radius: .75rem; }
.search-wrapper { position: relative; }.search-wrapper svg { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none; }.search-wrapper input { padding-left: 2.3rem; }
.filter-panel input, .filter-panel select, .clear-filters { min-height: 42px; }
.clear-filters { display: inline-flex; justify-content: center; align-items: center; gap: .4rem; white-space: nowrap; }
@media (max-width: 1100px) { .filter-panel { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) { .filter-panel { grid-template-columns: minmax(0, 1fr); }.clear-filters { width: 100%; } }

.position-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(3px);
}
.position-modal {
  width: 100%;
  max-width: 620px;
  max-height: calc(100dvh - 2.5rem);
  overflow-y: auto;
  border: 1px solid var(--border, #cbd8e8);
  border-radius: 1rem;
  background: var(--surface, #fff);
  color: var(--text);
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
}
.position-form {
  padding: 1.5rem;
  display: grid;
  gap: 1.25rem;
}
.modal-heading {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border);
}
.heading-content {
  display: flex;
  align-items: center;
  gap: 0.8rem;
}
.heading-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.65rem;
  border-radius: 0.7rem;
  background: var(--surface-muted);
  color: var(--primary);
  flex-shrink: 0;
}
h3 {
  font-weight: 700;
  font-size: 1.2rem;
  color: var(--text);
}
.modal-heading .hint {
  margin-top: 0.2rem;
}
.close-button {
  background: transparent;
  color: var(--text-muted);
  padding: 0.35rem;
}
.close-button:hover {
  background: var(--surface-muted);
}
.modal-actions {
  display: flex;
  flex-direction: row-reverse;
  gap: 0.6rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border);
}
.status-toggle {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  grid-column: 1 / -1;
  padding: 0.8rem;
  background: var(--surface-muted);
  border: 1px solid var(--border);
  border-radius: 0.6rem;
}
.status-checkbox {
  accent-color: var(--primary);
}
.edit-button {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}
.status-badge {
  display: inline-flex;
  border-radius: 999px;
  padding: 0.25rem 0.7rem;
  font-size: 0.75rem;
  font-weight: 600;
}
.active-status {
  background: var(--surface-muted);
  color: var(--success, #15803d);
}
.inactive-status {
  background: var(--surface-muted);
  color: var(--text-muted);
}
@media (max-width: 640px) {
  .position-modal-overlay {
    padding: 0.75rem;
  }
  .position-modal {
    max-height: calc(100dvh - 1.5rem);
  }
  .position-form {
    padding: 1rem;
  }
  .modal-actions {
    flex-direction: column;
  }
  .modal-actions button {
    width: 100%;
  }
}
label {
  display: grid;
  gap: 0.45rem;
  color: var(--text);
  font-size: 0.875rem;
  font-weight: 500;
}
input,
select {
  width: 100%;
  min-width: 0;
  padding: 0.65rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  color: var(--text);
  background: var(--surface);
}
.status-checkbox {
  width: auto;
}
.status-filter {
  width: auto;
}
button {
  padding: 0.55rem 0.85rem;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 0.85rem;
}
.primary {
  background: var(--primary);
  color: #fff;
}
.secondary {
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
}
button:disabled,
input:disabled,
select:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}
button:focus-visible,
input:focus-visible,
select:focus-visible {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}
.hint {
  color: var(--text-muted);
  font-size: 0.85rem;
}
.error {
  background: #fef2f2;
  color: #b91c1c;
  padding: 0.75rem;
  border-radius: 0.5rem;
}
.table-scroll {
  overflow-x: auto;
}
table {
  width: 100%;
  min-width: 680px;
  border-collapse: collapse;
}
th,
td {
  padding: 0.8rem;
  border-bottom: 1px solid var(--border);
  text-align: left;
  color: var(--text);
  font-size: 0.85rem;
}
th {
  background: var(--surface-muted);
}
</style>
