<template>
  <div class="p-6 max-w-5xl mx-auto">
    <h2 class="text-xl font-bold mb-4">文化財を新規作成</h2>

    <form @submit.prevent="submit" class="grid lg:grid-cols-2 gap-6">
      <!-- 基本情報 -->
      <section class="grid gap-3">
        <h3 class="font-semibold">基本</h3>

        <label class="block">
          <div class="text-sm text-gray-600">Slug（英小文字・-）</div>
          <!-- <input v-model="f.slug" class="border rounded px-3 py-2 w-full" placeholder="sannai-maruyama" required /> -->
          <input v-model="f.slug" class="border rounded px-3 py-2 w-full"
          placeholder="sannai-maruyama" required
          pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$" title="英小文字・数字・ハイフンのみ" />
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

        <label class="block">
          <div class="text-sm text-gray-600">指定文化財（例：特別史跡・国宝 など）</div>
          <input v-model="f.designated_heritage" class="border rounded px-3 py-2 w-full" />
        </label>

        <div class="grid grid-cols-2 gap-3">
          <label class="block">
            <div class="text-sm text-gray-600">遺跡種別（任意）</div>
            <input v-model="f.site_type" class="border rounded px-3 py-2 w-full" />
          </label>
          <label class="block">
            <div class="text-sm text-gray-600">時代（任意）</div>
            <input v-model="f.period" class="border rounded px-3 py-2 w-full" />
          </label>
        </div>

        <div class="grid grid-cols-3 gap-3">
          <label class="block">
            <div class="text-sm text-gray-600">おすすめ度（0–5）</div>
            <input v-model.number="f.rating" type="number" min="0" max="5" class="border rounded px-3 py-2 w-full" />
          </label>

          <label class="block">
            <div class="text-sm text-gray-600">管理主体（任意）</div>
            <input v-model="f.managing_agency" class="border rounded px-3 py-2 w-full" />
          </label>

          <label class="block">
            <div class="text-sm text-gray-600">公式サイトURL（任意）</div>
            <input v-model="f.official_url" class="border rounded px-3 py-2 w-full" placeholder="https://..." />
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
          <input v-model="t_en.slug_localized" class="border rounded px-3 py-2 w-full" placeholder="e.g. sannai-maruyama" />
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
      <section class="lg:col-span-2">
        <h3 class="font-semibold mb-2">タグ</h3>
        <div>
          <button
            v-for="t in allTags"
            :key="t.slug"
            type="button"
            class="px-2 py-1 border rounded-full mr-2 mb-2"
            :class="selectedTags.includes(t.slug) ? 'bg-black text-white' : 'bg-white'"
            @click="toggleTag(t.slug)"
          >#{{ t.name }}</button>
        </div>
      </section>

      <!-- 操作 -->
      <section class="lg:col-span-2 flex items-center gap-3 pt-2">
        <button class="px-4 py-2 rounded border" :disabled="loading">保存</button>
        <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
        <div v-if="done" class="text-green-700 text-sm">保存しました</div>
      </section>
    </form>

    <hr class="my-6"/>

    <!-- 画像アップロード（任意） -->
    <!-- <form @submit.prevent="upload" class="grid gap-2">
      <div class="font-semibold">画像を追加（任意）</div>
      <input type="file" @change="onFile" accept="image/*" />
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" v-model="is_cover" /> カバー画像にする
      </label>
      <input v-model="cap_ja" placeholder="キャプション（JA）" class="border rounded px-3 py-2" />
      <input v-model="cap_en" placeholder="Caption (EN)" class="border rounded px-3 py-2" />
      <button class="px-3 py-2 border rounded" :disabled="!createdId || !file || uploading">アップロード</button>
    </form> -->
  <!-- 画像アップロード（任意） -->
  <form @submit.prevent="upload" class="grid gap-2">
    <div class="font-semibold">画像を追加（任意）</div>
    <label class="inline-flex items-center gap-2 text-sm">
      <input type="checkbox" v-model="is_cover" />
      1枚目をカバー画像にする
    </label>
    <input v-model="cap_ja" placeholder="キャプション（JA, 全画像に適用）" class="border rounded px-3 py-2" />
    <input v-model="cap_en" placeholder="Caption (EN, apply to all)" class="border rounded px-3 py-2" />

    <input type="file" multiple @change="onFiles" accept="image/*" />
    <div class="grid grid-cols-3 gap-2 my-2" v-if="previews.length">
      <img v-for="(src,i) in previews" :key="i" :src="src" class="w-full h-24 object-cover rounded border" />
    </div>

    <div class="flex items-center gap-3">
      <button type="submit" class="px-3 py-2 border rounded" :disabled="!canUpload">
        アップロード
      </button>
      <span v-if="!createdId" class="text-sm text-gray-500">まず「保存」で文化財を作成してください</span>
      <span v-else-if="!files.length" class="text-sm text-gray-500">画像を選択してください</span>
      <span v-else-if="uploading" class="text-sm text-gray-500">アップロード中…</span>
    </div>
  </form>
 </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { api, listTags, listPrefectures } from '../lib/api'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'

