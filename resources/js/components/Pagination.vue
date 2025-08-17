<template>
  <nav v-if="show" class="flex items-center gap-1 select-none" aria-label="Pagination">
    <!-- Prev -->
    <button
      class="px-3 py-1.5 rounded border text-sm disabled:opacity-50"
      :disabled="page <= 1"
      @click="go(page - 1)"
      :aria-disabled="page <= 1"
    >
      {{ t('prev') }}
    </button>

    <!-- Pages (with ellipsis) -->
    <template v-for="(item, idx) in items" :key="idx">
      <button
        v-if="item.type === 'page'"
        class="px-3 py-1.5 rounded border text-sm"
        :class="item.n === page ? 'bg-gray-900 text-white border-gray-900' : 'hover:bg-gray-50'"
        :aria-current="item.n === page ? 'page' : undefined"
        @click="go(item.n)"
      >
        {{ item.n }}
      </button>
      <span v-else class="px-2 text-sm text-gray-500">…</span>
    </template>

    <!-- Next -->
    <button
      class="px-3 py-1.5 rounded border text-sm disabled:opacity-50"
      :disabled="page >= last"
      @click="go(page + 1)"
      :aria-disabled="page >= last"
    >
      {{ t('next') }}
    </button>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

// Props: Laravelのpaginateが返すmetaを含むオブジェクト想定
const props = defineProps({
  pagination: { type: Object, required: true },
  // 何ページ分を前後に見せるか（例: 2 → 現在±2）
  radius: { type: Number, default: 2 },
})

const emit = defineEmits(['change'])
const { locale } = useI18n()

// i18n（簡易）
const t = (k) => {
  const dict = {
    ja: { prev: '前へ', next: '次へ' },
    en: { prev: 'Prev', next: 'Next' },
  }
  return dict[locale.value]?.[k] ?? k
}

const page = computed(() => Number(props.pagination?.current_page || 1))
const last = computed(() => Number(props.pagination?.last_page || 1))
const show = computed(() => last.value > 1)

const items = computed(() => {
  const cur = page.value
  const end = last.value
  const r = props.radius

  // 範囲を作成（1, …, window, …, last）
  const pages = new Set([1, end])
  for (let i = cur - r; i <= cur + r; i++) {
    if (i >= 1 && i <= end) pages.add(i)
  }
  const sorted = Array.from(pages).sort((a, b) => a - b)

  // 隣接していない箇所に省略記号
  const out = []
  for (let i = 0; i < sorted.length; i++) {
    const n = sorted[i]
    const prev = sorted[i - 1]
    if (i > 0 && n - prev > 1) out.push({ type: 'ellipsis' })
    out.push({ type: 'page', n })
  }
  return out
})

function go(n) {
  if (n < 1 || n > last.value || n === page.value) return
  emit('change', n)
}
</script>
