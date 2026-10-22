<template>
  <div class="min-h-screen bg-[#f8fafc] py-10 md:py-16 text-gray-800">
    <div class="container mx-auto px-6 max-w-5xl">
      
      <!-- Navigasi / Header -->
      <div class="mb-8 flex items-center justify-between">
        <NuxtLink to="/cart" class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-[#00a9c3] transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          <span>Kembali ke Keranjang</span>
        </NuxtLink>
        <span class="text-xs font-black uppercase tracking-widest text-[#00a9c3]">Tahap Pembayaran</span>
      </div>

      <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Checkout Pesanan</h1>
        <p class="text-gray-500 text-xs sm:text-sm mt-1">Periksa data pemesan, opsi pengiriman, dan metode pembayaran Anda.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- KOLOM KIRI: FORMULIR CHECKOUT (8 Kolom) -->
        <div class="lg:col-span-8 space-y-6">
          
          <!-- 1. DATA PEMESAN -->
          <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
              <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider">1. Data Pemesan</h2>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-[#00a9c3] border border-blue-100 uppercase">
                Tipe: {{ customerTypeLabel }}
              </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div>
                <label class="block text-gray-400 font-bold uppercase tracking-wider text-[10px] mb-1">Nama Lengkap / Instansi</label>
                <input 
                  v-model="orderForm.customer_name" 
                  type="text" 
                  class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-800 font-bold outline-none cursor-not-allowed"
                  readonly
                />
              </div>
              <div>
                <label class="block text-gray-400 font-bold uppercase tracking-wider text-[10px] mb-1">Alamat Email</label>
                <input 
                  v-model="orderForm.customer_email" 
                  type="email" 
                  class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-800 font-bold outline-none cursor-not-allowed"
                  readonly
                />
              </div>
              <div class="sm:col-span-2">
                <label class="block text-gray-700 font-bold text-xs mb-1">
                  Nomor Telepon / WhatsApp <span class="text-rose-500">*</span>
                </label>
                <input 
                  v-model="orderForm.customer_phone" 
                  type="tel" 
                  placeholder="Contoh: 081234567890 (Untuk konfirmasi paket)" 
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#00a9c3] focus:ring-2 focus:ring-[#00a9c3]/20 outline-none text-xs transition"
                />
              </div>
            </div>
          </div>

          <!-- 2. OPSI PENGIRIMAN -->
          <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">
              2. Opsi Pengiriman
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
              <!-- Opsi A: Ambil Sendiri (Pick Up Tebet) -->
              <div 
                @click="orderForm.shipping_method = 'pickup'" 
                :class="[
                  'p-5 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between',
                  orderForm.shipping_method === 'pickup' 
                    ? 'border-[#00a9c3] bg-[#00a9c3]/5 shadow-sm' 
                    : 'border-gray-200/80 hover:border-gray-300 bg-white'
                ]"
              >
                <div>
                  <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0" :class="orderForm.shipping_method === 'pickup' ? 'border-[#00a9c3]' : 'border-gray-300'">
                      <div v-if="orderForm.shipping_method === 'pickup'" class="w-2 h-2 rounded-full bg-[#00a9c3]"></div>
                    </div>
                    <h3 class="font-black text-xs sm:text-sm text-gray-900 leading-snug">Ambil Sendiri (Pick Up)</h3>
                  </div>
                  <p class="text-[11px] text-gray-500 leading-relaxed pl-6.5">
                    Ambil langsung di kantor ABC Jakarta (Tebet, Jl. Dr. Saharjo).
                  </p>
                </div>
                <span class="mt-3 text-[10px] font-black uppercase text-emerald-600 pl-6.5">Bebas Ongkir (Rp 0)</span>
              </div>

              <!-- Opsi B: Ekspedisi / Kurir Pengiriman -->
              <div 
                @click="orderForm.shipping_method = 'delivery'" 
                :class="[
                  'p-5 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between',
                  orderForm.shipping_method === 'delivery' 
                    ? 'border-[#00a9c3] bg-[#00a9c3]/5 shadow-sm' 
                    : 'border-gray-200/80 hover:border-gray-300 bg-white'
                ]"
              >
                <div>
                  <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0" :class="orderForm.shipping_method === 'delivery' ? 'border-[#00a9c3]' : 'border-gray-300'">
                      <div v-if="orderForm.shipping_method === 'delivery'" class="w-2 h-2 rounded-full bg-[#00a9c3]"></div>
                    </div>
                    <h3 class="font-black text-xs sm:text-sm text-gray-900 leading-snug">Ekspedisi / Kurir</h3>
                  </div>
                  <p class="text-[11px] text-gray-500 leading-relaxed pl-6.5">
                    Pengiriman paket diatur langsung oleh pihak kantor ABC Jakarta.
                  </p>
                </div>
                <span class="mt-3 text-[10px] font-bold text-[#00a9c3] pl-6.5">Tarif Kurir Resmi</span>
              </div>
            </div>

            <!-- Detail jika Ambil Sendiri -->
            <div v-if="orderForm.shipping_method === 'pickup'" class="p-4 bg-blue-50/70 rounded-2xl border border-blue-100 text-xs text-blue-900 leading-relaxed">
              <p class="font-bold flex items-center gap-1.5 mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#00a9c3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Lokasi Pengambilan Buku:
              </p>
              <p class="text-gray-700 pl-5.5">
                Kantor Adventist Book Center (ABC) Jakarta<br/>
                Jl. Dr. Saharjo No.48, Ps. Manggis, Kec. Setiabudi, Jakarta Selatan 12960<br/>
                <span class="text-gray-500 text-[11px] italic mt-1 block">
                  Operasional: Senin–Kamis 08:00–16:00, Jumat 08:00–13:00 WIB
                </span>
              </p>
            </div>

            <!-- Formulir Alamat & Skema Ongkir jika Memilih Ekspedisi -->
            <div v-else class="space-y-4 pt-4 border-t border-gray-100">
              <div>
                <label class="block text-gray-700 font-bold text-xs mb-2">Skema Pembayaran Ongkos Kirim <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <label 
                    class="p-3.5 rounded-xl border-2 cursor-pointer transition flex items-start gap-2.5"
                    :class="orderForm.shipping_payment_type === 'prepaid' ? 'border-[#00a9c3] bg-[#00a9c3]/5' : 'border-gray-200'"
                  >
                    <input type="radio" value="prepaid" v-model="orderForm.shipping_payment_type" class="mt-0.5 accent-[#00a9c3]" />
                    <div>
                      <span class="font-bold text-xs text-gray-900 block">Bayar Bersama Tagihan</span>
                      <p class="text-[10px] text-gray-500 mt-0.5 leading-snug">
                        Admin ABC Jakarta akan menghitung ongkir riil, lalu tagihan langsung siap Anda bayar via web/transfer.
                      </p>
                    </div>
                  </label>

                  <label 
                    class="p-3.5 rounded-xl border-2 cursor-pointer transition flex items-start gap-2.5"
                    :class="orderForm.shipping_payment_type === 'cod' ? 'border-[#00a9c3] bg-[#00a9c3]/5' : 'border-gray-200'"
                  >
                    <input type="radio" value="cod" v-model="orderForm.shipping_payment_type" class="mt-0.5 accent-[#00a9c3]" />
                    <div>
                      <span class="font-bold text-xs text-gray-900 block">Bayar di Tempat (COD Kurir)</span>
                      <p class="text-[10px] text-gray-500 mt-0.5 leading-snug">
                        Hanya bayar harga buku sekarang. Ongkos kirim dibayarkan tunai ke kurir saat paket sampai.
                      </p>
                    </div>
                  </label>
                </div>
              </div>

              <div>
                <label class="block text-gray-700 font-bold text-xs mb-1">
                  Alamat Lengkap Pengiriman <span class="text-rose-500">*</span>
                </label>
                <textarea 
                  v-model="orderForm.shipping_address" 
                  rows="3" 
                  placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..."
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#00a9c3] focus:ring-2 focus:ring-[#00a9c3]/20 outline-none text-xs transition"
                ></textarea>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 font-bold text-xs mb-1">
                    Kota / Kabupaten <span class="text-rose-500">*</span>
                  </label>
                  <input 
                    v-model="orderForm.shipping_city" 
                    type="text" 
                    placeholder="Contoh: Jakarta Selatan / Bandung" 
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a9c3] outline-none text-xs transition"
                  />
                </div>
                <div>
                  <label class="block text-gray-700 font-bold text-xs mb-1">Kode Pos</label>
                  <input 
                    v-model="orderForm.shipping_postal_code" 
                    type="text" 
                    placeholder="Contoh: 12960" 
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a9c3] outline-none text-xs transition"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- 3. METODE PEMBAYARAN -->
          <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">
              3. Metode Pembayaran
            </h2>

            <div class="space-y-3">
              <!-- Transfer Bank Mandiri (Semua Akun) -->
              <label 
                class="flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition"
                :class="orderForm.payment_method === 'transfer' ? 'border-[#00a9c3] bg-[#00a9c3]/5' : 'border-gray-200/80 hover:border-gray-300'"
              >
                <input 
                  type="radio" 
                  value="transfer" 
                  v-model="orderForm.payment_method" 
                  class="mt-1 accent-[#00a9c3]" 
                />
                <div>
                  <span class="font-bold text-gray-900 text-xs block">Transfer Bank Mandiri (Lunas)</span>
                  <p class="text-gray-400 text-[11px] mt-0.5">
                    Rekening resmi Mandiri: 123-00-9876543-2 a.n. Adventist Book Center Jakarta.
                  </p>
                </div>
              </label>

              <!-- QRIS (Semua Akun) -->
              <label 
                class="flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition"
                :class="orderForm.payment_method === 'qris' ? 'border-[#00a9c3] bg-[#00a9c3]/5' : 'border-gray-200/80 hover:border-gray-300'"
              >
                <input 
                  type="radio" 
                  value="qris" 
                  v-model="orderForm.payment_method" 
                  class="mt-1 accent-[#00a9c3]" 
                />
                <div>
                  <span class="font-bold text-gray-900 text-xs block">QRIS ABC Jakarta (Lunas)</span>
                  <p class="text-gray-400 text-[11px] mt-0.5">
                    Pembayaran instan melalui GoPay, OVO, Dana, BCA Mobile, atau e-Wallet lainnya.
                  </p>
                </div>
              </label>

              <!-- Tunai (Cash) Khusus Pick Up di Kantor -->
              <label 
                v-if="orderForm.shipping_method === 'pickup'"
                class="flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition"
                :class="orderForm.payment_method === 'cash' ? 'border-[#00a9c3] bg-[#00a9c3]/5' : 'border-gray-200/80 hover:border-gray-300'"
              >
                <input 
                  type="radio" 
                  value="cash" 
                  v-model="orderForm.payment_method" 
                  class="mt-1 accent-[#00a9c3]" 
                />
                <div>
                  <span class="font-bold text-gray-900 text-xs block">Bayar Tunai di Kasir Kantor</span>
                  <p class="text-gray-400 text-[11px] mt-0.5">
                    Pembayaran langsung di kasir ABC Jakarta saat mengambil buku fisik.
                  </p>
                </div>
              </label>

              <!-- Opsi Kredit / Tempo (Khusus Mitra PL, Gereja, Sekolah) -->
              <label 
                v-if="canUseCredit"
                class="flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition"
                :class="orderForm.payment_method === 'kredit' ? 'border-[#00a9c3] bg-[#00a9c3]/5' : 'border-gray-200/80 hover:border-gray-300'"
              >
                <input 
                  type="radio" 
                  value="kredit" 
                  v-model="orderForm.payment_method" 
                  class="mt-1 accent-[#00a9c3]" 
                />
                <div>
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-900 text-xs">Kredit / Tempo (Fasilitas Khusus Kemitraan)</span>
                    <span class="text-[9px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.5 rounded">Tersedia</span>
                  </div>
                  <p class="text-gray-400 text-[11px] mt-0.5">
                    Cicilan tempo siklus 30 hari (Maks. Plafon Rp 5.000.000 untuk PL).
                  </p>
                </div>
              </label>

              <!-- Pesan edukasi jika akun Jemaat -->
              <div v-else class="p-3 bg-gray-50 rounded-xl border border-gray-200/60 text-[11px] text-gray-500 italic">
                *Akun Jemaat Umum wajib menggunakan metode pembayaran tunai/lunas (Transfer, QRIS, atau Bayar di Kasir).
              </div>
            </div>
          </div>

          <!-- Catatan Tambahan -->
          <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
            <label class="block text-gray-700 font-bold text-xs mb-1">Catatan Tambahan Pesanan (Opsional)</label>
            <input 
              v-model="orderForm.notes" 
              type="text" 
              placeholder="Contoh: Titipkan ke satpam kantor / hubungi nomor WhatsApp ini jika tidak aktif..." 
              class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a9c3] outline-none text-xs transition"
            />
          </div>
        </div>

        <!-- KOLOM KANAN: RINGKASAN PESANAN (4 Kolom) -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-gray-100 shadow-sm sticky top-24">
          <h2 class="text-base font-black text-gray-900 mb-4 pb-3 border-b border-gray-100">Ringkasan Pembelian</h2>

          <!-- Info Diskon Khusus PL -->
          <div v-if="isApprovedPL" class="mb-4 p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-800 text-[11px] flex items-center gap-1.5">
            <span class="font-bold">🏷️ Tarif Khusus Penginjil:</span>
            <span>Semua harga buku telah disesuaikan secara otomatis.</span>
          </div>

          <!-- Daftar Buku di Keranjang -->
          <div class="space-y-3 mb-6 max-h-56 overflow-y-auto pr-1">
            <div 
              v-for="item in cart" 
              :key="item.id" 
              class="flex justify-between items-center text-xs text-gray-600 gap-2"
            >
              <div class="flex-1 pr-2">
                <p class="font-bold text-gray-800 line-clamp-1">{{ item.title }}</p>
                <p class="text-[10px] text-gray-400">{{ item.qty }} eks × {{ formatRupiah(isApprovedPL ? item.member_price : item.price) }}</p>
              </div>
              <span class="font-bold text-gray-900 shrink-0">
                {{ formatRupiah((isApprovedPL ? item.member_price : item.price) * item.qty) }}
              </span>
            </div>
          </div>

          <!-- Rincian Biaya -->
          <div class="space-y-2.5 text-xs pt-3 border-t border-gray-100 mb-6">
            <div class="flex justify-between text-gray-500">
              <span>Total Barang</span>
              <span class="font-bold text-gray-900">{{ totalItems }} eks</span>
            </div>
            <div class="flex justify-between text-gray-500">
              <span>Biaya Pengiriman</span>
              <span class="font-bold text-gray-900">
                {{ orderForm.shipping_method === 'pickup' 
                  ? 'Bebas Ongkir (Rp 0)' 
                  : (orderForm.shipping_payment_type === 'cod' ? 'Bayar di Tempat (COD)' : 'Dihitung oleh Admin') }}
              </span>
            </div>
            <div class="flex justify-between text-sm font-black text-gray-900 pt-3 border-t border-gray-100">
              <span>Total Tagihan Buku</span>
              <span class="text-base text-[#00a9c3]">{{ formatRupiah(subtotal) }}</span>
            </div>
          </div>

          <!-- Tombol Konfirmasi Pesanan -->
          <button 
            :disabled="submitting || cart.length === 0"
            @click="handlePlaceOrder"
            class="w-full py-4 rounded-2xl bg-[#00a9c3] text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-[#00a9c3]/30 hover:bg-[#0092a8] active:scale-95 disabled:opacity-50 transition"
          >
            {{ submitting ? 'Memproses Pesanan...' : 'Konfirmasi & Buat Pesanan' }}
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ElMessage, ElNotification } from 'element-plus'
import { useAuth } from '~/composables/useAuth'
import { useCart } from '~/composables/useCart'
import { useApi } from '~/composables/useApi'

