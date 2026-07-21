<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- 1. HEADER & FILTER -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Buku Piutang</h2>
          <p class="text-sm text-slate-500">Kelola penagihan invoice kredit dan riwayat cicilan pelanggan.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
          <!-- Filter Tipe Pelanggan -->
          <el-select v-model="filterType" placeholder="Tipe Pelanggan" clearable class="w-40" @change="loadData">
            <el-option label="Penginjil (PL)" value="penginjil" />
            <el-option label="Gereja" value="gereja" />
            <el-option label="Sekolah" value="sekolah" />
          </el-select>

          <!-- Search Invoice/Nama -->
          <el-input 
            v-model="searchQuery" 
            placeholder="Cari No. Invoice..." 
            clearable 
            class="w-full sm:w-64"
            @keyup.enter="loadData"
            @clear="loadData"
          >
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
        </div>
      </div>

      <!-- 2. TABEL DATA PIUTANG -->
      <div class="overflow-x-auto">
        <el-table :data="receivables" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
          
          <el-table-column label="Invoice & Tanggal" width="180">
            <template #default="{ row }">
              <div class="font-bold text-[#1B293C]">{{ row.order_number }}</div>
              <div class="text-[10px] text-slate-400">{{ formatDateTime(row.created_at) }}</div>
            </template>
          </el-table-column>

          <el-table-column label="Pelanggan" min-width="200">
            <template #default="{ row }">
              <div class="font-bold text-slate-700">{{ row.customer?.name }}</div>
              <el-tag size="small" type="info" effect="plain" class="mt-1 uppercase text-[9px] font-bold">
                {{ row.customer?.type }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column label="Total Nota" width="140" align="right">
            <template #default="{ row }">
              <span class="text-slate-500 font-medium">Rp {{ formatNumber(row.total_amount) }}</span>
            </template>
          </el-table-column>

          <el-table-column label="Sisa Tagihan" width="150" align="right">
            <template #default="{ row }">
              <!-- Warna Merah jika masih ada hutang, Abu-abu jika Lunas -->
              <span :class="(row.remaining_amount ?? row.total_amount) > 0 ? 'text-red-500 font-black' : 'text-slate-300 font-medium'">
                Rp {{ formatNumber(row.remaining_amount ?? (row.payment_status === 'paid' ? 0 : row.total_amount)) }}
              </span>
            </template>
          </el-table-column>

          <el-table-column label="Status" width="120" align="center">
            <template #default="{ row }">
              <el-tag :type="getStatusType(row)" effect="dark" size="small" class="font-bold">
                {{ getStatusLabel(row) }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column label="Aksi" width="120" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-2">
                <!-- Tombol Bayar Cicilan (Hanya jika belum lunas) -->
                <el-tooltip v-if="row.payment_status !== 'paid'" content="Input Pembayaran Cicilan" placement="top">
                  <el-button 
                    circle size="small" type="warning" 
                    @click="openPaymentModal(row)"
                  >
                    <Icon icon="solar:wallet-money-bold" />
                  </el-button>
                </el-tooltip>

                <!-- Tombol WhatsApp Reminder (Fix Icon & Style) -->
                <el-tooltip v-if="row.payment_status !== 'paid'" content="Kirim Tagihan via WhatsApp" placement="top">
                  <el-button 
                    circle size="small" 
                    type="success"
                    color="#25D366"
                    plain
                    @click="sendWhatsAppReminder(row)"
                  >
                    <Icon icon="solar:whatsapp-bold" class="text-lg" />
                  </el-button>
                </el-tooltip>

                <!-- Status Done -->
                <Icon v-if="row.payment_status === 'paid'" icon="solar:check-circle-bold" class="text-2xl text-green-400" />
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- 3. MODAL: INPUT PEMBAYARAN CICILAN -->
    <el-dialog v-model="isPaymentOpen" title="Pencatatan Cicilan" width="400px" align-center append-to-body>
      <div v-if="selectedOrder" class="mb-5 space-y-3">
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
          <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest leading-none mb-1">Sisa Tagihan Nota Ini</p>
          <p class="text-3xl font-black text-red-500">Rp {{ formatNumber(selectedOrder.remaining_amount ?? selectedOrder.total_amount) }}</p>
        </div>
        
        <el-form label-position="top">
          <el-form-item label="Jumlah Bayar (Rp)" required>
            <el-input-number 
              v-model="paymentForm.amount" 
              :min="1" 
              :max="selectedOrder.remaining_amount ?? selectedOrder.total_amount" 
              class="!w-full"
            />
          </el-form-item>
          <el-form-item label="Metode Pembayaran">
            <el-select v-model="paymentForm.method" class="w-full">
              <el-option label="Transfer Bank" value="transfer" />
              <el-option label="Tunai / Cash" value="cash" />
            </el-select>
          </el-form-item>
        </el-form>
      </div>
      <template #footer>
        <el-button @click="isPaymentOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" @click="submitPayment" :loading="loadingPay">
          Simpan Pembayaran
        </el-button>
      </template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { ElMessage, ElMessageBox } from 'element-plus';
import { onMounted, reactive, ref } from 'vue';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const { apiFetch } = useApi();

// States
const receivables = ref([]);
const loading = ref(false);
const loadingPay = ref(false);
const searchQuery = ref('');
const filterType = ref('');
const isPaymentOpen = ref(false);
const selectedOrder = ref(null);
const paymentForm = reactive({ amount: 0, method: 'transfer' });

// Helpers
const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val || 0);
const formatDateTime = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const getStatusLabel = (row: any) => {
  if (row.payment_status === 'paid') return 'LUNAS';
  const remaining = row.remaining_amount ?? row.total_amount;
  return (remaining < row.total_amount && remaining > 0) ? 'DICICIL' : 'BELUM BAYAR';
};

const getStatusType = (row: any) => {
  if (row.payment_status === 'paid') return 'success';
  const remaining = row.remaining_amount ?? row.total_amount;
  return (remaining < row.total_amount && remaining > 0) ? 'warning' : 'danger';
};

// Functions
const loadData = async () => {
  loading.value = true;
  try {
    // API Call: Hanya ambil yang BELUM LUNAS (Piutang Berjalan)
    const res = await apiFetch(`/orders?payment_status=unpaid&search=${searchQuery.value}&type=${filterType.value}`);
    receivables.value = res.data || res;
  } catch (e) {
    ElMessage.error('Gagal memuat data piutang');
  } finally {
    loading.value = false;
  }
};

const openPaymentModal = (row: any) => {
  selectedOrder.value = row;
  paymentForm.amount = row.remaining_amount ?? row.total_amount;
  isPaymentOpen.value = true;
};

const submitPayment = async () => {
  if (paymentForm.amount <= 0) return ElMessage.warning('Masukkan nominal pembayaran yang valid');
  
  loadingPay.value = true;
  try {
    await apiFetch(`/orders/${selectedOrder.value.id}/pay`, {
      method: 'POST',
      body: paymentForm
    });
    ElMessage.success('Pembayaran cicilan berhasil dicatat!');
    isPaymentOpen.value = false;
    loadData(); // Refresh list agar yang sudah lunas hilang dari buku piutang
  } catch (e) {
    ElMessage.error('Gagal mencatat pembayaran');
  } finally {
    loadingPay.value = false;
  }
};

const sendWhatsAppReminder = (row: any) => {
  const remaining = row.remaining_amount ?? row.total_amount;
  // Template Pesan Profesional ABC Jakarta
  const message = 
    `Syalom ${row.customer?.name},\n\n` +
    `Kami dari *ABC (Adventist Book Center) Jakarta* ingin menginformasikan sisa tagihan untuk *Invoice ${row.order_number}* adalah sebesar *Rp ${formatNumber(remaining)}*.\n\n` +
    `Pembayaran dapat dilakukan melalui transfer bank atau setor tunai ke kantor kami. Mohon abaikan pesan ini jika Anda sudah melakukan pelunasan. Terima kasih, Tuhan memberkati. 🙏✨`;
    
  const url = `https://wa.me/${row.customer?.phone}?text=${encodeURIComponent(message)}`;
  window.open(url, '_blank');
};

onMounted(loadData);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; }
</style>