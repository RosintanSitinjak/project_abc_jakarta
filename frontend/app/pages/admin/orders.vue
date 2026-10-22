<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- 1. HEADER HALAMAN -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Pesanan Masuk & Logistik</h2>
          <p class="text-sm text-slate-500">Monitoring transaksi daring, penetapan ongkir, manajemen piutang, dan kurir.</p>
        </div>
        <div class="flex items-center gap-2.5">
          <!-- Tombol Cetak Laporan -->
          <el-button type="warning" plain @click="handleExportMonthly" class="!rounded-xl font-semibold">
            <Icon icon="solar:document-bold" class="mr-1" /> Cetak Laporan Terfilter
          </el-button>

          <!-- TOMBOL ANTREAN VERIFIKASI (STRUK TRANSFER & APPROVAL KREDIT) -->
          <el-badge :value="ordersWaitingVerification.length" :hidden="ordersWaitingVerification.length === 0" class="!inline-flex">
            <el-button 
              @click="isQueueOpen = true" 
              class="!rounded-xl font-bold !border-[#7c3aed] !text-[#7c3aed] hover:!bg-[#7c3aed]/10 shadow-sm"
            >
              <Icon icon="solar:shield-check-bold" class="mr-1.5 text-base text-[#7c3aed]" /> 
              Antrean Verifikasi & Approval
            </el-button>
          </el-badge>

          <!-- Tombol Pesanan Manual -->
          <el-button type="primary" color="#00a9c3" @click="openCreateDialog" class="!rounded-xl shadow-lg font-semibold">
            + Pesanan Manual (Assisted Order)
          </el-button>
        </div>
      </div>

      <!-- 2. FILTER BAR -->
      <div class="flex flex-wrap items-center gap-3 mb-6 p-3 bg-slate-50/70 rounded-2xl border border-slate-200">
        <!-- Filter Rentang Tanggal -->
        <el-date-picker 
          v-model="dateRange" 
          type="daterange" 
          start-placeholder="Mulai" 
          end-placeholder="Selesai" 
          value-format="YYYY-MM-DD" 
          class="!w-56" 
          clearable
          @change="loadData" 
        />
        
        <!-- Filter Status Bayar -->
        <el-select 
          v-model="filterStatus" 
          placeholder="Status Bayar" 
          clearable 
          class="!w-48" 
          @change="loadData"
        >
          <el-option label="Perlu Verifikasi / Approval" value="needs_verification" />
          <el-option label="Menunggu Estimasi Ongkir" value="waiting_shipping_fee" />
          <el-option label="Belum Lunas" value="unpaid" />
          <el-option label="Lunas" value="paid" />
          <el-option label="Dibatalkan" value="cancelled" />
        </el-select>

        <!-- Filter Tipe Pelanggan -->
        <el-select 
          v-model="filterType" 
          placeholder="Tipe Pelanggan" 
          clearable 
          class="!w-40" 
          @change="loadData"
        >
          <el-option label="Jemaat Umum" value="jemaat" />
          <el-option label="Penginjil (PL)" value="penginjil" />
          <el-option label="Gereja" value="gereja" />
          <el-option label="Sekolah" value="sekolah" />
        </el-select>

        <!-- Input Pencarian Real-Time -->
        <el-input 
          v-model="searchQuery" 
          placeholder="Cari Invoice atau Pelanggan..." 
          clearable 
          class="flex-1 min-w-[200px]" 
          @keyup.enter="loadData" 
          @clear="loadData"
        >
          <template #prefix><Icon icon="solar:magnifer-linear" /></template>
        </el-input>

        <el-button 
          v-if="hasActiveFilter" 
          plain 
          type="info" 
          class="!rounded-xl text-xs" 
          @click="resetFilters"
        >
          <Icon icon="solar:restart-bold" class="mr-1" /> Reset
        </el-button>
      </div>

      <!-- 3. TABEL DATA PESANAN -->
      <el-table :data="filteredOrdersList" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden text-xs">
        <el-table-column label="Waktu" width="125" align="center">
          <template #default="{ row }">
            <div class="font-bold text-slate-600 uppercase">{{ formatDateTime(row.created_at) }}</div>
          </template>
        </el-table-column>

        <el-table-column prop="order_number" label="No. Invoice" width="160">
          <template #default="{ row }">
            <span class="font-bold text-[#1B293C]">{{ row.order_number }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Pelanggan" min-width="170">
          <template #default="{ row }">
            <div class="font-bold text-slate-800">{{ row.customer?.name || 'Pelanggan Umum' }}</div>
            <el-tag 
              v-if="row.customer" 
              size="small" 
              effect="plain"
              :type="getCustomerTagType(row.customer.type)"
              class="!text-[9px] h-4 px-1.5 uppercase font-black mt-0.5"
            >
              {{ row.customer.type === 'penginjil' ? 'PL' : row.customer.type }}
            </el-tag>
          </template>
        </el-table-column>

        <!-- PENGIRIMAN & METODE ONGKIR -->
        <el-table-column label="Metode Kirim" width="170">
          <template #default="{ row }">
            <div v-if="row.delivery_method === 'pickup'" class="space-y-0.5">
              <div class="flex items-center text-blue-600 font-bold gap-1">
                <Icon icon="solar:shop-bold" class="text-base" /> Pickup di Kantor
              </div>
              <el-tag size="small" type="success" effect="plain" class="!text-[9px] font-bold">
                Bebas Ongkir (Rp 0)
              </el-tag>
            </div>
            <div v-else class="space-y-0.5">
              <div class="flex items-center text-slate-700 font-bold gap-1">
                <Icon icon="solar:box-minimalistic-bold" class="text-[#00A9C3] text-base" /> Kurir Ekspedisi
              </div>
              <el-tag 
                size="small" 
                :type="row.shipping_payment_type === 'cod' ? 'info' : 'warning'" 
                class="!text-[9px] font-bold"
              >
                {{ row.shipping_payment_type === 'cod' ? 'Ongkir COD (Bayar di Tempat)' : 'Ongkir via Tagihan' }}
              </el-tag>
            </div>
          </template>
        </el-table-column>

        <!-- STATUS PEMBAYARAN CERDAS -->
        <el-table-column label="Pembayaran" width="160" align="center">
          <template #default="{ row }">
            <!-- 1. Menunggu penetapan ongkir admin -->
            <el-tag 
              v-if="row.payment_status === 'waiting_shipping_fee'" 
              type="warning" 
              effect="dark" 
              size="small"
              class="font-black animate-pulse"
            >
              INPUT ONGKIR
            </el-tag>
            
            <!-- 2. PEMBELI TRANSFER/QRIS SUDAH UPLOAD BUKTI (PERLU VERIFIKASI STRUK) -->
            <el-tag 
              v-else-if="row.payment_status === 'unpaid' && row.payment_proof && row.payment_method !== 'kredit'" 
              color="#7c3aed"
              effect="dark" 
              size="small"
              class="font-black !border-none !text-white animate-bounce cursor-pointer shadow-sm"
              @click="openViewDetail(row)"
            >
              <Icon icon="solar:camera-bold" class="inline mr-1 text-xs" /> VERIFIKASI STRUK
            </el-tag>

            <!-- 3. PESANAN KREDIT MASIH PENDING (PERLU PERSETUJUAN KREDIT) -->
            <el-tag 
              v-else-if="row.payment_method === 'kredit' && row.shipping_status === 'pending'" 
              color="#2563eb"
              effect="dark" 
              size="small"
              class="font-black !border-none !text-white animate-pulse cursor-pointer shadow-sm"
              @click="openViewDetail(row)"
            >
              <Icon icon="solar:shield-check-bold" class="inline mr-1 text-xs" /> APPROVAL KREDIT
            </el-tag>

            <!-- 4. PESANAN KREDIT AKTIF (PIUTANG TEMPO) -->
            <el-tag 
              v-else-if="row.payment_method === 'kredit' && row.payment_status === 'unpaid'" 
              type="warning" 
              effect="dark" 
              size="small"
              class="font-black"
            >
              PIUTANG TEMPO
            </el-tag>

            <!-- 5. Belum bayar biasa (Transfer/Cash belum lunas) -->
            <el-tag 
              v-else-if="row.payment_status === 'unpaid'" 
              type="danger" 
              effect="dark" 
              size="small"
              class="font-black"
            >
              BELUM LUNAS
            </el-tag>

            <!-- 6. Lunas -->
            <el-tag 
              v-else-if="row.payment_status === 'paid'" 
              type="success" 
              effect="dark" 
              size="small"
              class="font-black"
            >
              LUNAS
            </el-tag>

            <!-- 7. Dibatalkan -->
            <el-tag 
              v-else 
              type="info" 
              effect="dark" 
              size="small"
              class="font-black"
            >
              BATAL
            </el-tag>

            <div class="text-[9px] mt-0.5 text-slate-400 font-bold italic uppercase">
              {{ row.payment_method === 'kredit' ? 'Kredit (Tempo)' : row.payment_method }}
            </div>
          </template>
        </el-table-column>

        <!-- STATUS LOGISTIK -->
        <el-table-column label="Logistik" width="115" align="center">
          <template #default="{ row }">
            <el-tag 
              :type="getShippingTagType(row.shipping_status)" 
              size="small" 
              effect="plain"
              class="font-bold uppercase text-[9px]"
            >
              {{ getShippingLabel(row.shipping_status) }}
            </el-tag>
            <div v-if="row.courier_name" class="text-[9px] text-slate-400 mt-0.5 truncate font-medium">
              {{ row.courier_name }}
            </div>
          </template>
        </el-table-column>

        <!-- TOTAL TAGIHAN -->
        <el-table-column label="Total Tagihan" width="130" align="right">
          <template #default="{ row }">
            <div class="font-bold text-[#1B293C]">Rp {{ formatNumber(row.total_amount) }}</div>
            <div v-if="row.shipping_fee > 0" class="text-[9px] text-[#00A9C3]">
              + Ongkir Rp {{ formatNumber(row.shipping_fee) }}
            </div>
          </template>
        </el-table-column>

        <!-- KOLOM AKSI -->
        <el-table-column label="Aksi" width="80" align="center" fixed="right">
          <template #default="{ row }">
            <el-tooltip :content="row.payment_status === 'waiting_shipping_fee' ? 'Input Ongkir Sekarang' : 'Lihat Detail Pesanan'" placement="top">
              <el-button 
                circle 
                size="small" 
                :type="row.payment_status === 'waiting_shipping_fee' ? 'warning' : ((row.payment_status === 'unpaid' && row.payment_proof) ? 'primary' : 'info')" 
                :plain="row.payment_status !== 'waiting_shipping_fee'" 
                @click="openViewDetail(row)"
              >
                <Icon :icon="row.payment_status === 'waiting_shipping_fee' ? 'solar:box-pencil-bold' : 'solar:eye-bold'" />
              </el-button>
            </el-tooltip>
          </template>
        </el-table-column>
      </el-table>
    </section>

    <!-- DIALOG KHUSUS: PUSAT ANTREAN VERIFIKASI PEMBAYARAN & KREDIT -->
    <el-dialog 
      v-model="isQueueOpen" 
      title="Pusat Antrean Verifikasi Pembayaran & Approval Kredit" 
      width="900px" 
      top="4vh" 
      append-to-body
      destroy-on-close
    >
      <div class="space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div>
            <h3 class="text-sm font-bold text-slate-800">Daftar Pesanan Menunggu Konfirmasi & Proses</h3>
            <p class="text-xs text-slate-500">Verifikasi struk transfer pembayaran lunas atau setujui pesanan tempo/kredit.</p>
          </div>
          <el-tag type="danger" size="large" class="!rounded-xl font-bold">
            {{ ordersWaitingVerification.length }} Pesanan Menunggu Tindakan
          </el-tag>
        </div>

        <!-- JIKA TIDAK ADA ANTREAN -->
        <div v-if="ordersWaitingVerification.length === 0" class="py-12 text-center space-y-2">
          <Icon icon="solar:check-circle-bold" class="text-5xl text-emerald-500 mx-auto" />
          <h4 class="text-sm font-bold text-slate-700">Semua Pesanan Sudah Diproses!</h4>
          <p class="text-xs text-slate-400">Tidak ada struk yang tertunda atau pengajuan kredit yang belum disetujui saat ini.</p>
        </div>

        <!-- DAFTAR KARTU ANTREAN -->
        <div v-else class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
          <div 
            v-for="order in ordersWaitingVerification" 
            :key="order.id" 
            class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col md:flex-row gap-4 items-start md:items-center justify-between"
          >
            <!-- Kolom 1: Struk Gambar ATAU Badge Kredit (Mencegah Placeholder FAILED) -->
            <div v-if="order.payment_method === 'kredit'" class="w-full md:w-36 h-28 bg-blue-50/80 border border-blue-200 rounded-xl shrink-0 flex flex-col items-center justify-center p-2 text-center text-blue-900">
              <Icon icon="solar:card-2-bold" class="text-3xl text-blue-600 mb-1" />
              <span class="text-[10px] font-black uppercase tracking-wide">Fasilitas Kredit</span>
              <span class="text-[9px] text-blue-600 font-medium">Jatuh Tempo 30 Hari</span>
              <span class="text-[8px] text-slate-400 mt-1 italic">Tanpa Bukti Transfer</span>
            </div>

            <div v-else class="w-full md:w-36 h-28 bg-white border border-slate-200 rounded-xl overflow-hidden shrink-0 flex items-center justify-center">
              <el-image 
                v-if="order.payment_proof"
                :src="order.payment_proof" 
                fit="contain" 
                class="w-full h-full cursor-pointer hover:scale-105 transition-transform" 
                :preview-src-list="[order.payment_proof]" 
              />
              <div v-else class="text-[10px] text-slate-400 text-center p-2">
                <Icon icon="solar:gallery-remove-linear" class="text-2xl mx-auto mb-1 opacity-50" />
                Belum ada struk
              </div>
            </div>

            <!-- Kolom 2: Info Pesanan & Pelanggan -->
            <div class="flex-1 space-y-1 text-xs">
              <div class="flex items-center gap-2">
                <span class="font-black text-[#1B293C] text-sm">{{ order.order_number }}</span>
                <el-tag size="small" effect="plain" class="font-bold uppercase text-[9px]">
                  {{ order.customer?.name }} ({{ order.customer?.type === 'penginjil' ? 'PL' : order.customer?.type?.toUpperCase() }})
                </el-tag>
              </div>
              <p class="text-slate-500 text-[11px]">
                Waktu Transaksi: <b>{{ formatDateTime(order.created_at) }}</b> • Metode: 
                <b :class="order.payment_method === 'kredit' ? 'text-blue-600' : 'text-slate-700'">
                  {{ order.payment_method === 'kredit' ? 'KREDIT / TEMPO' : order.payment_method?.toUpperCase() }}
                </b>
              </p>
              <div v-if="order.payment_method === 'kredit'" class="text-[11px] text-blue-700 bg-blue-50/80 px-2 py-0.5 rounded border border-blue-200 inline-block font-semibold">
                📌 Jatuh Tempo: 30 Hari ke depan (Masuk Buku Piutang)
              </div>
              <div v-if="order.notes" class="text-[11px] text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                📝 <i>"{{ order.notes }}"</i>
              </div>
            </div>

            <!-- Kolom 3: Tagihan & Tombol Aksi Sesuai Metode -->
            <div class="text-right shrink-0 space-y-2 w-full md:w-auto">
              <div>
                <p class="text-[10px] text-slate-400 uppercase font-bold">Total Pembayaran</p>
                <p class="text-base font-black text-[#00A9C3]">Rp {{ formatNumber(order.total_amount) }}</p>
              </div>
              <div class="flex gap-2 justify-end">
                <el-button size="small" plain @click="openViewDetail(order)">
                  Rincian
                </el-button>

                <!-- AKSI KREDIT: SETUJUI KREDIT (Status bayar tetap Belum Lunas/Piutang) -->
                <el-button 
                  v-if="order.payment_method === 'kredit'"
                  type="primary" 
                  color="#2563eb"
                  size="small" 
                  class="!rounded-xl font-bold" 
                  @click="quickApproveOrder(order)"
                  :loading="loadingQuickApprove === order.id"
                >
                  <Icon icon="solar:check-read-bold" class="mr-1" /> Setujui Kredit & Proses
                </el-button>

                <!-- AKSI TRANSFER/QRIS: ACC LUNAS -->
                <el-button 
                  v-else
                  type="success" 
                  size="small" 
                  class="!rounded-xl font-bold" 
                  @click="quickApproveOrder(order)"
                  :loading="loadingQuickApprove === order.id"
                >
                  <Icon icon="solar:check-read-bold" class="mr-1" /> ACC Lunas
                </el-button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <template #footer>
        <el-button @click="isQueueOpen = false">Tutup Antrean</el-button>
      </template>
    </el-dialog>

    <!-- DIALOG 1: INPUT PESANAN MANUAL (ASSISTED ORDER) -->
    <el-dialog 
      v-model="isCreateOpen" 
      title="Formulir Pemesanan Manual (Assisted Order)" 
      width="950px" 
      top="4vh" 
      destroy-on-close 
      append-to-body
    >
      <el-form label-position="top">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
          <el-form-item label="Waktu Transaksi" required>
            <el-date-picker 
              v-model="createForm.date" 
              type="datetime" 
              value-format="YYYY-MM-DD HH:mm:ss" 
              class="!w-full" 
            />
          </el-form-item>
          
          <el-form-item label="Pelanggan" required>
            <el-select 
              v-model="createForm.customer_id" 
              filterable 
              class="w-full" 
              placeholder="Pilih Pelanggan"
              @change="handleCustomerChange"
            >
              <el-option 
                v-for="c in customers" 
                :key="c.id" 
                :label="`${c.name} (${c.type === 'penginjil' ? 'PL' : c.type.toUpperCase()})`" 
                :value="c.id" 
              />
            </el-select>
          </el-form-item>

          <el-form-item label="Metode Pembayaran">
            <el-select v-model="createForm.payment_method" class="w-full">
              <el-option label="Cash (Tunai)" value="cash" />
              <el-option label="Transfer Bank Mandiri" value="transfer" />
              <el-option label="QRIS" value="qris" />
              <el-option v-if="selectedCustomerType !== 'jemaat'" label="Kredit / Tempo (Mitra)" value="kredit" />
            </el-select>
          </el-form-item>
        </div>

        <!-- OPSI PENGIRIMAN & ONGKIR -->
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl mb-4">
          <div class="font-bold text-slate-700 text-xs uppercase mb-3 tracking-wider">Opsi Pengiriman & Ongkir:</div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <el-form-item label="Metode Pengambilan">
              <el-select v-model="createForm.delivery_method" class="w-full">
                <el-option label="Ambil Sendiri di Kantor (Pickup)" value="pickup" />
                <el-option label="Ekspedisi / Kurir" value="delivery" />
              </el-select>
            </el-form-item>

            <el-form-item v-if="createForm.delivery_method === 'delivery'" label="Skema Ongkos Kirim">
              <el-select v-model="createForm.shipping_payment_type" class="w-full">
                <el-option label="Bayar di Tempat (COD Kurir)" value="cod" />
                <el-option label="Bayar Bersama Tagihan" value="prepaid" />
              </el-select>
            </el-form-item>

            <el-form-item v-if="createForm.delivery_method === 'delivery' && createForm.shipping_payment_type === 'prepaid'" label="Biaya Ongkir (Rp)">
              <el-input-number v-model="createForm.shipping_fee" :min="0" :step="5000" class="!w-full" controls-position="right" />
            </el-form-item>
          </div>

          <el-form-item v-if="createForm.delivery_method === 'delivery'" label="Alamat Tujuan Kirim">
            <el-input v-model="createForm.shipping_address" placeholder="Masukkan alamat lengkap pengiriman pesanan..." />
          </el-form-item>
        </div>

        <!-- CATATAN TAMBAHAN (ASSISTED ORDER) -->
        <div class="mb-4">
          <el-form-item label="Catatan Tambahan Pesanan (Opsional)">
            <el-input v-model="createForm.notes" placeholder="Catatan pesanan dari pembeli via telepon / WhatsApp..." />
          </el-form-item>
        </div>

        <!-- ITEM BUKU -->
        <div class="mb-4">
          <p class="text-xs font-bold uppercase text-slate-400 mb-2">Item Buku</p>
          <el-table :data="createForm.items" border class="w-full rounded-lg text-xs">
            <el-table-column label="Judul Buku">
              <template #default="scope">
                <el-select 
                  v-model="scope.row.book_id" 
                  filterable 
                  placeholder="Pilih Judul Buku"
                  class="w-full" 
                  @change="updateItemPrice(scope.$index)"
                >
                  <el-option 
                    v-for="b in books" 
                    :key="b.id" 
                    :label="`${b.title} (Sisa: ${b.stock})`" 
                    :value="b.id" 
                  />
                </el-select>
              </template>
            </el-table-column>
            <el-table-column label="Qty" width="120" align="center">
              <template #default="scope">
                <el-input-number v-model="scope.row.qty" :min="1" size="small" controls-position="right" class="!w-full" />
              </template>
            </el-table-column>
            <el-table-column label="Subtotal" width="140" align="right">
              <template #default="scope">
                <b>Rp {{ formatNumber(scope.row.price * scope.row.qty) }}</b>
              </template>
            </el-table-column>
            <el-table-column width="50" align="center">
              <template #default="scope">
                <el-button type="danger" link @click="createForm.items.splice(scope.$index, 1)">
                  <Icon icon="solar:trash-bin-trash-bold" />
                </el-button>
              </template>
            </el-table-column>
          </el-table>
          <el-button class="mt-2 !rounded-xl" type="primary" plain size="small" @click="addEmptyItem">
            + Tambah Buku
          </el-button>
        </div>

        <!-- TOTAL & PEMBAYARAN AWAL (DP) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
          <div v-if="selectedCustomerType !== 'jemaat'">
            <el-form-item label="Pembayaran Awal / DP (Opsional)">
              <el-input-number v-model="createForm.paid_amount" :min="0" :max="manualCalculatedGrandTotal" class="!w-full" />
            </el-form-item>
          </div>
          <div v-else class="text-xs text-amber-600 bg-amber-50 p-3 rounded-xl border border-amber-200">
            * Kategori <b>Jemaat Umum</b> wajib melunasi transaksi secara langsung (Cash/Transfer/QRIS).
          </div>

          <div class="p-4 bg-slate-900 rounded-2xl flex justify-between items-center text-white shadow-lg">
            <div>
              <p class="text-[9px] opacity-60 uppercase tracking-widest">Sisa Piutang</p>
              <p class="text-xl font-bold text-red-400">
                Rp {{ formatNumber(selectedCustomerType === 'jemaat' ? 0 : (manualCalculatedGrandTotal - createForm.paid_amount)) }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-[9px] opacity-60 uppercase tracking-widest">Grand Total</p>
              <p class="text-2xl font-black text-[#00A9C3]">Rp {{ formatNumber(manualCalculatedGrandTotal) }}</p>
            </div>
          </div>
        </div>
      </el-form>
      <template #footer>
        <el-button @click="isCreateOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" @click="submitManualOrder" :loading="loadingSubmit" class="!rounded-xl px-8 font-bold">
          SIMPAN PESANAN
        </el-button>
      </template>
    </el-dialog>

    <!-- DIALOG 2: DETAIL PESANAN & KELOLA ONGKIR -->
    <el-dialog v-model="isDetailOpen" title="Rincian Pesanan & Kelola Ongkir" width="850px" top="4vh" append-to-body>
      <div v-if="selectedOrder" class="space-y-4">
        <!-- ALERT JIKA MENUNGGU ESTIMASI ONGKIR -->
        <el-alert 
          v-if="selectedOrder.payment_status === 'waiting_shipping_fee'" 
          title="PESANAN MENUNGGU PENETAPAN ONGKIR" 
          type="warning" 
          description="Pelanggan memilih bayar ongkir bersama tagihan. Masukkan biaya ongkir riil di bawah ini, lalu klik 'SIMPAN PERUBAHAN & TERBITKAN TAGIHAN' agar status berubah ke siap dibayar." 
          show-icon 
          :closable="false"
        />

        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="bg-[#1B293C] p-4 rounded-2xl text-white shadow-md flex-1 min-w-[280px]">
            <p class="text-[10px] uppercase font-bold opacity-60 leading-none">No. Invoice</p>
            <p class="text-lg font-black">{{ selectedOrder.order_number }}</p>
            <p class="text-xs mt-1">{{ selectedOrder.customer?.name }} ({{ selectedOrder.customer?.type === 'penginjil' ? 'PL' : selectedOrder.customer?.type?.toUpperCase() }})</p>
          </div>
          <div class="flex gap-2">
            <!-- WA BUTTON -->
            <el-button 
              type="success" 
              size="large" 
              class="!h-14 !rounded-2xl font-bold" 
              @click="sendWhatsAppInvoice(selectedOrder)"
            >
              <Icon icon="logos:whatsapp-icon" class="text-lg mr-1.5" />
              KIRIM WA TAGIHAN
            </el-button>
            <el-button 
              type="primary" 
              color="#00A9C3" 
              size="large" 
              class="!h-14 !rounded-2xl font-bold" 
              @click="downloadInvoice(selectedOrder.id)"
            >
              CETAK INVOICE
            </el-button>
          </div>
        </div>

        <!-- INFO ALAMAT & PENGIRIMAN -->
        <div class="p-3 bg-blue-50/70 border border-blue-100 rounded-xl text-xs space-y-2">
          <div class="flex justify-between items-center">
            <span class="font-bold text-blue-900 uppercase tracking-wider">Metode Pengiriman:</span>
            <el-tag :type="selectedOrder.delivery_method === 'pickup' ? 'success' : 'primary'" class="font-bold uppercase">
              {{ selectedOrder.delivery_method === 'pickup' ? 'Ambil Sendiri di Kantor (Pickup)' : 'Ekspedisi Kurir' }}
            </el-tag>
          </div>

          <!-- KONDISI PICKUP -->
          <div v-if="selectedOrder.delivery_method === 'pickup'" class="border-t border-blue-100 pt-1.5 text-slate-700">
            <span class="font-bold text-slate-600">Lokasi Pengambilan: </span>
            <span class="text-blue-900 font-semibold">Kantor ABC Jakarta (Tebet, Jl. Dr. Saharjo) - Bebas Biaya Kirim</span>
          </div>

          <!-- KONDISI DELIVERY EKSPEDISI -->
          <template v-else>
            <div class="flex justify-between border-t border-blue-100 pt-1 text-slate-700">
              <span class="font-bold text-slate-600">Skema Ongkos Kirim:</span>
              <span class="font-semibold text-slate-800">
                {{ selectedOrder.shipping_payment_type === 'cod' ? 'Bayar di Tempat (COD Kurir Langsung)' : 'Dibayar Bersama Tagihan Buku' }}
              </span>
            </div>
            <div class="text-slate-700">
              <span class="font-bold text-slate-600">Alamat Pengiriman Tujuan: </span>
              <span class="font-medium text-slate-900">
                {{ selectedOrder.shipping_address || selectedOrder.customer?.address || '(Pelanggan belum mencantumkan alamat pengiriman)' }}
              </span>
            </div>
          </template>
        </div>

        <!-- KOTAK CATATAN PEMBELI -->
        <div v-if="selectedOrder.notes" class="p-3 bg-amber-50/80 border border-amber-200 rounded-xl text-xs">
          <div class="flex items-center gap-1.5 font-bold text-amber-900 mb-1">
            <Icon icon="solar:notes-bold" class="text-amber-600 text-sm" />
            <span>Catatan Tambahan dari Pembeli:</span>
          </div>
          <p class="text-slate-700 italic pl-5 bg-white/70 p-2.5 rounded-lg border border-amber-100 font-medium">
            "{{ selectedOrder.notes }}"
          </p>
        </div>
        
        <!-- TABEL BUKU -->
        <el-table :data="selectedOrder.items" border size="small" class="w-full text-xs">
          <el-table-column label="Buku">
            <template #default="{ row }">{{ row.book?.title }}</template>
          </el-table-column>
          <el-table-column prop="qty" label="Qty" width="70" align="center" />
          <el-table-column label="Subtotal" width="130" align="right">
            <template #default="{ row }">Rp {{ formatNumber(row.price_at_purchase * row.qty) }}</template>
          </el-table-column>
        </el-table>

        <!-- BIAYA & BUKTI BAYAR / BADGE KREDIT -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="border rounded-xl p-3 bg-slate-50">
            <p class="text-[10px] font-bold text-slate-400 uppercase mb-2">Informasi Pembayaran / Bukti</p>
            
            <!-- JIKA KREDIT: TAMPILKAN KARTU INFORMASI PIUTANG -->
            <div v-if="selectedOrder.payment_method === 'kredit'" class="h-28 rounded-xl border border-blue-200 bg-blue-50/60 p-3 flex flex-col justify-center text-center">
              <div class="flex items-center justify-center gap-1.5 text-blue-800 font-bold text-xs mb-1">
                <Icon icon="solar:card-2-bold" class="text-base text-blue-600" />
                <span>Transaksi Kredit / Tempo (30 Hari)</span>
              </div>
              <p class="text-[10px] text-slate-600">
                Menggunakan fasilitas tempo kemitraan. Tagihan masuk ke <b>Buku Piutang</b>.
              </p>
              <p class="text-[9px] text-blue-600 mt-1 font-semibold">
                Jatuh Tempo: {{ selectedOrder.due_date ? formatDateTime(selectedOrder.due_date) : '+30 Hari dari pemesanan' }}
              </p>
            </div>

            <!-- JIKA TRANSFER / CASH: GAMBAR STRUK -->
            <template v-else>
              <el-image 
                v-if="selectedOrder.payment_proof" 
                :src="selectedOrder.payment_proof" 
                class="h-28 rounded border bg-white w-full" 
                fit="contain" 
                :preview-src-list="[selectedOrder.payment_proof]" 
              />
              <div v-else class="h-28 rounded border border-dashed flex items-center justify-center text-slate-400 bg-white text-xs">
                Belum ada bukti struk terlampir
              </div>
              <el-upload action="#" :auto-upload="false" :show-file-list="false" :on-change="handleUploadProof">
                <el-button size="small" type="primary" plain class="w-full mt-2 !rounded-xl">Ganti / Upload Bukti</el-button>
              </el-upload>
            </template>
          </div>
          
          <div class="p-4 border rounded-2xl bg-orange-50/40 flex flex-col justify-between">
            <div class="space-y-1.5 text-xs text-slate-600">
              <div class="flex justify-between">
                <span>Subtotal Buku:</span>
                <span class="font-bold">Rp {{ formatNumber(modalBookSubtotal) }}</span>
              </div>
              <div class="flex justify-between text-[#00A9C3]">
                <span>Biaya Ekspedisi (Ongkir):</span>
                <span class="font-bold">Rp {{ formatNumber(shippingFeeInput) }}</span>
              </div>
              <div class="flex justify-between border-t pt-1 font-bold text-slate-800">
                <span>Total Tagihan:</span>
                <span>Rp {{ formatNumber(modalCalculatedTotal) }}</span>
              </div>
            </div>
            <div class="text-center border-t border-orange-200/50 pt-2 mt-2">
              <p class="text-[9px] font-bold text-orange-500 uppercase tracking-widest">Sisa Piutang / Belum Bayar</p>
              <p class="text-2xl font-black text-orange-600">Rp {{ formatNumber(modalCalculatedRemaining) }}</p>
            </div>
          </div>
        </div>

        <!-- FORM UPDATE ONGKIR, LOGISTIK, STATUS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <el-form-item label="Biaya Ekspedisi / Ongkir (Rp)">
            <el-input-number 
              v-model="shippingFeeInput" 
              :min="0" 
              :step="5000" 
              controls-position="right"
              class="!w-full" 
              :disabled="selectedOrder.delivery_method === 'pickup' || selectedOrder.shipping_payment_type === 'cod'"
            />
            <span class="text-[10px] text-slate-400">
              {{ selectedOrder.delivery_method === 'pickup' ? '* Bebas Ongkir (Ambil Sendiri di Kantor)' : (selectedOrder.shipping_payment_type === 'cod' ? '* Ongkir dibayar di tempat langsung ke kurir' : '* Memasukkan ongkir otomatis menerbitkan tagihan ke user') }}
            </span>
          </el-form-item>

          <el-form-item label="Status Pembayaran">
            <el-select v-model="selectedOrder.payment_status" class="w-full">
              <el-option label="Menunggu Estimasi Ongkir" value="waiting_shipping_fee" />
              <el-option label="Belum Lunas" value="unpaid" />
              <el-option label="Lunas" value="paid" />
            </el-select>
          </el-form-item>

          <el-form-item label="Kurir Ekspedisi">
            <el-select v-model="selectedOrder.courier_name" placeholder="Pilih Kurir" class="w-full" clearable>
              <el-option label="Kurir Internal ABC Jakarta" value="Kurir Kantor ABC" />
              <el-option label="J&T Express" value="J&T" />
              <el-option label="JNE" value="JNE" />
              <el-option label="SiCepat" value="SiCepat" />
              <el-option label="Ambil Langsung (Pickup)" value="Self Pickup" />
            </el-select>
          </el-form-item>

          <el-form-item label="Status Logistik">
            <el-select v-model="selectedOrder.shipping_status" class="w-full">
              <el-option label="Pending" value="pending" />
              <el-option label="Packing (Dikemas)" value="processing" />
              <el-option label="Shipping (Dalam Perjalanan)" value="shipping" />
              <el-option label="Delivered (Diterima)" value="delivered" />
            </el-select>
          </el-form-item>

          <el-form-item label="Nomor Resi Kurir" class="md:col-span-2">
            <el-input v-model="selectedOrder.tracking_number" placeholder="Contoh: JNT123456789" />
          </el-form-item>
        </div>

        <!-- TOMBOL AKSI DENGAN POPCONFIRM -->
        <div class="flex gap-3 pt-2">
          <el-popconfirm
            v-if="selectedOrder.payment_status !== 'cancelled'"
            title="Batalkan pesanan ini? Stok buku akan otomatis dikembalikan ke inventaris gudang."
            confirm-button-text="Ya, Batalkan"
            cancel-button-text="Batal"
            confirm-button-type="danger"
            width="280"
            @confirm="handleCancelOrder(selectedOrder.id)"
          >
            <template #reference>
              <el-button 
                type="danger" 
                plain 
                class="flex-1 !h-12 font-bold !rounded-2xl" 
                :loading="loadingCancel"
              >
                BATALKAN PESANAN
              </el-button>
            </template>
          </el-popconfirm>
          <el-button v-else type="info" disabled class="flex-1 !h-12 font-bold !rounded-2xl">
            SUDAH DIBATALKAN
          </el-button>

          <el-button 
            type="primary" 
            color="#00A9C3" 
            class="flex-[2] !h-12 font-bold shadow-lg !rounded-2xl" 
            @click="confirmUpdateStatus" 
            :loading="loadingUpdate"
          >
            SIMPAN PERUBAHAN & TERBITKAN TAGIHAN
          </el-button>
        </div>
      </div>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { ElMessage, ElMessageBox } from 'element-plus';
import { onMounted, reactive, ref, computed, watch } from 'vue';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const { apiFetch, uploadAttachment } = useApi();

const orders = ref<any[]>([]); 
const customers = ref([]); 
const books = ref([]); 
const loading = ref(false); 

// Filter States
const searchQuery = ref(''); 
const dateRange = ref([]); 
const filterStatus = ref(''); 
const filterType = ref(''); 

// Dialog states
const isCreateOpen = ref(false); 
const isDetailOpen = ref(false); 
const isQueueOpen = ref(false);
const selectedOrder = ref<any>(null); 
const paymentProofId = ref(null); 
const loadingSubmit = ref(false); 
const loadingUpdate = ref(false);
const loadingCancel = ref(false);
const loadingQuickApprove = ref<string | null>(null);
const shippingFeeInput = ref(0);

const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val || 0);
const formatDateTime = (dateStr: string) => new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });

