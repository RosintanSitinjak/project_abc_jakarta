<template>
  <section class="py-20 md:py-24 bg-[#00a9c3]/5 relative overflow-hidden">
    <!-- Dekorasi Latar Belakang Halus -->
    <div class="absolute top-1/4 right-0 w-72 h-72 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10 "></div>

    <div class="container mx-auto px-6 lg:px-8 max-w-6xl">
      <!-- Header Seksi: Judul & Tombol Lihat Semua -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-12 border-b border-gray-100 pb-6">
        <div>
          <!-- Tagline Kecil -->
          <span class="block text-xs font-bold uppercase tracking-widest text-[#00a9c3] mb-2">
            Artikel & Wawasan Terbaru
          </span>
          <!-- Judul Halaman Literasi -->
          <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">
            Jendela <span class="text-[#00a9c3] italic font-serif">Literasi</span>
          </h2>
        </div>
        
        <!-- Tombol Menuju Halaman Index Artikel -->
        <NuxtLink 
          to="/articles" 
          class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-widest text-gray-900 hover:text-[#00a9c3] transition-colors duration-200 group/all"
        >
          Lihat Semua Artikel Literasi
          <svg class="w-3.5 h-3.5 text-[#00a9c3] transform group-hover/all:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </NuxtLink>
      </div>

      <!-- State: Loading -->
      <div v-if="status === 'pending'" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-[#00a9c3] border-t-transparent"></div>
      </div>

      <!-- State: Jika Artikel Kosong -->
      <div v-else-if="!articles || articles.length === 0" class="text-center py-12 bg-white rounded-2xl p-6">
        <p class="text-gray-500 text-sm font-medium">Belum ada artikel literasi yang diterbitkan saat ini.</p>
      </div>

      <!-- State: Grid 3 Artikel Terbaru -->
      <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <article 
          v-for="item in articles" 
          :key="item.id" 
          class="group flex flex-col h-full bg-white rounded-2xl mb-5 transition-all duration-300"
        >
          <!-- Bagian Frame Foto Cover -->
          <div class="relative aspect-[16/10] overflow-hidden  mb-5 border border-gray-100/70 shadow-sm bg-gray-50 ">
            <img 
              :src="getThumbnailUrl(item)" 
              :alt="item.title"
              class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500" 
              loading="lazy"
            />
            <!-- Badge Kategori Mengambang -->
            <span class="absolute top-3 left-3 bg-white/95 text-[#00a9c3] text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded shadow-sm border border-gray-100">
              New
            </span>
          </div>

          <!-- Bagian Teks Konten -->
          <div class="flex flex-col flex-1">
            <!-- Judul Artikel -->
            <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-2.5 line-clamp-2 group-hover:text-[#00a9c3] transition-colors duration-200 leading-snug tracking-tight">
              <NuxtLink :to="`/articles/${item.slug}`">
                {{ item.title }}
              </NuxtLink>
            </h3>

            <!-- Cuplikan Isi Deskripsi -->
            <p class="text-gray-500 text-sm leading-relaxed mb-5 line-clamp-2">
              {{ stripHtml(item.content || item.description) }}
            </p>

            <!-- Tombol Baca Selengkapnya -->
            <NuxtLink 
              :to="`/articles/${item.slug}`" 
              class="text-xs font-bold text-[#00a9c3] hover:text-[#0092a8] inline-flex items-center gap-1 mt-auto group/btn w-fit"
            >
              Read More
              <svg class="w-3 h-3 transform group-hover/btn:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </NuxtLink>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const baseUrlLaravel = 'http://localhost:8000'

// Mengambil data artikel dengan batas maksimal 3 data langsung melalui query API 
const { data: response, status } = await useFetch(`${baseUrlLaravel}/api/public/articles`, { query: { limit: 3 } })

const articles = computed(() => response.value?.data || response.value || [])

// Fungsi membersihkan tag HTML di cuplikan teks deskripsi
const stripHtml = (html) => {
  if (!html) return 'Klik selengkapnya untuk membaca pembahasan.'
  return html.replace(/<[^>]*>?/gm, '') || ''
}

// Menangani pembuatan URL gambar thumbnail dari backend Laravel secara otomatis
// Menangani pembuatan URL gambar thumbnail dari backend Laravel secara otomatis
const getThumbnailUrl = (item) => {
  // 1. Ambil path mentah dari data thumbnail
  let path = item.thumbnail?.path || ''
  
  // 2. Jika tidak ada path, kembalikan string kosong
  if (!path) return '' 

  // 3. Jika path diawali 'public/', ganti jadi 'storage/' (standar Laravel)
  if (path.startsWith('public/')) {
    path = path.replace('public/', 'storage/')
  }

  // 4. JIKA path tidak diawali 'http' DAN belum ada kata 'storage/' di depannya,
  // MAKA kita tambahkan 'storage/' secara otomatis (Ini solusi untuk laptop kamu)
  if (!path.startsWith('http') && !path.startsWith('storage/')) {
    path = 'storage/' + path
  }

  // 5. Pastikan ada tanda '/' di awal path agar penggabungan URL benar
  const cleanPath = path.startsWith('/') ? path : '/' + path
  
  // 6. Gabungkan dengan base URL Laravel jika bukan link eksternal (http)
  return path.startsWith('http') ? path : `${baseUrlLaravel}${cleanPath}`
}
</script>

<style scoped>
/* Transisi pembesaran gambar yang halus saat hover kartu */
.group-hover\:scale-103 {
  transform: scale(1.03);
}
</style>