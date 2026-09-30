<template>
  <p v-if="year" class="text-sm" style="color:var(--text-muted)">School year: {{ year.name }} · {{ year.start_date }} to {{ year.end_date }}</p>
</template>
<script setup lang="ts">
import { onMounted, ref } from 'vue';
import axios from 'axios';
const year = ref<{ name: string; start_date: string; end_date: string } | null>(null);
onMounted(async () => {
  try {
    const { data } = await axios.get('https://enhs-leave-management-system.onrender.com/api/leave-school-year/current', {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
    });
    year.value = data.data;
  } catch (error) { console.error('Could not load school year.', error); }
});
</script>
