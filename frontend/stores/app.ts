import { defineStore } from 'pinia'

export interface PublicServiceScope {
  id: string
  service_id: string
  scope: string
}

export interface PublicService {
  id: string
  name: string
  slug: string
  description: string | null
  content: string | null
  scopes: PublicServiceScope[]
  thumbnail: PublicAttachment | null
}

export interface PublicCategory {
  id: string
  name: string
  description: string | null
}

export interface PublicAttachment {
  id: string
  attachmentable_id: string
  attachmentable_type: string
  name: string
  path: string
  size: number | null
  mime: string | null
  disk: string | null
  folder: string | null
  type: string | null
  remark: string | null
}

export interface PublicClient {
  id: string
  name: string
  logo: PublicAttachment | null
}

export interface PublicProduct {
  id: string
  category_id: string | null
  title: string
  description: string | null
  feature: string | null
  area: string | null
  category: PublicCategory | null
  thumbnail: PublicAttachment | null
}

export interface PublicPortfolio {
  id: string
  category_id: string | null
  title: string
  description: string | null
  client_name: string | null
  year: number | null
  areas: string[] | null
  category: PublicCategory | null
  thumbnail: PublicAttachment | null
  photos: PublicAttachment[]
}

export const useAppStore = defineStore('app', {
  state: () => ({
    sidebar: false,
    services: [] as PublicService[],
    products: [] as PublicProduct[],
    portfolios: [] as PublicPortfolio[],
    clients: [] as PublicClient[],
    servicesLoaded: false,
    productsLoaded: false,
    portfoliosLoaded: false,
    clientsLoaded: false,
    loadingServices: false,
    loadingProducts: false,
    loadingPortfolios: false,
    loadingClients: false,
  }),
  share: {
    initialize: true,
    omit: ['sidebar', 'loadingServices', 'loadingProducts', 'loadingPortfolios', 'loadingClients'],
  },
  actions: {
    toggleSidebar() {
      this.sidebar = !this.sidebar
    },
    setSidebar(value: boolean) {
      this.sidebar = value
    },
    async getServices(force = false) {
      if (this.loadingServices) return this.services
      if (!force && this.servicesLoaded) return this.services

      this.loadingServices = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase.replace(/\/$/, '')
        const data = await $fetch<PublicService[]>(`${apiBase}/api/public/services`)
        this.services = data
        this.servicesLoaded = true
        return data
      } finally {
        this.loadingServices = false
      }
    },
    async getProducts(force = false) {
      if (this.loadingProducts) return this.products
      if (!force && this.productsLoaded) return this.products

      this.loadingProducts = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase.replace(/\/$/, '')
        const data = await $fetch<PublicProduct[]>(`${apiBase}/api/public/products`)
        this.products = data
        this.productsLoaded = true
        return data
      } finally {
        this.loadingProducts = false
      }
    },
    async getPortfolios(force = false) {
      if (this.loadingPortfolios) return this.portfolios
      if (!force && this.portfoliosLoaded) return this.portfolios

      this.loadingPortfolios = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase.replace(/\/$/, '')
        const data = await $fetch<PublicPortfolio[]>(`${apiBase}/api/public/portfolios`)
        this.portfolios = data
        this.portfoliosLoaded = true
        return data
      } finally {
        this.loadingPortfolios = false
      }
    },
    async getClients(force = false) {
      if (this.loadingClients) return this.clients
      if (!force && this.clientsLoaded) return this.clients

      this.loadingClients = true
      try {
        const config = useRuntimeConfig()
        const apiBase = config.public.apiBase.replace(/\/$/, '')
        const response = await $fetch<PublicClient[] | { data: PublicClient[] }>(
          `${apiBase}/api/clients`,
        )
        const data = Array.isArray(response) ? response : response.data ?? []
        this.clients = data
        this.clientsLoaded = true
        return data
      } finally {
        this.loadingClients = false
      }
    },
  },
})
