import './bootstrap';
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createI18n } from 'vue-i18n'
import router from './router'
import App from './App.vue'

const messages = {
  ja: {
    menu: { list: '一覧', detail: '詳細', lang: '言語' },
    search_placeholder: 'キーワードで検索',
  },
  en: {
    menu: { list: 'List', detail: 'Detail', lang: 'Language' },
    search_placeholder: 'Search by keyword',
  }
}

const pinia = createPinia()
// ★超簡易の永続化：各ストアの state を localStorage に保存/復元
pinia.use(({ store }) => {
  const key = `pinia:${store.$id}`
  const saved = localStorage.getItem(key)
  if (saved) store.$patch(JSON.parse(saved))
  store.$subscribe((_, state) => {
    localStorage.setItem(key, JSON.stringify(state))
  })
})

const i18n = createI18n({
  legacy: false,
  locale: 'ja',
  fallbackLocale: 'en',
  messages,
})

const app = createApp(App)
  app.use(createPinia())
  app.use(router)
  app.use(i18n)
  app.mount('#app')
