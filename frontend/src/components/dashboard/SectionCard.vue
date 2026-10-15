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
  <section class="rounded-lg border p-4 md:p-5" style="border-color: var(--border); background: var(--surface);">
    <header class="mb-4 flex flex-wrap items-start justify-between gap-2">
      <div>
        <h2 class="text-base font-semibold" style="color: var(--text);">{{ title }}</h2>
        <p v-if="subtitle" class="text-xs" style="color: var(--text);">{{ subtitle }}</p>
      </div>
      <div class="flex items-center gap-2">
        <slot name="actions" />
      </div>
    </header>

    <div v-if="loading" class="space-y-2" role="status" aria-label="Loading">
      <div class="h-4 w-full animate-pulse rounded" style="background: var(--surface-muted);" />
      <div class="h-4 w-5/6 animate-pulse rounded" style="background: var(--surface-muted);" />
      <div class="h-4 w-2/3 animate-pulse rounded" style="background: var(--surface-muted);" />
    </div>

    <div v-else-if="error" class="rounded-lg p-3 text-sm" style="background: rgba(214, 69, 69, 0.1); color: var(--danger);" role="alert">
      <p>{{ error }}</p>
      <button
        type="button"
        class="mt-2 rounded-md px-3 py-1 text-xs font-medium text-white transition-colors hover:opacity-90"
        style="background: var(--danger);"
        @click="$emit('retry')"
      >
        Retry
      </button>
    </div>

    <p v-else-if="empty" class="py-6 text-center text-sm" style="color: var(--text);">
      {{ emptyMessage ?? "Nothing to show yet." }}
    </p>

    <slot v-else />
  </section>
</template>
