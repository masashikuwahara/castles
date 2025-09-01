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
          :to="{ name:'tag', params:{ locale: $route.params.locale, slug: t.slug } }">
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

    <div>
    <h2 class="text-xl font-bold mb-3">{{ place?.name }}</h2>

    <!-- ヒーロー（クリックで拡大） -->
    <div v-if="img" class="mb-4 overflow-hidden rounded-xl border bg-white cursor-zoom-in"
         @click="openViewer(0)" role="button" aria-label="画像を拡大表示">
      <picture>
        <source v-if="img.srcset?.webp" :srcset="img.srcset.webp" type="image/webp" />
        <img :src="img.src" :sizes="img.sizes || '100vw'" loading="lazy" decoding="async" class="w-full object-cover" alt="" />
      </picture>
      <p v-if="img.caption" class="mt-2 px-3 pb-2 text-sm text-gray-600">{{ img.caption }}</p>
    </div>

    <!-- 既存の城データ表示（構造/築城主…）はそのまま -->

    <!-- ギャラリー -->
    <section class="lg:col-span-3 mt-8" v-if="gallery.length">
      <h3 class="font-semibold mb-2">Photos</h3>
      <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <figure v-for="(ph, i) in gallery" :key="ph.id || i"
                class="rounded-lg overflow-hidden border bg-white cursor-zoom-in"
                @click="openViewer(img ? i + 1 : i)" role="button">
          <picture>
            <source v-if="ph.srcset?.webp" :srcset="ph.srcset.webp" type="image/webp" />
            <img :src="ph.src" loading="lazy" decoding="async" alt="" class="w-full h-40 object-cover" />
          </picture>
          <figcaption v-if="ph.caption" class="px-2 py-1 text-xs text-gray-600">{{ ph.caption }}</figcaption>
        </figure>
      </div>
    </section>

    <!-- ライトボックス -->
    <div v-if="viewerOpen"
         class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
         role="dialog" aria-modal="true" :aria-label="`${place?.name} の写真`"
         @click.self="closeViewer">
      <button ref="closeBtn" class="absolute top-4 right-4 text-white text-2xl" @click="closeViewer" aria-label="Close">✕</button>
      <button class="absolute left-4 top-1/2 -translate-y-1/2 text-white text-3xl" @click="prev" aria-label="Prev">‹</button>
      <button class="absolute right-4 top-1/2 -translate-y-1/2 text-white text-3xl" @click="next" aria-label="Next">›</button>

      <div class="max-w-5xl max-h-[80vh]">
        <img :src="bigSrc" class="max-w-full max-h-[80vh] object-contain" decoding="async" alt=""
             @error="(e)=>{const t=e.target; if(currentItem?.original && t.src!==currentItem.original){ t.src=currentItem.original }}"/>
        <div v-if="currentItem?.caption" class="mt-2 text-center text-sm text-white/90">{{ currentItem.caption }}</div>
      </div>
    </div>
  </div>
</template>

