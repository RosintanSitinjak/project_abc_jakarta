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
          <el-button type="warning" plain @click="handleExportMonthly" class="!rounded-xl">
            <Icon icon="solar:document-bold" class="mr-1" /> Cetak Laporan Terfilter
          </el-button>
          <el-button type="primary" color="#00a9c3" @click="openCreateDialog" class="!rounded-xl shadow-lg">
            + Pesanan Manual
          </el-button>
        </div>
      </div>

      <!-- 2. FILTER BAR (COMPACT) -->
      <div class="flex flex-wrap items-center gap-2 mb-6 p-3 bg-slate-50/50 rounded-2xl border border-slate-100">
        <el-date-picker v-model="dateRange" type="daterange" start-placeholder="Mulai" end-placeholder="Selesai" value-format="YYYY-MM-DD" class="!w-60" @change="loadData" />
        
        <el-select v-model="filterStatus" placeholder="Status Bayar" clearable class="!w-32" @change="loadData">
          <el-option label="Lunas" value="paid" /><el-option label="Belum Lunas" value="unpaid" />
        </el-select>

        <el-input v-model="searchQuery" placeholder="Cari Invoice/Nama..." clearable class="flex-1 min-w-[200px]" @keyup.enter="loadData" @clear="loadData">
          <template #prefix><Icon icon="solar:magnifer-linear" /></template>
        </el-input>

        <el-popover placement="bottom" :width="200" trigger="click">
          <template #reference><el-button plain icon="solar:filter-linear">Lainnya</el-button></template>
          <div class="space-y-3">
            <el-select v-model="filterType" placeholder="Tipe Akun" clearable class="w-full" @change="loadData">
              <el-option label="Jemaat" value="jemaat" /><el-option label="Penginjil" value="penginjil" /><el-option label="Gereja" value="gereja" />
            </el-select>
          </div>
        </el-popover>
      </div>

      <!-- 3. TABEL DATA -->
      <el-table :data="orders" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden text-xs">
        <el-table-column label="Waktu" width="140" align="center"><template #default="{ row }"><div class="font-bold text-slate-500 uppercase">{{ formatDateTime(row.created_at) }}</div></template></el-table-column>
        <el-table-column prop="order_number" label="No. Invoice" width="160" />
        <el-table-column label="Pelanggan" min-width="180">
           <template #default="{ row }">
              <div class="font-bold text-slate-700">{{ row.customer?.name || 'User Terhapus' }}</div>
              <el-tag v-if="row.customer" size="small" type="info" class="!text-[8px] h-4 px-1 uppercase font-black">{{ row.customer.type }}</el-tag>
           </template>
        </el-table-column>
        <el-table-column label="Pembayaran" width="120" align="center">
          <template #default="{ row }">
            <el-tag :type="row.payment_status === 'paid' ? 'success' : 'danger'" effect="dark" size="small">{{ row.payment_status.toUpperCase() }}</el-tag>
            <div class="text-[9px] mt-0.5 text-slate-400 font-bold italic">{{ row.payment_method.toUpperCase() }}</div>
          </template>
        </el-table-column>
        <el-table-column label="Total" width="140" align="right">
          <template #default="{ row }"><span class="font-bold text-[#1B293C]">Rp {{ formatNumber(row.total_amount) }}</span></template>
        </el-table-column>
        <el-table-column label="Aksi" width="80" align="center">
          <template #default="{ row }"><el-button circle size="small" type="primary" plain @click="openViewDetail(row)"><Icon icon="solar:eye-bold" /></el-button></template>
        </el-table-column>
      </el-table>
    </section>

    <!-- DIALOG 1: INPUT PESANAN BARU -->
    <el-dialog v-model="isCreateOpen" title="Formulir Input Pesanan Baru" width="950px" top="5vh" destroy-on-close append-to-body>
      <el-form label-position="top">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
          <el-form-item label="Waktu" required><el-date-picker v-model="createForm.date" type="datetime" value-format="YYYY-MM-DD HH:mm:ss" class="!w-full" /></el-form-item>
          <el-form-item label="Pelanggan" required><el-select v-model="createForm.customer_id" filterable class="w-full" @change="handleCustomerChange"><el-option v-for="c in customers" :key="c.id" :label="`${c.name} (${c.type.toUpperCase()})`" :value="c.id" /></el-select></el-form-item>
          <el-form-item label="Metode"><el-select v-model="createForm.payment_method" class="w-full"><el-option label="Cash" value="cash" /><el-option label="Transfer" value="transfer" /><el-option v-if="selectedCustomerType !== 'jemaat'" label="Kredit" value="kredit" /></el-select></el-form-item>
          <el-form-item v-if="selectedCustomerType !== 'jemaat'" label="Bayar DP"><el-input-number v-model="createForm.paid_amount" :min="0" :max="calculatedTotal" class="!w-full" /></el-form-item>
          <el-form-item v-else label="Info"><el-tag type="success" class="w-full h-8 flex justify-center items-center">WAJIB LUNAS</el-tag></el-form-item>
        </div>

        <div class="mb-6">
          <p class="text-xs font-bold uppercase text-slate-400 mb-3">Item Buku</p>
          <el-table :data="createForm.items" border class="w-full rounded-lg">
            <el-table-column label="Judul Buku"><template #default="scope"><el-select v-model="scope.row.book_id" filterable class="w-full" @change="updateItemPrice(scope.$index)"><el-option v-for="b in books" :key="b.id" :label="b.title" :value="b.id" /></el-select></template></el-table-column>
            <el-table-column label="Qty" width="130" align="center"><template #default="scope"><el-input-number v-model="scope.row.qty" :min="1" size="small" controls-position="right" class="!w-full" /></template></el-table-column>
            <el-table-column label="Subtotal" width="140" align="right"><template #default="scope">Rp{{ formatNumber(scope.row.price * scope.row.qty) }}</template></el-table-column>
            <el-table-column width="50" align="center"><template #default="scope"><el-button type="danger" link @click="createForm.items.splice(scope.$index, 1)"><Icon icon="solar:trash-bin-trash-bold"/></el-button></template></el-table-column>
          </el-table>
          <el-button class="mt-3" type="primary" plain size="small" @click="addEmptyItem">+ Tambah Buku</el-button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
           <div class="border rounded-xl p-4 bg-slate-50">
              <p class="text-[11px] font-bold text-slate-400 mb-3 text-center uppercase">Lampirkan Bukti</p>
              <el-upload action="#" :auto-upload="false" :show-file-list="false" :on-change="handleCreateUploadProof"><div class="h-32 border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-[#00A9C3] bg-white"><template v-if="!createForm.payment_proof_id"><Icon icon="solar:camera-add-bold" class="text-3xl text-slate-300" /><p class="text-[10px] mt-2">Pilih Foto Bukti</p></template><template v-else><Icon icon="solar:check-circle-bold" class="text-4xl text-green-500" /><p class="text-[10px] mt-2 font-bold text-green-600">TERPILIH</p></template></div></el-upload>
           </div>
           <div class="mt-6 p-6 bg-slate-900 rounded-2xl flex justify-between items-center text-white shadow-xl h-fit">
              <div><p class="text-[10px] opacity-50 uppercase tracking-widest">Sisa Piutang</p><p class="text-2xl font-black text-red-400">Rp {{ formatNumber(selectedCustomerType === 'jemaat' ? 0 : (calculatedTotal - createForm.paid_amount)) }}</p></div>
              <div class="text-right"><p class="text-[10px] opacity-50 uppercase tracking-widest">Total Nota</p><p class="text-4xl font-black text-[#00A9C3]">Rp {{ formatNumber(calculatedTotal) }}</p></div>
           </div>
        </div>
      </el-form>
      <template #footer><el-button type="primary" color="#00A9C3" @click="submitManualOrder" :loading="loadingSubmit" class="!rounded-xl px-10">SIMPAN PESANAN</el-button></template>
    </el-dialog>

    <!-- DIALOG 2: DETAIL SUPER KOMPLIT -->
    <el-dialog v-model="isDetailOpen" title="Detail Pesanan & Invoice" width="850px" top="5vh" append-to-body>
      <div v-if="selectedOrder" class="space-y-6">
        <div class="flex justify-between items-start">
          <div class="bg-[#1B293C] p-4 rounded-2xl text-white shadow-lg flex-1 mr-4">
            <p class="text-[10px] uppercase font-bold opacity-60 leading-none">No. Invoice</p>
            <p class="text-lg font-black">{{ selectedOrder.order_number }}</p>
            <p class="text-xs mt-1">{{ selectedOrder.customer?.name }} ({{ selectedOrder.customer?.type.toUpperCase() }})</p>
          </div>
          <el-button type="success" size="large" class="!h-16 !rounded-2xl" @click="downloadInvoice(selectedOrder.id)">CETAK INVOICE</el-button>
        </div>

        <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl text-xs"><p class="font-bold text-blue-600 mb-1 uppercase tracking-widest">Tujuan Kirim:</p>{{ selectedOrder.customer?.address || 'Alamat Kosong' }}</div>
        
        <el-table :data="selectedOrder.items" border size="small" class="w-full">
            <el-table-column label="Buku"><template #default="{ row }">{{ row.book?.title }}</template></el-table-column>
            <el-table-column prop="qty" label="Qty" width="80" align="center" />
            <el-table-column label="Subtotal" width="130" align="right"><template #default="{ row }">Rp{{ formatNumber(row.price_at_purchase * row.qty) }}</template></el-table-column>
        </el-table>

        <div class="grid grid-cols-2 gap-6">
           <div class="border rounded-xl p-4 bg-slate-50"><p class="text-[11px] font-bold text-slate-400 uppercase mb-2">Bukti Pembayaran</p><el-image v-if="selectedOrder.payment_proof" :src="selectedOrder.payment_proof" class="h-32 rounded border bg-white" fit="contain" :preview-src-list="[selectedOrder.payment_proof]" /><el-upload action="#" :auto-upload="false" :show-file-list="false" :on-change="handleUploadProof"><el-button size="small" type="primary" plain class="w-full mt-2">Ganti Bukti</el-button></el-upload></div>
           <div class="p-6 border rounded-2xl bg-orange-50/30 flex flex-col justify-center text-center"><p class="text-[11px] font-bold text-orange-500 uppercase tracking-widest">Sisa Tagihan</p><p class="text-3xl font-black text-orange-600">Rp {{ formatNumber(selectedOrder.remaining_amount) }}</p></div>
        </div>

        <div class="grid grid-cols-2 gap-4">
           <el-form-item label="Pembayaran"><el-select v-model="selectedOrder.payment_status" class="w-full"><el-option label="Belum Lunas" value="unpaid" /><el-option label="Lunas" value="paid" /></el-select></el-form-item>
           <el-form-item label="Logistik"><el-select v-model="selectedOrder.shipping_status" class="w-full"><el-option label="Pending" value="pending" /><el-option label="Packing" value="processing" /><el-option label="Dikirim" value="shipping" /><el-option label="Diterima" value="delivered" /></el-select></el-form-item>
           <el-form-item label="Kurir"><el-select v-model="selectedOrder.courier_name" placeholder="Pilih" class="w-full"><el-option label="Kurir Kantor" value="Kurir Kantor" /><el-option label="J&T" value="J&T" /><el-option label="Sicepat" value="Sicepat" /><el-option label="Self Pickup" value="Self Pickup" /></el-select></el-form-item>
           <el-form-item label="Resi"><el-input v-model="selectedOrder.tracking_number" @keyup.enter="confirmUpdateStatus" /></el-form-item>
        </div>
        <div class="flex gap-3">
          <el-button type="danger" plain class="flex-1 !h-14 font-bold" @click="handleCancelOrder(selectedOrder.id)">BATALKAN</el-button>
          <el-button type="primary" color="#00A9C3" class="flex-[2] !h-14 font-bold shadow-lg" @click="confirmUpdateStatus" :loading="loadingUpdate">SIMPAN</el-button>
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
const orders = ref([]); const customers = ref([]); const books = ref([]); const loading = ref(false); 
const searchQuery = ref(''); const dateRange = ref([]); const filterStatus = ref(''); const filterType = ref(''); const filterMethod = ref('');
const isCreateOpen = ref(false); const isDetailOpen = ref(false); const selectedOrder = ref(null); const paymentProofId = ref(null); const loadingSubmit = ref(false); const loadingUpdate = ref(false);