// DAFTAR ANTREAN KHUSUS (MENGGABUNGKAN STRUK TRANSFER & APPROVAL KREDIT PENDING)
const ordersWaitingVerification = computed(() => {
  return orders.value.filter((o: any) => {
    // 1. Pesanan Transfer/QRIS yang sudah ada bukti dan belum lunas
    const needsProofVerification = o.payment_status === 'unpaid' && o.payment_proof && o.payment_method !== 'kredit';
    // 2. Pesanan Kredit yang masih pending (butuh persetujuan admin untuk diproses/packing)
    const needsCreditApproval = o.payment_method === 'kredit' && o.shipping_status === 'pending';
    
    return needsProofVerification || needsCreditApproval;
  });
});

// LIST TABEL DENGAN FILTER
const filteredOrdersList = computed(() => {
  if (filterStatus.value === 'needs_verification') {
    return ordersWaitingVerification.value;
  }
  return orders.value;
});

// FORM CREATE ASSISTED ORDER
const createForm = reactive({ 
  date: '', 
  customer_id: '', 
  delivery_method: 'pickup',
  shipping_payment_type: 'none',
  shipping_fee: 0,
  shipping_address: '',
  notes: '',
  payment_method: 'cash', 
  paid_amount: 0, 
  payment_proof_id: null, 
  items: [{ book_id: '', qty: 1, price: 0 }] 
});

