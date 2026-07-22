<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- HEADER & FILTER -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Manajemen Pengguna</h2>
          <p class="text-sm text-slate-500">Kontrol akses staff dan verifikasi akun pelanggan.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Cari nama/email..." clearable class="w-full sm:w-64">
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>

          <el-select v-model="filterType" placeholder="Tipe" clearable @change="loadUsers" class="w-32">
            <el-option label="Jemaat" value="jemaat" />
            <el-option label="Penginjil" value="penginjil" />
            <el-option label="Gereja" value="gereja" />
          </el-select>

          <el-button type="primary" color="#00A9C3" @click="openCreate">+ Tambah User</el-button>
        </div>
      </div>

      <!-- TABEL DATA -->
      <div class="overflow-x-auto">
        <el-table :data="users" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
          <el-table-column prop="name" label="Nama & Hak Akses" min-width="180">
             <template #default="{ row }">
                <div class="flex flex-col">
                  <span :class="{'line-through text-slate-400': row.customer?.status === 'suspended'}" class="font-bold text-[#1B293C]">{{ row.name }}</span>
                  <div class="flex items-center gap-1 mt-1">
                    <el-tag v-if="row.role === 1" size="small" color="#1B293C" class="!text-white border-0 text-[8px] font-black">PIMPINAN</el-tag>
                    <el-tag v-else-if="row.role === 2" size="small" type="primary" class="text-[8px] font-black">STAFF ADMIN</el-tag>
                    <el-tag v-else size="small" type="info" plain class="text-[8px] font-black">PELANGGAN</el-tag>
                  </div>
                </div>
             </template>
          </el-table-column>

          <el-table-column prop="email" label="Email Login" min-width="200" />
          
          <el-table-column label="WhatsApp" width="140">
             <template #default="{ row }">
                <span v-if="row.customer?.phone" class="text-xs font-mono text-slate-600">{{ row.customer.phone }}</span>
                <span v-else class="text-slate-300 italic text-[10px]">No Phone</span>
             </template>
          </el-table-column>

          <el-table-column label="Status Akun" width="110" align="center">
            <template #default="{ row }">
              <template v-if="row.customer">
                <el-tag v-if="row.customer.status === 'approved'" size="small" type="success">AKTIF</el-tag>
                <el-tag v-else-if="row.customer.status === 'pending'" size="small" type="warning">PENDING</el-tag>
                <el-tag v-else-if="row.customer.status === 'suspended'" size="small" type="info">BLOKIR</el-tag>
                <el-tag v-else size="small" type="danger">DITOLAK</el-tag>
              </template>
              <span v-else class="text-slate-300">-</span>
            </template>
          </el-table-column>

          <el-table-column label="Aksi" width="220" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-1">
                <!-- FITUR WA HELPER: Kirim Kredensial -->
                <el-tooltip v-if="row.role === 3 && row.customer?.phone" content="Kirim Detail Login ke WA" placement="top">
                  <el-button circle size="small" type="success" @click="sendWhatsAppCredentials(row)">
                    <Icon icon="solar:letter-bold" />
                  </el-button>
                </el-tooltip>

                <!-- KHUSUS PL PENDING -->
                <template v-if="row.customer?.type === 'penginjil' && row.customer?.status === 'pending'">
                  <el-button size="small" type="success" @click="handleApprove(row.customer.id)">Setujui PL</el-button>
                  <el-button size="small" type="danger" plain @click="openRejectModal(row.customer.id)">Tolak</el-button>
                </template>

                <!-- AKSI UMUM -->
                <template v-else>
                  <el-button circle size="small" type="primary" plain @click="openEdit(row)"><Icon icon="solar:pen-2-bold" /></el-button>
                  <el-button v-if="row.customer" circle size="small" :type="row.customer.status === 'suspended' ? 'success' : 'warning'" plain @click="handleToggleStatus(row.customer.id)">
                    <Icon :icon="row.customer.status === 'suspended' ? 'solar:play-bold' : 'solar:stop-bold'" />
                  </el-button>
                  <el-button v-if="row.id !== authUser?.id && row.role !== 1" circle size="small" type="danger" plain @click="confirmDelete(row)"><Icon icon="solar:trash-bin-trash-bold" /></el-button>
                </template>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- MODAL: TAMBAH & EDIT USER (INCLUDE NO HP) -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit Pengguna' : 'Tambah Pengguna Baru'" width="500px">
      <el-form label-position="top">
        <el-form-item label="Nama Lengkap" required>
          <el-input v-model="form.name" placeholder="Masukkan nama lengkap..." />
        </el-form-item>
        
        <el-form-item label="Email Alamat" required>
          <el-input v-model="form.email" :disabled="isEditing" placeholder="user@abc.com" />
        </el-form-item>

        <div class="grid grid-cols-2 gap-4">
          <el-form-item label="Hak Akses / Role">
            <el-select v-model="form.role" class="w-full">
              <el-option label="Pimpinan ABC" :value="1" />
              <el-option label="Admin Staff" :value="2" />
              <el-option label="Pelanggan" :value="3" />
            </el-select>
          </el-form-item>
          <!-- FIELD WHATSAPP BARU -->
          <el-form-item label="No. WhatsApp">
             <el-input v-model="form.phone" placeholder="628xxx" />
          </el-form-item>
        </div>

        <el-form-item v-if="form.role === 3" label="Kategori Pelanggan" required>
          <el-select v-model="form.customer_type" class="w-full">
             <el-option label="Jemaat Umum" value="jemaat" />
             <el-option label="Penginjil Literatur (PL)" value="penginjil" />
             <el-option label="Gereja / Institusi" value="gereja" />
          </el-select>
        </el-form-item>

        <div class="grid grid-cols-2 gap-4">
          <el-form-item :label="isEditing ? 'Password Baru' : 'Password'">
            <el-input v-model="form.password" type="password" show-password @keyup.enter="submitForm" />
          </el-form-item>
          <el-form-item label="Konfirmasi Password">
            <el-input v-model="form.password_confirmation" type="password" show-password @keyup.enter="submitForm" />
          </el-form-item>
        </div>
      </el-form>
      <template #footer>
        <el-button @click="isDialogOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" :loading="loadingSubmit" @click="submitForm">Simpan User</el-button>
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
  name: '', email: '', role: 3, 
  customer_type: 'jemaat', phone: '', // Ditambahkan
  password: '', password_confirmation: ''
});

