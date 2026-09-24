<template>
  <div class="show-details-page">
    
    <div v-if="isLoading" class="site-container details-loading">
      <div class="skeleton-hero">
        <div class="skeleton-poster"></div>
        <div class="skeleton-details">
          <div class="skeleton-line pill"></div>
          <div class="skeleton-line title"></div>
          <div class="skeleton-line meta"></div>
          <div class="skeleton-line text"></div>
          <div class="skeleton-line text"></div>
        </div>
      </div>
    </div>

    
    <div v-else-if="errorMessage" class="site-container details-error">
      <svg viewBox="0 0 24 24" width="56" height="56" stroke="#ff5e57" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
      </svg>
      <h2>Unable to Load Show Details</h2>
      <p>{{ errorMessage }}</p>
      <div class="error-actions">
        <button class="btn-primary" @click="fetchShowDetails">Retry</button>
        <router-link to="/" class="btn-secondary">Return to Home</router-link>
      </div>
    </div>

    
    <div v-else-if="show" class="details-content">
      
      <section class="details-hero">
        <div
          class="hero-ambient-bg"
          :style="{ backgroundImage: `url(${show.image?.original || show.image?.medium || ''})` }"
        ></div>
        <div class="hero-overlay-gradient"></div>

        <div class="site-container hero-container">
          <div class="hero-layout">
            
            <div class="hero-poster-col">
              <div class="poster-card">
                <img
                  :src="show.image?.original || show.image?.medium || fallbackPoster"
                  :alt="show.name"
                  class="poster-img"
                />
                <div v-if="show.rating?.average" class="poster-rating-badge">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="#dca114">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                  </svg>
                  <span class="rating-num">{{ show.rating.average.toFixed(1) }}</span>
                  <span class="rating-max">/10</span>
                </div>
              </div>
            </div>

            
            <div class="hero-info-col">
              <div class="hero-status-badges">
                <span
                  class="badge-pill"
                  :class="show.status === 'Running' ? 'status-running' : 'status-ended'"
                >

                  {{ show.status || 'Status Unknown' }}
                </span>
                <span v-if="show.type" class="badge-pill meta-badge">{{ show.type }}</span>
                <span v-if="show.language" class="badge-pill meta-badge">{{ show.language }}</span>
                <span v-if="networkName" class="badge-pill network-badge">
                  {{ networkName }}
                </span>
              </div>

              <h1 class="page-title">{{ show.name }}</h1>

              <div class="meta-row-info">
                <span v-if="premieredYear" class="meta-item">{{ premieredYear }}</span>
                <span v-if="endedYear && endedYear !== premieredYear" class="meta-item">&ndash; {{ endedYear }}</span>
                <span v-else-if="show.status === 'Running'" class="meta-item">&ndash; Present</span>
                <span class="meta-dot">&bull;</span>
                <span v-if="show.averageRuntime || show.runtime" class="meta-item">
                  {{ show.averageRuntime || show.runtime }} min per episode
                </span>
                <span v-if="scheduleSummary" class="meta-dot">&bull;</span>
                <span v-if="scheduleSummary" class="meta-item">{{ scheduleSummary }}</span>
              </div>

              <div v-if="show.genres && show.genres.length" class="genres-list">
                <span v-for="genre in show.genres" :key="genre" class="genre-pill">
                  {{ genre }}
                </span>
              </div>

              
              <div class="hero-cta-group">
                <button
                  type="button"
                  class="btn-fav-toggle"
                  :class="{ 'is-in-favorites': isFavorite(show.id) }"
                  @click="toggleFavorite(show)"
                >
                  <svg viewBox="0 0 24 24" width="18" height="18" :fill="isFavorite(show.id) ? '#dca114' : 'none'" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                  </svg>
                  <span>{{ isFavorite(show.id) ? 'Saved in Favourites' : '+ Add to Favourites' }}</span>
                </button>

                <router-link
                  to="/library"
                  class="btn-library-nav"
                >
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
                    <polyline points="17 2 12 7 7 2"></polyline>
                  </svg>
                  <span>Explore More Titles</span>
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </section>

      
      <section class="details-body-section">
        <div class="site-container">
          <div class="main-body-grid">
            
            <div class="body-main-col">
              
              <div class="content-block">
                <h2 class="block-title">Storyline</h2>
                <div class="storyline-text" v-html="cleanedSummary"></div>
              </div>

              
              <div class="content-block">
                <div class="block-head-wrap">
                  <h2 class="block-title">Cast &amp; Characters</h2>
                  <span class="block-count" v-if="castList.length">({{ castList.length }} members)</span>
                </div>

                <div v-if="castList.length === 0" class="empty-block">
                  <p>No cast information available for this title.</p>
                </div>
                <div v-else class="cast-grid-display">
                  <div
                    v-for="(item, index) in castList"
                    :key="index"
                    class="cast-member-card"
                  >
                    <div class="cast-thumb-wrap">
                      <img
                        :src="item.person.image?.medium || item.character.image?.medium || fallbackAvatar"
                        :alt="item.person.name"
                        class="cast-thumb"
                        loading="lazy"
                      />
                    </div>
                    <div class="cast-member-info">
                      <strong class="actor-name">{{ item.person.name }}</strong>
                      <span class="char-name">as {{ item.character.name }}</span>
                      <small v-if="item.person.country?.name" class="actor-origin">
                        {{ item.person.country.name }}
                      </small>
                    </div>
                  </div>
                </div>
              </div>

              
              <div class="content-block">
                <div class="block-head-wrap">
                  <h2 class="block-title">Seasons &amp; Episodes Guide</h2>
                  <span class="block-count" v-if="seasonsList.length">({{ seasonsList.length }} seasons)</span>
                </div>

                <div v-if="seasonsList.length === 0" class="empty-block">
                  <p>No season details listed for this title.</p>
                </div>
                <div v-else class="seasons-list-display">
                  <div
                    v-for="season in seasonsList"
                    :key="season.id"
                    class="season-item-card"
                  >
                    <div class="season-thumb-wrap">
                      <img
                        :src="season.image?.medium || show.image?.medium || fallbackPoster"
                        :alt="`Season ${season.number}`"
                        class="season-thumb"
                        loading="lazy"
                      />
                    </div>
                    <div class="season-item-content">
                      <div class="season-item-header">
                        <h3 class="season-title">Season {{ season.number }}</h3>
                        <span v-if="season.episodeOrder" class="episodes-badge">
                          {{ season.episodeOrder }} Episodes
                        </span>
                      </div>
                      <div class="season-meta">
                        <span v-if="season.premiereDate">Aired: {{ formatDate(season.premiereDate) }}</span>
                        <span v-if="season.endDate"> &ndash; {{ formatDate(season.endDate) }}</span>
                        <span v-if="season.network?.name"> &bull; {{ season.network.name }}</span>
                      </div>
                      <div
                        v-if="season.summary"
                        class="season-summary-text"
                        v-html="stripExternalLinks(season.summary)"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            
            <div class="body-side-col">
              <div class="specs-card">
                <h3 class="specs-card-title">Show Information</h3>
                <dl class="specs-list">
                  <div class="spec-row" v-if="networkWithCountry">
                    <dt>Network / Channel</dt>
                    <dd>{{ networkWithCountry }}</dd>
                  </div>
                  <div class="spec-row" v-if="scheduleDetails">
                    <dt>Air Schedule</dt>
                    <dd>{{ scheduleDetails }}</dd>
                  </div>
                  <div class="spec-row" v-if="show.status">
                    <dt>Status</dt>
                    <dd>{{ show.status }}</dd>
                  </div>
                  <div class="spec-row" v-if="show.premiered">
                    <dt>First Premiered</dt>
                    <dd>{{ formatDate(show.premiered) }}</dd>
                  </div>
                  <div class="spec-row" v-if="show.ended">
                    <dt>Series Ended</dt>
                    <dd>{{ formatDate(show.ended) }}</dd>
                  </div>
                  <div class="spec-row" v-if="show.averageRuntime || show.runtime">
                    <dt>Episode Runtime</dt>
                    <dd>{{ show.averageRuntime || show.runtime }} minutes</dd>
                  </div>
                  <div class="spec-row" v-if="show.type">
                    <dt>Show Type</dt>
                    <dd>{{ show.type }}</dd>
                  </div>
                  <div class="spec-row" v-if="show.language">
                    <dt>Original Language</dt>
                    <dd>{{ show.language }}</dd>
                  </div>
                  <div class="spec-row" v-if="totalEpisodesCount">
                    <dt>Total Episodes</dt>
                    <dd>{{ totalEpisodesCount }}</dd>
                  </div>
                  <div class="spec-row" v-if="show.weight">
                    <dt>Popularity Score</dt>
                    <dd>{{ show.weight }}</dd>
                  </div>
                </dl>
              </div>

              
              <div class="side-cta-card">
                <h4>Build your personal watchlist</h4>
                <p>Add <strong>{{ show.name }}</strong> to your favourites grid to access it anytime.</p>
                <button
                  type="button"
                  class="btn-primary full-width"
                  @click="toggleFavorite(show)"
                >
                  {{ isFavorite(show.id) ? 'Remove from Favourites' : '+ Add to Favourites' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useFavorites } from '../composables/useFavorites';
import { tvmazeApi } from '../services/tvmazeApi';
import {
  FALLBACK_POSTER as fallbackPoster,
  FALLBACK_AVATAR as fallbackAvatar,
  formatDate,
  stripExternalLinks
} from '../utils/formatters';
import logoImg from '../assets/logo/apple-touch-icon.png';

const route = useRoute();
const { isFavorite, toggleFavorite } = useFavorites();

const show = ref(null);
const isLoading = ref(true);
const errorMessage = ref('');

const fetchShowDetails = async () => {
  const showId = route.params.id;
  if (!showId) return;

  isLoading.value = true;
  errorMessage.value = '';

  try {
    const data = await tvmazeApi.getShowById(showId, {
      embedCast: true,
      embedSeasons: true,
      embedEpisodes: true
    });
    show.value = data;

    if (data.name) {
      document.title = `${data.name} - Movie Library`;
    }
  } catch (err) {
    console.error('Error fetching show details:', err);
    errorMessage.value = 'Could not load show information from TVmaze. Please check your connection and try again.';
  } finally {
    isLoading.value = false;
  }
};

watch(
  () => route.params.id,
  (newId) => {
    if (newId) {
      fetchShowDetails();
    }
  }
);

onMounted(() => {
  fetchShowDetails();
});

const networkName = computed(() => {
  if (!show.value) return '';
  return show.value.network?.name || show.value.webChannel?.name || '';
});

const networkWithCountry = computed(() => {
  if (!show.value) return '';
  const net = show.value.network || show.value.webChannel;
  if (!net) return '';
  if (net.country?.name) {
    return `${net.name} (${net.country.name})`;
  }
  return net.name;
});

const premieredYear = computed(() => {
  if (!show.value?.premiered) return '';
  return show.value.premiered.split('-')[0];
});

const endedYear = computed(() => {
  if (!show.value?.ended) return '';
  return show.value.ended.split('-')[0];
});

const scheduleSummary = computed(() => {
  if (!show.value?.schedule) return '';
  const { days, time } = show.value.schedule;
  if (!days?.length && !time) return '';
  if (days?.length && time) return `${days.join(', ')} at ${time}`;
  if (days?.length) return days.join(', ');
  return time;
});

const scheduleDetails = computed(() => {
  if (!show.value?.schedule) return '';
  const { days, time } = show.value.schedule;
  if (!days?.length && !time) return '';
  const daysStr = days?.length ? days.join(', ') : 'Daily';
  const timeStr = time ? `at ${time}` : '';
  const timezone = show.value.network?.country?.timezone ? `(${show.value.network.country.timezone})` : '';
  return `${daysStr} ${timeStr} ${timezone}`.trim();
});

const cleanedSummary = computed(() => {
  if (!show.value?.summary) return '<p>No detailed summary provided for this title.</p>';
  return stripExternalLinks(show.value.summary);
});

const castList = computed(() => {
  return show.value?._embedded?.cast || [];
});

const seasonsList = computed(() => {
  return show.value?._embedded?.seasons || [];
});

const totalEpisodesCount = computed(() => {
  if (show.value?._embedded?.episodes?.length) {
    return show.value._embedded.episodes.length;
  }
  const seasons = seasonsList.value;
  if (seasons.length > 0) {
    const total = seasons.reduce((acc, curr) => acc + (curr.episodeOrder || 0), 0);
    if (total > 0) return total;
  }
  return null;
});

</script>

<style scoped src="./ShowDetailsView.css"></style>
