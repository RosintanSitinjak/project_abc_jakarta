<template>
  <div class="min-h-screen bg-[#f2f2f2] text-gray-700 pb-24 relative overflow-hidden">
    <!-- Elemen Ornamen Halus Latar Belakang (Sesuai Karakter Beranda & Footer) -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-1/3 left-0 w-80 h-80 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10"></div>

    <!-- 1. HEADER BANNER (Gaya Bersih & Terbuka, Tanpa Blok Biru Tua) -->
    <section class="py-16 md:py-20 text-center relative z-10">
      <div class="container mx-auto px-6">
        <!-- Badge Tagline Kecil -->
        <span class="inline-block px-3 py-1 rounded-full bg-[#00a9c3]/10 text-[#00a9c3] text-[10px] font-black uppercase tracking-[0.2em] mb-4 border border-[#00a9c3]/20">
          Inspirasi, Cerita, dan Gagasan
        </span>

        <!-- Judul Utama -->
        <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 tracking-tight leading-tight max-w-3xl mx-auto">
          <span class="text-[#00a9c3] italic font-serif">Jendela</span> Literasi & Pengetahuan
        </h1>
        
        <!-- Garis Pembatas Estetis Khas Tema -->
        <div class="w-12 h-[3px] bg-[#00a9c3] mx-auto mt-4 mb-5 rounded-full"></div>

        <!-- Deskripsi Pendek -->
        <p class="text-gray-500 max-w-2xl mx-auto text-sm md:text-base leading-relaxed">
          Tempat terbaik untuk berhenti sejenak dan membaca. Menyajikan informasi yang valid, 
          bermakna, dan menginspirasi untuk menemani waktu luang Anda setiap hari.
        </p>
      </div>
    </section>

    <!-- 2. DAFTAR ARTIKEL GRID -->
    <div class="max-w-6xl mx-auto px-6 sm:px-6 lg:px-8 relative z-20">
      
      <!-- State: Loading -->
      <div v-if="status === 'pending'" class="flex flex-col justify-center items-center py-24 space-y-4">
        <el-icon class="is-loading text-3xl text-[#00a9c3]"><Loading /></el-icon>
        <span class="text-xs tracking-widest uppercase text-gray-400 font-bold">Memuat Artikel Terbaik...</span>
      </div>

      <!-- State: Kosong -->
      <div v-else-if="!articles || articles.length === 0" class="text-center py-20 bg-white rounded-2xl border border-gray-200/60 shadow-sm p-8 max-w-md mx-auto">
        <div class="w-12 h-12 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <p class="text-gray-900 text-base font-bold">Belum Ada Literasi</p>
        <p class="text-gray-400 text-xs mt-1">Belum ada literasi diterbitkan saat ini.</p>
      </div>

      <!-- State: Grid Berhasil Dimuat -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div 
          v-for="item in articles" 
          :key="item.id"
          @click="navigateTo(`/articles/${item.slug}`)"
          class="group bg-white rounded-2xl border border-gray-200/50 overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col h-full cursor-pointer"
        >
          <!-- Bagian Cover Gambar -->
          <div class="relative aspect-[16/10] w-full bg-gray-50 overflow-hidden">
            <img 
              v-if="item.thumbnailUrl" 
              :src="item.thumbnailUrl" 
              :alt="item.title"
              class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500"
              loading="lazy"
            />
            <!-- Placeholder jika gambar kosong -->
            <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300 space-y-2 bg-gray-50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 stroke-current opacity-50" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span class="text-[9px] uppercase tracking-widest font-bold text-gray-400">No Cover</span>
            </div>
            
            <!-- Floating Badge Kategori Minimalis -->
            <span class="absolute top-4 left-4 bg-white/95 text-[#00a9c3] text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded shadow-sm border border-gray-100">
              Insights
            </span>
          </div>

          <!-- Bagian Konten Teks -->
          <div class="p-6 flex-1 flex flex-col justify-between">
            <div class="space-y-2.5">
              <!-- Judul Artikel -->
              <h2 class="text-base sm:text-lg font-bold text-gray-900 line-clamp-2 group-hover:text-[#00a9c3] transition-colors duration-200 leading-snug tracking-tight">
                <!-- Menghentikan propagasi klik ganda pada tautan teks bawaan -->
                <NuxtLink :to="`/articles/${item.slug}`" @click.stop>
                  {{ item.title }}
                </NuxtLink>
              </h2>
              
              <!-- Ringkasan Deskripsi Bersih -->
              <p class="text-gray-500 text-sm leading-relaxed line-clamp-3">
                {{ cleanDescription(item.description) }}
              </p>
            </div>

            <!-- Bagian Footer Kartu -->
            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between" @click.stop>
              <!-- Penulis (Diberi @click.stop agar klik info penulis tidak memicu navigasi ganda) -->
              <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 font-bold uppercase text-[9px] border border-gray-200">
                  {{ (item.author?.name || 'A')[0] }}
                </div>
                <span class="text-[11px] text-gray-400">
                  By <span class="text-gray-600 font-medium">{{ item.author?.name || 'Admin' }}</span>
                </span>
              </div>

              <!-- Tombol Navigasi Detil -->
              <NuxtLink 
                :to="`/articles/${item.slug}`"
                class="text-xs font-bold text-[#00a9c3] hover:text-[#0092a8] inline-flex items-center gap-1 group/btn"
              >
                Read More 
                <svg class="w-3 h-3 transform group-hover/btn:translate-x-0.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
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

// 1. Mengambil data artikel dengan logika perbaikan path gambar
const { data: articles, status } = await useAsyncData('public-articles', async () => {
  try {
    const response = await apiFetch<any>('/public/articles')
    const rawData = unwrap(response)
    
    const articlesArray = rawData?.data || rawData || []
    const baseUrlLaravel = 'http://localhost:8000'

    return articlesArray.map((item: any) => {
      let path = item.thumbnail?.path || ''

      // Ganti public/ ke storage/
      if (path.startsWith('public/')) {
        path = path.replace('public/', 'storage/')
      }

      // Tambahkan storage/ jika belum ada (Solusi untuk laptop Anda)
      if (path && !path.startsWith('http') && !path.startsWith('storage/')) {
        path = 'storage/' + path
      }

      // Gabungkan dengan URL Backend
      const fullImageUrl = path.startsWith('http')
        ? path
        : `${baseUrlLaravel}${path.startsWith('/') ? path : '/' + path}`
  
      return {
        id: item.id,
        slug: item.slug,
        title: item.title,
        description: item.description,
        author: item.author,
        thumbnailUrl: path ? fullImageUrl : null 
      }
    })
  } catch (err) {
    console.error('Gagal memuat daftar artikel:', err)
    return []
  }
})

// 2. Tambahkan Fungsi ini kembali agar tidak error "_ctx.cleanDescription is not a function"
const cleanDescription = (text: string): string => {
  if (!text) return 'Klik selengkapnya untuk membaca pembahasan.'
  // Menghapus tag HTML (seperti <p>, <b>, dll) agar deskripsi tampil bersih
  return text.replace(/<\/?[^>]+(>|$)/g, "")
}
</script>

<style scoped>
.group-hover\:scale-103 {
  transform: scale(1.03);
}
</style>