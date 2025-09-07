<template>
  <div class="max-w-3xl mx-auto space-y-6">
    <h2 class="text-xl font-bold">城ページの新規作成</h2>

    <section class="clay p-4 space-y-3">
      <div class="grid sm:grid-cols-2 gap-3">
        <label class="block">
          <div class="text-sm text-gray-600">スラッグ</div>
          <input v-model="fields.slug" class="w-full border rounded px-3 py-2" placeholder="matsumoto">
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">都道府県</div>
          <select v-model="fields.prefecture_id" class="w-full border rounded px-3 py-2">
            <option v-for="p in masters.prefectures" :key="p.id" :value="p.id">{{ p.name_ja }}</option>
          </select>
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">市区町村</div>
          <input v-model="fields.city" class="w-full border rounded px-3 py-2">
        </label>

        <label class="block">
          <div class="text-sm text-gray-600">緯度 / 経度</div>
          <div class="flex gap-2">
            <input v-model.number="fields.lat" type="number" step="0.0000001" class="w-full border rounded px-3 py-2">
            <input v-model.number="fields.lng" type="number" step="0.0000001" class="w-full border rounded px-3 py-2">
          </div>
        </label>
      </div>

      <div class="grid sm:grid-cols-2 gap-3">
        <label class="block">
          <div class="text-sm text-gray-600">名称（日本語）</div>
          <input v-model="t.ja.name" class="w-full border rounded px-3 py-2">
        </label>
        <label class="block">
          <div class="text-sm text-gray-600">名称（英語）</div>
          <input v-model="t.en.name" class="w-full border rounded px-3 py-2">
        </label>
      </div>

      <label class="block">
        <div class="text-sm text-gray-600">概要（日本語）</div>
        <textarea v-model="t.ja.summary" rows="3" class="w-full border rounded px-3 py-2"></textarea>
      </label>
      <label class="block">
        <div class="text-sm text-gray-600">Summary（English）</div>
        <textarea v-model="t.en.summary" rows="3" class="w-full border rounded px-3 py-2"></textarea>
      </label>

      <div>
        <div class="text-sm text-gray-600 mb-1">タグ</div>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="tg in masters.tags" :key="tg.slug"
            :class="['clay-btn', selected.has(tg.slug) && 'clay-btn--active']"
            @click.prevent="toggleTag(tg.slug)"
          >#{{ tg.name }}</button>
        </div>
      </div>
    </section>

    <section class="clay p-4 space-y-3">
      <div class="text-sm font-semibold">画像（複数可）</div>
      <input type="file" multiple @change="onFiles">
      <div v-if="files.length" class="mt-2 space-y-2">
        <div v-for="(f,i) in files" :key="i" class="flex items-center gap-2">
          <input type="radio" name="cover" :value="i" v-model="coverIndex">
          <div class="text-sm w-48 truncate">{{ f.name }}</div>
          <input class="border rounded px-2 py-1 text-sm flex-1" placeholder="キャプション（ja）" v-model="captions_ja[i]">
          <input class="border rounded px-2 py-1 text-sm flex-1" placeholder="Caption (en)" v-model="captions_en[i]">
        </div>
        <p class="text-xs text-gray-500">チェックを付けた画像がカバーになります。</p>
      </div>
    </section>

    <div class="flex gap-3">
      <button class="clay-btn clay-pressable" @click="submit" :disabled="submitting">保存</button>
      <div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
      <div v-if="done"  class="text-green-700 text-sm">保存しました</div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getAdminMaster, createPlace } from '../lib/api-admin'
import { useLocaleRoute } from '../composables/useLocaleRoute'

const route = useRoute()
const router = useRouter()
const { rl, loc } = useLocaleRoute()

const masters = reactive({ prefectures: [], tags: [] })
const fields = reactive({ slug:'', prefecture_id:null, city:'', lat:null, lng:null })
const t = reactive({ ja:{name:'', summary:'', slug_localized:''}, en:{name:'', summary:'', slug_localized:''} })
const selected = reactive(new Set())
const files = ref([])
const captions_ja = ref([])
const captions_en = ref([])
const coverIndex = ref(0)

const submitting = ref(false)
const error = ref('')
const done = ref(false)

function toggleTag(slug) { selected.has(slug) ? selected.delete(slug) : selected.add(slug) }
function onFiles(e){ files.value = Array.from(e.target.files || []) }

async function submit(){
  submitting.value = true; error.value=''; done.value=false
  try {
    await createPlace(loc.value, {
      fields,
      t,
      tags: Array.from(selected),
      photos: files.value,
      captions_ja: captions_ja.value,
      captions_en: captions_en.value,
      cover_index: coverIndex.value
    })
    done.value = true
    // 作成後、詳細へ飛ぶ/一覧へ戻る等
    // router.push(rl({ name:'list' }))
  } catch (e) {
    error.value = e?.response?.data?.message || e.message
  } finally {
    submitting.value = false
  }
}

onMounted(async ()=>{
  const m = await getAdminMaster(loc.value)
  masters.prefectures = m.prefectures
  masters.tags = m.tags
})
</script>