const route = useRoute()
const { locale } = useI18n()
const loc = computed(()=> route.params.locale || locale.value || 'ja')
const createdId = ref(null)
const loading = ref(false)
const error = ref('')
const done = ref(false)
const allTags = ref([])
const selectedTags = ref([])
const prefectures = ref([])

const f = ref({
  // CulturalSite に合わせたカラム
  prefecture_id: null,
  slug: '',
  city: '',
  lat: null,
  lng: null,
  designated_heritage: '',
  site_type: '',
  period: '',
  rating: 0,
  managing_agency: '',
  official_url: '',
  meta: null,
})

const t_ja = ref({
  name: '',
  slug_localized: '',
  summary: '',
})

const t_en = ref({
  name: '',
  slug_localized: '',
  summary: '',
})

// Meta JSON テキスト⇄オブジェクト
const metaText = ref('')
const metaError = ref('')
watch(metaText, (v) => {
  if (!v.trim()) { f.value.meta = null; metaError.value = ''; return }
  try { f.value.meta = JSON.parse(v); metaError.value = '' }
  catch (e) { metaError.value = e.message }
})

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

async function submit() {
  if (metaError.value) return
  loading.value = true; error.value = ''; done.value = false
  try {
    if (!t_ja.value.slug_localized) t_ja.value.slug_localized = f.value.slug
    if (!t_en.value.slug_localized && t_en.value.name) {
      t_en.value.slug_localized = `${f.value.slug}-site`
    }
    const payload = {
      ...f.value,
      t_ja: t_ja.value,
      t_en: t_en.value,
      tags: selectedTags.value,
    }
    const res = await api.post('/admin/culturals', payload)
    createdId.value = res?.data?.data?.id ?? res?.data?.id ?? null
    done.value = true
  } catch (e) {
    error.value = e?.response?.data?.message || JSON.stringify(e?.response?.data) || e.message
  } finally {
    loading.value = false
  }
}

// 画像アップロード
// const file = ref(null)
// const cap_ja = ref(''); const cap_en = ref('')
// const is_cover = ref(false); const uploading = ref(false)
// function onFile(e){ file.value = e.target.files?.[0] || null }

// async function upload() {
//   if (!createdId.value || !file.value) return
//   uploading.value = true
//   try {
//     const fd = new FormData()
//     fd.append('file', file.value)
//     fd.append('caption_ja', cap_ja.value || '')
//     fd.append('caption_en', cap_en.value || '')
//     fd.append('is_cover', is_cover.value ? '1' : '0')
//     await api.post(`/admin/culturals/${createdId.value}/photos`, fd, {
//       headers: { 'Content-Type': 'multipart/form-data' }
//     })
//     file.value = null; cap_ja.value = ''; cap_en.value = ''; is_cover.value = false
//     alert('アップロードしました')
//   } catch (e) {
//     alert(e?.response?.data?.message || e.message)
//   } finally {
//     uploading.value = false
//   }
// }

  const files = ref([])
  const previews = ref([])
  const cap_ja = ref(''); const cap_en = ref('')
  const is_cover = ref(false); const uploading = ref(false)
  function onFiles(e){
    const list = Array.from(e.target.files || [])
    files.value = list
    previews.value = list.map(f => URL.createObjectURL(f))
  }
  const canUpload = computed(() => !!createdId.value && files.value.length > 0 && !uploading.value)

  async function upload() {
    if (!canUpload.value) return
    uploading.value = true
    try {
      for (let i = 0; i < files.value.length; i++) {
        const file = files.value[i]
        const fd = new FormData()
        fd.append('file', file)
        fd.append('caption_ja', cap_ja.value || '')
        fd.append('caption_en', cap_en.value || '')
        fd.append('is_cover', is_cover.value && i === 0 ? '1' : '0') // 1枚目だけカバー
        await api.post(`/admin/culturals/${createdId.value}/photos`, fd, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
      }
      files.value = []; previews.value = []; // is_cover は好みでリセット
      alert('アップロードしました')
    } catch (e) {
      alert(e?.response?.data?.message || e.message)
    } finally {
      uploading.value = false
    }
  }
</script>
