<template>
  <div class="min-h-screen bg-[#f8fafc] py-10 md:py-16 text-gray-800">
    <div class="container mx-auto px-6 max-w-5xl">
      
      <!-- Navigasi Atas -->
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
        <p class="text-gray-500 text-xs sm:text-sm mt-1">Pilih jalur pengiriman dan selesaikan pesanan literatur Anda.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- KOLOM KIRI (8 Kolom) -->
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
                  Nomor WhatsApp / Telepon <span class="text-rose-500">*</span>
                </label>
                <input 
                  v-model="orderForm.customer_phone" 
                  type="tel" 
                  placeholder="Contoh: 081234567890 (Untuk konfirmasi paket & resi pengiriman)" 
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#00a9c3] focus:ring-2 focus:ring-[#00a9c3]/20 outline-none text-xs transition"
                />
              </div>
            </div>
          </div>

          <!-- 2. PILIHAN JALUR PENGIRIMAN -->
          <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">
              2. Metode Pengiriman
            </h2>

            <div class="space-y-3.5 mb-6">
              
              <!-- JALUR 1: Ambil Sendiri (Pick Up Tebet) -->
              <div 
                @click="setShippingRoute('pickup')" 
                :class="[
                  'p-4 sm:p-5 rounded-2xl border-2 cursor-pointer transition-all flex items-start gap-3.5',
                  orderForm.shipping_route === 'pickup' 
                    ? 'border-[#00a9c3] bg-[#00a9c3]/5 shadow-sm' 
                    : 'border-gray-200 hover:border-gray-300 bg-white'
                ]"
              >
                <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5" :class="orderForm.shipping_route === 'pickup' ? 'border-[#00a9c3]' : 'border-gray-300'">
                  <div v-if="orderForm.shipping_route === 'pickup'" class="w-2 h-2 rounded-full bg-[#00a9c3]"></div>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <h3 class="font-black text-xs sm:text-sm text-gray-900">Jalur 1: Ambil Sendiri (Pick Up)</h3>
                    <span class="text-[10px] font-black uppercase text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Ongkir Rp 0 (Bebas Ongkir)</span>
                  </div>
                  <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                    Total tagihan murni hanya harga buku. Ambil langsung di kantor <b>Adventist Book Center (ABC) Jakarta, Tebet</b>.
                  </p>
                </div>
              </div>

              <!-- JALUR 2: Ekspedisi Opsi A (Bayar Ongkir di Tempat / DFO Kurir) -->
              <div 
                @click="setShippingRoute('delivery_dfo')" 
                :class="[
                  'p-4 sm:p-5 rounded-2xl border-2 cursor-pointer transition-all flex items-start gap-3.5',
                  orderForm.shipping_route === 'delivery_dfo' 
                    ? 'border-[#00a9c3] bg-[#00a9c3]/5 shadow-sm' 
                    : 'border-gray-200 hover:border-gray-300 bg-white'
                ]"
              >
                <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5" :class="orderForm.shipping_route === 'delivery_dfo' ? 'border-[#00a9c3]' : 'border-gray-300'">
                  <div v-if="orderForm.shipping_route === 'delivery_dfo'" class="w-2 h-2 rounded-full bg-[#00a9c3]"></div>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <h3 class="font-black text-xs sm:text-sm text-gray-900">Jalur 2: Ekspedisi – Bayar Ongkir di Tempat (DFO / COD Kurir)</h3>
                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">COD Ongkir</span>
                  </div>
                  <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                    Tagihan web murni seharga buku. Biaya ongkir dibayarkan langsung kepada kurir ekspedisi saat paket tiba di alamat Anda.
                  </p>
                </div>
              </div>

              <!-- JALUR 3: Ekspedisi Opsi B (Bayar Ongkir Sekaligus - Dihitung Admin) -->
              <div 
                @click="setShippingRoute('delivery_prepaid')" 
                :class="[
                  'p-4 sm:p-5 rounded-2xl border-2 cursor-pointer transition-all flex items-start gap-3.5',
                  orderForm.shipping_route === 'delivery_prepaid' 
                    ? 'border-[#00a9c3] bg-[#00a9c3]/5 shadow-sm' 
                    : 'border-gray-200 hover:border-gray-300 bg-white'
                ]"
              >
                <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5" :class="orderForm.shipping_route === 'delivery_prepaid' ? 'border-[#00a9c3]' : 'border-gray-300'">
                  <div v-if="orderForm.shipping_route === 'delivery_prepaid'" class="w-2 h-2 rounded-full bg-[#00a9c3]"></div>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <h3 class="font-black text-xs sm:text-sm text-gray-900">Jalur 3: Ekspedisi – Bayar Ongkir Sekaligus (Dihitung Admin)</h3>
                    <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">1 Kali Transfer</span>
                  </div>
                  <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                    Pesanan dibuat terlebih dahulu (Belum bayar). Admin akan menimbang buku & menginput tarif ongkir, lalu total akhir dibayar di halaman <b>Pesanan Saya</b>.
                  </p>
                </div>
              </div>

            </div>

            <!-- DETAIL KONDISIONAL BERDASARKAN JALUR PENGIRIMAN -->

            <!-- Form Alamat untuk Ekspedisi (Jalur 2 & Jalur 3) -->
            <div v-if="orderForm.shipping_route !== 'pickup'" class="space-y-4 pt-4 border-t border-gray-100">
              <h3 class="font-bold text-xs text-gray-900">Alamat Lengkap Tujuan Pengiriman Ekspedisi</h3>
              <div>
                <label class="block text-gray-700 font-bold text-xs mb-1">
                  Alamat Lengkap <span class="text-rose-500">*</span>
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
                    placeholder="Contoh: Jakarta Selatan" 
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

              <!-- Banner Info Jalur 2 (DFO) -->
              <div v-if="orderForm.shipping_route === 'delivery_dfo'" class="p-3.5 bg-blue-50/80 rounded-2xl border border-blue-200 text-xs text-blue-900 leading-relaxed">
                🚚 <b>Keterangan Ekspedisi DFO:</b> Biaya ongkir dibayarkan langsung kepada kurir ekspedisi saat paket tiba di alamat Anda.
              </div>

              <!-- Banner Info Jalur 3 (Ongkir Sekaligus) -->
              <div v-if="orderForm.shipping_route === 'delivery_prepaid'" class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900 leading-relaxed">
                ⏳ <b>Keterangan Ongkir Sekaligus:</b> Pesanan dibuat dengan status <b>Menunggu Konfirmasi Ongkir (Belum bayar)</b>. Admin akan menimbang buku, menginput ongkir, lalu Anda mentransfer total akhir di menu <b>Pesanan Saya (My Orders)</b>.
              </div>
            </div>

            <!-- Detail Ambil Sendiri (Jalur 1) -->
            <div v-else class="p-4 bg-emerald-50/60 rounded-2xl border border-emerald-200 text-xs text-emerald-950 leading-relaxed">
              📍 <b>Lokasi Pengambilan Buku:</b><br/>
              <b>Kantor Adventist Book Center (ABC) Jakarta</b><br/>
              Jl. Dr. Saharjo No.48, Ps. Manggis, Kec. Setiabudi, Jakarta Selatan 12960<br/>
              <span class="text-gray-600 text-[11px] italic mt-1 block">
                Operasional: Senin–Kamis 08:00–16:00, Jumat 08:00–13:00 WIB
              </span>
            </div>
          </div>

          <!-- 3. METODE PEMBAYARAN -->
          <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">
              3. Metode Pembayaran
            </h2>

            <!-- KONDISI JALUR 3: Pembayaran Ditunda sampai Admin Input Ongkir -->
            <div v-if="orderForm.shipping_route === 'delivery_prepaid'" class="p-4 bg-gray-50 rounded-2xl border border-gray-200 text-xs text-gray-600 leading-relaxed">
              Pembayaran ditunda sementara sampai admin ABC Jakarta menginput nominal ongkir resmi. Anda akan mentransfer total akhir pada halaman <b>Pesanan Saya</b>.
            </div>

            <!-- KONDISI JALUR 1 & JALUR 2: Pilih Metode Pembayaran Sekarang -->
            <div v-else class="space-y-4">
              
              <!-- Opsi A: Transfer Bank Mandiri / QRIS (Semua User) -->
              <label 
                class="flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition"
                :class="orderForm.payment_method === 'transfer' ? 'border-[#00a9c3] bg-[#00a9c3]/5' : 'border-gray-200/80 hover:border-gray-300'"
              >
                <input type="radio" value="transfer" v-model="orderForm.payment_method" class="mt-1 accent-[#00a9c3]" />
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-gray-900 text-xs block">Transfer Bank Mandiri / QRIS</span>
                    <span class="text-[9px] bg-blue-100 text-[#00a9c3] font-bold px-2 py-0.5 rounded">Instan</span>
                  </div>
                  <p class="text-gray-400 text-[11px] mt-0.5">
                    Transfer ke rekening resmi Bank Mandiri ABC Jakarta sekarang dan lampirkan struk pembayaran.
                  </p>
                </div>
              </label>

              <!-- Opsi B: Tunai saat Ambil di Toko (HANYA MUNCUL DI JALUR 1 PICK UP) -->
              <label 
                v-if="orderForm.shipping_route === 'pickup'"
                class="flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition"
                :class="orderForm.payment_method === 'cash_pickup' ? 'border-[#00a9c3] bg-[#00a9c3]/5' : 'border-gray-200/80 hover:border-gray-300'"
              >
                <input type="radio" value="cash_pickup" v-model="orderForm.payment_method" class="mt-1 accent-[#00a9c3]" />
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-gray-900 text-xs block">Bayar Tunai di Kasir Kantor (Tebet)</span>
                    <span class="text-[9px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded">Bayar di Tempat</span>
                  </div>
                  <p class="text-gray-400 text-[11px] mt-0.5">
                    Pembayaran langsung secara tunai saat mengambil buku di kantor ABC Jakarta.
                  </p>
                </div>
              </label>

              <!-- Opsi C: Kredit / Piutang Tempo (HANYA UNTUK PL, GEREJA, SEKOLAH) -->
              <label 
                v-if="canUseCredit"
                class="flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition"
                :class="orderForm.payment_method === 'credit' ? 'border-[#00a9c3] bg-[#00a9c3]/5' : 'border-gray-200/80 hover:border-gray-300'"
              >
                <input type="radio" value="credit" v-model="orderForm.payment_method" class="mt-1 accent-[#00a9c3]" />
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-gray-900 text-xs block">Kredit / Piutang (Fasilitas Khusus Kemitraan)</span>
                    <span class="text-[9px] bg-purple-100 text-purple-800 font-bold px-2 py-0.5 rounded">Maks Rp 5.000.000</span>
                  </div>
                  <p class="text-gray-400 text-[11px] mt-0.5">
                    Fasilitas pembayaran tempo siklus 30 hari khusus akun Penginjil Literatur / Lembaga.
                  </p>
                </div>
              </label>

              <!-- Pesan jika Jemaat -->
              <div v-else-if="orderForm.shipping_route !== 'pickup'" class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-[11px] text-gray-500 italic">
                *Akun Jemaat Umum menggunakan metode pembayaran Transfer/QRIS.
              </div>

              <!-- ======================================================== -->
              <!-- BOX REKENING MANDIRI & UPLOAD STRUK LANGSUNG             -->
              <!-- ======================================================== -->
              <div v-if="orderForm.payment_method === 'transfer'" class="mt-4 p-5 rounded-3xl bg-slate-900 text-white space-y-4 shadow-md">
                <div>
                  <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Rekening Resmi Pembayaran ABC Jakarta:</p>
                  <div class="flex items-center justify-between font-mono bg-white/10 p-3.5 rounded-2xl border border-white/10">
                    <div>
                      <span class="text-xs text-gray-300 block font-sans">Bank Mandiri (KCP Jakarta Tebet)</span>
                      <span class="text-sm font-black text-[#00a9c3] tracking-wider">123-00-0987654-3</span>
                    </div>
                    <span class="text-[10px] text-gray-300 font-sans">a.n. Adventist Book Center</span>
                  </div>
                </div>

                <!-- Bagian Form Unggah Bukti Struk -->
                <div class="pt-3 border-t border-white/10 space-y-2">
                  <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-gray-200">
                      Unggah Bukti Transfer / Struk Bank <span class="text-rose-400">*</span>
                    </label>
                    <span v-if="proofFile" class="text-[10px] text-emerald-400 font-bold">✓ Foto Terpilih</span>
                  </div>
                  
                  <p class="text-[11px] text-gray-400 leading-relaxed">
                    Silakan transfer sebesar <b class="text-[#00a9c3]">{{ formatRupiah(subtotal) }}</b>, lalu lampirkan foto struk di bawah ini sebelum membuat pesanan:
                  </p>

                  <!-- Input File -->
                  <input 
                    type="file" 
                    accept="image/*" 
                    @change="handleProofChange" 
                    class="w-full text-xs text-gray-300 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#00a9c3] file:text-white hover:file:bg-[#0092a8] cursor-pointer bg-white/5 p-2 rounded-2xl border border-white/10"
                  />

                  <!-- Preview Gambar Struk Transfer -->
                  <div v-if="proofPreview" class="relative mt-3 w-fit">
                    <img :src="proofPreview" alt="Pratinjau Struk" class="max-h-48 rounded-xl border border-white/20 object-contain bg-white/10 p-1" />
                    <button 
                      @click="removeProof" 
                      type="button" 
                      class="absolute -top-2 -right-2 bg-rose-500 text-white rounded-full h-6 w-6 flex items-center justify-center text-xs hover:bg-rose-600 shadow"
                      title="Batalkan foto"
                    >
                      ✕
                    </button>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- Catatan Tambahan Pesanan -->
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

        <!-- KOLOM KANAN: RINGKASAN TAGIHAN (4 Kolom) -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-gray-100 shadow-sm sticky top-24">
          <h2 class="text-base font-black text-gray-900 mb-4 pb-3 border-b border-gray-100">Ringkasan Pembelian</h2>

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
              <span>Total Buku</span>
              <span class="font-bold text-gray-900">{{ totalItems }} eks</span>
            </div>
            
            <div class="flex justify-between text-gray-500">
              <span>Skema Pengiriman</span>
              <span class="font-bold text-right" :class="orderForm.shipping_route === 'pickup' ? 'text-emerald-600' : 'text-gray-900'">
                {{ shippingLabelShort }}
              </span>
            </div>

            <div class="flex justify-between text-gray-500">
              <span>Ongkos Kirim</span>
              <span class="font-bold text-right">
                <template v-if="orderForm.shipping_route === 'pickup'">Rp 0 (Bebas Ongkir)</template>
                <template v-else-if="orderForm.shipping_route === 'delivery_dfo'">Bayar ke Kurir (DFO)</template>
                <template v-else>Dihitung Admin</template>
              </span>
            </div>

            <div class="flex justify-between text-sm font-black text-gray-900 pt-3 border-t border-gray-100">
              <span>{{ orderForm.shipping_route === 'delivery_prepaid' ? 'Tagihan Buku Saja' : 'Total Tagihan' }}</span>
              <span class="text-base text-[#00a9c3]">{{ formatRupiah(subtotal) }}</span>
            </div>

            <p v-if="orderForm.shipping_route === 'delivery_prepaid'" class="text-[10px] text-amber-600 italic leading-tight mt-1">
              *Ongkos kirim akan ditambahkan setelah admin menimbang paket buku Anda.
            </p>
          </div>

          <!-- Tombol Konfirmasi Pesanan -->
          <button 
            :disabled="submitting || cart.length === 0"
            @click="handlePlaceOrder"
            class="w-full py-4 rounded-2xl bg-[#00a9c3] text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-[#00a9c3]/30 hover:bg-[#0092a8] active:scale-95 disabled:opacity-50 transition"
          >
            {{ submitButtonText }}
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

