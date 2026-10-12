<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- 1. HEADER -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Pesanan Masuk</h2>
          <p class="text-sm text-slate-500">Monitoring transaksi, logistik, dan dokumen invoice.</p>
        </div>
        <div class="flex items-center gap-2">
          <el-button type="warning" plain @click="handleExportMonthly" class="!rounded-xl font-semibold">
            <Icon icon="solar:document-bold" class="mr-1" /> Cetak Laporan Terfilter
          </el-button>
          <el-button type="primary" color="#00a9c3" @click="openCreateDialog" class="!rounded-xl shadow-lg font-semibold">
            + Pesanan Manual
          </el-button>
        </div>
      </div>

      <!-- 2. FILTER BAR (REAKTIF & OTOMATIS) -->
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
        
        <!-- Filter Status Bayar (Ukuran Pas & Anti-Truncate) -->
        <el-select 
          v-model="filterStatus" 
          placeholder="Status Bayar" 
          clearable 
          class="!w-36" 
          @change="loadData"
        >
          <el-option label="Lunas" value="paid" />
          <el-option label="Belum Lunas" value="unpaid" />
        </el-select>

        <!-- Filter Tipe Pelanggan (Pengganti 'Lainnya') -->
        <el-select 
          v-model="filterType" 
          placeholder="Tipe Pelanggan" 
          clearable 
          class="!w-40"
          @change="loadData"
        >
          <el-option label="Jemaat" value="jemaat" />
          <el-option label="Penginjil (PL)" value="penginjil" />
          <el-option label="Gereja" value="gereja" />
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

        <!-- Tombol Reset Semua Filter -->
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
      <el-table :data="orders" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden text-xs">
        <el-table-column label="Waktu" width="130" align="center">
          <template #default="{ row }">
            <div class="font-bold text-slate-600 uppercase">{{ formatDateTime(row.created_at) }}</div>
          </template>
        </el-table-column>

        <el-table-column prop="order_number" label="No. Invoice" width="170">
          <template #default="{ row }">
            <span class="font-bold text-[#1B293C]">{{ row.order_number }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Pelanggan" min-width="180">
          <template #default="{ row }">
            <div class="font-bold text-slate-800">{{ row.customer?.name || 'Pelanggan Umum' }}</div>
            <el-tag 
              v-if="row.customer" 
              size="small" 
              effect="plain"
              :type="row.customer.type === 'penginjil' ? 'warning' : (row.customer.type === 'gereja' ? 'primary' : 'info')"
              class="!text-[9px] h-4 px-1.5 uppercase font-black mt-0.5"
            >
              {{ row.customer.type === 'penginjil' ? 'PL' : row.customer.type }}
            </el-tag>
          </template>
        </el-table-column>

        <!-- KOLOM PEMBAYARAN -->
        <el-table-column label="Pembayaran" width="120" align="center">
          <template #default="{ row }">
            <el-tag 
              :type="row.payment_status === 'paid' ? 'success' : 'danger'" 
              effect="dark" 
              size="small"
              class="font-black"
            >
              {{ row.payment_status === 'paid' ? 'PAID' : 'UNPAID' }}
            </el-tag>
            <div class="text-[9px] mt-0.5 text-slate-400 font-bold italic uppercase">{{ row.payment_method }}</div>
          </template>
        </el-table-column>

        <!-- KOLOM BARU: STATUS LOGISTIK & PENGIRIMAN -->
        <el-table-column label="Logistik" width="125" align="center">
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

        <el-table-column label="Total" width="130" align="right">
          <template #default="{ row }">
            <span class="font-bold text-[#1B293C]">Rp {{ formatNumber(row.total_amount) }}</span>
          </template>
        </el-table-column>

        <!-- KOLOM AKSI -->
        <el-table-column label="Aksi" width="80" align="center" fixed="right">
          <template #default="{ row }">
            <el-tooltip content="Lihat Detail Pesanan & Invoice" placement="top">
              <el-button circle size="small" type="primary" plain @click="openViewDetail(row)">
                <Icon icon="solar:eye-bold" />
              </el-button>
            </el-tooltip>
          </template>
        </el-table-column>
      </el-table>
    </section>

    <!-- DIALOG 1: INPUT PESANAN BARU (ASSISTED ORDER) -->
    <el-dialog 
      v-model="isCreateOpen" 
      title="Formulir Input Pesanan Baru" 
      width="950px" 
      top="5vh" 
      destroy-on-close 
      append-to-body
    >
      <el-form label-position="top">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
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
                :label="`${c.name} (${c.type.toUpperCase()})`" 
                :value="c.id" 
              />
            </el-select>
          </el-form-item>
          
          <el-form-item label="Metode Pembayaran">
            <el-select v-model="createForm.payment_method" class="w-full">
              <el-option label="Cash" value="cash" />
              <el-option label="Transfer Bank" value="transfer" />
              <el-option v-if="selectedCustomerType !== 'jemaat'" label="Kredit (Piutang)" value="kredit" />
            </el-select>
          </el-form-item>
          
          <el-form-item v-if="selectedCustomerType !== 'jemaat'" label="Bayar DP / Awal">
            <el-input-number 
              v-model="createForm.paid_amount" 
              :min="0" 
              :max="calculatedTotal" 
              class="!w-full" 
            />
          </el-form-item>
          <el-form-item v-else label="Aturan Bayar">
            <el-tag type="success" class="w-full h-8 flex justify-center items-center font-bold">
              WAJIB LUNAS (CASH)
            </el-tag>
          </el-form-item>
        </div>

        <!-- Kolom Alamat Kirim Khusus Pesanan (Dapat Disesuaikan / Diedit) -->
        <div class="mb-4">
          <el-form-item label="Alamat / Tujuan Pengiriman Khusus">
            <el-input 
              v-model="createForm.shipping_address" 
              placeholder="Alamat kirim (otomatis terisi dari profil, dapat diubah jika dikirim ke tempat lain)" 
            />
          </el-form-item>
        </div>

        <!-- Item Buku -->
        <div class="mb-6">
          <p class="text-xs font-bold uppercase text-slate-400 mb-3">Item Buku</p>
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
                    :label="`${b.title} (Sisa Stok: ${b.stock})`" 
                    :value="b.id" 
                  />
                </el-select>
              </template>
            </el-table-column>
            <el-table-column label="Qty" width="130" align="center">
              <template #default="scope">
                <el-input-number 
                  v-model="scope.row.qty" 
                  :min="1" 
                  size="small" 
                  controls-position="right" 
                  class="!w-full" 
                />
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
          <el-button class="mt-3 !rounded-xl" type="primary" plain size="small" @click="addEmptyItem">
            + Tambah Buku
          </el-button>
        </div>

        <!-- Bukti Bayar & Ringkasan Total -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
          <div class="border rounded-xl p-4 bg-slate-50">
            <p class="text-[11px] font-bold text-slate-400 mb-3 text-center uppercase">Lampirkan Bukti Transfer (Opsional)</p>
            <el-upload 
              action="#" 
              :auto-upload="false" 
              :show-file-list="false" 
              :on-change="handleCreateUploadProof"
            >
              <div class="h-32 border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-[#00A9C3] bg-white transition-colors">
                <template v-if="!createForm.payment_proof_id">
                  <Icon icon="solar:camera-add-bold" class="text-3xl text-slate-300" />
                  <p class="text-[10px] mt-2 text-slate-500">Pilih Foto Struk / Bukti Bayar</p>
                </template>
                <template v-else>
                  <Icon icon="solar:check-circle-bold" class="text-4xl text-green-500" />
                  <p class="text-[10px] mt-2 font-bold text-green-600">FOTO BUKTI TERLAMPIR</p>
                </template>
              </div>
            </el-upload>
          </div>
          
          <div class="p-6 bg-slate-900 rounded-2xl flex justify-between items-center text-white shadow-xl h-fit">
            <div>
              <p class="text-[10px] opacity-60 uppercase tracking-widest">Sisa Piutang</p>
              <p class="text-2xl font-black text-red-400">
                Rp {{ formatNumber(selectedCustomerType === 'jemaat' ? 0 : (calculatedTotal - createForm.paid_amount)) }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-[10px] opacity-60 uppercase tracking-widest">Total Tagihan</p>
              <p class="text-4xl font-black text-[#00A9C3]">Rp {{ formatNumber(calculatedTotal) }}</p>
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

    <!-- DIALOG 2: DETAIL PESANAN & INVOICE -->
    <el-dialog v-model="isDetailOpen" title="Detail Pesanan & Invoice" width="850px" top="5vh" append-to-body>
      <div v-if="selectedOrder" class="space-y-6">
        <div class="flex justify-between items-start">
          <div class="bg-[#1B293C] p-4 rounded-2xl text-white shadow-lg flex-1 mr-4">
            <p class="text-[10px] uppercase font-bold opacity-60 leading-none">No. Invoice</p>
            <p class="text-lg font-black">{{ selectedOrder.order_number }}</p>
            <p class="text-xs mt-1">{{ selectedOrder.customer?.name }} ({{ selectedOrder.customer?.type?.toUpperCase() }})</p>
          </div>
          <el-button type="success" size="large" class="!h-16 !rounded-2xl font-bold" @click="downloadInvoice(selectedOrder.id)">
            CETAK INVOICE
          </el-button>
        </div>

        <!-- Info Alamat Tujuan Kirim -->
        <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl text-xs">
          <p class="font-bold text-blue-600 mb-1 uppercase tracking-widest">Tujuan Kirim:</p>
          <p class="text-slate-700 font-medium">
            {{ selectedOrder.shipping_address || selectedOrder.customer?.address || 'Alamat Kosong (Diambil Langsung / Pickup)' }}
          </p>
        </div>
        
        <!-- Tabel Item Barang -->
        <el-table :data="selectedOrder.items" border size="small" class="w-full text-xs">
          <el-table-column label="Buku">
            <template #default="{ row }">{{ row.book?.title }}</template>
          </el-table-column>
          <el-table-column prop="qty" label="Qty" width="80" align="center" />
          <el-table-column label="Subtotal" width="140" align="right">
            <template #default="{ row }">Rp {{ formatNumber(row.price_at_purchase * row.qty) }}</template>
          </el-table-column>
        </el-table>

        <!-- Bukti Bayar & Sisa Tagihan -->
        <div class="grid grid-cols-2 gap-6">
          <div class="border rounded-xl p-4 bg-slate-50">
            <p class="text-[11px] font-bold text-slate-400 uppercase mb-2">Bukti Pembayaran</p>
            <el-image 
              v-if="selectedOrder.payment_proof" 
              :src="selectedOrder.payment_proof" 
              class="h-32 rounded border bg-white w-full" 
              fit="contain" 
              :preview-src-list="[selectedOrder.payment_proof]" 
            />
            <div v-else class="h-32 rounded border border-dashed flex items-center justify-center text-slate-400 bg-white">
              Tidak Ada Bukti Struk
            </div>
            <el-upload action="#" :auto-upload="false" :show-file-list="false" :on-change="handleUploadProof">
              <el-button size="small" type="primary" plain class="w-full mt-2 !rounded-xl">Ganti Bukti</el-button>
            </el-upload>
          </div>
          
          <div class="p-6 border rounded-2xl bg-orange-50/40 flex flex-col justify-center text-center">
            <p class="text-[11px] font-bold text-orange-500 uppercase tracking-widest">Sisa Tagihan</p>
            <p class="text-3xl font-black text-orange-600">Rp {{ formatNumber(selectedOrder.remaining_amount) }}</p>
          </div>
        </div>

        <!-- Form Update Status Pembayaran & Logistik -->
        <div class="grid grid-cols-2 gap-4">
          <el-form-item label="Status Pembayaran">
            <el-select v-model="selectedOrder.payment_status" class="w-full">
              <el-option label="Belum Lunas" value="unpaid" />
              <el-option label="Lunas" value="paid" />
            </el-select>
          </el-form-item>
          
          <el-form-item label="Status Logistik (Pengiriman)">
            <el-select v-model="selectedOrder.shipping_status" class="w-full">
              <el-option label="Pending" value="pending" />
              <el-option label="Packing (Dikemas)" value="processing" />
              <el-option label="Dikirim (Shipping)" value="shipping" />
              <el-option label="Diterima (Delivered)" value="delivered" />
            </el-select>
          </el-form-item>
          
          <el-form-item label="Kurir Ekspedisi">
            <el-select v-model="selectedOrder.courier_name" placeholder="Pilih Kurir" class="w-full">
              <el-option label="Kurir Kantor ABC" value="Kurir Kantor ABC" />
              <el-option label="J&T Express" value="J&T" />
              <el-option label="SiCepat" value="SiCepat" />
              <el-option label="JNE" value="JNE" />
              <el-option label="Ambil Sendiri (Pickup)" value="Self Pickup" />
            </el-select>
          </el-form-item>
          
          <el-form-item label="Nomor Resi Pengiriman">
            <el-input v-model="selectedOrder.tracking_number" placeholder="Masukkan No. Resi..." @keyup.enter="confirmUpdateStatus" />
          </el-form-item>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex gap-3">
          <el-button 
            type="danger" 
            plain 
            class="flex-1 !h-14 font-bold !rounded-2xl" 
            :disabled="selectedOrder.payment_status === 'cancelled'"
            @click="handleCancelOrder(selectedOrder.id)"
          >
            {{ selectedOrder.payment_status === 'cancelled' ? 'SUDAH DIBATALKAN' : 'BATALKAN PESANAN' }}
          </el-button>
          <el-button 
            type="primary" 
            color="#00A9C3" 
            class="flex-[2] !h-14 font-bold shadow-lg !rounded-2xl" 
            @click="confirmUpdateStatus" 
            :loading="loadingUpdate"
          >
            SIMPAN PERUBAHAN
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

const orders = ref([]); 
const customers = ref([]); 
const books = ref([]); 
const loading = ref(false); 

// Variabel Filter
const searchQuery = ref(''); 
const dateRange = ref([]); 
const filterStatus = ref(''); 
const filterType = ref(''); 
const filterMethod = ref('');

// Dialog States
const isCreateOpen = ref(false); 
const isDetailOpen = ref(false); 
const selectedOrder = ref(null); 
const paymentProofId = ref(null); 
const loadingSubmit = ref(false); 
const loadingUpdate = ref(false);

const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val || 0);
const formatDateTime = (dateStr: string) => new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });

