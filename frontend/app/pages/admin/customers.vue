<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Daftar Gereja & Jemaat</h2>
          <p class="text-sm text-slate-500">Kelola data pelanggan, verifikasi PL, dan batas limit kredit.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Cari nama..." clearable class="w-full sm:w-64">
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          <el-button type="primary" color="#00A9C3" @click="openCreate">Tambah Pelanggan</el-button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto">
        <el-table :data="customers" v-loading="loading" stripe class="w-full">
          <el-table-column prop="name" label="Nama Pelanggan" min-width="180" />
          
          <el-table-column label="Kategori" width="130">
            <template #default="{ row }">
              <el-tag size="small" :type="row.type === 'penginjil' ? 'warning' : 'info'">{{ row.type.toUpperCase() }}</el-tag>
            </template>
          </el-table-column>

          <!-- KOLOM BARU: STATUS VERIFIKASI (PENTING BUAT SKRIPSI) -->
          <el-table-column label="Status Akun" width="130" align="center">
            <template #default="{ row }">
              <el-tag size="small" :type="row.status === 'approved' ? 'success' : 'danger'" effect="dark">
                {{ row.status === 'approved' ? 'AKTIF' : 'PENDING' }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column prop="phone" label="WhatsApp" width="140" />
          
          <el-table-column label="Limit Kredit" width="150">
            <template #default="{ row }">
              <span v-if="row.type === 'jemaat'" class="text-slate-400">Cash Only</span>
              <span v-else>Rp {{ new Intl.NumberFormat('id-ID').format(row.credit_limit) }}</span>
            </template>
          </el-table-column>

          <el-table-column label="Aksi" width="120" align="center">
            <template #default="{ row }">
              <el-button text type="primary" @click="openEdit(row)"><Icon icon="solar:pen-2-outline" /></el-button>
              <el-button text type="danger" @click="confirmDelete(row)"><Icon icon="solar:trash-bin-trash-outline" /></el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- Dialog Form -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru'" width="500px">
      <el-form :model="form" label-position="top">
        <el-form-item label="Nama Lengkap / Gereja" required>
          <el-input v-model="form.name" />
        </el-form-item>
        
        <el-form-item label="Tipe Pelanggan">
          <el-select v-model="form.type" class="w-full" @change="handleTypeChange">
            <el-option label="Personal Jemaat (Cash Only)" value="jemaat" />
            <el-option label="Gereja / Institusi" value="gereja" />
            <el-option label="Sekolah" value="sekolah" />
            <el-option label="Penginjil Literatur (PL)" value="penginjil" />
          </el-select>
        </el-form-item>

        <!-- Status Verifikasi (Muncul saat Edit) -->
        <el-form-item label="Status Verifikasi Akun">
          <el-radio-group v-model="form.status">
            <el-radio value="approved">Setujui (Aktif)</el-radio>
            <el-radio value="pending">Pending</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="Nomor WhatsApp">
          <el-input v-model="form.phone" placeholder="Contoh: 628xxxx" />
        </el-form-item>

        <!-- INPUT LIMIT (Hanya jika bukan jemaat personal) -->
        <el-form-item v-if="form.type !== 'jemaat'" label="Limit Kredit (Maks 5jt untuk PL)">
          <el-input-number 
            v-model="form.credit_limit" 
            :min="0" 
            :max="form.type === 'penginjil' ? 5000000 : 999999999"
            class="!w-full" 
          />
        </el-form-item>

        <el-form-item label="Alamat">
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

const form = reactive({ name: '', type: 'jemaat', status: 'approved', phone: '', address: '', credit_limit: 0 });

// Reset limit kalau pindah ke Jemaat Personal
const handleTypeChange = (val) => {
  if (val === 'jemaat') form.credit_limit = 0;
  if (val === 'penginjil') form.status = 'pending'; // Otomatis nunggu verifikasi
};

const loadCustomers = async () => {
  loading.value = true;
  try {
    const res = await apiFetch(`/customers?search=${searchQuery.value}`);
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
    ElMessage.success('Selesai disimpan');
    isDialogOpen.value = false;
    loadCustomers();
  } catch (e) { ElMessage.error('Gagal simpan. Cek validasi limit.'); }
};

const confirmDelete = async (row: any) => {
  try {
    await ElMessageBox.confirm(`Hapus ${row.name}?`, 'Hapus', { type: 'warning' });
    await apiFetch(`/customers/${row.id}`, { method: 'DELETE' });
    ElMessage.success('Terhapus');
    loadCustomers();
  } catch (e) {}
};

watch(searchQuery, () => loadCustomers());
onMounted(loadCustomers);
</script>