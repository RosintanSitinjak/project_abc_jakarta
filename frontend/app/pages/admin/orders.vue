<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- HEADER -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Pesanan Masuk</h2>
          <p class="text-sm text-slate-500">Monitoring transaksi, verifikasi pembayaran, dan status logistik.</p>
        </div>
        <div class="flex items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Cari No. Invoice..." clearable class="w-full sm:w-64" @keyup.enter="loadData">
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          <el-button type="primary" color="#00a9c3" @click="openCreateDialog">
            + Pesanan Manual
          </el-button>
        </div>
      </div>

      <!-- TABEL UTAMA -->
      <div class="mt-6 overflow-x-auto">
        <el-table :data="orders" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
          <el-table-column label="Waktu" width="140" align="center">
            <template #default="{ row }">
              <div class="text-[10px] font-bold text-slate-500 uppercase">{{ formatDateTime(row.created_at) }}</div>
            </template>
          </el-table-column>
          <el-table-column prop="order_number" label="No. Invoice" width="160" />
          <el-table-column label="Pelanggan" min-width="180">
             <template #default="{ row }">
                <div class="font-bold text-slate-700">{{ row.customer?.name }}</div>
                <div class="text-[9px] text-[#00a9c3] font-bold uppercase">{{ row.customer?.type }}</div>
             </template>
          </el-table-column>
          <el-table-column label="Pembayaran" width="130" align="center">
            <template #default="{ row }">
              <el-tag :type="row.payment_status === 'paid' ? 'success' : 'danger'" effect="dark" size="small">
                {{ row.payment_status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Total" width="140">
            <template #default="{ row }">
              <span class="font-bold text-[#1B293C]">Rp {{ formatNumber(row.total_amount) }}</span>
            </template>
          </el-table-column>
          <el-table-column label="Logistik" width="130" align="center">
            <template #default="{ row }">
              <el-tag size="small" effect="plain" :type="row.shipping_status === 'delivered' ? 'success' : 'info'">
                {{ row.shipping_status ? row.shipping_status.toUpperCase() : 'PENDING' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Aksi" width="100" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-1">
                <el-button circle size="small" type="primary" plain @click="openViewDetail(row)">
                  <Icon icon="solar:eye-bold" />
                </el-button>
                <el-button circle size="small" type="danger" plain @click="handleDelete(row)">
                  <Icon icon="solar:trash-bin-trash-bold" />
                </el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- DIALOG 1: PESANAN MANUAL (ASSISTED) -->
    <el-dialog v-model="isCreateOpen" title="Formulir Input Pesanan (Assisted)" width="900px" top="5vh" append-to-body>
      <el-form label-position="top">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <el-form-item label="Waktu Transaksi" required>
            <el-date-picker v-model="createForm.date" type="datetime" format="YYYY-MM-DD HH:mm" value-format="YYYY-MM-DD HH:mm:ss" class="!w-full" />
          </el-form-item>
          <el-form-item label="Nama Pelanggan" required>
            <el-select v-model="createForm.customer_id" filterable placeholder="Pilih Pelanggan" class="w-full" @change="handleCustomerChange">
              <el-option v-for="c in customers" :key="c.id" :label="`${c.name} (${c.type.toUpperCase()})`" :value="c.id" />
            </el-select>
          </el-form-item>
          <el-form-item label="Metode Pembayaran">
            <el-select v-model="createForm.payment_method" class="w-full">
              <el-option label="Tunai (Cash)" value="cash" />
              <el-option label="Transfer Bank" value="transfer" />
              <el-option v-if="selectedCustomerType !== 'jemaat'" label="Kredit (Tempo 30 Hari)" value="kredit" />
            </el-select>
          </el-form-item>
        </div>

        <div class="mt-4">
          <p class="text-xs font-bold uppercase text-slate-400 mb-3 tracking-widest">Detail Item Buku</p>
          <el-table :data="createForm.items" border class="rounded-lg">
            <el-table-column label="Judul Buku">
              <template #default="scope">
                <el-select v-model="scope.row.book_id" filterable placeholder="Pilih Buku" class="w-full" @change="updateItemPrice(scope.$index)">
                  <el-option v-for="b in books" :key="b.id" :label="b.title" :value="b.id" />
                </el-select>
              </template>
            </el-table-column>
            <el-table-column label="Harga @ (Rp)" width="140" align="right">
              <template #default="scope">Rp {{ formatNumber(scope.row.price) }}</template>
            </el-table-column>
            <el-table-column label="Qty" width="110">
              <template #default="scope">
                <el-input-number v-model="scope.row.qty" :min="1" size="small" class="!w-full" @keyup.enter="submitManualOrder" />
              </template>
            </el-table-column>
            <el-table-column label="Total" width="150" align="right">
              <template #default="scope">
                <span class="font-bold text-[#00A9C3]">Rp {{ formatNumber(scope.row.price * scope.row.qty) }}</span>
              </template>
            </el-table-column>
          </el-table>
          <el-button class="mt-3" type="primary" plain size="small" @click="addEmptyItem">+ Tambah Baris</el-button>
        </div>

        <div class="mt-8 p-6 bg-slate-900 rounded-2xl text-right">
          <p class="text-[10px] text-slate-400 font-bold uppercase">Total Bayar Akhir</p>
          <p class="text-4xl font-black text-[#00A9C3]">Rp {{ formatNumber(calculatedTotal) }}</p>
        </div>
      </el-form>
      <template #footer>
        <el-button @click="isCreateOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" @click="submitManualOrder" :loading="loadingSubmit">Simpan Pesanan</el-button>
      </template>
    </el-dialog>

    <!-- DIALOG 2: DETAIL, VERIFIKASI & UNGGAH BUKTI -->
    <el-dialog v-model="isDetailOpen" title="Detail & Verifikasi Pesanan" width="750px" top="5vh" append-to-body>
      <div v-if="selectedOrder" class="space-y-6">
        <div class="bg-[#1B293C] p-5 rounded-2xl text-white shadow-lg flex justify-between items-center">
          <div>
            <p class="text-[10px] uppercase font-bold opacity-50">No. Invoice</p>
            <p class="text-lg font-black tracking-tight">{{ selectedOrder.order_number }}</p>
          </div>
          <div class="text-right">
            <p class="text-sm font-medium">{{ selectedOrder.customer?.name }}</p>
            <p class="text-2xl font-black text-[#00A9C3]">Rp {{ formatNumber(selectedOrder.total_amount) }}</p>
          </div>
        </div>

        <!-- Tabel Detail Barang -->
        <div class="border rounded-xl p-4 bg-slate-50/50">
          <p class="text-[11px] font-bold text-slate-400 uppercase mb-3">Daftar Buku Dipesan</p>
          <el-table :data="selectedOrder.items" border size="small" class="rounded-lg">
            <el-table-column label="Judul Buku">
              <template #default="{ row }">{{ row.book?.title || 'Data buku tidak ditemukan' }}</template>
            </el-table-column>
            <el-table-column prop="qty" label="Qty" width="70" align="center" />
            <el-table-column label="Subtotal" width="130" align="right">
              <template #default="{ row }">Rp {{ formatNumber(row.price_at_purchase * row.qty) }}</template>
            </el-table-column>
          </el-table>
        </div>

        <!-- Bukti Transfer + FITUR UNGGAH MANUAl -->
        <div v-if="selectedOrder.payment_method !== 'kredit'" class="border rounded-xl p-4">
           <p class="text-[11px] font-bold text-slate-400 uppercase mb-2">Bukti Pembayaran</p>
           <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
              <div class="flex justify-center bg-white p-2 rounded-lg border h-40">
                 <el-image v-if="selectedOrder.payment_proof" :src="selectedOrder.payment_proof" :preview-src-list="[selectedOrder.payment_proof]" class="h-full rounded" fit="contain" />
                 <div v-else class="flex items-center text-slate-300 italic text-xs">Belum ada bukti diunggah</div>
              </div>
              <el-upload action="#" :auto-upload="false" :show-file-list="false" :on-change="handleUploadProof">
                <div class="p-5 border-2 border-dashed border-slate-200 rounded-lg text-center cursor-pointer hover:border-[#00A9C3]">
                  <Icon icon="solar:upload-minimalistic-bold" class="text-2xl text-[#00A9C3] mx-auto" />
                  <p class="text-[10px] font-bold mt-2 uppercase text-slate-500">Klik Unggah Bukti Baru</p>
                </div>
              </el-upload>
           </div>
        </div>

        <!-- Form Update logistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <el-form-item label="Status Pembayaran">
              <el-select v-model="selectedOrder.payment_status" class="w-full">
                <el-option label="Belum Lunas" value="unpaid" />
                <el-option label="Lunas" value="paid" />
              </el-select>
            </el-form-item>
            <el-form-item label="Status Logistik">
              <el-select v-model="selectedOrder.shipping_status" class="w-full">
                <el-option label="Pending" value="pending" />
                <el-option label="Proses Packing" value="processing" />
                <el-option label="Telah Dikirim" value="shipping" />
                <el-option label="Barang Diterima" value="delivered" />
              </el-select>
            </el-form-item>
          </div>
          <div class="space-y-4">
            <el-form-item label="Nama Kurir">
              <el-input v-model="selectedOrder.courier_name" placeholder="Misal: J&T, Sicepat" />
            </el-form-item>
            <el-form-item label="Nomor Resi">
              <el-input v-model="selectedOrder.tracking_number" placeholder="Input resi" @keyup.enter="confirmUpdateStatus" />
            </el-form-item>
          </div>
        </div>

        <el-button type="primary" color="#00A9C3" class="w-full !h-12 font-bold shadow-lg" :loading="loadingUpdate" @click="confirmUpdateStatus">
          Simpan & Perbarui Transaksi
        </el-button>
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
const loadingSubmit = ref(false);
const loadingUpdate = ref(false);
const searchQuery = ref('');
const isCreateOpen = ref(false);
const isDetailOpen = ref(false);
const selectedOrder = ref(null);
const paymentProofId = ref(null); // Menampung ID Lampiran Baru

const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val || 0);
const formatDateTime = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
};

const createForm = reactive({
  date: '', customer_id: '', payment_method: 'cash',
  items: [{ book_id: '', qty: 1, price: 0 }]
});

const selectedCustomerType = computed(() => {
  const c = customers.value.find(c => c.id === createForm.customer_id);
  return c ? c.type : 'jemaat';
});

const calculatedTotal = computed(() => createForm.items.reduce((sum, i) => sum + (i.price * i.qty), 0));

const handleCustomerChange = () => {
  if (selectedCustomerType.value === 'jemaat') createForm.payment_method = 'cash';
  refreshAllPrices();
};

const loadData = async () => {
  loading.value = true;
  try {
    const [o, c, b] = await Promise.all([apiFetch('/orders'), apiFetch('/customers'), apiFetch('/books')]);
    orders.value = o.data || o;
    customers.value = c.data || c;
    books.value = b.data || b;
  } finally { loading.value = false; }
};

const openCreateDialog = () => {
  const now = new Date();
  const offset = now.getTimezoneOffset() * 60000;
  const localTime = (new Date(now - offset)).toISOString().slice(0, 19).replace('T', ' ');
  Object.assign(createForm, { date: localTime, customer_id: '', payment_method: 'cash', items: [{ book_id: '', qty: 1, price: 0 }] });
  isCreateOpen.value = true;
};

const addEmptyItem = () => createForm.items.push({ book_id: '', qty: 1, price: 0 });

const updateItemPrice = (idx: number) => {
  const book = books.value.find(b => b.id === createForm.items[idx].book_id);
  const cust = customers.value.find(c => c.id === createForm.customer_id);
  if (book && cust) {
    createForm.items[idx].price = (cust.type === 'penginjil') ? (book.member_price || book.price) : book.price;
  }
};

const refreshAllPrices = () => createForm.items.forEach((_, idx) => updateItemPrice(idx));

const submitManualOrder = async () => {
  if (!createForm.customer_id || createForm.items.some(i => !i.book_id)) return ElMessage.warning('Lengkapi data!');
  loadingSubmit.value = true;
  try {
    await apiFetch('/orders', { method: 'POST', body: { ...createForm, total_amount: calculatedTotal.value } });
    ElMessage.success('Berhasil disimpan');
    isCreateOpen.value = false;
    loadData();
  } catch (e) { ElMessage.error('Gagal. Cek stok gudang!'); }
  finally { loadingSubmit.value = false; }
};

const openViewDetail = (row: any) => {
  selectedOrder.value = JSON.parse(JSON.stringify(row));
  paymentProofId.value = null; // Reset ID unggahan saat buka detail
  isDetailOpen.value = true;
};

const handleUploadProof = async (file: any) => {
  if (!file.raw) return;
  try {
    ElMessage.info('Sedang mengunggah bukti...');
    const res = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Order', type: 'payment_proof' });
    paymentProofId.value = res.id;
    selectedOrder.value.payment_proof = res.url; // Update preview lokal
    ElMessage.success('Bukti berhasil diunggah!');
  } catch (e) { ElMessage.error('Gagal unggah bukti'); }
};

const confirmUpdateStatus = async () => {
  if (!selectedOrder.value) return;

  const isJemaat = selectedOrder.value.customer?.type === 'jemaat';
  const isNotPaid = selectedOrder.value.payment_status === 'unpaid';
  const isTryingToShip = ['processing', 'shipping', 'delivered'].includes(selectedOrder.value.shipping_status);

  if (isJemaat && isNotPaid && isTryingToShip) {
    return ElMessageBox.alert('Pelanggan Jemaat wajib melunasi pembayaran sebelum dikirim.', 'Blokir Logistik', { type: 'error' });
  }

  loadingUpdate.value = true;
  try {
    await apiFetch(`/orders/${selectedOrder.value.id}`, {
      method: 'PUT',
      body: { 
        ...selectedOrder.value,
        payment_proof_id: paymentProofId.value 
      }
    });
    ElMessage.success('Status & Data Berhasil Diperbarui');
    isDetailOpen.value = false;
    loadData();
  } catch (e) { ElMessage.error('Gagal update status'); }
  finally { loadingUpdate.value = false; }
};

const handleDelete = async (row: any) => {
  try {
    await ElMessageBox.confirm(`Hapus transaksi ${row.order_number}?`, 'Konfirmasi', { type: 'warning' });
    await apiFetch(`/orders/${row.id}`, { method: 'DELETE' });
    ElMessage.success('Terhapus');
    loadData();
  } catch (e) {}
};

watch(searchQuery, (v) => { if(v==='') loadData(); });
onMounted(loadData);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; }
</style>