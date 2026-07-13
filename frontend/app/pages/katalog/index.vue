<template>
  <div class="min-h-screen bg-[#f2f2f2] text-gray-700 pb-24 relative overflow-hidden">
    <!-- Elemen Ornamen Halus Latar Belakang -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-1/3 left-0 w-80 h-80 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10"></div>

    <!-- 1. HEADER BANNER KATALOG -->
    <section class="py-16 md:py-20 text-center relative z-10">
      <div class="container mx-auto px-6">
        <!-- Badge Tagline -->
        <span class="inline-block px-3 py-1 rounded-full bg-[#00a9c3]/10 text-[#00a9c3] text-[10px] font-black uppercase tracking-[0.2em] mb-4 border border-[#00a9c3]/20">
          Koleksi Pustaka
        </span>

        <!-- Judul Utama -->
        <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 tracking-tight leading-tight max-w-3xl mx-auto">
          Katalog <span class="text-[#00a9c3] italic font-serif">Buku</span> Pilihan
        </h1>
        
        <!-- Garis Pembatas Khas Tema -->
        <div class="w-12 h-[3px] bg-[#00a9c3] mx-auto mt-4 mb-5 rounded-full"></div>

        <!-- Deskripsi -->
        <p class="text-gray-500 max-w-2xl mx-auto text-sm md:text-base leading-relaxed">
          Temukan ragam literatur berkualitas, buku panduan praktis, hingga jurnal ilmiah yang dikurasi langsung oleh tim ahli kami.
        </p>
      </div>
    </section>

    <!-- 2. DAFTAR KATALOG GRID -->
    <div class="max-w-6xl mx-auto px-6 sm:px-6 lg:px-8 relative z-20">
      
      <!-- State: Loading -->
      <div v-if="status === 'pending'" class="flex flex-col justify-center items-center py-24 space-y-4">
        <el-icon class="is-loading text-3xl text-[#00a9c3]"><Loading /></el-icon>
        <span class="text-xs tracking-widest uppercase text-gray-400 font-bold">Memuat Katalog Buku...</span>
      </div>

      <!-- State: Kosong -->
      <div v-else-if="!books || books.length === 0" class="text-center py-20 bg-white rounded-2xl border border-gray-200/60 shadow-sm p-8 max-w-md mx-auto">
        <div class="w-12 h-12 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <p class="text-gray-900 text-base font-bold">Katalog Kosong</p>
        <p class="text-gray-400 text-xs mt-1">Belum ada koleksi buku yang diterbitkan saat ini.</p>
      </div>

      <!-- State: Grid Buku Berhasil Dimuat -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
          v-for="book in books" 
          :key="book.id"
          class="group bg-white rounded-2xl border border-gray-200/50 overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col h-full"
        >
          <!-- Bagian Sampul Buku (Aspek Rasio Khusus Buku Potret 3:4) -->
          <div class="relative aspect-[3/4] w-full bg-gray-50 overflow-hidden border-b border-gray-100">
            <img 
              v-if="book.coverUrl" 
              :src="book.coverUrl" 
              :alt="book.title"
              class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500"
              loading="lazy"
            />
            <!-- Placeholder Sampul Kosong -->
            <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300 p-4 text-center bg-gray-50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 stroke-current opacity-40 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13" />
              </svg>
              <span class="text-[9px] uppercase tracking-widest font-bold text-gray-400">No Cover Available</span>
            </div>
            
            <!-- Floating Jenis/Kategori Buku -->
            <span v-if="book.type" class="absolute top-3 left-3 bg-white/95 text-[#00a9c3] text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded shadow-sm border border-gray-100">
              {{ book.type }}
            </span>
          </div>

          <!-- Bagian Detail Buku -->
          <div class="p-5 flex-1 flex flex-col justify-between">
            <div class="space-y-2">
              <!-- Judul Buku -->
              <h2 class="text-sm sm:text-base font-bold text-gray-900 line-clamp-2 group-hover:text-[#00a9c3] transition-colors duration-200 leading-snug tracking-tight">
                <NuxtLink :to="`/katalog/${book.slug}`">
                  {{ book.title }}
                </NuxtLink>
              </h2>
              
              <!-- Deskripsi Singkat Buku -->
              <p class="text-gray-400 text-xs leading-relaxed line-clamp-2">
                {{ cleanDescription(book.description) }}
              </p>
            </div>

            <!-- Bagian Harga & Tombol Navigasi -->
            <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between">
              <!-- Komponen Harga -->
              <div class="flex flex-col">
  <!-- KONDISI 1: JIKA PENGINJIL SUDAH APPROVED (Hanya mereka yang lihat harga ini) -->
  <template v-if="isApprovedPL">
  <span class="text-[9px] text-green-600 font-black uppercase">Harga Khusus Penginjil</span>
  <div class="flex items-center gap-1.5">
    <span class="text-sm font-extrabold text-gray-900">{{ formatPrice(book.member_price) }}</span>
    <span class="text-[10px] text-gray-400 line-through">{{ formatPrice(book.price) }}</span>
  </div>
