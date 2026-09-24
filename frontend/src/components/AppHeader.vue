<template>
  <header class="header">
    <div class="header-inner site-container">
      <router-link to="/" class="brand-logo" aria-label="eFlix Homepage">
        <div class="logo-circle">
          <img :src="logoImg" alt="eFlix" class="brand-logo-img" />
        </div>
        <span class="brand-text">eFlix</span>
      </router-link>

      <nav class="desktop-nav" aria-label="Main Navigation">
        <ul class="nav-list">
          <li><a href="/#home" class="nav-link">HOME</a></li>
          <li><a href="/#recommendations" class="nav-link">RECOMMENDED</a></li>
          <li><a href="/#screens" class="nav-link">OUR SCREENS</a></li>
          <li><router-link to="/library" class="nav-link">MOVIE LIBRARY</router-link></li>
          <li class="nav-contact"><a href="/#contact" class="nav-link">LOCATION & CONTACT</a></li>
        </ul>
      </nav>

      <button
        class="hamburger-btn"
        :class="{ 'is-active': isMenuOpen }"
        @click="toggleMenu"
        :aria-expanded="isMenuOpen"
        aria-controls="mobile-drawer"
        aria-label="Toggle navigation menu"
      >
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
      </button>
    </div>

    <transition name="drawer-fade">
      <div v-if="isMenuOpen" class="drawer-backdrop" @click="closeMenu">
        <div id="mobile-drawer" class="drawer-content" @click.stop>
          <div class="drawer-header">
            <div class="drawer-brand">
              <div class="logo-circle drawer-logo-circle">
                <img :src="logoImg" alt="eFlix" class="brand-logo-img" />
              </div>
              <span class="drawer-title">eFlix</span>
            </div>
            <button class="drawer-close" @click="closeMenu" aria-label="Close menu">&times;</button>
          </div>
          <ul class="drawer-list">
            <li><a href="/#home" class="drawer-link" @click="closeMenu">HOME</a></li>
            <li><a href="/#recommendations" class="drawer-link" @click="closeMenu">RECOMMENDED</a></li>
            <li><a href="/#screens" class="drawer-link" @click="closeMenu">OUR SCREENS</a></li>
            <li><router-link to="/library" class="drawer-link" @click="closeMenu">MOVIE LIBRARY</router-link></li>
            <li><a href="/#contact" class="drawer-link" @click="closeMenu">LOCATION & CONTACT</a></li>
          </ul>
        </div>
      </div>
    </transition>
  </header>
</template>

<script setup>
import { ref } from 'vue';
import logoImg from '../assets/logo/apple-touch-icon.png';

const isMenuOpen = ref(false);

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
  if (isMenuOpen.value) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
};

const closeMenu = () => {
  isMenuOpen.value = false;
  document.body.style.overflow = '';
};
</script>

<style scoped src="./AppHeader.css"></style>
