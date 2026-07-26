<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- 1. HEADER & RINGKASAN -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Buku Piutang</h2>
          <p class="text-sm text-slate-500">Kelola sisa tagihan kredit dan monitoring jatuh tempo.</p>
        </div>
        <div class="bg-red-50 border border-red-100 px-6 py-3 rounded-2xl text-right">
           <p class="text-[10px] font-bold text-red-400 uppercase tracking-widest leading-none mb-1">Total Piutang Berjalan</p>
           <p class="text-2xl font-black text-red-600">Rp {{ formatNumber(totalReceivables) }}</p>
        </div>
      </div>

      <!-- 2. FILTER -->
      <div class="flex flex-wrap items-center gap-3 mb-6 p-3 bg-slate-50/50 rounded-2xl border">
        <el-select v-model="filterType" placeholder="Tipe Pelanggan" clearable class="w-44" @change="loadData">
          <el-option label="Penginjil (PL)" value="penginjil" /><el-option label="Gereja" value="gereja" /><el-option label="Sekolah" value="sekolah" />
        </el-select>
        <el-input v-model="searchQuery" placeholder="Cari No. Invoice/Nama..." clearable class="flex-1" @keyup.enter="loadData" @clear="loadData"><template #prefix><Icon icon="solar:magnifer-linear" /></template></el-input>
      </div>

      <!-- 3. TABEL DATA -->
      <div class="overflow-x-auto">
        <el-table :data="receivables" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden text-xs">
          <el-table-column label="Invoice" width="160">
            <template #default="{ row }">
              <div class="font-bold text-[#1B293C]">{{ row.order_number }}</div>
              <div class="text-[9px] text-slate-400 font-bold uppercase">{{ row.customer?.name || 'User Terhapus' }}</div>
            </template>
          </el-table-column>

          <el-table-column label="Jatuh Tempo" width="140" align="center">
            <template #default="{ row }">
              <div :class="isOverdue(row.due_date) ? 'text-red-500 font-black' : 'text-slate-600'">{{ formatDate(row.due_date) }}</div>
              <div v-if="isOverdue(row.due_date)" class="text-[8px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full mt-1 font-bold">TERLAMBAT</div>
            </template>
          </el-table-column>

          <el-table-column label="Sisa Tagihan" width="150" align="right">
            <template #default="{ row }"><span class="font-black text-red-500">Rp {{ formatNumber(row.remaining_amount) }}</span></template>
          </el-table-column>

          <el-table-column label="Status" width="100" align="center">
            <template #default="{ row }"><el-tag :type="getStatusType(row)" effect="dark" size="small">{{ getStatusLabel(row) }}</el-tag></template>
          </el-table-column>

          <el-table-column label="Aksi" width="120" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-2">
                <el-tooltip content="Input Cicilan" placement="top"><el-button circle size="small" type="warning" @click="openPaymentModal(row)"><Icon icon="solar:wallet-money-bold" /></el-button></el-tooltip>
                <el-tooltip content="Tagih via WA" placement="top"><el-button circle size="small" @click="sendWhatsAppReminder(row)" class="!bg-[#25D366] !border-[#25D366]"><Icon icon="ic:baseline-whatsapp" class="text-white text-lg" /></el-button></el-tooltip>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- MODAL: INPUT CICILAN + PREVIEW FOTO -->
    <el-dialog v-model="isPaymentOpen" title="Pencatatan Cicilan" width="400px" align-center append-to-body>
      <div v-if="selectedOrder" class="space-y-4">
        <div class="p-4 bg-slate-50 rounded-2xl text-center border">
          <p class="text-[10px] font-bold text-slate-400 uppercase">Sisa Tagihan</p>
          <p class="text-3xl font-black text-red-500">Rp {{ formatNumber(selectedOrder.remaining_amount) }}</p>
        </div>
        <el-form label-position="top">
          <el-form-item label="Jumlah Bayar (Rp)" required><el-input-number v-model="paymentForm.amount" :min="1" :max="selectedOrder.remaining_amount" class="!w-full" /></el-form-item>
          <el-form-item label="Metode Bayar"><el-select v-model="paymentForm.method" class="w-full"><el-option label="Transfer Bank" value="transfer" /><el-option label="Tunai" value="cash" /></el-select></el-form-item>
          
          <!-- PREVIEW FOTO STRUK -->
          <el-form-item label="Upload Bukti Struk (Opsional)">
            <el-upload action="#" :auto-upload="false" :show-file-list="false" :on-change="handleProofUpload">
              <div class="h-32 border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-[#00A9C3] bg-white overflow-hidden">
                <img v-if="proofPreviewUrl" :src="proofPreviewUrl" class="h-full w-full object-contain" />
                <template v-else>
                  <Icon icon="solar:camera-add-bold" class="text-3xl text-slate-300" />
                  <p class="text-[10px] mt-1 text-slate-400 font-bold uppercase">Pilih Foto</p>
                </template>
              </div>
            </el-upload>
          </el-form-item>
        </el-form>
      </div>
      <template #footer><el-button @click="isPaymentOpen = false">Batal</el-button><el-button type="primary" color="#00A9C3" @click="submitPayment" :loading="loadingPay" class="!rounded-xl px-8">SIMPAN</el-button></template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { ElMessage, ElMessageBox } from 'element-plus';
