<template>
  <div>
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-xl font-bold">クイズ</h2>
      <div class="text-sm text-gray-600">問題はランダム出題</div>
    </div>

    <!-- 進捗/スコア -->
    <div class="flex items-center gap-4 text-sm text-gray-600 mb-2">
      <span>Q {{ currentNo }} / {{ qstore.total }}</span>
      <span>正解: {{ qstore.correct }}</span>
      <span>不正解: {{ qstore.wrong }}</span>
      <button class="px-2 py-1 border rounded" @click="restart(qstore.total)">リセット</button>
      <!-- 10問へのショートカット（任意） -->
      <!-- <button class="px-2 py-1 border rounded" @click="restart(10)">10問にする</button> -->
    </div>
    
    <div v-if="loading">Loading...</div>
    <div v-else-if="error" class="p-3 bg-red-50 text-red-700 rounded">{{ error }}</div>

    <!-- 結果画面 -->
    <div v-else-if="finished" class="p-6 border rounded-xl bg-white max-w-xl">
      <h3 class="text-lg font-bold mb-3">結果</h3>
      <p class="mb-2">全 {{ qstore.total }} 問中 {{ qstore.correct }} 正解</p>
      <p class="mb-4">正解率：{{ Math.round((qstore.correct / qstore.total) * 100) }}%</p>
      <div class="flex gap-2">
        <button class="px-3 py-2 border rounded" @click="restart(qstore.total)">同じ問数でもう一度</button>
        <button class="px-3 py-2 border rounded" @click="restart(10)">10問で挑戦</button>
        <router-link class="px-3 py-2 border rounded inline-block"
          :to="{ name:'list', params:{ locale: loc }, query:{} }">一覧へ戻る</router-link>
      </div>
    </div>
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
            <router-link
              v-if="explain.slug"
              class="underline"
              :to="{ name:'detail', params:{ locale: loc, slug: explain.slug } }"
              >
              {{ explain.name }}
            </router-link>
          </div>
          <p v-if="explain.summary" class="text-sm mt-2 text-gray-700">{{ explain.summary }}</p>
          <button class="mt-3 px-3 py-2 rounded border" @click="goNext">次の問題へ</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getQuiz } from '../lib/api'
import { useQuizStore } from '../stores/quiz'

const qstore = useQuizStore()
const route = useRoute()
const router = useRouter()
const loc = computed(() => route.params.locale || 'ja')

// 出題数：?count= のクエリで変更可能（無ければ5）
const targetCount = computed(() => {
  const n = Number(route.query.count || 5)
  return Number.isFinite(n) && n > 0 ? Math.min(n, 50) : 5 // 安全のため上限50
})

const loading = ref(true)
const error = ref('')
const q = ref(null)
const explain = ref({})
const blur = ref(8)
const answered = ref(false)
const isCorrect = ref(false)
const selectedId = ref(null)
const nextQ = ref(null)

const currentNo = computed(() =>
  Math.min(qstore.answered + (answered.value ? 0 : 1), qstore.total)
)

const finished = computed(() => qstore.finished)

function btnClass(id) {
  if (!answered.value || !q.value?.correct_id) return ''
  if (id === q.value.correct_id) return 'bg-green-600 text-white border-green-600'
  if (id === selectedId.value)  return 'bg-red-600 text-white border-red-600'
  return 'opacity-60'
}

async function fetchOne() {
  if (finished.value) return
  loading.value = true; error.value = ''; answered.value = false; isCorrect.value = false
  try {
    const params = { choices: 4, exclude: qstore.seen.join(',') || undefined }
    let { data } = await getQuiz(loc.value, params)
    q.value = data.data.question
    explain.value = data.data.explain
    blur.value = 8; nextQ.value = null; selectedId.value = null
  } catch (e) {
    // データ不足で exclude しすぎ → exclude無しでリトライ
    if (e?.response?.data?.message === 'No quiz data') {
      try {
        const { data } = await getQuiz(loc.value, { choices: 4 })
        q.value = data.data.question
        explain.value = data.data.explain
      } catch (ee) {
        error.value = ee?.response?.data?.message || ee.message || 'Failed to load'
      }
    } else {
      error.value = e?.response?.data?.message || e.message || 'Failed to load'
    }
  } finally {
    loading.value = false
  }
}

function answer(id) {
  if (answered.value || finished.value) return
  answered.value = true
  isCorrect.value = (id === q.value.correct_id)
  selectedId.value = id
  blur.value = 0
  qstore.mark(isCorrect.value)
  qstore.push(q.value.place_id)

  // 先読み（残りがあれば）
  if (!qstore.finished) {
    getQuiz(loc.value, {
      choices: 4,
      exclude: qstore.seen.join(',') || undefined,
    }).then(({ data }) => { nextQ.value = data.data }).catch(() => {})
  }
}

function onKey(e) {
  if (!q.value) return
  const max = q.value.choices?.length || 0
  if (!answered.value && e.key >= '1' && e.key <= String(max)) {
    const idx = Number(e.key) - 1
    const id = q.value.choices[idx]?.id
    if (id) answer(id)
  } else if (answered.value && e.key === 'Enter' && !finished.value) {
    goNext()
  }
}

onMounted(() => {
  // ページ入場時に新セッションを開始（常にtargetCountで開始）
  qstore.start(targetCount.value)
  window.addEventListener('keydown', onKey)
  fetchOne()
})
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))

async function goNext() {
  if (finished.value) return
  if (nextQ.value?.question) {
    const n = nextQ.value
    q.value = n.question
    explain.value = n.explain
    answered.value = false
    isCorrect.value = false
    selectedId.value = null
    blur.value = 8
    nextQ.value = null
    // 次を先読み
    getQuiz(loc.value, {
      choices: 4,
      exclude: qstore.seen.join(',') || undefined,
    }).then(({ data }) => { nextQ.value = data.data }).catch(() => {})
  } else {
    await fetchOne()
  }
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function restart(n = qstore.total) {
  qstore.start(n)
  // URLのcountを更新（セッション開始数をURLに反映）
  router.replace({ name: 'quiz', params: { locale: loc.value }, query: { count: n } })
  q.value = null; explain.value = {}; nextQ.value = null; selectedId.value = null
  answered.value = false; isCorrect.value = false; blur.value = 8
  fetchOne()
}
</script>