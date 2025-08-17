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
    <div v-else-if="store.error" class="p-3 bg-red-50 text-red-700 rounded mb-4">
      {{ store.error.message || store.error }}
    </div>
    <div v-else class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
      <PlaceCard v-for="p in store.items" :key="p.id" :place="p" />
    </div>

    <!-- ページャ -->
     <Pagination
     v-if="store.pagination && store.pagination.last_page > 1"
     :pagination="store.pagination"
     :radius="2"
     @change="onPageChange"
     />
     <p v-if="store.pagination" class="text-sm text-gray-500 mb-3">
      {{ store.pagination.from }}–{{ store.pagination.to }} / {{ store.pagination.total }}
     </p>
  </div>
</template>

<script setup>
import { ref, watch, watchEffect, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { usePlacesStore } from '../stores/places'
import PlaceCard from '../components/PlaceCard.vue'
import FilterBar from '../components/FilterBar.vue'
import Pagination from '../components/Pagination.vue'

const store = usePlacesStore()
const route = useRoute()
const router = useRouter()
const { t } = useI18n()

const q = ref(route.query.q || '')

const hasPager = computed(() =>
!!(store.pagination && Array.isArray(store.pagination.links) && store.pagination.links.length)
)

function reload() {
  const params = { ...route.query, q: q.value || undefined, page: undefined }
  router.replace({ query: params })
}

function onPageChange(n) {
  const params = { ...route.query, page: n }
  router.replace({ query: params })
  window.scrollTo({ top: 0, behavior: 'smooth' })
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
