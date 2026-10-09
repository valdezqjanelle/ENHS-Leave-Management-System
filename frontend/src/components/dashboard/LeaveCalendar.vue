<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { ChevronLeft, ChevronRight } from "lucide-vue-next";

export interface CalendarEvent {
  id: number;
  employee: string | null;
  leave_type: string | null;
  start_date: string; // YYYY-MM-DD
  end_date: string; // YYYY-MM-DD
}

const props = defineProps<{
  events: CalendarEvent[];
  loading?: boolean;
  error?: string | null;
}>();

const emit = defineEmits<{
  (e: "month-change", month: string): void;
  (e: "select", event: CalendarEvent): void;
  (e: "retry"): void;
}>();

const pad = (n: number) => String(n).padStart(2, "0");
const toKey = (d: Date) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

const cursor = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1));

const monthKey = computed(() => `${cursor.value.getFullYear()}-${pad(cursor.value.getMonth() + 1)}`);
const monthLabel = computed(() =>
  cursor.value.toLocaleDateString("en-PH", { month: "long", year: "numeric" }),
);

watch(monthKey, (m) => emit("month-change", m), { immediate: true });

const shift = (delta: number) => {
  cursor.value = new Date(cursor.value.getFullYear(), cursor.value.getMonth() + delta, 1);
};
const goToday = () => {
  const n = new Date();
  cursor.value = new Date(n.getFullYear(), n.getMonth(), 1);
};

const todayKey = toKey(new Date());

// 6 rows x 7 columns, starting on Sunday
const cells = computed(() => {
  const first = cursor.value;
  const start = new Date(first.getFullYear(), first.getMonth(), 1 - first.getDay());
  return Array.from({ length: 42 }, (_, i) => {
    const d = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i);
    const key = toKey(d);
    return {
      key,
      day: d.getDate(),
      inMonth: d.getMonth() === first.getMonth(),
      events: props.events.filter((e) => e.start_date <= key && e.end_date >= key),
    };
  });
});

const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
const MAX_CHIPS = 2;
</script>

<template>
  <div>
    <div class="mb-3 flex items-center justify-between">
      <div class="flex items-center gap-1">
        <button type="button" class="rounded-md p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="Previous month" @click="shift(-1)">
          <ChevronLeft class="h-4 w-4 text-slate-600 dark:text-slate-200" />
        </button>
        <button type="button" class="rounded-md p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="Next month" @click="shift(1)">
          <ChevronRight class="h-4 w-4 text-slate-600 dark:text-slate-200" />
        </button>
        <h3 class="ml-2 text-sm font-semibold text-slate-900 dark:text-white" aria-live="polite">{{ monthLabel }}</h3>
      </div>
      <button type="button" class="rounded-md border border-slate-300 px-2 py-1 text-xs text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700" @click="goToday">
        Today
      </button>
    </div>

    <div v-if="error" class="rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-200" role="alert">
      {{ error }}
      <button type="button" class="ml-2 underline" @click="emit('retry')">Retry</button>
    </div>

    <div v-else class="relative">
      <div class="grid grid-cols-7 gap-px overflow-hidden rounded-lg border border-slate-200 bg-slate-200 text-xs dark:border-slate-700 dark:bg-slate-700" :class="loading ? 'opacity-60' : ''">
        <div v-for="w in weekdays" :key="w" class="bg-slate-50 py-1 text-center font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-300">
          {{ w }}
        </div>

        <div
          v-for="c in cells"
          :key="c.key"
          class="min-h-[64px] bg-white p-1 dark:bg-slate-900"
          :class="c.inMonth ? '' : 'opacity-40'"
        >
          <div
            class="mb-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full text-[11px]"
            :class="c.key === todayKey ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-300'"
          >
            {{ c.day }}
          </div>

          <button
            v-for="ev in c.events.slice(0, MAX_CHIPS)"
            :key="ev.id"
            type="button"
            class="mb-0.5 block w-full truncate rounded bg-green-100 px-1 text-left text-[10px] text-green-800 hover:bg-green-200 dark:bg-green-900/50 dark:text-green-200"
            :title="`${ev.employee ? ev.employee + ' — ' : ''}${ev.leave_type} (${ev.start_date} to ${ev.end_date})`"
            @click="emit('select', ev)"
          >
            {{ ev.employee ?? ev.leave_type }}
          </button>

          <span v-if="c.events.length > MAX_CHIPS" class="text-[10px] text-slate-500 dark:text-slate-400">
            +{{ c.events.length - MAX_CHIPS }} more
          </span>
        </div>
      </div>

      <p class="mt-2 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-300">
        <span class="inline-block h-2.5 w-2.5 rounded bg-green-300" /> Approved leave
        <span v-if="!loading && !events.length" class="ml-auto">No approved leave this month.</span>
      </p>
    </div>
  </div>
</template>
