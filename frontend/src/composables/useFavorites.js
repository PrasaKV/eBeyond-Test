import { ref, watch } from 'vue';

const STORAGE_KEY = 'movie_library_favorites';

const loadStoredFavorites = () => {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (raw) {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed)) return parsed;
    }
  } catch (err) {
    console.error('Error loading stored favorites:', err);
  }
  return [];
};

const favorites = ref(loadStoredFavorites());

watch(
  favorites,
  (newVal) => {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(newVal));
    } catch (err) {
      console.error('Error saving favorites:', err);
    }
  },
  { deep: true }
);

export function useFavorites() {
  const isFavorite = (showId) => {
    if (!showId) return false;
    return favorites.value.some((item) => String(item.showId || item.id) === String(showId));
  };

  const addFavorite = (show) => {
    if (!show || isFavorite(show.id)) return false;

    const stripHtml = (html) => {
      if (!html) return 'No description available.';
      const doc = new DOMParser().parseFromString(html, 'text/html');
      return (doc.body.textContent || '').trim();
    };

    const newCard = {
      id: `fav-${show.id}-${Date.now()}`,
      showId: show.id,
      title: show.name || show.title,
      image: show.image?.original || show.image?.medium || '',
      rating: show.rating?.average || null,
      genres: Array.isArray(show.genres) ? show.genres : [],
      premiered: show.premiered || '',
      description: stripHtml(show.summary || show.description),
      addedAt: Date.now()
    };

    favorites.value.unshift(newCard);
    return true;
  };

  const removeFavorite = (identifier) => {
    favorites.value = favorites.value.filter(
      (item) => String(item.id) !== String(identifier) && String(item.showId) !== String(identifier)
    );
  };

  const toggleFavorite = (show) => {
    const id = show.id || show.showId;
    if (isFavorite(id)) {
      removeFavorite(id);
      return false;
    } else {
      addFavorite(show);
      return true;
    }
  };

  const clearAllFavorites = () => {
    favorites.value = [];
  };

  return {
    favorites,
    isFavorite,
    addFavorite,
    removeFavorite,
    toggleFavorite,
    clearAllFavorites
  };
}