<!-- resources/js/pages/PlaceDetail.vue（主要部分の置き換え） -->
<template>
  <div>
    <h2 class="text-xl font-bold mb-3">{{ place?.name }}</h2>

    <!-- ヒーロー（クリックで拡大） -->
    <div v-if="img" class="mb-4 overflow-hidden rounded-xl border bg-white cursor-zoom-in"
         @click="openViewer(0)" role="button" aria-label="画像を拡大表示">
      <picture>
        <source v-if="img.srcset?.webp" :srcset="img.srcset.webp" type="image/webp" />
        <img :src="img.src" :sizes="img.sizes || '100vw'" loading="lazy" decoding="async" class="w-full object-cover" alt="" />
      </picture>
      <p v-if="img.caption" class="mt-2 px-3 pb-2 text-sm text-gray-600">{{ img.caption }}</p>
    </div>

    <!-- 既存の城データ表示（構造/築城主…）はそのまま -->

    <!-- ギャラリー -->
    <section class="lg:col-span-3 mt-8" v-if="gallery.length">
      <h3 class="font-semibold mb-2">Photos</h3>
      <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <figure v-for="(ph, i) in gallery" :key="ph.id || i"
                class="rounded-lg overflow-hidden border bg-white cursor-zoom-in"
                @click="openViewer(img ? i + 1 : i)" role="button">
          <picture>
            <source v-if="ph.srcset?.webp" :srcset="ph.srcset.webp" type="image/webp" />
            <img :src="ph.src" loading="lazy" decoding="async" alt="" class="w-full h-40 object-cover" />
          </picture>
          <figcaption v-if="ph.caption" class="px-2 py-1 text-xs text-gray-600">{{ ph.caption }}</figcaption>
        </figure>
      </div>
    </section>

    <!-- ライトボックス -->
    <div v-if="viewerOpen"
         class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
         role="dialog" aria-modal="true" :aria-label="`${place?.name} の写真`"
         @click.self="closeViewer">
      <button ref="closeBtn" class="absolute top-4 right-4 text-white text-2xl" @click="closeViewer" aria-label="Close">✕</button>
      <button class="absolute left-4 top-1/2 -translate-y-1/2 text-white text-3xl" @click="prev" aria-label="Prev">‹</button>
      <button class="absolute right-4 top-1/2 -translate-y-1/2 text-white text-3xl" @click="next" aria-label="Next">›</button>

      <div class="max-w-5xl max-h-[80vh]">
        <img :src="bigSrc" class="max-w-full max-h-[80vh] object-contain" decoding="async" alt=""
             @error="(e)=>{const t=e.target; if(currentItem?.original && t.src!==currentItem.original){ t.src=currentItem.original }}"/>
        <div v-if="currentItem?.caption" class="mt-2 text-center text-sm text-white/90">{{ currentItem.caption }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watchEffect, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { usePlacesStore } from '../stores/places'
import { useLocaleRoute } from '../composables/useLocaleRoute'

const { locale } = useI18n()
const route = useRoute()
const { rl, loc } = useLocaleRoute()
const store = usePlacesStore()

const loading = ref(true)
const error = ref('')

const place = computed(() => store.current)

// カバー（言語別キャプション）
const img = computed(() => {
  const c = place.value?.cover_photo
  if (!c) return null
  const cap = locale.value === 'ja' ? c.caption_ja : c.caption_en
  return { ...c, caption: cap }
})

// ギャラリー（カバー除外 → 言語別キャプション）
const gallery = computed(() => {
  const arr = place.value?.photos || []
  const lang = locale.value
  return arr.map(p => ({ ...p, caption: lang === 'ja' ? p.caption_ja : p.caption_en }))
})

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

// ===== Lightbox =====
const viewerOpen = ref(false)
const viewerIndex = ref(0)
const closeBtn = ref(null)
const lastFocus = ref(null)

const viewerItems = computed(() => {
  const items = []
  if (img.value) items.push(img.value)
  for (const p of gallery.value) items.push(p)
  return items
})
const currentItem = computed(() => viewerItems.value[viewerIndex.value])
const bigSrc = computed(() => currentItem.value?.full || currentItem.value?.original || currentItem.value?.src || '')

function openViewer(i){ viewerIndex.value = i; viewerOpen.value = true }
function closeViewer(){ viewerOpen.value = false }
function next(){ viewerIndex.value = (viewerIndex.value + 1) % viewerItems.value.length }
function prev(){ viewerIndex.value = (viewerIndex.value + viewerItems.value.length - 1) % viewerItems.value.length }

function onKey(e){
  if (!viewerOpen.value) return
  if (e.key === 'Escape') return closeViewer()
  if (e.key === 'ArrowRight') return next()
  if (e.key === 'ArrowLeft') return prev()
}
onMounted(()=> window.addEventListener('keydown', onKey))
onBeforeUnmount(()=> window.removeEventListener('keydown', onKey))

watch(viewerOpen, async v => {
  if (v) {
    lastFocus.value = document.activeElement
    document.body.style.overflow = 'hidden'
    await nextTick(); closeBtn.value?.focus?.()
  } else {
    document.body.style.overflow = ''
    lastFocus.value?.focus?.()
  }
})

// 先読み
watch(currentItem, () => {
  const items = viewerItems.value
  if (!items.length) return
  const n = items[(viewerIndex.value + 1) % items.length]
  const u = n?.full || n?.original || n?.src
  if (u) { const im = new Image(); im.src = u }
})
</script>

