// resources/js/stores/auth.js
import { defineStore } from 'pinia'
import { login as apiLogin, me as apiMe, logout as apiLogout, setAuthToken } from '../lib/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    bootstrapDone: false,
    loading: false,
    error: null,
  }),
  actions: {
    async bootstrap() {
      // ← localStorage にトークンが無ければ何もしない
      const token = localStorage.getItem('auth_token')
      if (!token) {
        this.bootstrapDone = true
        return
      }
      setAuthToken(token)

      try {
        this.loading = true
        const { data } = await apiMe()   // ここは 200 想定
        this.user = data
      } catch (_) {
        // トークンが不正/期限切れなら破棄
        this.user = null
        setAuthToken(null)
      } finally {
        this.loading = false
        this.bootstrapDone = true
      }
    },

    async login(email, password) {
      this.loading = true; this.error = null
      try {
        const { data } = await apiLogin(email, password) // => { token: '...' }
        setAuthToken(data.token)
        const meRes = await apiMe()
        this.user = meRes.data
        return true
      } catch (e) {
        this.error = e?.response?.data || e.message
        this.user = null
        setAuthToken(null)
        return false
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try { await apiLogout() } catch {}
      this.user = null
      setAuthToken(null)
    },
  },
})
