<template>
  <section class="favorites-section" id="screens" aria-label="Collect your favourites">
    <div class="site-container">
      <div class="favorites-header-wrap">
        <div class="header-text-group">
          <h2 class="favorites-title">Collect your favourites</h2>
          <span class="favorites-count-text" v-if="favorites.length > 0">
            {{ favorites.length }} {{ favorites.length === 1 ? 'title' : 'titles' }} saved
          </span>
        </div>

        <div class="search-box-wrapper" ref="searchContainerRef">
          <div class="search-input-group">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
              type="text"
              class="search-input"
              v-model="searchQuery"
              @input="handleSearchInput"
              @focus="isDropdownOpen = true"
              @keydown.esc="isDropdownOpen = false"
              placeholder="Search title and add to grid"
              aria-label="Search title and add to grid"
            />
            <button
              v-if="searchQuery"
              class="search-clear-btn"
              @click="clearSearch"
              aria-label="Clear search input"
            >
              &times;
            </button>
          </div>

          <div
            v-if="isDropdownOpen && (searchResults.length > 0 || isSearching || searchError)"
            class="search-dropdown"
          >
            <div v-if="isSearching" class="dropdown-status">
              <span>Searching TVmaze database...</span>
            </div>
            <div v-else-if="searchError" class="dropdown-status dropdown-error">
              <span>{{ searchError }}</span>
            </div>
            <ul v-else-if="searchResults.length > 0" class="dropdown-list">
              <li
                v-for="item in searchResults"
                :key="item.show.id"
                class="dropdown-item"
              >
                <img
                  :src="item.show.image?.medium || item.show.image?.original || fallbackImage"
                  :alt="item.show.name"
                  class="dropdown-thumb"
                />
                <div class="dropdown-info">
                  <span class="dropdown-title">{{ item.show.name }}</span>
                  <span class="dropdown-meta">
                    {{ formatYear(item.show.premiered) }} &bull; {{ item.show.genres?.slice(0, 2).join(', ') || 'Drama' }}
                    <template v-if="item.show.rating?.average"> &bull; ★ {{ item.show.rating.average }}</template>
                  </span>
                </div>
                <div class="dropdown-actions">
                  <router-link
                    :to="`/show/${item.show.id}`"
                    class="dropdown-view-btn"
                    @click="isDropdownOpen = false"
                    title="View all details"
                  >
                    Details
                  </router-link>
                  <button
                    type="button"
                    class="dropdown-add-btn"
                    @click="addShowToFavorites(item.show)"
                    :disabled="isAlreadyAdded(item.show.id)"
                  >
                    {{ isAlreadyAdded(item.show.id) ? 'Added' : '+ Add' }}
                  </button>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="favorites-divider"></div>

      
      <div v-if="favorites.length === 0" class="empty-state">
        <div class="empty-icon-wrap">
          <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
          </svg>
        </div>
        <h3 class="empty-title">Your personal watchlist is empty</h3>
        <p class="empty-desc">
          Search for your favourite shows above or pick titles from the recommendations row to build your collection.
        </p>
        <div class="empty-actions">
          <a href="#recommendations" class="btn-primary">Browse Recommendations</a>
          <button class="btn-secondary" @click="loadSampleFavorites" :disabled="isLoadingSamples">
            {{ isLoadingSamples ? 'Loading...' : 'Add Sample Favourites' }}
          </button>
        </div>
      </div>

      
      <transition-group v-else name="card-anim" tag="div" class="cards-grid">
        <article
          v-for="card in favorites"
          :key="card.id"
          class="movie-card"
        >
          <div class="card-image-wrap">
            <img
              :src="card.image || fallbackImage"
              :alt="card.title"
              class="card-image"
              loading="lazy"
            />

            <div v-if="card.rating" class="card-badge-rating">
              <svg viewBox="0 0 24 24" width="12" height="12" fill="#dca114">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
              </svg>
              <span>{{ card.rating }}</span>
            </div>

            <button
              type="button"
              class="card-remove-btn"
              @click="removeCard(card.id)"
              :aria-label="'Remove ' + card.title + ' from favourites'"
              title="Remove from favourites"
            >
              <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>

          <div class="card-body">
            <div class="card-header-meta" v-if="card.genres?.length || card.premiered">
              <span v-if="card.premiered">{{ formatYear(card.premiered) }}</span>
              <span v-if="card.genres?.length" class="genre-preview">
                &bull; {{ card.genres.slice(0, 2).join(', ') }}
              </span>
            </div>

            <h3 class="card-title">{{ card.title }}</h3>
            <p class="card-desc">{{ card.description }}</p>

            <div class="card-footer-actions">
              <router-link
                :to="`/show/${card.showId || card.id}`"
                class="btn-see-more"
                :aria-label="'See more details about ' + card.title"
              >
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="12" y1="16" x2="12" y2="12"></line>
                  <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <span>See More</span>
              </router-link>
            </div>
          </div>
        </article>
      </transition-group>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useFavorites } from '../composables/useFavorites';
import { formatYear, FALLBACK_POSTER as fallbackImage } from '../utils/formatters';

const { favorites, isFavorite, addFavorite, removeFavorite } = useFavorites();

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const searchError = ref('');
const isDropdownOpen = ref(false);
const searchContainerRef = ref(null);
const isLoadingSamples = ref(false);

let debounceTimer = null;

const handleSearchInput = () => {
  clearTimeout(debounceTimer);
  const q = searchQuery.value.trim();
  if (!q) {
    searchResults.value = [];
    searchError.value = '';
    return;
  }

  isDropdownOpen.value = true;
  isSearching.value = true;
  searchError.value = '';

  debounceTimer = setTimeout(async () => {
    try {
      const response = await fetch(`https://api.tvmaze.com/search/shows?q=${encodeURIComponent(q)}`);
      if (!response.ok) {
        throw new Error('Failed to fetch search results.');
      }
      const data = await response.json();
      searchResults.value = data.slice(0, 8);
      if (searchResults.value.length === 0) {
        searchError.value = 'No matching titles found.';
      }
    } catch (err) {
      searchError.value = 'Could not load search results. Please try again.';
    } finally {
      isSearching.value = false;
    }
  }, 350);
};

const clearSearch = () => {
  searchQuery.value = '';
  searchResults.value = [];
  searchError.value = '';
  isDropdownOpen.value = false;
};

const isAlreadyAdded = (showId) => {
  return isFavorite(showId);
};

const addShowToFavorites = (show) => {
  addFavorite(show);
  clearSearch();
};

const removeCard = (cardId) => {
  removeFavorite(cardId);
};

const loadSampleFavorites = async () => {
  isLoadingSamples.value = true;
  try {
    const sampleIds = [169, 82, 178]; 
    for (const id of sampleIds) {
      try {
        const res = await fetch(`https://api.tvmaze.com/shows/${id}`);
        if (res.ok) {
          const show = await res.json();
          addFavorite(show);
        }
      } catch (e) {
        console.error(e);
      }
    }
  } finally {
    isLoadingSamples.value = false;
  }
};

const handleClickOutside = (e) => {
  if (searchContainerRef.value && !searchContainerRef.value.contains(e.target)) {
    isDropdownOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
  clearTimeout(debounceTimer);
});
</script>

<style scoped src="./FavoritesGrid.css"></style>