const loadUsers = async () => {
  loading.value = true;
  try {
    const res = await apiFetch(`/user-management?search=${searchQuery.value}&type=${filterType.value}&status=${filterStatus.value}`);
    users.value = res.data || res;
  } finally { loading.value = false; }
};

const openCreate = () => {
  isEditing.value = false;
  Object.assign(form, { name: '', email: '', role: 3, customer_type: 'jemaat', phone: '', password: '', password_confirmation: '' });
  isDialogOpen.value = true;
};

const openEdit = (row: any) => {
  isEditing.value = true;
  editingId.value = row.id;
  Object.assign(form, {
    name: row.name,
    email: row.email,
    role: row.role,
    customer_type: row.customer?.type || 'jemaat',
    phone: row.customer?.phone || '',
    password: '',
    password_confirmation: ''
  });
  isDialogOpen.value = true;
};

const submitForm = async () => {
  loadingSubmit.value = true;
  try {
    const method = isEditing.value ? 'PUT' : 'POST';
    await apiFetch(isEditing.value ? `/user-management/${editingId.value}` : '/user-management', { method, body: form });
    ElMessage.success('Selesai!');
    isDialogOpen.value = false; loadUsers();
  } catch (e) { ElMessage.error('Gagal.'); }
  finally { loadingSubmit.value = false; }
};

const sendWhatsAppCredentials = (row: any) => {
    const pass = "Sesuai saat pendaftaran"; // Password tidak bisa ditarik balik (hashed), jadi kita kasih instruksi
    const message = `Syalom ${row.name},\n\nAkun Balai Buku Advent Jakarta Anda telah aktif.\nEmail: *${row.email}*\n\nSilakan login di website kami untuk memesan literatur rohani. Terima kasih! 🙏`;
    const url = `https://api.whatsapp.com/send?phone=${row.customer?.phone}&text=${encodeURIComponent(message)}`;
    window.open(url, '_blank');
};

const handleApprove = async (id: string) => { await apiFetch(`/customers/${id}/approve`, { method: 'PATCH' }); loadUsers(); };
const handleToggleStatus = async (id: string) => { await apiFetch(`/customers/${id}/toggle-status`, { method: 'PATCH' }); loadUsers(); };
const confirmDelete = async (row: any) => {
  await ElMessageBox.confirm(`Hapus ${row.name}?`, 'Peringatan');
  await apiFetch(`/user-management/${row.id}`, { method: 'DELETE' }); loadUsers();
};

watch([searchQuery, filterType, filterStatus], () => loadUsers());
onMounted(loadUsers);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; }
</style>