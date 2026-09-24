<template>
  <div class="home-view">
    <HeroBanner />
    <IntroSection />
    <RecommendationsSection />
    <FavoritesGrid />
    <ContactSection />
  </div>
</template>

<script setup>
import HeroBanner from '../components/HeroBanner.vue';
import IntroSection from '../components/IntroSection.vue';
import RecommendationsSection from '../components/RecommendationsSection.vue';
import FavoritesGrid from '../components/FavoritesGrid.vue';
import ContactSection from '../components/ContactSection.vue';
</script>

<style scoped src="./HomeView.css"></style>