// Penjaga Halaman: Wajib Login
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
const { apiFetch, uploadAttachment, getErrorMessage } = useApi()

const submitting = ref(false)

// Label tipe akun
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

// State Formulir Checkout
const orderForm = reactive({
  customer_name: authUser.value?.name || '',
  customer_email: authUser.value?.email || '',
  customer_phone: authUser.value?.customer?.phone || '',
  // 3 Jalur: 'pickup' | 'delivery_dfo' | 'delivery_prepaid'
  shipping_route: 'pickup',
  shipping_address: authUser.value?.customer?.address || '',
  shipping_city: '',
  shipping_postal_code: '',
  payment_method: 'transfer',
  notes: ''
})

const setShippingRoute = (route: string) => {
  orderForm.shipping_route = route
  if (route !== 'pickup' && orderForm.payment_method === 'cash_pickup') {
    orderForm.payment_method = 'transfer'
  }
}

// State Unggah Bukti Transfer
const proofFile = ref<File | null>(null)
const proofPreview = ref<string | null>(null)

const handleProofChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    proofFile.value = file
    proofPreview.value = URL.createObjectURL(file)
  }
}

const removeProof = () => {
  proofFile.value = null
  if (proofPreview.value) {
    URL.revokeObjectURL(proofPreview.value)
    proofPreview.value = null
  }
}

