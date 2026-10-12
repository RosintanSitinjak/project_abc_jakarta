<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- HEADER DENGAN FILTER CANGGIH -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Daftar Gereja & Jemaat</h2>
          <p class="text-sm text-slate-500">Kelola data pelanggan, verifikasi PL, dan monitoring piutang.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
          <!-- Filter Tipe -->
          <el-select v-model="filterType" placeholder="Tipe" clearable class="w-32" size="default">
            <el-option label="Jemaat" value="jemaat" />
            <el-option label="Gereja" value="gereja" />
            <el-option label="Sekolah" value="sekolah" />
            <el-option label="Penginjil" value="penginjil" />
          </el-select>

          <!-- Filter Status -->
          <el-select v-model="filterStatus" placeholder="Status" clearable class="w-32" size="default">
            <el-option label="Aktif" value="approved" />
            <el-option label="Pending" value="pending" />
          </el-select>

          <!-- Pencarian Nama -->
          <el-input v-model="searchQuery" placeholder="Cari nama..." clearable class="w-full sm:w-64">
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>

          <el-button type="primary" color="#00A9C3" @click="openCreate">
            + Tambah Pelanggan
          </el-button>
        </div>
      </div>

      <!-- TABEL DAFTAR PELANGGAN -->
      <div class="mt-6 overflow-x-auto">
        <el-table :data="customers" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
          <el-table-column prop="name" label="Nama Pelanggan" min-width="180" />
          
          <el-table-column label="Kategori" width="130" align="center">
            <template #default="{ row }">
              <el-tag size="small" :type="row.type === 'penginjil' ? 'warning' : 'info'" effect="plain">
                {{ row.type.toUpperCase() }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column label="Status Akun" width="120" align="center">
            <template #default="{ row }">
              <el-tag size="small" :type="row.status === 'approved' ? 'success' : 'danger'" effect="dark">
                {{ row.status === 'approved' ? 'AKTIF' : 'PENDING' }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column prop="phone" label="WhatsApp" width="140" />
          
          <el-table-column label="Limit Kredit" width="150">
            <template #default="{ row }">
              <span v-if="row.type === 'jemaat'" class="text-slate-400 italic text-xs">Cash Only</span>
              <span v-else class="font-semibold text-slate-600">Rp {{ formatNumber(row.credit_limit) }}</span>
            </template>
          </el-table-column>

          <!-- KOLOM BARU: HUTANG BERJALAN (POIN SKRIPSI) -->
          <el-table-column label="Hutang Saat Ini" width="160">
            <template #default="{ row }">
              <span :class="row.current_debt > 0 ? 'text-red-500 font-black' : 'text-green-500 font-bold'">
                Rp {{ formatNumber(row.current_debt || 0) }}
              </span>
            </template>
          </el-table-column>

          <!-- AKSI: Edit, Hapus, & Kirim WA -->
          <el-table-column label="Aksi" width="160" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-1">
                <!-- Tombol Kirim Akun via WA (Fitur Gaptek) -->
                <el-tooltip content="Kirim Info Akun via WA" placement="top">
                  <el-button circle size="small" type="success" plain @click="sendWaAccount(row)">
                    <Icon icon="solar:whatsapp-bold" />
                  </el-button>
                </el-tooltip>

                <el-button circle size="small" type="primary" plain @click="openEdit(row)">
                  <Icon icon="solar:pen-2-bold" />
                </el-button>
                
                <el-button circle size="small" type="danger" plain @click="confirmDelete(row)">
                  <Icon icon="solar:trash-bin-trash-bold" />
                </el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- FORM DIALOG (Lengkap dengan Pilihan Sekolah) -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru'" width="500px" align-center>
      <el-form :model="form" label-position="top">
        <el-form-item label="Nama Lengkap / Instansi" required>
          <el-input v-model="form.name" placeholder="Misal: Gereja Advent UNAI" />
        </el-form-item>
        
        <el-form-item label="Kategori Pelanggan">
          <el-select v-model="form.type" class="w-full" @change="handleTypeChange">
            <el-option label="Jemaat Personal (Cash Only)" value="jemaat" />
            <el-option label="Gereja / Institusi" value="gereja" />
            <el-option label="Sekolah" value="sekolah" />
            <el-option label="Penginjil Literatur (PL)" value="penginjil" />
          </el-select>
        </el-form-item>

        <el-form-item label="Status Verifikasi">
          <el-radio-group v-model="form.status">
            <el-radio value="approved">Aktif (Approved)</el-radio>
            <el-radio value="pending">Menunggu (Pending)</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="Nomor WhatsApp">
          <el-input v-model="form.phone" placeholder="Contoh: 62812345678" />
        </el-form-item>

        <el-form-item v-if="form.type !== 'jemaat'" label="Batas Limit Kredit (Rupiah)">
          <el-input-number 
            v-model="form.credit_limit" 
            :min="0" 
            :max="form.type === 'penginjil' ? 5000000 : 999999999" 
            class="!w-full" 
          />
        </el-form-item>

        <el-form-item label="Alamat Lengkap">
          <el-input v-model="form.address" type="textarea" :rows="2" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="isDialogOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" @click="submitForm">Simpan Data</el-button>
      </template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { ElMessage, ElMessageBox } from 'element-plus';
import { onMounted, reactive, ref, watch } from 'vue';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const { apiFetch, unwrap } = useApi();
const customers = ref([]);
const loading = ref(false);
const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const searchQuery = ref('');
const filterType = ref('');
const filterStatus = ref('');

const form = reactive({ name: '', type: 'jemaat', status: 'approved', phone: '', address: '', credit_limit: 0 });

const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val);

const handleTypeChange = (val) => {
  if (val === 'jemaat') form.credit_limit = 0;
  // Penginjil otomatis pending biar admin verifikasi dulu
  if (val === 'penginjil' && !isEditing.value) form.status = 'pending';
};

// FITUR WA: Kirim info akun (Sangat berguna buat jemaat gaptek)
const sendWaAccount = (row: any) => {
  const message = `Syalom ${row.name}, akun Balai Buku Advent Jakarta Anda sudah aktif. Silakan login ke website untuk melakukan pemesanan. Terima kasih.`;
  const url = `https://wa.me/${row.phone}?text=${encodeURIComponent(message)}`;
  window.open(url, '_blank');
};

const loadCustomers = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (searchQuery.value) params.append('search', searchQuery.value);
    if (filterType.value) params.append('type', filterType.value);
    if (filterStatus.value) params.append('status', filterStatus.value);

    const res = await apiFetch(`/customers?${params.toString()}`);
    customers.value = unwrap(res);
  } catch (e) { ElMessage.error('Gagal memuat data'); }
  finally { loading.value = false; }
};

