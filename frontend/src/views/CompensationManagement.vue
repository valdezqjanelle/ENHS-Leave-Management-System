<template>
  <div class="compensation-management space-y-6">
    <div class="management-card p-6">
      <h1 class="text-2xl font-bold">Positions &amp; Salary Schedules</h1>
      <p class="mt-2 text-sm muted">Manage personnel positions and approved salary rates.</p>
    </div>
    <div class="flex flex-wrap gap-2" aria-label="Compensation management sections">
      <button type="button" :class="{ selected: activeTab === 'positions' }" :aria-pressed="activeTab === 'positions'" @click="selectTab('positions')">Positions</button>
      <button type="button" :class="{ selected: activeTab === 'salary' }" :aria-pressed="activeTab === 'salary'" @click="selectTab('salary')">Salary Schedules</button>
    </div>
    <PositionManagement v-if="activeTab === 'positions'" @changed="emit('positions-changed')" />
    <SalaryScheduleManagement v-else />
  </div>
</template>
<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PositionManagement from '@/components/PositionManagement.vue'
import SalaryScheduleManagement from '@/components/SalaryScheduleManagement.vue'
const emit = defineEmits<{ 'positions-changed': [] }>()
const route = useRoute(), router = useRouter()
const activeTab = computed(() => route.query.tab === 'salary' || route.query.section === 'salary' ? 'salary' : 'positions')
const selectTab = (tab: 'positions' | 'salary') => router.replace({ path: '/admin-settings', query: { section: 'compensation', tab } })
</script>
<style scoped>
.compensation-management { color: var(--text); min-width: 0; }
.management-card, :deep(.neo-card) { background: var(--surface); border: 1px solid var(--border); border-radius: 1rem; box-shadow: 0 6px 18px rgba(23, 32, 51, .06); min-width: 0; }
.muted { color: var(--text-muted); }
button { padding: .65rem 1rem; border: 1px solid var(--border); border-radius: .6rem; background: var(--surface); color: var(--text); font-size: .875rem; font-weight: 600; }
button.selected { background: var(--primary); color: #fff; border-color: var(--primary); }
button:focus-visible { outline: 2px solid var(--primary); outline-offset: 3px; }
</style>
