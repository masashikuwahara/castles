import { createRouter, createWebHistory } from 'vue-router'

const PlaceList = () => import('../pages/PlaceList.vue')
const PlaceDetail = () => import('../pages/PlaceDetail.vue')

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/ja/places' },
    { name: 'list', path: '/:locale(ja|en)/places', component: PlaceList },
    { name: 'detail', path: '/:locale(ja|en)/places/:slug', component: PlaceDetail },
  ],
})

router.beforeEach((to, from, next) => {
  if (!to.params.locale) return next({ ...to, params: { ...to.params, locale: 'ja' } })
  next()
})

export default router
