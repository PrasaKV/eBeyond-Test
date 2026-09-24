<template>
  <section class="recommendations-section" id="recommendations" aria-label="Randomised Recommendations">
    <div class="site-container">
      <div class="section-header">
        <div class="header-titles">
          <h2 class="section-title">DISCOVER &amp; EXPLORE</h2>
          <p class="section-subtitle">
            A randomised selection of top TV shows and series. Hover over any title to pause scrolling and explore.
          </p>
        </div>

        <div class="header-controls">
          <div class="nav-arrows">
            <button
              type="button"
              class="arrow-btn"
              @click="scrollManual(-320)"
              aria-label="Scroll recommendations left"
            >
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
            </button>
            <button
              type="button"
              class="arrow-btn"
              @click="scrollManual(320)"
              aria-label="Scroll recommendations right"
            >
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Scroller Track Container -->
    <div
      class="scroller-outer"
      @mouseenter="handleMouseEnter"
      @mouseleave="handleMouseLeave"
      @touchstart="handleTouchStart"
      @touchend="handleTouchEnd"
    >
      <!-- Gradient masks on sides for sleek fade effect -->
      <div class="edge-mask mask-left"></div>
      <div class="edge-mask mask-right"></div>

      <!-- Loading skeleton -->
      <div v-if="isLoading && recommendations.length === 0" class="skeleton-row">
        <div v-for="n in 6" :key="n" class="skeleton-card">
          <div class="skeleton-poster"></div>
          <div class="skeleton-line title"></div>
          <div class="skeleton-line text"></div>
          <div class="skeleton-line btn"></div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="fetchError && recommendations.length === 0" class="error-container site-container">
        <p>{{ fetchError }}</p>
        <button class="btn-primary" @click="fetchRandomRecommendations">Try Again</button>
      </div>

      <!-- Scrolling Single Row -->
      <div
        v-else
        class="scroller-track"
        ref="trackRef"
        @scroll="onTrackScroll"
      >
        <article
          v-for="(show, index) in displayShows"
          :key="`${show.id}-${index}`"
          class="recommendation-card"
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
              <svg viewBox="0 0 24 24" width="12" height="12" fill="#dca114">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
              </svg>
              <span>{{ show.rating.average.toFixed(1) }}</span>
            </div>

            <span v-if="show.genres && show.genres.length" class="card-genre-tag">
              {{ show.genres[0] }}
            </span>
          </div>

          <div class="card-content">
            <div class="card-meta">
              <span class="meta-year">{{ formatYear(show.premiered) }}</span>
              <span v-if="show.network?.name || show.webChannel?.name" class="meta-net">
                &bull; {{ show.network?.name || show.webChannel?.name }}
              </span>
            </div>

            <h3 class="card-title" :title="show.name">{{ show.name }}</h3>

            <p class="card-summary">
              {{ stripHtml(show.summary) }}
            </p>

            <div class="card-actions">
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
                <span>{{ isFavorite(show.id) ? 'Added' : 'Add' }}</span>
              </button>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useFavorites } from '../composables/useFavorites';
import { useRecommendations } from '../composables/useRecommendations';
import { formatYear, stripHtml, FALLBACK_POSTER as fallbackImage } from '../utils/formatters';

const { isFavorite, toggleFavorite } = useFavorites();
const {
  recommendations,
  isLoading,
  fetchError,
  trackRef,
  displayShows,
  fetchRandomRecommendations,
  scrollManual,
  onTrackScroll,
  handleMouseEnter,
  handleMouseLeave,
  handleTouchStart,
  handleTouchEnd
} = useRecommendations();
</script>

<style scoped src="./RecommendationsSection.css"></style>
