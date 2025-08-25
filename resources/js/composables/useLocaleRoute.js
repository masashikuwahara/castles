import { useRoute } from 'vue-router'
import { computed } from 'vue'

export function useLocaleRoute() {
  const route = useRoute()

  // locale の安全なフォールバック
  const loc = computed(() => {
    const p = route.params?.locale
    if (typeof p === 'string' && p.length) return p
    // ここで最終フォールバック
    return (navigator.language && navigator.language.startsWith('en')) ? 'en' : 'ja'
  })

  /**
   * locale を必ず注入した to を返す
   * - name 指定の { name, params, query } 形式を推奨
   * - 文字列 or path 指定もサポート（頭に /:locale を自動付与）
   */
  const rl = (to) => {
    if (typeof to === 'string') {
      const path = to.startsWith('/') ? to : `/${to}`
      return `/${loc.value}${path}`
    }
    if (to && typeof to === 'object') {
      const params = { ...(to.params || {}) }
      if (typeof params.locale !== 'string' || !params.locale) {
        params.locale = loc.value
      }
      // path 指定にも対応（必要なら /:locale を前置）
      if (to.path && !to.name) {
        const p = to.path.startsWith('/') ? to.path : `/${to.path}`
        return { ...to, path: `/${params.locale}${p}`, params }
      }
      return { ...to, params }
    }
    return to
  }

  return { rl, loc }
}
