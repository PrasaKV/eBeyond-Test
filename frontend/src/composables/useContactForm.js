import { ref, reactive } from 'vue';

export function useContactForm() {
  const form = reactive({
    firstName: '',
    lastName: '',
    email: '',
    telephone: '',
    message: '',
    terms: false
  });

  const errors = reactive({
    firstName: '',
    lastName: '',
    email: '',
    message: '',
    terms: ''
  });

  const isSubmitting = ref(false);
  const successMessage = ref('');
  const serverError = ref('');
  const showTermsModal = ref(false);

  const clearFieldError = (field) => {
    errors[field] = '';
    serverError.value = '';
  };

  const validateForm = () => {
    let isValid = true;

    errors.firstName = '';
    errors.lastName = '';
    errors.email = '';
    errors.message = '';
    errors.terms = '';

    if (!form.firstName) {
      errors.firstName = 'First name is required.';
      isValid = false;
    }

    if (!form.lastName) {
      errors.lastName = 'Last name is required.';
      isValid = false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!form.email) {
      errors.email = 'Email is required.';
      isValid = false;
    } else if (!emailRegex.test(form.email)) {
      errors.email = 'Please enter a valid email address.';
      isValid = false;
    }

    if (!form.message) {
      errors.message = 'Message is required.';
      isValid = false;
    }

    if (!form.terms) {
      errors.terms = 'You must agree to the Terms & Conditions.';
      isValid = false;
    }

    return isValid;
  };

  const acceptTerms = () => {
    form.terms = true;
    errors.terms = '';
    showTermsModal.value = false;
  };

  const resetForm = () => {
    form.firstName = '';
    form.lastName = '';
    form.email = '';
    form.telephone = '';
    form.message = '';
    form.terms = false;
  };

  const handleSubmit = async () => {
    serverError.value = '';
    successMessage.value = '';

    if (!validateForm()) {
      return;
    }

    isSubmitting.value = true;

    try {
      const apiBase = (import.meta.env.VITE_API_BASE_URL).replace(/\/+$/, '');
      const response = await fetch(`${apiBase}/api/contact.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          firstName: form.firstName,
          lastName: form.lastName,
          email: form.email,
          telephone: form.telephone,
          message: form.message,
          terms: form.terms
        })
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        if (data.errors) {
          Object.keys(data.errors).forEach((key) => {
            errors[key] = data.errors[key];
          });
        } else {
          serverError.value = data.message || 'Submission failed. Please check the form and try again.';
        }
        return;
      }

      successMessage.value = data.message || 'Thank you! Your message has been sent successfully.';
      resetForm();
    } catch (err) {
      serverError.value = 'Network error: could not connect to server. Please ensure the backend is running.';
    } finally {
      isSubmitting.value = false;
    }
  };

  return {
    form,
    errors,
    isSubmitting,
    successMessage,
    serverError,
    showTermsModal,
    clearFieldError,
    validateForm,
    acceptTerms,
    resetForm,
    handleSubmit
  };
}
