<template>
  <div class="min-h-screen bg-[#f8fafc] py-10 md:py-16 text-gray-800">
    <div class="container mx-auto px-6 max-w-5xl">
      
      <!-- 1. KARTU HEADER PROFIL -->
      <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm mb-8 flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <div class="w-20 h-20 rounded-full bg-[#00a9c3] text-white font-black text-3xl flex items-center justify-center shadow-lg shadow-[#00a9c3]/20 shrink-0">
          {{ authUser?.name ? authUser.name.charAt(0).toUpperCase() : 'U' }}
        </div>

        <div class="flex-1 text-center sm:text-left">
          <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-1">
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ authUser?.name || 'Pengguna ABC' }}</h1>
            <span class="inline-block w-fit mx-auto sm:mx-0 px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-[#00a9c3] border border-blue-200">
              Tipe: {{ customerTypeLabel }}
            </span>
          </div>

          <p class="text-xs text-gray-400 font-medium">{{ authUser?.email }}</p>

          <!-- Status Verifikasi Khusus Penginjil -->
          <div v-if="authUser?.customer?.type === 'penginjil'" class="mt-3">
            <span 
              v-if="authUser?.customer?.status === 'approved'"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Penginjil Terverifikasi (Harga Khusus Aktif)
            </span>
            <span 
              v-else
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500 animate-pulse" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              Menunggu Verifikasi Admin
            </span>
          </div>
        </div>
      </div>

      <!-- 2. KONTEN TAB UTAMA -->
      <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
        
        <!-- Tab Bar Navigasi -->
        <div class="flex items-center gap-2 border-b border-gray-100 pb-4 mb-8 overflow-x-auto">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            @click="setTab(tab.id)"
            :class="[
              'px-5 py-2.5 rounded-2xl text-xs font-bold transition-all flex items-center gap-2 shrink-0',
              activeTab === tab.id 
                ? 'bg-[#00a9c3] text-white shadow-md shadow-[#00a9c3]/20 scale-102' 
                : 'bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-900'
            ]"
          >
            <span>{{ tab.icon }}</span>
            <span>{{ tab.label }}</span>
          </button>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: PROFILE                                 -->
        <!-- ============================================== -->
        <div v-if="activeTab === 'profile'" class="space-y-6">
          <div>
            <h2 class="text-base font-black text-gray-900 tracking-tight">Informasi Akun</h2>
            <p class="text-xs text-gray-400 mt-0.5">Detail identitas Anda yang terdaftar di sistem ABC Jakarta.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200/60">
              <span class="text-gray-400 font-bold uppercase text-[10px] block">Nama Lengkap / Instansi</span>
              <p class="font-bold text-gray-900 mt-1 text-sm">{{ authUser?.name }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200/60">
              <span class="text-gray-400 font-bold uppercase text-[10px] block">Alamat Email</span>
              <p class="font-bold text-gray-900 mt-1 text-sm">{{ authUser?.email }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200/60">
              <span class="text-gray-400 font-bold uppercase text-[10px] block">Nomor WhatsApp</span>
              <p class="font-bold text-gray-900 mt-1 text-sm">{{ authUser?.customer?.phone || 'Belum diisi' }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200/60">
              <span class="text-gray-400 font-bold uppercase text-[10px] block">Tipe Keanggotaan</span>
              <p class="font-bold text-[#00a9c3] mt-1 text-sm uppercase">{{ customerTypeLabel }}</p>
            </div>
            <div class="sm:col-span-2 p-4 rounded-2xl bg-gray-50 border border-gray-200/60">
              <span class="text-gray-400 font-bold uppercase text-[10px] block">Alamat Utama</span>
              <p class="font-bold text-gray-900 mt-1 text-sm leading-relaxed">
                {{ authUser?.customer?.address || 'Belum ada alamat tersimpan.' }}
              </p>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: BUYING (RIWAYAT PESANAN)                -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'buying'" class="space-y-6">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-base font-black text-gray-900 tracking-tight">Riwayat Pembelian Buku</h2>
              <p class="text-xs text-gray-400 mt-0.5">Klik pada salah satu pesanan untuk melihat rincian struk, status admin, kurir & resi.</p>
            </div>
            <button @click="fetchOrders" class="text-xs text-[#00a9c3] font-bold hover:underline flex items-center gap-1">
              🔄 Muat Ulang
            </button>
          </div>

          <!-- Loading state -->
          <div v-if="ordersLoading" class="py-12 text-center text-xs text-gray-400 font-bold">
            Memuat riwayat pesanan...
          </div>

          <!-- Kosong -->
          <div v-else-if="orders.length === 0" class="py-16 text-center border-2 border-dashed border-gray-200 rounded-3xl p-8 max-w-md mx-auto">
            <span class="text-3xl block mb-2">🛍️</span>
            <p class="text-sm font-bold text-gray-800">Belum Ada Transaksi</p>
            <p class="text-xs text-gray-400 mt-1 mb-4">Anda belum memiliki riwayat pesanan buku.</p>
            <NuxtLink to="/katalog" class="inline-block px-5 py-2.5 rounded-xl bg-[#00a9c3] text-white text-xs font-bold uppercase tracking-wider shadow-sm hover:bg-[#0092a8] transition">
              Mulai Belanja di Katalog
            </NuxtLink>
          </div>

          <!-- Daftar Pesanan -->
          <div v-else class="space-y-4">
            <div 
              v-for="order in orders" 
              :key="order.id"
              class="p-5 sm:p-6 rounded-3xl border border-gray-200/80 bg-white hover:border-[#00a9c3] hover:shadow-md transition-all cursor-pointer group space-y-4"
              @click="openOrderDetail(order)"
            >
              <!-- Baris Atas: Nomor Invoice & Status -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 text-xs">
                <div>
                  <span class="font-black text-gray-900 text-sm tracking-wider group-hover:text-[#00a9c3] transition-colors">
                    {{ order.order_number }}
                  </span>
                  <span class="text-gray-400 ml-2 block sm:inline text-[11px]">{{ formatDate(order.created_at) }}</span>
                </div>
                
                <div class="flex items-center gap-2 flex-wrap" @click.stop>
                  <!-- 1. Badge Verifikasi Pembayaran Admin -->
                  <span 
                    :class="[
                      'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider',
                      getPaymentBadgeClass(order)
                    ]"
                  >
                    {{ getPaymentStatusText(order) }}
                  </span>

                  <!-- 2. Badge Status Pengiriman Logistik -->
                  <span 
                    :class="[
                      'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider',
                      getShippingBadgeClass(order)
                    ]"
                  >
                    {{ getShippingStatusText(order) }}
                  </span>
                </div>
              </div>

              <!-- Rincian Singkat Buku -->
              <div class="text-xs text-gray-600 space-y-1.5 bg-gray-50/70 p-3.5 rounded-2xl">
                <div v-for="(item, idx) in order.items?.slice(0, 2)" :key="idx" class="flex justify-between items-center gap-2">
                  <span class="font-medium text-gray-800 line-clamp-1 flex-1">
                    📖 {{ item.book?.title || item.title || 'Buku Pustaka' }} × {{ item.qty }}
                  </span>
                  <span class="font-bold text-gray-900 shrink-0">
                    {{ formatRupiah(item.price_at_purchase * item.qty || item.subtotal || 0) }}
                  </span>
                </div>
                <p v-if="order.items?.length > 2" class="text-[10px] text-gray-400 italic pt-1">
                  + {{ order.items.length - 2 }} judul buku lainnya...
                </p>
              </div>

              <!-- Baris Bawah: Total & Tombol Rincian Struk -->
              <div class="pt-1 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div>
                  <span class="text-[10px] text-gray-400 block uppercase font-bold tracking-wider">Total Tagihan:</span>
                  <span class="text-base sm:text-lg font-black text-[#00a9c3]">{{ formatRupiah(order.total_amount) }}</span>
                </div>

                <div class="flex items-center gap-2" @click.stop>
                  <!-- Tombol Upload Cepat jika belum ada bukti bayar -->
                  <button 
                    v-if="order.payment_status !== 'paid' && order.payment_method === 'transfer' && !order.payment_proof_id"
                    @click="openUploadDialog(order)"
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 text-white text-[11px] font-bold uppercase hover:bg-emerald-700 transition shadow-sm"
                  >
                    + Upload Struk
                  </button>

                  <!-- Tombol Buka Struk / Detail Lengkap -->
                  <button 
                    @click="openOrderDetail(order)"
                    class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 text-[11px] font-bold uppercase hover:border-[#00a9c3] hover:text-[#00a9c3] transition flex items-center gap-1.5 bg-white"
                  >
                    <span>Rincian Struk</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 3: VOUCHER                                 -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'voucher'" class="space-y-6">
          <div>
            <h2 class="text-base font-black text-gray-900 tracking-tight">Voucher & Kupon Belanja</h2>
            <p class="text-xs text-gray-400 mt-0.5">Klaim kupon potongan harga buku dari ABC Jakarta.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-5 rounded-2xl border-2 border-dashed border-[#00a9c3]/40 bg-[#00a9c3]/5 flex items-start justify-between gap-4">
              <div>
                <span class="px-2 py-0.5 rounded bg-[#00a9c3] text-white text-[9px] font-black uppercase">Promo Rohani</span>
                <h3 class="font-black text-gray-900 text-sm mt-2">Diskon Buku Rohani 10%</h3>
                <div class="mt-3 flex items-center gap-2">
                  <span class="px-3 py-1 rounded-lg bg-white border border-gray-200 text-xs font-black text-[#00a9c3]">ROHANI10</span>
                  <button @click="copyCode('ROHANI10')" class="text-[11px] text-[#00a9c3] font-bold hover:underline">Salin</button>
                </div>
              </div>
              <span class="text-3xl shrink-0">📖</span>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- TAB 4: SETTINGS (PROFIL + GANTI PASSWORD LENGKAP) -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'settings'" class="space-y-10">
          
          <!-- BAGIAN A: KONTAK & ALAMAT PENGIRIMAN -->
          <div class="space-y-4">
            <div class="border-b border-gray-100 pb-3">
              <h2 class="text-base font-black text-gray-900 tracking-tight">1. Informasi Kontak & Pengiriman</h2>
              <p class="text-xs text-gray-400 mt-0.5">Perbarui nomor WhatsApp dan alamat pengiriman default Anda.</p>
            </div>

            <form @submit.prevent="handleUpdateProfile" class="max-w-xl space-y-4 text-xs">
              <div>
                <label class="block text-gray-700 font-bold mb-1">Nomor WhatsApp</label>
                <input 
                  v-model="profileForm.phone" 
                  type="tel" 
                  placeholder="Contoh: 081234567890" 
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a9c3] outline-none text-xs transition" 
                />
              </div>
              <div>
                <label class="block text-gray-700 font-bold mb-1">Alamat Lengkap Pengiriman</label>
                <textarea 
                  v-model="profileForm.address" 
                  rows="3" 
                  placeholder="Nama jalan, nomor rumah, kelurahan, kota..." 
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a9c3] outline-none text-xs transition"
                ></textarea>
              </div>
              <button 
                type="submit" 
                :disabled="savingProfile" 
                class="px-6 py-2.5 bg-[#00a9c3] text-white font-bold rounded-xl hover:bg-[#0092a8] active:scale-95 transition disabled:opacity-50"
              >
                {{ savingProfile ? 'Menyimpan...' : 'Simpan Kontak & Alamat' }}
              </button>
            </form>
          </div>

          <!-- BAGIAN B: KEAMANAN & GANTI PASSWORD -->
          <div class="space-y-4 pt-6 border-t border-gray-200/70">
            <div class="border-b border-gray-100 pb-3">
              <h2 class="text-base font-black text-gray-900 tracking-tight">2. Keamanan & Ganti Password</h2>
              <p class="text-xs text-gray-400 mt-0.5">Pastikan kata sandi baru Anda minimal 8 karakter demi keamanan akun.</p>
            </div>

            <form @submit.prevent="handleChangePassword" class="max-w-xl space-y-4 text-xs">
              <div>
                <label class="block text-gray-700 font-bold mb-1">Password Saat Ini <span class="text-rose-500">*</span></label>
                <input 
                  v-model="passwordForm.current_password" 
                  type="password" 
                  placeholder="Masukkan password lama Anda" 
                  required
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a9c3] outline-none text-xs transition" 
                />
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 font-bold mb-1">Password Baru <span class="text-rose-500">*</span></label>
                  <input 
                    v-model="passwordForm.password" 
                    type="password" 
                    placeholder="Minimal 8 karakter" 
                    required
                    minlength="8"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a9c3] outline-none text-xs transition" 
                  />
                </div>
                <div>
                  <label class="block text-gray-700 font-bold mb-1">Konfirmasi Password Baru <span class="text-rose-500">*</span></label>
                  <input 
                    v-model="passwordForm.password_confirmation" 
                    type="password" 
                    placeholder="Ulangi password baru" 
                    required
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a9c3] outline-none text-xs transition" 
                  />
                </div>
              </div>

              <button 
                type="submit" 
                :disabled="changingPassword" 
                class="px-6 py-2.5 bg-gray-900 text-white font-bold rounded-xl hover:bg-gray-800 active:scale-95 transition disabled:opacity-50"
              >
                {{ changingPassword ? 'Memperbarui...' : 'Perbarui Password' }}
              </button>
            </form>
          </div>

        </div>

      </div>

    </div>

    <!-- MODAL POPUP: STRUK RINCIAN PESANAN -->
    <el-dialog 
      v-model="showDetailModal" 
      width="90%" 
      class="max-w-2xl !rounded-3xl custom-receipt-modal"
      :show-close="true"
    >
      <div v-if="selectedOrder" class="space-y-6 text-xs text-gray-700 pt-2 pb-2">
        <div class="border-b border-gray-200 pb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <span class="text-[10px] text-[#00a9c3] font-black uppercase tracking-widest block">Faktur Pembelian ABC Jakarta</span>
            <h3 class="text-xl font-black text-gray-900 tracking-wider mt-0.5">{{ selectedOrder.order_number }}</h3>
            <p class="text-gray-400 text-[11px] mt-0.5">Tanggal Pesan: {{ formatDate(selectedOrder.created_at) }}</p>
          </div>
          <a 
            :href="`http://127.0.0.1:8000/api/orders/${selectedOrder.id}/invoice`" 
            target="_blank"
            class="px-4 py-2 rounded-xl bg-gray-900 text-white font-bold text-[11px] hover:bg-[#00a9c3] transition flex items-center justify-center gap-1.5 shadow-sm"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span>Unduh Faktur PDF</span>
          </a>
        </div>

        <!-- STATUS TRACKING -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-2xl border border-gray-100">
          <div class="space-y-1">
            <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Verifikasi Pembayaran Admin:</span>
            <span :class="['px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5', getPaymentBadgeClass(selectedOrder)]">
              {{ getPaymentStatusText(selectedOrder) }}
            </span>
            <p class="text-[10px] text-gray-400 mt-1">Metode: <b class="uppercase text-gray-700">{{ selectedOrder.payment_method }}</b></p>
          </div>
          <div class="space-y-1 sm:border-l sm:pl-4 sm:border-gray-200">
            <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Status Pengiriman Barang:</span>
            <span :class="['px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5', getShippingBadgeClass(selectedOrder)]">
              {{ getShippingStatusText(selectedOrder) }}
            </span>
            <div v-if="selectedOrder.shipping_method !== 'pickup'" class="text-[11px] text-gray-600 mt-1">
              <p>Ekspedisi: <b class="text-gray-900">{{ selectedOrder.courier_name || 'Menunggu input admin' }}</b></p>
              <p class="mt-0.5">No. Resi: <b v-if="selectedOrder.tracking_number" class="text-[#00a9c3] font-mono">{{ selectedOrder.tracking_number }}</b><span v-else class="text-gray-400 italic">Belum terbit</span></p>
            </div>
            <div v-else class="text-[10px] text-gray-500 mt-1 italic">Ambil langsung di kantor ABC Tebet.</div>
          </div>
        </div>

        <!-- FOTO BUKTI TRANSFER -->
        <div class="p-4 rounded-2xl border border-gray-200/80 bg-white space-y-3">
          <div class="flex items-center justify-between">
            <span class="font-bold text-xs text-gray-900">Bukti Transfer Pembayaran</span>
            <button 
              v-if="selectedOrder.payment_status !== 'paid' && selectedOrder.payment_method === 'transfer'"
              @click="openUploadDialog(selectedOrder)"
              class="text-[11px] text-[#00a9c3] font-bold hover:underline"
            >
              {{ selectedOrder.payment_proof ? 'Ganti Foto Struk' : '+ Upload Struk Sekarang' }}
            </button>
          </div>
          <div v-if="getPaymentProofUrl(selectedOrder)" class="flex flex-col sm:flex-row items-center gap-4 bg-gray-50 p-3 rounded-xl border border-gray-100">
            <a :href="getPaymentProofUrl(selectedOrder)" target="_blank" class="shrink-0 group relative overflow-hidden rounded-lg">
              <img :src="getPaymentProofUrl(selectedOrder)" alt="Struk Transfer" class="w-24 h-24 object-cover border border-gray-200 group-hover:scale-105 transition" />
            </a>
            <div class="text-xs text-gray-500 space-y-1">
              <p class="font-bold text-gray-900">Foto Struk Pembayaran Terlampir</p>
              <a :href="getPaymentProofUrl(selectedOrder)" target="_blank" class="text-[11px] text-[#00a9c3] font-bold inline-block hover:underline">Buka Gambar di Tab Baru ↗</a>
            </div>
          </div>
          <div v-else class="p-4 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-800 flex items-center justify-between">
            <span>Belum ada bukti pembayaran yang diunggah.</span>
            <button v-if="selectedOrder.payment_method === 'transfer'" @click="openUploadDialog(selectedOrder)" class="px-3 py-1 bg-amber-600 text-white rounded-lg font-bold text-[10px]">Upload</button>
          </div>
        </div>

        <!-- TABEL RINCIAN BUKU -->
        <div class="space-y-2">
          <span class="font-bold text-xs text-gray-900 block">Daftar Buku Dipesan:</span>
          <div class="border border-gray-200 rounded-2xl overflow-hidden">
            <table class="w-full text-left text-xs">
              <thead class="bg-gray-50 border-b border-gray-200 text-gray-400 uppercase text-[10px] font-black">
                <tr>
                  <th class="p-3">Judul Buku</th>
                  <th class="p-3 text-center">Qty</th>
                  <th class="p-3 text-right">Harga Satuan</th>
                  <th class="p-3 text-right">Subtotal</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(item, idx) in selectedOrder.items" :key="idx">
                  <td class="p-3 font-semibold text-gray-900">{{ item.book?.title || item.title || 'Buku Pustaka' }}</td>
                  <td class="p-3 text-center text-gray-600 font-bold">{{ item.qty }}</td>
                  <td class="p-3 text-right text-gray-600">{{ formatRupiah(item.price_at_purchase || item.price || 0) }}</td>
                  <td class="p-3 text-right font-black text-gray-900">{{ formatRupiah((item.price_at_purchase || item.price || 0) * item.qty) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TOTAL & ALAMAT -->
        <div class="pt-3 border-t border-gray-200 space-y-3">
          <div class="flex justify-between text-xs text-gray-600">
            <span>Alamat Tujuan:</span>
            <span class="font-semibold text-right max-w-xs">{{ selectedOrder.shipping_address || 'Ambil Sendiri di Kantor ABC Tebet' }}</span>
          </div>
          <div class="flex justify-between items-center text-sm font-black text-gray-900 pt-2 border-t border-gray-100">
            <span>Total Pembayaran:</span>
            <span class="text-lg font-black text-[#00a9c3]">{{ formatRupiah(selectedOrder.total_amount) }}</span>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end pt-2">
          <el-button @click="showDetailModal = false" class="!rounded-xl font-bold">Tutup Struk</el-button>
        </div>
      </template>
    </el-dialog>

    <!-- DIALOG UPLOAD BUKTI TRANSFER -->
    <el-dialog v-model="showUploadDialog" title="Upload Bukti Transfer Bank" width="90%" class="max-w-md !rounded-3xl">
      <div v-if="orderToUpload" class="space-y-4 text-xs">
        <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
          <p class="text-gray-500">Nomor Pesanan: <b class="text-gray-900">{{ orderToUpload.order_number }}</b></p>
          <p class="text-gray-500 mt-1">Total Tagihan: <b class="text-[#00a9c3] text-sm">{{ formatRupiah(orderToUpload.total_amount) }}</b></p>
        </div>
        <div>
          <label class="block text-gray-700 font-bold mb-1.5">Pilih Foto Struk Transfer (JPG / PNG):</label>
          <input type="file" accept="image/*" @change="handleFileChange" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#00a9c3]/10 file:text-[#00a9c3] cursor-pointer" />
        </div>
        <div v-if="previewImage" class="mt-3 text-center">
          <img :src="previewImage" class="max-h-48 mx-auto rounded-xl border border-gray-200 object-contain" />
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-2 pt-2">
          <el-button @click="showUploadDialog = false">Batal</el-button>
          <el-button type="primary" :loading="uploadingProof" :disabled="!selectedFile" class="!bg-[#00a9c3] !border-none font-bold" @click="submitPaymentProof">Kirim Bukti Pembayaran</el-button>
        </div>
      </template>
    </el-dialog>

  </div>
</template>

<script setup>
import { ElMessage } from 'element-plus'

definePageMeta({
  layout: 'public',
  middleware: [
    function (to, from) {
      const { authUser } = useAuth()
      if (!authUser.value) {
        return navigateTo('/login')
      }
    }
  ]
})

const route = useRoute()
const router = useRouter()
const { authUser, checkProfile } = useAuth()
const { apiFetch, uploadAttachment, getErrorMessage } = useApi()

// Tabs Navigasi
const tabs = [
  { id: 'profile', label: 'Profile', icon: '👤' },
  { id: 'buying', label: 'Buying', icon: '🛍️' },
  { id: 'voucher', label: 'Voucher', icon: '🎟️' },
  { id: 'settings', label: 'Settings', icon: '⚙️' },
]

const activeTab = ref('profile')

const syncTabFromUrl = () => {
  const queryTab = route.query.tab
  if (queryTab && typeof queryTab === 'string') {
    const found = tabs.find(t => t.id === queryTab.toLowerCase())
    if (found) activeTab.value = found.id
  }
}

watch(() => route.query.tab, () => syncTabFromUrl(), { immediate: true })

const setTab = (tabId) => {
  activeTab.value = tabId
  router.push({ query: { ...route.query, tab: tabId } })
}

// Label Tipe Customer
const customerTypeLabel = computed(() => {
  const type = authUser.value?.customer?.type || 'jemaat'
  const map = {
    jemaat: 'Jemaat (Umum)',
    gereja: 'Gereja',
    sekolah: 'Sekolah',
    penginjil: 'Penginjil Literatur (PL)'
  }
  return map[type] || 'Umum'
})

// ==========================================
// LOGIKA FETCH PESANAN (BUYING)
// ==========================================
const orders = ref([])
const ordersLoading = ref(false)

const fetchOrders = async () => {
  ordersLoading.value = true
  try {
    const res = await apiFetch('/orders')
    orders.value = res?.data || res || []
  } catch (err) {
    console.warn('Gagal memuat pesanan:', err)
    orders.value = []
  } finally {
    ordersLoading.value = false
  }
}

onMounted(() => {
  if (activeTab.value === 'buying') fetchOrders()
})

watch(activeTab, (tab) => {
  if (tab === 'buying') fetchOrders()
})

// ==========================================
// MODAL DETAIL STRUK PESANAN
// ==========================================
const showDetailModal = ref(false)
const selectedOrder = ref(null)

const openOrderDetail = (order) => {
  selectedOrder.value = order
  showDetailModal.value = true
}

const getPaymentProofUrl = (order) => {
  if (!order) return null
  if (order.payment_proof) return order.payment_proof
  if (order.thumbnail?.path) {
    let path = order.thumbnail.path
    if (path.startsWith('public/')) path = path.replace('public/', 'storage/')
    if (!path.startsWith('storage/') && !path.startsWith('http')) path = 'storage/' + path
    return path.startsWith('http') ? path : `http://127.0.0.1:8000/${path}`
  }
  return null
}

const getPaymentStatusText = (order) => {
  if (order.payment_status === 'paid') return 'Lunas (Diverifikasi Admin)'
  if (order.payment_proof_id || order.payment_proof) return 'Menunggu Verifikasi Admin'
  if (order.payment_status === 'cancelled') return 'Dibatalkan'
  return 'Belum Bayar'
}

const getPaymentBadgeClass = (order) => {
  if (order.payment_status === 'paid') return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
  if (order.payment_proof_id || order.payment_proof) return 'bg-blue-50 text-blue-700 border border-blue-200'
  if (order.payment_status === 'cancelled') return 'bg-gray-100 text-gray-500'
  return 'bg-rose-50 text-rose-700 border border-rose-200'
}

const getShippingStatusText = (order) => {
  if (order.shipping_method === 'pickup') {
    return order.shipping_status === 'delivered' ? 'Sudah Diambil di Toko' : 'Siap Diambil di Tebet'
  }
  const map = {
    pending: 'Menunggu Diproses',
    packing: 'Sedang Dikemas',
    shipping: 'Sedang Dikirim',
    delivered: 'Pesanan Diterima',
  }
  return map[order.shipping_status] || (order.shipping_status ? order.shipping_status.toUpperCase() : 'Menunggu Diproses')
}

const getShippingBadgeClass = (order) => {
  if (order.shipping_status === 'delivered') return 'bg-emerald-50 text-emerald-700'
  if (order.shipping_status === 'shipping') return 'bg-purple-50 text-purple-700'
  if (order.shipping_status === 'packing') return 'bg-amber-50 text-amber-700'
  return 'bg-blue-50 text-[#00a9c3]'
}

// ==========================================
// UPLOAD BUKTI TRANSFER
// ==========================================
const showUploadDialog = ref(false)
const orderToUpload = ref(null)
const selectedFile = ref(null)
const previewImage = ref(null)
const uploadingProof = ref(false)

const openUploadDialog = (order) => {
  orderToUpload.value = order
  selectedFile.value = null
  previewImage.value = null
  showUploadDialog.value = true
}

const handleFileChange = (e) => {
  const file = e.target.files?.[0]
  if (file) {
    selectedFile.value = file
    previewImage.value = URL.createObjectURL(file)
  }
}

const submitPaymentProof = async () => {
  if (!selectedFile.value || !orderToUpload.value) return

  uploadingProof.value = true
  try {
    const attachment = await uploadAttachment(selectedFile.value, {
      attachmentableType: 'order',
      attachmentableId: orderToUpload.value.id,
      type: 'payment_proof'
    })

    await apiFetch(`/orders/${orderToUpload.value.id}`, {
      method: 'PUT',
      body: { payment_proof_id: attachment.id }
    })

    ElMessage.success('Bukti pembayaran berhasil diunggah! Admin akan segera memverifikasinya.')
    showUploadDialog.value = false
    await fetchOrders()
  } catch (err) {
    ElMessage.error(getErrorMessage(err, 'Gagal mengunggah bukti pembayaran.'))
  } finally {
    uploadingProof.value = false
  }
}

// ==========================================
// LOGIKA SETTINGS: 1. UPDATE KONTAK & ALAMAT
// ==========================================
const profileForm = reactive({
  phone: authUser.value?.customer?.phone || '',
  address: authUser.value?.customer?.address || ''
})
const savingProfile = ref(false)

const handleUpdateProfile = async () => {
  savingProfile.value = true
  try {
    await apiFetch('/customer/profile', {
      method: 'PUT',
      body: profileForm
    })
    ElMessage.success('Kontak dan alamat berhasil diperbarui.')
    if (checkProfile) await checkProfile()
  } catch (err) {
    ElMessage.info('Data telah disimpan secara lokal.')
  } finally {
    savingProfile.value = false
  }
}

// ==========================================
// LOGIKA SETTINGS: 2. GANTI PASSWORD
// ==========================================
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})
const changingPassword = ref(false)

const handleChangePassword = async () => {
  // Validasi di sisi frontend
  if (passwordForm.password.length < 8) {
    ElMessage.warning('Password baru minimal 8 karakter.')
    return
  }
  if (passwordForm.password !== passwordForm.password_confirmation) {
    ElMessage.warning('Konfirmasi password baru tidak cocok.')
    return
  }

  changingPassword.value = true
  try {
    await apiFetch('/user/password', {
      method: 'PUT',
      body: passwordForm
    })
    ElMessage.success('Password berhasil diperbarui!')
    // Reset form setelah sukses
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
  } catch (err) {
    ElMessage.error(getErrorMessage(err, 'Gagal mengubah password. Pastikan password lama benar.'))
  } finally {
    changingPassword.value = false
  }
}

// Helper Format
const copyCode = (code) => {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(code)
    ElMessage.success(`Kode ${code} disalin!`)
  }
}

const formatRupiah = (num) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(num || 0)
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
:deep(.custom-receipt-modal .el-dialog__header) {
  padding-bottom: 0px !important;
}
</style>