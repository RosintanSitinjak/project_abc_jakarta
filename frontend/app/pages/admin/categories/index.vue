<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- HEADER -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Kategori Buku</h2>
          <p class="text-sm text-slate-500">Kelola pengelompokan literatur ABC Jakarta.</p>
        </div>
        <div class="flex items-center gap-3">
          <el-input 
            v-model="searchQuery" 
            placeholder="Cari kategori..." 
            clearable 
            class="w-full sm:w-64"
            @keyup.enter="loadCategories"
          >
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          <el-button type="primary" color="#00A9C3" @click="openCreate">+ Tambah Kategori</el-button>
        </div>
      </div>

      <!-- TABEL DAFTAR KATEGORI -->
      <div class="mt-6 overflow-x-auto">
        <el-table :data="categories" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
          <el-table-column prop="name" label="Nama Kategori" min-width="180" />
          <el-table-column prop="description" label="Deskripsi" min-width="250" show-overflow-tooltip />
          
          <!-- KOLOM KOLEKSI (KLIK-ABLE) -->
          <el-table-column label="Koleksi" width="120" align="center">
            <template #default="{ row }">
              <el-button 
                type="primary" 
                link 
                class="font-bold"
                @click="$router.push({ path: '/admin/books', query: { category_id: row.id } })"
              >
                {{ row.books_count }} Judul
                <Icon icon="solar:alt-arrow-right-linear" class="ml-1" />
              </el-button>
            </template>
          </el-table-column>

          <el-table-column label="Aksi" width="150" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-2">
                <el-button size="small" @click="openEdit(row)">Edit</el-button>
                <el-button size="small" type="danger" plain @click="handleDelete(row)">Hapus</el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- FORM DIALOG -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit Kategori' : 'Tambah Kategori Baru'" width="450px" append-to-body>
      <el-form label-position="top">
        <el-form-item label="Nama Kategori" required>
          <el-input v-model="form.name" placeholder="Misal: Sekolah Sabat" @keyup.enter="submitForm" />
        </el-form-item>
        <el-form-item label="Deskripsi">
          <el-input v-model="form.description" type="textarea" :rows="3" placeholder="Penjelasan singkat..." />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="isDialogOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" @click="submitForm" :loading="loadingSubmit">Simpan</el-button>
      </template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { ElMessage, ElMessageBox, ElNotification } from 'element-plus';
import { onMounted, reactive, ref, watch } from 'vue';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const { apiFetch, unwrap } = useApi();

const categories = ref([]);
const loading = ref(false);
const loadingSubmit = ref(false);
const searchQuery = ref('');

const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const form = reactive({ name: '', description: '' });

const loadCategories = async () => {
  loading.value = true;
  try {
    const res = await apiFetch(`/categories?search=${searchQuery.value}`);
    categories.value = unwrap(res);
  } finally { loading.value = false; }
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
  if (!form.name) return ElMessage.warning('Nama kategori wajib diisi');
  loadingSubmit.value = true;
  try {
    const method = isEditing.value ? 'PUT' : 'POST';
    const url = isEditing.value ? `/categories/${editingId.value}` : '/categories';
    await apiFetch(url, { method, body: form });
    ElMessage.success('Kategori berhasil diperbarui');
    isDialogOpen.value = false;
    loadCategories();
  } catch (e) {
    ElMessage.error('Gagal menyimpan data');
  } finally { loadingSubmit.value = false; }
};

const handleDelete = async (row: any) => {
  try {
    await ElMessageBox.confirm(`Hapus kategori ${row.name}?`, 'Peringatan', { type: 'warning' });
    await apiFetch(`/categories/${row.id}`, { method: 'DELETE' });
    ElMessage.success('Berhasil dihapus');
    loadCategories();
  } catch (e: any) {
    // Menangkap pesan error "Gagal menghapus karena ada buku" dari backend
    const msg = e.data?.message || 'Terjadi kesalahan';
    ElNotification({ title: 'Gagal Hapus', message: msg, type: 'error' });
  }
};

watch(searchQuery, (v) => { if (v === '') loadCategories(); });
onMounted(loadCategories);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; }
</style>