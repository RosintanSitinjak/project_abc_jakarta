<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Daftar Gereja & Jemaat</h2>
          <p class="text-sm text-slate-500">Kelola data pelanggan dan monitoring piutang berjalan.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
          <el-select v-model="filterType" placeholder="Tipe" clearable class="w-32" @change="loadCustomers">
            <el-option label="Jemaat" value="jemaat" /><el-option label="Gereja" value="gereja" /><el-option label="PL" value="penginjil" />
          </el-select>
          <el-input v-model="searchQuery" placeholder="Cari nama..." clearable class="w-full sm:w-64" @keyup.enter="loadCustomers" />
          <el-button type="primary" color="#00A9C3" @click="openCreate">+ Tambah</el-button>
        </div>
      </div>

      <el-table :data="customers" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
        <el-table-column prop="name" label="Nama Pelanggan" min-width="180" />
        <el-table-column label="Hutang Saat Ini" width="160">
          <template #default="{ row }">
            <span :class="row.current_debt > 0 ? 'text-red-500 font-black' : 'text-green-500 font-bold'">
              Rp {{ formatNumber(row.current_debt || 0) }}
            </span>
          </template>
        </el-table-column>

        <el-table-column label="Aksi" width="220" align="center">
          <template #default="{ row }">
            <div class="flex justify-center gap-1">
              <!-- RIWAYAT/KARTU PIUTANG -->
              <el-tooltip content="Kartu Piutang (History)" placement="top">
                <el-button circle size="small" type="info" plain @click="viewHistory(row)">
                  <Icon icon="solar:history-bold" />
                </el-button>
              </el-tooltip>

              <!-- BAYAR CICILAN -->
              <el-tooltip v-if="row.current_debt > 0" content="Input Setoran" placement="top">
                <el-button circle size="small" type="warning" @click="openPayDialog(row)">
                  <Icon icon="solar:wallet-money-bold" />
                </el-button>
              </el-tooltip>

              <el-button circle size="small" type="primary" plain @click="openEdit(row)"><Icon icon="solar:pen-2-bold" /></el-button>
              <el-button circle size="small" type="danger" plain @click="confirmDelete(row)"><Icon icon="solar:trash-bin-trash-bold" /></el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </section>

    <!-- DIALOG: INPUT SETORAN CICILAN -->
    <el-dialog v-model="isPayOpen" title="Pencatatan Setoran Piutang" width="450px">
      <div v-if="selectedCustomer" class="mb-4 p-4 bg-slate-50 rounded-2xl text-center">
         <p class="text-[10px] font-bold text-slate-400 uppercase">Hutang {{ selectedCustomer.name }}</p>
         <p class="text-3xl font-black text-red-500">Rp {{ formatNumber(selectedCustomer.current_debt) }}</p>
      </div>
      <el-form label-position="top">
        <el-form-item label="Jumlah Setoran (Rp)" required>
          <el-input-number v-model="payForm.amount" :min="1" :max="selectedCustomer?.current_debt" class="!w-full" />
        </el-form-item>
        <el-form-item label="Metode Bayar">
          <el-select v-model="payForm.payment_method" class="w-full">
            <el-option label="Transfer Bank" value="transfer" /><el-option label="Tunai / Cash" value="cash" />
          </el-select>
        </el-form-item>
        <el-form-item label="Upload Bukti Struk">
          <el-upload action="#" :auto-upload="false" :show-file-list="false" :on-change="handleUploadProof">
             <div class="border-2 border-dashed border-slate-200 p-4 rounded-xl text-center cursor-pointer hover:border-[#00A9C3]">
                <Icon v-if="!payForm.attachment_id" icon="solar:camera-add-bold" class="text-2xl text-slate-300 mx-auto" />
                <Icon v-else icon="solar:check-circle-bold" class="text-2xl text-green-500 mx-auto" />
                <p class="text-[10px] mt-1">{{ payForm.attachment_id ? 'Bukti Terpilih' : 'Klik untuk Unggah Foto' }}</p>
             </div>
          </el-upload>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="isPayOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" @click="submitPayment" :loading="loadingPay">Simpan Setoran</el-button>
      </template>
    </el-dialog>

    <!-- DIALOG: KARTU PIUTANG (HISTORY) -->
    <el-dialog v-model="isHistoryOpen" title="Kartu Piutang / Riwayat Cicilan" width="600px">
       <el-table :data="paymentHistoryData" border size="small">
          <el-table-column label="Tanggal" width="130">
             <template #default="{ row }">{{ new Date(row.created_at).toLocaleDateString('id-ID') }}</template>
          </el-table-column>
          <el-table-column label="Nominal" width="130">
             <template #default="{ row }">Rp {{ formatNumber(row.amount) }}</template>
          </el-table-column>
          <el-table-column prop="payment_method" label="Metode" width="100" />
          <el-table-column label="Bukti" align="center">
             <template #default="{ row }">
                <el-image v-if="row.proof_url" :src="row.proof_url" :preview-src-list="[row.proof_url]" class="w-8 h-8 rounded" fit="cover" />
                <span v-else class="text-slate-300">-</span>
             </template>
          </el-table-column>
       </el-table>
    </el-dialog>

    <!-- FORM DIALOG: TAMBAH/EDIT -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit' : 'Tambah'" width="500px">
       <el-form :model="form" label-position="top">
          <el-form-item label="Nama" required><el-input v-model="form.name" /></el-form-item>
          <el-form-item label="Kategori">
            <el-select v-model="form.type" class="w-full">
              <el-option label="Jemaat" value="jemaat" /><el-option label="Gereja" value="gereja" /><el-option label="PL" value="penginjil" />
            </el-select>
          </el-form-item>
          <el-form-item v-if="form.type !== 'jemaat'" label="Limit"><el-input-number v-model="form.credit_limit" class="!w-full" /></el-form-item>
       </el-form>
       <template #footer><el-button type="primary" @click="submitForm">Simpan</el-button></template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { ElMessage, ElMessageBox } from 'element-plus';
