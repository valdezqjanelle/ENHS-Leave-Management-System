<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { CheckCircle2, Clock, FileText, Plus, RefreshCw, XCircle } from "lucide-vue-next";
import { getEmployeeCalendar, getEmployeeOverview } from "@/services/dashboard";
import StatCard from "./StatCard.vue";
import SectionCard from "./SectionCard.vue";
import StatusBadge from "./StatusBadge.vue";
import DonutChart from "./DonutChart.vue";
import LeaveCalendar, { type CalendarEvent } from "./LeaveCalendar.vue";

const router = useRouter();

const errMsg = (e: any, fallback: string) => e?.response?.data?.message || fallback;

const fmt = (d?: string | null) =>
  d
    ? new Date(d + "T00:00:00").toLocaleDateString("en-PH", { month: "short", day: "numeric", year: "numeric" })
    : "—";
const fmtRange = (a?: string | null, b?: string | null) =>
  a && b && a !== b ? `${fmt(a)} – ${fmt(b)}` : fmt(a);
const fmtDate = (iso?: string | null) =>
  iso ? new Date(iso).toLocaleDateString("en-PH", { month: "short", day: "numeric", year: "numeric" }) : "";

const todayLabel = new Date().toLocaleDateString("en-PH", {
  weekday: "long", month: "long", day: "numeric", year: "numeric",
});

/* ---------- overview ---------- */
const data = ref<any>(null);
const loading = ref(true);
const error = ref<string | null>(null);

const load = async () => {
  loading.value = true;
  error.value = null;
  try {
    data.value = (await getEmployeeOverview()).data;
  } catch (e) {
    error.value = errMsg(e, "Could not load your dashboard.");
  } finally {
    loading.value = false;
  }
};

/* ---------- calendar ---------- */
const events = ref<CalendarEvent[]>([]);
const calLoading = ref(true);
const calError = ref<string | null>(null);
const calMonth = ref("");

const loadCalendar = async (month = calMonth.value) => {
  if (!month) return;
  calMonth.value = month;
  calLoading.value = true;
  calError.value = null;
  try {
    events.value = (await getEmployeeCalendar(month)).data.events ?? [];
  } catch (e) {
    calError.value = errMsg(e, "Could not load your leave calendar.");
  } finally {
    calLoading.value = false;
  }
};

const refresh = () => {
  load();
  loadCalendar();
};

onMounted(load);

const balanceCards = computed(() => {
  const b = data.value?.balances;
  return [
    { key: "vacation", label: "Vacation Leave", value: b ? b.vacation : null },
    { key: "sick", label: "Sick Leave", value: b ? b.sick : null },
    { key: "local", label: "Local Credits", value: b ? b.local : null },
  ];
});

const total = computed(() => data.value?.summary?.total ?? 0);

