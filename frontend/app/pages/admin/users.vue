<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- HEADER & FILTER -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Manajemen Pengguna</h2>
          <p class="text-sm text-slate-500">Kontrol akses staff, atur kredensial, dan verifikasi akun pelanggan.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
          <el-input 
            v-model="searchQuery" 
            placeholder="Cari nama/email..." 
            clearable 
            class="w-full sm:w-64"
            @keyup.enter="loadUsers"
            @clear="loadUsers"
          >
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>

          <el-select 
            v-model="filterType" 
            placeholder="Semua Tipe" 
            clearable 
            @change="loadUsers" 
            class="w-36"
          >
            <el-option label="Jemaat" value="jemaat" />
            <el-option label="Penginjil (PL)" value="penginjil" />
            <el-option label="Gereja" value="gereja" />
            <el-option label="Sekolah" value="sekolah" />
          </el-select>

          <el-button type="primary" color="#00A9C3" @click="openCreate">+ Tambah User</el-button>
        </div>
      </div>

      <!-- TABEL DATA PENGGUNA -->
      <div class="overflow-x-auto">
        <el-table :data="users" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden text-xs">
          <!-- NAMA & HAK AKSES -->
          <el-table-column prop="name" label="Nama & Hak Akses" min-width="180">
             <template #default="{ row }">
                <div class="flex flex-col">
                  <span :class="{'line-through text-slate-400': row.customer?.status === 'suspended'}" class="font-bold text-[#1B293C] text-xs">
                    {{ row.name }}
                  </span>
                  <div class="flex items-center gap-1 mt-1">
                    <el-tag v-if="row.role === 1" size="small" color="#1B293C" class="!text-white border-0 text-[8px] font-black">PIMPINAN</el-tag>
                    <el-tag v-else-if="row.role === 2" size="small" type="primary" class="text-[8px] font-black">STAFF ADMIN</el-tag>
                    <el-tag v-else size="small" type="info" plain class="text-[8px] font-black">PELANGGAN</el-tag>
                  </div>
                </div>
             </template>
          </el-table-column>

          <!-- EMAIL LOGIN -->
          <el-table-column prop="email" label="Email Login" min-width="200">
            <template #default="{ row }">
              <span class="font-mono text-slate-700">{{ row.email }}</span>
            </template>
          </el-table-column>
          
          <!-- WHATSAPP -->
          <el-table-column label="WhatsApp" width="150">
             <template #default="{ row }">
                <span v-if="row.customer?.phone" class="font-mono text-slate-600 font-semibold">{{ row.customer.phone }}</span>
                <span v-else class="text-slate-300 italic text-[11px]">No Phone</span>
             </template>
          </el-table-column>

          <!-- STATUS AKUN -->
          <el-table-column label="Status Akun" width="110" align="center">
            <template #default="{ row }">
              <template v-if="row.customer">
                <el-tag v-if="row.customer.status === 'approved'" size="small" type="success" class="font-bold">AKTIF</el-tag>
                <el-tag v-else-if="row.customer.status === 'pending'" size="small" type="warning" class="font-bold animate-pulse">PENDING</el-tag>
                <el-tag v-else-if="row.customer.status === 'suspended'" size="small" type="danger" class="font-bold">DIBEKUKAN</el-tag>
                <el-tag v-else size="small" type="info" class="font-bold">DITOLAK</el-tag>
              </template>
              <span v-else class="text-slate-300">-</span>
            </template>
          </el-table-column>

          <!-- KOLOM AKSI LENGKAP -->
          <el-table-column label="Aksi" width="240" align="center">
            <template #default="{ row }">
              <div class="flex justify-center items-center gap-1.5">
                <!-- 1. KIRIM KREDENSIAL WA -->
                <el-tooltip v-if="row.role === 3 && row.customer?.phone" content="Kirim Detail Login via WA" placement="top">
                  <el-button circle size="small" type="success" @click="sendWhatsAppCredentials(row)">
                    <Icon icon="logos:whatsapp-icon" class="text-xs" />
                  </el-button>
                </el-tooltip>

                <!-- 2. FITUR BARU: KIRIM LINK RESET PASSWORD VIA EMAIL -->
                <el-tooltip v-if="row.email" content="Kirim Link Reset Password ke Email" placement="top">
                  <el-button 
                    circle 
                    size="small" 
                    type="info" 
                    plain 
                    :loading="loadingEmailReset === row.email"
                    @click="sendEmailResetLink(row.email)"
                  >
                    <Icon icon="solar:key-minimalistic-square-bold" class="text-slate-600" />
                  </el-button>
                </el-tooltip>

                <!-- 3. KHUSUS VERIFIKASI AKUN PL (STATUS PENDING) -->
                <template v-if="row.customer?.type === 'penginjil' && row.customer?.status === 'pending'">
                  <el-button size="small" type="success" @click="handleApprove(row.customer.id)">Setujui PL</el-button>
                  <el-button size="small" type="danger" plain @click="openRejectModal(row.customer.id)">Tolak</el-button>
                </template>

                <!-- 4. AKSI STANDAR PENGGUNA -->
                <template v-else>
                  <!-- Edit Pengguna -->
                  <el-tooltip content="Edit Data & Reset Password Manual" placement="top">
                    <el-button circle size="small" type="primary" plain @click="openEdit(row)">
                      <Icon icon="solar:pen-2-bold" />
                    </el-button>
                  </el-tooltip>

                  <!-- Suspend / Buka Blokir Akun -->
                  <el-tooltip v-if="row.customer" :content="row.customer.status === 'suspended' ? 'Buka Blokir (Aktifkan Akun)' : 'Bekukan / Blokir Akun'" placement="top">
                    <el-button circle size="small" :type="row.customer.status === 'suspended' ? 'success' : 'warning'" plain @click="handleToggleStatus(row.customer.id)">
                      <Icon :icon="row.customer.status === 'suspended' ? 'solar:play-bold' : 'solar:stop-bold'" />
                    </el-button>
                  </el-tooltip>

                  <!-- Hapus Pengguna (Dilindungi: Akun Sendiri & Pimpinan) -->
                  <el-tooltip v-if="row.id !== authUser?.id && row.role !== 1" content="Hapus Pengguna" placement="top">
                    <el-button circle size="small" type="danger" plain @click="confirmDelete(row)">
                      <Icon icon="solar:trash-bin-trash-bold" />
                    </el-button>
                  </el-tooltip>
                </template>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- MODAL: TAMBAH & EDIT USER -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit Pengguna' : 'Tambah Pengguna Baru'" width="500px" append-to-body align-center>
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
          
          <el-form-item label="No. WhatsApp">
             <el-input v-model="form.phone" placeholder="08xxxxxxxxxx" />
          </el-form-item>
        </div>

        <el-form-item v-if="form.role === 3" label="Kategori Pelanggan" required>
          <el-select v-model="form.customer_type" class="w-full">
             <el-option label="Jemaat Umum" value="jemaat" />
             <el-option label="Penginjil Literatur (PL)" value="penginjil" />
             <el-option label="Gereja / Institusi" value="gereja" />
             <el-option label="Sekolah" value="sekolah" />
          </el-select>
        </el-form-item>

        <div class="grid grid-cols-2 gap-4">
          <el-form-item :label="isEditing ? 'Password Baru (Opsional)' : 'Password'">
            <el-input v-model="form.password" type="password" show-password placeholder="Minimal 8 karakter" @keyup.enter="submitForm" />
          </el-form-item>
          <el-form-item label="Konfirmasi Password">
            <el-input v-model="form.password_confirmation" type="password" show-password placeholder="Ulangi password" @keyup.enter="submitForm" />
          </el-form-item>
        </div>
      </el-form>
      <template #footer>
        <el-button @click="isDialogOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" :loading="loadingSubmit" @click="submitForm" class="!rounded-xl px-8 font-bold">
          Simpan User
        </el-button>
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
const users = ref<any[]>([]);
const loading = ref(false);
const loadingSubmit = ref(false);
const loadingEmailReset = ref<string | null>(null);
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
  customer_type: 'jemaat', 
  phone: '', 
  password: '', 
  password_confirmation: ''
});

