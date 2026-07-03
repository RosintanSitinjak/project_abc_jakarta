<template>
  <section class="py-20 md:py-24 bg-[#00a9c3]/5 relative overflow-hidden">
    <!-- Dekorasi Latar Belakang Halus -->
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10"></div>

    <div class="container mx-auto px-6 lg:px-8 max-w-6xl">
      <!-- Header Seksi: Judul & Navigasi Utama -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-12 border-b border-gray-200/60 pb-6">
        <div>
          <!-- Tagline Kecil -->
          <span class="block text-xs font-bold uppercase tracking-widest text-[#00a9c3] mb-2">
            Rekomendasi Pustaka
          </span>
          <!-- Judul Seksi -->
          <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">
            Katalog <span class="text-[#00a9c3] italic font-serif">Pilihan</span>
          </h2>
        </div>
        
        <!-- Tombol Menuju Halaman Index Katalog -->
        <NuxtLink 
          to="/katalog" 
          class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-widest text-gray-900 hover:text-[#00a9c3] transition-colors duration-200 group/all"
        >
          Lihat Semua Koleksi 
          <svg class="w-3.5 h-3.5 text-[#00a9c3] transform group-hover/all:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </NuxtLink>
      </div>

      <!-- State: Loading -->
      <div v-if="status === 'pending'" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-[#00a9c3] border-t-transparent"></div>
      </div>

      <!-- State: Jika Katalog Kosong -->
      <div v-else-if="!books || books.length === 0" class="text-center py-12 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <p class="text-gray-500 text-sm font-medium">Belum ada buku dalam katalog yang ditampilkan saat ini.</p>
      </div>

      <!-- State: Grid Koleksi Buku Terpilih (Menampilkan 4 Buku) -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
          v-for="book in books" 
          :key="book.id" 
          class="group bg-white rounded-2xl border border-gray-200/40 overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 flex flex-col h-full"
        >
          <!-- Frame Sampul Buku (Rasio 3:4 Khas Buku) -->
          <div class="relative aspect-[3/4] w-full bg-gray-50 overflow-hidden border-b border-gray-100">
            <img 
              v-if="book.coverUrl"
              :src="book.coverUrl" 
              :alt="book.title"
              class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500" 
              loading="lazy"
            />
            <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300 p-4 bg-gray-50 text-center">
              <span class="text-[9px] uppercase tracking-widest font-bold text-gray-400">No Cover</span>
            </div>

            <!-- Badge Kategori / Jenis Buku Mengambang -->
            <span v-if="book.type" class="absolute top-3 left-3 bg-white/95 text-[#00a9c3] text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded shadow-sm border border-gray-100">
              {{ book.type }}
            </span>
          </div>

          <!-- Bagian Informasi Konten -->
          <div class="p-5 flex-1 flex flex-col justify-between">
            <div class="space-y-2">
              <!-- Judul Buku -->
              <h3 class="text-sm font-bold text-gray-900 line-clamp-2 group-hover:text-[#00a9c3] transition-colors duration-200 leading-snug tracking-tight">
                <NuxtLink :to="`/katalog/${book.slug}`">
                  {{ book.title }}
                </NuxtLink>
              </h3>

              <!-- Ringkasan Deskripsi Buku -->
              <p class="text-gray-400 text-xs leading-relaxed line-clamp-2">
                {{ stripHtml(book.description) }}
              </p>
            </div>

            <!-- Bagian Footer Kartu (Harga & Aksi) -->
            <div class="mt-5 pt-3 border-t border-gray-50 flex items-end justify-between">
              <!-- Harga -->
              <div class="flex flex-col">
                <span class="text-[9px] text-gray-400 uppercase tracking-wider font-medium">Investasi</span>
                <span class="text-xs sm:text-sm font-extrabold text-[#00a9c3]">
                  {{ formatPrice(book.price) }}
                </span>
              </div>

              <!-- Tombol Aksi: Tinjau Lebih Jauh -->
              <NuxtLink 
                :to="`/katalog/${book.slug}`" 
                class="text-[10px] font-black uppercase tracking-widest text-gray-900 bg-gray-50 border border-gray-200/60 px-2.5 py-1.5 rounded-lg group-hover:bg-[#00a9c3] group-hover:text-white group-hover:border-[#00a9c3] transition-all duration-300 inline-flex items-center gap-1"
              >
                Tinjau Lebih Jauh
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const baseUrlLaravel = 'http://localhost:8000'

// Mengambil data dari endpoint katalog/books Anda (Batas maksimal 4 item untuk row beranda)
const { data: response, status } = await useFetch(`${baseUrlLaravel}/api/public/books`, { query: { limit: 4 } })

const books = computed(() => {
  const rawData = response.value?.data || response.value || []
  
  return rawData.map(item => {
    const rawPath = item.image?.path || item.thumbnail?.path || ''
    const originalPath = rawPath.startsWith('public/') ? rawPath.replace('public/', 'storage/') : rawPath
    const fullImageUrl = originalPath.startsWith('http') ? originalPath : `${baseUrlLaravel}${originalPath.startsWith('/') ? originalPath : '/' + originalPath}`

    return {
      id: item.id,
      slug: item.slug,
      title: item.title,
      description: item.description,
      type: item.type || item.category?.name,
      price: item.price || 0,
      coverUrl: rawPath ? fullImageUrl : null
    }
  })
})

// Fungsi pembersih tag HTML teks deskripsi
const stripHtml = (html) => {
  if (!html) return 'Klik untuk meninjau detail informasi buku.'
  return html.replace(/<[^>]*>?/gm, '') || ''
}

// Fungsi pembantu format rupiah (IDR)
const formatPrice = (price) => {
  if (!price || price === 0) return 'Gratis'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(price)
}
</script>

<style scoped>
.group-hover\:scale-103 {
  transform: scale(1.03);
}
</style>