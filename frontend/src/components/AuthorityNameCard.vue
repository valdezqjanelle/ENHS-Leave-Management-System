<template>
  <form class="inner-card p-5 space-y-5" @submit.prevent="save">
    <div>
      <h3 class="text-lg font-semibold text-[var(--text)]">{{ title }}</h3>

      <p v-if="description" class="text-sm text-[var(--tex)] mt-1">
        {{ description }}
      </p>
    </div>

    <div
      v-if="feedback"
      :role="feedback.type === 'success' ? 'status' : 'alert'"
      class="rounded-lg border px-4 py-3 text-sm"
      :class="
        feedback.type === 'success'
          ? 'border-green-200 bg-green-50 text-green-800'
          : 'border-red-200 bg-red-50 text-red-800'
      "
    >
      {{ feedback.message }}
    </div>

    <div>
      <label
        :for="`${idPrefix}-name`"
        class="block text-sm font-medium text-[var(--text)] mb-2"
      >
        Name
      </label>

      <input
        :id="`${idPrefix}-name`"
        v-model="name"
        type="text"
        maxlength="255"
        required
        autocomplete="off"
        :disabled="loading"
        class="w-full border rounded-lg px-3 py-2 field-editable"
        :aria-invalid="Boolean(nameError)"
        :aria-describedby="nameError ? `${idPrefix}-name-error` : undefined"
      />

      <p
        v-if="nameError"
        :id="`${idPrefix}-name-error`"
        class="mt-1 text-sm text-red-600"
      >
        {{ nameError }}
      </p>
    </div>

    <div class="flex justify-end">
      <button
        type="submit"
        class="rounded-lg bg-[#2563eb] px-5 py-2 font-medium text-white hover:bg-[#1d4ed8] disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="saving || loading"
      >
        {{ saving ? "Saving..." : saveLabel }}
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import type {
  AuthorityNameResponse,
  AuthorityNameSaveResponse,
} from "@/services/approvingAuthorities";

const props = defineProps<{
  title: string;
  description?: string;
  saveLabel: string;
  /** Unique prefix for element ids, e.g. "authorized-officer". */
  idPrefix: string;
  load: () => Promise<AuthorityNameResponse>;
  save: (payload: AuthorityNameResponse) => Promise<AuthorityNameSaveResponse>;
}>();

const name = ref("");
const nameError = ref("");
const loading = ref(true);
const saving = ref(false);
const feedback = ref<{ type: "success" | "error"; message: string } | null>(
  null,
);

onMounted(async () => {
  try {
    const data = await props.load();
    name.value = data.name || "";
  } catch (error: any) {
    console.error(`Failed to load ${props.title}:`, error.response?.data || error);
    feedback.value = {
      type: "error",
      message: error.response?.data?.message || `Unable to load ${props.title}.`,
    };
  } finally {
    loading.value = false;
  }
});

const save = async () => {
  nameError.value = "";
  feedback.value = null;

  const trimmed = name.value.replace(/\s+/g, " ").trim();

  if (!trimmed) {
    nameError.value = "Name is required.";
    return;
  }

  saving.value = true;

  try {
    const result = await props.save({ name: trimmed });

    name.value = result.data?.name ?? trimmed;
    feedback.value = {
      type: "success",
      message: result.message || `${props.title} saved successfully.`,
    };
  } catch (error: any) {
    console.error(`Failed to save ${props.title}:`, error.response?.data || error);
    nameError.value = error.response?.data?.errors?.name?.[0] || "";
    feedback.value = {
      type: "error",
      message:
        error.response?.data?.message || `Failed to save ${props.title}.`,
    };
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.inner-card {
  background: var(--surface-muted);
  border: 1px solid #c8d8eb;
  border-radius: 1.1rem;
}

.field-editable {
  background: var(--surface-muted);
  color: var(--text);
  border-color: #c8d8eb;
}

.field-editable::placeholder {
  color: #94a3b8;
}

.field-editable:focus {
  outline: none;
  border-color: #7aa7e8;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}
</style>
