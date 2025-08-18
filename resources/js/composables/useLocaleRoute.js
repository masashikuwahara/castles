import { useRoute } from 'vue-router'

export function useLocaleRoute() {
  const route = useRoute()
  const rl = (to = {}) => {
    const params = { ...(to.params || {}) }
    if (!('locale' in params)) params.locale = route.params.locale || 'ja'
    return { ...to, params }
  }
  return { rl }
}