const selectedCustomerType = computed(() => { 
  const c = customers.value.find((c: any) => c.id === createForm.customer_id); 
  return c ? c.type : 'jemaat'; 
});

const manualCalculatedGrandTotal = computed(() => {
  const booksTotal = createForm.items.reduce((sum, i) => sum + (i.price * i.qty), 0);
  const fee = (createForm.delivery_method === 'delivery' && createForm.shipping_payment_type === 'prepaid') 
    ? (Number(createForm.shipping_fee) || 0) 
    : 0;
  return booksTotal + fee;
});

const modalBookSubtotal = computed(() => {
  if (!selectedOrder.value?.items) return 0;
  return selectedOrder.value.items.reduce((sum: number, i: any) => sum + (Number(i.price_at_purchase || 0) * Number(i.qty || 1)), 0);
});

const modalCalculatedTotal = computed(() => {
  return modalBookSubtotal.value + (Number(shippingFeeInput.value) || 0);
});

const modalCalculatedRemaining = computed(() => {
  if (!selectedOrder.value) return 0;
  if (selectedOrder.value.payment_status === 'paid') return 0;
  const alreadyPaid = Math.max(0, (Number(selectedOrder.value.total_amount) || 0) - (Number(selectedOrder.value.remaining_amount) || 0));
  return Math.max(0, modalCalculatedTotal.value - alreadyPaid);
});

