<template>
  <div>
    <!-- 固定ヘッダ -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <h1 class="font-bold">
          <router-link
            :to="{ name: 'list', params: { locale: $route.params.locale }, query: {} }"
            class="no-underline text-inherit hover:opacity-80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-gray-400 rounded"
            title="最初に戻る"
          >
            Castles & Cultural Properties
          </router-link>
        </h1>

        <!-- デスクトップ: ナビ -->
        <nav class="hidden md:flex items-center gap-2 text-sm">
          <router-link
            :to="{ path: `/${$route.params.locale}/castles/top100` }"
            :class="linkClass('top100')"
            :aria-current="currentKey==='top100' ? 'page' : undefined"
          >100名城</router-link>

          <router-link
            :to="{ path: `/${$route.params.locale}/castles/top100-continued` }"
            :class="linkClass('top100c')"
            :aria-current="currentKey==='top100c' ? 'page' : undefined"
          >続100名城</router-link>

          <router-link
            :to="{ path: `/${$route.params.locale}/castles/others` }"
            :class="linkClass('others')"
            :aria-current="currentKey==='others' ? 'page' : undefined"
          >それ以外の城</router-link>

          <router-link
            :to="{ path: `/${$route.params.locale}/cultural-properties` }"
            :class="linkClass('cp')"
            :aria-current="currentKey==='cp' ? 'page' : undefined"
          >文化財</router-link>

          <router-link
            :to="{ name: 'tags-index', params: { locale: $route.params.locale } }"
            :class="linkClass('tags')"
            :aria-current="currentKey==='tags' ? 'page' : undefined"
          >タグ</router-link>

          <!-- 言語切替 -->
          <div class="flex items-center gap-2 ml-4">
            <button class="px-3 py-1 rounded border" @click="switchLocale('ja')">日本語</button>
            <button class="px-3 py-1 rounded border" @click="switchLocale('en')">EN</button>
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
            :to="{ path: `/${$route.params.locale}/castles/top100` }"
            :class="linkClass('top100', true)"
            @click="open=false"
          >100名城</router-link>
          <router-link
            :to="{ path: `/${$route.params.locale}/castles/top100-continued` }"
            :class="linkClass('top100c', true)"
            @click="open=false"
          >続100名城</router-link>
          <router-link
            :to="{ path: `/${$route.params.locale}/castles/others` }"
            :class="linkClass('others', true)"
            @click="open=false"
          >それ以外の城</router-link>
          <router-link
            :to="{ path: `/${$route.params.locale}/cultural-properties` }"
            :class="linkClass('cp', true)"
            @click="open=false"
          >文化財</router-link>
          <router-link
            :to="{ name: 'tags-index', params: { locale: $route.params.locale } }"
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
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'

const { locale } = useI18n()
const route = useRoute()
const router = useRouter()
const open = ref(false)

// 現在地に応じたキー判定（list 画面のクエリを見て分類）
const currentKey = computed(() => {
  if (route.name === 'tags-index') return 'tags'
  if (route.name === 'list') {
    const q = route.query
    if (q.type === 'castle' && (q.top100 === '1' || q.top100 === 1)) return 'top100'
    if (q.type === 'castle' && (q.top100c === '1' || q.top100c === 1)) return 'top100c'
    if (q.others === '1' || q.others === 1) return 'others'
    if (q.type === 'cultural_property') return 'cp'
  }
  return ''
})

// 見た目（アクティブ状態で色分け）。mobile=true で幅100%に
function linkClass(key, mobile = false) {
  const base = mobile ? 'block w-full text-left px-3 py-2 rounded-md' : 'px-3 py-2 rounded-md'
  const on  = 'bg-gray-900 text-white'
  const off = 'text-gray-700 hover:bg-gray-100'
  return `${base} ${currentKey.value === key ? on : off}`
}

function switchLocale(next) {
  const params = { ...route.params, locale: next }
  router.push({ name: route.name || 'list', params, query: route.query })
  locale.value = next
  open.value = false
}
</script>
