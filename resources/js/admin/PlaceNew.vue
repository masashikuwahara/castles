<template>
  <div class="p-6 max-w-5xl mx-auto">
    <h2 class="text-xl font-bold mb-4">城を新規作成</h2>

    <form @submit.prevent="submit" class="grid lg:grid-cols-2 gap-6">
      <!-- 基本情報 -->
      <section class="grid gap-3">
        <h3 class="font-semibold">基本</h3>

        <label class="block">
          <div class="text-sm text-gray-600">タイプ</div>
          <select v-model="f.type" class="border rounded px-3 py-2 w-full">
            <option value="castle">castle</option>
            <option value="cultural">cultural</option>
          </select>
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">Slug（英小文字・-）</div>
          <input v-model="f.slug" class="border rounded px-3 py-2 w-full" placeholder="himeji,matsumoto" required />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">都道府県</div>
          <select
            v-model.number="f.prefecture_id"
            class="border rounded px-3 py-2 w-full"
            required
          >
            <option disabled value="">選択してください</option>
            <option
              v-for="p in prefectures"
              :key="p.id"
              :value="p.id"
            >
              {{ p.name_ja }} ({{ p.name_en }}) - {{ p.code }}
            </option>
          </select>
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">市区町村</div>
          <input v-model="f.city" class="border rounded px-3 py-2 w-full" />
        </label>

        <div class="grid grid-cols-2 gap-3">
          <label class="block">
            <div class="text-sm text-gray-600">緯度(lat)</div>
            <input v-model.number="f.lat" type="number" step="0.0000001" class="border rounded px-3 py-2 w-full" />
          </label>
          <label class="block">
            <div class="text-sm text-gray-600">経度(lng)</div>
            <input v-model.number="f.lng" type="number" step="0.0000001" class="border rounded px-3 py-2 w-full" />
          </label>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <label class="block">
            <div class="text-sm text-gray-600">成立年（built_year）</div>
            <input v-model="f.built_year" type="text" class="border rounded px-3 py-2 w-full" />
          </label>
          <label class="block">
            <div class="text-sm text-gray-600">廃城年（abolished_year）</div>
            <input v-model.number="f.abolished_year" type="number" class="border rounded px-3 py-2 w-full" />
          </label>
        </div>
      </section>

      <!-- 構造・文化財・評価 -->
      <section class="grid gap-3">
        <h3 class="font-semibold">構造・文化財・評価</h3>

        <label class="block">
          <div class="text-sm text-gray-600">城郭構造（castle_structure）</div>
          <input v-model="f.castle_structure" class="border rounded px-3 py-2 w-full" />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">天守構造（tenshu_structure）</div>
          <input v-model="f.tenshu_structure" class="border rounded px-3 py-2 w-full" />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">築城者（founder）</div>
          <input v-model="f.founder" class="border rounded px-3 py-2 w-full" />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">主な改修者（main_renovators）</div>
          <input v-model="f.main_renovators" class="border rounded px-3 py-2 w-full" />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">主要城主（main_lords）</div>
          <input v-model="f.main_lords" class="border rounded px-3 py-2 w-full" />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">指定文化財（designated_heritage）</div>
          <input v-model="f.designated_heritage" class="border rounded px-3 py-2 w-full" />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">遺構（remains）</div>
          <input v-model="f.remains" class="border rounded px-3 py-2 w-full" />
        </label>

        <div class="grid grid-cols-3 gap-3">
          <label class="block">
            <div class="text-sm text-gray-600">おすすめ度（0–5）</div>
            <input v-model.number="f.rating" type="number" min="0" max="5" class="border rounded px-3 py-2 w-full" />
          </label>

          <label class="block">
            <div class="text-sm text-gray-600">公式サイトURL（任意）</div>
            <input v-model="f.official_url" class="border rounded px-3 py-2 w-full" placeholder="https://..." />
          </label>
        </div>
        <div class="grid grid-cols-3 gap-3">
          <label class="inline-flex items-center gap-2 mt-6">
            <input type="checkbox" v-model="f.is_top100" /> 100名城
          </label>
          <label class="inline-flex items-center gap-2 mt-6">
            <input type="checkbox" v-model="f.is_top100_continued" /> 続100名城
          </label>
        </div>
      </section>

      <!-- 翻訳（日本語） -->
      <section class="grid gap-3">
        <h3 class="font-semibold">翻訳（日本語）</h3>

        <label class="block">
          <div class="text-sm text-gray-600">名称（日本語）</div>
          <input v-model="t_ja.name" class="border rounded px-3 py-2 w-full" required />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">日本語 slug_localized</div>
          <input v-model="t_ja.slug_localized" class="border rounded px-3 py-2 w-full" placeholder="未入力なら slug を使用" />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">概要（日本語）</div>
          <textarea v-model="t_ja.summary" class="border rounded px-3 py-2 w-full" rows="3" />
        </label>
      </section>

      <!-- 翻訳（英語） -->
      <section class="grid gap-3">
        <h3 class="font-semibold">Translation (EN)</h3>

        <label class="block">
          <div class="text-sm text-gray-600">Name (EN)</div>
          <input v-model="t_en.name" class="border rounded px-3 py-2 w-full" />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">slug_localized (EN)</div>
          <input v-model="t_en.slug_localized" class="border rounded px-3 py-2 w-full" placeholder="e.g. matsumoto-castle" />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">Summary (EN)</div>
          <textarea v-model="t_en.summary" class="border rounded px-3 py-2 w-full" rows="3" />
        </label>
      </section>

      <!-- Meta(JSON) -->
      <section class="lg:col-span-2">
        <h3 class="font-semibold mb-2">Meta（JSON / 任意）</h3>
        <textarea v-model="metaText" class="border rounded px-3 py-2 w-full font-mono" rows="6"
                  placeholder='{"opening_hours":"...", "closed":"...", "fees":"...", "notes":"..."}'></textarea>
        <p v-if="metaError" class="text-sm text-red-600 mt-1">JSONエラー: {{ metaError }}</p>
      </section>
      
      <!-- タグ -->
      <div class="mt-4">
        <div class="text-sm text-gray-600 mb-1">タグ</div>
        <button
          v-for="t in allTags"
          :key="t.slug"
          type="button"
          class="px-2 py-1 border rounded-full mr-2 mb-2"
          :class="selectedTags.includes(t.slug) ? 'bg-black text-white' : 'bg-white'"
          @click="toggleTag(t.slug)"
        >#{{ t.name }}</button>
      </div>

      <!-- 操作 -->
      <section class="lg:col-span-2 flex items-center gap-3 pt-2">
        <button class="px-4 py-2 rounded border" :disabled="loading">保存</button>
        <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
        <div v-if="done" class="text-green-700 text-sm">保存しました</div>
      </section>
    </form>
  </div>

  <hr class="my-6"/>

  <form @submit.prevent="upload" class="grid gap-2">
    <div class="font-semibold">画像を追加（任意）</div>

    !-- カバー指定 & まとめキャプション -->
    <label class="inline-flex items-center gap-2 text-sm">
      <input type="checkbox" v-model="is_cover" />
      1枚目をカバー画像にする
    </label>
    <input v-model="cap_ja" placeholder="キャプション（JA, 全画像に適用）" class="border rounded px-3 py-2" />
    <input v-model="cap_en" placeholder="Caption (EN, apply to all)" class="border rounded px-3 py-2" />

    <!-- 複数選択 -->
    <input type="file" multiple @change="onFiles" accept="image/*" />

    <!-- プレビュー -->
    <div class="grid grid-cols-3 gap-2 my-2" v-if="previews.length">
      <img v-for="(src,i) in previews" :key="i" :src="src" class="w-full h-24 object-cover rounded border" />
    </div>

    <!-- ボタン（状態表示つき） -->
    <div class="flex items-center gap-3">
      <button
        type="submit"
        class="px-3 py-2 border rounded"
        :disabled="!canUpload"
      >
        アップロード
      </button>
      <span v-if="!createdId" class="text-sm text-gray-500">
        まず「保存」を押して城を作成してください（画像はその後に登録できます）
      </span>
      <span v-else-if="!files.length" class="text-sm text-gray-500">
        画像を選択してください
      </span>
      <span v-else-if="uploading" class="text-sm text-gray-500">
        アップロード中…
      </span>
    </div>
  </form>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { api, listTags, listPrefectures } from '../lib/api'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'
