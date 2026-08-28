<template>
  <div class="location-autocomplete">
    <input
      v-model="localValue"
      type="text"
      placeholder="Start typing a city..."
      class="location-input"
      @input="onInput"
      @focus="onInput"
      @blur="onBlur"
      @keydown.down.prevent="moveHighlight(1)"
      @keydown.up.prevent="moveHighlight(-1)"
      @keydown.enter.prevent="confirmHighlighted"
    />

    <ul v-if="isOpen" class="location-dropdown">
      <li v-if="loading" class="location-loading">Searching...</li>

      <li
        v-for="(loc, i) in results"
        :key="loc.label"
        class="location-item"
        :class="{ 'location-item-active': i === highlightedIndex }"
        @mousedown.prevent="selectLocation(loc)"
        @mouseenter="highlightedIndex = i"
        v-html="highlightMatch(loc.label, localValue)"
      />
    </ul>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useLocationSearch, type LocationResult } from "../composables/useLocationSearch";

const props = defineProps<{ modelValue: string }>();
const emit = defineEmits<{ (e: "update:modelValue", value: string): void }>();

const localValue = ref(props.modelValue);
const highlightedIndex = ref(-1);
const { results, isOpen, loading, search, close } = useLocationSearch();

watch(() => props.modelValue, (v) => { localValue.value = v; });
watch(results, () => { highlightedIndex.value = -1; });

function onInput() {
  emit("update:modelValue", localValue.value);
  search(localValue.value);
}

function selectLocation(loc: LocationResult) {
  localValue.value = loc.label;
  emit("update:modelValue", loc.label);
  close();
}

function moveHighlight(dir: number) {
  if (!isOpen.value || results.value.length === 0) return;
  const max = results.value.length - 1;
  highlightedIndex.value = Math.min(max, Math.max(0, highlightedIndex.value + dir));
}

function confirmHighlighted() {
  if (highlightedIndex.value >= 0) {
    selectLocation(results.value[highlightedIndex.value]);
  }
}

function onBlur() {
  setTimeout(() => close(), 150);
}

function highlightMatch(label: string, query: string): string {
  if (!query) return label;
  const idx = label.toLowerCase().indexOf(query.toLowerCase());
  if (idx === -1) return label;
  const before = label.slice(0, idx);
  const match = label.slice(idx, idx + query.length);
  const after = label.slice(idx + query.length);
  return `${before}<strong class="location-match">${match}</strong>${after}`;
}
</script>

<style scoped>
.location-autocomplete {
  position: relative;
  width: 100%;
}

.location-input {
  width: 100%;
  min-width: 0;
  border: 1px solid #334155;
  border-radius: 0.65rem;
  padding: 0.7rem 0.85rem;
  background: #0f1a2a;
  color: #f8fafc;
  font-size: 0.875rem;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.location-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
}

.location-input::placeholder {
  color: #64748b;
}

.location-dropdown {
  position: absolute;
  z-index: 30;
  margin-top: 0.25rem;
  width: 100%;
  overflow: hidden;
  border-radius: 0.65rem;
  border: 1px solid #334155;
  background: #111d2e;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.4);
  max-height: 220px;
  overflow-y: auto;
}

.location-item {
  padding: 0.6rem 0.85rem;
  font-size: 0.875rem;
  color: #f8fafc;
  cursor: pointer;
}

.location-item:hover,
.location-item-active {
  background: #2563eb;
}

.location-loading {
  padding: 0.6rem 0.85rem;
  font-size: 0.8rem;
  color: #64748b;
}

:deep(.location-match) {
  color: #60a5fa;
  font-weight: 600;
}
</style>