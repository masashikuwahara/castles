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

// 使い方

// <script setup>
// +import { useLocaleRoute } from '../composables/useLocaleRoute'
// +const { rl } = useLocaleRoute()
// </script>

// <!-- 例：クイズへのリンク -->
// - <router-link :to="{ name:'quiz', params:{ locale: $route.params.locale } }">クイズ</router-link>
// + <router-link :to="rl({ name:'quiz' })">クイズ</router-link>