const hasActiveFilter = computed(() => {
  return !!(searchQuery.value || filterStatus.value || filterType.value || (dateRange.value && dateRange.value.length === 2));
});

const getShippingLabel = (status: string) => {
  switch (status) {
    case 'processing': return 'Packing';
    case 'shipping': return 'Dikirim';
    case 'delivered': return 'Diterima';
    case 'cancelled': return 'Batal';
    default: return 'Pending';
  }
};

const getShippingTagType = (status: string) => {
  switch (status) {
    case 'processing': return 'warning';
    case 'shipping': return 'primary';
    case 'delivered': return 'success';
    default: return 'info';
  }
};

const getCustomerTagType = (type: string) => {
  switch (type) {
    case 'penginjil': return 'warning';
    case 'gereja': return 'primary';
    case 'sekolah': return 'success';
    default: return 'info';
  }
};

watch([filterStatus, filterType, dateRange], () => { 
  if (filterStatus.value !== 'needs_verification') {
    loadData(); 
  }
});

watch(searchQuery, (newVal) => { if (!newVal || newVal.trim() === '') loadData(); });

const handleCustomerChange = () => { 
  const cust = customers.value.find((c: any) => c.id === createForm.customer_id);
  if (cust) {
    createForm.shipping_address = cust.address || '';
    if (cust.type === 'jemaat') { 
      createForm.payment_method = 'cash'; 
      createForm.paid_amount = manualCalculatedGrandTotal.value; 
    } else { 
      createForm.paid_amount = 0; 
    } 
  }
  refreshAllPrices(); 
};

