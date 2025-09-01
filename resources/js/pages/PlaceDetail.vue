<template>
  <div>
    <h2 class="text-xl font-bold mb-3">{{ place?.name }}</h2>

    <!-- ヒーロー画像 -->
    <div v-if="img" class="mb-4 overflow-hidden rounded-xl border bg-white">
      <picture>
        <source v-if="img.srcset?.webp" :srcset="img.srcset.webp" type="image/webp" />
        <img :src="img.src" :sizes="img.sizes || '100vw'"
             :width="img.width || null" :height="img.height || null"
             loading="lazy" decoding="async" alt="" class="w-full object-cover" />
      </picture>
      <p v-if="img.caption" class="mt-2 px-3 pb-2 text-sm text-gray-600">{{ img.caption }}</p>
    </div>

    <div v-if="loading">Loading...</div>
    <div v-else-if="error" class="p-3 bg-red-50 text-red-700 rounded">{{ error }}</div>
    <div v-else-if="!place" class="text-gray-500">見つかりませんでした。</div>

    <div v-else class="grid lg:grid-cols-3 gap-6">
      <!-- 概要 + 基本情報 -->
      <section class="lg:col-span-2">
        <h3 class="font-semibold mb-2">概要</h3>
        <p class="text-gray-800 whitespace-pre-line">{{ place.summary }}</p>

        <div class="mt-6 grid sm:grid-cols-2 gap-4">
          <div>
            <h4 class="font-semibold text-sm text-gray-600">所在地</h4>
            <p class="text-gray-800">{{ place.prefecture?.name_ja }} {{ place.city }}</p>
          </div>
          <div v-if="place.built_year">
            <h4 class="font-semibold text-sm text-gray-600">築城年</h4>
            <p class="text-gray-800">{{ place.built_year }}</p>
          </div>
          <div v-if="place.abolished_year">
            <h4 class="font-semibold text-sm text-gray-600">廃城年</h4>
            <p class="text-gray-800">{{ place.abolished_year }}</p>
          </div>
          <div v-if="place.castle_structure">
            <h4 class="font-semibold text-sm text-gray-600">城郭構造</h4>
            <p class="text-gray-800">{{ place.castle_structure }}</p>
          </div>
          <div v-if="place.tenshu_structure">
            <h4 class="font-semibold text-sm text-gray-600">天守構造</h4>
            <p class="text-gray-800">{{ place.tenshu_structure }}</p>
          </div>
          <div v-if="place.founder">
            <h4 class="font-semibold text-sm text-gray-600">築城主</h4>
            <p class="text-gray-800">{{ place.founder }}</p>
          </div>
          <div v-if="place.main_renovators">
            <h4 class="font-semibold text-sm text-gray-600">主な改修者</h4>
            <p class="text-gray-800">{{ place.main_renovators }}</p>
          </div>
          <div v-if="place.main_lords">
            <h4 class="font-semibold text-sm text-gray-600">主な城主</h4>
            <p class="text-gray-800">{{ place.main_lords }}</p>
          </div>
          <div v-if="place.designated_heritage">
            <h4 class="font-semibold text-sm text-gray-600">指定文化財</h4>
            <p class="text-gray-800">{{ place.designated_heritage }}</p>
          </div>
          <div v-if="place.remains">
            <h4 class="font-semibold text-sm text-gray-600">遺構</h4>
            <p class="text-gray-800">{{ place.remains }}</p>
          </div>
          <div v-if="place.rating">
            <h4 class="font-semibold text-sm text-gray-600">おすすめ度</h4>
            <p class="text-gray-800">★{{ place.rating }}</p>
          </div>
        </div>

        <!-- タグ -->
        <div v-if="(place.tags?.length)" class="mt-6">
          <h4 class="font-semibold text-sm text-gray-600 mb-2">タグ</h4>
          <div class="flex flex-wrap gap-2">
            <router-link
              v-for="t in place.tags" :key="t.slug"
              class="text-xs px-2 py-0.5 bg-gray-100 rounded-full hover:bg-gray-200"
              :to="rl({ name:'list', query:{ tags: t.slug } })">
              #{{ t.name }}
            </router-link>
          </div>
        </div>
      </section>

      <!-- 写真ギャラリー -->
      <section class="lg:col-span-3 mt-8" v-if="gallery.length">
        <h3 class="font-semibold mb-2">Photos</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
          <figure
            v-for="(ph, i) in gallery"
            :key="i"
            class="rounded-lg overflow-hidden border bg-white cursor-zoom-in"
            @click="openViewer(img ? i + 1 : i)"
            role="button"
            :aria-label="ph.caption || 'open photo'">
            <picture>
              <source v-if="ph.srcset?.webp" :srcset="ph.srcset.webp" type="image/webp" />
              <img :src="ph.src" loading="lazy" decoding="async" alt="" class="w-full h-40 object-cover" />
            </picture>
            <figcaption v-if="ph.caption" class="px-2 py-1 text-xs text-gray-600">{{ ph.caption }}</figcaption>
          </figure>
        </div>
      </section>

      <!-- ライトボックス -->
      <div
        v-if="viewerOpen"
        class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="closeViewer"
      >
        <button class="absolute top-4 right-4 text-white text-2xl" @click="closeViewer" aria-label="Close">✕</button>
        <button class="absolute left-4 top-1/2 -translate-y-1/2 text-white text-3xl" @click="prev" aria-label="Prev">‹</button>
        <button class="absolute right-4 top-1/2 -translate-y-1/2 text-white text-3xl" @click="next" aria-label="Next">›</button>

        <div class="max-w-5xl max-h-[80vh]">
          <img
            :src="bigSrc"
            class="max-w-full max-h-[80vh] object-contain"
            decoding="async"
            alt=""
            @error="(e)=>{ const t=e.target; if(currentItem?.original && t.src!==currentItem.original){ t.src=currentItem.original } }"
          />
          <div v-if="currentItem?.caption" class="mt-2 text-center text-sm text-white/90">
            {{ currentItem.caption }}
          </div>
        </div>
      </div>

      <!-- サイド：地図など -->
      <aside>
        <div v-if="place.lat && place.lng" class="rounded-xl overflow-hidden border">
          <iframe
            :src="gmapsEmbed(place.lat, place.lng)"
            class="w-full h-64" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
        <div class="mt-4">
          <router-link :to="rl({ name:'list' })" class="underline">一覧に戻る</router-link>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watchEffect, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { usePlacesStore } from '../stores/places'
