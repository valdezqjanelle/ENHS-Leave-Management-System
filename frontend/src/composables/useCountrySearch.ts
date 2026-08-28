import { ref } from "vue";
import { COUNTRIES } from "../data/countries";

export function useCountrySearch() {
  const results = ref<string[]>([]);
  const isOpen = ref(false);

  function search(term: string) {
    if (!term || term.trim().length < 1) {
      results.value = [];
      isOpen.value = false;
      return;
    }

    const t = term.toLowerCase();

    results.value = COUNTRIES
      .filter((c) => c.toLowerCase().includes(t))
      .sort((a, b) => {
        const aStarts = a.toLowerCase().startsWith(t) ? 0 : 1;
        const bStarts = b.toLowerCase().startsWith(t) ? 0 : 1;
        return aStarts - bStarts;
      })
      .slice(0, 8);

    isOpen.value = results.value.length > 0;
  }

  function close() {
    isOpen.value = false;
  }

  return { results, isOpen, search, close };
}