watch(manualCalculatedGrandTotal, (newTotal) => { 
  if (selectedCustomerType.value === 'jemaat') createForm.paid_amount = newTotal; 
});

const loadData = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (searchQuery.value && searchQuery.value.trim()) params.append('search', searchQuery.value.trim());
    if (filterStatus.value && filterStatus.value !== 'needs_verification') params.append('payment_status', filterStatus.value);
    if (filterType.value) params.append('type', filterType.value);
    if (dateRange.value && Array.isArray(dateRange.value) && dateRange.value.length === 2) {
      params.append('start_date', dateRange.value[0]);
      params.append('end_date', dateRange.value[1]);
    }

    const queryStr = params.toString() ? `?${params.toString()}` : '';
    const [o, c, b] = await Promise.all([
      apiFetch(`/orders${queryStr}`), 
      apiFetch('/customers'), 
      apiFetch('/books')
    ]);
    
    orders.value = o.data || o; 
    customers.value = c.data || c; 
    books.value = b.data || b;
  } finally { 
    loading.value = false; 
  }
};

const resetFilters = () => {
  dateRange.value = [];
  filterStatus.value = '';
  filterType.value = '';
  searchQuery.value = '';
  loadData();
};

const downloadInvoice = (id: string) => window.open(`http://localhost:8000/api/orders/${id}/invoice`, '_blank');

