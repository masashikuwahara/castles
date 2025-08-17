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
