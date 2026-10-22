<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- 1. HEADER & RINGKASAN TOTAL PIUTANG -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Buku Piutang</h2>
          <p class="text-sm text-slate-500">Kelola sisa tagihan kredit, siklus umur piutang (aging), dan jatuh tempo tempo literatur.</p>
        </div>
        <div class="bg-red-50 border border-red-100 px-6 py-3 rounded-2xl text-right shadow-xs">
          <p class="text-[10px] font-bold text-red-400 uppercase tracking-widest leading-none mb-1">Total Piutang Berjalan</p>
          <p class="text-2xl font-black text-red-600">Rp {{ formatNumber(totalReceivables) }}</p>
        </div>
      </div>

      <!-- 2. FILTER BAR -->
      <div class="flex flex-wrap items-center gap-3 mb-6 p-3 bg-slate-50/70 rounded-2xl border border-slate-200">
        <!-- Filter Tipe Pelanggan -->
        <el-select 
          v-model="filterType" 
          placeholder="Semua Tipe" 
          clearable 
          class="!w-44" 
          @change="loadData"
        >
          <el-option label="Penginjil (PL)" value="penginjil" />
          <el-option label="Gereja" value="gereja" />
          <el-option label="Sekolah" value="sekolah" />
        </el-select>

        <!-- Pencarian Real-Time (No. Invoice atau Nama) -->
        <el-input 
          v-model="searchQuery" 
          placeholder="Cari No. Invoice atau Nama Pelanggan..." 
          clearable 
          class="flex-1 min-w-[200px]" 
          @keyup.enter="loadData" 
          @clear="loadData"
        >
          <template #prefix><Icon icon="solar:magnifer-linear" /></template>
        </el-input>

        <!-- Tombol Reset Filter Cepat -->
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

      <!-- 3. TABEL DATA PIUTANG -->
      <div class="overflow-x-auto">
        <el-table :data="receivables" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden text-xs">
          <!-- INVOICE & PELANGGAN BESERTA ITEM BUKU -->
          <el-table-column label="Invoice & Pelanggan" min-width="190">
            <template #default="{ row }">
              <div class="font-bold text-[#1B293C] text-xs">{{ row.order_number }}</div>
              <div class="flex items-center gap-1.5 mt-0.5">
                <span class="text-[10px] text-slate-600 font-bold uppercase">{{ row.customer?.name || 'Pelanggan Terhapus' }}</span>
                <el-tag 
                  v-if="row.customer" 
                  size="small" 
                  effect="plain" 
                  :type="row.customer.type === 'penginjil' ? 'warning' : 'primary'"
                  class="!text-[8px] h-3.5 px-1 uppercase font-black"
                >
                  {{ row.customer.type === 'penginjil' ? 'PL' : row.customer.type }}
                </el-tag>
              </div>
              <!-- Preview Judul Buku Singkat -->
              <div v-if="row.items && row.items.length" class="text-[10px] text-slate-400 mt-1 truncate">
                📚 {{ row.items.map((i: any) => `${i.book?.title} (${i.qty}x)`).join(', ') }}
              </div>
            </template>
          </el-table-column>

          <!-- UMUR PIUTANG (AGING OF RECEIVABLES - 30/60/90 HARI) -->
          <el-table-column label="Umur Piutang" width="130" align="center">
            <template #default="{ row }">
              <el-tag 
                size="small" 
                :type="getAgingBadge(row.created_at).type"
                effect="plain" 
                class="font-bold !text-[9px]"
              >
                {{ getAgingBadge(row.created_at).label }}
              </el-tag>
              <div class="text-[9px] text-slate-400 mt-0.5">
                Terbit: {{ formatDate(row.created_at) }}
              </div>
            </template>
          </el-table-column>

          <!-- JATUH TEMPO DENGAN PERINGATAN TERLAMBAT -->
          <el-table-column label="Jatuh Tempo" width="130" align="center">
            <template #default="{ row }">
              <div :class="isOverdue(row.due_date) ? 'text-red-500 font-black' : 'text-slate-600 font-semibold'">
                {{ formatDate(row.due_date) }}
              </div>
              <div v-if="isOverdue(row.due_date)" class="text-[8px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full mt-1 font-bold inline-block animate-pulse">
                TERLAMBAT
              </div>
            </template>
          </el-table-column>

          <!-- TOTAL & SISA TAGIHAN -->
          <el-table-column label="Sisa Tagihan" width="150" align="right">
            <template #default="{ row }">
              <div class="font-black text-red-500 text-xs">Rp {{ formatNumber(row.remaining_amount) }}</div>
              <div class="text-[9px] text-slate-400 mt-0.5">
                Total Nota: Rp {{ formatNumber(row.total_amount) }}
              </div>
            </template>
          </el-table-column>

          <!-- STATUS NOTA -->
          <el-table-column label="Status" width="110" align="center">
            <template #default="{ row }">
              <el-tag :type="getStatusType(row)" effect="dark" size="small" class="font-bold">
                {{ getStatusLabel(row) }}
              </el-tag>
            </template>
          </el-table-column>

          <!-- AKSI (CICILAN & WHATSAPP) -->
          <el-table-column label="Aksi" width="120" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-1.5">
                <el-tooltip content="Catat Pembayaran Cicilan" placement="top">
                  <el-button circle size="small" type="warning" @click="openPaymentModal(row)">
                    <Icon icon="solar:wallet-money-bold" />
                  </el-button>
                </el-tooltip>
                <el-tooltip content="Kirim Tagihan via WhatsApp" placement="top">
                  <el-button circle size="small" @click="sendWhatsAppReminder(row)" class="!bg-[#25D366] !border-[#25D366]">
                    <Icon icon="ic:baseline-whatsapp" class="text-white text-lg" />
                  </el-button>
                </el-tooltip>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- MODAL: PENCATATAN CICILAN + UPLOAD BUKTI -->
    <el-dialog 
      v-model="isPaymentOpen" 
      :title="`Pencatatan Cicilan: ${selectedOrder?.order_number}`" 
      width="460px" 
      align-center 
      append-to-body
    >
      <div v-if="selectedOrder" class="space-y-4">
        <!-- Info Pelanggan & Sisa Tagihan -->
        <div class="p-4 bg-slate-50 rounded-2xl border text-center space-y-1">
          <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
            Pelanggan: <span class="text-slate-800">{{ selectedOrder.customer?.name }}</span>
          </p>
          <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sisa Tagihan Nota Berjalan</p>
          <p class="text-2xl font-black text-red-500">Rp {{ formatNumber(selectedOrder.remaining_amount) }}</p>
          
          <div class="text-[10px] text-slate-500 pt-1 border-t border-slate-200 mt-2 flex justify-between">
            <span>Total Nota: <b>Rp {{ formatNumber(selectedOrder.total_amount) }}</b></span>
            <span>Jatuh Tempo: <b>{{ formatDate(selectedOrder.due_date) }}</b></span>
          </div>
        </div>

        <!-- Tombol Pintas Nominal Bayar -->
        <div class="flex items-center justify-between gap-2">
          <span class="text-[11px] font-bold text-slate-500">Pilih Nominal:</span>
          <div class="flex gap-1.5">
            <el-button size="small" plain @click="paymentForm.amount = Math.round(selectedOrder.remaining_amount / 2)" class="!text-xs">
              50%
            </el-button>
            <el-button size="small" type="primary" plain @click="paymentForm.amount = selectedOrder.remaining_amount" class="!text-xs">
              Bayar Lunas (100%)
            </el-button>
          </div>
        </div>

        <el-form label-position="top">
          <el-form-item label="Jumlah Bayar (Rp)" required>
            <el-input-number 
              v-model="paymentForm.amount" 
              :min="1" 
              :max="selectedOrder.remaining_amount" 
              :step="50000"
              class="!w-full" 
            />
          </el-form-item>
          
          <el-form-item label="Metode Pembayaran">
            <el-select v-model="paymentForm.method" class="w-full">
              <el-option label="Transfer Bank Mandiri ABC" value="transfer" />
              <el-option label="Tunai / Kasir Kantor" value="cash" />
              <el-option label="QRIS ABC Jakarta" value="qris" />
            </el-select>
          </el-form-item>
          
          <!-- UPLOAD PREVIEW BUKTI STRUK -->
          <el-form-item label="Upload Bukti Struk (Opsional)">
            <el-upload 
              action="#" 
              :auto-upload="false" 
              :show-file-list="false" 
              :on-change="handleProofUpload"
            >
              <div class="h-28 border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-[#00A9C3] bg-white overflow-hidden transition-colors w-full p-2">
                <img v-if="proofPreviewUrl" :src="proofPreviewUrl" class="h-full w-full object-contain" />
                <template v-else>
                  <Icon icon="solar:camera-add-bold" class="text-2xl text-slate-300" />
                  <p class="text-[10px] mt-1 text-slate-400 font-bold uppercase">Pilih Foto Struk Transfer</p>
                  <span class="text-[9px] text-slate-300">Kosongkan jika membayar tunai di kantor</span>
                </template>
              </div>
            </el-upload>
          </el-form-item>
        </el-form>
      </div>

      <template #footer>
        <el-button @click="isPaymentOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" @click="submitPayment" :loading="loadingPay" class="!rounded-xl px-8 font-bold">
          SIMPAN PEMBAYARAN
        </el-button>
      </template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { ElMessage } from 'element-plus';