const handleExportMonthly = () => {
  const params = new URLSearchParams();
  if (dateRange.value && Array.isArray(dateRange.value) && dateRange.value.length === 2) {
    params.append('start_date', dateRange.value[0]);
    params.append('end_date', dateRange.value[1]);
  }
  if (filterStatus.value && filterStatus.value !== 'needs_verification') params.append('payment_status', filterStatus.value);
  if (filterType.value) params.append('type', filterType.value);
  if (searchQuery.value) params.append('search', searchQuery.value);

  const url = `http://localhost:8000/api/orders/export/monthly?${params.toString()}`;
  window.open(url, '_blank');
};

const openCreateDialog = () => {
  const now = new Date(); 
  const localTime = (new Date(now.getTime() - (now.getTimezoneOffset() * 60000))).toISOString().slice(0, 19).replace('T', ' ');
  Object.assign(createForm, { 
    date: localTime, 
    customer_id: '', 
    delivery_method: 'pickup',
    shipping_payment_type: 'none',
    shipping_fee: 0,
    shipping_address: '',
    notes: '',
    payment_method: 'cash', 
    paid_amount: 0, 
    payment_proof_id: null, 
    items: [{ book_id: '', qty: 1, price: 0 }] 
  });
  isCreateOpen.value = true;
};

