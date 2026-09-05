import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
    meta: { public: true },
  },
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('../views/DashboardView.vue'),
  },
  {
    path: '/job-posts',
    name: 'JobPosts',
    component: () => import('../views/JobPostsView.vue'),
  },
  {
    path: '/candidates',
    name: 'Candidates',
    component: () => import('../views/CandidatesView.vue'),
  },
  {
    path: '/reports',
    name: 'Reports',
    component: () => import('../views/ReportsView.vue'),
  },
  {
    path: '/companies',
    name: 'Companies',
    component: () => import('../views/CompaniesView.vue'),
  },
  {
    path: '/companies/:id',
    name: 'CompanyDetail',
    component: () => import('../views/CompanyDetailView.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const token = localStorage.getItem('admin_token')
  if (!to.meta.public && !token) {
    return { name: 'Login' }
  }
  if (to.meta.public && token) {
    return { path: '/' }
  }
})

export default router