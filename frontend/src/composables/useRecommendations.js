import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { tvmazeApi } from '../services/tvmazeApi';
import { shuffleArray } from '../utils/formatters';

export function useRecommendations() {
  const recommendations = ref([]);
  const isLoading = ref(false);
  const fetchError = ref('');
  const trackRef = ref(null);
  const isHovered = ref(false);
  const isInteracting = ref(false);

  // Return original + duplicate for continuous wrapping
  const displayShows = computed(() => {
    if (recommendations.value.length === 0) return [];
    return [...recommendations.value, ...recommendations.value];
  });

  const fetchRandomRecommendations = async () => {
    isLoading.value = true;
    fetchError.value = '';

    try {
      const valid = await tvmazeApi.getRandomShows(20);
      if (valid.length === 0) {
        throw new Error('No valid shows found on this page.');
      }
      const shuffled = shuffleArray(valid).slice(0, 18);
      recommendations.value = shuffled;

      if (trackRef.value) {
        trackRef.value.scrollLeft = 0;
      }
    } catch (err) {
      console.error('Failed to fetch recommendations:', err);
      fetchError.value = 'Could not load recommendations. Please click Shuffle to try again.';
    } finally {
      isLoading.value = false;
    }
  };

  let animationFrameId = null;
  const scrollSpeed = 0.85;

  const autoScroll = () => {
    if (trackRef.value && !isHovered.value && !isInteracting.value) {
      trackRef.value.scrollLeft += scrollSpeed;
      const halfway = trackRef.value.scrollWidth / 2;
      if (trackRef.value.scrollLeft >= halfway) {
        trackRef.value.scrollLeft -= halfway;
      }
    }
    animationFrameId = requestAnimationFrame(autoScroll);
  };

  const onTrackScroll = () => {
    if (!trackRef.value) return;
    const halfway = trackRef.value.scrollWidth / 2;
    if (halfway > 0 && trackRef.value.scrollLeft >= halfway) {
      trackRef.value.scrollLeft -= halfway;
    } else if (trackRef.value.scrollLeft <= 0) {
      trackRef.value.scrollLeft += halfway;
    }
  };

  const scrollManual = (delta) => {
    if (!trackRef.value) return;
    isInteracting.value = true;
    trackRef.value.scrollBy({ left: delta, behavior: 'smooth' });
    setTimeout(() => {
      isInteracting.value = false;
    }, 600);
  };

  const handleMouseEnter = () => { isHovered.value = true; };
  const handleMouseLeave = () => { isHovered.value = false; };
  const handleTouchStart = () => { isInteracting.value = true; };
  const handleTouchEnd = () => {
    setTimeout(() => { isInteracting.value = false; }, 1000);
  };

  onMounted(() => {
    fetchRandomRecommendations();
    animationFrameId = requestAnimationFrame(autoScroll);
  });

  onBeforeUnmount(() => {
    if (animationFrameId) {
      cancelAnimationFrame(animationFrameId);
    }
  });

  return {
    recommendations,
    isLoading,
    fetchError,
    trackRef,
    displayShows,
    fetchRandomRecommendations,
    scrollManual,
    onTrackScroll,
    handleMouseEnter,
    handleMouseLeave,
    handleTouchStart,
    handleTouchEnd
  };
}