const updateItemPrice = (idx: number) => {
  const book = books.value.find((b: any) => b.id === createForm.items[idx].book_id);
  const cust = customers.value.find((c: any) => c.id === createForm.customer_id);
  if (book && cust) {
    createForm.items[idx].price = (cust.type === 'penginjil') ? (book.member_price || book.price) : book.price;
  }
};

const addEmptyItem = () => createForm.items.push({ book_id: '', qty: 1, price: 0 });
const refreshAllPrices = () => createForm.items.forEach((_, idx) => updateItemPrice(idx));

const submitManualOrder = async () => {
  if (!createForm.customer_id) return ElMessage.warning('Pilih pelanggan terlebih dahulu.');
  if (createForm.items.some(i => !i.book_id)) return ElMessage.warning('Pilih judul buku pada daftar pesanan.');

  loadingSubmit.value = true;
  try {
    await apiFetch('/orders', { 
      method: 'POST', 
      body: { 
        ...createForm,
        source: 'admin_manual'
      } 
    });
    ElMessage.success('Pesanan manual berhasil diterbitkan!'); 
    isCreateOpen.value = false; 
    loadData();
  } catch (e: any) { 
    ElMessage.error(e.data?.message || 'Gagal menyimpan pesanan!'); 
  } finally { 
    loadingSubmit.value = false; 
  }
};

