import { defineStore } from 'pinia'
import { listPlaces, getPlace } from '../lib/api'

export const usePlacesStore = defineStore('places', {
  state: () => ({
    items: [],
    pagination: null,
    current: null,
    loading: false,
    error: null,
  }),
  actions: {
    async fetchList(locale, params) {
      this.loading = true; this.error = null
      try {
        const { data } = await listPlaces(locale, params)
        this.items = data.data
        this.pagination = { ...data.meta, links: data.links }
      } catch (e) {
        this.error = e?.response?.data || e.message || 'Request failed'
      } finally {
        this.loading = false
      }
    },
    async fetchOne(locale, slug) {
      this.loading = true; this.error = null
      try {
        const { data } = await getPlace(locale, slug)
        this.current = data.data
      } catch (e) {
        this.error = e?.response?.data || e.message || 'Request failed'
      } finally {
        this.loading = false
      }
    }
  }
})