const loadUsers = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (searchQuery.value && searchQuery.value.trim()) params.append('search', searchQuery.value.trim());
    if (filterType.value) params.append('type', filterType.value);
    if (filterStatus.value) params.append('status', filterStatus.value);

    const queryStr = params.toString() ? `?${params.toString()}` : '';
    const res = await apiFetch(`/user-management${queryStr}`);
    users.value = res.data || res;
  } finally { 
    loading.value = false; 
  }
};

const openCreate = () => {
  isEditing.value = false;
  editingId.value = null;
  Object.assign(form, { 
    name: '', 
    email: '', 
    role: 3, 
    customer_type: 'jemaat', 
    phone: '', 
    password: '', 
    password_confirmation: '' 
  });
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
  if (!form.name.trim() || !form.email.trim()) {
    return ElMessage.warning('Nama lengkap dan email login wajib diisi.');
  }

  loadingSubmit.value = true;
  try {
    const method = isEditing.value ? 'PUT' : 'POST';
    await apiFetch(isEditing.value ? `/user-management/${editingId.value}` : '/user-management', { 
      method, 
      body: form 
    });
    ElMessage.success(isEditing.value ? 'Data pengguna diperbarui!' : 'Pengguna baru berhasil ditambahkan!');
    isDialogOpen.value = false; 
    loadUsers();
  } catch (e: any) { 
    ElMessage.error(e.data?.message || 'Gagal menyimpan data pengguna.'); 
  } finally { 
    loadingSubmit.value = false; 
  }
};

