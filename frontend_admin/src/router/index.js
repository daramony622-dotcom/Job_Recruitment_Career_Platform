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
    path: '/candidates/:id',
    name: 'CandidateDetail',
    component: () => import('../views/CandidateDetailView.vue'),
  },
  {
    path: '/job-posts/:id',
    name: 'JobPostDetail',
    component: () => import('../views/JobPostDetailView.vue'),
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
  {
    path: '/admin-resources/:resource',
    name: 'AdminResources',
    component: () => import('../views/AdminResourcesView.vue'),
  },
  {
    path: '/security',
    name: 'Security',
    component: () => import('../views/SecurityView.vue'),
  },
  {
    path: '/settings',
    name: 'Settings',
    component: () => import('../views/SettingsView.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

import { useAuth } from '../stores/auth'

router.beforeEach(async (to) => {
  const auth = useAuth()
  const urlToken = to.query.token

  if (urlToken) {
    const user = await auth.loginWithToken(urlToken)
    if (user && (user.role === 'admin' || user.is_admin)) {
      const cleanQuery = { ...to.query }
      delete cleanQuery.token
      return { path: '/dashboard', query: cleanQuery }
    }
  }

  const token = localStorage.getItem('admin_token')
  if (!to.meta.public && !token) {
    return { name: 'Login' }
  }
  if (to.meta.public && token) {
    return { path: '/' }
  }
})

export default router