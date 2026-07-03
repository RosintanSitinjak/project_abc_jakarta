<template>
  <div class="min-h-screen bg-gray-50/30 py-16 px-4 sm:px-6 relative overflow-hidden">
    <!-- Elemen Dekoratif Halus Latar Belakang -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-1/2 left-0 w-72 h-72 bg-gray-200/40 rounded-full blur-3xl -z-10"></div>

    <!-- State: Loading -->
    <div v-if="status === 'pending'" class="flex flex-col justify-center items-center min-h-[60vh] space-y-4">
      <el-icon class="is-loading text-3xl text-[#00a9c3]"><Loading /></el-icon>
      <span class="text-xs uppercase tracking-widest text-[#00a9c3] font-bold">Memuat Isi Artikel...</span>
    </div>

    <!-- State: Artikel Ditemukan -->
    <article v-else-if="article" class="max-w-3xl mx-auto bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-gray-100 relative z-10">
      <!-- Tombol Kembali Bergaya Minimalis -->
      <NuxtLink 
        to="/articles" 
        class="inline-flex items-center gap-2 text-xs uppercase tracking-widest font-bold text-gray-400 hover:text-[#00a9c3] mb-8 transition-colors duration-300 group"
      >
        <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Artikel
      </NuxtLink>

      <!-- Informasi Atas -->
      <div class="mb-4">
        <span class="text-xs font-bold uppercase tracking-widest text-[#00a9c3] bg-[#00a9c3]/10 px-3 py-1 rounded-md">
          Insights
        </span>
      </div>

      <!-- Judul Artikel -->
      <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight sm:leading-snug">
        {{ article.title }}
      </h1>

      <!-- Meta Data Penulis & Tanggal -->
      <div class="mt-4 flex items-center gap-3 text-xs text-gray-400 border-b border-gray-100 pb-6 mb-8">
        <div class="flex items-center gap-1.5">
          <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold uppercase border border-gray-200 text-[10px]">
            {{ (article.author?.name || 'A')[0] }}
          </div>
          <span>Oleh <span class="text-gray-700 font-semibold">{{ article.author?.name || 'Admin' }}</span></span>
        </div>
        <span>&bull;</span>
        <div class="flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>Bacaan Terpercaya</span>
        </div>
      </div>

      <!-- Hero Gambar Thumbnail Utama -->
      <div v-if="article.thumbnailUrl" class="my-8 aspect-[16/9] sm:aspect-[21/9] rounded-2xl overflow-hidden bg-gray-100 border border-gray-200/60 shadow-inner group">
        <img 
          :src="article.thumbnailUrl" 
          :alt="article.title" 
          class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-500" 
        />
      </div>

      <!-- Konten Utama Artikel (HTML Dinamis) -->
      <div 
        class="prose prose-neutral max-w-none text-gray-700 leading-relaxed font-normal text-base sm:text-lg dynamic-html-content image-fix"
        v-html="article.content_html"
      ></div>
    </article>

    <!-- State: Artikel Tidak Ditemukan -->
    <div v-else class="text-center py-24 max-w-md mx-auto bg-white rounded-3xl p-8 border border-gray-100 shadow-sm relative z-10">
      <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
      </div>
      <h2 class="text-xl font-black text-gray-900 tracking-tight">Artikel Tidak Ditemukan</h2>
      <p class="text-gray-400 text-sm mt-2 leading-relaxed">
        Maaf, tautan artikel ini salah atau berkas telah dipindahkan oleh pengelola admin literatur kami.
      </p>
      <NuxtLink 
        to="/articles" 
        class="mt-6 inline-flex items-center justify-center w-full text-xs uppercase tracking-widest font-bold bg-[#00a9c3] hover:bg-[#0092a8] text-white px-6 py-3.5 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg"
      >
        Kembali ke Daftar Artikel
      </NuxtLink>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { Loading } from '@element-plus/icons-vue'

definePageMeta({
  layout: 'public',
})

const route = useRoute()
const articleSlug = route.params.slug as string

const { data: article, status } = await useAsyncData(`direct-detail-${articleSlug}`, async () => {
  try {
    const response = await $fetch<any>(`http://localhost:8000/api/public/articles/${articleSlug}`)
    const data = response?.data ? response.data : response
    
    if (!data) return null

    const baseUrlLaravel = 'http://localhost:8000'
    
    const rawPath = data.thumbnail?.path || ''
    const originalPath = rawPath.startsWith('public/') ? rawPath.replace('public/', 'storage/') : rawPath
    const fullImageUrl = originalPath.startsWith('http') 
      ? originalPath 
      : `${baseUrlLaravel}${originalPath.startsWith('/') ? originalPath : '/' + originalPath}`

    let rawContentHtml = data.description || data.content || ''

    if (rawContentHtml && typeof rawContentHtml === 'string') {
      rawContentHtml = rawContentHtml.replace(/src="\/storage\//g, `src="${baseUrlLaravel}/storage/`)
      rawContentHtml = rawContentHtml.replace(/src="\/api\/attachments\//g, `src="${baseUrlLaravel}/api/attachments/`)
    }

    return {
      id: data.id,
      title: data.title || 'Tanpa Judul',
      content_html: rawContentHtml || 'Tidak ada konten.',
      author: data.author,
      thumbnailUrl: originalPath ? fullImageUrl : null
    }
  } catch (err) {
    console.error('Gagal mengambil detail artikel:', err)
    return null
  }
})

useHead({
  title: computed(() => article.value?.title || 'Detail Artikel'),
})
</script>

<style scoped>
/* Styling presisi untuk elemen gambar di dalam v-html agar rapi */
.image-fix :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 1.25rem;
  margin: 2rem auto;
  display: block;
  box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(229, 231, 235, 0.6);
}

/* Penyelarasan paragraf teks utama v-html */
.dynamic-html-content :deep(p) {
  margin-bottom: 1.5rem;
  line-height: 1.8;
  color: #4b5563; /* Gray-600 */
}

/* Penyelarasan heading di dalam isi artikel v-html */
.dynamic-html-content :deep(h1), 
.dynamic-html-content :deep(h2), 
.dynamic-html-content :deep(h3) {
  font-weight: 800;
  color: #111827; /* Gray-900 */
  margin-top: 2.5rem;
  margin-bottom: 0.75rem;
  tracking-tight;
}

.dynamic-html-content :deep(h2) {
  font-size: 1.5rem;
  border-left: 4px solid #00a9c3;
  padding-left: 0.75rem;
}

/* Penyelarasan untuk format list di dalam artikel */
.dynamic-html-content :deep(ul) {
  list-style-type: cubic-bezier(0.4, 0, 0.2, 1);
  padding-left: 1.5rem;
  margin-bottom: 1.5rem;
  space-y: 0.5rem;
}

.dynamic-html-content :deep(ol) {
  list-style-type: decimal;
  padding-left: 1.5rem;
  margin-bottom: 1.5rem;
}
</style>