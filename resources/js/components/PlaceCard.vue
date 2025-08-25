<template>
  <article class="bg-white rounded-xl shadow hover:shadow-md transition">
    <div v-if="cover" class="w-full h-40 overflow-hidden rounded-t-xl">
      <picture>
        <!-- <source v-if="cover.srcset?.avif" :srcset="cover.srcset.avif" type="image/avif" /> -->
        <source v-if="cover.srcset?.webp" :srcset="cover.srcset.webp" type="image/webp" />
        <img
          :src="cover.src"
          :sizes="cover.sizes || '(min-width:1024px) 25vw, (min-width:768px) 33vw, 50vw'"
          :width="cover.width || null"
          :height="cover.height || null"
          loading="lazy"
          decoding="async"
          alt=""
          class="w-full h-40 object-cover"
        />
      </picture>
    </div>

    <div class="p-3">
      <h3 class="font-semibold line-clamp-1">{{ place.name }}</h3>
      <p v-if="place.summary" class="text-sm text-gray-600 line-clamp-2">{{ place.summary }}</p>

      <div class="mt-2 flex flex-wrap gap-1">
        <router-link
          v-for="t in place.tags || []"
          :key="t.slug"
          class="text-xs px-2 py-0.5 bg-gray-100 rounded-full hover:bg-gray-200"
          :to="rl({ name:'tag', params:{ slug: t.slug } })"
        >
          #{{ t.name }}
        </router-link>
      </div>

      <router-link :to="detailTo(place)" class="inline-block mt-3 text-sm underline">
        詳細
      </router-link>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'
import { useLocaleRoute } from '../composables/useLocaleRoute'

const { place } = defineProps({
  place: { type: Object, required: true }
})

const { rl } = useLocaleRoute()

const cover  = computed(() => place?.cover_photo || place?.coverPhoto || null)

// 詳細ページへの to 生成（locale は rl() が注入）
const detailTo = (p) => {
  const name = p?.type === 'cultural' ? 'cultural-detail' : 'detail'
  const slug = p?.slug_localized || p?.slug
  return rl({ name, params: { slug } })
}
</script>