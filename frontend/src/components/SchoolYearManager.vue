<template>
  <section class="neo-card p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="font-semibold">School Year Management</h3>
        <p class="text-sm">
          Latest processed school year: {{ years[0]?.name || "Not yet set" }}
        </p>
      </div>
      <button type="button" class="sy-button" @click="open = !open">
        {{ open ? "Close" : "Manage School Year" }}
      </button>
    </div>
    <p v-if="error" class="sy-error mt-3" role="alert">{{ error }}</p>
    <div v-if="open" class="mt-5 space-y-4">
      <p class="text-sm">
        VL carries over and receives 15.000 days. SL resets; explicitly select
        its new grant after checking the agency policy. Local Credits and
        lifetime Used Leave remain unchanged.
      </p>
      <form
        class="grid grid-cols-1 md:grid-cols-2 gap-3"
        @submit.prevent="preview"
      >
        <label
          >School year
          <input
            v-model="form.name"
            placeholder="2026-2027"
            required
            pattern="[0-9]{4}-[0-9]{4}"
        /></label>
        <label
          >SL allocation after reset
          <select v-model="form.sl_grant" required>
            <option value="">Select confirmed policy</option>
            <option value="0">Reset only — opening SL 0.000</option>
            <option value="15">Reset + annual grant — opening SL 15.000</option>
          </select>
        </label>
        <label
          >School-year start
          <input v-model="form.start_date" type="date" required
        /></label>
        <label
          >School-year end <input v-model="form.end_date" type="date" required
        /></label>
        <button class="sy-button" type="submit" :disabled="busy">
          Preview New School Year
        </button>
      </form>
      <template v-if="result">
        <p v-if="result.pending_old_applications" class="sy-error">
          Resolve {{ result.pending_old_applications }} pending application(s)
          dated before the new school year before activation.
        </p>
        <p class="text-sm">
          Employees with missing hire dates or hired after the start date are
          unchecked for review. Selecting a reviewed employee applies the full
          displayed grant; no prorating is assumed. Archived balances cannot be
          selected.
        </p>
        <div class="sy-scroll">
          <table>
            <thead>
              <tr>
                <th>Include</th>
                <th>Employee</th>
                <th>Date hired</th>
                <th>Current VL</th>
                <th>New VL</th>
                <th>Current SL</th>
                <th>New SL</th>
                <th>Service Credits</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in result.rows" :key="row.employee_id">
                <td>
                  <input
                    v-model="selected"
                    type="checkbox"
                    :value="row.employee_id"
                    :disabled="!row.can_process || row.balance_deleted || busy"
                  />
                </td>
                <td>
                  {{ row.employee_name }}
                  <strong v-if="row.needs_review">— Review required</strong>
                </td>
                <td>{{ row.date_hired || "Missing" }}</td>
                <td>{{ row.before.vacation_balance }}</td>
                <td>
                  {{
                    selected.includes(row.employee_id)
                      ? row.after.vacation_balance
                      : row.before.vacation_balance
                  }}
                </td>
                <td>{{ row.before.sick_balance }}</td>
                <td>
                  {{
                    selected.includes(row.employee_id)
                      ? row.after.sick_balance
                      : row.before.sick_balance
                  }}
                </td>
                <td>{{ row.before.service_credits }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <label
          >Policy reference / review note<textarea
            v-model="reviewNote"
            placeholder="Record the confirmed SL policy, employee eligibility review and annual grants checked."
            minlength="10"
            maxlength="2000"
          />
        </label>
        <label class="sy-check"
          ><input v-model="confirmed" type="checkbox" /> I verified the policy,
          employee eligibility and that these annual grants have not already
          been credited.</label
        >
        <p class="text-sm">
          Excluded employees keep all existing balances. Any later allocation
          for them must be reviewed and recorded separately; this school year
          cannot be processed again.
        </p>
        <button
          type="button"
          class="sy-button"
          :disabled="
            busy ||
            !confirmed ||
            reviewNote.trim().length < 10 ||
            !selected.length ||
            result.pending_old_applications > 0
          "
          @click="activate"
        >
          Confirm &amp; Activate School Year
        </button>
      </template>
      <div class="sy-history">
        <h4 class="font-semibold">School Year History</h4>
        <p v-if="!years.length">No processed school years yet.</p>
        <button
          v-for="year in years"
          :key="year.id"
          class="sy-history-button"
          type="button"
          :disabled="busy"
          @click="viewHistory(year.id)"
        >
          {{ year.name }} — {{ year.processed_at }}
        </button>
        <div v-if="history" class="sy-scroll mt-3">
          <p>
            {{ history.school_year.name }} · Processed by user #{{
              history.school_year.processed_by
            }}
            · {{ history.school_year.processed_at }}
          </p>
          <table>
            <thead>
              <tr>
                <th>Employee</th>
                <th>Action</th>
                <th>VL before → after</th>
                <th>SL before → after</th>
                <th>Local Credits</th>
                <th>Review note</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="entry in history.entries" :key="entry.id">
                <td>{{ entry.employee_name }}</td>
                <td>{{ entry.processed ? "Processed" : "Excluded" }}</td>
                <td>
                  {{ entry.before_balances.vacation_balance }} →
                  {{ entry.after_balances.vacation_balance }}
                </td>
                <td>
                  {{ entry.before_balances.sick_balance }} →
                  {{ entry.after_balances.sick_balance }}
                </td>
                <td>{{ entry.after_balances.service_credits }}</td>
                <td>{{ entry.review_note }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { confirmAction } from "@/composables/useNotifications";
import { onMounted, ref, watch } from "vue";
import axios from "axios";
interface Balances {
  vacation_balance: string;
  sick_balance: string;
  service_credits: string;
}
interface PreviewRow {
  employee_id: number;
  employee_name: string;
  date_hired: string | null;
  needs_review: boolean;
  can_process: boolean;
  balance_deleted: boolean;
  before: Balances;
  after: Balances;
}
interface Year {
  id: number;
  name: string;
  processed_at: string;
  processed_by: number;
}
interface Preview {
  rows: PreviewRow[];
  pending_old_applications: number;
  preview_token: string;
}
interface HistoryEntry {
  id: number;
  employee_name: string;
  processed: boolean;
  before_balances: Balances;
  after_balances: Balances;
  review_note: string;
}
const emit = defineEmits<{ (e: "processed"): void }>();
const base =
  "https://enhs-leave-management-system.onrender.com/api/leave-school-years";
const headers = () => ({
  headers: {
    Authorization: `Bearer ${localStorage.getItem("token")}`,
    Accept: "application/json",
  },
});
const open = ref(false),
  busy = ref(false),
  error = ref(""),
  confirmed = ref(false),
  reviewNote = ref("");
const years = ref<Year[]>([]),
  selected = ref<number[]>([]),
  result = ref<Preview | null>(null);
const history = ref<{ school_year: Year; entries: HistoryEntry[] } | null>(
  null,
);
const form = ref({ name: "", start_date: "", end_date: "", sl_grant: "" });
watch(
  form,
  () => {
    result.value = null;
    confirmed.value = false;
  },
  { deep: true },
);
const message = (e: unknown) => {
  if (axios.isAxiosError(e)) {
    const data = e.response?.data;
    const details = data?.errors
      ? Object.values(data.errors).flat().join(" ")
      : "";
    return details || data?.message || "Unable to process school year.";
  }
  return "Unable to process school year.";
};
const load = async () => {
  years.value = (await axios.get(base, headers())).data;
};
const preview = async () => {
  if (busy.value) return;
  busy.value = true;
  error.value = "";
  result.value = null;
  confirmed.value = false;
  try {
    result.value = (
      await axios.post(`${base}/preview`, form.value, headers())
    ).data;
    selected.value = result
      .value!.rows.filter(
        (r) => !r.needs_review && r.can_process && !r.balance_deleted,
      )
      .map((r) => r.employee_id);
  } catch (e) {
    error.value = message(e);
  } finally {
    busy.value = false;
  }
};
const activate = async () => {
  if (busy.value || !result.value || !confirmed.value) return;
  if (
    !(await confirmAction({
      title: "Apply school-year allocations?",
      message:
        "Apply the displayed allocations and SL resets? Previous balances will be archived.",
      confirmLabel: "Apply allocations",
      variant: "primary",
    }))
  )
    return;
  busy.value = true;
  error.value = "";
  try {
    await axios.post(
      `${base}/activate`,
      {
        ...form.value,
        preview_token: result.value.preview_token,
        employee_ids: selected.value,
        review_note: reviewNote.value,
        confirmed: true,
      },
      headers(),
    );
    result.value = null;
    confirmed.value = false;
    emit("processed");
    await load();
  } catch (e) {
    error.value = message(e);
  } finally {
    busy.value = false;
  }
};
const viewHistory = async (id: number) => {
  if (busy.value) return;
  busy.value = true;
  error.value = "";
  try {
    history.value = (await axios.get(`${base}/${id}/history`, headers())).data;
  } catch (e) {
    error.value = message(e);
  } finally {
    busy.value = false;
  }
};
onMounted(async () => {
  try {
    await load();
  } catch (e) {
    error.value = message(e);
  }
});
</script>

<style scoped>
section {
  color: var(--text);
}
label {
  display: block;
  font-size: 0.875rem;
}
input:not([type="checkbox"]),
select,
textarea {
  display: block;
  width: 100%;
  padding: 0.6rem;
  margin-top: 0.3rem;
  border: 1px solid var(--border, #cbd5e1);
  border-radius: 0.5rem;
  background: var(--surface, white);
  color: var(--text);
}
textarea {
  min-height: 80px;
}
.sy-button {
  background: #2563eb;
  color: white;
  padding: 0.65rem 1rem;
  border-radius: 0.5rem;
}
button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.sy-error {
  color: #dc2626;
}
.sy-scroll {
  overflow-x: auto;
}
table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}
th,
td {
  padding: 0.65rem;
  text-align: left;
  border-bottom: 1px solid var(--border, #cbd5e1);
  white-space: nowrap;
}
.sy-check {
  display: flex;
  gap: 0.5rem;
  align-items: flex-start;
}
.sy-check input {
  margin-top: 0.2rem;
}
.sy-history {
  border-top: 1px solid var(--border, #cbd5e1);
  padding-top: 1rem;
}
.sy-history-button {
  display: block;
  padding: 0.5rem 0;
  color: var(--primary, #2563eb);
  text-align: left;
}
</style>
