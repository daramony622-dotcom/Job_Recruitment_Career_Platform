import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const isAdminUser = (user) =>
  Boolean(user && (['admin', 'hr', 'company'].includes(String(user.role || '').toLowerCase()) || user.is_admin === true || user.is_admin === 1))

const isCompanyScopedUser = (user) => Boolean(user && ['hr', 'company'].includes(String(user.role || '').toLowerCase()))

const isAllowedAdminRoute = (user, path) => {
  if (!isCompanyScopedUser(user)) return true

  if (String(user.role || '').toLowerCase() === 'hr' && path.startsWith('/admin-companies')) {
    return false
  }

  const allowedPrefixes = [
    '/dashboard',
    '/admin/dashboard',
    '/admin',
    '/job-posts',
    '/admin-resources/applications',
    '/admin-resources/interviews',
    '/admin-resources/categories',
    '/admin-resources/skill-categories',
    '/admin-resources/skills',
    '/reports',
    '/settings',
  ]

  if (String(user.role || '').toLowerCase() === 'hr') {
    allowedPrefixes.push('/candidates')
  }

  return allowedPrefixes.some((prefix) => path === prefix || path.startsWith(`${prefix}/`))
}

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('../client/HomeView.vue'),
  },
  {
    path: '/jobs',
    name: 'Jobs',
    component: () => import('../client/JobsView.vue'),
  },
  {
    path: '/jobs/:id',
    name: 'JobDetail',
    component: () => import('../client/JobDetailView.vue'),
  },
  {
    path: '/companies',
    name: 'Companies',
    component: () => import('../client/CompaniesView.vue'),
  },
  {
    path: '/companies/:id',
    name: 'CompanyDetail',
    component: () => import('../client/CompanyDetailView.vue'),
  },
  {
    path: '/about',
    name: 'About',
    component: () => import('../client/AboutView.vue'),
  },
  {
    path: '/contact',
    name: 'Contact',
    component: () => import('../client/ContactView.vue'),
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('../client/ProfileView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/notifications',
    name: 'Notifications',
    component: () => import('../client/NotificationView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/dashboard',
    alias: ['/dashboard', '/admin', '/company/dashboard', '/user/dashboard'],
    name: 'Dashboard',
    component: () => import('../admin/DashboardView.vue'),
    meta: { admin: true },
  },

  {
    path: '/job-posts',
    name: 'AdminJobPosts',
    component: () => import('../admin/JobPostsView.vue'),
    meta: { admin: true },
  },
  {
    path: '/candidates',
    name: 'Candidates',
    component: () => import('../admin/CandidatesView.vue'),
    meta: { admin: true },
  },
  {
    path: '/candidates/:id',
    name: 'CandidateDetail',
    component: () => import('../admin/CandidateDetailView.vue'),
    meta: { admin: true },
  },
  {
    path: '/job-posts/:id',
    name: 'AdminJobPostDetail',
    component: () => import('../admin/JobPostDetailView.vue'),
    meta: { admin: true },
  },
  {
    path: '/reports',
    name: 'Reports',
    component: () => import('../admin/ReportsView.vue'),
    meta: { admin: true },
  },
  {
    path: '/admin-companies',
    name: 'AdminCompanies',
    component: () => import('../admin/AdminCompaniesView.vue'),
    meta: { admin: true },
  },
  {
    path: '/admin-companies/:id',
    name: 'AdminCompanyDetail',
    component: () => import('../admin/AdminCompanyDetailView.vue'),
    meta: { admin: true },
  },
  {
    path: '/admin-resources/:resource',
    name: 'AdminResources',
    component: () => import('../admin/AdminResourcesView.vue'),
    meta: { admin: true },
  },
  {
    path: '/security',
    name: 'Security',
    component: () => import('../admin/SecurityView.vue'),
    meta: { admin: true },
  },
  {
    path: '/settings',
    name: 'Settings',
    component: () => import('../admin/SettingsView.vue'),
    meta: { admin: true },
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../client/LoginView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../client/RegisterView.vue'),
    meta: { guestOnly: true },
  },
  
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('../client/ForgotPasswordView.vue'),
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

  // 1. Handle OAuth token callback in query parameter (?token=...)
  if (to.query.token) {
    const user = await auth.loginWithToken(to.query.token)

    if (isAdminUser(user)) {
      return { path: '/dashboard', replace: true }
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

  // 3. Prevent logged-in users from visiting guest-only routes (/login, /register, /forgot-password)
  if (to.meta.guestOnly && auth.isAuthenticated.value) {
    if (isAdminUser(currentUser)) {
      return { path: '/dashboard', replace: true }
    }
    return { path: '/', replace: true }
  }

  // 4. Protect admin workspace routes and enforce HR/company scope.
  if (to.meta.admin) {
    if (!auth.isAuthenticated.value || !currentUser) {
      return { path: '/login', query: { redirect: to.fullPath } }
    }
    if (!isAdminUser(currentUser)) {
      return { path: '/', replace: true }
    }
    if (!isAllowedAdminRoute(currentUser, to.path)) {
      return { path: '/dashboard', replace: true }
    }
  }

  // 5. Enforce auth-protected routes (e.g. /profile, /notifications)
  if (to.meta.requiresAuth && !auth.isAuthenticated.value) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }
})


export default router