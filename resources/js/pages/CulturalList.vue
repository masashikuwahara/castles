<template>
  <div>
    <div class="mb-4 flex items-center gap-2">
      <input v-model="q" placeholder="Search"
             class="border rounded px-3 py-2 w-full md:w-80" @keyup.enter="reload" />
      <button @click="reload" class="px-3 py-2 border rounded">Search</button>
    </div>

    <!-- フィルタバー -->
    <FilterBar mode="cultural" />

    <div v-if="store.loading">Loading...</div>
    <div v-else-if="store.error" class="p-3 bg-red-50 text-red-700 rounded mb-4">
      {{ store.error.message || store.error }}
    </div>
    <div v-else class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
      <PlaceCard v-for="p in store.items" :key="p.id" :place="p" />
    </div>

    <!-- <div class="mt-6 flex gap-2" v-if="safeLinks.length">
      <button v-for="(l, idx) in safeLinks" :key="idx" :disabled="!l.url"
              @click="goto(l.url)" class="px-3 py-1 border rounded disabled:opacity-50"
              v-html="l.label" />
    </div> -->

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
import { ref, computed, watchEffect } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCulturalsStore } from '../stores/culturals'
import PlaceCard from '../components/PlaceCard.vue'
import FilterBar from '../components/FilterBar.vue'
import Pagination from '../components/Pagination.vue'

const route = useRoute()
const router = useRouter()
const store = useCulturalsStore()

const q = ref(route.query.q || '')
// const safeLinks = computed(() =>
// Array.isArray(store.pagination?.links)
// ? store.pagination.links.map(l => ({ ...l, url: l.url || null }))
// : []
// )

function reload() {
  const params = { ...route.query, q: q.value || undefined, page: undefined }
  router.replace({ query: params })
  store.fetchList(route.params.locale, params)
}
watchEffect(() => {
  store.fetchList(route.params.locale, route.query)
})
function goto(url) {
  const u = new URL(url)
  const page = u.searchParams.get('page')
  const params = { ...route.query, page }
  router.replace({ query: params })
}

function onPageChange(page) {
  const params = { ...route.query, page }
  router.replace({ query: params })
}
</script>
