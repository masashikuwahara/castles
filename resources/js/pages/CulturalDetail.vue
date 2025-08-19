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
    </div>

    <div v-if="loading">Loading...</div>
    <div v-else-if="error" class="p-3 bg-red-50 text-red-700 rounded">{{ error }}</div>
    <div v-else-if="!place" class="text-gray-500">見つかりませんでした。</div>
    <div v-else class="grid lg:grid-cols-3 gap-6">
      <!-- 基本情報 -->
      <section class="lg:col-span-2">
        <h3 class="font-semibold mb-2">概要</h3>
        <p class="text-gray-800 whitespace-pre-line">{{ place.summary }}</p>

        <div class="mt-6 grid sm:grid-cols-2 gap-4">
          <div>
            <h4 class="font-semibold text-sm text-gray-600">所在地</h4>
            <p class="text-gray-800">{{ place.prefecture?.name_ja }} {{ place.city }}</p>
          </div>
          <div v-if="designation">
            <h4 class="font-semibold text-sm text-gray-600">指定文化財</h4>
            <p class="text-gray-800">{{ designation }}</p>
          </div>
          <div v-if="periods.length">
            <h4 class="font-semibold text-sm text-gray-600">時代</h4>
            <p class="text-gray-800">{{ periods.join('・') }}</p>
          </div>
          <div v-if="siteTypes.length">
            <h4 class="font-semibold text-sm text-gray-600">遺跡種別</h4>
            <p class="text-gray-800">{{ siteTypes.join('・') }}</p>
          </div>
          <div v-if="place.built_year">
            <h4 class="font-semibold text-sm text-gray-600">成立年代</h4>
            <p class="text-gray-800">{{ place.built_year }}</p>
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
              :to="{ name:'list', params:{ locale: loc }, query:{ tags: t.slug, type: 'cultural' } }">
              #{{ t.name }}
            </router-link>
          </div>
        </div>

        <!-- 写真ギャラリー（必要なら） -->
        <!-- ここに複数写真を置く実装を後で足せます -->
      </section>

      <!-- サイド：地図など -->
      <aside>
        <div v-if="place.lat && place.lng" class="rounded-xl overflow-hidden border">
          <iframe
            :src="gmapsEmbed(place.lat, place.lng)"
            class="w-full h-64" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
        <div class="mt-4">
          <router-link
            class="text-sm underline"
            :to="{ name:'cultural-list', params:{ locale: loc } }">文化財一覧に戻る</router-link>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watchEffect } from 'vue'
import { useRoute } from 'vue-router'
import { usePlacesStore } from '../stores/places'

const route = useRoute()
const loc = computed(() => route.params.locale || 'ja')
const store = usePlacesStore()

const loading = ref(true)
const error = ref('')
const place = computed(() => store.current)
const img = computed(() => place.value?.cover_photo || null)
const designation = computed(() => place.value?.designated_heritage || null)

// タグから「時代」「遺跡種別」を抽出（slugベース）
const PERIOD_SLUGS = ['joumon','yayoi','kofun','asuka','nara','heian','kamakura','muromachi','edo','meiji']
const TYPE_SLUGS   = ['kofun','kaizuka','kyoseki','teraato','jokaku-ato','isekI','jinya-ato','gokoku','sekiheki']
const periods = computed(() =>
  (place.value?.tags || [])
    .filter(t => PERIOD_SLUGS.includes(t.slug))
    .map(t => t.name)
)
const siteTypes = computed(() =>
  (place.value?.tags || [])
    .filter(t => TYPE_SLUGS.includes(t.slug))
    .map(t => t.name)
)

function gmapsEmbed(lat, lng) {
  return `https://www.google.com/maps?q=${lat},${lng}&hl=${loc.value}&z=15&output=embed`
}

watchEffect(async () => {
  loading.value = true; error.value = ''
  try {
    await store.fetchOne(loc.value, route.params.slug)
    // 文化財以外が誤って来たら城ページへ誘導（保険）
    if (store.current?.type !== 'cultural') {
      // ここで別のページへ飛ばすなら router.push(...) を使用
    }
  } catch (e) {
    error.value = e?.response?.data?.message || e.message || 'Failed to load'
  } finally {
    loading.value = false
  }
})
</script>
