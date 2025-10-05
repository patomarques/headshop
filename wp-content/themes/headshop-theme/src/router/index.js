import { createRouter, createWebHistory } from 'vue-router'

const HomeView = () => import('@/views/HomeView.vue')
const ProductsView = () => import('@/views/ProductsView.vue')
const CategoriesView = () => import('@/views/CategoriesView.vue')
const ProductDetailView = () => import('@/views/ProductDetailView.vue')

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/produtos', name: 'products', component: ProductsView },
    { path: '/categorias', name: 'categories', component: CategoriesView },
    { path: '/produto/:id', name: 'product-detail', component: ProductDetailView, props: true },
  ],
  scrollBehavior() {
    return { top: 0 }
  }
})

export default router


