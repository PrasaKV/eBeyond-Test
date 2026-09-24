import { ref } from 'vue';

const isDetailsOpen = ref(false);
const activeShowId = ref(null);
const initialShowData = ref(null);

export function useShowDetails() {
  const openShowDetails = (showIdOrObject) => {
    if (!showIdOrObject) return;

    if (typeof showIdOrObject === 'object') {
      const showId = showIdOrObject.id || showIdOrObject.showId;
      activeShowId.value = showId;
      initialShowData.value = showIdOrObject;
    } else {
      activeShowId.value = showIdOrObject;
      initialShowData.value = null;
    }

    isDetailsOpen.value = true;
    document.body.style.overflow = 'hidden';
  };

  const closeShowDetails = () => {
    isDetailsOpen.value = false;
    activeShowId.value = null;
    initialShowData.value = null;
    document.body.style.overflow = '';
  };

  return {
    isDetailsOpen,
    activeShowId,
    initialShowData,
    openShowDetails,
    closeShowDetails
  };
}