import { onMounted, reactive, ref, computed, watch } from 'vue';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const { apiFetch, uploadAttachment, unwrap } = useApi();

const receivables = ref<any[]>([]); 
const loading = ref(false); 
const loadingPay = ref(false); 
const searchQuery = ref(''); 
const filterType = ref('');

const isPaymentOpen = ref(false); 
const selectedOrder = ref<any>(null); 
const paymentForm = reactive({ amount: 0, method: 'transfer', attachment_id: null });
const proofPreviewUrl = ref<string | null>(null);

const totalReceivables = computed(() => receivables.value.reduce((sum, item) => sum + (Number(item.remaining_amount) || 0), 0));
const hasActiveFilter = computed(() => !!(searchQuery.value || filterType.value));

const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val || 0);
const formatDate = (d: string) => { 
  if (!d || d.startsWith('1970')) return 'Belum Set'; 
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }); 
};

// HITUNG UMUR PIUTANG (AGING OF RECEIVABLES)
const getReceivableAge = (createdAt: string) => {
  if (!createdAt) return 0;
  const created = new Date(createdAt);
  const now = new Date();
  const diffTime = Math.abs(now.getTime() - created.getTime());
  return Math.floor(diffTime / (1000 * 60 * 60 * 24));
};

const getAgingBadge = (createdAt: string) => {
  const days = getReceivableAge(createdAt);
  if (days <= 30) return { label: `${days} Hari (Lancar)`, type: 'success' as const };
  if (days <= 60) return { label: `${days} Hari (30-60)`, type: 'warning' as const };
  if (days <= 90) return { label: `${days} Hari (61-90)`, type: 'danger' as const };
  return { label: `${days} Hari (>90 Hari)`, type: 'danger' as const };
};

