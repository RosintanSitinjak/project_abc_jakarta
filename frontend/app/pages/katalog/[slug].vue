<template>
  <div class="min-h-screen bg-white py-12 md:py-20">
    <div class="container mx-auto px-6 max-w-6xl">
      
      <!-- Tombol Kembali -->
      <NuxtLink to="/katalog" class="inline-flex items-center gap-2 text-gray-400 hover:text-[#00a9c3] mb-8 group">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span class="text-sm font-bold uppercase">Kembali ke Katalog</span>
      </NuxtLink>

      <!-- 1. State: Sedang Memuat (Loading) -->
      <div v-if="status === 'pending'" class="flex justify-center items-center py-40">
        <el-icon class="is-loading text-4xl text-[#00a9c3]"><Loading /></el-icon>
      </div>

      <!-- 2. State: Data Buku Ditemukan -->
      <div v-else-if="book" class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-start">
        
        <!-- Gambar Buku -->
        <div class="bg-gray-50 rounded-[40px] p-8 border border-gray-100 flex justify-center">
          <img 
            :src="book.coverUrl" 
            :alt="book.title" 
            class="w-full max-w-[400px] h-auto shadow-2xl rounded-xl object-cover"
          />
        </div>

        <!-- Detail Info -->
        <div class="flex flex-col">
          <span class="px-3 py-1 bg-[#00a9c3]/10 text-[#00a9c3] text-[10px] font-black uppercase rounded-full border border-[#00a9c3]/20 w-max mb-4">
            {{ book.type }}
          </span>

          <h1 class="text-3xl md:text-5xl font-black text-gray-900 mb-6 tracking-tight">
            {{ book.title }}
          </h1>

          <!-- Harga -->
          <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 mb-10">
            <!-- Jika Penginjil Approved -->
            <div v-if="isApprovedPL">
              <p class="text-[10px] text-green-600 font-black uppercase mb-2">Harga Khusus Penginjil</p>
              <div class="flex items-baseline gap-3">
                <span class="text-4xl font-black text-gray-900">{{ formatPrice(book.member_price) }}</span>
                <span class="text-lg text-gray-400 line-through decoration-red-400/40">{{ formatPrice(book.price) }}</span>
              </div>
            </div>
            <!-- Jika Umum -->
            <div v-else>
              <p class="text-[10px] text-gray-400 font-black uppercase mb-2">Harga Satuan</p>
              <span class="text-4xl font-black text-[#00a9c3]">{{ formatPrice(book.price) }}</span>
              <p v-if="isPendingPL" class="text-xs text-orange-500 mt-4 italic font-medium">
                Akun Anda sedang menunggu verifikasi untuk harga khusus.
              </p>
            </div>
          </div>

          <!-- Deskripsi -->
          <div class="mb-10">
            <h3 class="text-sm font-black uppercase text-gray-900 mb-4 pb-2 border-b w-max border-[#00a9c3]">Sinopsis</h3>
            <div class="prose text-gray-600 leading-relaxed italic" v-html="book.description"></div>
          </div>

          <el-button type="primary" class="!h-16 !rounded-2xl !bg-[#00a9c3] !border-none text-lg font-black uppercase shadow-xl" @click="handleAddToCart">
            Tambah ke Keranjang
          </el-button>
        </div>
      </div>

      <!-- 3. State: Jika Buku Tidak Ditemukan -->
      <div v-else class="text-center py-40 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
        <h2 class="text-2xl font-bold text-gray-400">Maaf, data buku tidak ditemukan.</h2>
        <p class="text-gray-400 text-sm mt-2 mb-6">Slug: {{ $route.params.slug }}</p>
        <NuxtLink to="/katalog" class="px-6 py-2 bg-[#00a9c3] text-white rounded-lg font-bold">Kembali ke Katalog</NuxtLink>
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

const { data: book, status } = await useAsyncData(`book-detail-${route.params.slug}`, async () => {
  try {
    // Memanggil API berdasarkan slug
    const response = await apiFetch<any>(`/public/books/${route.params.slug}`)
    
    // PENTING: Laravel biasanya membungkus data di dalam "data"
    const data = response?.data || response
    
    // Cek jika data kosong
    if (!data || !data.id) return null

    // LOGIKA GAMBAR (Agar pasti muncul di laptop kamu)
    let path = data.image?.path || data.thumbnail?.path || ''
    if (path.startsWith('public/')) path = path.replace('public/', 'storage/')
    if (path && !path.startsWith('http') && !path.startsWith('storage/')) {
        path = 'storage/' + path
    }
    const fullImageUrl = path.startsWith('http') ? path : `${baseUrlLaravel}/${path}`

    return {
      id: data.id,
      title: data.title,
      description: data.description || 'Tidak ada deskripsi.',
      price: data.price || 0,
      member_price: data.member_price || data.price_le || 0,
      type: data.category?.name || data.type || 'Katalog',
      coverUrl: path ? fullImageUrl : '/images/placeholder-buku.png'
    }
  } catch (err) {
    console.error('Error Fetch Detail:', err)
    return null
  }
})

const formatPrice = (p: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p)

const handleAddToCart = () => {
  if (!authUser.value) {
    ElMessage.warning('Silakan login terlebih dahulu.')
    return navigateTo('/login')
  }
  ElMessage.success('Masuk keranjang!')
}
</script>