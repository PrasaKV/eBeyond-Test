<template>
  <section class="contact-section" id="contact" aria-label="Contact Us">
    <div class="site-container">
      <div class="contact-heading-wrap">
        <h2 class="contact-title">How to reach us</h2>
        <p class="contact-subtitle">Have questions or need assistance? Reach out to our team today.</p>
      </div>

      <div class="contact-grid">
        <div class="form-column">
          <form class="contact-form" @submit.prevent="handleSubmit" novalidate>
            <div v-if="successMessage" class="alert-success" role="alert">
              {{ successMessage }}
            </div>

            <div v-if="serverError" class="alert-error" role="alert">
              {{ serverError }}
            </div>

            <div class="form-row-2">
              <div class="form-group">
                <label for="firstName" class="form-label">First Name *</label>
                <input
                  type="text"
                  id="firstName"
                  class="form-control"
                  :class="{ 'is-invalid': errors.firstName }"
                  v-model.trim="form.firstName"
                  @input="clearFieldError('firstName')"
                  autocomplete="given-name"
                />
                <span v-if="errors.firstName" class="error-text">{{ errors.firstName }}</span>
              </div>

              <div class="form-group">
                <label for="lastName" class="form-label">Last Name *</label>
                <input
                  type="text"
                  id="lastName"
                  class="form-control"
                  :class="{ 'is-invalid': errors.lastName }"
                  v-model.trim="form.lastName"
                  @input="clearFieldError('lastName')"
                  autocomplete="family-name"
                />
                <span v-if="errors.lastName" class="error-text">{{ errors.lastName }}</span>
              </div>
            </div>

            <div class="form-group">
              <label for="email" class="form-label">Email *</label>
              <input
                type="email"
                id="email"
                class="form-control"
                :class="{ 'is-invalid': errors.email }"
                v-model.trim="form.email"
                @input="clearFieldError('email')"
                autocomplete="email"
              />
              <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
            </div>

            <div class="form-group">
              <label for="telephone" class="form-label">Telephone</label>
              <input
                type="tel"
                id="telephone"
                class="form-control"
                v-model.trim="form.telephone"
                autocomplete="tel"
              />
            </div>

            <div class="form-group">
              <label for="message" class="form-label">Message</label>
              <textarea
                id="message"
                rows="5"
                class="form-control textarea-control"
                :class="{ 'is-invalid': errors.message }"
                v-model.trim="form.message"
                @input="clearFieldError('message')"
              ></textarea>
              <span v-if="errors.message" class="error-text">{{ errors.message }}</span>
            </div>

            <div class="form-notes">
              <span class="required-note">*required fields</span>
            </div>

            <div class="form-group terms-group">
              <label class="checkbox-container">
                <input
                  type="checkbox"
                  id="terms"
                  v-model="form.terms"
                  @change="clearFieldError('terms')"
                />
                <span class="custom-checkbox"></span>
                <span class="checkbox-label">
                  I agree to the
                  <button type="button" class="terms-link" @click="showTermsModal = true">
                    Terms &amp; Conditions
                  </button>
                </span>
              </label>
              <span v-if="errors.terms" class="error-text block-error">{{ errors.terms }}</span>
            </div>

            <div class="form-action">
              <button
                type="submit"
                class="btn-primary submit-btn"
                :disabled="isSubmitting"
              >
                {{ isSubmitting ? 'SUBMITTING...' : 'SUBMIT' }}
              </button>
            </div>
          </form>
        </div>

        <div class="map-column">
          <div class="map-container">
            <iframe
              title="eBEYONDS Office Location"
              src="https://maps.google.com/maps?q=6.8448775,79.940426&hl=en&z=16&output=embed"
              class="map-frame"
              loading="lazy"
              allowfullscreen
            ></iframe>
          </div>
        </div>
      </div>
    </div>

    <transition name="modal-fade">
      <div v-if="showTermsModal" class="modal-backdrop" @click="showTermsModal = false">
        <div class="modal-card" @click.stop>
          <div class="modal-header">
            <h3 class="modal-title">Terms &amp; Conditions</h3>
            <button class="modal-close" @click="showTermsModal = false" aria-label="Close modal">&times;</button>
          </div>
          <div class="modal-body">
            <p>Welcome to eFlix Entertainment. By submitting your information through this form, you agree to allow us to process your contact details to respond to your inquiry and provide updates regarding our services.</p>
            <p>We respect your privacy and will not disclose, sell, or rent your personal data to any unauthorized third parties. For questions about how your data is handled, feel free to contact us.</p>
          </div>
          <div class="modal-footer">
            <button class="btn-primary" @click="acceptTerms">I Understand &amp; Agree</button>
          </div>
        </div>
      </div>
    </transition>
  </section>
</template>

<script setup>
import { useContactForm } from '../composables/useContactForm';

const {
  form,
  errors,
  isSubmitting,
  successMessage,
  serverError,
  showTermsModal,
  clearFieldError,
  acceptTerms,
  handleSubmit
} = useContactForm();
</script>

<style scoped src="./ContactSection.css"></style>
