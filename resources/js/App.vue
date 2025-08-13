<template>
  <div>
<header class="bg-white shadow">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
    <h1 class="font-bold">Castles & Cultural Properties</h1>

    <nav class="flex items-center gap-4 text-sm">
      <router-link :to="{ path: `/${$route.params.locale}/castles/top100` }">100名城</router-link>
      <router-link :to="{ path: `/${$route.params.locale}/castles/top100-continued` }">続100名城</router-link>
      <router-link :to="{ path: `/${$route.params.locale}/castles/others` }">それ以外の城</router-link>
      <router-link :to="{ path: `/${$route.params.locale}/cultural-properties` }">文化財</router-link>
      <router-link :to="{ name: 'tags-index', params: { locale: $route.params.locale } }">タグ</router-link>

      <div class="flex items-center gap-2 ml-4">
        <button class="px-3 py-1 rounded border" @click="switchLocale('ja')">日本語</button>
        <button class="px-3 py-1 rounded border" @click="switchLocale('en')">EN</button>
      </div>
    </nav>
  </div>
</header>

    <main class="max-w-7xl mx-auto px-4 py-6">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
const { locale } = useI18n()
const route = useRoute()
const router = useRouter()

function switchLocale(next) {
  const params = { ...route.params, locale: next }
  router.push({ name: route.name, params, query: route.query })
  locale.value = next
}
</script>
