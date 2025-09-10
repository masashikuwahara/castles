<!-- admin/AdminHome.vue -->
<template>
  <div class="p-6">
    <h2 class="text-xl font-bold mb-4">管理トップ</h2>

    <div v-if="auth.user" class="grid gap-3">
      <router-link class="px-3 py-2 border rounded" :to="{ name:'admin-place-new' }">城を新規作成</router-link>
      <router-link class="px-3 py-2 border rounded" :to="{ name:'admin-cultural-new' }">文化財を新規作成</router-link>
      <button class="px-3 py-2 border rounded" @click="logout()">ログアウト</button>
    </div>

    <div v-else>
      <router-link class="underline" :to="{ name:'admin-login' }">ログインしてください</router-link>
    </div>
  </div>
</template>
<script setup>
import { useAuthStore } from '../stores/auth'
const auth = useAuthStore()
const logout = async () => { await auth.logout() }
</script>