// Form Create Pesanan Manual
const createForm = reactive({ 
  date: '', 
  customer_id: '', 
  shipping_address: '',
  payment_method: 'cash', 
  paid_amount: 0, 
  payment_proof_id: null, 
  items: [{ book_id: '', qty: 1, price: 0 }] 
});

const selectedCustomerType = computed(() => { 
  const c = customers.value.find(c => c.id === createForm.customer_id); 
  return c ? c.type : 'jemaat'; 
});

const calculatedTotal = computed(() => createForm.items.reduce((sum, i) => sum + (i.price * i.qty), 0));

// Cek apakah ada filter yang sedang aktif
const hasActiveFilter = computed(() => {
  return !!(searchQuery.value || filterStatus.value || filterType.value || (dateRange.value && dateRange.value.length === 2));
});

// Helper Label Logistik
const getShippingLabel = (status: string) => {
  switch (status) {
    case 'processing': return 'Packing';
    case 'shipping': return 'Dikirim';
    case 'delivered': return 'Diterima';
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

// AUTO-RESET: Watcher agar filter otomatis memuat ulang saat di-clear
watch([filterStatus, filterType, dateRange], () => {
  loadData();
});

watch(searchQuery, (newVal) => {
  if (!newVal || newVal.trim() === '') {
    loadData();
  }
});

const handleCustomerChange = () => { 
  const cust = customers.value.find(c => c.id === createForm.customer_id);
  if (cust) {
    // Otomatis terisi alamat profil pelanggan, tetapi tetap bisa diedit
    createForm.shipping_address = cust.address || '';
    
    if (cust.type === 'jemaat') { 
      createForm.payment_method = 'transfer'; 
      createForm.paid_amount = calculatedTotal.value; 
    } else { 
      createForm.paid_amount = 0; 
    } 
  }
  refreshAllPrices(); 
};

watch(calculatedTotal, (newTotal) => { 
  if (selectedCustomerType.value === 'jemaat') createForm.paid_amount = newTotal; 
});

// LOAD DATA BERSIH (Bebas dari null string param)
const loadData = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (searchQuery.value && searchQuery.value.trim()) params.append('search', searchQuery.value.trim());
    if (filterStatus.value) params.append('payment_status', filterStatus.value);
    if (filterType.value) params.append('type', filterType.value);
    if (filterMethod.value) params.append('payment_method', filterMethod.value);
    if (dateRange.value && Array.isArray(dateRange.value) && dateRange.value.length === 2 && dateRange.value[0] && dateRange.value[1]) {
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

// Reset Semua Filter dalam 1 Klik
const resetFilters = () => {
  dateRange.value = [];
  filterStatus.value = '';
  filterType.value = '';
  filterMethod.value = '';
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
  if (filterStatus.value) params.append('payment_status', filterStatus.value);
  if (filterType.value) params.append('type', filterType.value);
  if (searchQuery.value) params.append('search', searchQuery.value);

  const url = `http://localhost:8000/api/orders/export/monthly?${params.toString()}`;
  window.open(url, '_blank');
};

const openCreateDialog = () => {
  const now = new Date(); 
  const localTime = (new Date(now - (now.getTimezoneOffset() * 60000))).toISOString().slice(0, 19).replace('T', ' ');
  Object.assign(createForm, { 
    date: localTime, 
    customer_id: '', 
    shipping_address: '',
    payment_method: 'cash', 
    paid_amount: 0, 
    payment_proof_id: null, 
    items: [{ book_id: '', qty: 1, price: 0 }] 
  });
  isCreateOpen.value = true;
};

const updateItemPrice = (idx: number) => {
  const book = books.value.find(b => b.id === createForm.items[idx].book_id);
  const cust = customers.value.find(c => c.id === createForm.customer_id);
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
    const finalPaid = selectedCustomerType.value === 'jemaat' ? calculatedTotal.value : createForm.paid_amount;
    await apiFetch('/orders', { 
      method: 'POST', 
      body: { 
        ...createForm, 
        total_amount: calculatedTotal.value, 
        paid_amount: finalPaid 
      } 
    });
    ElMessage.success('Pesanan berhasil dibuat!'); 
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
  isDetailOpen.value = true; 
};

const handleUploadProof = async (file: any) => {
  try {
    const res = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Order', type: 'payment_proof' });
    paymentProofId.value = res.id; 
    selectedOrder.value.payment_proof = res.url; 
    ElMessage.success('Bukti pembayaran terunggah!');
  } catch (e) {
    ElMessage.error('Gagal mengunggah bukti struk.');
  }
};

const handleCreateUploadProof = async (file: any) => {
  try {
    const res = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Order', type: 'payment_proof' });
    createForm.payment_proof_id = res.id; 
    ElMessage.success('Foto bukti bayar terlampir!');
  } catch (e) {
    ElMessage.error('Gagal melampirkan foto bukti.');
  }
};

const confirmUpdateStatus = async () => {
  loadingUpdate.value = true;
  try {
    await apiFetch(`/orders/${selectedOrder.value.id}`, { 
      method: 'PUT', 
      body: { 
        ...selectedOrder.value, 
        payment_proof_id: paymentProofId.value || selectedOrder.value.payment_proof_id 
      } 
    });
    ElMessage.success('Data pesanan dan pengiriman diperbarui!'); 
    isDetailOpen.value = false; 
    loadData();
  } catch (e: any) { 
    ElMessage.error(e.data?.message || 'Gagal memperbarui status!'); 
  } finally { 
    loadingUpdate.value = false; 
  }
};

// BATALKAN PESANAN BERPROTEKSI KONFIRMASI
const handleCancelOrder = async (id: string) => {
  try {
    await ElMessageBox.confirm(
      'Apakah Anda yakin ingin membatalkan pesanan ini? Stok buku akan otomatis dikembalikan ke katalog inventaris dan sisa piutang akan disesuaikan.', 
      'Konfirmasi Pembatalan', 
      { 
        type: 'warning',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Kembali',
        confirmButtonClass: 'el-button--danger'
      }
    );
    
    loadingUpdate.value = true;
    await apiFetch(`/orders/${id}/cancel`, { method: 'POST' });
    ElMessage.success('Pesanan berhasil dibatalkan dan stok buku dikembalikan.');
    isDetailOpen.value = false;
    loadData();
  } catch (e: any) {
    if (e !== 'cancel') {
      ElMessage.error(e.data?.message || 'Gagal membatalkan pesanan.');
    }
  } finally {
    loadingUpdate.value = false;
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