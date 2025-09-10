<template>
  <div class="p-6 max-w-3xl">
    <h2 class="text-xl font-bold mb-4">城を新規作成</h2>

    <form @submit.prevent="submit">
      <div class="grid gap-3">
        <label class="block">
          <div class="text-sm text-gray-600">Slug（英小文字・-）</div>
          <input v-model="f.slug" class="border rounded px-3 py-2 w-full" required />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">都道府県ID</div>
          <input v-model.number="f.prefecture_id" type="number" class="border rounded px-3 py-2 w-full" required />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">市区町村</div>
          <input v-model="f.city" class="border rounded px-3 py-2 w-full" />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">名称（日本語）</div>
          <input v-model="f.name_ja" class="border rounded px-3 py-2 w-full" required />
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">概要（日本語）</div>
          <textarea v-model="f.summary_ja" class="border rounded px-3 py-2 w-full" rows="3" />
        </label>

        <details class="mt-2">
          <summary class="cursor-pointer text-sm text-gray-600">英語（任意）</summary>
          <div class="mt-2 grid gap-3">
            <label class="block">
              <div class="text-sm text-gray-600">Name (EN)</div>
              <input v-model="f.name_en" class="border rounded px-3 py-2 w-full" />
            </label>
            <label class="block">
              <div class="text-sm text-gray-600">Summary (EN)</div>
              <textarea v-model="f.summary_en" class="border rounded px-3 py-2 w-full" rows="3" />
            </label>
          </div>
        </details>

        <div class="pt-2 flex gap-2">
          <button class="px-4 py-2 rounded border" :disabled="loading">保存</button>
          <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
          <div v-if="done" class="text-green-700 text-sm">保存しました</div>
        </div>
      </div>
    </form>

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
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { api } from '../lib/api'

const f = ref({
  slug: '', prefecture_id: null, city: '',
  name_ja: '', summary_ja: '',
  name_en: '', summary_en: '',
})
const loading = ref(false)
const error = ref('')
const done = ref(false)
const createdId = ref(null)

async function submit() {
  loading.value = true; error.value = ''; done.value = false
  try {
    const { data } = await api.post('/admin/places', f.value)
    createdId.value = data.data?.id ?? data.id ?? null
    done.value = true
  } catch (e) {
    error.value = e?.response?.data?.message || JSON.stringify(e?.response?.data) || e.message
  } finally {
    loading.value = false
  }
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
