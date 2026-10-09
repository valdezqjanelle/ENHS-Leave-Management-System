<script setup lang="ts">
import { computed } from "vue";
import { Doughnut } from "vue-chartjs";
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from "chart.js";

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps<{
  labels: string[];
  values: number[];
  colors: string[];
}>();

const isDark = () => document.documentElement.classList.contains("dark");

const data = computed(() => ({
  labels: props.labels,
  datasets: [
    {
      data: props.values,
      backgroundColor: props.colors,
      borderWidth: 0,
    },
  ],
}));

const options = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  cutout: "65%",
  plugins: {
    legend: {
      position: "bottom" as const,
      labels: { color: isDark() ? "#e2e8f0" : "#334155", boxWidth: 12 },
    },
  },
}));
</script>

<template>
  <div class="h-56">
    <Doughnut :data="data" :options="options" aria-label="Status distribution chart" role="img" />
  </div>
</template>
