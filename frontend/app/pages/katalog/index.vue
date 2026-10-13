<template>
  <div class="min-h-screen bg-[#f2f2f2] text-gray-700 pb-24 relative overflow-hidden">
    <!-- Elemen Ornamen Halus Latar Belakang -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-1/3 left-0 w-80 h-80 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10"></div>

    <!-- 1. HEADER BANNER KATALOG -->
    <section class="py-12 md:py-16 text-center relative z-10">
      <div class="container mx-auto px-6 max-w-4xl">
        <span class="inline-block px-3 py-1 rounded-full bg-[#00a9c3]/10 text-[#00a9c3] text-[10px] font-black uppercase tracking-[0.2em] mb-4 border border-[#00a9c3]/20">
          Koleksi Pustaka
        </span>

        <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 tracking-tight leading-tight max-w-3xl mx-auto">
          Katalog <span class="text-[#00a9c3] italic font-serif">Buku</span> Pilihan
        </h1>
        
        <div class="w-12 h-[3px] bg-[#00a9c3] mx-auto mt-4 mb-5 rounded-full"></div>

        <p class="text-gray-500 max-w-2xl mx-auto text-sm md:text-base leading-relaxed">
          Temukan ragam literatur berkualitas, buku panduan praktis, hingga jurnal ilmiah yang dikurasi langsung oleh tim ahli kami.
        </p>

        <!-- FITUR BARU: SEARCH BAR -->
        <div class="mt-8 max-w-lg mx-auto">
          <div class="flex items-center bg-white rounded-2xl border border-gray-200/70 shadow-sm px-4 py-2 focus-within:border-[#00a9c3] transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Cari judul buku atau nama penulis..." 
              class="w-full px-3 py-1.5 text-xs text-gray-800 outline-none bg-transparent"
            />
            <button v-if="searchQuery" @click="searchQuery = ''" class="text-[11px] text-gray-400 font-bold hover:text-gray-600">
              ✕
            </button>
          </div>
        </div>

        <!-- FITUR BARU: FILTER KATEGORI -->
        <div class="flex items-center justify-center gap-2 flex-wrap mt-5">
          <button
            v-for="cat in categoryList"
            :key="cat"
            @click="selectedCategory = cat"
            :class="[
              'px-3.5 py-1.5 rounded-full text-xs font-bold transition-all',
              selectedCategory === cat
                ? 'bg-[#00a9c3] text-white shadow-sm'
                : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200/60'
            ]"
          >
            {{ cat }}
          </button>
        </div>
      </div>
    </section>

    <!-- 2. DAFTAR KATALOG GRID -->
    <div class="max-w-6xl mx-auto px-6 sm:px-6 lg:px-8 relative z-20">
      
      <!-- State: Loading -->
      <div v-if="status === 'pending'" class="flex flex-col justify-center items-center py-24 space-y-4">
        <el-icon class="is-loading text-3xl text-[#00a9c3]"><Loading /></el-icon>
        <span class="text-xs tracking-widest uppercase text-gray-400 font-bold">Memuat Katalog Buku...</span>
      </div>

      <!-- State: Kosong / Tidak Ketemu -->
      <div v-else-if="filteredBooks.length === 0" class="text-center py-20 bg-white rounded-2xl border border-gray-200/60 shadow-sm p-8 max-w-md mx-auto">
        <div class="w-12 h-12 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <p class="text-gray-900 text-base font-bold">Buku Tidak Ditemukan</p>
        <p class="text-gray-400 text-xs mt-1">Coba kata kunci pencarian atau kategori lain.</p>
      </div>

      <!-- State: Grid Buku Berhasil Dimuat -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
          v-for="book in filteredBooks" 
          :key="book.id"
          class="group bg-white rounded-2xl border border-gray-200/50 overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col h-full"
        >
          <!-- Bagian Sampul Buku (Aspek Rasio 3:4) -->
          <div class="relative aspect-[3/4] w-full bg-gray-50 overflow-hidden border-b border-gray-100">
            <img 
              v-if="book.coverUrl" 
              :src="book.coverUrl" 
              :alt="book.title"
              class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500"
              loading="lazy"
            />
            <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-300 p-4 text-center bg-gray-50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 stroke-current opacity-40 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13" />
              </svg>
              <span class="text-[9px] uppercase tracking-widest font-bold text-gray-400">No Cover Available</span>
            </div>
            
            <!-- Badge Kategori -->
            <span v-if="book.categoryName" class="absolute top-3 left-3 bg-white/95 text-[#00a9c3] text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded shadow-sm border border-gray-100">
              {{ book.categoryName }}
            </span>

            <!-- FITUR BARU: Badge Stok -->
            <span 
              :class="[
                'absolute top-3 right-3 text-[9px] font-black px-2 py-0.5 rounded shadow-sm',
                book.stock > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
              ]"
            >
              {{ book.stock > 0 ? `Sisa ${book.stock}` : 'Habis' }}
            </span>
          </div>

          <!-- Bagian Detail Buku -->
          <div class="p-5 flex-1 flex flex-col justify-between">
            <div class="space-y-1.5">
              <p v-if="book.author" class="text-[11px] text-gray-400 font-medium line-clamp-1">
                {{ book.author }}
              </p>
              <h2 class="text-sm sm:text-base font-bold text-gray-900 line-clamp-2 group-hover:text-[#00a9c3] transition-colors duration-200 leading-snug tracking-tight">
                <NuxtLink :to="`/katalog/${book.slug}`">
                  {{ book.title }}
                </NuxtLink>
              </h2>
              <p class="text-gray-400 text-xs leading-relaxed line-clamp-2">
                {{ cleanDescription(book.description) }}
              </p>
            </div>

            <!-- Bagian Harga & Tombol Aksi -->
            <div class="mt-4 pt-3 border-t border-gray-50 flex flex-col gap-3">
              <!-- Komponen Harga (Sesuai Logika Karla) -->
              <div class="flex flex-col">
                <template v-if="isApprovedPL">
                  <span class="text-[9px] text-green-600 font-black uppercase tracking-wider">Harga Khusus Penginjil</span>
                  <div class="flex items-center gap-1.5">
                    <span class="text-sm font-extrabold text-gray-900">{{ formatPrice(book.member_price) }}</span>
                    <span class="text-[10px] text-gray-400 line-through">{{ formatPrice(book.price) }}</span>
                  </div>
                </template>

                <template v-else-if="isPendingPL">
                  <span class="text-sm font-extrabold text-[#00a9c3]">{{ formatPrice(book.price) }}</span>
                  <span class="text-[8px] text-orange-500 font-bold italic leading-none mt-0.5">Verifikasi akun tertunda</span>
                </template>

                <template v-else>
                  <span class="text-[9px] text-gray-400 uppercase tracking-wider font-medium">Harga</span>
                  <span class="text-sm font-extrabold text-[#00a9c3]">
                    {{ formatPrice(book.price) }}
                  </span>
                </template>
              </div>

              <!-- Tombol Aksi: + Keranjang & Detail -->
              <div class="flex items-center gap-2">
                <button
                  :disabled="book.stock <= 0"
                  @click="addToCart(book)"
                  :class="[
                    'flex-1 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5',
                    book.stock > 0
                      ? 'bg-[#00a9c3] text-white hover:bg-[#0092a8] active:scale-95 shadow-sm'
                      : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                  ]"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                  <span>{{ book.stock > 0 ? '+ Keranjang' : 'Habis' }}</span>
                </button>

                <NuxtLink 
                  :to="`/katalog/${book.slug}`"
                  class="py-2 px-3 rounded-xl border border-gray-200 text-gray-600 hover:text-[#00a9c3] hover:border-[#00a9c3] text-xs font-bold transition flex items-center gap-1"
                >
                  Detail
                </NuxtLink>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- FLOATING CART BUTTON -->
    <NuxtLink 
      to="/cart"
      v-if="totalItems > 0"
      class="fixed bottom-6 right-6 z-50 bg-[#00a9c3] text-white px-5 py-3 rounded-full shadow-2xl flex items-center gap-3 hover:scale-105 active:scale-95 transition-all group"
    >
      <div class="relative">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
        <span class="absolute -top-2 -right-2 bg-rose-500 text-white text-[10px] font-black h-4 w-4 rounded-full flex items-center justify-center">
          {{ totalItems }}
        </span>
      </div>
      <div class="text-left text-xs font-bold leading-none">
        <span class="block text-[9px] opacity-80 uppercase">Keranjang</span>
        <span class="text-xs font-black">{{ formatPrice(subtotal) }}</span>
      </div>
    </NuxtLink>
  </div>