const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val || 0);
const formatDateTime = (dateStr: string) => new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });

const createForm = reactive({ date: '', customer_id: '', payment_method: 'cash', paid_amount: 0, payment_proof_id: null, items: [{ book_id: '', qty: 1, price: 0 }] });
const selectedCustomerType = computed(() => { const c = customers.value.find(c => c.id === createForm.customer_id); return c ? c.type : 'jemaat'; });
const calculatedTotal = computed(() => createForm.items.reduce((sum, i) => sum + (i.price * i.qty), 0));

const handleCustomerChange = () => { if (selectedCustomerType.value === 'jemaat') { createForm.payment_method = 'transfer'; createForm.paid_amount = calculatedTotal.value; } else { createForm.paid_amount = 0; } refreshAllPrices(); };
watch(calculatedTotal, (newTotal) => { if (selectedCustomerType.value === 'jemaat') createForm.paid_amount = newTotal; });

const loadData = async () => {
  loading.value = true;
  try {
    let url = `/orders?search=${searchQuery.value}&payment_status=${filterStatus.value}&type=${filterType.value}&payment_method=${filterMethod.value}`;
    if (dateRange.value && dateRange.value.length === 2) url += `&start_date=${dateRange.value[0]}&end_date=${dateRange.value[1]}`;
    const [o, c, b] = await Promise.all([apiFetch(url), apiFetch('/customers'), apiFetch('/books')]);
    orders.value = o.data || o; customers.value = c.data || c; books.value = b.data || b;
  } finally { loading.value = false; }
};

