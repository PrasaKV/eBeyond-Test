/**
 * TVmaze API Client Service
 * Centralizes all network interactions with the TVmaze REST API.
 */

const BASE_URL = 'https://api.tvmaze.com';

/**
 * Handle API responses with status verification
 */
async function handleResponse(response) {
  if (!response.ok) {
    throw new Error(`TVmaze API error: ${response.status} ${response.statusText}`);
  }
  return response.json();
}

export const tvmazeApi = {
  /**
   * Fetch paginated list of shows (250 items per page)
   * @param {number} page
   */
  async getShows(page = 0) {
    const res = await fetch(`${BASE_URL}/shows?page=${page}`);
    const data = await handleResponse(res);
    return data.filter((item) => item.image && item.summary);
  },

  /**
   * Search shows by title or keyword
   * @param {string} query
   */
  async searchShows(query) {
    const trimmed = query?.trim();
    if (!trimmed) return [];
    const res = await fetch(`${BASE_URL}/search/shows?q=${encodeURIComponent(trimmed)}`);
    const data = await handleResponse(res);
    return data.map((item) => item.show).filter((s) => s && s.image);
  },

  /**
   * Fetch single show by ID with optional cast and seasons embedding
   * @param {number|string} showId
   * @param {boolean} embedCast
   * @param {boolean} embedSeasons
   */
  async getShowById(showId, { embedCast = true, embedSeasons = true, embedEpisodes = true } = {}) {
    const embeds = [];
    if (embedCast) embeds.push('embed[]=cast');
    if (embedSeasons) embeds.push('embed[]=seasons');
    if (embedEpisodes) embeds.push('embed[]=episodes');
    const query = embeds.length ? `?${embeds.join('&')}` : '';

    const res = await fetch(`${BASE_URL}/shows/${showId}${query}`);
    return handleResponse(res);
  },

  /**
   * Fetch cast members for a show
   * @param {number|string} showId
   */
  async getShowCast(showId) {
    const res = await fetch(`${BASE_URL}/shows/${showId}/cast`);
    return handleResponse(res);
  },

  /**
   * Fetch seasons list for a show
   * @param {number|string} showId
   */
  async getShowSeasons(showId) {
    const res = await fetch(`${BASE_URL}/shows/${showId}/seasons`);
    return handleResponse(res);
  },

  /**
   * Fetch a random page of shows for recommendations
   * @param {number} maxPage
   */
  async getRandomShows(maxPage = 20) {
    const randomPage = Math.floor(Math.random() * maxPage);
    return this.getShows(randomPage);
  }
};