import { onMounted, reactive, ref, watch } from 'vue';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const { apiFetch, unwrap, uploadAttachment } = useApi();
const customers = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const filterType = ref('');
const filterStatus = ref('');

// State Cicilan
const isPayOpen = ref(false);
const loadingPay = ref(false);
const selectedCustomer = ref(null);
const payForm = reactive({ amount: 0, payment_method: 'transfer', attachment_id: null });

// State History
const isHistoryOpen = ref(false);
const paymentHistoryData = ref([]);

// State Edit
const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const form = reactive({ name: '', type: 'jemaat', status: 'approved', phone: '', address: '', credit_limit: 0 });

const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val);

const loadCustomers = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (searchQuery.value) params.append('search', searchQuery.value);
    if (filterType.value) params.append('type', filterType.value);
    const res = await apiFetch(`/customers?${params.toString()}`);
    customers.value = unwrap(res);
  } finally { loading.value = false; }
};

const openPayDialog = (row: any) => {
  selectedCustomer.value = row;
  payForm.amount = row.current_debt;
  payForm.attachment_id = null;
  isPayOpen.value = true;
};

const handleUploadProof = async (file: any) => {
  try {
    const res = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Customer', type: 'debt_payment' });
    payForm.attachment_id = res.id;
    ElMessage.success('Bukti terunggah');
  } catch (e) { ElMessage.error('Gagal upload'); }
};

const submitPayment = async () => {
  loadingPay.value = true;
  try {
    await apiFetch(`/customers/${selectedCustomer.value.id}/pay-debt`, { method: 'PATCH', body: payForm });
    ElMessage.success('Berhasil!');
    isPayOpen.value = false;
    loadCustomers();
  } catch (e) { ElMessage.error('Gagal'); }
  finally { loadingPay.value = false; }
};

const viewHistory = async (row: any) => {
  try {
    const res = await apiFetch(`/customers/${row.id}/payment-history`);
    paymentHistoryData.value = res;
    isHistoryOpen.value = true;
  } catch (e) { ElMessage.error('Gagal ambil riwayat'); }
};

const openCreate = () => { isEditing.value = false; isDialogOpen.value = true; };
const openEdit = (row: any) => { isEditing.value = true; editingId.value = row.id; Object.assign(form, { ...row }); isDialogOpen.value = true; };
const submitForm = async () => {
  const method = isEditing.value ? 'PUT' : 'POST';
  await apiFetch(isEditing.value ? `/customers/${editingId.value}` : '/customers', { method, body: form });
  isDialogOpen.value = false; loadCustomers();
};
const confirmDelete = async (row: any) => {
  await ElMessageBox.confirm(`Hapus ${row.name}?`, 'Hapus');
  await apiFetch(`/customers/${row.id}`, { method: 'DELETE' });
  loadCustomers();
};

onMounted(loadCustomers);
</script>