import { onBeforeUnmount } from 'vue'

const route = useRoute()
const { locale } = useI18n()
const loc = computed(()=> route.params.locale || locale.value || 'ja')
const createdId = ref(null)
const allTags = ref([])
const selectedTags = ref([])
const prefectures = ref([])

const f = ref({
  type: 'castle',
  slug: '',
  prefecture_id: null,
  city: '',
  lat: null,
  lng: null,
  built_year: null,
  abolished_year: null,
  castle_structure: '',
  tenshu_structure: '',
  founder: '',
  main_renovators: '',
  main_lords: '',
  designated_heritage: '',
  remains: '',
  rating: 0,
  is_top100: false,
  is_top100_continued: false,
  official_url: '',
  meta: null,
})

const t_ja = ref({
  name: '',
  slug_localized: '',
  summary: '',
  castle_structure_text: '',
  tenshu_structure_text: '',
  designated_heritage_text: '',
  remains_text: '',
})

const t_en = ref({
  name: '',
  slug_localized: '',
  summary: '',
  castle_structure_text: '',
  tenshu_structure_text: '',
  designated_heritage_text: '',
  remains_text: '',
})

// Meta JSON テキスト⇄オブジェクト
const metaText = ref('')
const metaError = ref('')
watch(metaText, (v) => {
  if (!v.trim()) { f.value.meta = null; metaError.value = ''; return }
  try {
    f.value.meta = JSON.parse(v)
    metaError.value = ''
  } catch (e) {
    metaError.value = e.message
  }
})

