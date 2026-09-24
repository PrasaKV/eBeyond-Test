

const BASE_URL = 'https://api.tvmaze.com';

async function handleResponse(response) {
  if (!response.ok) {
    throw new Error(`TVmaze API error: ${response.status} ${response.statusText}`);
  }
  return response.json();
}

export const tvmazeApi = {
  
  async getShows(page = 0) {
    const res = await fetch(`${BASE_URL}/shows?page=${page}`);
    const data = await handleResponse(res);
    return data.filter((item) => item.image && item.summary);
  },

  
  async searchShows(query) {
    const trimmed = query?.trim();
    if (!trimmed) return [];
    const res = await fetch(`${BASE_URL}/search/shows?q=${encodeURIComponent(trimmed)}`);
    const data = await handleResponse(res);
    return data.map((item) => item.show).filter((s) => s && s.image);
  },

  
  async getShowById(showId, { embedCast = true, embedSeasons = true, embedEpisodes = true } = {}) {
    const embeds = [];
    if (embedCast) embeds.push('embed[]=cast');
    if (embedSeasons) embeds.push('embed[]=seasons');
    if (embedEpisodes) embeds.push('embed[]=episodes');
    const query = embeds.length ? `?${embeds.join('&')}` : '';

    const res = await fetch(`${BASE_URL}/shows/${showId}${query}`);
    return handleResponse(res);
  },

  
  async getShowCast(showId) {
    const res = await fetch(`${BASE_URL}/shows/${showId}/cast`);
    return handleResponse(res);
  },

  
  async getShowSeasons(showId) {
    const res = await fetch(`${BASE_URL}/shows/${showId}/seasons`);
    return handleResponse(res);
  },

  
  async getRandomShows(maxPage = 20) {
    const randomPage = Math.floor(Math.random() * maxPage);
    return this.getShows(randomPage);
  }
};