</template>

<script lang="ts" setup>
import { Loading } from '@element-plus/icons-vue'
import { useApi } from '../../composables/useApi'
import { useAuth } from '../../composables/useAuth'
import { useCart } from '../../composables/useCart'

definePageMeta({ layout: 'public' })

const { apiFetch, unwrap } = useApi()
const { isApprovedPL, isPendingPL } = useAuth()
const { addToCart, totalItems, subtotal } = useCart()

const baseUrlLaravel = 'http://127.0.0.1:8000'

// Filter Kategori & Search
const categoryList = ['Semua', 'Rohani', 'Kesehatan', 'Pendidikan', 'Anak-anak', 'Sekolah Sabat']
const selectedCategory = ref('Semua')
const searchQuery = ref('')

const { data: rawBooks, status } = await useAsyncData('public-books', async () => {
  try {
    const response = await apiFetch<any>('/public/books') 
    const rawData = unwrap(response)
    const booksArray = rawData?.data || rawData || []

    return booksArray.map((item: any) => {
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
        author: item.author || '',
        description: item.description || '',
        categoryName: item.category?.name || item.type || 'Rohani',
        price: Number(item.price) || 0, 
        member_price: Number(item.member_price) || Number(item.price_le) || Number(item.price) || 0,
        stock: item.stock !== undefined ? Number(item.stock) : 99,
        coverUrl: path ? fullImageUrl : null
      }
    })
  } catch (err) {
    console.error('Gagal memuat katalog:', err)
    return []
  }
})

const books = computed(() => rawBooks.value || [])

// Logika Filter & Search
const filteredBooks = computed(() => {
  return books.value.filter((b: any) => {
    const matchCat = selectedCategory.value === 'Semua' || 
      b.categoryName?.toLowerCase()?.includes(selectedCategory.value.toLowerCase())

    const query = searchQuery.value.trim().toLowerCase()
    const matchSearch = !query || 
      b.title?.toLowerCase()?.includes(query) || 
      b.author?.toLowerCase()?.includes(query)

    return matchCat && matchSearch
  })
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