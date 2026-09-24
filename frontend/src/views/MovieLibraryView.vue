<template>
  <div class="movie-library-page">
    <!-- Top breadcrumb / navigation bar -->
    <div class="library-top-bar">
      <div class="site-container top-bar-flex">
        <router-link to="/" class="btn-back">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
          <span>Back to Home</span>
        </router-link>

        <div class="library-page-crumb">
          <router-link to="/" class="crumb-link">Home</router-link>
          <span class="crumb-separator">/</span>
          <span class="crumb-active">Movie &amp; TV Library</span>
        </div>
      </div>
    </div>

    <!-- Library Header & Search Section -->
    <section class="library-header-section">
      <div class="site-container">
        <div class="header-content">
          <h1 class="library-main-title">MOVIE &amp; TV LIBRARY</h1>
          <p class="library-subtitle">
            Explore thousands of TV series, films, animations, and documentaries. Use the live filters below to find the exact title you're looking for.
          </p>
        </div>

        <!-- Search Bar with Instant Querying -->
        <div class="library-search-wrapper">
          <div class="search-input-box">
            <svg class="search-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
              type="text"
              class="library-search-input"
              v-model="searchQuery"
              @input="handleSearchChange"
              placeholder="Search by title, keyword, cast member, or network..."
              aria-label="Search movies and TV shows"
            />
            <button
              v-if="searchQuery"
              type="button"
              class="search-clear-btn"
              @click="clearSearch"
              aria-label="Clear search"
            >
              &times;
            </button>
          </div>
        </div>


        <!-- Filter Controls Toolbar -->
        <div class="filters-toolbar">
          <!-- Genre -->
          <div class="filter-group">
            <label for="genreFilter" class="filter-label">Genre</label>
            <div class="select-wrap">
              <select id="genreFilter" v-model="selectedGenre" class="filter-select">
                <option value="all">All Genres</option>
                <option v-for="genre in availableGenres" :key="genre" :value="genre">
                  {{ genre }}
                </option>
              </select>
            </div>
          </div>

          <!-- Type -->
          <div class="filter-group">
            <label for="typeFilter" class="filter-label">Show Type</label>
            <div class="select-wrap">
              <select id="typeFilter" v-model="selectedType" class="filter-select">
                <option value="all">All Types</option>
                <option value="Scripted">Scripted Series</option>
                <option value="Animation">Animation</option>
                <option value="Reality">Reality</option>
                <option value="Talk Show">Talk Show</option>
                <option value="Documentary">Documentary</option>
              </select>
            </div>
          </div>

          <!-- Release Era -->
          <div class="filter-group">
            <label for="eraFilter" class="filter-label">Release Era</label>
            <div class="select-wrap">
              <select id="eraFilter" v-model="selectedEra" class="filter-select">
                <option value="all">All Eras</option>
                <option value="2020s">2020 &ndash; Present</option>
                <option value="2010s">2010 &ndash; 2019</option>
                <option value="2000s">2000 &ndash; 2009</option>
                <option value="classics">Classics (Pre-2000)</option>
              </select>
            </div>
          </div>

          <!-- Status -->
          <div class="filter-group">
            <label for="statusFilter" class="filter-label">Status</label>
            <div class="select-wrap">
              <select id="statusFilter" v-model="selectedStatus" class="filter-select">
                <option value="all">All Statuses</option>
                <option value="Running">Running (Ongoing)</option>
                <option value="Ended">Ended</option>
              </select>
            </div>
          </div>

          <!-- Rating -->
          <div class="filter-group">
            <label for="ratingFilter" class="filter-label">Min Rating</label>
            <div class="select-wrap">
              <select id="ratingFilter" v-model="selectedRating" class="filter-select">
                <option value="all">All Ratings</option>
                <option value="8">8.0+ Stars</option>
                <option value="7">7.0+ Stars</option>
                <option value="6">6.0+ Stars</option>
              </select>
            </div>
          </div>

          <!-- Sort By -->
          <div class="filter-group sort-group">
            <label for="sortBy" class="filter-label">Sort By</label>
            <div class="select-wrap">
              <select id="sortBy" v-model="sortBy" class="filter-select">
                <option value="rating-desc">Highest Rated</option>
                <option value="year-desc">Newest First</option>
                <option value="year-asc">Oldest First</option>
                <option value="title-asc">Title (A &ndash; Z)</option>
                <option value="title-desc">Title (Z &ndash; A)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Active Filter Tags & Reset Bar -->
        <div class="active-filters-bar" v-if="hasActiveFilters">
          <span class="active-label">Active Filters:</span>
          <div class="tags-container">
            <span v-if="searchQuery" class="filter-tag">
              Search: "{{ searchQuery }}"
              <button type="button" @click="clearSearch">&times;</button>
            </span>
            <span v-if="showFavoritesOnly" class="filter-tag">
              Saved Watchlist Only
              <button type="button" @click="showFavoritesOnly = false">&times;</button>
            </span>
            <span v-if="selectedGenre !== 'all'" class="filter-tag">
              Genre: {{ selectedGenre }}
              <button type="button" @click="selectedGenre = 'all'">&times;</button>
            </span>
            <span v-if="selectedType !== 'all'" class="filter-tag">
              Type: {{ selectedType }}
              <button type="button" @click="selectedType = 'all'; activeCategory = 'all'">&times;</button>
            </span>
            <span v-if="selectedEra !== 'all'" class="filter-tag">
              Era: {{ formatEraLabel(selectedEra) }}
              <button type="button" @click="selectedEra = 'all'">&times;</button>
            </span>
            <span v-if="selectedStatus !== 'all'" class="filter-tag">
              Status: {{ selectedStatus }}
              <button type="button" @click="selectedStatus = 'all'">&times;</button>
            </span>
            <span v-if="selectedRating !== 'all'" class="filter-tag">
              Rating: {{ selectedRating }}.0+
              <button type="button" @click="selectedRating = 'all'">&times;</button>
            </span>
            <button type="button" class="btn-reset-filters" @click="resetAllFilters">
              Reset All Filters
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Results Section -->
    <section class="library-results-section" id="library-results">
      <div class="site-container">
        <!-- Results Header Bar & View Switcher -->
        <div class="results-header-wrap">
          <div class="results-info-group">
            <h2 class="results-heading">
              <span v-if="isLoading">Loading titles...</span>
              <span v-else>
                Showing {{ displayedShows.length }} of {{ filteredShows.length }} {{ filteredShows.length === 1 ? 'title' : 'titles' }}
              </span>
            </h2>
            <span class="page-indicator" v-if="!isLoading && totalPages > 1">
              Page {{ currentPage }} of {{ totalPages }} &bull; Max 12 per page
            </span>
          </div>

          <div class="view-mode-toggle" aria-label="View density toggle">
            <button
              type="button"
              class="view-mode-btn"
              :class="{ 'is-active': viewMode === 'grid' }"
              @click="viewMode = 'grid'"
              title="Grid View (12 Cards)"
              aria-label="Grid View"
            >
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
              </svg>
            </button>
            <button
              type="button"
              class="view-mode-btn"
              :class="{ 'is-active': viewMode === 'compact' }"
              @click="viewMode = 'compact'"
              title="Compact List View (12 Rows)"
              aria-label="Compact List View"
            >
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                <rect x="3" y="4" width="18" height="4" rx="1"/>
                <rect x="3" y="10" width="18" height="4" rx="1"/>
                <rect x="3" y="16" width="18" height="4" rx="1"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Loading Skeleton Grid (12 items) -->
        <div v-if="isLoading && shows.length === 0" class="cards-grid">
          <div v-for="n in 12" :key="n" class="skeleton-card">
            <div class="skeleton-poster"></div>
            <div class="skeleton-meta"></div>
            <div class="skeleton-title"></div>
            <div class="skeleton-desc"></div>
            <div class="skeleton-btn"></div>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="fetchError && shows.length === 0" class="empty-state">
          <p class="error-msg">{{ fetchError }}</p>
          <button class="btn-primary" @click="fetchInitialShows">Try Again</button>
        </div>

        <!-- Empty Filter Results -->
        <div v-else-if="filteredShows.length === 0" class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              <line x1="8" y1="11" x2="14" y2="11"></line>
            </svg>
          </div>
          <h3 class="empty-title">No matching movies or series found</h3>
          <p class="empty-desc">
            We couldn't find any titles matching your current filter combination. Try clearing some filters or searching with different keywords.
          </p>
          <button class="btn-primary" @click="resetAllFilters">Reset All Filters</button>
        </div>

        <!-- Shows Grid View (Limited to 12 cards) -->
        <div v-else-if="viewMode === 'grid'" class="cards-grid">
          <article
            v-for="show in displayedShows"
            :key="show.id"
            class="library-card"
          >
            <div class="card-media-wrap">
              <img
                :src="show.image?.medium || show.image?.original || fallbackImage"
                :alt="show.name"
                class="card-poster"
                loading="lazy"
              />
              <div class="media-overlay"></div>

              <div v-if="show.rating?.average" class="card-rating-tag">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="#dca114">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
                <span>{{ show.rating.average.toFixed(1) }}</span>
              </div>

              <span v-if="show.genres && show.genres.length" class="card-genre-tag">
                {{ show.genres[0] }}
              </span>
            </div>

            <div class="card-body">
              <div class="card-meta">
                <span class="meta-year">{{ formatYear(show.premiered) }}</span>
                <span v-if="show.network?.name || show.webChannel?.name" class="meta-net">
                  &bull; {{ show.network?.name || show.webChannel?.name }}
                </span>
                <span v-if="show.status" class="meta-status" :class="show.status === 'Running' ? 'is-running' : 'is-ended'">
                  &bull; {{ show.status }}
                </span>
              </div>

              <h3 class="card-title" :title="show.name">{{ show.name }}</h3>

              <p class="card-summary">
                {{ stripHtml(show.summary) }}
              </p>

              <div class="card-actions">
                <!-- Dedicated page navigation: NO popup -->
                <router-link
                  :to="`/show/${show.id}`"
                  class="btn-see-more"
                  :aria-label="`See more details about ${show.name}`"
                >
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                  </svg>
                  <span>See More</span>
                </router-link>

                <button
                  type="button"
                  class="btn-card-add"
                  :class="{ 'is-added': isFavorite(show.id) }"
                  @click="toggleFavorite(show)"
                  :title="isFavorite(show.id) ? 'Remove from favourites' : 'Add to favourites'"
                  :aria-label="isFavorite(show.id) ? `Remove ${show.name} from favourites` : `Add ${show.name} to favourites`"
                >
                  <svg
                    v-if="isFavorite(show.id)"
                    viewBox="0 0 24 24"
                    width="14"
                    height="14"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  <svg
                    v-else
                    viewBox="0 0 24 24"
                    width="14"
                    height="14"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                  </svg>
                  <span>{{ isFavorite(show.id) ? 'Saved' : 'Add' }}</span>
                </button>
              </div>
            </div>
          </article>
        </div>

        <!-- Shows Compact List View (Limited to 12 rows) -->
        <div v-else class="compact-list">
          <article
            v-for="show in displayedShows"
            :key="show.id"
            class="compact-item"
          >
            <div class="compact-poster-wrap">
              <img
                :src="show.image?.medium || show.image?.original || fallbackImage"
                :alt="show.name"
                class="compact-poster"
                loading="lazy"
              />
            </div>

            <div class="compact-body">
              <div class="compact-head">
                <div class="compact-title-group">
                  <h3 class="compact-title">{{ show.name }}</h3>
                  <div class="compact-badges">
                    <span v-if="show.rating?.average" class="badge-rating">
                      ★ {{ show.rating.average.toFixed(1) }}
                    </span>
                    <span class="badge-year">{{ formatYear(show.premiered) }}</span>
                    <span v-if="show.type" class="badge-type">{{ show.type }}</span>
                    <span v-if="show.status" class="badge-status" :class="show.status === 'Running' ? 'is-running' : 'is-ended'">
                      {{ show.status }}
                    </span>
                  </div>
                </div>

                <div class="compact-actions">
                  <router-link
                    :to="`/show/${show.id}`"
                    class="btn-see-more compact-btn"
                    :aria-label="`See more details about ${show.name}`"
                  >
                    <span>See More</span>
                  </router-link>

                  <button
                    type="button"
                    class="btn-card-add compact-btn"
                    :class="{ 'is-added': isFavorite(show.id) }"
                    @click="toggleFavorite(show)"
                  >
                    <span>{{ isFavorite(show.id) ? 'Saved' : '+ Add' }}</span>
                  </button>
                </div>
              </div>

              <div v-if="show.genres && show.genres.length" class="compact-genres">
                <span v-for="g in show.genres" :key="g" class="compact-genre-pill">
                  {{ g }}
                </span>
              </div>

              <p class="compact-summary">
                {{ stripHtml(show.summary) }}
              </p>
            </div>
          </article>
        </div>

        <!-- Pagination Controls (Strict 12 Cards limit per page) -->
        <div v-if="totalPages > 1" class="pagination-wrapper">
          <button
            type="button"
            class="page-nav-btn"
            :disabled="currentPage === 1"
            @click="goToPage(currentPage - 1)"
            aria-label="Previous page"
          >
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Prev</span>
          </button>

          <div class="page-numbers">
            <button
              v-for="(p, idx) in visiblePageNumbers"
              :key="idx"
              type="button"
              class="page-num-btn"
              :class="{ 'is-active': p === currentPage, 'is-ellipsis': p === '...' }"
              :disabled="p === '...'"
              @click="p !== '...' && goToPage(p)"
            >
              {{ p }}
            </button>
          </div>

          <button
            type="button"
            class="page-nav-btn"
            :disabled="currentPage === totalPages"
            @click="goToPage(currentPage + 1)"
            aria-label="Next page"
          >
            <span>Next</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>
        </div>

        <!-- TVmaze catalogue page loader if reached end of local pool -->
        <div v-if="!searchQuery && !isLoading && currentPage === totalPages && !showFavoritesOnly" class="load-more-wrap">
          <button
            type="button"
            class="btn-load-more"
            @click="loadNextApiPage"
            :disabled="isLoadingMore"
          >
            <span v-if="isLoadingMore">Fetching more titles from TVmaze...</span>
            <span v-else>Load More Titles into Catalogue (+250)</span>
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue';
import { useFavorites } from '../composables/useFavorites';
import { useMovieLibrary } from '../composables/useMovieLibrary';
import {
  formatYear,
  formatEra as formatEraLabel,
  stripHtml,
  FALLBACK_POSTER as fallbackImage
} from '../utils/formatters';

const { favorites, isFavorite, toggleFavorite } = useFavorites();

const {
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
  availableGenres,
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
} = useMovieLibrary(favorites);

watch(
  [selectedGenre, selectedType, selectedEra, selectedStatus, selectedRating, sortBy, activeCategory, showFavoritesOnly],
  () => {
    currentPage.value = 1;
  }
);

onMounted(() => {
  document.title = 'Movie & TV Library - eFlix';
  fetchInitialShows();
});
</script>

<style scoped src="./MovieLibraryView.css"></style>
