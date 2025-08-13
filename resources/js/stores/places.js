import { defineStore } from 'pinia'
import { listPlaces, getPlace } from '../lib/api'

export const usePlacesStore = defineStore('places', {
  state: () => ({
    items: [],
    pagination: null,
    current: null,
    loading: false,
    error: null,
    _req: { list: 0, one: 0 },
  }),
  actions: {
    async fetchList(locale, params) {
      this.loading = true; this.error = null
      const rid = ++this._req.list
      try {
        const { data } = await listPlaces(locale, params)
        if (rid !== this._req.list) return
        this.items = data.data
        this.pagination = { ...data.meta, links: data.meta?.links || [] }
      } catch (e) {
        this.error = e?.response?.data || e.message || 'Request failed'
      } finally {
        if (rid === this._req.list) this.loading = false
      }
    },
    async fetchOne(locale, slug) {
      this.loading = true; this.error = null
      const rid = ++this._req.one
      try {
        const { data } = await getPlace(locale, slug)
        if (rid !== this._req.one) return
        this.current = data.data
      } catch (e) {
        this.error = e?.response?.data || e.message || 'Request failed'
      } finally {
        if (rid === this._req.one) this.loading = false
      }
    }
  }
})
