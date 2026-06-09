export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const baseURL = config.public.apiBase

  let csrfPromise: Promise<void> | null = null

  const readXsrfToken = () => {
    if (!import.meta.client) return null
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/)
    return match ? decodeURIComponent(match[1]) : null
  }

  const ensureCsrfCookie = async () => {
    if (!import.meta.client) return
    if (readXsrfToken()) return
    if (!csrfPromise) {
      csrfPromise = $fetch('/sanctum/csrf-cookie', {
        baseURL,
        credentials: 'include',
      })
        .then(() => undefined)
        .finally(() => {
          csrfPromise = null
        })
    }
    await csrfPromise
  }

  const api = $fetch.create({
    baseURL,
    credentials: 'include',
    onRequest: async ({ options }) => {
      const method = (options.method || 'GET').toUpperCase()
      if (!['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) return

      await ensureCsrfCookie()
      const token = readXsrfToken()
      if (!token) return

      if (options.headers instanceof Headers) {
        options.headers.set('X-XSRF-TOKEN', token)
      } else {
        options.headers = {
          ...(options.headers || {}),
          'X-XSRF-TOKEN': token,
        }
      }
    },
  })

  return {
    provide: { api },
  }
})
