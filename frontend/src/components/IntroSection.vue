<template>
  <section class="intro-section" id="library" aria-label="Movie Library Introduction">
    <div class="site-container">
      <div class="intro-content">
        <h1 class="intro-title">MOVIE LIBRARY</h1>
        <p class="intro-text">
          Explore thousands of TV series, films, and documentaries with our interactive catalogue. Filter by genre, broadcast status, and ratings to discover your next favourite show.
        </p>
        <div class="intro-actions">
          <router-link to="/library" class="btn-explore-library">
            <span>Explore Full Library</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </router-link>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
</script>

<style scoped src="./IntroSection.css"></style>
