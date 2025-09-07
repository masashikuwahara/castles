<template>
  <div>
    <h2 class="text-xl font-bold mb-4">管理トップ</h2>
    <ul class="list-disc pl-5 space-y-2">
      <li><router-link :to="rl({ name:'admin-place-new' })" class="underline">城を新規作成</router-link></li>
      <li><router-link :to="rl({ name:'admin-cultural-new' })" class="underline">文化財を新規作成</router-link></li>
    </ul>
  </div>
</template>
<script setup>
import { useLocaleRoute } from '../composables/useLocaleRoute'
const { rl } = useLocaleRoute()
</script>
