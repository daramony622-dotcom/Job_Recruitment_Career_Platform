const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
const apiOrigin = apiUrl.replace(/\/api\/?$/, '')

export function resolveMediaUrl(value) {
  if (!value) return ''
  if (/^https?:\/\//i.test(value)) return value

  const path = String(value).replace(/^\/+/, '')
  if (path.startsWith('storage/')) return `${apiOrigin}/${path}`
  return `${apiOrigin}/storage/${path}`
}

export function profileImage(userOrProfile) {
  const profile = userOrProfile?.profile || userOrProfile || {}
  return resolveMediaUrl(
    profile.avatar_url || profile.avatar || userOrProfile?.avatar_url || userOrProfile?.avatar,
  )
}
