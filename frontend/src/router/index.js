import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const routes = [
  {
    path: '/pharmacovigilance/login',
    name: 'login',
    component: () => import('@/views/LoginView.vue'),
    meta: { requiresGuest: true },
  },
  {
    path: '/pharmacovigilance',
    component: () => import('@/components/layout/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: 'search',
        name: 'search',
        component: () => import('@/views/SearchView.vue'),
      },
      {
        path: 'orders',
        name: 'orders',
        component: () => import('@/views/OrdersView.vue'),
      },
      {
        path: 'orders/:id',
        name: 'order-detail',
        component: () => import('@/views/OrderDetailView.vue'),
        props: true,
      },
      {
        path: 'customers/:id',
        name: 'customer-detail',
        component: () => import('@/views/CustomerDetailView.vue'),
        props: true,
      },
      {
        path: 'alerts',
        name: 'alerts',
        component: () => import('@/views/AlertHistoryView.vue'),
      },
      {
        path: 'audit-logs',
        name: 'audit-logs',
        component: () => import('@/views/AuditLogView.vue'),
        meta: { requiresAdmin: true },
      },
    ],
  },
  // Redirect root to search
  {
    path: '/',
    redirect: '/pharmacovigilance/search',
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/pharmacovigilance/login',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guard — redirect to login if not authenticated
router.beforeEach((to) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    return { name: 'search' }
  }

  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return { name: 'search' }
  }
})

export default router