// Label Singkat di Ringkasan
const shippingLabelShort = computed(() => {
  if (orderForm.shipping_route === 'pickup') return 'Ambil Sendiri'
  if (orderForm.shipping_route === 'delivery_dfo') return 'Ekspedisi (DFO)'
  return 'Ekspedisi (Hitung Admin)'
})

// Teks Tombol
const submitButtonText = computed(() => {
  if (submitting.value) return 'Memproses Pesanan...'
  if (orderForm.shipping_route === 'delivery_prepaid') {
    return 'Konfirmasi & Tunggu Konfirmasi Ongkir'
  }
  return 'Konfirmasi & Buat Pesanan'
})

// Validasi Keranjang saat masuk halaman
onMounted(() => {
  if (cart.value.length === 0) {
    ElMessage.info('Keranjang belanja Anda masih kosong.')
    navigateTo('/katalog')
  }
})

// Eksekusi Pemesanan
const handlePlaceOrder = async () => {
  // 1. Validasi Nomor Telepon
  if (!orderForm.customer_phone || !orderForm.customer_phone.trim()) {
    ElMessage.warning('Mohon lengkapi nomor WhatsApp/Telepon untuk konfirmasi pesanan.')
    return
  }

  // 2. Validasi Alamat jika pengiriman Ekspedisi (Jalur 2 & 3)
  if (orderForm.shipping_route !== 'pickup') {
    if (!orderForm.shipping_address.trim() || !orderForm.shipping_city.trim()) {
      ElMessage.warning('Mohon lengkapi Alamat dan Kota tujuan pengiriman ekspedisi.')
      return
    }
  }

  // 3. Validasi Bukti Transfer Langsung (Wajib jika pilih Transfer pada Jalur 1 & Jalur 2)
  if (orderForm.shipping_route !== 'delivery_prepaid' && orderForm.payment_method === 'transfer') {
    if (!proofFile.value) {
      ElMessage.warning('Mohon unggah foto struk bukti transfer pembayaran Anda terlebih dahulu.')
      return
    }
  }

  submitting.value = true
  try {
    let paymentProofId: string | null = null

    // UPLOAD FOTO STRUK DULU KE SERVER (Jika ada berkas struk)
    if (proofFile.value) {
      const attachment = await uploadAttachment(proofFile.value, {
        attachmentableType: 'order',
        type: 'payment_proof'
      })
      paymentProofId = attachment.id
    }

    // Mapping nilai ke kolom database sesuai controller Intan
    const deliveryMethod = orderForm.shipping_route === 'pickup' ? 'pickup' : 'delivery'
    let shippingPaymentType = 'none'
    if (orderForm.shipping_route === 'delivery_dfo') shippingPaymentType = 'dfo'
    if (orderForm.shipping_route === 'delivery_prepaid') shippingPaymentType = 'prepaid'

    const payload = {
      customer_id: authUser.value?.customer?.id,
      customer_name: orderForm.customer_name,
      customer_email: orderForm.customer_email,
      customer_phone: orderForm.customer_phone,
      delivery_method: deliveryMethod,
      shipping_type: orderForm.shipping_route,
      shipping_payment_type: shippingPaymentType,
      shipping_fee: 0,
      shipping_address: orderForm.shipping_route === 'pickup' 
        ? 'Ambil Sendiri di Kantor ABC Jakarta (Tebet)'
        : `${orderForm.shipping_address}, ${orderForm.shipping_city} ${orderForm.shipping_postal_code}`,
      payment_method: orderForm.shipping_route === 'delivery_prepaid' ? 'pending_calculation' : orderForm.payment_method,
      payment_proof_id: paymentProofId,
      payment_status: 'unpaid',
      shipping_status: orderForm.shipping_route === 'delivery_prepaid' ? 'waiting_shipping_fee' : 'pending',
      notes: orderForm.notes || null,
      total_amount: subtotal.value,
      source: 'web',
      items: cart.value.map((item) => ({
        book_id: item.id,
        title: item.title,
        qty: item.qty,
        unit_price: isApprovedPL.value ? item.member_price : item.price,
        subtotal: (isApprovedPL.value ? item.member_price : item.price) * item.qty
      }))
    }

    // Kirim pesanan ke backend API Laravel
    await apiFetch('/orders', {
      method: 'POST',
      body: payload
    })

    if (orderForm.shipping_route === 'delivery_prepaid') {
      ElNotification({
        title: 'Pesanan Dibuat (Menunggu Ongkir)',
        message: 'Pesanan Anda telah dicatat. Admin ABC akan segera menginput ongkir pada pesanan Anda.',
        type: 'info',
        duration: 6000
      })
    } else {
      ElNotification({
        title: 'Pesanan Berhasil Dibuat!',
        message: 'Pesanan beserta bukti pembayaran Anda telah diterima. Admin ABC akan segera memverifikasinya.',
        type: 'success',
        duration: 6000
      })
    }

    // Bersihkan file dan keranjang belanja
    removeProof()
    clearCart()

    // Langsung arahkan ke tab Buying (Riwayat Pesanan)
    navigateTo('/profile?tab=buying')
  } catch (err: any) {
    ElMessage.error(getErrorMessage(err, 'Gagal membuat pesanan. Silakan periksa kembali data Anda.'))
  } finally {
    submitting.value = false
  }
}
</script>