// Deteksi apakah piutang sudah melewati tanggal jatuh tempo
const isOverdue = (date: string) => { 
  if (!date) return false; 
  const due = new Date(date);
  const now = new Date();
  return due < now && due.toDateString() !== now.toDateString(); 
};

const getStatusLabel = (row: any) => {
  if (isOverdue(row.due_date)) return 'JATUH TEMPO';
  return (Number(row.remaining_amount) < Number(row.total_amount) && Number(row.remaining_amount) > 0) ? 'DICICIL' : 'BELUM BAYAR';
};

const getStatusType = (row: any) => {
  if (isOverdue(row.due_date)) return 'danger';
  return (Number(row.remaining_amount) < Number(row.total_amount) && Number(row.remaining_amount) > 0) ? 'warning' : 'danger';
};

watch(filterType, () => loadData());
watch(searchQuery, (newVal) => {
  if (!newVal || newVal.trim() === '') loadData();
});

// LOAD DATA: PESANAN UNPAID DENGAN METODE KREDIT
const loadData = async () => {
  loading.value = true; 
  try { 
    const params = new URLSearchParams();
    params.append('payment_status', 'unpaid');
    params.append('payment_method', 'kredit');
    
    if (filterType.value) params.append('type', filterType.value);
    if (searchQuery.value && searchQuery.value.trim()) params.append('search', searchQuery.value.trim());

    const res = await apiFetch(`/orders?${params.toString()}`); 
    receivables.value = unwrap(res); 
  } finally { 
    loading.value = false; 
  }
};

