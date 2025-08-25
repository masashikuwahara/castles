<template>
  <div v-if="store.loading">Loading...</div>
  <div v-else-if="!store.current">Not found.</div>
  <div v-else class="grid md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
      <img v-if="cover" :src="cover" class="w-full rounded-xl mb-4" />
      <h2 class="text-2xl font-bold mb-2">{{ p.name }}</h2>
      <p class="mb-4 text-gray-700">{{ p.summary }}</p>

      <div class="grid sm:grid-cols-2 gap-3 text-sm">
        <div><span class="font-semibold">城郭構造:</span> {{ p.castle_structure }}</div>
        <div><span class="font-semibold">天守構造:</span> {{ p.tenshu_structure }}</div>
        <div><span class="font-semibold">築城主:</span> {{ p.founder }}</div>
        <div><span class="font-semibold">築城年:</span> {{ p.built_year }}</div>
        <div><span class="font-semibold">廃城年:</span> {{ p.abolished_year }}</div>
        <div><span class="font-semibold">主な改修者:</span> {{ p.main_renovators }}</div>
        <div><span class="font-semibold">主な城主:</span> {{ p.main_lords }}</div>
        <div><span class="font-semibold">指定文化財:</span> {{ p.designated_heritage }}</div>
        <div><span class="font-semibold">遺構:</span> {{ p.remains }}</div>
        <div><span class="font-semibold">おすすめ度:</span> {{ p.rating }} / 5</div>
      </div>

      <div class="mt-6 flex flex-wrap gap-2">
        <router-link v-for="t in p.tags || []" :key="t.slug"
          class="text-xs px-2 py-1 bg-gray-100 rounded-full hover:bg-gray-200"
          :to="rl({ name:'tag' })">
          #{{ t.name }}
        </router-link>
      </div>
    </div>

    <aside>
      <div class="bg-white rounded-xl shadow p-4">
        <h3 class="font-semibold mb-2">Location</h3>
        <p class="text-sm text-gray-600">
          {{ p.prefecture?.name_ja }} {{ p.city }}
        </p>
        <div class="mt-3">
          <iframe
            v-if="p.lat && p.lng"
            class="w-full h-64 rounded"
            :src="`https://www.google.com/maps?q=${p.lat},${p.lng}&z=14&output=embed`"
            loading="lazy" />
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { usePlacesStore } from '../stores/places'

const store = usePlacesStore()
const route = useRoute()

const p = computed(() => store.current || {})
const cover = computed(() => p.value?.cover_photo?.path || null)

function load() {
  store.fetchOne(route.params.locale, route.params.slug)
}

onMounted(load)
watch(() => [route.params.locale, route.params.slug], load)
</script>
