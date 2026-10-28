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
        
        <!-- Tab Bar Navigasi (Otomatis menampilkan tab Piutang untuk PL, Gereja, Sekolah) -->
        <div class="flex items-center gap-2 border-b border-gray-100 pb-4 mb-8 overflow-x-auto">
          <button 
            v-for="tab in availableTabs" 
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
            <span v-if="tab.id === 'piutang'" class="px-1.5 py-0.2 rounded-full text-[9px] bg-white text-[#00a9c3] font-black uppercase">
              Tempo
            </span>
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
                  <span 
                    :class="[
                      'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider',
                      getPaymentBadgeClass(order)
                    ]"
                  >
                    {{ getPaymentStatusText(order) }}
                  </span>

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
                  <button 
                    v-if="order.payment_status !== 'paid' && order.payment_method === 'transfer' && !order.payment_proof_id"
                    @click="openUploadDialog(order)"
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 text-white text-[11px] font-bold uppercase hover:bg-emerald-700 transition shadow-sm"
                  >
                    + Upload Struk
                  </button>

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
        <!-- TAB 3: PIUTANG & KREDIT TEMPO (PL & LEMBAGA)  -->
        <!-- ============================================== -->
        <div v-else-if="activeTab === 'piutang'" class="space-y-6">
          
          <!-- BANNER METRIK RINGKASAN KREDIT -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Total Hutang Berjalan -->
            <div class="p-5 rounded-3xl bg-rose-50/70 border border-rose-200">
              <span class="text-[10px] font-bold text-rose-500 uppercase tracking-wider block">Total Saldo Hutang Berjalan</span>
              <p class="text-2xl font-black text-rose-600 mt-1">{{ formatRupiah(totalRemainingDebt) }}</p>
              <p class="text-[11px] text-gray-500 mt-1 font-medium">Dari {{ debtOrders.length }} faktur tempo aktif</p>
            </div>

            <!-- Sisa Plafon Belanja -->
            <div class="p-5 rounded-3xl bg-[#00a9c3]/10 border border-[#00a9c3]/30">
              <span class="text-[10px] font-bold text-[#00a9c3] uppercase tracking-wider block">Sisa Plafon Belanja</span>
              <p class="text-2xl font-black text-[#00a9c3] mt-1">{{ formatRupiah(availableCreditLimit) }}</p>
              <p class="text-[11px] text-gray-500 mt-1 font-medium">Batas sisa kredit yang tersedia</p>
            </div>

            <!-- Plafon Maksimal -->
            <div class="p-5 rounded-3xl bg-gray-50 border border-gray-200/80">
              <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Batas Plafon Kredit</span>
              <p class="text-2xl font-black text-gray-800 mt-1">{{ formatRupiah(userCreditLimit) }}</p>
              <p class="text-[11px] text-gray-500 mt-1 font-medium">Maksimal fasilitas kemitraan ABC</p>
            </div>
          </div>

          <!-- PEMBERITAHUAN KEBIJAKAN FLEKSIBEL ABC JAKARTA -->
          <div class="p-4 rounded-2xl bg-blue-50/80 border border-blue-100 shadow-sm flex items-start gap-3">
            <span class="text-xl shrink-0">🤝</span>
            <div class="text-xs">
              <p class="font-bold text-gray-900">Kebijakan Cicilan Fleksibel ABC Jakarta</p>
              <p class="text-gray-600 mt-0.5 leading-relaxed">
                Pembayaran cicilan bersifat fleksibel tanpa bunga dan tanpa nominal tetap. Silakan transfer semampunya ke rekening resmi Bank Mandiri ABC Jakarta, lalu unggah foto bukti transfer. Kasir admin akan memverifikasi dan memotong saldo hutang Anda.
              </p>
            </div>
          </div>

          <!-- HEADER DAFTAR FAKTUR PIUTANG -->
          <div class="flex items-center justify-between pt-2">
            <div>
              <h2 class="text-base font-black text-gray-900 tracking-tight">Faktur Piutang & Cicilan Berjalan</h2>
              <p class="text-xs text-gray-400 mt-0.5">Daftar pesanan dengan metode kredit tempo yang memiliki sisa tagihan.</p>
            </div>
            <button @click="fetchOrders" class="text-xs text-[#00a9c3] font-bold hover:underline flex items-center gap-1">
              🔄 Muat Ulang
            </button>
          </div>

          <!-- KOSONG (LUNAS SEMUA) -->
          <!-- KOSONG (LUNAS SEMUA) -->
