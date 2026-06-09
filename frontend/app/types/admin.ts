import type { UploadUserFile } from 'element-plus'

export interface User {
  id: string
  name: string
  email: string
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

export interface SiteSetting {
  id: string
  siteName: string
  email: string
  phone: string
  whatsappNumber: string
  address: string
  linkedinUrl: string
  facebookUrl: string
  twitterUrl: string
  instagramUrl: string
  vision: string
  mission: string
  aboutUs: string
  siteDescription: string
  logo: UploadUserFile | null
}

export interface ServiceScope {
  id: string
  serviceId: string
  scope: string
}

export interface Service {
  id: string
  name: string
  slug: string
  description: string
  content: string
  scopes: ServiceScope[]
  thumbnail: UploadUserFile | null
}

export interface Category {
  id: string
  name: string
  description?: string | null
}

export interface Client {
  id: string
  name: string
  position?: number | null
  logo: UploadUserFile | null
}

export interface Product {
  id: string
  title: string
  categoryId: string | null
  category: Category | null
  area: string
  feature: string
  description: string
  position?: number | null
  image: UploadUserFile | null
}

export interface Portfolio {
  id: string
  title: string
  clientName: string
  year: number | null
  categoryId: string | null
  category: Category | null
  areas: string[]
  description: string
  thumbnail: UploadUserFile | null
  documentations: UploadUserFile[]
}

export interface AttachmentApi {
  id: string
  name: string
  path: string
}

export interface ArticleApi {
  id: string
  title: string
  slug: string
  author: { id: string, name: string } | null
  description: string | null
  thumbnail: AttachmentApi | null
}

export interface Article {
  id: string
  title: string
  slug: string
  description: string | null
  author: User | null
  thumbnail: UploadUserFile | null
}
