<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- HEADER -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Database Gereja & Jemaat</h2>
          <p class="text-sm text-slate-500">Kelola informasi profil dan plafon kredit pelanggan.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <el-select v-model="filterType" placeholder="Tipe" clearable class="w-32" @change="loadCustomers">
            <el-option label="Jemaat" value="jemaat" /><el-option label="Gereja" value="gereja" /><el-option label="PL" value="penginjil" /><el-option label="Sekolah" value="sekolah" />
          </el-select>
          <el-input v-model="searchQuery" placeholder="Cari nama..." clearable class="w-full sm:w-64" @keyup.enter="loadCustomers" />
          <el-button type="primary" color="#00A9C3" @click="openCreate">+ Tambah</el-button>
        </div>
      </div>

      <!-- TABEL DATA -->
      <el-table :data="customers" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
        <el-table-column prop="name" label="Nama Pelanggan" min-width="180" />
        <el-table-column label="Kategori" width="120" align="center">
          <template #default="{ row }">
            <el-tag size="small" effect="plain" class="uppercase font-bold">{{ row.type }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="phone" label="WhatsApp" width="140" />
        
        <!-- KOLOM LIMIT KREDIT (REVISI VISUAL) -->
        <el-table-column label="Limit Kredit" width="150" align="right">
          <template #default="{ row }">
            <div class="flex flex-col items-end">
              <span v-if="row.type === 'jemaat'" class="text-[10px] text-slate-400 italic">Cash Only</span>
              
              <!-- Jika limit sangat tinggi (simbol Tanpa Batas) -->
              <el-tag v-else-if="row.credit_limit >= 900000000" size="small" type="success" effect="plain">Tanpa Batas</el-tag>
              
              <span v-else class="font-bold text-slate-700">Rp {{ formatNumber(row.credit_limit) }}</span>
              
              <span v-if="row.type === 'penginjil' && row.credit_limit === 5000000" class="text-[8px] text-orange-400 font-black uppercase">Standard PL</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Hutang Berjalan" width="140" align="right">
          <template #default="{ row }">
            <span :class="row.current_debt > 0 ? 'text-red-500 font-black' : 'text-slate-400'">Rp {{ formatNumber(row.current_debt || 0) }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Aksi" width="150" align="center">
          <template #default="{ row }">
            <div class="flex justify-center gap-1">
              <el-button circle size="small" type="info" plain @click="viewHistory(row)"><Icon icon="solar:history-bold" /></el-button>
              <el-button circle size="small" type="primary" plain @click="openEdit(row)"><Icon icon="solar:pen-2-bold" /></el-button>
              <el-button circle size="small" type="danger" plain @click="confirmDelete(row)"><Icon icon="solar:trash-bin-trash-bold" /></el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </section>

    <!-- FORM DIALOG EDIT/TAMBAH -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit Profil' : 'Tambah Baru'" width="500px">
       <el-form :model="form" label-position="top">
          <el-form-item label="Nama Lengkap / Instansi" required><el-input v-model="form.name" /></el-form-item>
          <div class="grid grid-cols-2 gap-4">
            <el-form-item label="Kategori">
              <el-select v-model="form.type" class="w-full">
                <el-option label="Jemaat" value="jemaat" /><el-option label="Gereja" value="gereja" /><el-option label="PL" value="penginjil" /><el-option label="Sekolah" value="sekolah" />
              </el-select>
            </el-form-item>
            <el-form-item label="WhatsApp"><el-input v-model="form.phone" /></el-form-item>
          </div>
          <el-form-item v-if="form.type !== 'jemaat'" label="Plafon Limit Kredit (Kosongkan jika Tanpa Batas)">
            <el-input-number v-model="form.credit_limit" :min="0" :max="999000000" class="!w-full" />
          </el-form-item>
          <el-form-item label="Alamat Pengiriman"><el-input v-model="form.address" type="textarea" :rows="3" /></el-form-item>
       </el-form>
       <template #footer><el-button type="primary" color="#00A9C3" @click="submitForm">Simpan Perubahan</el-button></template>
    </el-dialog>

    <!-- DIALOG HISTORY TETAP SAMA -->
    <el-dialog v-model="isHistoryOpen" title="Riwayat Cicilan" width="600px">
       <el-table :data="paymentHistoryData" border size="small">
          <el-table-column label="Tanggal"><template #default="{ row }">{{ new Date(row.created_at).toLocaleDateString('id-ID') }}</template></el-table-column>
          <el-table-column label="Nominal"><template #default="{ row }">Rp {{ formatNumber(row.amount) }}</template></el-table-column>
          <el-table-column prop="payment_method" label="Metode" align="center" />
          <el-table-column label="Bukti" align="center"><template #default="{ row }"><el-image v-if="row.proof_url" :src="row.proof_url" :preview-src-list="[row.proof_url]" class="w-8 h-8 rounded" fit="cover" /><span v-else>-</span></template></el-table-column>
       </el-table>
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
const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const form = reactive({ name: '', type: 'jemaat', phone: '', address: '', credit_limit: 0 });
const isHistoryOpen = ref(false);
const paymentHistoryData = ref([]);

const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val || 0);

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

const openCreate = () => { isEditing.value = false; Object.assign(form, { name: '', type: 'jemaat', phone: '', address: '', credit_limit: 0 }); isDialogOpen.value = true; };
const openEdit = (row: any) => { isEditing.value = true; editingId.value = row.id; Object.assign(form, { ...row }); isDialogOpen.value = true; };
const submitForm = async () => {
  const method = isEditing.value ? 'PUT' : 'POST';
  await apiFetch(isEditing.value ? `/customers/${editingId.value}` : '/customers', { method, body: form });
  ElMessage.success('Tersimpan'); isDialogOpen.value = false; loadCustomers();
};
const viewHistory = async (row: any) => {
  const res = await apiFetch(`/customers/${row.id}/payment-history`);
  paymentHistoryData.value = res; isHistoryOpen.value = true;
};
const confirmDelete = async (row: any) => {
  await ElMessageBox.confirm(`Hapus ${row.name}?`, 'Peringatan');
  await apiFetch(`/customers/${row.id}`, { method: 'DELETE' });
  loadCustomers();
};
onMounted(loadCustomers);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; }
</style>