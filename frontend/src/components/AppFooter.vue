<template>
  <footer class="app-footer" aria-label="Site Footer">
    <div class="site-container">
      <div class="footer-top">
        <div class="footer-brand-wrap">
          <router-link to="/" class="footer-logo-link" aria-label="eFlix Home">
            <div class="footer-logo-circle">
              <img :src="logoImg" alt="eFlix Logo" class="footer-logo-img" />
            </div>
            <span class="footer-brand-title">eFlix</span>
          </router-link>
          <div class="footer-address">
            <strong class="company-name">eFlix Entertainment</strong>
            <p>370 Level, 4 High Level Rd</p>
            <p>Pannipitiya, Sri Lanka</p>
          </div>
        </div>

        <div class="footer-social-column">
          <div class="footer-social">
            <span class="social-label">Follow us on</span>
            <div class="social-icons">
              <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Twitter">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                  <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
              </a>
              <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="YouTube">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true">
                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
              </a>
            </div>
          </div>
          <div class="footer-dir-wrap">
            <button
              id="direction-toggle-btn"
              class="footer-dir-btn"
              @click="toggleDirection"
              :aria-label="`Toggle reading direction between Left To Right and Right To Left, currently ${currentDir === 'ltr' ? 'Left To Right' : 'Right To Left'}`"
            >
              <span class="dir-icon" aria-hidden="true">⇄</span>
              <span>Read from {{ currentDir === 'ltr' ? 'Left To Right' : 'Right To Left' }}</span>
            </button>
          </div>
        </div>
      </div>

      <div class="footer-divider"></div>

      <div class="footer-bottom">
        <p class="copyright-text">Copyright &copy; 2026 eFlix. All rights reserved.</p>
        <p class="credits-text">
          Powered by
          <a href="https://www.tvmaze.com" target="_blank" rel="noopener noreferrer">TVmaze API</a>
          &bull; Photos via Unsplash
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import logoImg from '../assets/logo/apple-touch-icon.png';

const currentDir = ref('ltr');

const applyDirection = (dir) => {
  currentDir.value = dir;
  document.documentElement.setAttribute('dir', dir);
  document.documentElement.setAttribute('lang', dir === 'rtl' ? 'ar' : 'en');
  try {
    localStorage.setItem('eflix_direction', dir);
  } catch (e) {
  }
};

const toggleDirection = () => {
  const nextDir = currentDir.value === 'ltr' ? 'rtl' : 'ltr';
  applyDirection(nextDir);
};

onMounted(() => {
  try {
    const savedDir = localStorage.getItem('eflix_direction');
    if (savedDir === 'rtl' || savedDir === 'ltr') {
      applyDirection(savedDir);
      return;
    }
  } catch (e) {
  }
  const htmlDir = document.documentElement.getAttribute('dir') || 'ltr';
  currentDir.value = htmlDir;
});
</script>

<style scoped src="./AppFooter.css"></style>
