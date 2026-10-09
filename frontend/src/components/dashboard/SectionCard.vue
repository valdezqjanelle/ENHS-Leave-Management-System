<script setup lang="ts">
defineProps<{
  title: string;
  subtitle?: string;
  loading?: boolean;
  error?: string | null;
  empty?: boolean;
  emptyMessage?: string;
}>();

defineEmits<{ (e: "retry"): void }>();
</script>

<template>
  <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 md:p-5">
    <header class="mb-4 flex flex-wrap items-start justify-between gap-2">
      <div>
        <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ title }}</h2>
        <p v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-300">{{ subtitle }}</p>
      </div>
      <div class="flex items-center gap-2">
        <slot name="actions" />
      </div>
    </header>

    <div v-if="loading" class="space-y-2" role="status" aria-label="Loading">
      <div class="h-4 w-full animate-pulse rounded bg-slate-200 dark:bg-slate-600" />
      <div class="h-4 w-5/6 animate-pulse rounded bg-slate-200 dark:bg-slate-600" />
      <div class="h-4 w-2/3 animate-pulse rounded bg-slate-200 dark:bg-slate-600" />
    </div>

    <div v-else-if="error" class="rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-200" role="alert">
      <p>{{ error }}</p>
      <button
        type="button"
        class="mt-2 rounded-md bg-red-600 px-3 py-1 text-xs font-medium text-white hover:bg-red-700"
        @click="$emit('retry')"
      >
        Retry
      </button>
    </div>

    <p v-else-if="empty" class="py-6 text-center text-sm text-slate-500 dark:text-slate-300">
      {{ emptyMessage ?? "Nothing to show yet." }}
    </p>

    <slot v-else />
  </section>
</template>
