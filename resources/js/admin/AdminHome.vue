<template>
  <div class="grid gap-3 md:grid-cols-3">
    <router-link class="px-4 py-3 rounded-xl border hover:bg-gray-50" :to="{ name:'admin-place-new' }">
      城を新規作成
    </router-link>
    <router-link class="px-4 py-3 rounded-xl border hover:bg-gray-50" :to="{ name:'admin-cultural-new' }">
      文化財を新規作成
    </router-link>
    <router-link class="px-4 py-3 rounded-xl border hover:bg-gray-50" :to="{ name:'admin-home' }">
      管理トップ
    </router-link>
  </div>
</template>