const loading = ref(false)
const error = ref('')
const done = ref(false)

async function submit() {
  if (metaError.value) return
  loading.value = true; error.value = ''; done.value = false
  try {
    if (!t_ja.value.slug_localized) t_ja.value.slug_localized = f.value.slug
    if (!t_en.value.slug_localized && t_en.value.name) {
      t_en.value.slug_localized = `${f.value.slug}-castle`
    }
    const payload = { ...f.value, t_ja: t_ja.value, t_en: t_en.value, tags: selectedTags.value }
    const res = await api.post('/admin/places', payload)
    createdId.value = res?.data?.data?.id ?? res?.data?.id ?? null
    done.value = true
    dirty.value = false
  } catch (e) {
    error.value = e?.response?.data?.message || JSON.stringify(e?.response?.data) || e.message
  } finally {
    loading.value = false
  }
}

const dirty = ref(false)
watch([f, t_ja, t_en, metaText, selectedTags], () => { dirty.value = true }, { deep:true })

window.addEventListener('beforeunload', onBeforeUnload)
function onBeforeUnload(e){ if(dirty.value){ e.preventDefault(); e.returnValue=''; } }
onBeforeUnmount(()=> window.removeEventListener('beforeunload', onBeforeUnload))

onMounted(async () => {
  const [{ data: tRes }, { data: pRes }] = await Promise.all([
    listTags(loc.value),
    listPrefectures(loc.value),
  ])
  allTags.value = tRes?.data || tRes
  prefectures.value = pRes?.data || pRes
})

function toggleTag(slug){
  const s = new Set(selectedTags.value)
  s.has(slug) ? s.delete(slug) : s.add(slug)
  selectedTags.value = [...s]
}

const cap_ja = ref('')
const cap_en = ref('')
const is_cover = ref(false)
const uploading = ref(false)
const files = ref([])
const previews = ref([])
function onFiles(e){
  const list = Array.from(e.target.files || [])
  files.value = list
  previews.value = list.map(f => URL.createObjectURL(f))
}

const canUpload = computed(() =>
  !!createdId.value && files.value.length > 0 && !uploading.value
)


async function upload(){
  if (!canUpload.value) return
  uploading.value = true
  try {
    for (let i = 0; i < files.value.length; i++) {
      const file = files.value[i]
      const fd = new FormData()
      fd.append('file', file)
      fd.append('caption_ja', cap_ja.value || '')
      fd.append('caption_en', cap_en.value || '')
      // 「1枚目をカバー」にチェックなら 1枚目のみカバー
      fd.append('is_cover', is_cover.value && i === 0 ? '1' : '0')

      await api.post(`/admin/places/${createdId.value}/photos`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    files.value = []
    previews.value = []
    alert('アップロードしました')
  } catch (e) {
    alert(e?.response?.data?.message || e.message)
  } finally {
    uploading.value = false
  }
}
</script>