const goApply = () => router.push("/leave-application");
const openApplication = (id: number) => router.push({ path: "/my-applications", query: { open: String(id) } });
const s = computed(() => data.value?.summary);
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-semibold tracking-tight" style="color: var(--text);">
          My Dashboard
        </h1>
        <p class="text-sm mt-1" style="color: var(--text-muted);">
          <template v-if="data">Welcome back, {{ data.greetingName }}. </template>{{ todayLabel }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition-colors" style="border-color: var(--border); background: var(--surface); color: var(--text);" :disabled="loading" @click="refresh" :style="loading ? 'opacity: 0.6;' : ''">
          <RefreshCw class="h-4 w-4" :class="loading ? 'animate-spin' : ''" aria-hidden="true" /> Refresh
        </button>
        <button type="button" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors hover:opacity-90" style="background: var(--primary);" @click="goApply">
          <Plus class="h-4 w-4" aria-hidden="true" /> Apply for Leave
        </button>
      </div>
    </div>

    <!-- Whole-page error -->
    <div v-if="error" class="rounded-lg border p-4 text-sm" style="border-color: var(--danger); background: rgba(214, 69, 69, 0.1); color: var(--danger);" role="alert">
      {{ error }}
      <button type="button" class="ml-2 underline" @click="load">Retry</button>
    </div>

    <template v-else>
      <!-- Balances -->
      <section aria-label="Leave balances">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div v-for="b in balanceCards" :key="b.key" class="rounded-lg border p-4" style="border-color: var(--border); background: var(--surface);">
            <p class="text-sm" style="color: var(--text-muted);">{{ b.label }}</p>
            <div v-if="loading" class="mt-1 h-8 w-16 animate-pulse rounded" style="background: var(--surface-muted);" />
            <p v-else-if="b.value === null" class="text-sm" style="color: var(--text-muted);">Balance unavailable</p>
            <p v-else class="text-3xl font-semibold" style="color: var(--text);">
              {{ b.value.toFixed(3) }} <span class="text-sm font-normal" style="color: var(--text-muted);">days</span>
            </p>
          </div>
        </div>
        <p v-if="!loading && data && !data.balances" class="mt-2 text-xs" style="color: var(--text-muted);">
          No leave balance record has been created for you yet. Please contact the administration office.
        </p>
      </section>

      <!-- Application summary -->
      <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <StatCard label="Pending" :value="s?.pending ?? null" :icon="Clock" tone="amber" to="/my-applications?status=pending" :loading="loading" />
        <StatCard label="Approved" :value="s?.approved ?? null" :icon="CheckCircle2" tone="green" to="/my-applications?status=approved" :loading="loading" />
        <StatCard label="Disapproved" :value="s?.disapproved ?? null" :icon="XCircle" tone="red" to="/my-applications?status=disapproved" :loading="loading" />
        <StatCard label="Total Applications" :value="s?.total ?? null" :icon="FileText" tone="blue" to="/my-applications" :loading="loading" />
      </div>

      <!-- Recent applications + status chart -->
      <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <SectionCard class="xl:col-span-2" title="Recent Applications" :loading="loading" :empty="!data?.recentApplications?.length" empty-message="You have not filed any leave applications yet.">
          <template #actions>
            <router-link to="/my-applications" class="text-sm font-medium hover:underline" style="color: var(--primary);">View all</router-link>
          </template>
          <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
              <thead class="text-xs uppercase" style="color: var(--text-muted);">
                <tr>
                  <th class="py-2 pr-3 font-medium">Filed</th>
                  <th class="py-2 pr-3 font-medium">Leave Type</th>
                  <th class="py-2 pr-3 font-medium">Requested Dates</th>
                  <th class="py-2 pr-3 font-medium">Days</th>
                  <th class="py-2 pr-3 font-medium">Status</th>
                  <th class="py-2 font-medium"><span class="sr-only">Action</span></th>
                </tr>
              </thead>
              <tbody class="divide-y" style="border-color: var(--border);">
                <tr v-for="a in data.recentApplications" :key="a.id" style="color: var(--text);">
                  <td class="whitespace-nowrap py-2 pr-3">{{ fmt(a.date_filed) }}</td>
                  <td class="max-w-[160px] truncate py-2 pr-3" :title="a.leave_type">{{ a.leave_type }}</td>
                  <td class="whitespace-nowrap py-2 pr-3">{{ fmtRange(a.start_date, a.end_date) }}</td>
                  <td class="py-2 pr-3">{{ a.days }}</td>
                  <td class="py-2 pr-3"><StatusBadge :status="a.status" /></td>
                  <td class="py-2 text-right">
                    <button type="button" class="text-sm font-medium hover:underline" style="color: var(--primary);" @click="openApplication(a.id)">View Details</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </SectionCard>

        <SectionCard title="My Applications by Status" :loading="loading" :empty="!total" empty-message="No applications to chart yet.">
          <DonutChart v-if="data"
            :labels="['Pending', 'Approved', 'Disapproved']"
            :values="[data.statusChart.pending, data.statusChart.approved, data.statusChart.disapproved]"
            :colors="['#D99117', '#16865B', '#D64545']" />
        </SectionCard>
      </div>

      <!-- Upcoming + updates -->
      <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <SectionCard title="Upcoming Approved Leave" :loading="loading" :empty="!data?.upcomingLeave?.length">
          <template #default>
            <ul class="divide-y" style="border-color: var(--border);">
              <li v-for="u in data.upcomingLeave" :key="u.id">
                <button type="button" class="w-full py-2 text-left transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50" @click="openApplication(u.id)">
                  <p class="text-sm font-medium" style="color: var(--text);">{{ u.leave_type }}</p>
                  <p class="text-xs" style="color: var(--text);">{{ fmtRange(u.start_date, u.end_date) }} · {{ u.days }} working day{{ u.days == 1 ? "" : "s" }}</p>
                </button>
              </li>
            </ul>
          </template>
        </SectionCard>

        <SectionCard title="Application Updates" :loading="loading" :empty="!data?.statusUpdates?.length" empty-message="No reviewed applications yet.">
          <ul class="space-y-3">
            <li v-for="u in data.statusUpdates" :key="u.id" class="text-sm">
              <div class="flex items-center gap-2">
                <StatusBadge :status="u.status" />
                <span style="color: var(--text);">{{ u.leave_type }}</span>
              </div>
              <p class="text-xs" style="color: var(--text-muted);">{{ fmtDate(u.reviewed_at) }}</p>
              <p v-if="u.reason" class="text-xs" style="color: var(--text);">Reason: {{ u.reason }}</p>
            </li>
          </ul>
        </SectionCard>

        <SectionCard title="My Leave Calendar" subtitle="Approved leave only">
          <LeaveCalendar :events="events" :loading="calLoading" :error="calError"
            @month-change="loadCalendar" @retry="loadCalendar()" @select="(ev) => openApplication(ev.id)" />
        </SectionCard>
      </div>
    </template>
  </div>
</template>
