/**
 * Centralized formatting and UI helper utilities for eFlix
 */

export const FALLBACK_POSTER =
  'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22300%22%20height%3D%22450%22%20viewBox%3D%220%200%20300%20450%22%3E%3Crect%20fill%3D%22%23262626%22%20width%3D%22300%22%20height%3D%22450%22%2F%3E%3Ctext%20fill%3D%22%23666%22%20font-family%3D%22sans-serif%22%20font-size%3D%2218%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3ENo%20Poster%3C%2Ftext%3E%3C%2Fsvg%3E';

export const FALLBACK_AVATAR =
  'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22200%22%20height%3D%22200%22%20viewBox%3D%220%200%20200%20200%22%3E%3Crect%20fill%3D%22%23222%22%20width%3D%22200%22%20height%3D%22200%22%2F%3E%3Ccircle%20cx%3D%22100%22%20cy%3D%2275%22%20r%3D%2240%22%20fill%3D%22%23444%22%2F%3E%3Cpath%20d%3D%22M30%20185%20c0-45%2035-70%2070-70%20s70%2025%2070%2070z%22%20fill%3D%22%23444%22%2F%3E%3C%2Fsvg%3E';

/**
 * Format ISO date string into human-readable date (e.g., "Oct 12, 2021")
 */
export function formatDate(dateString) {
  if (!dateString) return 'N/A';
  try {
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleDateString('en-GB', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  } catch {
    return dateString;
  }
}

/**
 * Extract 4-digit release year from date string
 */
export function formatYear(dateString) {
  if (!dateString) return 'N/A';
  return dateString.substring(0, 4);
}

/**
 * Translate era code to friendly label
 */
export function formatEra(era) {
  if (era === '2020s') return '2020 – Present';
  if (era === '2010s') return '2010 – 2019';
  if (era === '2000s') return '2000 – 2009';
  if (era === 'classics') return 'Classics (<2000)';
  return era;
}

/**
 * Strips HTML tags and truncates to given max length
 */
export function stripHtml(html, maxLength = 130) {
  if (!html) return 'Explore this title to view full series details, episode summaries, seasons breakdown, and cast lists.';
  const doc = new DOMParser().parseFromString(html, 'text/html');
  const text = (doc.body.textContent || '').trim();
  if (maxLength && text.length > maxLength) {
    return text.slice(0, maxLength - 3) + '...';
  }
  return text;
}

/**
 * Remove external anchor tags while preserving inner text
 */
export function stripExternalLinks(html) {
  if (!html) return '';
  return html.replace(/<\/?a[^>]*>/gi, '');
}

/**
 * Fisher-Yates array shuffle
 */
export function shuffleArray(array) {
  const arr = [...array];
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
  return arr;
}
