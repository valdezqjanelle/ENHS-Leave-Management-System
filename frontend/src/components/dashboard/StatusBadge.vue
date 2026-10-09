<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{ status: string | null | undefined }>();

const key = computed(() => String(props.status ?? "").toLowerCase());

const classes = computed(() => {
  switch (key.value) {
    case "approved":
      return "bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300";
    case "pending":
      return "bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300";
    case "disapproved":
    case "rejected":
      return "bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300";
    default:
      return "bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200";
  }
});

const label = computed(
  () => key.value.charAt(0).toUpperCase() + key.value.slice(1) || "—",
);
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
    :class="classes"
  >
    {{ label }}
  </span>
</template>
