import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const isAdminUser = (user) =>
  Boolean(user && (['admin', 'hr', 'company'].includes(user.role) || user.is_admin))

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/HomeView.vue'),
  },
  {
    path: '/jobs',
    name: 'Jobs',
    component: () => import('../views/JobsView.vue'),
  },
  {
    path: '/jobs/:id',
    name: 'JobDetail',
    component: () => import('../views/JobDetailView.vue'),
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
    path: '/about',
    name: 'About',
    component: () => import('../views/AboutView.vue'),
  },
  {
    path: '/contact',
    name: 'Contact',
    component: () => import('../views/ContactView.vue'),
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('../views/ProfileView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/notifications',
    name: 'Notifications',
    component: () => import('../views/NotificationView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/RegisterView.vue'),
    meta: { guestOnly: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach(async (to) => {
  const auth = useAuth()
  const adminUrl = (import.meta.env.VITE_ADMIN_URL || 'http://localhost:5174').replace(/\/$/, '')

  // 1. Handle OAuth token callback in query parameter (?token=...)
  if (to.query.token) {
    const user = await auth.loginWithToken(to.query.token)

    if (isAdminUser(user)) {
      const target = new URL('/dashboard', adminUrl)
      target.searchParams.set('token', to.query.token)
      window.location.assign(target.toString())
      return false
    }

    // Clean query parameters from URL for candidate user
    const cleanQuery = { ...to.query }
    delete cleanQuery.token
    return { path: to.path, query: Object.keys(cleanQuery).length ? cleanQuery : undefined, replace: true }
  }

  // 2. Fetch current user if token exists in storage but user state is uninitialized
  if (auth.token.value && !auth.user.value) {
    await auth.fetchCurrentUser()
  }

  const currentUser = auth.user.value

  // 3. Cross-app redirection for Admin/HR users attempting to access client login or protected routes
  if (currentUser && isAdminUser(currentUser)) {
    if (to.query.redirect === 'admin' || to.meta.guestOnly) {
      const target = new URL('/dashboard', adminUrl)
      if (auth.token.value) {
        target.searchParams.set('token', auth.token.value)
      }
      window.location.assign(target.toString())
      return false
    }
  }

  // 4. Enforce guest-only routes (e.g. prevent logged-in candidates from opening /login or /register)
  if (to.meta.guestOnly && auth.isAuthenticated.value) {
    return { path: '/' }
  }

  // 5. Enforce auth-protected routes (e.g. /profile, /notifications)
  if (to.meta.requiresAuth && !auth.isAuthenticated.value) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }
})

export default router