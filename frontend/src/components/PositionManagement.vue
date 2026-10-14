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
    <p v-if="error" role="alert" class="error">{{ error }}</p>
    <form v-if="editing" class="position-form" @submit.prevent="save">
      <h3 class="font-semibold text-[var(--text)]">
        {{ editingId ? "Edit position" : "New position" }}
      </h3>
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
            placeholder="Optional if not schedule-based"
            :disabled="busy || assigned > 0"
        /></label>
        <label class="flex items-center gap-2"
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
        This position is referenced by {{ assigned }} record(s). Its type and
        grade are locked; deactivate it to stop new assignments. Existing
        employee records remain.
      </p>
      <div class="flex gap-2">
        <button type="submit" class="primary" :disabled="busy">
          {{ busy ? "Saving…" : "Save position" }}</button
        ><button
          type="button"
          class="secondary"
          :disabled="busy"
          @click="editing = false"
        >
          Cancel
        </button>
      </div>
    </form>
    <div class="flex flex-wrap gap-3">
      <input
        v-model="search"
        class="search flex-1"
        type="search"
        placeholder="Search code, position or salary grade…"
        aria-label="Search positions"
      /><select
        v-model="status"
        class="status-filter"
        aria-label="Filter position status"
      >
        <option value="all">All positions</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
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
            <td>{{ position.is_active ? "Active" : "Inactive" }}</td>
            <td>
              <button
                type="button"
                class="secondary"
                :disabled="busy"
                @click="editPosition(position)"
              >
                Edit
              </button>
            </td>
          </tr>
          <tr v-if="!loading && !filtered.length">
            <td colspan="6">No positions match your search.</td>
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
  </section>
</template>
<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import {
  managedPositions,
  savePosition,
  compensationError,
  type ManagedPosition,
} from "@/services/compensation";
import { confirmAction, notify } from "@/composables/useNotifications";
const emit = defineEmits<{ changed: [] }>();
const rows = ref<ManagedPosition[]>([]),
  search = ref(""),
  status = ref("all"),
  page = ref(1);
const busy = ref(false),
  loading = ref(false),
  error = ref(""),
  editing = ref(false),
  editingId = ref<number | null>(null),
  assigned = ref(0);
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
      `${p.code} ${p.name} ${p.type} SG ${p.salary_grade ?? ""}`
        .toLowerCase()
        .includes(search.value.trim().toLowerCase()),
  ),
);
const pages = computed(() =>
  Math.max(1, Math.ceil(filtered.value.length / 10)),
);
const displayed = computed(() =>
  filtered.value.slice((page.value - 1) * 10, page.value * 10),
);
watch([search, status], () => {
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
onMounted(load);
</script>
<style scoped>
.position-form {
  padding: 1.25rem;
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  background: var(--surface-muted);
  display: grid;
  gap: 1rem;
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
