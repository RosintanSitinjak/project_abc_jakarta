<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Database Gereja & Jemaat</h2>
          <p class="text-sm text-slate-500">Kelola profil dan riwayat transaksi pelanggan.</p>
        </div>
        <div class="flex items-center gap-3">
          <el-select v-model="filterType" placeholder="Tipe" clearable class="w-32" @change="loadCustomers">
            <el-option label="Jemaat" value="jemaat" /><el-option label="Gereja" value="gereja" /><el-option label="PL" value="penginjil" /><el-option label="Sekolah" value="sekolah" />
          </el-select>
          <el-input v-model="searchQuery" placeholder="Cari..." clearable class="w-48" @keyup.enter="loadCustomers" />
          <el-button type="primary" color="#00A9C3" @click="openCreate">+ Tambah</el-button>
        </div>
      </div>

      <el-table :data="customers" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden text-xs">
        <el-table-column prop="name" label="Nama Pelanggan" min-width="180" fixed="left" />
        <el-table-column label="Kategori" width="120" align="center"><template #default="{ row }"><el-tag size="small" effect="plain" class="uppercase font-bold">{{ row.type }}</el-tag></template></el-table-column>
        <el-table-column prop="phone" label="WhatsApp" width="130" />
        <el-table-column label="Limit" width="140" align="right"><template #default="{ row }"><span v-if="row.type === 'jemaat'">Cash</span><span v-else>Rp {{ formatNumber(row.credit_limit) }}</span></template></el-table-column>
        <el-table-column label="Hutang" width="140" align="right"><template #default="{ row }"><span :class="row.current_debt > 0 ? 'text-red-500 font-bold' : 'text-slate-400'">Rp {{ formatNumber(row.current_debt) }}</span></template></el-table-column>
        
        <el-table-column label="Aksi" width="180" align="center" fixed="right">
          <template #default="{ row }">
            <div class="flex justify-center gap-1">
              <el-tooltip content="Riwayat Belanja" placement="top">
                <el-button circle size="small" type="primary" plain @click="openOrderHistory(row)"><Icon icon="solar:bag-heart-bold" /></el-button>
              </el-tooltip>
              <el-tooltip content="Kartu Piutang" placement="top">
                <el-button circle size="small" type="info" plain @click="viewHistory(row)"><Icon icon="solar:history-bold" /></el-button>
              </el-tooltip>
              <el-button circle size="small" type="primary" plain @click="openEdit(row)"><Icon icon="solar:pen-2-bold" /></el-button>
              <el-button circle size="small" type="danger" plain @click="confirmDelete(row)"><Icon icon="solar:trash-bin-trash-bold" /></el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </section>

    <!-- DIALOG: RIWAYAT BELANJA -->
    <el-dialog v-model="isOrderHistoryOpen" :title="'Riwayat Belanja: ' + selectedCustomer?.name" width="750px">
       <el-table :data="customerOrders" border stripe size="small">
          <el-table-column label="Waktu" width="130"><template #default="{ row }">{{ formatDateTime(row.created_at) }}</template></el-table-column>
          <el-table-column prop="order_number" label="No. Invoice" width="160" />
          <el-table-column label="Buku"><template #default="{ row }"><div v-for="i in row.items" :key="i.id" class="text-[9px] border-b last:border-0 py-1">- {{ i.book?.title }} ({{ i.qty }}x)</div></template></el-table-column>
          <el-table-column label="Total Nota" width="120" align="right"><template #default="{ row }">Rp {{ formatNumber(row.total_amount) }}</template></el-table-column>
          <el-table-column prop="payment_status" label="Status" width="100" align="center"><template #default="{ row }"><el-tag :type="row.payment_status === 'paid' ? 'success' : 'danger'" size="small">{{ row.payment_status.toUpperCase() }}</el-tag></template></el-table-column>
       </el-table>
    </el-dialog>
    
    <!-- (Dialog Edit & History Cicilan tetap sama seperti versi sebelumnya) -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit' : 'Tambah'" width="500px">
       <el-form :model="form" label-position="top">
          <el-form-item label="Nama" required><el-input v-model="form.name" /></el-form-item>
          <div class="grid grid-cols-2 gap-4">
            <el-form-item label="Tipe"><el-select v-model="form.type" class="w-full"><el-option label="Jemaat" value="jemaat" /><el-option label="Gereja" value="gereja" /><el-option label="PL" value="penginjil" /><el-option label="Sekolah" value="sekolah" /></el-select></el-form-item>
            <el-form-item label="WhatsApp"><el-input v-model="form.phone" /></el-form-item>
          </div>
          <el-form-item v-if="form.type !== 'jemaat'" label="Limit Kredit"><el-input-number v-model="form.credit_limit" :max="999000000" class="!w-full" /></el-form-item>
          <el-form-item label="Alamat"><el-input v-model="form.address" type="textarea" :rows="3" /></el-form-item>
       </el-form>
       <template #footer><el-button type="primary" color="#00A9C3" @click="submitForm">Simpan Perubahan</el-button></template>
    </el-dialog>

    <el-dialog v-model="isHistoryOpen" title="Kartu Piutang" width="600px">
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
import { onMounted, reactive, ref } from 'vue';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const { apiFetch, unwrap, uploadAttachment } = useApi();
const customers = ref([]); const loading = ref(false); const searchQuery = ref(''); const filterType = ref('');
const isDialogOpen = ref(false); const isEditing = ref(false); const editingId = ref(null);
const form = reactive({ name: '', type: 'jemaat', phone: '', address: '', credit_limit: 0 });
const isHistoryOpen = ref(false); const paymentHistoryData = ref([]);
const isOrderHistoryOpen = ref(false); const customerOrders = ref([]); const selectedCustomer = ref(null);

const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val || 0);
const formatDateTime = (d: string) => new Date(d).toLocaleDateString('id-ID', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit'});

const loadCustomers = async () => {
  loading.value = true; try { const res = await apiFetch(`/customers?search=${searchQuery.value}&type=${filterType.value}`); customers.value = unwrap(res); } finally { loading.value = false; }
};

const openOrderHistory = async (row: any) => {
    selectedCustomer.value = row;
    try { const res = await apiFetch(`/customers/${row.id}/orders`); customerOrders.value = res.data || res; isOrderHistoryOpen.value = true; } 
    catch (e) { ElMessage.error('Gagal!'); }
};

const viewHistory = async (row: any) => { const res = await apiFetch(`/customers/${row.id}/payment-history`); paymentHistoryData.value = res; isHistoryOpen.value = true; };
const openCreate = () => { isEditing.value = false; Object.assign(form, { name: '', type: 'jemaat', phone: '', address: '', credit_limit: 0 }); isDialogOpen.value = true; };
const openEdit = (row: any) => { isEditing.value = true; editingId.value = row.id; Object.assign(form, { ...row }); isDialogOpen.value = true; };
const submitForm = async () => { await apiFetch(isEditing.value ? `/customers/${editingId.value}` : '/customers', { method: isEditing.value ? 'PUT' : 'POST', body: form }); isDialogOpen.value = false; loadCustomers(); };
const confirmDelete = async (row: any) => { await ElMessageBox.confirm(`Hapus ${row.name}?`, 'Peringatan'); await apiFetch(`/customers/${row.id}`, { method: 'DELETE' }); loadCustomers(); };

onMounted(loadCustomers);
</script>