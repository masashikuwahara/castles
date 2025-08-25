<template>
  <div>
    <!-- 固定ヘッダ -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <h1 class="font-bold">
          <router-link
            :to="rl({ name:'list' })"
            class="no-underline text-inherit hover:opacity-80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-400 rounded"
            title="最初に戻る"
          >
            Castles & Cultural Properties
          </router-link>
        </h1>

        <!-- デスクトップ: ナビ -->
        <nav class="hidden md:flex items-center gap-2 text-sm">
          <router-link
            :to="rl({ name:'castles-top100' })"
            :class="linkClass('top100')"
            :aria-current="currentKey==='top100' ? 'page' : undefined"
          >100名城</router-link>

          <router-link
            :to="rl({ name:'castles-top100c' })"
            :class="linkClass('top100c')"
            :aria-current="currentKey==='top100c' ? 'page' : undefined"
          >続100名城</router-link>

          <router-link
            :to="rl({ name:'castles-others' })"
            :class="linkClass('others')"
            :aria-current="currentKey==='others' ? 'page' : undefined"
          >それ以外の城</router-link>

          <router-link
            :to="rl({ name:'cultural-list' })"
            :class="linkClass('cultural')"
            :aria-current="currentKey==='cultural' ? 'page' : undefined"
          >文化財</router-link>

          <router-link
            :to="rl({ name:'tags-index' })"
            :class="linkClass('tags')"
            :aria-current="currentKey==='tags' ? 'page' : undefined"
          >タグ</router-link>

          <router-link
            :to="rl({ name:'quiz' })"
            :class="linkClass('quiz')"
            :aria-current="currentKey==='quiz' ? 'page' : undefined"
          >クイズ</router-link>

          <!-- 言語切替 -->
           <div class="flex items-center gap-2 ml-4">
            <button
              class="px-3 py-1 rounded border"
              :class="locale === 'ja' ? 'bg-gray-200 font-semibold' : ''"
              @click="switchLocale('ja')">日本語</button>
            <button
              class="px-3 py-1 rounded border"
              :class="locale === 'en' ? 'bg-gray-200 font-semibold' : ''"
              @click="switchLocale('en')">EN</button>
          </div>
        </nav>

        <!-- モバイル: ハンバーガー -->
        <button class="md:hidden px-3 py-2 border rounded" @click="open = !open" aria-label="menu">
          {{ open ? 'Close' : 'Menu' }}
        </button>
      </div>

      <!-- モバイルメニュー -->
      <div v-if="open" class="md:hidden border-t">
        <nav class="max-w-7xl mx-auto px-4 py-2 grid gap-1 text-sm">
          <router-link
            :to="rl({ name:'castles-top100' })"
            :class="linkClass('top100', true)"
            @click="open=false"
          >100名城</router-link>
          <router-link
            :to="rl({ name:'top100-continued' })"
            :class="linkClass('top100c', true)"
            @click="open=false"
          >続100名城</router-link>
          <router-link
            :to="rl({ name:'others' })"
            :class="linkClass('others', true)"
            @click="open=false"
          >それ以外の城</router-link>
          <router-link
            :to="rl({ name:'cultural-list' })"
            :class="linkClass('cultural')"
          >文化財</router-link>
          <router-link
            :to="rl({ name:'tags-index' })"
            :class="linkClass('tags', true)"
            @click="open=false"
          >タグ</router-link>

          <div class="flex items-center gap-2 pt-2">
            <button class="px-3 py-1 rounded border" @click="switchLocale('ja')">日本語</button>
            <button class="px-3 py-1 rounded border" @click="switchLocale('en')">EN</button>
          </div>
        </nav>
      </div>
    </header>

    <!-- コンテンツ -->
    <main class="max-w-7xl mx-auto px-4 py-6">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useLocaleRoute } from './composables/useLocaleRoute'

const route = useRoute()
const router = useRouter()
const { locale } = useI18n()

const { rl } = useLocaleRoute()

const currentKey = computed(() => {
  // ルート名でまず判定
  switch (route.name) {
    case 'castles-top100':   return 'top100'
    case 'castles-top100c':  return 'top100c'
    case 'castles-others':   return 'others'
    case 'cultural-list':
    case 'cultural-detail':  return 'cultural'
    case 'tags-index':
    case 'tag':              return 'tags'
    case 'quiz':             return 'quiz'
  }
  // 一覧/詳細のときはクエリでも判定（直リンク等のため）
  if (route.name === 'list' || route.name === 'detail') {
    const q = route.query
    if (q.type === 'cultural') return 'cultural'
    if (q.top100 === '1' || q.top100 == 1) return 'top100'
    if (q.top100c === '1' || q.top100c == 1) return 'top100c'
    if (q.others === '1' || q.others == 1) return 'others'
  }
  return ''
})

function linkClass(key) {
  const base = 'px-3 py-2 rounded hover:bg-gray-50'
  return currentKey.value === key ? `${base} bg-gray-200 font-semibold` : base
}

function switchLocale(next) {
  if (next === route.params.locale) {
    locale.value = next
    return
  }
  const target = {
    name: route.name || 'list',
    params: { ...route.params, locale: next },
    query: route.query
  }
  locale.value = next
  router.replace(target)
}
</script>
