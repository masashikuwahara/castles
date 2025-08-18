<template>
  <div>
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-xl font-bold">クイズ</h2>
      <div class="text-sm text-gray-600">問題はランダム出題</div>
    </div>

    <div class="flex items-center gap-4 text-sm text-gray-600">
      <span>正解: {{ qstore.correct }}</span>
      <span>不正解: {{ qstore.wrong }}</span>
      <button class="px-2 py-1 border rounded" @click="qstore.reset()">リセット</button>
    </div>

    <div v-if="loading">Loading...</div>
    <div v-else-if="error" class="p-3 bg-red-50 text-red-700 rounded">{{ error }}</div>
    <div v-else-if="!q" class="text-gray-500">問題が見つかりません。</div>
    <div v-else class="grid lg:grid-cols-5 gap-6">
      <!-- 画像＆ブラー -->
      <div class="lg:col-span-3">
        <div class="mb-3 flex items-center gap-3">
          <label class="text-sm text-gray-600">ブラー</label>
          <input type="range" min="0" max="12" v-model.number="blur" />
          <span class="text-sm text-gray-600">{{ blur }}px</span>
        </div>
        <div class="overflow-hidden rounded-xl border bg-white">
          <picture v-if="q.image">
            <source v-if="q.image.srcset?.webp" :srcset="q.image.srcset.webp" type="image/webp" />
            <img
              :src="q.image.src"
              :sizes="q.image.sizes || '(min-width:1024px) 60vw, 100vw'"
              :width="q.image.width || null"
              :height="q.image.height || null"
              :style="{ filter: `blur(${blur}px)` }"
              loading="lazy"
              decoding="async"
              alt=""
              class="w-full object-cover"
            />
          </picture>
          <div v-else class="p-6 text-sm text-gray-500">画像がありません</div>
        </div>
      </div>

      <div class="flex items-center gap-2 mb-3">
        <button class="px-3 py-1.5 border rounded"
                :disabled="blur<=0 || answered"
                @click="blur = Math.max(0, blur - 3)">
          ヒント（少しクリアに）
        </button>
        <span class="text-sm text-gray-600">現在: {{ blur }}px</span>
      </div>

      <!-- 選択肢 -->
      <div class="lg:col-span-2">
        <h3 class="font-semibold mb-2">この城はどこ？</h3>
        <div class="grid gap-2">
          <button
            v-for="c in q.choices"
            :key="c.id"
            class="px-3 py-2 rounded border text-left hover:bg-gray-50"
            :class="btnClass(c.id)"
            :disabled="answered"
            @click="answer(c.id)"
          >
            {{ c.name }}
          </button>
        </div>

        <!-- 結果 -->
        <div v-if="answered" class="mt-4 p-3 rounded border"
             :class="isCorrect ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'">
          <div class="font-semibold mb-1">{{ isCorrect ? '正解！' : '残念！' }}</div>
          <div class="text-sm">
            <span class="text-gray-700">答え：</span>
            <!-- <router-link
              class="underline"
              :to="{ name:'detail', params:{ locale: $route.params.locale, slug: explain.slug } }"
              >{{ explain.name }}</router-link> -->
            <router-link
              v-if="explain.slug"
              class="underline"
              :to="{ name:'detail', params:{ locale: loc, slug: explain.slug } }"
              >
              {{ explain.name }}
            </router-link>
          </div>
          <p v-if="explain.summary" class="text-sm mt-2 text-gray-700">{{ explain.summary }}</p>
          <button class="mt-3 px-3 py-2 rounded border" @click="next">次の問題へ</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// import { ref } from 'vue'
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { getQuiz } from '../lib/api'
import { useQuizStore } from '../stores/quiz'

const qstore = useQuizStore()

const route = useRoute()
const loc = computed(() => route.params.locale || 'ja') // ← フォールバック
const loading = ref(true)
const error = ref('')
const q = ref(null)
const explain = ref({})
const blur = ref(8)
const answered = ref(false)
const isCorrect = ref(false)

function btnClass(id) {
  // if (!answered.value) return ''
  // return id === q.value.question.correct_id
  if (!answered.value || !q.value?.correct_id) return ''
  return id === q.value.correct_id
    ? 'bg-green-600 text-white border-green-600'
    : 'opacity-60'
}

async function fetchOne() {
  loading.value = true; error.value = ''; answered.value = false; isCorrect.value = false
  try {
    // const { data } = await getQuiz(route.params.locale, { choices: 4 })
    const { data } = await getQuiz(route.params.locale, {
      choices: 4,
      exclude: qstore.seen.join(',') || undefined,
    })
    q.value = data.data.question
    explain.value = data.data.explain
    blur.value = 8
  } catch (e) {
    error.value = e?.response?.data?.message || e.message || 'Failed to load'
  } finally {
    loading.value = false
  }
}

function answer(id) {
  answered.value = true
  isCorrect.value = (id === q.value.correct_id)
  blur.value = 0 // 正解/不正解後はクリア表示
  qstore.mark(isCorrect.value)
  qstore.push(q.value.place_id)
}

function next() {
  fetchOne()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

fetchOne()
</script>
