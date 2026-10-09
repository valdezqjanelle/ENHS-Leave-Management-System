<script setup lang="ts">
import { computed } from "vue";
import { Bar } from "vue-chartjs";
import {
  Chart as ChartJS,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from "chart.js";

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend);

const props = defineProps<{
  labels: string[];
  datasets: { label: string; data: number[]; color: string }[];
  horizontal?: boolean;
  stacked?: boolean;
  height?: string;
}>();

const isDark = () => document.documentElement.classList.contains("dark");

// Long leave-type names are shortened on the axis; the tooltip shows the full name.
const shorten = (s: string) => (s.length > 22 ? s.slice(0, 21) + "…" : s);

const data = computed(() => ({
  labels: props.labels,
  datasets: props.datasets.map((d) => ({
    label: d.label,
    data: d.data,
    backgroundColor: d.color,
    borderRadius: 4,
    maxBarThickness: 28,
  })),
}));

const options = computed(() => {
  const text = isDark() ? "#cbd5e1" : "#475569";
  const grid = isDark() ? "#334155" : "#e2e8f0";
  const cat = {
    stacked: !!props.stacked,
    grid: { display: false },
    ticks: {
      color: text,
      callback(this: any, value: any) {
        return shorten(String(this.getLabelForValue(value)));
      },
    },
  };
  const num = {
    stacked: !!props.stacked,
    beginAtZero: true,
    grid: { color: grid },
    ticks: { color: text, precision: 0 },
  };

  return {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: props.horizontal ? ("y" as const) : ("x" as const),
    scales: props.horizontal ? { x: num, y: cat } : { x: cat, y: num },
    plugins: {
      legend: {
        display: props.datasets.length > 1,
        labels: { color: text, boxWidth: 12 },
      },
    },
  };
});
</script>

<template>
  <div :class="height ?? 'h-64'">
    <Bar :data="data" :options="options" role="img" aria-label="Bar chart" />
  </div>
</template>
