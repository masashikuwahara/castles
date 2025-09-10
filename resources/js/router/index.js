import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const PlaceList   = () => import('../pages/PlaceList.vue')
const PlaceDetail = () => import('../pages/PlaceDetail.vue')
const QuizPage = () => import('../pages/QuizPage.vue')
const CulturalList = () => import('../pages/CulturalList.vue')
const CulturalDetail = () => import('../pages/CulturalDetail.vue')

const AdminLayout   = () => import('../admin/AdminLayout.vue')
const AdminHome     = () => import('../admin/AdminHome.vue')
const AdminPlaceNew = () => import('../admin/PlaceNew.vue')
const AdminCultNew  = () => import('../admin/CulturalNew.vue')
const AdminLogin    = () => import('../admin/AdminLogin.vue')

const toList = (locale, query) => ({ name: 'list', params: { locale }, query })

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

    //文化財
    { name: 'cultural-list', path: '/:locale(ja|en)/cultural', component: CulturalList },
    { name: 'cultural-detail', path: '/:locale(ja|en)/cultural/:slug', component: CulturalDetail },

    //クイズ
    { path: '/:locale(ja|en)/quiz', name: 'quiz', component: QuizPage },

    // ★ タグページ
    { name: 'tags-index', path: '/:locale(ja|en)/tags', component: () => import('../pages/TagsIndex.vue') },
    { name: 'tag', path: '/:locale(ja|en)/tags/:slug',
      redirect: to => toList(to.params.locale, { tags: to.params.slug }) },
    
    //ログイン
    {
      path: '/admin',
      component: AdminLayout,
      meta: { requiresAuth: true },
      children: [
        { path: '', name: 'admin-home', component: () => import('../admin/AdminHome.vue') },
        { path: 'places/new', name: 'admin-place-new', component: () => import('../admin/PlaceNew.vue') },
        { path: 'culturals/new', name: 'admin-cultural-new', component: () => import('../admin/CulturalNew.vue') },
      ],
    },
    { path: '/admin/login', name: 'admin-login', component: () => import('../admin/AdminLogin.vue') },
  ],
  scrollBehavior (to, from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  }
})

router.beforeEach((to, from, next) => {
  const needsLocale =
    to.matched.some(m => /\/:locale(\(|$)/.test(m.path)) &&
    (typeof to.params.locale !== 'string' || !to.params.locale)

  if (needsLocale) {
    const fallback =
      (typeof from.params.locale === 'string' && from.params.locale) ||
      (navigator.language && navigator.language.startsWith('en') ? 'en' : 'ja')

    next({
      ...to,
      params: { ...to.params, locale: fallback },
      replace: true,
    })
  } else {
    next()
  }
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  if (!auth.bootstrapDone) { await auth.bootstrap() }

  if (to.meta.requiresAuth && !auth.user) {
    return next({ name: 'admin-login', query: { redirect: to.fullPath } })
  }
  next()
})
export default router
