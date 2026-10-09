<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { CalendarCheck, CheckCircle2, Clock, RefreshCw, Users } from "lucide-vue-next";
import {
  getAdminActivity,
  getAdminAnalytics,
  getAdminCalendar,
  getAdminOverview,
  type DashboardPeriod,
} from "@/services/dashboard";
import StatCard from "./StatCard.vue";
import SectionCard from "./SectionCard.vue";
import StatusBadge from "./StatusBadge.vue";
import DonutChart from "./DonutChart.vue";
import BarChart from "./BarChart.vue";
import LeaveCalendar, { type CalendarEvent } from "./LeaveCalendar.vue";

const router = useRouter();

/* ---------- helpers ---------- */
const errMsg = (e: any, fallback: string) =>
  e?.response?.data?.message || fallback;

const fmt = (d?: string | null) =>
  d
    ? new Date(d + "T00:00:00").toLocaleDateString("en-PH", { month: "short", day: "numeric", year: "numeric" })
    : "—";

const fmtRange = (a?: string | null, b?: string | null) =>
  a && b && a !== b ? `${fmt(a)} – ${fmt(b)}` : fmt(a);

const fmtDateTime = (iso?: string | null) =>
  iso
    ? new Date(iso).toLocaleString("en-PH", { month: "short", day: "numeric", hour: "numeric", minute: "2-digit" })
    : "";

const todayLabel = new Date().toLocaleDateString("en-PH", {
  weekday: "long", month: "long", day: "numeric", year: "numeric",
});

/* ---------- overview (cards, pending, upcoming) ---------- */
const overview = ref<any>(null);
const overviewLoading = ref(true);
const overviewError = ref<string | null>(null);

const loadOverview = async () => {
  overviewLoading.value = true;
  overviewError.value = null;
  try {
    overview.value = (await getAdminOverview()).data;
  } catch (e) {
    overviewError.value = errMsg(e, "Could not load the dashboard summary.");
  } finally {
    overviewLoading.value = false;
  }
};

/* ---------- analytics ---------- */
const period = ref<DashboardPeriod>("this_year");
const analytics = ref<any>(null);
const analyticsLoading = ref(true);
const analyticsError = ref<string | null>(null);

const periodOptions: { value: DashboardPeriod; label: string }[] = [
  { value: "this_month", label: "This month" },
  { value: "last_3_months", label: "Last 3 months" },
  { value: "this_year", label: "This year" },
  { value: "all", label: "All time" },
];

const loadAnalytics = async () => {
  analyticsLoading.value = true;
  analyticsError.value = null;
  try {
    analytics.value = (await getAdminAnalytics(period.value)).data;
  } catch (e) {
    analyticsError.value = errMsg(e, "Could not load analytics.");
  } finally {
    analyticsLoading.value = false;
  }
};

const statusTotal = computed(() =>
  analytics.value ? Object.values<number>(analytics.value.statusChart).reduce((a, b) => a + b, 0) : 0,
);

const typeTotal = computed(() =>
  (analytics.value?.leaveByType ?? []).reduce((a: number, t: any) => a + t.count, 0),
);

// Keep the chart readable: top 8 leave types, the rest grouped as "Others".
const typeRows = computed(() => {
  const rows: { name: string; count: number }[] = analytics.value?.leaveByType ?? [];
  if (rows.length <= 8) return rows;
  const top = rows.slice(0, 7);
  const rest = rows.slice(7).reduce((a, r) => a + r.count, 0);
  return [...top, { name: "Others", count: rest }];
});

const monthlyHasData = computed(() => (analytics.value?.monthly ?? []).some((m: any) => m.total > 0));

/* ---------- activity ---------- */
const activity = ref<any[]>([]);
const activityLoading = ref(true);
const activityError = ref<string | null>(null);

const loadActivity = async () => {
  activityLoading.value = true;
  activityError.value = null;
  try {
    activity.value = (await getAdminActivity()).data.activities ?? [];
  } catch (e) {
    activityError.value = errMsg(e, "Could not load recent activity.");
  } finally {
    activityLoading.value = false;
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
    events.value = (await getAdminCalendar(month)).data.events ?? [];
  } catch (e) {
    calError.value = errMsg(e, "Could not load the leave calendar.");
  } finally {
    calLoading.value = false;
  }
};

/* ---------- refresh ---------- */
const refreshing = computed(
  () => overviewLoading.value || analyticsLoading.value || activityLoading.value || calLoading.value,
);
const refreshAll = () => {
  loadOverview();
  loadAnalytics();
  loadActivity();
  loadCalendar();
};