definePageMeta({
  layout: 'public',
  middleware: [
    function (to, from) {
      const { authUser } = useAuth()
      if (!authUser.value) {
        if (import.meta.client) {
          ElMessage.warning('Silakan login terlebih dahulu untuk mengakses checkout.')
        }
        return navigateTo('/login')
      }
    }
  ]
})

const { authUser, isApprovedPL, canUseCredit } = useAuth()
const { cart, totalItems, subtotal, clearCart, formatRupiah } = useCart()
const { apiFetch, getErrorMessage } = useApi()

const submitting = ref(false)

const customerTypeLabel = computed(() => {
  const type = authUser.value?.customer?.type || 'jemaat'
  const map: Record<string, string> = {
    jemaat: 'Jemaat Umum',
    gereja: 'Gereja',
    sekolah: 'Sekolah',
    penginjil: 'Penginjil Literatur'
  }
  return map[type] || 'Umum'
})

// Form Checkout State
const orderForm = reactive({
  customer_name: authUser.value?.name || '',
  customer_email: authUser.value?.email || '',
  customer_phone: authUser.value?.customer?.phone || '',
  shipping_method: 'pickup', // 'pickup' atau 'delivery'
  shipping_payment_type: 'prepaid', // 'prepaid' (tagihan) atau 'cod'
  shipping_address: authUser.value?.customer?.address || '',
  shipping_city: '',
  shipping_postal_code: '',
  payment_method: 'transfer', // 'transfer', 'qris', 'cash', 'kredit'
  notes: ''
})

