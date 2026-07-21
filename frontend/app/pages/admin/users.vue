<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- HEADER & FILTER (Sesuai kode kamu) -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Manajemen Pengguna</h2>
          <p class="text-sm text-slate-500">Verifikasi pendaftaran PL dan kontrol akses akun.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Cari nama/email..." clearable class="w-full sm:w-64" @clear="loadUsers">
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>

          <el-select v-model="filterType" placeholder="Tipe Akun" clearable @change="loadUsers" class="w-32">
            <el-option label="Jemaat" value="jemaat" />
            <el-option label="Penginjil" value="penginjil" />
            <el-option label="Gereja" value="gereja" />
            <el-option label="Sekolah" value="sekolah" />
          </el-select>

          <el-select v-model="filterStatus" placeholder="Status" clearable @change="loadUsers" class="w-32">
            <el-option label="Pending" value="pending" />
            <el-option label="Aktif" value="approved" />
            <el-option label="Ditolak" value="rejected" />
            <el-option label="Diblokir" value="suspended" />
          </el-select>

          <el-button type="primary" color="#00A9C3" @click="openCreate">+ Tambah User</el-button>
        </div>
      </div>

      <!-- TABEL DATA (Sesuai kode kamu) -->
      <div class="overflow-x-auto">
        <el-table :data="users" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
          <el-table-column prop="name" label="Nama" min-width="180">
             <template #default="{ row }">
                <div class="flex flex-col">
                  <span :class="{'line-through text-slate-400': row.customer?.status === 'suspended'}" class="font-bold">{{ row.name }}</span>
                  <span v-if="row.customer?.rejection_reason" class="text-[10px] text-danger italic">Ket: {{ row.customer.rejection_reason }}</span>
                </div>
             </template>
          </el-table-column>

          <el-table-column prop="email" label="Email" min-width="200" />
          
          <el-table-column label="Tipe Akun" width="130" align="center">
            <template #default="{ row }">
              <el-tag v-if="row.customer" size="small" :type="row.customer.type === 'penginjil' ? 'warning' : 'info'" effect="plain">
                {{ row.customer.type.toUpperCase() }}
              </el-tag>
              <el-tag v-else size="small" type="success">INTERNAL</el-tag>
            </template>
          </el-table-column>

          <el-table-column label="Status" width="120" align="center">
            <template #default="{ row }">
              <template v-if="row.customer">
                <el-tag v-if="row.customer.status === 'approved'" size="small" type="success">AKTIF</el-tag>
                <el-tag v-else-if="row.customer.status === 'pending'" size="small" type="warning">PENDING</el-tag>
                <el-tag v-else-if="row.customer.status === 'rejected'" size="small" type="danger">DITOLAK</el-tag>
                <el-tag v-else-if="row.customer.status === 'suspended'" size="small" type="info">DIBLOKIR</el-tag>
              </template>
              <span v-else>-</span>
            </template>
          </el-table-column>

          <el-table-column label="Aksi" width="220" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-1">
                <template v-if="row.customer?.type === 'penginjil' && row.customer?.status === 'pending'">
                  <el-button size="small" type="success" @click="handleApprove(row.customer.id)">Setujui PL</el-button>
                  <el-button size="small" type="danger" plain @click="openRejectModal(row.customer.id)">Tolak</el-button>
                </template>
                <template v-else>
                  <el-button circle size="small" type="primary" plain @click="openEdit(row)"><Icon icon="solar:pen-2-bold" /></el-button>
                  <el-button v-if="row.customer" circle size="small" :type="row.customer.status === 'suspended' ? 'success' : 'warning'" plain @click="handleToggleStatus(row.customer.id)">
                    <Icon :icon="row.customer.status === 'suspended' ? 'solar:play-bold' : 'solar:stop-bold'" />
                  </el-button>
                  <el-button v-if="row.id !== authUser?.id" circle size="small" type="danger" plain @click="confirmDelete(row)"><Icon icon="solar:trash-bin-trash-bold" /></el-button>
                </template>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- MODAL: TAMBAH & EDIT USER (DIPERBARUI) -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit Pengguna' : 'Tambah Pengguna Baru'" width="500px" append-to-body>
      <el-form label-position="top">
        <el-form-item label="Nama Lengkap" required>
          <el-input v-model="form.name" />
        </el-form-item>
        
        <el-form-item label="Email" required>
          <el-input v-model="form.email" :disabled="isEditing" />
        </el-form-item>

        <el-form-item label="Role / Hak Akses">
          <el-select v-model="form.role" class="w-full">
            <el-option label="Pimpinan ABC" :value="1" />
            <el-option label="Admin Staff" :value="2" />
            <el-option label="Pelanggan / Jemaat" :value="3" />
          </el-select>
        </el-form-item>

        <!-- FITUR BARU: PILIHAN TIPE JIKA ROLE = PELANGGAN -->
        <el-form-item v-if="form.role === 3" label="Kategori Pelanggan" required>
          <el-select v-model="form.customer_type" placeholder="Pilih Tipe" class="w-full">
             <el-option label="Jemaat Umum" value="jemaat" />
             <el-option label="Penginjil Literatur (PL)" value="penginjil" />
             <el-option label="Gereja / Institusi" value="gereja" />
             <el-option label="Sekolah" value="sekolah" />
          </el-select>
          <p class="text-[10px] text-[#00A9C3] mt-1 font-bold italic">* Menentukan jenis harga di katalog.</p>
        </el-form-item>

        <div class="grid grid-cols-2 gap-4">
          <el-form-item :label="isEditing ? 'Ganti Password' : 'Password'">
            <el-input v-model="form.password" type="password" show-password placeholder="Min 8 karakter" @keyup.enter="submitForm" />
          </el-form-item>
          <el-form-item label="Konfirmasi Password">
            <el-input v-model="form.password_confirmation" type="password" show-password @keyup.enter="submitForm" />
          </el-form-item>
        </div>
      </el-form>

      <template #footer>
        <el-button @click="isDialogOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" :loading="loadingSubmit" @click="submitForm">
          {{ isEditing ? 'Update Data' : 'Simpan User' }}
        </el-button>
      </template>
    </el-dialog>

    <!-- MODAL: PENOLAKAN PL (Sama seperti sebelumnya) -->
    <el-dialog v-model="rejectModalVisible" title="Tolak Pendaftaran PL" width="400px" append-to-body>
        <p class="mb-3 text-sm text-slate-600">Berikan alasan penolakan.</p>
        <el-input v-model="rejectionReason" type="textarea" :rows="3" placeholder="Nama tidak terdaftar..." />
        <template #footer>
            <el-button @click="rejectModalVisible = false">Batal</el-button>
            <el-button type="danger" @click="confirmReject">Konfirmasi Tolak</el-button>
        </template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
