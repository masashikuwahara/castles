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

const i18n = createI18n({
  legacy: false,
  locale: 'ja',
  fallbackLocale: 'en',
  messages,
})

createApp(App)
  .use(createPinia())
  .use(router)
  .use(i18n)
  .mount('#app')