// Proteksi jika Jemaat mencoba memilih kredit
watchEffect(() => {
  if (!canUseCredit.value && orderForm.payment_method === 'kredit') {
    orderForm.payment_method = 'transfer'
  }
})

onMounted(() => {
  if (cart.value.length === 0) {
    ElMessage.info('Keranjang belanja Anda masih kosong.')
    navigateTo('/katalog')
  }
})

// Handler Buat Pesanan
const handlePlaceOrder = async () => {
  if (!orderForm.customer_phone.trim()) {
    ElMessage.warning('Mohon lengkapi nomor Telepon / WhatsApp untuk konfirmasi pesanan.')
    return
  }

  if (orderForm.shipping_method === 'delivery') {
    if (!orderForm.shipping_address.trim() || !orderForm.shipping_city.trim()) {
      ElMessage.warning('Mohon isi alamat lengkap dan kota pengiriman ekspedisi.')
      return
    }
  }

  submitting.value = true
  try {
    const fullShippingAddress = orderForm.shipping_method === 'delivery' 
      ? `${orderForm.shipping_address}, ${orderForm.shipping_city} ${orderForm.shipping_postal_code}`.trim()
      : null

    const payload = {
      customer_id: authUser.value?.customer?.id,
      customer_name: orderForm.customer_name,
      customer_email: orderForm.customer_email,
      customer_phone: orderForm.customer_phone,
      
      delivery_method: orderForm.shipping_method,
      shipping_method: orderForm.shipping_method,
      shipping_payment_type: orderForm.shipping_method === 'pickup' ? 'none' : orderForm.shipping_payment_type,
      shipping_address: fullShippingAddress,
      
      payment_method: orderForm.payment_method === 'credit' ? 'kredit' : orderForm.payment_method,
      source: 'website',
      notes: orderForm.notes,
      total_amount: subtotal.value,
      
      items: cart.value.map((item) => ({
        book_id: item.id,
        qty: item.qty
      }))
    }

    await apiFetch('/orders', {
      method: 'POST',
      body: payload
    })

    // Notifikasi cerdas berdasarkan metode yang dipilih
    let successMsg = 'Pesanan Anda telah dicatat oleh pihak kantor ABC Jakarta.'
    if (orderForm.payment_method === 'kredit') {
      successMsg = 'Pesanan Kredit/Tempo Anda berhasil diajukan dengan tempo 30 hari. Menunggu persetujuan admin untuk pengemasan buku.'
    } else if (orderForm.shipping_method === 'delivery' && orderForm.shipping_payment_type === 'prepaid') {
      successMsg = 'Pesanan Anda telah dicatat. Admin akan segera menghitung ongkos kirim dan menerbitkan tagihan final.'
    } else {
      successMsg = 'Pesanan Anda berhasil dibuat! Silakan lakukan pembayaran dan unggah bukti transfer di menu Pesanan Saya.'
    }

    ElNotification({
      title: 'Pesanan Berhasil Dibuat!',
      message: successMsg,
      type: 'success',
      duration: 6000
    })

    clearCart()
    navigateTo('/katalog')
  } catch (err: any) {
    ElMessage.error(getErrorMessage(err, 'Gagal membuat pesanan. Silakan coba kembali.'))
  } finally {
    submitting.value = false
  }
}
</script>