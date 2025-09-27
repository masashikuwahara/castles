<template>
  <footer class="mt-10 border-t bg-white/60 dark:bg-gray-900/60 backdrop-blur supports-[backdrop-filter]:bg-white/40">
    <div class="bg-white/50 dark:bg-gray-900/50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs sm:text-sm flex flex-col sm:flex-row items-center justify-between text-gray-500 dark:text-gray-400">
        <div>© {{ year }} {{ siteName }}</div>v.{{ hist }}
        <button @click="scrollTop" class="mt-2 sm:mt-0 underline">{{ t('footer.back_to_top') }}</button>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed} from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import { useLocaleRoute } from '@/composables/useLocaleRoute'

const { rl } = useLocaleRoute()
const route = useRoute()
const { t, locale } = useI18n()

const hist = '1.0.0' 

const siteName = 'Daytripper'
const social = {
  twitter: '',
  github: '',
  rss: ''
}
const version = import.meta.env.VITE_APP_VERSION || ''
// ————————————————

const year = new Date().getFullYear()
const isJA = computed(() => (route.params.locale ?? locale.value) === 'ja')

function langLink(next) {
  return {
    name: route.name || 'list',
    params: { ...route.params, locale: next },
    query: route.query
  }
}

function scrollTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>
