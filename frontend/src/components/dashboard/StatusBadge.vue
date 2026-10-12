<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{ status: string | null | undefined }>();

const key = computed(() => String(props.status ?? "").toLowerCase());

const style = computed(() => {
  switch (key.value) {
    case "approved":
      return { background: 'rgba(22, 134, 91, 0.12)', color: '#16865B' };
    case "pending":
      return { background: 'rgba(217, 145, 23, 0.12)', color: '#D99117' };
    case "disapproved":
    case "rejected":
      return { background: 'rgba(214, 69, 69, 0.12)', color: '#D64545' };
    default:
      return { background: 'rgba(102, 117, 134, 0.12)', color: '#66758A' };
  }
});

const label = computed(
  () => key.value.charAt(0).toUpperCase() + key.value.slice(1) || "—",
);
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
    :style="style"
  >
    {{ label }}
  </span>
</template>
