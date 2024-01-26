import { createRouter, createWebHistory  } from 'vue-router'
import Style from '@/views/StyleView.vue'
import Home from '@/views/HomeView.vue'
import Add from '@/views/FormsView.vue'
import Edit from '@/views/FormsView.vue'

const routes = [
  {
    meta: {
      title: 'Select style'
    },
    path: '/',
    name: 'style',
    component: Style
  },
  {
    meta: {
      title: 'Dashboard'
    },
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/TablesView.vue')
  },
  {
    meta: {
      title: 'Add'
    },
    path: '/add',
    name: 'add',
    component: () => import('@/views/FormsView.vue')
  },
  {
    meta: {
      title: 'Update'
    },
    path: '/update/:id',
    name: 'update',
    component: () => import('@/views/FormsView.vue')
  },
  {
    meta: {
      title: 'Login'
    },
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginView.vue')
  },
  {
    meta: {
      title: '404'
    },
    path: '/:pathMatch(.*)*',
    component: () => import('@/views/ErrorView.vue')
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { top: 0 }
  }
})

export default router
