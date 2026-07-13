<template>
  <div class="min-h-screen bg-white py-20">
    <div class="container mx-auto px-6 max-w-5xl">
      <div v-if="status === 'pending'" class="text-center py-20">
        <el-icon class="is-loading text-3xl"><Loading /></el-icon>
      </div>

      <div v-else-if="book" class="grid grid-cols-1 md:grid-cols-2 gap-12">
        <!-- KIRI: Gambar Buku -->
        <div class="bg-gray-50 rounded-3xl p-8 flex items-center justify-center border border-gray-100 shadow-sm">
          <img :src="book.coverUrl" class="w-full max-w-[350px] shadow-2xl rounded-lg object-cover" />
        </div>

        <!-- KANAN: Informasi Buku -->
        <div class="flex flex-col">
          <span class="text-[#00a9c3] font-bold uppercase tracking-widest text-xs mb-2">{{ book.type }}</span>
          <h1 class="text-4xl font-black text-gray-900 mb-6 leading-tight">{{ book.title }}</h1>
          
          <!-- Harga Detail -->
          <div class="mb-8 p-6 bg-slate-50 rounded-2xl border border-slate-100">
             <template v-if="isApprovedPL">
                <p class="text-xs text-green-600 font-bold uppercase mb-1">Harga Khusus Penginjil</p>
                <p class="text-3xl font-black text-gray-900">{{ formatPrice(book.member_price) }}</p>
                <p class="text-sm text-gray-400 line-through">Harga Normal: {{ formatPrice(book.price) }}</p>
             </template>
             <template v-else>
                <p class="text-xs text-gray-400 font-bold uppercase mb-1">Harga Satuan</p>
                <p class="text-3xl font-black text-[#00a9c3]">{{ formatPrice(book.price) }}</p>
                <p v-if="isPendingPL" class="text-xs text-orange-500 mt-2 italic">Akun Anda sedang diverifikasi untuk harga khusus.</p>
             </template>
          </div>

          <!-- Deskripsi -->
          <div class="prose max-w-none text-gray-600 mb-10">
            <h3 class="text-gray-900 font-bold mb-2">Deskripsi Buku</h3>
            <div v-html="book.description"></div>
          </div>

          <!-- Tombol Aksi -->
          <el-button type="primary" class="!h-14 !rounded-2xl !bg-[#00a9c3] font-bold text-lg">
            TAMBAH KE KERANJANG
          </el-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Loading } from '@element-plus/icons-vue'

definePageMeta({ layout: 'public' })
const route = useRoute()
const { apiFetch, unwrap } = useApi()
const { isApprovedPL, isPendingPL } = useAuth()
const baseUrlLaravel = 'http://127.0.0.1:8000'

const { data: book, status } = await useAsyncData(`book-${route.params.slug}`, async () => {
  const response = await apiFetch<any>(`/public/books/${route.params.slug}`)
  const data = unwrap(response)
  if (!data) return null

  // Logika Gambar yang sama
  let path = data.image?.path || data.thumbnail?.path || ''
  if (path.startsWith('public/')) path = path.replace('public/', 'storage/')
  if (path && !path.startsWith('http') && !path.startsWith('storage/')) path = 'storage/' + path
  const fullImageUrl = path.startsWith('http') ? path : `${baseUrlLaravel}/${path}`

  return {
    ...data,
    coverUrl: path ? fullImageUrl : null,
    member_price: data.member_price || data.price_le || 0
  }
})

const formatPrice = (p: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(p)
</script>