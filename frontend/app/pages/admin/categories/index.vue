<template>
  <AdminShell>
    <section class="glass-panel p-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-xl font-bold text-[#1B293C]">Kategori Buku</h2>
          <p class="text-sm text-slate-500">Kelola kategori literatur ABC Jakarta</p>
        </div>
        <el-button type="primary" color="#00A9C3" @click="openCreate">Tambah Kategori</el-button>
      </div>

      <el-table v-loading="loading" :data="categories" stripe>
        <el-table-column prop="name" label="Nama Kategori" />
        <el-table-column prop="description" label="Deskripsi" />
<el-table-column label="Aksi" width="180" align="center">
  <template #default="{ row }">
    <div class="flex justify-center gap-2">
      <el-button size="small" type="primary" plain @click="openEdit(row)">
        Edit
      </el-button>
      <!-- TAMBAHKAN TOMBOL INI -->
      <el-button size="small" type="danger" plain @click="confirmDelete(row)">
        Hapus
      </el-button>
    </div>
  </template>
</el-table-column>      </el-table>
    </section>

    <!-- Dialog -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit' : 'Tambah'" width="400px">
      <el-form label-position="top">
        <el-form-item label="Nama">
          <el-input v-model="form.name" />
        </el-form-item>
        <el-form-item label="Deskripsi">
          <el-input v-model="form.description" type="textarea" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="isDialogOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" @click="submitForm">Simpan</el-button>
      </template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';
import { ElMessage } from 'element-plus';

const { apiFetch } = useApi();
const categories = ref([]);
const loading = ref(false);
const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const form = reactive({ name: '', description: '' });

const loadCategories = async () => {
  loading.value = true;
  try {
    const res = await apiFetch('/categories');
    categories.value = Array.isArray(res) ? res : (res.data || []);
  } catch (e) {
    ElMessage.error('Gagal memuat data');
  } finally {
    loading.value = false;
  }
};

const openCreate = () => {
  isEditing.value = false;
  form.name = ''; form.description = '';
  isDialogOpen.value = true;
};

const openEdit = (row: any) => {
  isEditing.value = true;
  editingId.value = row.id;
  form.name = row.name;
  form.description = row.description;
  isDialogOpen.value = true;
};

const submitForm = async () => {
  try {
    const method = isEditing.value ? 'PUT' : 'POST';
    const url = isEditing.value ? `/categories/${editingId.value}` : '/categories';
    await apiFetch(url, { method, body: form });
    ElMessage.success('Berhasil disimpan');
    isDialogOpen.value = false;
    loadCategories();
  } catch (e) {
    ElMessage.error('Gagal menyimpan');
  }
};

const confirmDelete = async (row: any) => {
  try {
    // Memunculkan kotak konfirmasi agar tidak asal hapus
    await ElMessageBox.confirm(
      `Apakah Anda yakin ingin menghapus kategori "${row.name}"?`,
      'Peringatan',
      {
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        type: 'warning',
      }
    );

    // Jika user klik Ya, maka jalankan perintah hapus ke Backend
    await apiFetch(`/categories/${row.id}`, { method: 'DELETE' });
    
    ElMessage.success('Kategori berhasil dihapus');
    loadCategories(); // Refresh tabel
  } catch (error) {
    // Jika user klik Batal, tidak terjadi apa-apa
  }
};

onMounted(loadCategories);
</script>