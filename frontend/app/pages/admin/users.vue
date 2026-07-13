<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Manajemen Pengguna</h2>
          <p class="text-sm text-slate-500">Daftar staff admin dan verifikasi pendaftaran akun.</p>
        </div>
        <!-- Perbaikan: openCreate sekarang terhubung ke fungsi -->
        <el-button type="primary" color="#00A9C3" @click="openCreate">Tambah User</el-button>
      </div>

      <div class="overflow-x-auto">
        <el-table :data="users" v-loading="loading" stripe border class="w-full rounded-xl">
          <el-table-column prop="name" label="Nama" min-width="180" />
          <el-table-column prop="email" label="Email" min-width="200" />
          
          <el-table-column label="Tipe Akun" width="150">
            <template #default="{ row }">
              <el-tag v-if="row.customer" size="small" effect="plain">
                {{ row.customer.type.toUpperCase() }}
              </el-tag>
              <el-tag v-else size="small" type="success">INTERNAL</el-tag>
            </template>
          </el-table-column>

          <el-table-column label="Status" width="120" align="center">
            <template #default="{ row }">
              <el-tag v-if="row.customer" size="small" :type="row.customer.status === 'approved' ? 'success' : 'warning'">
                {{ row.customer.status === 'approved' ? 'AKTIF' : 'PENDING' }}
              </el-tag>
              <span v-else>-</span>
            </template>
          </el-table-column>

          <el-table-column label="Aksi" width="180" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-1">
                <el-button 
                  v-if="row.customer?.type === 'penginjil' && row.customer?.status === 'pending'"
                  size="small" type="success" @click="handleApprove(row.customer.id)"
                >Setujui PL</el-button>

                <!-- Tombol Edit sekarang sudah terhubung ke openEdit(row) -->
                <el-button v-else circle size="small" type="primary" plain @click="openEdit(row)">
                  <Icon icon="solar:pen-2-bold" />
                </el-button>
                
                <el-button v-if="row.id !== authUser?.id" circle size="small" type="danger" plain @click="confirmDelete(row)">
                  <Icon icon="solar:trash-bin-trash-bold" />
                </el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- FORM DIALOG: UNTUK TAMBAH & EDIT USER (INI YANG TADI HILANG) -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit Pengguna' : 'Tambah Pengguna Baru'" width="500px">
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

        <div class="grid grid-cols-2 gap-4">
          <el-form-item :label="isEditing ? 'Ganti Password' : 'Password'">
            <el-input v-model="form.password" type="password" show-password placeholder="Min 8 karakter" />
          </el-form-item>
          <el-form-item label="Konfirmasi Password">
            <el-input v-model="form.password_confirmation" type="password" show-password />
          </el-form-item>
        </div>
        <p v-if="isEditing" class="text-[10px] text-slate-400 italic">* Kosongkan password jika tidak ingin diganti.</p>
      </el-form>

      <template #footer>
        <el-button @click="isDialogOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" :loading="loadingSubmit" @click="submitForm">
          {{ isEditing ? 'Update Data' : 'Simpan User' }}
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
const users = ref([]);
const loading = ref(false);
const loadingSubmit = ref(false);
const authUser = useState<any>("auth-user", () => null);

// STATE UNTUK FORM (INI YANG TADI HILANG)
const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const form = reactive({
  name: '',
  email: '',
  role: 3,
  password: '',
  password_confirmation: ''
});

const loadUsers = async () => {
  loading.value = true;
  try {
    const res = await apiFetch('/user-management');
    users.value = res.data || res;
  } finally { loading.value = false; }
};

// FUNGSI UNTUK MEMBUKA FORM EDIT
const openEdit = (row: any) => {
  isEditing.value = true;
  editingId.value = row.id;
  // Isi form dengan data yang dipilih
  Object.assign(form, {
    name: row.name,
    email: row.email,
    role: row.role,
    password: '',
    password_confirmation: ''
  });
  isDialogOpen.value = true;
};

const openCreate = () => {
  isEditing.value = false;
  editingId.value = null;
  Object.assign(form, { name: '', email: '', role: 3, password: '', password_confirmation: '' });
  isDialogOpen.value = true;
};

// FUNGSI UNTUK SIMPAN (POST ATAU PUT)
const submitForm = async () => {
  if (!form.name || !form.email) return ElMessage.warning('Lengkapi data wajib');
  
  loadingSubmit.value = true;
  try {
    const method = isEditing.value ? 'PUT' : 'POST';
    const url = isEditing.value ? `/user-management/${editingId.value}` : '/user-management';
    
    await apiFetch(url, { method, body: form });
    ElMessage.success('Berhasil disimpan!');
    isDialogOpen.value = false;
    loadUsers();
  } catch (e) {
    ElMessage.error('Gagal menyimpan. Email mungkin sudah ada.');
  } finally {
    loadingSubmit.value = false;
  }
};

const handleApprove = async (customerId: string) => {
  try {
    await apiFetch(`/customers/${customerId}/approve`, { method: 'PATCH' });
    ElMessage.success('Verifikasi Berhasil!');
    loadUsers();
  } catch (e) { ElMessage.error('Gagal verifikasi'); }
};

const confirmDelete = async (row: any) => {
  try {
    await ElMessageBox.confirm(`Hapus user ${row.name}?`, 'Hapus', { type: 'warning' });
    await apiFetch(`/user-management/${row.id}`, { method: 'DELETE' });
    ElMessage.success('Berhasil dihapus');
    loadUsers();
  } catch (e) {}
};

onMounted(loadUsers);
</script>