<template>
  <div class="min-h-screen bg-white py-12 px-4 sm:px-6">
    <!-- State: Loading -->
    <div v-if="status === 'pending'" class="flex flex-col justify-center items-center min-h-[50vh] space-y-3">
      <el-icon class="is-loading text-2xl text-neutral-900"><Loading /></el-icon>
      <span class="text-xs uppercase tracking-widest text-neutral-400 font-medium">Memuat Isi Artikel...</span>
    </div>

    <!-- State: Artikel Ditemukan -->
    <article v-else-if="article" class="max-w-3xl mx-auto">
      <NuxtLink to="/articles" class="inline-block text-xs uppercase tracking-widest font-bold text-neutral-400 hover:text-black mb-8 transition">
        &larr; Kembali
      </NuxtLink>

      <h1 class="text-3xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight leading-tight">
        {{ article.title }}
      </h1>

      <div class="mt-4 flex items-center gap-2 text-xs text-neutral-500 uppercase tracking-wider mb-8">
        <span>By <span class="text-neutral-900 font-semibold">{{ article.author?.name || 'Admin' }}</span></span>
        <span>&bull;</span>
        <span>Insights</span>
      </div>

      <div v-if="article.thumbnailUrl" class="my-8 aspect-[21/9] rounded-2xl overflow-hidden bg-neutral-100 border border-neutral-200">
        <img :src="article.thumbnailUrl" :alt="article.title" class="w-full h-full object-cover" />
      </div>

      <!-- Konten Artikel (HTML) -->
      <div 
        class="prose prose-neutral max-w-none text-neutral-800 leading-relaxed space-y-4 font-normal text-base sm:text-lg image-fix dynamic-html-content"
        v-html="article.content_html"
      ></div>
    </article>

    <!-- State: Artikel Tidak Ditemukan -->
    <div v-else class="text-center py-20">
      <h2 class="text-lg font-bold text-neutral-900">Artikel Tidak Ditemukan</h2>
      <p class="text-neutral-400 text-sm mt-1">Gagal mengambil data artikel dari server backend.</p>
      <NuxtLink to="/articles" class="mt-6 inline-block text-xs uppercase tracking-widest font-bold bg-black text-white px-6 py-3 rounded-xl">
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
// PERBAIKAN 1: Gunakan .slug karena nama file adalah [slug].vue
const articleSlug = route.params.slug

const { data: article, status } = await useAsyncData(`article-detail-${articleSlug}`, async () => {
  try {
    // PERBAIKAN 2: Tambahkan /public/ pada URL API agar bisa diakses tanpa login
    const response = await $fetch<any>(`http://localhost:8000/api/public/articles/${articleSlug}`)
    
    // Sesuaikan dengan struktur data dari Laravel (biasanya dibungkus dalam properti 'data')
    const data = response?.data ? response.data : response
    
    if (!data) return null

    const baseUrlLaravel = 'http://localhost:8000'
    
    // Logika pengolahan path gambar thumbnail
    const rawPath = data.thumbnail?.path || ''
    const originalPath = rawPath.startsWith('public/') ? rawPath.replace('public/', 'storage/') : rawPath
    const fullImageUrl = originalPath.startsWith('http') 
      ? originalPath 
      : `${baseUrlLaravel}${originalPath.startsWith('/') ? originalPath : '/' + originalPath}`

    // Logika pengolahan isi konten (HTML)
    let rawContentHtml = data.description || data.content || ''

    if (rawContentHtml && typeof rawContentHtml === 'string') {
      // Fix link gambar di dalam konten agar mengarah ke backend yang benar
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
.image-fix :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 1rem;
  margin: 1.5rem auto;
  display: block;
}

.dynamic-html-content :deep(p) {
  margin-bottom: 1.25rem;
  line-height: 1.75;
}

.dynamic-html-content :deep(h1), 
.dynamic-html-content :deep(h2), 
.dynamic-html-content :deep(h3) {
  font-weight: 700;
  color: #171717;
  margin-top: 1.5rem;
  margin-bottom: 0.5rem;
}
</style>