<div v-if="debtOrders.length === 0 && totalRemainingDebt === 0" class="py-14 text-center border-2 border-dashed border-emerald-200 bg-emerald-50/30 rounded-3xl p-8 max-w-md mx-auto">
            <span class="text-4xl block mb-2">🎉</span>
            <p class="text-sm font-black text-emerald-800">Semua Tagihan Piutang Lunas</p>
            <p class="text-xs text-gray-500 mt-1 mb-4">Anda tidak memiliki tagihan piutang berjalan saat ini.</p>
            <NuxtLink to="/katalog" class="inline-block px-5 py-2.5 rounded-xl bg-[#00a9c3] text-white text-xs font-bold uppercase tracking-wider shadow-sm hover:bg-[#0092a8] transition">
              Gunakan Plafon untuk Belanja
            </NuxtLink>
          </div>

          <!-- DAFTAR KARTU FAKTUR PIUTANG -->
          <div v-else class="space-y-4">
            <div 
              v-for="order in debtOrders" 
              :key="order.id"
              class="p-5 sm:p-6 rounded-3xl border border-gray-200/80 bg-white hover:border-[#00a9c3] hover:shadow-md transition-all cursor-pointer group space-y-4"
              @click="openOrderDetail(order)"
            >
              <!-- Baris Atas: Nomor Faktur & Status -->
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 text-xs">
                <div>
                  <span class="font-black text-gray-900 text-sm tracking-wider group-hover:text-[#00a9c3] transition-colors">
                    {{ order.order_number }}
                  </span>
                  <span class="text-gray-400 ml-2 block sm:inline text-[11px]">{{ formatDate(order.created_at) }}</span>
                </div>
                
                <div class="flex items-center gap-2" @click.stop>
                  <span 
                    :class="[
                      'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider',
                      (order.total_amount - getRemainingDebt(order)) > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200'
                    ]"
                  >
                    {{ (order.total_amount - getRemainingDebt(order)) > 0 ? 'Sebagian Dicicil' : 'Belum Dicicil' }}
                  </span>

                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#00a9c3]/10 text-[#00a9c3] border border-[#00a9c3]/20">
                    Kredit Tempo
                  </span>
                </div>
              </div>

              <!-- Rincian Buku Singkat -->
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

              <!-- Baris Rincian Saldo Hutang -->
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3.5 bg-[#f8fafc] rounded-2xl border border-gray-100 text-xs">
                <div>
                  <span class="text-[10px] text-gray-400 uppercase font-bold block">Total Belanja Awal</span>
                  <span class="font-bold text-gray-700">{{ formatRupiah(order.total_amount) }}</span>
                </div>
                <div>
                  <span class="text-[10px] text-gray-400 uppercase font-bold block">Sudah Disetor</span>
                  <span class="font-bold text-emerald-600">{{ formatRupiah(Math.max(0, Number(order.total_amount) - getRemainingDebt(order))) }}</span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                  <span class="text-[10px] text-rose-500 uppercase font-bold block">Sisa Hutang Faktur</span>
                  <span class="font-black text-rose-600 text-sm">{{ formatRupiah(getRemainingDebt(order)) }}</span>
                </div>
              </div>

              <!-- Baris Bawah: Tombol Setor Cicilan -->
              <div class="pt-1 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs" @click.stop>
                <div class="text-gray-400 text-[11px] flex items-center gap-1.5">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#00a9c3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>Transfer nominal bebas semampunya, lalu lampirkan foto struk di sini.</span>
                </div>

                <div class="flex items-center gap-2">
                  <!-- Tombol Upload Bukti Cicilan -->
                  <button 
                    @click="openDebtPaymentDialog(order)"
                    class="px-4 py-2 rounded-xl bg-[#00a9c3] text-white text-[11px] font-bold uppercase hover:bg-[#0092a8] transition shadow-md shadow-[#00a9c3]/20 flex items-center gap-1.5"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>+ Setor Cicilan</span>
                  </button>

                  <!-- Tombol Rincian Struk -->
                  <button 
                    @click="openOrderDetail(order)"
                    class="px-3.5 py-2 rounded-xl border border-gray-300 text-gray-700 text-[11px] font-bold uppercase hover:border-[#00a9c3] hover:text-[#00a9c3] transition flex items-center gap-1.5 bg-white"
                  >
                    <span>Rincian</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- RIWAYAT BUKTI SETORAN CICILAN YANG SUDAH DIKIRIM -->
          <div v-if="debtPaymentHistory.length > 0" class="mt-8 pt-6 border-t border-gray-100 space-y-4">
            <h3 class="text-sm font-black text-gray-900 tracking-tight">Riwayat Bukti Setoran Cicilan Anda</h3>
            <div class="divide-y divide-gray-100 border border-gray-200/80 rounded-2xl overflow-hidden bg-white">
              <div 
                v-for="(pay, idx) in debtPaymentHistory" 
                :key="idx"
                class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs"
              >
                <div>
                  <span class="font-bold text-gray-900">{{ pay.order_number }}</span>
                  <span class="text-gray-400 text-[11px] ml-2">{{ formatDate(pay.date) }}</span>
                  <p class="text-[11px] text-gray-500 mt-0.5">{{ pay.notes || 'Setoran cicilan tempo' }}</p>
                </div>
                <div class="flex items-center gap-3">
                  <a v-if="pay.proof_url" :href="pay.proof_url" target="_blank" class="text-[#00a9c3] font-bold hover:underline text-[11px]">
                    Lihat Struk ↗
                  </a>
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-50 text-amber-700 border border-amber-200">
                    {{ pay.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 4: VOUCHER                                 -->
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
        <!-- TAB 5: SETTINGS                                -->
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
              <p class="text-xs text-gray-400 mt-0.5">Pastikan kata sandi baru Anda minimal 8 karakter.</p>
            </div>

            <form @submit.prevent="handleChangePassword" class="max-w-xl space-y-4 text-xs">
              <div>
                <label class="block text-gray-700 font-bold mb-1">Password Saat Ini <span class="text-rose-500">*</span></label>
                <div class="relative">
                  <input 
                    v-model="passwordForm.current_password" 
                    :type="showCurrentPassword ? 'text' : 'password'" 
                    placeholder="Masukkan password lama Anda" 
                    required 
                    class="w-full pl-4 pr-11 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a9c3] outline-none text-xs transition" 
                  />
                  <button 
                    type="button" 
                    @click="showCurrentPassword = !showCurrentPassword"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#00a9c3] transition p-1"
                  >
                    <svg v-if="!showCurrentPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#00a9c3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 font-bold mb-1">Password Baru <span class="text-rose-500">*</span></label>
                  <div class="relative">
                    <input 
                      v-model="passwordForm.password" 
                      :type="showNewPassword ? 'text' : 'password'" 
                      placeholder="Minimal 8 karakter" 
                      required 
                      minlength="8" 
                      class="w-full pl-4 pr-11 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a9c3] outline-none text-xs transition" 
                    />
                    <button 
                      type="button" 
                      @click="showNewPassword = !showNewPassword"
                      class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#00a9c3] transition p-1"
                    >
                      <svg v-if="!showNewPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#00a9c3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                      </svg>
                    </button>
                  </div>
                </div>

                <div>
                  <label class="block text-gray-700 font-bold mb-1">Konfirmasi Password <span class="text-rose-500">*</span></label>
                  <div class="relative">
                    <input 
                      v-model="passwordForm.password_confirmation" 
                      :type="showConfirmPassword ? 'text' : 'password'" 
                      placeholder="Ulangi password baru" 
                      required 
                      class="w-full pl-4 pr-11 py-2.5 rounded-xl border border-gray-200 focus:border-[#00a9c3] outline-none text-xs transition" 
                    />
                    <button 
                      type="button" 
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#00a9c3] transition p-1"
                    >
                      <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#00a9c3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                      </svg>
                    </button>
                  </div>
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

    <!-- MODAL POPUP: STRUK RINCIAN PESANAN (BUYING) -->
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

    <!-- ========================================================= -->
    <!-- MODAL KHUSUS: UPLOAD BUKTI SETOR CICILAN PIUTANG          -->
    <!-- (TANPA KETIK NOMINAL - STATUS MENJADI PENDING VERIFIKASI) -->
    <!-- ========================================================= -->
    <el-dialog 
      v-model="showDebtModal" 
      title="Setor Pembayaran Cicilan Piutang" 
      width="90%" 
      class="max-w-md !rounded-3xl"
    >
      <div v-if="orderForDebtPayment" class="space-y-4 text-xs">
        
        <!-- Rincian Faktur yang Dicicil -->
        <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-100 space-y-1">
          <div class="flex justify-between">
            <span class="text-gray-500">Nomor Faktur:</span>
            <b class="text-gray-900 font-mono">{{ orderForDebtPayment.order_number }}</b>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Sisa Tagihan Faktur:</span>
            <b class="text-rose-600 font-bold text-sm">{{ formatRupiah(getRemainingDebt(orderForDebtPayment)) }}</b>
          </div>
        </div>

        <!-- Rekening Resmi Bank Mandiri ABC Jakarta (Sama dengan Checkout) -->
        <div class="p-4 rounded-2xl bg-slate-900 text-white space-y-1 shadow-md">
          <span class="text-[9px] text-gray-400 uppercase tracking-widest font-bold block">Tujuan Transfer Cicilan:</span>
          <p class="text-xs text-gray-300 font-sans">Bank Mandiri (KCP Jakarta Tebet)</p>
          <div class="flex items-center justify-between pt-1">
            <p class="text-sm font-black text-[#00a9c3] font-mono tracking-wider">123-00-0987654-3</p>
            <button 
              @click="copyBankNumber"
              type="button" 
              class="px-2.5 py-1 bg-white/10 hover:bg-white/20 text-white rounded-lg font-bold text-[10px] transition"
            >
              {{ copiedBankText }}
            </button>
          </div>
          <p class="text-[10px] text-gray-400">a.n. Adventist Book Center</p>
        </div>

        <!-- Informasi Pembayaran Fleksibel -->
        <div class="p-3 bg-blue-50/80 rounded-xl border border-blue-100 text-[11px] text-blue-900 leading-relaxed flex items-start gap-2">
          <span class="text-sm">💡</span>
          <span>Anda <b>tidak perlu mengetik nominal pembayaran</b>. Silakan transfer sejumlah dana semampunya ke rekening di atas, lalu lampirkan foto struk transfer di bawah. Kasir admin ABC Jakarta akan memverifikasi mutasi bank dan memotong saldo hutang Anda.</span>
        </div>

        <!-- Upload File Bukti Transfer -->
        <div>
          <label class="block text-gray-700 font-bold mb-1.5">
            Unggah Foto Struk Transfer Cicilan: <span class="text-rose-500">*</span>
          </label>
          <input 
            type="file" 
            accept="image/*" 
            @change="handleDebtFileChange" 
            class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#00a9c3]/10 file:text-[#00a9c3] cursor-pointer bg-white p-2 border border-gray-200 rounded-xl" 
          />
        </div>

        <!-- Preview Struk -->
        <div v-if="debtPreviewImage" class="text-center pt-1">
          <img :src="debtPreviewImage" class="max-h-44 mx-auto rounded-xl border border-gray-200 object-contain shadow-sm bg-gray-50 p-1" />
        </div>

        <!-- Catatan Tambahan (Opsional) -->
        <div>
          <label class="block text-gray-700 font-bold mb-1">Catatan Setoran (Opsional):</label>
          <input 
            v-model="debtPaymentNotes" 
            type="text" 
            placeholder="Contoh: Cicilan tahap 1 transfer via Rekening Budi..." 
            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs focus:border-[#00a9c3] outline-none transition" 
          />
        </div>

      </div>

      <template #footer>
        <div class="flex justify-end gap-2 pt-2">
          <el-button @click="showDebtModal = false" class="!rounded-xl font-bold">Batal</el-button>
          <el-button 
            type="primary" 
            :loading="uploadingDebtProof" 
            :disabled="!debtSelectedFile" 
            class="!bg-[#00a9c3] !border-none !rounded-xl font-bold" 
            @click="submitDebtPayment"
          >
            Kirim Bukti Cicilan (Pending)
          </el-button>
        </div>
      </template>
    </el-dialog>

    <!-- DIALOG UPLOAD BUKTI TRANSFER REGULER (TAB BUYING) -->
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
import { ref, reactive, computed, watch, onMounted } from 'vue'
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

// Cek apakah User adalah Lembaga (Gereja, Sekolah) atau Penginjil Literatur (PL)
const isLembagaOrPL = computed(() => {
  const type = authUser.value?.customer?.type?.toLowerCase() || ''
  return ['penginjil', 'gereja', 'sekolah'].includes(type)
})

// Tabs Navigasi Dinamis: Tab Piutang otomatis muncul di antara Buying dan Voucher bagi PL / Lembaga
const availableTabs = computed(() => {
  const list = [
    { id: 'profile', label: 'Profile', icon: '👤' },
    { id: 'buying', label: 'Buying', icon: '🛍️' }
  ]

  if (isLembagaOrPL.value) {
    list.push({ id: 'piutang', label: 'Piutang & Cicilan', icon: '💳' })
  }

  list.push(
    { id: 'voucher', label: 'Voucher', icon: '🎟️' },
    { id: 'settings', label: 'Settings', icon: '⚙️' }
  )

  return list
})

const activeTab = ref('profile')

const syncTabFromUrl = () => {
  const queryTab = route.query.tab
  if (queryTab && typeof queryTab === 'string') {
    const found = availableTabs.value.find(t => t.id === queryTab.toLowerCase())
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
// DATA PESANAN & PIUTANG
// ==========================================
const orders = ref([])
const ordersLoading = ref(false)

const fetchOrders = async () => {
  ordersLoading.value = true
  try {
    const res = await apiFetch('/orders', {
      headers: {
        Accept: 'application/json'
      }
    })
    
    // Tangani semua kemungkinan struktur respons Laravel (array langsung, res.data, atau paginasi res.data.data)
    if (Array.isArray(res)) {
      orders.value = res
    } else if (res && Array.isArray(res.data)) {
      orders.value = res.data
    } else if (res && res.data && Array.isArray(res.data.data)) {
      orders.value = res.data.data
    } else {
      orders.value = []
    }
  } catch (err) {
    console.error('Gagal memuat pesanan:', err)
    orders.value = []
  } finally {
    ordersLoading.value = false
  }
}

// ==========================================
// LOGIKA KHUSUS TAB PIUTANG
// ==========================================
// Hitung sisa hutang per faktur
const getRemainingDebt = (order) => {
  if (!order) return 0
  if (order.remaining_amount !== undefined && order.remaining_amount !== null) {
    return Number(order.remaining_amount)
  }
  const total = Number(order.total_amount || 0)
  const paid = Number(order.paid_amount || 0)
  return Math.max(0, total - paid)
}

// Daftar Faktur Tempo Aktif yang masih memiliki sisa tagihan
const debtOrders = computed(() => {
  return orders.value.filter(order => {
    const isCreditOrder = order.payment_method === 'kredit' || order.payment_method === 'credit' || order.payment_method === 'tempo' || order.is_tempo
    const remaining = getRemainingDebt(order)
    const isNotPaid = order.payment_status !== 'paid' && order.payment_status !== 'cancelled'
    return isCreditOrder && remaining > 0 && isNotPaid
  })
})

// Total Saldo Hutang Berjalan: Otomatis membaca current_debt akun (Rp 240.000) atau total sisa faktur
const totalRemainingDebt = computed(() => {
  const customerDebt = Number(authUser.value?.customer?.current_debt)
  if (!isNaN(customerDebt) && customerDebt > 0) return customerDebt
  return debtOrders.value.reduce((acc, curr) => acc + getRemainingDebt(curr), 0)
})

// Plafon Kredit: PL = 5 Juta, Lembaga (Gereja/Sekolah) = 10 Juta
const userCreditLimit = computed(() => {
  const customLimit = Number(authUser.value?.customer?.credit_limit)
  if (!isNaN(customLimit) && customLimit > 0) return customLimit
  const type = authUser.value?.customer?.type?.toLowerCase()
  return type === 'penginjil' ? 5000000 : 10000000
})

const availableCreditLimit = computed(() => {
  return Math.max(0, userCreditLimit.value - totalRemainingDebt.value)
})

// Modal Setor Cicilan Piutang
const showDebtModal = ref(false)
const orderForDebtPayment = ref(null)
const debtSelectedFile = ref(null)
const debtPreviewImage = ref(null)
const debtPaymentNotes = ref('')
const uploadingDebtProof = ref(false)
const copiedBankText = ref('Salin No. Rekening')

const debtPaymentHistory = ref([])

const openDebtPaymentDialog = (order) => {
  orderForDebtPayment.value = order
  debtSelectedFile.value = null
  debtPreviewImage.value = null
  debtPaymentNotes.value = ''
  copiedBankText.value = 'Salin No. Rekening'
  showDebtModal.value = true
}

const handleDebtFileChange = (e) => {
  const file = e.target.files?.[0]
  if (file) {
    debtSelectedFile.value = file
    debtPreviewImage.value = URL.createObjectURL(file)
  }
}

const copyBankNumber = () => {
  if (navigator.clipboard) {
    navigator.clipboard.writeText('123-00-0987654-3')
    copiedBankText.value = 'Tersalin! ✅'
    setTimeout(() => {
      copiedBankText.value = 'Salin No. Rekening'
    }, 2000)
  }
}

// Submit Bukti Cicilan -> Status Masuk Pending Admin
const submitDebtPayment = async () => {
  if (!debtSelectedFile.value || !orderForDebtPayment.value) return

  uploadingDebtProof.value = true
  try {
    // 1. Upload file foto struk ke server
    const attachment = await uploadAttachment(debtSelectedFile.value, {
      attachmentableType: 'order',
      attachmentableId: orderForDebtPayment.value.id,
      type: 'debt_payment_proof'
    })

    // 2. Hubungkan bukti bayar ke pesanan
    await apiFetch(`/orders/${orderForDebtPayment.value.id}`, {
      method: 'PUT',
      body: {
        payment_proof_id: attachment.id,
        notes: debtPaymentNotes.value ? `[Cicilan]: ${debtPaymentNotes.value}` : orderForDebtPayment.value.notes
      }
    })

    // Catat ke daftar riwayat lokal
    debtPaymentHistory.value.unshift({
      order_number: orderForDebtPayment.value.order_number,
      date: new Date().toISOString(),
      proof_url: debtPreviewImage.value,
      notes: debtPaymentNotes.value || 'Setoran cicilan tempo fleksibel',
      status: 'Menunggu Verifikasi Admin'
    })

    ElMessage.success('Bukti setoran cicilan berhasil dikirim! Menunggu verifikasi kasir admin ABC.')
    showDebtModal.value = false
    await fetchOrders()
    if (checkProfile) await checkProfile()
  } catch (err) {
    ElMessage.error(getErrorMessage(err, 'Gagal mengunggah bukti cicilan.'))
  } finally {
    uploadingDebtProof.value = false
  }
}

// ==========================================
// MODAL DETAIL STRUK PESANAN (BUYING)
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
// UPLOAD BUKTI TRANSFER REGULER (TAB BUYING)
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
// SETTINGS: 1. UPDATE KONTAK & ALAMAT
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
// SETTINGS: 2. GANTI PASSWORD (TOGGLE MATA)
// ==========================================
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})
const changingPassword = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const handleChangePassword = async () => {
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