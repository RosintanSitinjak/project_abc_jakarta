<template>
  <div class="min-h-screen bg-[#f8fafc] py-12 md:py-20 text-gray-800">
    <div class="container mx-auto px-6 max-w-5xl">
      <!-- Navigasi Atas / Breadcrumb -->
      <div class="mb-8 flex items-center justify-between">
        <NuxtLink to="/katalog" class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-[#00a9c3] transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          <span>Lanjut Belanja Buku</span>
        </NuxtLink>
        <span class="text-xs font-black uppercase tracking-widest text-[#00a9c3]">Keranjang Belanja</span>
      </div>

      <!-- KONDISI 1: KERANJANG KOSONG -->
      <div v-if="cart.length === 0" class="text-center py-20 bg-white rounded-3xl border border-gray-100 shadow-sm p-8 max-w-md mx-auto">
        <div class="w-16 h-16 bg-blue-50 text-[#00a9c3] rounded-full flex items-center justify-center mx-auto mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
        </div>
        <h2 class="text-xl font-extrabold text-gray-900 mb-2">Keranjang Belanja Kosong</h2>
        <p class="text-sm text-gray-500 mb-6">Pilih buku rohani dan kesehatan terbaik dari katalog kami.</p>
        <NuxtLink to="/katalog" class="inline-block px-6 py-3 rounded-xl bg-[#00a9c3] text-white text-xs font-bold uppercase tracking-wider shadow-md hover:bg-[#0092a8] transition">
          Buka Katalog Buku
        </NuxtLink>
      </div>

      <!-- KONDISI 2: ADA BUKU DI KERANJANG -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- List Daftar Buku (8 Kolom) -->
        <div class="lg:col-span-8 space-y-4">
          <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
            <h1 class="text-base font-black text-gray-900">Daftar Buku ({{ totalItems }} barang)</h1>
            <button @click="clearCart" class="text-xs text-rose-500 font-bold hover:underline">
              Kosongkan Semua
            </button>
          </div>

          <div 
            v-for="item in cart" 
            :key="item.id" 
            class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center gap-5"
          >
            <!-- Sampul Buku -->
            <img 
              :src="item.coverUrl || '/images/placeholder-buku.png'" 
              :alt="item.title"
              class="w-20 h-28 object-cover rounded-xl border border-gray-100 shrink-0 bg-gray-50" 
            />

            <!-- Informasi Buku -->
            <div class="flex-1 text-center sm:text-left">
              <h3 class="font-bold text-gray-900 text-sm sm:text-base leading-snug line-clamp-2">
                {{ item.title }}
              </h3>
              <p class="text-xs text-gray-400 mt-0.5">Penulis: {{ item.author || 'ABC Jakarta' }}</p>

              <!-- Harga Satuan -->
              <div class="mt-2">
                <!-- Jika Penginjil Approved -->
                <template v-if="isApprovedPL">
                  <span class="text-sm font-black text-gray-900">{{ formatRupiah(item.member_price) }}</span>
                  <span class="text-[11px] text-gray-400 line-through ml-2">{{ formatRupiah(item.price) }}</span>
                  <span class="text-[9px] bg-emerald-50 text-emerald-700 font-bold px-1.5 py-0.5 rounded ml-2 border border-emerald-200">
                    Harga PL
                  </span>
                </template>
                <!-- Harga Umum -->
                <template v-else>
                  <span class="text-sm font-black text-[#00a9c3]">{{ formatRupiah(item.price) }}</span>
                </template>
              </div>
            </div>

            <!-- Kontrol Qty & Tombol Hapus -->
            <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto gap-3">
              <!-- Counter Qty (Dibatasi Stok) -->
              <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden bg-gray-50">
                <button 
                  @click="updateQty(item.id, item.qty - 1)" 
                  class="px-3 py-1.5 text-gray-600 hover:bg-gray-200 transition font-bold"
                  title="Kurang 1"
                >
                  -
                </button>
                <span class="px-3 py-1.5 text-xs font-black text-gray-900 min-w-8 text-center">
                  {{ item.qty }}
                </span>
                <button 
                  :disabled="item.qty >= item.stock"
                  @click="updateQty(item.id, item.qty + 1)" 
                  class="px-3 py-1.5 text-gray-600 hover:bg-gray-200 transition font-bold disabled:opacity-40 disabled:cursor-not-allowed"
                  :title="item.qty >= item.stock ? 'Maksimal sesuai sisa stok' : 'Tambah 1'"
                >
                  +
                </button>
              </div>

              <!-- Info jika sudah mencapai batas stok -->
              <span v-if="item.qty >= item.stock" class="text-[10px] text-amber-600 font-medium">
                Maksimal (Stok: {{ item.stock }})
              </span>

              <!-- Tombol Hapus -->
              <button @click="removeFromCart(item.id)" class="text-xs text-gray-400 hover:text-rose-500 font-medium transition">
                Hapus
              </button>
            </div>
          </div>
        </div>

        <!-- Ringkasan Pesanan (4 Kolom) -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-gray-100 shadow-sm sticky top-24">
          <h2 class="text-base font-black text-gray-900 mb-4 pb-3 border-b border-gray-100">Ringkasan Pesanan</h2>

          <div class="space-y-3 text-xs mb-6">
            <div class="flex justify-between text-gray-500">
              <span>Total Buku</span>
              <span class="font-bold text-gray-900">{{ totalItems }} eks</span>
            </div>
            <div class="flex justify-between text-gray-500">
              <span>Kategori Pembeli</span>
              <span class="font-bold" :class="isApprovedPL ? 'text-emerald-600' : 'text-gray-900'">
                {{ isApprovedPL ? 'Penginjil (Diskon Aktif)' : 'Umum / Jemaat' }}
              </span>
            </div>
            <div class="flex justify-between text-sm font-black text-gray-900 pt-3 border-t border-gray-100">
              <span>Subtotal Belanja</span>
              <span class="text-base text-[#00a9c3]">{{ formatRupiah(subtotal) }}</span>
            </div>
          </div>

          <button 
            @click="handleProceedToCheckout" 
            class="w-full py-4 rounded-2xl bg-[#00a9c3] text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-[#00a9c3]/25 hover:bg-[#0092a8] active:scale-95 transition"
          >
            Lanjut ke Checkout
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ElMessage } from 'element-plus'
import { useAuth } from '~/composables/useAuth'
import { useCart } from '~/composables/useCart'

definePageMeta({ layout: 'public' })

const { authUser, isApprovedPL } = useAuth()
const { cart, updateQty, removeFromCart, clearCart, totalItems, subtotal, formatRupiah } = useCart()

const handleProceedToCheckout = () => {
  // Gatekeeping: Jika belum login, minta login terlebih dahulu
  if (!authUser.value) {
    ElMessage.warning('Silakan login terlebih dahulu untuk menyelesaikan pesanan.')
    return navigateTo('/login')
  }
  navigateTo('/checkout')
}
</script>