const downloadInvoice = (id: string) => window.open(`http://localhost:8000/api/orders/${id}/invoice`, '_blank');

const handleExportMonthly = () => {
  let url = `http://localhost:8000/api/orders/export/monthly?`;
  if (dateRange.value && dateRange.value.length === 2) url += `start_date=${dateRange.value[0]}&end_date=${dateRange.value[1]}`;
  window.open(url, '_blank');
};

const openCreateDialog = () => {
  const now = new Date(); const localTime = (new Date(now - (now.getTimezoneOffset() * 60000))).toISOString().slice(0, 19).replace('T', ' ');
  Object.assign(createForm, { date: localTime, customer_id: '', payment_method: 'cash', paid_amount: 0, payment_proof_id: null, items: [{ book_id: '', qty: 1, price: 0 }] });
  isCreateOpen.value = true;
};

const updateItemPrice = (idx: number) => {
  const book = books.value.find(b => b.id === createForm.items[idx].book_id);
  const cust = customers.value.find(c => c.id === createForm.customer_id);
  if (book && cust) createForm.items[idx].price = (cust.type === 'penginjil') ? (book.member_price || book.price) : book.price;
};

const addEmptyItem = () => createForm.items.push({ book_id: '', qty: 1, price: 0 });
const refreshAllPrices = () => createForm.items.forEach((_, idx) => updateItemPrice(idx));

