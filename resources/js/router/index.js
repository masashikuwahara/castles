import { createRouter, createWebHistory } from 'vue-router'

const PlaceList   = () => import('../pages/PlaceList.vue')
const PlaceDetail = () => import('../pages/PlaceDetail.vue')

const toList = (locale, query) => ({ name: 'list', params: { locale }, query })

const QuizPage = () => import('../pages/QuizPage.vue')

const CulturalList = () => import('../pages/CulturalList.vue')
const CulturalDetail = () => import('../pages/CulturalDetail.vue')

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/ja/places' },

    // 一覧・詳細
    { name: 'list',   path: '/:locale(ja|en)/places',            component: PlaceList },
    { name: 'detail', path: '/:locale(ja|en)/places/:slug',      component: PlaceDetail },

    // 100名城/続100/その他 → 一覧へクエリ付きリダイレクト
    { name:'castles-top100',
      path:'/:locale(ja|en)/castles/top100',
      redirect: to => ({ name:'list', params:{ locale: to.params.locale }, query:{ type:'castle', top100:1 } })
    },
    { name:'castles-top100c',
      path:'/:locale(ja|en)/castles/top100-continued',
      redirect: to => ({ name:'list', params:{ locale: to.params.locale }, query:{ type:'castle', top100c:1 } })
    },
    { name:'castles-others',
      path:'/:locale(ja|en)/castles/others',
      redirect: to => ({ name:'list', params:{ locale: to.params.locale }, query:{ type:'castle', others:1 } })
    },

    { name: 'cultural-list', path: '/:locale(ja|en)/cultural', component: CulturalList },

    // 文化財の“別ページ”詳細
    { name: 'cultural-detail', path: '/:locale(ja|en)/cultural/:slug', component: CulturalDetail },
    
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
    
    {
      path: '/:locale/admin',
      component: () => import('../admin/AdminLayout.vue'),
      children: [
        { path: '', name: 'admin-home', component: () => import('../admin/AdminHome.vue') },
        { path: 'places/new', name: 'admin-place-new', component: () => import('../admin/PlaceNew.vue') },
        { path: 'culturals/new', name: 'admin-cultural-new', component: () => import('../admin/CulturalNew.vue') },
      ]
    }
  ],
})

router.beforeEach((to, from, next) => {
  const needsLocale =
    to.matched.some(m => /\/:locale(\(|$)/.test(m.path)) &&
    (typeof to.params.locale !== 'string' || !to.params.locale)

  if (needsLocale) {
    const fallback =
      (typeof from.params.locale === 'string' && from.params.locale) ||
      (navigator.language && navigator.language.startsWith('en') ? 'en' : 'ja')

    return next({
      ...to,
      params: { ...to.params, locale: fallback },
      replace: true,
    })
  }
  next()
})

router.beforeEach((to, from, next) => {
  if (!to.params.locale) return next({ ...to, params: { ...to.params, locale: 'ja' } })
  next()
})

export default router
