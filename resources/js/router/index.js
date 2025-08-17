import { createRouter, createWebHistory } from 'vue-router'

const PlaceList   = () => import('../pages/PlaceList.vue')
const PlaceDetail = () => import('../pages/PlaceDetail.vue')

const toList = (locale, query) => ({ name: 'list', params: { locale }, query })

const QuizPage = () => import('../pages/QuizPage.vue')

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/ja/places' },

    // 一覧・詳細
    { name: 'list',   path: '/:locale(ja|en)/places',            component: PlaceList },
    { name: 'detail', path: '/:locale(ja|en)/places/:slug',      component: PlaceDetail },

    // ★ プレフィルタ直リンク（→ 一覧にクエリ付きでリダイレクト）
    { path: '/:locale(ja|en)/castles/top100',
      redirect: to => toList(to.params.locale, { type: 'castle', top100: 1 }) },
    { path: '/:locale(ja|en)/castles/top100-continued',
      redirect: to => toList(to.params.locale, { type: 'castle', top100c: 1 }) },
    { path: '/:locale(ja|en)/castles/others',
      redirect: to => toList(to.params.locale, { others: 1 }) },
    { path: '/:locale(ja|en)/cultural-properties',
      redirect: to => toList(to.params.locale, { type: 'cultural_property' }) },
    { path: '/:locale(ja|en)/quiz', name: 'quiz', component: QuizPage },

    // ★ タグページ
    { name: 'tags-index', path: '/:locale(ja|en)/tags', component: () => import('../pages/TagsIndex.vue') },
    { name: 'tag', path: '/:locale(ja|en)/tags/:slug',
      redirect: to => toList(to.params.locale, { tags: to.params.slug }) },
  ],
})

router.beforeEach((to, from, next) => {
  if (!to.params.locale) return next({ ...to, params: { ...to.params, locale: 'ja' } })
  next()
})

export default router
