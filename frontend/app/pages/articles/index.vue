<template>
  <div class="min-h-screen bg-slate-50 py-16 px-4 sm:px-6 md:px-10 lg:px-20">
    <div class="max-w-6xl mx-auto">
      
      <div class="mb-14 text-center md:text-left">
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-neutral-400 mb-2">Our Insights & Updates</p>
        <h1 class="text-4xl font-extrabold text-neutral-900 tracking-tight md:text-5xl">New Article</h1>
        <div class="mt-6 h-[2px] w-16 bg-black mx-auto md:mx-0"></div>
      </div>

      <div v-if="status === 'pending'" class="flex flex-col justify-center items-center py-24 space-y-3">
        <el-icon class="is-loading text-3xl text-neutral-900"><Loading /></el-icon>
        <span class="text-xs tracking-widest uppercase text-neutral-400 font-medium">Memuat Artikel...</span>
      </div>

      <div v-else-if="!articles || articles.length === 0" class="text-center py-24 bg-white rounded-3xl border border-neutral-200/60 shadow-sm p-8">
        <p class="text-neutral-400 text-sm font-medium">Belum ada artikel yang diterbitkan saat ini.</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div 
          v-for="item in articles" 
          :key="item.id"
          class="group bg-white rounded-2xl border border-neutral-200/70 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-500 flex flex-col h-full"
        >
          <div class="relative aspect-[16/10] w-full bg-neutral-100 overflow-hidden border-b border-neutral-100">
            <img 
              v-if="item.thumbnailUrl" 
              :src="item.thumbnailUrl" 
              :alt="item.title"
              class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
              loading="lazy"
            />
            <div v-else class="w-full h-full flex flex-col items-center justify-center text-neutral-300 space-y-2 bg-neutral-50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 stroke-current opacity-80" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span class="text-[10px] uppercase tracking-widest font-semibold text-neutral-400">No Cover Available</span>
            </div>
          </div>

          <div class="p-6 flex-1 flex flex-col justify-between">
            <div class="space-y-3">
              <h2 class="text-lg font-bold text-neutral-900 line-clamp-2 group-hover:text-neutral-700 transition duration-300 leading-snug">
                {{ item.title }}
              </h2>
              
              <p class="text-neutral-500 text-sm leading-relaxed line-relaxed line-clamp-3">
                {{ cleanDescription(item.description) }}
              </p>
            </div>

            <div class="mt-6 pt-4 border-t border-neutral-100 flex items-center justify-between">
              <NuxtLink 
                :to="`/articles/${item.slug}`"
                class="text-xs uppercase tracking-widest font-bold text-neutral-900 hover:text-neutral-500 transition duration-300 flex items-center gap-2"
              >
                Baca Selengkapnya →
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script lang="ts" setup>
import { Loading } from '@element-plus/icons-vue'
import { useApi } from '../../composables/useApi'

definePageMeta({
  layout: 'public',
})

const { apiFetch, unwrap } = useApi()

const { data: articles, status } = await useAsyncData('public-articles', async () => {
  try {
    const response = await apiFetch<any>('/articles')
    const rawData = unwrap(response)
    
    const articlesArray = rawData?.data || rawData || []
    const baseUrlLaravel = 'http://localhost:8000'

    return articlesArray.map((item: any) => {
      const rawPath = item.thumbnail?.path || ''
      const originalPath = rawPath.startsWith('public/') 
        ? rawPath.replace('public/', 'storage/') 
        : rawPath
        
      const fullImageUrl = originalPath.startsWith('http')
        ? originalPath
        : `${baseUrlLaravel}${originalPath.startsWith('/') ? originalPath : '/' + originalPath}`
  
      return {
        id: item.id,
        title: item.title,
        description: item.description,
        author: item.author,
        thumbnailUrl: originalPath ? fullImageUrl : null
      }
    })
  } catch (err) {
    console.error('Gagal memuat daftar artikel:', err)
    return []
  }
})

const cleanDescription = (text: string): string => {
  if (!text) return 'Klik selengkapnya untuk membaca pembahasan.'
  const cleanText = text.replace(/<\/?[^>]+(>|$)/g, "")
  return cleanText
}
</script>