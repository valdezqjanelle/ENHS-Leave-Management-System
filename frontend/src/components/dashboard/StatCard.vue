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

const tones: Record<string, { bg: string; text: string }> = {
  blue: { bg: 'rgba(22, 135, 232, 0.12)', text: '#1687E8' },
  amber: { bg: 'rgba(217, 145, 23, 0.12)', text: '#D99117' },
  green: { bg: 'rgba(22, 134, 91, 0.12)', text: '#16865B' },
  indigo: { bg: 'rgba(99, 102, 241, 0.12)', text: '#6366F1' },
  red: { bg: 'rgba(214, 69, 69, 0.12)', text: '#D64545' },
};

const toneStyle = computed(() => {
  const t = tones[props.tone ?? 'blue'];
  if (!t) return { background: 'rgba(22, 135, 232, 0.12)', color: '#1687E8' };
  return { background: t.bg, color: t.text };
});
</script>

<template>
  <component
    :is="to ? 'router-link' : 'div'"
    :to="to"
    class="group flex items-center gap-4 rounded-lg border text-[var(--text)] p-4 transition"
    style="border-color: var(--border); background: var(--surface);"
    :class="to ? 'hover:border-[var(--primary)] hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--primary)]' : ''"
    :aria-label="`${label}: ${loading ? 'loading' : error ? 'unavailable' : value}`"
  >
    <div class="rounded-lg p-3" :style="toneStyle">
      <component :is="icon" class="h-6 w-6" aria-hidden="true" />
    </div>

    <div class="min-w-0">
      <p class="truncate text-sm" style="color: var(--text);">{{ label }}</p>

      <div v-if="loading" class="mt-1 h-7 w-12 animate-pulse rounded" style="background: var(--surface-muted);" />
      <p v-else-if="error || value === null" class="text-2xl font-semibold" style="color: var(--text);">—</p>
      <p v-else class="text-2xl font-semibold" style="color: var(--text);">{{ value }}</p>

      <p v-if="hint" class="truncate text-xs" style="color: var(--text);">{{ hint }}</p>
    </div>
  </component>
</template>