import { useLocaleRoute } from '../composables/useLocaleRoute'

const route = useRoute()
const { locale } = useI18n()
const { rl, loc } = useLocaleRoute()
const store = usePlacesStore()

const loading = ref(true)
const error = ref('')

const place = computed(() => store.current)

// カバー画像（言語別キャプション）
const img = computed(() => {
  const c = place.value?.cover_photo
  if (!c) return null
  const cap = locale.value === 'ja' ? c.caption_ja : c.caption_en
  return { ...c, caption: cap }
})

// Google Map
function gmapsEmbed(lat, lng) {
  return `https://www.google.com/maps?q=${lat},${lng}&hl=${loc.value}&z=15&output=embed`
}

// 取得
watchEffect(async () => {
  loading.value = true; error.value = ''
  try {
    await store.fetchOne(loc.value, route.params.slug)
  } catch (e) {
    error.value = e?.response?.data?.message || e.message || 'Failed to load'
  } finally {
    loading.value = false
  }
})

// ギャラリー（カバー除外、言語別キャプション）
const gallery = computed(() => {
  const arr = place.value?.photos || []
  const lang = locale.value
  return arr
    .filter(p => !p.is_cover)
    .map(p => ({ ...p, caption: lang === 'ja' ? p.caption_ja : p.caption_en }))
})

// ===== ライトボックス =====
const viewerOpen = ref(false)
const viewerIndex = ref(0)
const viewerItems = computed(() => {
  const items = []
  if (img.value) items.push(img.value)
  for (const p of gallery.value) items.push(p)
  return items
})
const currentItem = computed(() => viewerItems.value[viewerIndex.value])

function openViewer(i) { viewerIndex.value = i; viewerOpen.value = true }
function closeViewer() { viewerOpen.value = false }
function next() { viewerIndex.value = (viewerIndex.value + 1) % viewerItems.value.length }
function prev() { viewerIndex.value = (viewerIndex.value + viewerItems.value.length - 1) % viewerItems.value.length }

function onKey(e) {
  if (!viewerOpen.value) return
  if (e.key === 'Escape') return closeViewer()
  if (e.key === 'ArrowRight') return next()
  if (e.key === 'ArrowLeft') return prev()
}
onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))

const bigSrc = computed(() => currentItem.value?.full || currentItem.value?.original || currentItem.value?.src || '')
</script>
