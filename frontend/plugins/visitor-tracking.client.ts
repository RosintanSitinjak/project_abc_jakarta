/**
 * Client-side plugin that fires a single POST to /api/public/visitor
 * on every page load to record the visit.
 */
export default defineNuxtPlugin(() => {
  if (!import.meta.client) return

  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase?.replace(/\/$/, '') ?? ''

  $fetch(`${apiBase}/api/public/visitor`, {
    method: 'POST',
    credentials: 'include',
  }).catch(() => {
    // Silently ignore – visitor tracking is best-effort
  })
})
