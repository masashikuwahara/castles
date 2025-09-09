<template>
  <div class="max-w-sm mx-auto p-6 bg-white rounded-xl border">
    <h2 class="font-bold mb-4">Admin Login</h2>
    <form @submit.prevent="onSubmit">
      <input v-model="email" type="email" class="w-full border rounded px-3 py-2 mb-2" placeholder="Email" />
      <input v-model="password" type="password" class="w-full border rounded px-3 py-2 mb-3" placeholder="Password" />
      <button class="w-full px-3 py-2 rounded bg-black text-white" :disabled="auth.loading">Login</button>
    </form>
    <p v-if="auth.error" class="mt-2 text-sm text-red-600">{{ auth.error.message || auth.error }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const email = ref('')
const password = ref('')

async function onSubmit() {
  const ok = await auth.login(email.value, password.value)
  if (ok) {
    router.replace(route.query.redirect || { name: 'admin-home' })
  }
}
</script>
