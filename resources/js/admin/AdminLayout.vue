<template>
  <div class="max-w-5xl mx-auto px-4 py-6">
    <header class="flex justify-between items-center mb-6">
      <h1 class="font-bold">Admin</h1>
      <div v-if="auth.user" class="flex items-center gap-3">
        <span class="text-sm text-gray-600">{{ auth.user.name }}さんログイン中</span>
        <button class="px-3 py-1 border rounded" @click="logout">Logout</button>
      </div>
    </header>
    <router-view />
  </div>
</template>
<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
const auth = useAuthStore()
const router = useRouter()
async function logout() {
  await auth.logout()
  router.replace({ name: 'admin-login' })
}
</script>
