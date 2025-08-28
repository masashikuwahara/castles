<template>
  <div class="flex flex-wrap gap-2 items-center mb-4">
    <!-- 検索 -->
    <input v-model="q" @keyup.enter="apply"
           class="border rounded px-3 py-2 w-64" :placeholder="t('search_placeholder')"/>

    <!-- （任意）時代 -->
    <select v-model="period" class="border rounded px-2 py-2">
      <option value="">時代: すべて</option>
      <option value="縄文時代">縄文時代</option>
      <option value="弥生時代">弥生時代</option>
      <option value="古墳時代">古墳時代</option>
      <!-- 必要に応じて増やす -->
    </select>

    <!-- （任意）遺跡種別 -->
    <select v-model="siteType" class="border rounded px-2 py-2">
      <option value="">遺跡種別: すべて</option>
      <option value="大規模集落">大規模集落</option>
      <option value="貝塚">貝塚</option>
      <option value="古墳">古墳</option>
      <!-- 必要に応じて増やす -->
    </select>

    <button @click="apply" class="px-3 py-2 border rounded">検索</button>
    <button @click="clearAll" class="px-3 py-2 border rounded">クリア</button>
  </div>
</template>

<script setup>
import {ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {useI18n} from 'vue-i18n'
import {useLocaleRoute} from '../composables/useLocaleRoute'

const route = useRoute()
const router = useRouter()
const { rl } = useLocaleRoute()
const { t } = useI18n()

// 初期値は現在のクエリから
const q        = ref(route.query.q || '')
const period   = ref(route.query.period || '')
const siteType = ref(route.query.site_type || '')

// クエリが外から変わったとき同期（戻る操作など）
watch(() => route.query, (qparams) => {
  q.value        = qparams.q || ''
  period.value   = qparams.period || ''
  siteType.value = qparams.site_type || ''
})

function apply() {
  const query = {
    q: q.value || undefined,
    period: period.value || undefined,
    site_type: siteType.value || undefined,
    page: undefined,           // ページャはリセット
  }
  router.replace( rl({ name:'cultural-list', query }) )
}

function clearAll() {
  q.value = ''; period.value = ''; siteType.value = ''
  apply()
}
</script>