// KIRIM LINK RESET PASSWORD KE EMAIL VIA GMAIL SMTP
const sendEmailResetLink = async (email: string) => {
  try {
    await ElMessageBox.confirm(
      `Kirimkan link atur ulang kata sandi resmi ke alamat email "${email}"?`,
      'Konfirmasi Kirim Email Reset',
      { type: 'info', confirmButtonText: 'Kirim Email', cancelButtonText: 'Batal' }
    );

    loadingEmailReset.value = email;
    await apiFetch('/auth/forgot-password', {
      method: 'POST',
      body: { email }
    });

    ElMessage.success(`Link reset password berhasil dikirim ke ${email}!`);
  } catch (e: any) {
    if (e !== 'cancel') {
      ElMessage.error(e.data?.message || 'Gagal mengirim email reset.');
    }
  } finally {
    loadingEmailReset.value = null;
  }
};

// KIRIM KREDENSIAL LOGIN VIA WHATSAPP
const sendWhatsAppCredentials = (row: any) => {
  const phone = row.customer?.phone;
  if (!phone) return ElMessage.warning('Nomor WhatsApp tidak tersedia.');

  let cleanPhone = phone.replace(/[^0-9]/g, '');
  if (cleanPhone.startsWith('0')) {
    cleanPhone = '62' + cleanPhone.slice(1);
  }

  const message = 
    `Salam damai sejahtera Bpk/Ibu *${row.name}*,\n\n` +
    `Akun Anda di sistem portal *Adventist Book Center (ABC) Jakarta* telah aktif! Berikut rincian akses login Anda:\n\n` +
    `📧 *Email Login*: ${row.email}\n` +
    `🔑 *Password*: Gunakan password yang Anda daftarkan (atau default: *12345678* jika dibuatkan oleh Admin)\n` +
    `🌐 *Link Login*: http://localhost:3000/login\n\n` +
    `Silakan login untuk memesan buku rohani, Buku Pelajaran Sekolah Sabat, dan literatur Advent secara mandiri.\n\n` +
    `Terima kasih dan Tuhan memberkati! 🙏`;

  const url = `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(message)}`;
  window.open(url, '_blank');
};

// SETUJUI AKUN PENGINJIL LITERATUR
const handleApprove = async (id: string) => { 
  try {
    await apiFetch(`/customers/${id}/approve`, { method: 'PATCH' }); 
    ElMessage.success('Akun Penginjil Literatur berhasil disetujui (AKTIF)!');
    loadUsers(); 
  } catch (e: any) {
    ElMessage.error(e.data?.message || 'Gagal menyetujui akun.');
  }
};

// TOLAK AKUN PENGINJIL LITERATUR DENGAN ALASAN
const openRejectModal = async (id: string) => {
  try {
    const { value: reason } = await ElMessageBox.prompt('Masukkan alasan penolakan verifikasi akun:', 'Tolak Akun Penginjil Literatur', {
      confirmButtonText: 'Tolak Akun',
      cancelButtonText: 'Batal',
      inputPlaceholder: 'Misal: Data keanggotaan PL tidak terverifikasi di Konferens',
      type: 'warning'
    });

    await apiFetch(`/customers/${id}/reject`, { 
      method: 'PATCH', 
      body: { reason: reason || 'Data keanggotaan tidak terverifikasi.' } 
    });
    
    ElMessage.success('Akun telah ditolak.');
    loadUsers();
  } catch (e: any) {
    if (e !== 'cancel') {
      ElMessage.error(e.data?.message || 'Gagal memproses penolakan.');
    }
  }
};

// TOGGLE SUSPEND / AKTIFKAN AKUN (KINI SUDAH TERHUBUNG KE BACKEND)
const handleToggleStatus = async (id: string) => { 
  try {
    const res: any = await apiFetch(`/customers/${id}/toggle-status`, { method: 'PATCH' }); 
    ElMessage.success(res.message || 'Status akun berhasil diperbarui.');
    loadUsers(); 
  } catch (e: any) {
    ElMessage.error(e.data?.message || 'Gagal mengubah status akun.');
  }
};

// HAPUS PENGGUNA BERPROTEKSI
const confirmDelete = async (row: any) => {
  try {
    await ElMessageBox.confirm(`Hapus pengguna "${row.name}"? Akses login dan profil terkait akan dihapus.`, 'Konfirmasi Hapus', {
      type: 'warning',
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal'
    });
    
    await apiFetch(`/user-management/${row.id}`, { method: 'DELETE' }); 
    ElMessage.success('Pengguna berhasil dihapus.');
    loadUsers(); 
  } catch (e: any) {
    if (e !== 'cancel') {
      ElMessage.error(e.data?.message || 'Gagal menghapus pengguna.');
    }
  }
};

watch([filterType, filterStatus], () => loadUsers());
watch(searchQuery, (newVal) => {
  if (!newVal || newVal.trim() === '') loadUsers();
});

onMounted(loadUsers);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { 
  @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; 
}
</style>