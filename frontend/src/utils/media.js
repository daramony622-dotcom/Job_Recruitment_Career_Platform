const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
const apiOrigin = apiUrl.replace(/\/api\/?$/, '')

export function resolveMediaUrl(value) {
  if (!value) return ''
  if (/^(https?:|blob:|data:)/i.test(value)) return value

  const path = String(value).replace(/^\/+/, '')

  // Already a full storage path
  if (path.startsWith('storage/')) return `${apiOrigin}/${path}`

  // Company logo / cover stored in storage/companies/...
  if (path.startsWith('companies/')) return `${apiOrigin}/storage/${path}`

  return `${apiOrigin}/storage/${path}`
}

export function profileImage(userOrProfile) {
  if (!userOrProfile) return ''
  const profile = userOrProfile?.profile || userOrProfile || {}
  const raw =
    profile.avatar_url ||
    profile.avatar ||
    userOrProfile?.avatar_url ||
    userOrProfile?.avatar ||
    userOrProfile?.telegram_photo ||
    profile.telegram_photo
  return resolveMediaUrl(raw)
}

