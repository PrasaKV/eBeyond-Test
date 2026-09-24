<template>
  <teleport to="body">
    <transition name="modal-backdrop-fade">
      <div
        v-if="isDetailsOpen"
        class="modal-backdrop"
        @click="handleBackdropClick"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-show-title"
      >
        <div class="modal-dialog" @click.stop ref="modalDialogRef">
          <!-- Close button -->
          <button
            type="button"
            class="modal-close-btn"
            @click="closeShowDetails"
            aria-label="Close details modal"
          >
            <svg viewBox="0 0 24 24" width="22" height="22" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>

          <!-- Loading state -->
          <div v-if="isLoading" class="modal-loading-state">
            <div class="spinner"></div>
            <p>Fetching full show details from TVmaze...</p>
          </div>

          <!-- Error state -->
          <div v-else-if="errorMessage" class="modal-error-state">
            <svg viewBox="0 0 24 24" width="48" height="48" stroke="#ff6b6b" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <h3>Could not load show information</h3>
            <p>{{ errorMessage }}</p>
            <button class="btn-primary" @click="fetchShowDetails">Retry</button>
          </div>

          <!-- Content display -->
          <div v-else-if="show" class="modal-content-wrap">
            <!-- Hero banner with backdrop blur -->
            <div class="modal-hero">
              <div
                class="hero-bg-blur"
                :style="{ backgroundImage: `url(${show.image?.original || show.image?.medium || ''})` }"
              ></div>
              <div class="hero-gradient-overlay"></div>

              <div class="hero-inner">
                <div class="poster-column">
                  <div class="poster-frame">
                    <img
                      :src="show.image?.original || show.image?.medium || fallbackPoster"
                      :alt="show.name"
                      class="poster-image"
                    />
                    <div v-if="show.rating?.average" class="poster-rating-badge">
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="#dca114">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                      </svg>
                      <span>{{ show.rating.average.toFixed(1) }}</span>
                      <small>/10</small>
                    </div>
                  </div>
                </div>

                <div class="hero-text-column">
                  <div class="hero-badges">
                    <span
                      class="status-pill"
                      :class="show.status === 'Running' ? 'status-running' : 'status-ended'"
                    >
                      <span class="status-dot"></span>
                      {{ show.status || 'Unknown' }}
                    </span>
                    <span v-if="show.type" class="meta-pill">{{ show.type }}</span>
                    <span v-if="show.language" class="meta-pill">{{ show.language }}</span>
                    <span v-if="networkName" class="network-pill">
                      {{ networkName }}
                    </span>
                  </div>

                  <h2 id="modal-show-title" class="show-title">{{ show.name }}</h2>

                  <div class="show-quick-facts">
                    <span v-if="premieredYear">{{ premieredYear }}</span>
                    <span v-if="endedYear && endedYear !== premieredYear">&ndash; {{ endedYear }}</span>
                    <span v-else-if="show.status === 'Running'">&ndash; Present</span>
                    <span class="dot-separator">&bull;</span>
                    <span v-if="show.averageRuntime || show.runtime">
                      {{ show.averageRuntime || show.runtime }} mins
                    </span>
                    <span v-if="scheduleText" class="dot-separator">&bull;</span>
                    <span v-if="scheduleText">{{ scheduleText }}</span>
                  </div>

                  <div v-if="show.genres && show.genres.length" class="genres-row">
                    <span v-for="genre in show.genres" :key="genre" class="genre-tag">
                      {{ genre }}
                    </span>
                  </div>

                  <div class="hero-actions">
                    <button
                      type="button"
                      class="btn-favorite"
                      :class="{ 'is-in-favorites': isFavorite(show.id) }"
                      @click="toggleFavorite(show)"
                    >
                      <svg viewBox="0 0 24 24" width="18" height="18" :fill="isFavorite(show.id) ? '#dca114' : 'none'" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                      </svg>
                      <span>{{ isFavorite(show.id) ? 'In Favourites' : '+ Add to Favourites' }}</span>
                    </button>

                    <a
                      v-if="show.officialSite"
                      :href="show.officialSite"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="btn-outline"
                    >
                      <span>Official Site</span>
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                      </svg>
                    </a>

                    <a
                      v-if="show.url"
                      :href="show.url"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="btn-outline"
                    >
                      <span>TVmaze</span>
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                      </svg>
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab navigation -->
            <div class="modal-tabs-bar">
              <button
                type="button"
                class="tab-btn"
                :class="{ active: activeTab === 'overview' }"
                @click="activeTab = 'overview'"
              >
                Overview
              </button>
              <button
                type="button"
                class="tab-btn"
                :class="{ active: activeTab === 'cast' }"
                @click="activeTab = 'cast'"
              >
                Cast &amp; Characters ({{ castList.length }})
              </button>
              <button
                type="button"
                class="tab-btn"
                :class="{ active: activeTab === 'seasons' }"
                @click="activeTab = 'seasons'"
              >
                Seasons ({{ seasonsList.length }})
              </button>
            </div>

            <!-- Tab contents -->
            <div class="modal-body-content">
              <!-- OVERVIEW TAB -->
              <div v-if="activeTab === 'overview'" class="tab-panel">
                <div class="overview-grid">
                  <div class="overview-main">
                    <h3 class="section-subheading">Storyline</h3>
                    <div class="synopsis-content" v-html="cleanedSummary"></div>

                    <div class="meta-data-table">
                      <div class="meta-row" v-if="show.premiered">
                        <span class="meta-label">Premiered</span>
                        <span class="meta-value">{{ formatDate(show.premiered) }}</span>
                      </div>
                      <div class="meta-row" v-if="show.ended">
                        <span class="meta-label">Ended</span>
                        <span class="meta-value">{{ formatDate(show.ended) }}</span>
                      </div>
                      <div class="meta-row" v-if="networkWithCountry">
                        <span class="meta-label">Network / Channel</span>
                        <span class="meta-value">{{ networkWithCountry }}</span>
                      </div>
                      <div class="meta-row" v-if="scheduleDetails">
                        <span class="meta-label">Broadcast Schedule</span>
                        <span class="meta-value">{{ scheduleDetails }}</span>
                      </div>
                      <div class="meta-row" v-if="show.type">
                        <span class="meta-label">Show Type</span>
                        <span class="meta-value">{{ show.type }}</span>
                      </div>
                      <div class="meta-row" v-if="show.language">
                        <span class="meta-label">Original Language</span>
                        <span class="meta-value">{{ show.language }}</span>
                      </div>
                      <div class="meta-row" v-if="show.weight">
                        <span class="meta-label">Popularity Score</span>
                        <span class="meta-value">{{ show.weight }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Quick side card -->
                  <div class="overview-side">
                    <div class="side-card">
                      <h4 class="side-card-title">Series Summary</h4>
                      <ul class="side-stats-list">
                        <li>
                          <span class="stat-name">Total Seasons</span>
                          <span class="stat-val">{{ seasonsList.length || 'N/A' }}</span>
                        </li>
                        <li>
                          <span class="stat-name">Total Episodes</span>
                          <span class="stat-val">{{ totalEpisodesCount }}</span>
                        </li>
                        <li>
                          <span class="stat-name">User Rating</span>
                          <span class="stat-val rating-val">
                            {{ show.rating?.average ? `${show.rating.average} / 10` : 'Not Rated' }}
                          </span>
                        </li>
                        <li>
                          <span class="stat-name">Status</span>
                          <span class="stat-val">{{ show.status || 'N/A' }}</span>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

              <!-- CAST TAB -->
              <div v-else-if="activeTab === 'cast'" class="tab-panel">
                <div v-if="castList.length === 0" class="empty-panel">
                  <p>No cast information available for this title.</p>
                </div>
                <div v-else class="cast-grid">
                  <div
                    v-for="(item, index) in castList"
                    :key="index"
                    class="cast-card"
                  >
                    <div class="cast-photo-frame">
                      <img
                        :src="item.person.image?.medium || item.character.image?.medium || fallbackAvatar"
                        :alt="item.person.name"
                        class="cast-photo"
                        loading="lazy"
                      />
                    </div>
                    <div class="cast-info">
                      <h4 class="person-name">{{ item.person.name }}</h4>
                      <p class="character-name">as {{ item.character.name }}</p>
                      <span v-if="item.person.country?.name" class="person-country">
                        {{ item.person.country.name }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- SEASONS TAB -->
              <div v-else-if="activeTab === 'seasons'" class="tab-panel">
                <div v-if="seasonsList.length === 0" class="empty-panel">
                  <p>No season details listed for this title.</p>
                </div>
                <div v-else class="seasons-grid">
                  <div
                    v-for="season in seasonsList"
                    :key="season.id"
                    class="season-card"
                  >
                    <div class="season-poster-frame">
                      <img
                        :src="season.image?.medium || show.image?.medium || fallbackPoster"
                        :alt="`Season ${season.number}`"
                        class="season-poster"
                        loading="lazy"
                      />
                    </div>
                    <div class="season-details">
                      <div class="season-head">
                        <h4 class="season-number">Season {{ season.number }}</h4>
                        <span class="season-badge" v-if="season.episodeOrder">
                          {{ season.episodeOrder }} Episodes
                        </span>
                      </div>
                      <p class="season-dates" v-if="season.premiereDate">
                        Aired: {{ formatDate(season.premiereDate) }}
                        <span v-if="season.endDate"> &ndash; {{ formatDate(season.endDate) }}</span>
                      </p>
                      <p v-if="season.network?.name" class="season-net">
                        Channel: {{ season.network.name }}
                      </p>
                      <div
                        v-if="season.summary"
                        class="season-summary"
                        v-html="stripOrClean(season.summary)"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useFavorites } from '../composables/useFavorites';
import { useShowDetails } from '../composables/useShowDetails';
import { tvmazeApi } from '../services/tvmazeApi';
import {
  formatDate,
  formatYear,
  stripExternalLinks as stripOrClean,
  FALLBACK_POSTER as fallbackPoster,
  FALLBACK_AVATAR as fallbackAvatar
} from '../utils/formatters';

const { isFavorite, toggleFavorite } = useFavorites();
const { isDetailsOpen, activeShowId, initialShowData, closeShowDetails } = useShowDetails();

const show = ref(null);
const isLoading = ref(false);
const errorMessage = ref('');
const activeTab = ref('overview');
const modalDialogRef = ref(null);

const fetchShowDetails = async () => {
  if (!activeShowId.value) return;

  isLoading.value = true;
  errorMessage.value = '';
  activeTab.value = 'overview';

  if (initialShowData.value) {
    show.value = { ...initialShowData.value };
  }

  try {
    const data = await tvmazeApi.getShowById(activeShowId.value, { embedCast: true, embedSeasons: true });
    show.value = data;
  } catch (err) {
    console.error('Error fetching show details:', err);
    if (!show.value) {
      errorMessage.value = err.message || 'Unable to retrieve show information. Please check your connection.';
    }
  } finally {
    isLoading.value = false;
  }
};

watch(
  () => activeShowId.value,
  (newId) => {
    if (newId && isDetailsOpen.value) {
      fetchShowDetails();
    }
  }
);

watch(
  () => isDetailsOpen.value,
  (open) => {
    if (open && activeShowId.value) {
      fetchShowDetails();
    }
  }
);

const handleBackdropClick = (e) => {
  if (modalDialogRef.value && !modalDialogRef.value.contains(e.target)) {
    closeShowDetails();
  }
};

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && isDetailsOpen.value) {
    closeShowDetails();
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

// Computed properties for formatting
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
  return formatYear(show.value.premiered);
});

const endedYear = computed(() => {
  if (!show.value?.ended) return '';
  return formatYear(show.value.ended);
});

const scheduleText = computed(() => {
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
  return show.value.summary;
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
  return 'N/A';
});
</script>

<style scoped src="./ShowDetailsModal.css"></style>
