import { ref } from "vue";
import axios from "axios";

export interface LocationResult {
  label: string;
  city: string;
  province: string;
}

export function useLocationSearch() {
  const results = ref<LocationResult[]>([]);
  const isOpen = ref(false);
  const loading = ref(false);

  let debounceTimer: ReturnType<typeof setTimeout>;

  function search(term: string) {
    clearTimeout(debounceTimer);

    if (!term || term.trim().length < 1) {
      results.value = [];
      isOpen.value = false;
      return;
    }

    debounceTimer = setTimeout(async () => {
      loading.value = true;

      try {
        const token = localStorage.getItem("token");

        const response = await axios.get(
          "https://enhs-leave-management-system.onrender.com/api/locations/search",
          {
            params: { q: term },
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        results.value = response.data.data;
        isOpen.value = results.value.length > 0;
      } catch (error) {
        console.error("Location search failed", error);
        results.value = [];
      } finally {
        loading.value = false;
      }
    }, 200);
  }

  function close() {
    isOpen.value = false;
  }

  return { results, isOpen, loading, search, close };
}