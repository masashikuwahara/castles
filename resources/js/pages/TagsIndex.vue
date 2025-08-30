<template>
  <div>
    <h2 class="text-xl font-bold mb-4">タグ一覧</h2>
    <div v-if="loading">Loading...</div>
    <div v-else class="flex flex-wrap gap-2">
      <router-link v-for="t in tags" :key="t.slug"
        class="px-3 py-1 rounded-full border hover:bg-gray-50 text-sm"
        :to="rl({ name:'list', query:{ tags: t.slug } })">
        #{{ t.name }}
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useLocaleRoute } from '../composables/useLocaleRoute'
import { listTags } from '../lib/api'

const route = useRoute()
const tags = ref([])
const loading = ref(true)
const { rl, loc } = useLocaleRoute()

onMounted(async () => {
  const { data } = await listTags(route.params.locale)
  tags.value = data.data || []
  loading.value = false
})
</script>