</template>

  <!-- KONDISI 2: JIKA PENGINJIL MASIH PENDING -->
  <template v-else-if="isPendingPL">
    <span class="text-sm font-extrabold text-[#00a9c3]">{{ formatPrice(book.price) }}</span>
    <span class="text-[8px] text-orange-500 font-bold italic leading-none">Verifikasi akun tertunda</span>
  </template>

  <!-- KONDISI 3: UMUM / JEMAAT / BELUM LOGIN -->
  <template v-else>
    <span class="text-[9px] text-gray-400 uppercase tracking-wider font-medium">Harga</span>
    <span class="text-sm font-extrabold text-[#00a9c3]">
      {{ formatPrice(book.price) }}
    </span>
  </template>
</div>

              <!-- Tombol Detail -->
              <NuxtLink 
                :to="`/katalog/${book.slug}`"
                class="text-[11px] font-black uppercase tracking-wider text-gray-900 hover:text-[#00a9c3] inline-flex items-center gap-1 group/btn"
              >
                Detail
                <svg class="w-3 h-3 text-[#00a9c3] transform group-hover/btn:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
import { useAuth } from '../../composables/useAuth'

definePageMeta({ layout: 'public' })

const { apiFetch, unwrap } = useApi()
const { isApprovedPL, isPendingPL } = useAuth()
const baseUrlLaravel = 'http://127.0.0.1:8000'

const { data: books, status } = await useAsyncData('public-books', async () => {
  try {
    const response = await apiFetch<any>('/public/books') 
    const rawData = unwrap(response)
    const booksArray = rawData?.data || rawData || []

    return booksArray.map((item: any) => {
      // LOGIKA GAMBAR (Sama dengan Artikel agar PASTI muncul)
      let path = item.image?.path || item.thumbnail?.path || ''
      if (path.startsWith('public/')) path = path.replace('public/', 'storage/')
      if (path && !path.startsWith('http') && !path.startsWith('storage/')) {
        path = 'storage/' + path
      }
      const fullImageUrl = path.startsWith('http') ? path : `${baseUrlLaravel}/${path}`
  
      return {
        id: item.id,
        slug: item.slug,
        title: item.title,
        description: item.description || '',
        type: item.category?.name || item.type,
        // PASTIKAN NAMA VARIABEL HARGA DI BAWAH INI BENAR
        price: item.price || 0, 
        member_price: item.member_price || item.price_le || 0, // Mengambil salah satu yang ada
        coverUrl: path ? fullImageUrl : null
      }
    })
  } catch (err) {
    console.error('Gagal memuat katalog:', err)
    return []
  }
})

const cleanDescription = (text: string) => text ? text.replace(/<\/?[^>]+(>|$)/g, "") : ''
const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price)
}
</script>

<style scoped>
.group-hover\:scale-103 {
  transform: scale(1.03);
}
</style>