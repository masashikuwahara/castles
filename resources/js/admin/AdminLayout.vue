<template>
  <div class="max-w-7xl mx-auto px-4 py-6 grid md:grid-cols-5 gap-6">
    <aside class="md:col-span-1 space-y-2">
      <router-link class="block px-3 py-2 rounded border" :to="rl({ name:'admin-home' })">管理トップ</router-link>
      <router-link class="block px-3 py-2 rounded border" :to="rl({ name:'admin-place-new' })">城を新規作成</router-link>
      <router-link class="block px-3 py-2 rounded border" :to="rl({ name:'admin-cultural-new' })">文化財を新規作成</router-link>
    </aside>
    <main class="md:col-span-4">
      <router-view />
    </main>
  </div>
</template>
<script setup>
import { useLocaleRoute } from '../composables/useLocaleRoute'
const { rl } = useLocaleRoute()
</script>
