<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
  label: string;
  value: number | null;
  icon: any;
  to?: string;
  tone?: "blue" | "amber" | "green" | "indigo" | "red";
  loading?: boolean;
  error?: boolean;
  hint?: string;
}>();

const tones: Record<string, string> = {
  blue: "bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300",
  amber: "bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300",
  green: "bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300",
  indigo: "bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300",
  red: "bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300",
};

const toneClass = computed(() => tones[props.tone ?? "blue"]);
</script>

<template>
  <component
    :is="to ? 'router-link' : 'div'"
    :to="to"
    class="group flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition dark:border-slate-700 dark:bg-slate-800"
    :class="to ? 'hover:border-blue-400 hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500' : ''"
    :aria-label="`${label}: ${loading ? 'loading' : error ? 'unavailable' : value}`"
  >
    <div class="rounded-lg p-3" :class="toneClass">
      <component :is="icon" class="h-6 w-6" aria-hidden="true" />
    </div>

    <div class="min-w-0">
      <p class="truncate text-sm text-slate-500 dark:text-slate-300">{{ label }}</p>

      <div v-if="loading" class="mt-1 h-7 w-12 animate-pulse rounded bg-slate-200 dark:bg-slate-600" />
      <p v-else-if="error || value === null" class="text-2xl font-semibold text-slate-400">—</p>
      <p v-else class="text-2xl font-semibold text-slate-900 dark:text-white">{{ value }}</p>

      <p v-if="hint" class="truncate text-xs text-slate-400">{{ hint }}</p>
    </div>
  </component>
</template>
