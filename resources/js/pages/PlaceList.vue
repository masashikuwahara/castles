<template>
  <div>
    <!-- 検索 -->
    <div class="mb-4 flex items-center gap-2">
      <input v-model="q" :placeholder="t('search_placeholder')"
             class="border rounded px-3 py-2 w-full md:w-80" @keyup.enter="reload" />
      <button @click="reload" class="px-3 py-2 border rounded">Search</button>
    </div>

    <!-- フィルタバー -->
    <FilterBar />

    <!-- 一覧 -->
    <div v-if="store.loading">Loading...</div>
    <div v-else class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
      <PlaceCard v-for="p in store.items" :key="p.id" :place="p" />
    </div>

    <!-- 簡易ページャ（APIのlinks/metaに合わせて必要なら） -->
    <div class="mt-6 flex gap-2" v-if="store.pagination?.links">
      <button
        v-for="(l, idx) in store.pagination.links"
        :key="idx"
        :disabled="!l.url"
        @click="goto(l.url)"
        class="px-3 py-1 border rounded disabled:opacity-50"
        v-html="l.label" />
    </div>
  </div>
</template>

<script setup>
import { ref, watch, watchEffect } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { usePlacesStore } from '../stores/places'
import PlaceCard from '../components/PlaceCard.vue'
import FilterBar from '../components/FilterBar.vue'

const store = usePlacesStore()
const route = useRoute()
const router = useRouter()
const { t } = useI18n()

const q = ref(route.query.q || '')

function reload() {
  const params = { ...route.query, q: q.value || undefined, page: undefined }
  router.replace({ query: params })
  store.fetchList(route.params.locale, params)
}

watchEffect(() => {
  store.fetchList(route.params.locale, route.query)
})
watch(() => route.query.q, (v) => { q.value = v || '' })

function goto(url) {
  // APIのページネーションURLをクエリに反映（page=番号 だけ拾う）
  const u = new URL(url)
  const page = u.searchParams.get('page')
  const params = { ...route.query, page }
  router.replace({ query: params })
}
</script>
