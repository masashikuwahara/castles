import axios from 'axios'
export const api = axios.create({ baseURL: '/api' })

export const listPlaces = (locale, params = {}) =>
  api.get(`/${locale}/places`, { params })

export const getPlace = (locale, slug) =>
  api.get(`/${locale}/places/${encodeURIComponent(slug)}`)

export const listTags = (locale) =>
  api.get(`/${locale}/tags`)

export const getQuiz = (locale, params = {}) =>
  api.get(`/${locale}/quiz`, { params })

export const listCulturalSites = (locale, params={}) =>
  api.get(`/${locale}/cultural-sites`, { params })

export const getCulturalSite = (locale, slug) =>
  api.get(`/${locale}/cultural-sites/${slug}`)

export const listCulturalTags = (locale) =>
  axios.get(`/api/${locale}/cultural/tags`);

export const listPrefectures = (locale) =>
  api.get(`/${locale}/prefectures`)

export function setAuthToken(token) {
  if (token) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    localStorage.setItem('auth_token', token);
  } else {
    delete api.defaults.headers.common['Authorization'];
    localStorage.removeItem('auth_token');
  }
}

// 起動時に復元
const saved = localStorage.getItem('auth_token');
if (saved) setAuthToken(saved);

// 使い回し用関数
export const login  = (email, password) => api.post('/login', { email, password });
export const me     = () => api.get('/me');
export const logout = () => api.post('/logout');