import { ref, computed } from 'vue';
import { tvmazeApi } from '../services/tvmazeApi';
import { formatYear } from '../utils/formatters';

export const ITEMS_PER_PAGE = 12;

export const AVAILABLE_GENRES = [
  'Action',
  'Adventure',
  'Animation',
  'Anime',
  'Comedy',
  'Crime',
  'Drama',
  'Espionage',
  'Family',
  'Fantasy',
  'History',
  'Horror',
  'Legal',
  'Medical',
  'Music',
  'Mystery',
  'Romance',
  'Science-Fiction',
  'Sports',
  'Supernatural',
  'Thriller',
  'War',
  'Western'
];

export function useMovieLibrary(favoritesRef = null) {
  const shows = ref([]);
  const isLoading = ref(true);
  const isLoadingMore = ref(false);
  const fetchError = ref('');
  const apiPage = ref(0);
  const currentPage = ref(1);

  // Filter states
  const searchQuery = ref('');
  const activeCategory = ref('all');
  const showFavoritesOnly = ref(false);
  const selectedGenre = ref('all');
  const selectedType = ref('all');
  const selectedEra = ref('all');
  const selectedStatus = ref('all');
  const selectedRating = ref('all');
  const sortBy = ref('rating-desc');
  const viewMode = ref('grid');

  let searchDebounceTimer = null;

  const fetchInitialShows = async () => {
    isLoading.value = true;
    fetchError.value = '';
    apiPage.value = 0;
    currentPage.value = 1;

    try {
      shows.value = await tvmazeApi.getShows(0);
    } catch (err) {
      console.error('Failed to fetch initial library shows:', err);
      fetchError.value = 'Could not load library titles. Please check your internet connection.';
    } finally {
      isLoading.value = false;
    }
  };

  const handleSearchChange = () => {
    clearTimeout(searchDebounceTimer);
    const q = searchQuery.value.trim();
    currentPage.value = 1;

    if (!q) {
      fetchInitialShows();
      return;
    }

    isLoading.value = true;
    searchDebounceTimer = setTimeout(async () => {
      try {
        shows.value = await tvmazeApi.searchShows(q);
      } catch (err) {
        console.error('Search error:', err);
      } finally {
        isLoading.value = false;
      }
    }, 350);
  };

  const clearSearch = () => {
    searchQuery.value = '';
    handleSearchChange();
  };

  const loadNextApiPage = async () => {
    if (isLoadingMore.value) return;
    isLoadingMore.value = true;
    apiPage.value += 1;

    try {
      const more = await tvmazeApi.getShows(apiPage.value);
      const existingIds = new Set(shows.value.map((s) => s.id));
      const newItems = more.filter((s) => !existingIds.has(s.id));
      shows.value = [...shows.value, ...newItems];
    } catch (err) {
      console.error('Failed to load more titles:', err);
    } finally {
      isLoadingMore.value = false;
    }
  };

  const hasActiveFilters = computed(() => {
    return (
      searchQuery.value.trim() !== '' ||
      activeCategory.value !== 'all' ||
      showFavoritesOnly.value ||
      selectedGenre.value !== 'all' ||
      selectedType.value !== 'all' ||
      selectedEra.value !== 'all' ||
      selectedStatus.value !== 'all' ||
      selectedRating.value !== 'all' ||
      sortBy.value !== 'rating-desc'
    );
  });

  const resetFilters = () => {
    searchQuery.value = '';
    activeCategory.value = 'all';
    showFavoritesOnly.value = false;
    selectedGenre.value = 'all';
    selectedType.value = 'all';
    selectedEra.value = 'all';
    selectedStatus.value = 'all';
    selectedRating.value = 'all';
    sortBy.value = 'rating-desc';
    currentPage.value = 1;
    fetchInitialShows();
  };

  const filteredShows = computed(() => {
    let result = [...shows.value];

    if (showFavoritesOnly.value && favoritesRef?.value) {
      const favIds = new Set(favoritesRef.value.map((f) => f.id));
      result = result.filter((show) => favIds.has(show.id));
    }

    if (activeCategory.value !== 'all') {
      if (activeCategory.value === 'movies') {
        result = result.filter((s) => s.type === 'Scripted' || s.genres?.includes('Drama'));
      } else if (activeCategory.value === 'series') {
        result = result.filter((s) => s.type !== 'Documentary' && s.type !== 'Reality');
      } else if (activeCategory.value === 'animation') {
        result = result.filter((s) => s.type === 'Animation' || s.genres?.includes('Animation'));
      } else if (activeCategory.value === 'documentary') {
        result = result.filter((s) => s.type === 'Documentary');
      }
    }

    if (selectedGenre.value !== 'all') {
      result = result.filter(
        (show) => show.genres && show.genres.includes(selectedGenre.value)
      );
    }

    if (selectedType.value !== 'all') {
      result = result.filter((show) => show.type === selectedType.value);
    }

    if (selectedEra.value !== 'all') {
      result = result.filter((show) => {
        const year = parseInt(formatYear(show.premiered), 10);
        if (isNaN(year)) return false;
        if (selectedEra.value === '2020s') return year >= 2020;
        if (selectedEra.value === '2010s') return year >= 2010 && year < 2020;
        if (selectedEra.value === '2000s') return year >= 2000 && year < 2010;
        if (selectedEra.value === 'classics') return year < 2000;
        return true;
      });
    }

    if (selectedStatus.value !== 'all') {
      result = result.filter((show) => show.status === selectedStatus.value);
    }

    if (selectedRating.value !== 'all') {
      const minRating = parseFloat(selectedRating.value);
      result = result.filter(
        (show) => show.rating?.average && show.rating.average >= minRating
      );
    }

    switch (sortBy.value) {
      case 'rating-desc':
        result.sort((a, b) => (b.rating?.average || 0) - (a.rating?.average || 0));
        break;
      case 'year-desc':
        result.sort((a, b) => (b.premiered || '').localeCompare(a.premiered || ''));
        break;
      case 'year-asc':
        result.sort((a, b) => (a.premiered || '').localeCompare(b.premiered || ''));
        break;
      case 'title-asc':
        result.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
        break;
      case 'title-desc':
        result.sort((a, b) => (b.name || '').localeCompare(a.name || ''));
        break;
    }

    return result;
  });

  const totalPages = computed(() => {
    return Math.ceil(filteredShows.value.length / ITEMS_PER_PAGE) || 1;
  });

  const displayedShows = computed(() => {
    const start = (currentPage.value - 1) * ITEMS_PER_PAGE;
    return filteredShows.value.slice(start, start + ITEMS_PER_PAGE);
  });

  const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page;
      window.scrollTo({ top: 320, behavior: 'smooth' });
    }
  };

  return {
    shows,
    isLoading,
    isLoadingMore,
    fetchError,
    currentPage,
    searchQuery,
    activeCategory,
    showFavoritesOnly,
    selectedGenre,
    selectedType,
    selectedEra,
    selectedStatus,
    selectedRating,
    sortBy,
    viewMode,
    availableGenres: AVAILABLE_GENRES,
    hasActiveFilters,
    filteredShows,
    totalPages,
    displayedShows,
    fetchInitialShows,
    handleSearchChange,
    clearSearch,
    loadNextApiPage,
    resetFilters,
    goToPage
  };
}
