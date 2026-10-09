<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import api from "@/services/api";
import ActiveSchoolYear from "@/components/ActiveSchoolYear.vue";
import AdminDashboardPanel from "@/components/dashboard/AdminDashboardPanel.vue";
import EmployeeDashboardPanel from "@/components/dashboard/EmployeeDashboardPanel.vue";

const role = ref<string | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);

const isAdmin = computed(() => role.value === "admin");

const loadRole = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get("/me");
    role.value = String(response.data?.role || "employee").toLowerCase();
  } catch (e: any) {
    error.value = e?.response?.data?.message || "Could not load your account. Please try again.";
  } finally {
    loading.value = false;
  }
};

onMounted(loadRole);
</script>

<template>
  <div class="min-h-screen space-y-6 bg-slate-50 p-4 dark:bg-slate-900 md:p-8">
    <ActiveSchoolYear />

    <div v-if="loading" class="py-10 text-center text-slate-600 dark:text-slate-200" role="status">
      Loading dashboard...
    </div>

    <div v-else-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
      {{ error }}
      <button type="button" class="ml-2 underline" @click="loadRole">Retry</button>
    </div>

    <AdminDashboardPanel v-else-if="isAdmin" />
    <EmployeeDashboardPanel v-else />
  </div>
</template>