const openCreate = () => {
  isEditing.value = false;
  Object.assign(form, { name: '', type: 'jemaat', status: 'approved', phone: '', address: '', credit_limit: 0 });
  isDialogOpen.value = true;
};

const openEdit = (row: any) => {
  isEditing.value = true;
  editingId.value = row.id;
  Object.assign(form, { ...row });
  isDialogOpen.value = true;
};

const submitForm = async () => {
  if(!form.name) return ElMessage.warning('Nama wajib diisi');
  try {
    const method = isEditing.value ? 'PUT' : 'POST';
    const url = isEditing.value ? `/customers/${editingId.value}` : '/customers';
    await apiFetch(url, { method, body: form });
    ElMessage.success('Data tersimpan');
    isDialogOpen.value = false;
    loadCustomers();
  } catch (e) { ElMessage.error('Gagal simpan'); }
};

const confirmDelete = async (row: any) => {
  try {
    await ElMessageBox.confirm(`Hapus ${row.name}?`, 'Hapus', { type: 'warning' });
    await apiFetch(`/customers/${row.id}`, { method: 'DELETE' });
    ElMessage.success('Terhapus');
    loadCustomers();
  } catch (e) {}
};

// Pantau perubahan filter
watch([searchQuery, filterType, filterStatus], () => {
  loadCustomers();
});

onMounted(loadCustomers);
</script>