onMounted(() => {
  loadOverview();
  loadAnalytics();
  loadActivity();
  // calendar loads itself through LeaveCalendar's initial "month-change"
});

/* ---------- navigation ---------- */
const openApplication = (id: number) => router.push(`/leave-print/${id}`);
const reviewPending = (employee: string) =>
  router.push({ path: "/admin-applications", query: { tab: "pending", search: employee } });

const s = computed(() => overview.value?.summary);
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Admin Dashboard</h1>
        <p class="text-sm text-slate-600 dark:text-slate-300">
          <template v-if="overview">Welcome back, {{ overview.greetingName }}. </template>{{ todayLabel }}
        </p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 disabled:opacity-60 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200"
        :disabled="refreshing"
        @click="refreshAll"
      >
        <RefreshCw class="h-4 w-4" :class="refreshing ? 'animate-spin' : ''" aria-hidden="true" />
        Refresh
      </button>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <StatCard label="Total Active Personnel" :value="s?.activePersonnel ?? null" :icon="Users" tone="blue"
        to="/employees?status=active" :loading="overviewLoading" :error="!!overviewError" />
      <StatCard label="Pending Applications" :value="s?.pendingApplications ?? null" :icon="Clock" tone="amber"
        to="/admin-applications?tab=pending" :loading="overviewLoading" :error="!!overviewError" />
      <StatCard label="Approved This Month" :value="s?.approvedThisMonth ?? null" :icon="CheckCircle2" tone="green"
        to="/admin-applications?tab=approved" :loading="overviewLoading" :error="!!overviewError" />
      <StatCard label="Personnel on Leave Today" :value="s?.onLeaveToday ?? null" :icon="CalendarCheck" tone="indigo"
        to="/admin-applications?tab=approved" hint="Based on approved leave dates" :loading="overviewLoading" :error="!!overviewError" />
    </div>

    <!-- Pending + upcoming -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
      <SectionCard
        class="xl:col-span-2"
        title="Pending Applications"
        subtitle="Waiting longest first"
        :loading="overviewLoading"
        :error="overviewError"
        :empty="!overview?.pendingApplications?.length"
        empty-message="No applications are waiting for review."
        @retry="loadOverview"
      >
        <template #actions>
          <router-link to="/admin-applications?tab=pending" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">
            View all pending
          </router-link>
        </template>

        <div class="overflow-x-auto">
          <table class="min-w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-500 dark:text-slate-300">
              <tr>
                <th class="py-2 pr-3 font-medium">Employee</th>
                <th class="py-2 pr-3 font-medium">Leave Type</th>
                <th class="py-2 pr-3 font-medium">Filed</th>
                <th class="py-2 pr-3 font-medium">Requested Dates</th>
                <th class="py-2 pr-3 font-medium">Days</th>
                <th class="py-2 pr-3 font-medium">Status</th>
                <th class="py-2 font-medium"><span class="sr-only">Action</span></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
              <tr v-for="a in overview.pendingApplications" :key="a.id" class="text-slate-800 dark:text-slate-100">
                <td class="py-2 pr-3 font-medium">{{ a.employee }}</td>
                <td class="max-w-[160px] truncate py-2 pr-3" :title="a.leave_type">{{ a.leave_type }}</td>
                <td class="whitespace-nowrap py-2 pr-3">{{ fmt(a.date_filed) }}</td>
                <td class="whitespace-nowrap py-2 pr-3">{{ fmtRange(a.start_date, a.end_date) }}</td>
                <td class="py-2 pr-3">{{ a.days }}</td>
                <td class="py-2 pr-3"><StatusBadge :status="a.status" /></td>
                <td class="py-2 text-right">
                  <button type="button" class="rounded-md bg-blue-600 px-3 py-1 text-xs font-medium text-white hover:bg-blue-700" @click="reviewPending(a.employee)">
                    Review
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </SectionCard>

      <SectionCard
        title="Upcoming Approved Leave"
        :loading="overviewLoading"
        :error="overviewError"
        :empty="!overview?.upcomingLeave?.length"
        empty-message="No upcoming approved leave."
        @retry="loadOverview"
      >
        <ul class="divide-y divide-slate-100 dark:divide-slate-700">
          <li v-for="u in overview.upcomingLeave" :key="u.id">
            <button type="button" class="w-full py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-700/50" @click="openApplication(u.id)">
              <p class="text-sm font-medium text-slate-900 dark:text-white">{{ u.employee }}</p>
              <p class="truncate text-xs text-slate-500 dark:text-slate-300">{{ u.leave_type }}</p>
              <p class="text-xs text-slate-600 dark:text-slate-200">
                {{ fmtRange(u.start_date, u.end_date) }} · {{ u.days }} working day{{ u.days == 1 ? "" : "s" }}
              </p>
            </button>
          </li>
        </ul>
      </SectionCard>
    </div>

    <!-- Calendar + activity -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
      <SectionCard class="xl:col-span-2" title="Leave Calendar" subtitle="Approved leave only — pending requests are not shown">
        <LeaveCalendar
          :events="events"
          :loading="calLoading"
          :error="calError"
          @month-change="loadCalendar"
          @retry="loadCalendar()"
          @select="(ev) => openApplication(ev.id)"
        />
      </SectionCard>

      <SectionCard
        title="Recent Activity"
        :loading="activityLoading"
        :error="activityError"
        :empty="!activity.length"
        empty-message="No recorded activity yet."
        @retry="loadActivity"
      >
        <ul class="space-y-3">
          <li v-for="a in activity" :key="a.id" class="text-sm">
            <p class="text-slate-900 dark:text-white">{{ a.description }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-300">
              {{ a.action }} · {{ a.user ?? "system" }} · {{ fmtDateTime(a.created_at) }}
            </p>
          </li>
        </ul>
      </SectionCard>
    </div>

    <!-- Trends & Analytics -->
    <SectionCard title="Leave Trends & Analytics" subtitle="A quick look. Use Reports for detailed, filterable, printable data.">
      <template #actions>
        <label class="sr-only" for="period">Period</label>
        <select id="period" v-model="period" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-sm text-slate-700 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" @change="loadAnalytics">
          <option v-for="p in periodOptions" :key="p.value" :value="p.value">{{ p.label }}</option>
        </select>
        <router-link to="/reports" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">Open Reports</router-link>
      </template>

      <div v-if="analyticsLoading" class="space-y-2" role="status" aria-label="Loading">
        <div class="h-40 animate-pulse rounded bg-slate-200 dark:bg-slate-600" />
      </div>

      <div v-else-if="analyticsError" class="rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-200" role="alert">
        {{ analyticsError }}
        <button type="button" class="ml-2 underline" @click="loadAnalytics">Retry</button>
      </div>

      <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div>
          <h3 class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">Applications by status</h3>
          <p v-if="!statusTotal" class="py-10 text-center text-sm text-slate-500 dark:text-slate-300">No applications in this period.</p>
          <DonutChart v-else
            :labels="['Pending', 'Approved', 'Disapproved']"
            :values="[analytics.statusChart.pending, analytics.statusChart.approved, analytics.statusChart.disapproved]"
            :colors="['#f59e0b', '#22c55e', '#ef4444']" />
        </div>

        <div>
          <h3 class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">Applications by leave type</h3>
          <p v-if="!typeTotal" class="py-10 text-center text-sm text-slate-500 dark:text-slate-300">No applications in this period.</p>
          <BarChart v-else horizontal :labels="typeRows.map((t) => t.name)"
            :datasets="[{ label: 'Applications', data: typeRows.map((t) => t.count), color: '#2563eb' }]" />
        </div>

        <div>
          <h3 class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-200">Volume over the last 6 months</h3>
          <p v-if="!monthlyHasData" class="py-10 text-center text-sm text-slate-500 dark:text-slate-300">No applications filed in the last 6 months.</p>
          <template v-else>
            <BarChart :labels="analytics.monthly.map((m: any) => m.label)" stacked
              :datasets="[
                { label: 'Approved', data: analytics.monthly.map((m: any) => m.approved), color: '#22c55e' },
                { label: 'Pending', data: analytics.monthly.map((m: any) => m.pending), color: '#f59e0b' },
                { label: 'Disapproved', data: analytics.monthly.map((m: any) => m.disapproved), color: '#ef4444' },
              ]" />
            <p v-if="analytics.hasEnoughHistory && analytics.comparison.percentChange !== null" class="mt-2 text-xs text-slate-600 dark:text-slate-300">
              {{ analytics.comparison.currentLabel }}: {{ analytics.comparison.current }} filed vs
              {{ analytics.comparison.previousLabel }}: {{ analytics.comparison.previous }}
              ({{ analytics.comparison.percentChange > 0 ? "+" : "" }}{{ analytics.comparison.percentChange }}%)
            </p>
            <p v-else class="mt-2 text-xs text-slate-500 dark:text-slate-400">Monthly comparison appears once there are at least two months of history.</p>
          </template>
        </div>
      </div>
    </SectionCard>
  </div>
</template>
