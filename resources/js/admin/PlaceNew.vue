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
          <input v-model="f.slug" class="border rounded px-3 py-2 w-full" placeholder="himeji" required />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">都道府県ID</div>
          <input v-model.number="f.prefecture_id" type="number" class="border rounded px-3 py-2 w-full" required />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">市区町村</div>
          <input v-model="f.city" class="border rounded px-3 py-2 w-full" />
        </label>

        <div class="grid grid-cols-2 gap-3">
          <label class="block">
            <div class="text-sm text-gray-600">緯度(lat)</div>
            <input v-model="f.lat" type="number" step="0.0000001" class="border rounded px-3 py-2 w-full" />
          </label>
          <label class="block">
            <div class="text-sm text-gray-600">経度(lng)</div>
            <input v-model="f.lng" type="number" step="0.0000001" class="border rounded px-3 py-2 w-full" />
          </label>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <label class="block">
            <div class="text-sm text-gray-600">成立年（built_year）</div>
            <input v-model.number="f.built_year" type="text" class="border rounded px-3 py-2 w-full" />
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

        <!-- <details>
          <summary class="cursor-pointer text-sm text-gray-600">詳細テキスト（日本語/任意）</summary>
          <div class="grid gap-3 mt-2">
            <textarea v-model="t_ja.castle_structure_text" class="border rounded px-3 py-2 w-full" rows="2" placeholder="城郭構造の解説" />
            <textarea v-model="t_ja.tenshu_structure_text" class="border rounded px-3 py-2 w-full" rows="2" placeholder="天守構造の解説" />
            <textarea v-model="t_ja.designated_heritage_text" class="border rounded px-3 py-2 w-full" rows="2" placeholder="文化財指定の解説" />
            <textarea v-model="t_ja.remains_text" class="border rounded px-3 py-2 w-full" rows="2" placeholder="遺構の解説" />
          </div>
        </details> -->
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

        <!-- <details>
          <summary class="cursor-pointer text-sm text-gray-600">Detail texts (EN / optional)</summary>
          <div class="grid gap-3 mt-2">
            <textarea v-model="t_en.castle_structure_text" class="border rounded px-3 py-2 w-full" rows="2" />
            <textarea v-model="t_en.tenshu_structure_text" class="border rounded px-3 py-2 w-full" rows="2" />
            <textarea v-model="t_en.designated_heritage_text" class="border rounded px-3 py-2 w-full" rows="2" />
            <textarea v-model="t_en.remains_text" class="border rounded px-3 py-2 w-full" rows="2" />
          </div>
        </details> -->
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

    <!-- 画像アップロード（任意） -->
    <form @submit.prevent="upload" class="grid gap-2">
      <div class="font-semibold">画像を追加（任意）</div>
      <input type="file" @change="onFile" accept="image/*" />
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" v-model="is_cover" /> カバー画像にする
      </label>
      <input v-model="cap_ja" placeholder="キャプション（JA）" class="border rounded px-3 py-2" />
      <input v-model="cap_en" placeholder="Caption (EN)" class="border rounded px-3 py-2" />
      <button class="px-3 py-2 border rounded" :disabled="!createdId || !file || uploading">アップロード</button>
    </form>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { api, listTags } from '../lib/api'

const createdId = ref(null)
const allTags = ref([])
const selectedTags = ref([])

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
    const payload = {
      ...f.value,
      // boolean はAPI側でバリデーションしてもらう前提
      t_ja: t_ja.value,
      t_en: t_en.value,
      tags: selectedTags.value,
    }
    const res = await api.post('/admin/places', payload)
    createdId.value = res?.data?.data?.id ?? res?.data?.id ?? null
    done.value = true
  } catch (e) {
    error.value = e?.response?.data?.message || JSON.stringify(e?.response?.data) || e.message
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  const { data } = await listTags('ja') // もしくは現在の言語
  allTags.value = data?.data || data // 返却形状に合わせて
})

function toggleTag(slug){
  const s = new Set(selectedTags.value)
  s.has(slug) ? s.delete(slug) : s.add(slug)
  selectedTags.value = [...s]
}

// 画像アップロード
const file = ref(null)
const cap_ja = ref(''); const cap_en = ref('')
const is_cover = ref(false); const uploading = ref(false)
function onFile(e){ file.value = e.target.files?.[0] || null }

async function upload() {
  if (!createdId.value || !file.value) return
  uploading.value = true
  try {
    const fd = new FormData()
    fd.append('file', file.value)
    fd.append('caption_ja', cap_ja.value || '')
    fd.append('caption_en', cap_en.value || '')
    fd.append('is_cover', is_cover.value ? '1' : '0')
    await api.post(`/admin/places/${createdId.value}/photos`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    file.value = null; cap_ja.value = ''; cap_en.value = ''; is_cover.value = false
    alert('アップロードしました')
  } catch (e) {
    alert(e?.response?.data?.message || e.message)
  } finally {
    uploading.value = false
  }
}
</script>
