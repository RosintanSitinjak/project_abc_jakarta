export type PaginatedResponse<T> = {
  data: T[]
}

type FetchOptions<T> = Parameters<typeof $fetch<T>>[1]

type AttachmentUploadOptions = {
  attachmentableType: string
  attachmentableId?: string
  type?: string
  remark?: string
}

type AttachmentApi = {
  id: string
  name: string
  path: string
}

let csrfPromise: Promise<void> | null = null

export const useApi = () => {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase.replace(/\/$/, '')

  const getXsrfToken = () => {
    if (process.server) return null
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/)
    if (!match || !match[1]) return null
    return decodeURIComponent(match[1])
  }

  const hasSessionCookie = () => {
    if (process.server) return false
    const cookieNames = document.cookie
      .split(';')
      .map((cookie) => cookie.trim().split('=')[0])
      .filter(Boolean)
    return cookieNames.some((name) => name && (name.endsWith('-session') || name.endsWith('_session')))
  }

  const ensureCsrf = async () => {
    if (process.server) return
    if (getXsrfToken() && hasSessionCookie()) return
    if (!csrfPromise) {
      csrfPromise = $fetch(`${apiBase}/sanctum/csrf-cookie`, {
        credentials: 'include',
      })
        .then(() => undefined)
        .finally(() => {
          csrfPromise = null
        })
    }
    await csrfPromise
  }

  const apiFetch = async <T>(path: string, options: FetchOptions<T> = {}) => {
    const method = (options.method ?? 'GET').toString().toUpperCase()
    if (!['GET', 'HEAD', 'OPTIONS'].includes(method)) {
      await ensureCsrf()
    }

    const headers = new Headers(options.headers as HeadersInit | undefined)
    const xsrfToken = getXsrfToken()
    if (xsrfToken) {
      headers.set('X-XSRF-TOKEN', xsrfToken)
    }

    return $fetch<T>(`${apiBase}/api${path}`, {
      credentials: 'include',
      ...options,
      headers,
    })
  }

  const unwrap = <T>(payload: T[] | PaginatedResponse<T> | null | undefined): T[] => {
    if (!payload) return []
    return Array.isArray(payload) ? payload : payload.data ?? []
  }

  const getErrorMessage = (error: unknown, fallback: string) => {
    if (error && typeof error === 'object' && 'data' in error) {
      const data = (error as { data?: { message?: string } }).data
      if (data?.message) return data.message
    }
    return fallback
  }

  const generateUuid = () => {
    if (typeof crypto !== 'undefined' && 'randomUUID' in crypto) {
      return crypto.randomUUID()
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (char) => {
      const rand = Math.random() * 16 | 0
      const value = char === 'x' ? rand : (rand & 0x3) | 0x8
      return value.toString(16)
    })
  }

  const uploadAttachment = async (file: File, options: AttachmentUploadOptions) => {
    const formData = new FormData()
    const attachmentableId = options.attachmentableId ?? generateUuid()

    formData.append('file', file)
    formData.append('attachmentable_id', attachmentableId)
    formData.append('attachmentable_type', options.attachmentableType)
    if (options.type) formData.append('type', options.type)
    if (options.remark) formData.append('remark', options.remark)

    return apiFetch<AttachmentApi>('/attachments', {
      method: 'POST',
      body: formData,
    })
  }

  return { apiBase, apiFetch, unwrap, getErrorMessage, uploadAttachment, getXsrfToken }
}