import { onMounted, reactive, ref, computed } from 'vue';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const { apiFetch, uploadAttachment, unwrap } = useApi();
const receivables = ref([]); const loading = ref(false); const loadingPay = ref(false); const searchQuery = ref(''); const filterType = ref('');
const isPaymentOpen = ref(false); const selectedOrder = ref(null); const paymentForm = reactive({ amount: 0, method: 'transfer', attachment_id: null });
const proofPreviewUrl = ref(null);

const totalReceivables = computed(() => receivables.value.reduce((sum, item) => sum + (item.remaining_amount || 0), 0));
const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val || 0);
const formatDate = (d: string) => { if (!d || d.startsWith('1970')) return 'Belum Set'; return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }); };
const formatDateTime = (d: string) => { if (!d) return '-'; return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour:'2-digit', minute:'2-digit' }); };
const isOverdue = (date: string) => { if (!date) return false; return new Date(date) < new Date() && new Date(date).toDateString() !== new Date().toDateString(); };
const getStatusLabel = (row: any) => (row.remaining_amount < row.total_amount && row.remaining_amount > 0) ? 'DICICIL' : 'BELUM BAYAR';
const getStatusType = (row: any) => (row.remaining_amount < row.total_amount && row.remaining_amount > 0) ? 'warning' : 'danger';

const loadData = async () => {
  loading.value = true; try { const res = await apiFetch(`/orders?payment_status=unpaid&payment_method=kredit&search=${searchQuery.value}&type=${filterType.value}`); receivables.value = unwrap(res); } finally { loading.value = false; }
};

const openPaymentModal = (row: any) => { selectedOrder.value = row; paymentForm.amount = row.remaining_amount; paymentForm.attachment_id = null; proofPreviewUrl.value = null; isPaymentOpen.value = true; };

const handleProofUpload = async (file: any) => {
  if (!file.raw) return;
  proofPreviewUrl.value = URL.createObjectURL(file.raw);
  try {
    const res = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Order', type: 'payment_proof' });
    paymentForm.attachment_id = res.id; ElMessage.success('Foto siap!');
  } catch (e) { ElMessage.error('Gagal upload'); proofPreviewUrl.value = null; }
};

// Cari fungsi submitPayment di receivables/index.vue dan pastikan body-nya lengkap
const submitPayment = async () => {
  if (paymentForm.amount <= 0) return ElMessage.warning('Jumlah tidak valid');
  
  loadingPay.value = true;
  try {
    await apiFetch(`/orders/${selectedOrder.value.id}/pay`, {
      method: 'POST',
      body: {
        amount: paymentForm.amount,
        method: paymentForm.method,
        attachment_id: paymentForm.attachment_id // PASTIKAN INI TERKIRIM
      }
    });
    ElMessage.success('Cicilan Berhasil Dicatat!');
    isPaymentOpen.value = false;
    loadData();
  } catch (e) {
    ElMessage.error('Gagal mencatat pembayaran');
  } finally {
    loadingPay.value = false;
  }
};

const sendWhatsAppReminder = (row: any) => {
  const phone = row.customer?.phone;
  if (!phone) return ElMessage.warning('No WA Kosong!');
  const message = `Syalom ${row.customer?.name}, sisa tagihan INV ${row.order_number} Anda adalah Rp ${formatNumber(row.remaining_amount)}. Jatuh tempo: ${formatDate(row.due_date)}. 🙏`;
  window.open(`https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(message)}`, '_blank');
};

onMounted(loadData);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; }
</style>