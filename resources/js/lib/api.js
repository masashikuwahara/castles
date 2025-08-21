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
