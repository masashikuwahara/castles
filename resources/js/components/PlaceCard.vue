<template>
  <article class="bg-white rounded-xl shadow hover:shadow-md transition">
    <img v-if="cover" :src="cover" alt="" class="w-full h-40 object-cover rounded-t-xl" />
    <div class="p-3">
      <h3 class="font-semibold line-clamp-1">{{ place.name }}</h3>
      <p class="text-sm text-gray-600 line-clamp-2">{{ place.summary }}</p>
      <div class="mt-2 flex flex-wrap gap-1">
        <span v-for="t in place.tags || []" :key="t.slug"
          class="text-xs px-2 py-0.5 bg-gray-100 rounded-full">{{ t.name }}</span>
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
</script>
