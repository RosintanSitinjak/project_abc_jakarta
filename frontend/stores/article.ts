import { defineStore } from 'pinia'
import type { PublicAttachment } from './app'

export interface PublicArticle {
  id: string
  title: string
  slug: string
  description: string | null
  author: string | null
  thumbnail: PublicAttachment | null
  views: number
}

export const useArticleStore = defineStore('article', {
  state: () => ({
    articles: [] as PublicArticle[],
    articlesLoaded: false,
    loadingArticles: false,
    popularArticles: [] as PublicArticle[],
    loadingPopular: false,
  }),
  actions: {
    async getArticles(force = false) {
      if (this.loadingArticles) return this.articles
      if (!force && this.articlesLoaded) return this.articles

      this.loadingArticles = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase.replace(/\/$/, '')
        const response = await $fetch<PublicArticle[] | { data: PublicArticle[] }>(
          `${apiBase}/api/public/articles`
        )
        const data = Array.isArray(response) ? response : response.data ?? []
        this.articles = data
        this.articlesLoaded = true
        return data
      } finally {
        this.loadingArticles = false
      }
    },
    async getArticle(slug: string) {
      const config = useRuntimeConfig()
      const apiBase = config.public.apiBase.replace(/\/$/, '')
      return $fetch<PublicArticle>(`${apiBase}/api/public/articles/${slug}`)
    },
    async getPopularArticles(exclude?: string) {
      this.loadingPopular = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase.replace(/\/$/, '')
        const params = exclude ? `?exclude=${exclude}` : ''
        const response = await $fetch<PublicArticle[]>(
          `${apiBase}/api/public/articles/popular${params}`
        )
        this.popularArticles = response
        return response
      } finally {
        this.loadingPopular = false
      }
    },
  },
})
