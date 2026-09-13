import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../stores/auth'

const isAdminUser = (user) => Boolean(user && (['admin', 'hr', 'company'].includes(user.role) || user.is_admin))
const isCompanyScopedUser = (user) => Boolean(user && ['hr', 'company'].includes(user.role))

const isAllowedAdminRoute = (user, path) => {
  if (!isCompanyScopedUser(user)) {
    return true
  }

  const allowedPrefixes = [
    '/dashboard', 
    '/job-posts', 
    '/admin-resources/applications', 
    '/admin-resources/interviews', 
    '/admin-resources/categories', 
    '/admin-resources/skill-categories', 
    '/admin-resources/skills', 
    '/reports',
    '/settings'
  ]
  return allowedPrefixes.some((prefix) => path === prefix || path.startsWith(`${prefix}/`))
}

const routes = [
  {
    path: '/login',
    name: 'Login',
    // Fallback view or explicit redirect logic
    redirect: () => {
      const clientUrl = (import.meta.env.VITE_CLIENT_URL || 'http://localhost:5173').replace(/\/$/, '')
      return `${clientUrl}/login?redirect=admin`
    },
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
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const auth = useAuth()
  const urlToken = to.query.token
  const clientUrl = (import.meta.env.VITE_CLIENT_URL || 'http://localhost:5173').replace(/\/$/, '')
  const clientLoginUrl = `${clientUrl}/login?redirect=admin`

  // 1. SSO Handshake: Token passed via URL parameter from frontend_user
  if (urlToken) {
    try {
      const user = await auth.loginWithToken(urlToken)
      if (isAdminUser(user)) {
        // Strip token query param cleanly from the address bar
        const cleanQuery = { ...to.query }
        delete cleanQuery.token

        return {
          path: to.path === '/login' ? '/dashboard' : to.path,
          query: Object.keys(cleanQuery).length ? cleanQuery : undefined,
          replace: true,
        }
      }
    } catch {
      // Fall through on token failure
    }

    auth.logout()
    window.location.href = `${clientUrl}/login?error=unauthorized`
    return false
  }

  // 2. Allow direct bypass for non-protected paths or external redirects
  if (to.path === '/login') {
    return true
  }

  // 3. Retrieve stored token
  const token = localStorage.getItem('admin_token') || 
                localStorage.getItem('job_platform_token') || 
                localStorage.getItem('auth_token')

  if (!token) {
    const currentRedirect = new URLSearchParams(window.location.search).get('redirect')
    if (currentRedirect !== 'admin') {
      window.location.assign(`${clientLoginUrl}&from=admin`)
    }
    return false
  }

  // 4. Validate user session & permissions
  let user = auth.user?.value || auth.user

  if (!user) {
    try {
      user = await auth.loginWithToken(token)
    } catch {
      auth.logout()
      window.location.href = clientLoginUrl
      return false
    }
  }

  // Verify Role & Authorization
  if (!isAdminUser(user)) {
    auth.logout()
    window.location.href = `${clientUrl}/login?error=unauthorized`
    return false
  }

  // Enforce company scope route restrictions (HR / Company Role)
  if (!isAllowedAdminRoute(user, to.path)) {
    return { path: '/dashboard', replace: true }
  }
})

export default router