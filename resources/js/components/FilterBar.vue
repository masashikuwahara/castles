<template>
  <div class="mb-4 space-y-3">
    <!-- カテゴリ（タブ/セグメント） -->
    <div class="flex flex-wrap gap-2">
      <button
        :class="btnClass(active==='top100')"
        @click="setCategory('top100')">100名城</button>

      <button
        :class="btnClass(active==='top100c')"
        @click="setCategory('top100c')">続100名城</button>

      <button
        :class="btnClass(active==='others')"
        @click="setCategory('others')">それ以外の城</button>

      <button
        :class="btnClass(active==='cp')"
        @click="setCategory('cp')">文化財</button>
    </div>

    <!-- タグ（トグル式） -->
    <div class="flex items-center justify-between">
      <div class="font-semibold text-sm">タグ</div>
      <button class="text-sm underline" @click="clearTags" v-if="selectedTags.length">クリア</button>
    </div>
    <div class="flex flex-wrap gap-2">
      <button
        v-for="t in tags"
        :key="t.slug"
        @click="toggleTag(t.slug)"
        :class="tagClass(selectedTags.includes(t.slug))">
        #{{ t.name }}
      </button>
    </div>

    <!-- 並び替え -->
    <div class="flex items-center gap-2">
      <label class="text-sm text-gray-600">並び替え</label>
      <select class="border rounded px-2 py-1" v-model="sort" @change="applyQuery()">
        <option value="rating_desc">おすすめ順</option>
        <option value="name_asc">名前順</option>
        <option value="created_desc">新着</option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { listTags } from '../lib/api'

const route = useRoute()
const router = useRouter()

const tags = ref([])
const selectedTags = ref([])
const sort = ref(route.query.sort || 'rating_desc')

// アクティブカテゴリ（クエリ -> UI）
const active = computed(() => {
  const q = route.query
  if (q.type === 'cultural_property') return 'cp'
  if (q.others === '1' || q.others === 1) return 'others'
  if (q.top100c === '1' || q.top100c === 1) return 'top100c'
  if (q.top100 === '1' || q.top100 === 1) return 'top100'
  // どれでもなければ「100名城」でもなく「文化財」でもない＝全部表示だが、
  // UI上は未選択にならないよう、とりあえず others 以外の「城」代表として top100 を基準にしない。
  return '' // 未選択状態
})

function btnClass(isActive) {
  return [
    'px-3 py-1.5 rounded-full border text-sm',
    isActive
      ? 'bg-gray-900 text-white border-gray-900'
      : 'bg-white hover:bg-gray-50'
  ]
}
function tagClass(isOn) {
  return [
    'text-xs px-2 py-1 rounded-full border',
    isOn ? 'bg-gray-900 text-white border-gray-900' : 'bg-white hover:bg-gray-50'
  ]
}

function setCategory(key) {
  const base = { ...route.query }
  delete base.top100; delete base.top100c; delete base.others; delete base.type

  if (key === 'top100') {
    base.type = 'castle'; base.top100 = 1
  } else if (key === 'top100c') {
    base.type = 'castle'; base.top100c = 1
  } else if (key === 'others') {
    base.others = 1 // コントローラ側で castle + 非top 条件を付与
  } else if (key === 'cp') {
    base.type = 'cultural_property'
  }

  router.replace({ query: base })
}

function toggleTag(slug) {
  const set = new Set(selectedTags.value)
  set.has(slug) ? set.delete(slug) : set.add(slug)
  selectedTags.value = [...set]
  applyQuery()
}
function clearTags() {
  selectedTags.value = []
  applyQuery()
}
function applyQuery() {
  const q = { ...route.query, sort: sort.value }
  if (selectedTags.value.length) q.tags = selectedTags.value.join(',')
  else delete q.tags
  router.replace({ query: q })
}

onMounted(async () => {
  // タグ一覧取得
  const { data } = await listTags(route.params.locale)
  tags.value = data.data || []

  // URLのtags -> UI
  const qs = route.query.tags
  selectedTags.value = qs ? String(qs).split(',').filter(Boolean) : []
})

// URLの変更に追随（戻る/進むでUI崩れないように）
watch(() => route.query.tags, (v) => {
  selectedTags.value = v ? String(v).split(',').filter(Boolean) : []
})
watch(() => route.query.sort, (v) => {
  if (v) sort.value = v
})
</script>
