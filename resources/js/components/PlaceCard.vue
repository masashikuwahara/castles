<template>
  <article class="bg-white rounded-xl shadow hover:shadow-md transition">
    <div v-if="cover" class="w-full h-40 overflow-hidden rounded-t-xl">
      <picture>
        <!-- AVIFを使うときは先に配置 -->
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
    <!-- 以降はそのまま -->
    <div class="p-3">
      <h3 class="font-semibold line-clamp-1">{{ place.name }}</h3>
      <p class="text-sm text-gray-600 line-clamp-2">{{ place.summary }}</p>
      <div class="mt-2 flex flex-wrap gap-1">
        <router-link v-for="t in place.tags || []" :key="t.slug"
          class="text-xs px-2 py-0.5 bg-gray-100 rounded-full hover:bg-gray-200"
          :to="{ name:'tag', params:{ locale: $route.params.locale, slug: t.slug } }">
          #{{ t.name }}
        </router-link>
      </div>
      <!-- <router-link
        :to="{ name:'detail', params:{ locale: $route.params.locale, slug: place.slug_localized || place.slug } }"
        class="inline-block mt-3 text-sm underline"
      >詳細</router-link> -->
      <router-link :to="detailTo(place)" class="inline-block mt-3 text-sm underline">詳細</router-link>
    </div>
  </article>
</template>

<script setup>
import { useRoute } from 'vue-router'
const props = defineProps({ place: Object })
const cover = props.place?.cover_photo || null
const route = useRoute()
const detailTo = (p) => {
  const name = p.type === 'cultural' ? 'cultural-detail' : 'detail'
  const slug = p.slug_localized || p.slug
  return { name, params: { locale: route.params.locale, slug } }
  }
</script>

<!-- <template>
  <article class="bg-white rounded-xl shadow hover:shadow-md transition">
    <img v-if="cover" :src="cover" alt="" class="w-full h-40 object-cover rounded-t-xl" />
    <div class="p-3">
      <h3 class="font-semibold line-clamp-1">{{ place.name }}</h3>
      <p class="text-sm text-gray-600 line-clamp-2">{{ place.summary }}</p>
      <div class="mt-2 flex flex-wrap gap-1">
        <router-link v-for="t in place.tags || []" :key="t.slug"
          class="text-xs px-2 py-0.5 bg-gray-100 rounded-full hover:bg-gray-200"
          :to="{ name:'tag', params:{ locale: $route.params.locale, slug: t.slug } }">
          #{{ t.name }}
        </router-link>
      </div>
      <router-link
        :to="{ name:'detail', params:{ locale: $route.params.locale, slug: place.slug_localized || place.slug } }"
        class="inline-block mt-3 text-sm underline"
      >詳細</router-link>
    </div>
  </article>
</template>

<script setup>
const props = defineProps({ place: { type: Object, required: true } })
const cover = props.place?.cover_photo?.path || null
</script> -->
