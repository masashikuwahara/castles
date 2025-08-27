<template>
  <div>
    <h2 class="text-xl font-bold mb-3">{{ site?.name }}</h2>

    <!-- ヒーロー画像 -->
    <div v-if="img" class="mb-4 overflow-hidden rounded-xl border bg-white cursor-zoom-in"
    @click="openViewer(0)"
    role="button"
    aria-label="画像を拡大表示"
    >
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
    <div v-else-if="!site" class="text-gray-500">見つかりませんでした。</div>

    <div v-else class="grid lg:grid-cols-3 gap-6">
      <!-- 概要 + 基本情報 -->
      <section class="lg:col-span-2">
        <h3 class="font-semibold mb-2">概要</h3>
        <p class="text-gray-800 whitespace-pre-line">{{ site.summary }}</p>

        <div class="mt-6 grid sm:grid-cols-2 gap-4">
          <div>
            <h4 class="font-semibold text-sm text-gray-600">所在地</h4>
            <p class="text-gray-800">{{ site.prefecture?.name_ja }} {{ site.city }}</p>
          </div>

          <div v-if="designation">
            <h4 class="font-semibold text-sm text-gray-600">指定文化財</h4>
            <p class="text-gray-800">{{ designation }}</p>
          </div>

          <div v-if="site.site_type">
            <h4 class="font-semibold text-sm text-gray-600">遺跡種別</h4>
            <p class="text-gray-800">{{ site.site_type }}</p>
          </div>

          <div v-if="site.period">
            <h4 class="font-semibold text-sm text-gray-600">時代</h4>
            <p class="text-gray-800">{{ site.period }}</p>
          </div>

          <div v-if="site.managing_agency">
            <h4 class="font-semibold text-sm text-gray-600">管理主体</h4>
            <p class="text-gray-800">{{ site.managing_agency }}</p>
          </div>

          <div v-if="site.official_url">
            <h4 class="font-semibold text-sm text-gray-600">公式サイト</h4>
            <p class="text-gray-800">
              <a :href="ensureHttp(site.official_url)" target="_blank" rel="noopener noreferrer" class="underline">
                {{ site.official_url }}
              </a>
            </p>
          </div>

          <div v-if="site.rating">
            <h4 class="font-semibold text-sm text-gray-600">おすすめ度</h4>
            <p class="text-gray-800">★{{ site.rating }}</p>
          </div>
        </div>

        <!-- タグ -->
        <div v-if="(site.tags?.length)" class="mt-6">
          <h4 class="font-semibold text-sm text-gray-600 mb-2">タグ</h4>
          <div class="flex flex-wrap gap-2">
            <router-link
              v-for="t in site.tags" :key="t.slug"
              class="text-xs px-2 py-0.5 bg-gray-100 rounded-full hover:bg-gray-200"
              :to="rl({ name:'list', query:{ tags: t.slug, type: 'cultural' } })">
              #{{ t.name }}
            </router-link>
          </div>
        </div>

        <!-- メタ（任意：あれば表示） -->
        <div v-if="Object.keys(meta).length" class="mt-6">
          <h3 class="font-semibold mb-2">見学情報</h3>
          <div class="grid sm:grid-cols-2 gap-4 text-sm">
            <div v-if="meta.opening_hours">
              <div class="text-gray-600">開館時間</div>
              <div class="text-gray-800 whitespace-pre-line">{{ meta.opening_hours }}</div>
            </div>
            <div v-if="meta.closed">
              <div class="text-gray-600">休館日</div>
              <div class="text-gray-800 whitespace-pre-line">{{ meta.closed }}</div>
            </div>
            <div v-if="meta.fees">
              <div class="text-gray-600">料金</div>
              <div class="text-gray-800 whitespace-pre-line">{{ meta.fees }}</div>
            </div>
            <div v-if="meta.notes">
              <div class="text-gray-600">備考</div>
              <div class="text-gray-800 whitespace-pre-line">{{ meta.notes }}</div>
            </div>
          </div>
        </div>
      </section>

      <!-- 写真ギャラリー -->
      <section class="lg:col-span-3 mt-8" v-if="gallery.length">
        <h3 class="font-semibold mb-2">Photos</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
          <figure
            v-for="(ph, i) in gallery"
            :key="ph.id || i"
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

      <!-- ライトボックス（モーダル） -->
      <div
        v-if="viewerOpen"
        class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        :aria-label="`${site?.name} の写真`"
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
        <div v-if="site.lat && site.lng" class="rounded-xl overflow-hidden border">
          <iframe
            :src="gmapsEmbed(site.lat, site.lng)"
            class="w-full h-64" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
        <div class="mt-4">
          <router-link :to="rl({ name:'cultural-list' })" class="underline">文化財一覧に戻る</router-link>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watchEffect, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import { useLocaleRoute } from '../composables/useLocaleRoute'
import { useCulturalsStore } from '../stores/culturals'
import { useI18n } from 'vue-i18n'
import { watch, nextTick } from 'vue'

const closeBtn = ref(null)
const lastFocus = ref(null)
const { locale } = useI18n()
const route = useRoute()
const { rl, loc } = useLocaleRoute()
const store = useCulturalsStore()

const loading = ref(true)
const error = ref('')

const site = computed(() => store.current)

// カバー画像（言語別キャプション）
const img = computed(() => {
  const c = site.value?.cover_photo
  if (!c) return null
  const cap = locale.value === 'ja' ? c.caption_ja : c.caption_en
  return { ...c, caption: cap }
})

const designation = computed(() => site.value?.designated_heritage || null)
const meta = computed(() => site.value?.meta || {})

// Google Map
function gmapsEmbed(lat, lng) {
  return `https://www.google.com/maps?q=${lat},${lng}&hl=${loc.value}&z=15&output=embed`
}

const ensureHttp = (u) => {
  if (!u) return ''
  return /^https?:\/\//i.test(u) ? u : `https://${u}`
}

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
  const arr = site.value?.photos || []
  const lang = locale.value
  return arr
    .filter(p => !p.is_cover)
    .map(p => ({ ...p, caption: lang === 'ja' ? p.caption_ja : p.caption_en }))
})

// ===== ライトボックス（拡大 viewer） =====
const viewerOpen = ref(false)
const viewerIndex = ref(0)
// 表示順：カバー → その他
const viewerItems = computed(() => {
  const items = []
  if (img.value) items.push(img.value)
  for (const p of gallery.value) items.push(p)
  return items
})
const currentItem = computed(() => viewerItems.value[viewerIndex.value])

function openViewer(i) {
  viewerIndex.value = i
  viewerOpen.value = true
}
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

// 大きい画像の src（cover-webp があればそれ、なければ original）
const bigSrc = computed(() => currentItem.value?.full || currentItem.value?.original || currentItem.value?.src || '')

watch(viewerOpen, async (v) => {
  if (v) {
    lastFocus.value = document.activeElement
    document.body.style.overflow = 'hidden'
    await nextTick()
    closeBtn.value?.focus?.()
  } else {
    document.body.style.overflow = ''
    lastFocus.value?.focus?.()
  }
})

watch(currentItem, () => {
  const items = viewerItems.value
  if (!items.length) return
  const n = items[(viewerIndex.value + 1) % items.length]
  const u = n?.full || n?.original || n?.src
  if (u) { const img = new Image(); img.src = u }
})
</script>