const openViewDetail = (row: any) => { 
  selectedOrder.value = JSON.parse(JSON.stringify(row)); 
  paymentProofId.value = null;
  shippingFeeInput.value = Number(selectedOrder.value.shipping_fee || 0);
  isDetailOpen.value = true; 
};

// 1-KLIK ACC & PROSES (MENGAKOMODASI CASH VS KREDIT)
const quickApproveOrder = async (order: any) => {
  loadingQuickApprove.value = order.id;
  try {
    const isCredit = order.payment_method === 'kredit';

    await apiFetch(`/orders/${order.id}`, {
      method: 'PUT',
      body: {
        // Jika kredit: payment_status tetap 'unpaid' (piutang aktif)
        // Jika transfer/cash: payment_status diubah jadi 'paid'
        payment_status: isCredit ? 'unpaid' : 'paid',
        shipping_status: order.shipping_status === 'pending' ? 'processing' : order.shipping_status
      }
    });

    if (isCredit) {
      ElMessage.success(`Pesanan kredit ${order.order_number} disetujui! Status logistik: Packing.`);
    } else {
      ElMessage.success(`Pembayaran ${order.order_number} berhasil diverifikasi Lunas! Status: Packing.`);
    }
    await loadData();
  } catch (e: any) {
    ElMessage.error(e.data?.message || 'Gagal memproses verifikasi.');
  } finally {
    loadingQuickApprove.value = null;
  }
};

// WHATSAPP GATEWAY
const sendWhatsAppInvoice = async (order: any) => {
  let phone = order.customer?.phone;

  if (!phone) {
    try {
      const { value } = await ElMessageBox.prompt(
        'Nomor WhatsApp pelanggan belum tercatat di profil. Masukkan nomor WhatsApp untuk mengirimkan rincian tagihan:',
        'Kirim Tagihan via WhatsApp',
        {
          confirmButtonText: 'Kirim WhatsApp',
          cancelButtonText: 'Batal',
          inputPattern: /^[0-9+]{9,15}$/,
          inputErrorMessage: 'Nomor WhatsApp tidak valid (contoh: 081234567890)',
        }
      );
      phone = value;
    } catch {
      return;
    }
  }

  let cleanPhone = phone.replace(/[^0-9]/g, '');
  if (cleanPhone.startsWith('0')) cleanPhone = '62' + cleanPhone.slice(1);

  const bSubtotal = modalBookSubtotal.value;
  const fee = Number(shippingFeeInput.value) || 0;

  let message = `Salam sejahtera Bpk/Ibu *${order.customer?.name}*,\n` +
    `Kami dari *Adventist Book Center (ABC) Jakarta* menginformasikan rincian tagihan pesanan Anda:\n\n` +
    `📄 *No. Invoice*: ${order.order_number}\n` +
    `📚 *Subtotal Buku*: Rp ${formatNumber(bSubtotal)}\n`;

  if (order.delivery_method === 'pickup') {
    message += `🏪 *Pengiriman*: Ambil Sendiri di Kantor ABC Jakarta (Bebas Ongkir)\n`;
  } else {
    if (order.shipping_payment_type === 'cod') {
      message += `🚚 *Ongkos Kirim*: Dibayar langsung ke kurir saat barang tiba (COD)\n`;
    } else {
      message += `🚚 *Ongkos Kirim*: Rp ${formatNumber(fee)}\n`;
    }
  }

  if (order.notes) {
    message += `📝 *Catatan Khusus*: ${order.notes}\n`;
  }

  message += `💰 *Grand Total*: *Rp ${formatNumber(modalCalculatedTotal.value)}*\n` +
    `📌 *Sisa yang Harus Dibayar*: *Rp ${formatNumber(modalCalculatedRemaining.value)}*\n\n` +
    `Pembayaran dapat ditransfer ke rekening resmi ABC Jakarta:\n` +
    `🏦 *Bank Mandiri*: 123-00-9876543-2\n` +
    `a.n. *Adventist Book Center Jakarta*\n\n` +
    `Mohon mengirimkan foto bukti transfer/QRIS balasan di chat ini setelah membayar. Terima kasih, Tuhan memberkati! 🙏`;

  window.open(`https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(message)}`, '_blank');
};

const handleUploadProof = async (file: any) => {
  try {
    const res = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Order', type: 'payment_proof' });
    paymentProofId.value = res.id; 
    selectedOrder.value.payment_proof = res.url; 
    ElMessage.success('Bukti pembayaran terunggah!');
  } catch (e) {
    ElMessage.error('Gagal mengunggah gambar bukti struk.');
  }
};

const confirmUpdateStatus = async () => {
  loadingUpdate.value = true;
  try {
    await apiFetch(`/orders/${selectedOrder.value.id}`, { 
      method: 'PUT', 
      body: { 
        shipping_fee: shippingFeeInput.value,
        payment_status: selectedOrder.value.payment_status,
        shipping_status: selectedOrder.value.shipping_status,
        courier_name: selectedOrder.value.courier_name,
        tracking_number: selectedOrder.value.tracking_number,
        shipping_address: selectedOrder.value.shipping_address,
        notes: selectedOrder.value.notes,
        payment_proof_id: paymentProofId.value || selectedOrder.value.payment_proof_id 
      } 
    });
    ElMessage.success('Perubahan pesanan dan biaya ongkir berhasil disimpan!'); 
    isDetailOpen.value = false; 
    loadData();
  } catch (e: any) { 
    ElMessage.error(e.data?.message || 'Gagal memperbarui status!'); 
  } finally { 
    loadingUpdate.value = false; 
  }
};

const handleCancelOrder = async (id: string) => {
  loadingCancel.value = true;
  try {
    await apiFetch(`/orders/${id}/cancel`, { method: 'POST' });
    ElMessage.success('Pesanan berhasil dibatalkan dan stok buku dikembalikan ke gudang.');
    isDetailOpen.value = false;
    loadData();
  } catch (e: any) {
    ElMessage.error(e.data?.message || 'Gagal membatalkan pesanan.');
  } finally {
    loadingCancel.value = false;
  }
};

onMounted(loadData);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { 
  @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; 
}
</style>