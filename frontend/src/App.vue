<template>
  <div class="app-root">
    <AppHeader />
    <main id="main-content">
      <router-view />
    </main>
    <AppFooter />
  </div>
</template>

<script setup>
import AppHeader from './components/AppHeader.vue';
import AppFooter from './components/AppFooter.vue';
</script>

<style scoped>
.app-root {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #000000;
}

#main-content {
  flex: 1;
}
</style>