// ... (Bagian import tetap sama)
import { Icon } from "@iconify/vue";
import { ElMessage, ElMessageBox } from 'element-plus';
import { onMounted, reactive, ref, watch } from 'vue';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const { apiFetch } = useApi();
const users = ref([]);
const loading = ref(false);
const loadingSubmit = ref(false);
const authUser = useState<any>("auth-user", () => null);
const searchQuery = ref('');
const filterType = ref('');
const filterStatus = ref('');

const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = reactive({
  name: '',
  email: '',
  role: 3,
  customer_type: 'jemaat', // Default
  password: '',
  password_confirmation: ''
});

const rejectModalVisible = ref(false);
const rejectionReason = ref('');
const currentCustomerId = ref(null);

const loadUsers = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (searchQuery.value) params.append('search', searchQuery.value);
    if (filterType.value) params.append('type', filterType.value);
    if (filterStatus.value) params.append('status', filterStatus.value);
    const res = await apiFetch(`/user-management?${params.toString()}`);
    users.value = res.data || res;
  } finally { loading.value = false; }
};

const openCreate = () => {
  isEditing.value = false;
  editingId.value = null;
  Object.assign(form, { name: '', email: '', role: 3, customer_type: 'jemaat', password: '', password_confirmation: '' });
  isDialogOpen.value = true;
};

const openEdit = (row: any) => {
  isEditing.value = true;
  editingId.value = row.id;
  Object.assign(form, {
    name: row.name,
    email: row.email,
    role: row.role,
    customer_type: row.customer?.type || 'jemaat', // Ambil tipe lama jika ada
    password: '',
    password_confirmation: ''
  });
  isDialogOpen.value = true;
};

const submitForm = async () => {
  if (!form.name || !form.email) return ElMessage.warning('Lengkapi data wajib');
  loadingSubmit.value = true;
  try {
    const method = isEditing.value ? 'PUT' : 'POST';
    const url = isEditing.value ? `/user-management/${editingId.value}` : '/user-management';
    await apiFetch(url, { method, body: form });
    ElMessage.success('Selesai!');
    isDialogOpen.value = false;
    loadUsers();
  } catch (e) { ElMessage.error('Gagal menyimpan.'); }
  finally { loadingSubmit.value = false; }
};

const handleApprove = async (customerId: string) => {
  try {
    await apiFetch(`/customers/${customerId}/approve`, { method: 'PATCH' });
    ElMessage.success('Disetujui!');
    loadUsers();
  } catch (e) { ElMessage.error('Gagal'); }
};

const openRejectModal = (id: string) => {
    currentCustomerId.value = id;
    rejectionReason.value = '';
    rejectModalVisible.value = true;
};

const confirmReject = async () => {
    if(!rejectionReason.value) return ElMessage.warning('Alasan wajib diisi');
    try {
        await apiFetch(`/customers/${currentCustomerId.value}/reject`, { method: 'PATCH', body: { reason: rejectionReason.value } });
        ElMessage.success('Ditolak');
        rejectModalVisible.value = false;
        loadUsers();
    } catch (e) { ElMessage.error('Gagal'); }
};

const handleToggleStatus = async (id: string) => {
    try {
        await apiFetch(`/customers/${id}/toggle-status`, { method: 'PATCH' });
        loadUsers();
    } catch (e) { ElMessage.error('Gagal'); }
};

const confirmDelete = async (row: any) => {
  try {
    await ElMessageBox.confirm(`Hapus ${row.name}?`, 'Hapus', { type: 'warning' });
    await apiFetch(`/user-management/${row.id}`, { method: 'DELETE' });
    loadUsers();
  } catch (e) {}
};

let searchTimer: any = null;
watch(searchQuery, () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => { loadUsers(); }, 500);
});

onMounted(loadUsers);
</script>