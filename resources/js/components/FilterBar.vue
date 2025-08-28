<template>
  <div class="mb-4 space-y-3">
    <!-- カテゴリ（タブ/セグメント） -->
  <div v-if="props.mode === 'castle'" class="flex flex-wrap gap-2">
    <button
      @click="apply({ type:'castle', top100:1 })"
      :class="chipClass(isActive('top100'))"
      :aria-pressed="isActive('top100')"
    >100名城</button>

    <button
      @click="apply({ type:'castle', top100c:1 })"
      :class="chipClass(isActive('top100c'))"
      :aria-pressed="isActive('top100c')"
    >続100名城</button>

    <button
      @click="apply({ type:'castle', others:1 })"
      :class="chipClass(isActive('others'))"
      :aria-pressed="isActive('others')"
    >それ以外の城</button>

    <button @click="router.push({ name:'cultural-list', params:{ locale: route.params.locale } })" 
    :class="chipClass(isActive('cultural'))"
    :aria-pressed="isActive('cultural')"
    >文化財</button>
  </div>

    <!-- タグ（トグル式） -->
    <!-- <div class="flex items-center justify-between">
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
    </div> -->

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
import { useLocaleRoute } from '../composables/useLocaleRoute'
import { listTags } from '../lib/api'
import { listCulturalTags } from '../lib/api'
import { usePlacesStore } from '../stores/places'

const props = defineProps({
  mode: { type: String, default: 'castle' } // 'castle' | 'cultural'
})

const route = useRoute()
const router = useRouter()
const store = usePlacesStore()
const { rl } = useLocaleRoute()

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
  return '' // 未選択状態
})

function goto(params) {
  if (props.mode === 'cultural') {
    router.replace( rl({ name:'cultural-list', query: { ...params, page: undefined } }) )
  } else {
    router.replace( rl({ name:'list', query: { ...params, page: undefined } }) )
  }
}

function pushQuery(nextQuery = {}) {
  const q = { ...route.query, ...nextQuery, page: undefined }

  if (props.mode === 'cultural') {
    router.replace( rl({ name: 'cultural-list', query: q }) )
  } else {
    router.replace( rl({ name: 'list', query: q }) )
  }
}

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

function apply(next = {}) {
  // 既存クエリから必要なものだけ引き継ぎつつ、置き換え
  const q0 = route.query
  const params = {
    q: next.q ?? q0.q ?? undefined,
    tags: next.tags ?? q0.tags ?? undefined,
    sort: next.sort ?? q0.sort ?? undefined,
    type: next.type ?? q0.type ?? undefined,
    top100: next.top100 ? 1 : undefined,
    top100c: next.top100c ? 1 : undefined,
    others: next.others ? 1 : undefined,
    page: undefined, // 切替時はページをリセット
  }

  // ★ 文化財に切替える場合は castle系フラグを確実に落とす
  if (params.type === 'cultural') {
    params.top100 = undefined
    params.top100c = undefined
    params.others = undefined
  }

  router.replace({ query: params })
  // 念のため即時フェッチ（watchEffect でも動くが、ここでも呼んでおく）
  store.fetchList(route.params.locale, params)
}

function isActive(key) {
  const q = route.query
  if (key === 'cultural') return q.type === 'cultural'
  if (key === 'top100')   return q.type === 'castle' && (q.top100 === '1' || q.top100 == 1)
  if (key === 'top100c')  return q.type === 'castle' && (q.top100c === '1' || q.top100c == 1)
  if (key === 'others')   return q.type === 'castle' && (q.others === '1' || q.others == 1)
  return false
}

// ★ タグ風チップのクラス
const baseChip =
  'inline-flex items-center gap-1 px-3 py-1.5 text-sm rounded-full border select-none ' +
  'transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-black ' +
  'active:scale-[0.98]';
const activeChip   = 'bg-black text-white border-black hover:bg-black';
const inactiveChip = 'bg-white-100 text-gray-800 border-black-100';

const chipClass = (active = false) => `${baseChip} ${active ? activeChip : inactiveChip}`;

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

/* ==== cultural タグ ==== */
// const allTags = ref([])           // [{name,slug,count}]
// const selected = ref([])          // ['slug1','slug2']

const allCulturalTags = ref([])    // [{name,slug,count}]
const selectedCultural = ref([])   // ['slug1','slug2']

onMounted(async () => {
  if (props.mode !== 'cultural') return
  try {
    const { data } = await listCulturalTags(loc.value)
    // allTags.value = data.data || []
    allCulturalTags.value = data.data || []
  } catch {}
})

// URL → 選択状態 同期
watch(() => route.query.tags, (v) => {
  // selected.value = (typeof v === 'string' && v.length)
  selectedCultural.value = (typeof v === 'string' && v.length)
    ? v.split(',').filter(Boolean)
    : []
}, { immediate: true })

// function toggleTag(slug) {
//   const set = new Set(selected.value)
function toggleTagCultural(slug) {
  const set = new Set(selectedCultural.value)
  set.has(slug) ? set.delete(slug) : set.add(slug)
  const tags = Array.from(set)
  pushQuery({ tags: tags.length ? tags.join(',') : undefined })
}
</script>
