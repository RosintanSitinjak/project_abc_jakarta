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
          <el-input v-model="searchQuery" placeholder="Cari No. Invoice..." clearable class="w-full sm:w-64">
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          <el-button type="primary" color="#00a9c3" @click="openCreateDialog">
            + Pesanan Manual
          </el-button>
        </div>
      </div>

      <!-- TABEL DAFTAR PESANAN UTAMA -->
      <div class="mt-6 overflow-x-auto">
        <el-table :data="orders" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
          
          <el-table-column label="Waktu Transaksi" width="160" align="center">
            <template #default="{ row }">
              <div class="text-[11px] font-bold text-slate-500 uppercase leading-tight">
                {{ formatDateTime(row.created_at) }}
              </div>
            </template>
          </el-table-column>

          <el-table-column prop="order_number" label="No. Invoice" width="160" />
          <el-table-column prop="customer.name" label="Pelanggan" min-width="180" />
          
          <el-table-column label="Asal" width="120" align="center">
            <template #default="{ row }">
              <el-tag size="small" :type="row.source === 'admin_manual' ? 'warning' : 'info'" effect="light" class="font-bold">
                {{ row.source === 'admin_manual' ? 'WA / MANUAL' : 'WEBSITE' }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column label="Pembayaran" width="130" align="center">
            <template #default="{ row }">
              <el-tag :type="row.payment_status === 'paid' ? 'success' : 'danger'" effect="dark" size="small">
                {{ row.payment_status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column label="Total (Rp)" width="140">
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

          <el-table-column label="Aksi" width="120" align="center">
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

    <!-- 1. DIALOG: INPUT PESANAN MANUAL -->
    <el-dialog v-model="isCreateOpen" title="Formulir Input Pesanan (Assisted)" width="900px" top="5vh" destroy-on-close>
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
            <p v-if="selectedCustomerType === 'penginjil'" class="text-[9px] text-orange-500 mt-1">* Limit PL: Rp 5.000.000</p>
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
            <el-table-column label="Harga @ (Rp)" width="150" align="right">
              <template #default="scope">
                <span class="text-slate-600 font-bold">Rp {{ formatNumber(scope.row.price) }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Qty" width="110">
              <template #default="scope">
                <el-input-number v-model="scope.row.qty" :min="1" size="small" class="!w-full" />
              </template>
            </el-table-column>
            <el-table-column label="Total" width="160" align="right">
              <template #default="scope">
                <span class="font-black text-[#00A9C3]">Rp {{ formatNumber(scope.row.price * scope.row.qty) }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Batal" width="70" align="center">
              <template #default="scope">
                <el-button type="danger" plain circle @click="createForm.items.splice(scope.$index, 1)">
                   <Icon icon="solar:close-circle-bold" />
                </el-button>
              </template>
            </el-table-column>
          </el-table>
          <el-button class="mt-3" type="primary" plain size="small" @click="addEmptyItem">+ Tambah Baris Buku</el-button>
        </div>

        <div class="mt-8 p-6 bg-slate-900 rounded-2xl text-right">
          <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Total Bayar Akhir</p>
          <p class="text-4xl font-black text-[#00A9C3]">Rp {{ formatNumber(calculatedTotal) }}</p>
        </div>
      </el-form>
      <template #footer>
        <el-button @click="isCreateOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" @click="submitManualOrder" :loading="loadingSubmit">Simpan Pesanan</el-button>
      </template>
    </el-dialog>

    <!-- 2. DIALOG: DETAIL & VERIFIKASI (DENGAN LOGIKA PENGAMAN) -->
    <el-dialog v-model="isDetailOpen" title="Verifikasi & Update Status" width="500px">
      <div v-if="selectedOrder" class="space-y-5">
        <div class="bg-[#1B293C] p-5 rounded-2xl text-white shadow-xl">
          <p class="text-[10px] uppercase font-bold opacity-50">No. Invoice</p>
          <p class="text-lg font-black tracking-tight mb-3">{{ selectedOrder.order_number }}</p>
          <div class="pt-3 border-t border-white/10 flex justify-between items-center">
            <span class="text-sm font-medium">{{ selectedOrder.customer?.name }}</span>
            <span class="text-xl font-black">Rp {{ formatNumber(selectedOrder.total_amount) }}</span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <el-form-item label="Status Pembayaran">
            <el-select v-model="selectedOrder.payment_status" class="w-full">
              <el-option label="Belum Lunas" value="unpaid" />
              <el-option label="Lunas" value="paid" />
            </el-select>
          </el-form-item>
          <el-form-item label="Status Logistik">
            <el-select v-model="selectedOrder.shipping_status" class="w-full">
              <el-option label="Pending" value="pending" />
              <el-option label="Diproses" value="processing" />
              <el-option label="Dikirim" value="shipping" />
              <el-option label="Selesai" value="delivered" />
            </el-select>
          </el-form-item>
        </div>

        <el-button type="primary" color="#00A9C3" class="w-full !h-12 font-bold" :loading="loadingUpdate" @click="confirmUpdateStatus">
          Simpan Perubahan
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

const { apiFetch, unwrap } = useApi();
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

const createForm = reactive({
  date: '', customer_id: '', payment_method: 'cash',
  items: [{ book_id: '', qty: 1, price: 0 }]
});

const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val);
const formatDateTime = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
};

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
  const localISOTime = (new Date(now - offset)).toISOString().slice(0, 19).replace('T', ' ');
  Object.assign(createForm, { date: localISOTime, customer_id: '', payment_method: 'cash', items: [{ book_id: '', qty: 1, price: 0 }] });
  isCreateOpen.value = true;
};

const addEmptyItem = () => createForm.items.push({ book_id: '', qty: 1, price: 0 });

const updateItemPrice = (idx: number) => {
  const book = books.value.find(b => b.id === createForm.items[idx].book_id);
  const cust = customers.value.find(c => c.id === createForm.customer_id);
  
  if (book && cust) {
    // REVISI: Hanya Penginjil yang dapat harga member.
    if (cust.type === 'penginjil') {
      createForm.items[idx].price = book.member_price || book.price;
    } else {
      createForm.items[idx].price = book.price;
    }
  }
};

const refreshAllPrices = () => createForm.items.forEach((_, idx) => updateItemPrice(idx));

const submitManualOrder = async () => {
  if (!createForm.customer_id || createForm.items.some(i => !i.book_id)) return ElMessage.warning('Lengkapi data!');
  loadingSubmit.value = true;
  try {
    await apiFetch('/orders', { method: 'POST', body: { ...createForm, total_amount: calculatedTotal.value, source: 'admin_manual' } });
    ElMessage.success('Pesanan Berhasil Disimpan');
    isCreateOpen.value = false;
    loadData();
  } catch (e) { ElMessage.error('Gagal simpan pesanan. Cek stok.'); }
  finally { loadingSubmit.value = false; }
};

const openViewDetail = (row: any) => {
  selectedOrder.value = JSON.parse(JSON.stringify(row));
  isDetailOpen.value = true;
};

const confirmUpdateStatus = async () => {
  if (!selectedOrder.value) return;

  const isDirectPay = ['cash', 'transfer'].includes(selectedOrder.value.payment_method);
  const isNotPaid = selectedOrder.value.payment_status === 'unpaid';
  const isTryingToShip = ['processing', 'shipping', 'delivered'].includes(selectedOrder.value.shipping_status);

  if (isDirectPay && isNotPaid && isTryingToShip) {
    return ElMessage({ message: 'Peringatan: Pesanan Tunai/Transfer harus LUNAS sebelum diproses!', type: 'error', duration: 5000 });
  }

  loadingUpdate.value = true;
  try {
    await apiFetch(`/orders/${selectedOrder.value.id}`, {
      method: 'PUT',
      body: { payment_status: selectedOrder.value.payment_status, shipping_status: selectedOrder.value.shipping_status }
    });
    ElMessage.success('Status Diperbarui');
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

watch(searchQuery, () => loadData());
onMounted(loadData);
</script>