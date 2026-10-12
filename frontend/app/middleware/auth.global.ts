export default defineNuxtRouteMiddleware(async (to) => {
  if (!to.path.startsWith('/admin')) return

  const authUser = useState<unknown | null>('auth-user', () => null)
  const authChecked = useState<boolean>('auth-checked', () => false)

  if (authChecked.value) {
    if (!authUser.value) {
      return navigateTo('/login')
    }
    return
  }

  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase.replace(/\/$/, '')

  try {
    if (import.meta.server) {
      // SSR: forward the browser cookies to the API
      // NOTE: Do NOT forward 'host' — it would send the frontend host (lamsolusi.com)
      // to the backend (lamsolusi.com:8089) causing session/routing issues.
      const headers = useRequestHeaders(['cookie', 'user-agent']) as Record<string, string>
      const reqHeaders = useRequestHeaders(['referer', 'origin', 'host']) as Record<string, string>
      
      const fallbackReferer = reqHeaders.referer || reqHeaders.origin || (reqHeaders.host ? `https://${reqHeaders.host}` : 'http://localhost:3000')
      
      const user = await $fetch(`${apiBase}/api/user`, {
        headers: {
          ...headers,
          referer: fallbackReferer,
          accept: 'application/json',
        },
      })
      authUser.value = user
    } else {
      // Client-side: use credentials (browser sends cookies automatically)
      const user = await $fetch(`${apiBase}/api/user`, {
        credentials: 'include',
        headers: { accept: 'application/json' },
      })
      authUser.value = user
    }
  } catch {
    authUser.value = null
  } finally {
    authChecked.value = true
  }

  if (!authUser.value) {
    return navigateTo('/login')
  }
})