const resetFilters = () => {
  filterType.value = '';
  searchQuery.value = '';
  loadData();
};

const openPaymentModal = (row: any) => { 
  selectedOrder.value = row; 
  paymentForm.amount = Number(row.remaining_amount) || 0; 
  paymentForm.method = 'transfer';
  paymentForm.attachment_id = null; 
  proofPreviewUrl.value = null; 
  isPaymentOpen.value = true; 
};

const handleProofUpload = async (file: any) => {
  if (!file.raw) return;
  proofPreviewUrl.value = URL.createObjectURL(file.raw);
  try {
    const res = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Order', type: 'payment_proof' });
    paymentForm.attachment_id = res.id; 
    ElMessage.success('Foto bukti struk siap dilampirkan!');
  } catch (e) { 
    ElMessage.error('Gagal mengunggah foto'); 
    proofPreviewUrl.value = null; 
  }
};

const submitPayment = async () => {
  if (paymentForm.amount <= 0) return ElMessage.warning('Jumlah pembayaran tidak valid.');
  
  loadingPay.value = true;
  try {
    await apiFetch(`/orders/${selectedOrder.value.id}/pay`, {
      method: 'POST',
      body: {
        amount: paymentForm.amount,
        method: paymentForm.method,
        attachment_id: paymentForm.attachment_id
      }
    });
    ElMessage.success('Pembayaran cicilan berhasil dicatat!');
    isPaymentOpen.value = false;
    loadData();
  } catch (e: any) {
    ElMessage.error(e.data?.message || 'Gagal mencatat pembayaran');
  } finally {
    loadingPay.value = false;
  }
};

// PENGINGAT WHATSAPP RESMI ABC JAKARTA
const sendWhatsAppReminder = (row: any) => {
  const phone = row.customer?.phone;
  if (!phone) return ElMessage.warning('Nomor WhatsApp pelanggan tidak tersedia.');

  let cleanPhone = phone.replace(/[^0-9]/g, '');
  if (cleanPhone.startsWith('0')) {
    cleanPhone = '62' + cleanPhone.slice(1);
  }

  const message = 
    `Salam damai sejahtera Bpk/Ibu *${row.customer?.name}*,\n` +
    `Kami dari *Adventist Book Center (ABC) Jakarta* menyampaikan pengingat terkait tagihan literatur buku Anda.\n\n` +
    `📌 *Rincian Faktur Nota:*\n` +
    `• No. Invoice: *${row.order_number}*\n` +
    `• Sisa Tagihan: *Rp ${formatNumber(row.remaining_amount)}*\n` +
    `• Jatuh Tempo: *${formatDate(row.due_date)}*\n\n` +
    `Pembayaran cicilan/pelunasan dapat ditransfer ke rekening resmi ABC Jakarta:\n` +
    `🏦 *Bank Mandiri*: 123-00-9876543-2\n` +
    `a.n. *Adventist Book Center Jakarta*\n\n` +
    `Layanan Konfirmasi / Bukti Transfer: 0813-7699-0897 (Sdri. Rosintan - Admin ABC Jakarta)\n\n` +
    `Mohon mengirimkan foto bukti transfer balasan di chat ini setelah melakukan pembayaran. Terima kasih atas kerja samanya. Tuhan memberkati! 🙏`;

  const waUrl = `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(message)}`;
  window.open(waUrl, '_blank');
};

onMounted(loadData);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { 
  @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; 
}
</style>