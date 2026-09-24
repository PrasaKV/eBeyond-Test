<template>
  <section class="hero-banner" id="home" aria-label="Cinema Hall Visual">
    <div class="hero-media-wrapper">
      <video
        ref="videoRef"
        class="hero-video"
        autoplay
        loop
        muted
        playsinline
        preload="auto"
      >
        <source :src="heroVideo" type="video/mp4" />
        <img
          src="../assets/hero.jpg"
          alt="Cinema auditorium theater seats"
          class="hero-image"
        />
      </video>
      <div class="hero-vignette-bottom"></div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import heroVideo from '../assets/hero.mp4';

const videoRef = ref(null);

onMounted(() => {
  if (videoRef.value) {
    videoRef.value.muted = true;
    videoRef.value.play().catch(() => {
      // Auto-play was prevented by browser policy
    });
  }
});
</script>

<style scoped src="./HeroBanner.css"></style>

