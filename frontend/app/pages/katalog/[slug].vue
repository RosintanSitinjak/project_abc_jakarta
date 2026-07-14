<template>
  <div class="min-h-screen bg-white py-12 md:py-20">
    <div class="container mx-auto px-6 max-w-6xl">
      
      <!-- Tombol Kembali -->
      <NuxtLink to="/katalog" class="inline-flex items-center gap-2 text-gray-400 hover:text-[#00a9c3] transition-colors mb-8 group">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span class="text-sm font-bold uppercase tracking-widest">Kembali ke Katalog</span>
      </NuxtLink>

      <!-- State: Loading -->
      <div v-if="status === 'pending'" class="flex justify-center items-center py-40">
        <el-icon class="is-loading text-4xl text-[#00a9c3]"><Loading /></el-icon>
      </div>

      <!-- State: Data Buku Ditemukan -->
      <div v-else-if="book" class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-start">
        
        <!-- BAGIAN KIRI: Gambar Buku -->
        <div class="relative group">
          <div class="absolute inset-0 bg-[#00a9c3]/5 blur-[100px] rounded-full scale-90 -z-10"></div>
          <div class="bg-gray-50 rounded-[40px] p-8 md:p-12 border border-gray-100 shadow-sm overflow-hidden flex justify-center">
            <img 
              :src="book.coverUrl" 
              :alt="book.title" 
              class="w-full max-w-[400px] h-auto shadow-2xl rounded-xl object-cover transform group-hover:scale-[1.02] transition-transform duration-700"
            />
          </div>
        </div>

        <!-- BAGIAN KANAN: Detail Informasi -->
        <div class="flex flex-col">
          <!-- Kategori & Stok -->
          <div class="flex items-center gap-3 mb-6">
            <span class="px-3 py-1 bg-[#00a9c3]/10 text-[#00a9c3] text-[10px] font-black uppercase tracking-widest rounded-full border border-[#00a9c3]/20">
              {{ book.type }}
            </span>
            <span v-if="book.stock > 0" class="text-[10px] font-bold text-green-600 uppercase tracking-widest">
              ● Stok Tersedia
            </span>
          </div>

          <h1 class="text-3xl md:text-5xl font-black text-gray-900 leading-tight mb-6 tracking-tight">
            {{ book.title }}
          </h1>

          <!-- BOX HARGA KRUSIAL -->
          <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 mb-10 shadow-sm">
            <div v-if="isApprovedPL">
              <p class="text-[10px] text-green-600 font-black uppercase tracking-[0.2em] mb-2">Harga Khusus Penginjil</p>
              <div class="flex items-baseline gap-3">
                <span class="text-4xl font-black text-gray-900">{{ formatPrice(book.member_price) }}</span>
                <span class="text-lg text-gray-400 line-through decoration-red-400/40">{{ formatPrice(book.price) }}</span>
              </div>
            </div>
            
            <div v-else>
              <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] mb-2">Harga Satuan</p>
              <span class="text-4xl font-black text-[#00a9c3]">{{ formatPrice(book.price) }}</span>
              
              <!-- Info jika belum approved -->
              <div v-if="isPendingPL" class="mt-4 p-3 bg-orange-50 rounded-xl border border-orange-100 flex items-center gap-2">
                <el-icon class="text-orange-500"><Warning /></el-icon>
                <p class="text-[11px] text-orange-700 font-medium italic">Akun Anda sedang menunggu verifikasi untuk mendapatkan harga khusus.</p>
              </div>
            </div>
          </div>

          <!-- DESKRIPSI BUKU -->
          <div class="mb-10">
            <h3 class="text-sm font-black uppercase tracking-widest text-gray-900 mb-4 pb-2 border-b w-max border-[#00a9c3]">Sinopsis / Deskripsi</h3>
            <div 
              class="prose prose-slate max-w-none text-gray-600 leading-relaxed italic font-serif text-lg"
              v-html="book.description"
            ></div>
          </div>

          <!-- Tombol Aksi -->
          <div class="flex flex-col sm:flex-row gap-4 mt-auto">
            <el-button 
              type="primary" 
              class="flex-1 !h-16 !rounded-2xl !bg-[#00a9c3] !border-none text-lg font-black uppercase tracking-widest shadow-xl shadow-[#00a9c3]/30 hover:!bg-gray-900 transition-all"
              @click="handleAddToCart"
            >
              Tambah ke Keranjang
            </el-button>
          </div>
        </div>

      </div>

      <!-- State: Jika Buku Tidak Ditemukan -->
      <div v-else class="text-center py-40">
        <h2 class="text-2xl font-bold text-gray-400 tracking-tighter">Buku tidak ditemukan.</h2>
        <NuxtLink to="/katalog" class="text-[#00a9c3] underline font-bold mt-4 inline-block">Kembali ke Katalog</NuxtLink>
      </div>

    </div>
  </div>
</template>

<script lang="ts" setup>
import { Loading, Warning } from '@element-plus/icons-vue'
import { useAuth } from '../../composables/useAuth'

definePageMeta({ layout: 'public' })

const route = useRoute()
const { apiFetch, unwrap } = useApi()
const { authUser, isApprovedPL, isPendingPL } = useAuth()
const baseUrlLaravel = 'http://127.0.0.1:8000'

// Mengambil detail buku berdasarkan slug dari URL
const { data: book, status } = await useAsyncData(`book-detail-${route.params.slug}`, async () => {
  try {
    const response = await apiFetch<any>(`/public/books/${route.params.slug}`)
    
    // Kita coba ambil datanya langsung tanpa unwrap dulu untuk tes
    // Biasanya Laravel Resource membungkus di response.data
    const data = response.data || response 
    
    if (!data || Object.keys(data).length === 0) return null

    // --- LOGIKA PINTAR GAMBAR ---
    let path = data.image?.path || data.thumbnail?.path || ''
    if (path.startsWith('public/')) path = path.replace('public/', 'storage/')
    if (path && !path.startsWith('http') && !path.startsWith('storage/')) {
        path = 'storage/' + path
    }
    const fullImageUrl = path.startsWith('http') ? path : `${baseUrlLaravel}/${path}`
    // ----------------------------

    return {
      ...data,
      coverUrl: path ? fullImageUrl : null,
      type: data.category?.name || data.type || 'Umum',
      // Pastikan variabel harga sinkron dengan database partner
      member_price: data.member_price || data.price_le || 0 
    }
  } catch (err) {
    console.error('Gagal memuat detail buku:', err)
    return null
  }
})

// Fungsi format rupiah
const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', maximumFractionDigits: 0
  }).format(price)
}

// Logika Tambah ke Keranjang
const handleAddToCart = () => {
  if (!authUser.value) {
    ElMessage.warning('Silakan masuk untuk mulai berbelanja.')
    return navigateTo('/login')
  }
  ElMessage.success('Berhasil ditambahkan ke keranjang')
}

// SEO: Judul halaman otomatis mengikuti judul buku
useHead({
  title: computed(() => book.value ? `${book.value.title} - ABC Jakarta` : 'Detail Buku'),
})
</script>

<style scoped>
/* Styling tambahan untuk v-html agar rapi */
:deep(.prose p) {
  margin-bottom: 1rem;
}
</style>