const submitManualOrder = async () => {
  loadingSubmit.value = true;
  try {
    const finalPaid = selectedCustomerType.value === 'jemaat' ? calculatedTotal.value : createForm.paid_amount;
    await apiFetch('/orders', { method: 'POST', body: { ...createForm, total_amount: calculatedTotal.value, paid_amount: finalPaid } });
    ElMessage.success('Simpan Berhasil!'); isCreateOpen.value = false; loadData();
  } catch (e) { ElMessage.error('Gagal!'); }
  finally { loadingSubmit.value = false; }
};

const openViewDetail = (row: any) => { selectedOrder.value = JSON.parse(JSON.stringify(row)); isDetailOpen.value = true; };

const handleUploadProof = async (file: any) => {
  const res = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Order', type: 'payment_proof' });
  paymentProofId.value = res.id; selectedOrder.value.payment_proof = res.url; ElMessage.success('Terunggah!');
};

const handleCreateUploadProof = async (file: any) => {
  const res = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Order', type: 'payment_proof' });
  createForm.payment_proof_id = res.id; ElMessage.success('Bukti terlampir!');
};

const confirmUpdateStatus = async () => {
  loadingUpdate.value = true;
  try {
    await apiFetch(`/orders/${selectedOrder.value.id}`, { method: 'PUT', body: { ...selectedOrder.value, payment_proof_id: paymentProofId.value } });
    ElMessage.success('Data diperbarui!'); isDetailOpen.value = false; loadData();
  } catch (e) { ElMessage.error('Gagal!'); }
  finally { loadingUpdate.value = false; }
};

const handleCancelOrder = async (id: string) => {
  await ElMessageBox.confirm('Batalkan pesanan?', 'Peringatan', { type: 'warning' });
  await apiFetch(`/orders/${id}/cancel`, { method: 'POST' });
  loadData(); isDetailOpen.value = false;
};

const handleDelete = async (row: any) => {
  await ElMessageBox.confirm(`Hapus ${row.order_number}?`, 'Peringatan', { type: 'warning' });
  await apiFetch(`/orders/${row.id}`, { method: 'DELETE' }); loadData();
};

onMounted(loadData);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; }
</style>