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
            @clear="loadCategories"
          >
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          <el-button type="primary" color="#00A9C3" @click="openCreate">+ Tambah Kategori</el-button>
        </div>
      </div>

      <!-- TABEL DAFTAR KATEGORI -->
      <div class="mt-6 overflow-x-auto">
        <el-table :data="categories" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden text-xs">
          <!-- NAMA KATEGORI -->
          <el-table-column prop="name" label="Nama Kategori" min-width="180">
            <template #default="{ row }">
              <span class="text-[#1B293C] uppercase tracking-wide">{{ row.name }}</span>
            </template>
          </el-table-column>

          <!-- DESKRIPSI (DENGAN TOOLTIP OTOMATIS) -->
          <el-table-column prop="description" label="Deskripsi" min-width="260" show-overflow-tooltip>
            <template #default="{ row }">
              <span class="text-slate-600">{{ row.description || '-' }}</span>
            </template>
          </el-table-column>
          
          <!-- KOLOM KOLEKSI (KLIK-ABLE KE KATALOG) -->
          <el-table-column label="Koleksi" width="130" align="center">
            <template #default="{ row }">
              <el-tooltip content="Lihat daftar buku kategori ini" placement="top">
                <el-button 
                  type="primary" 
                  link 
                  class="font-bold !text-xs"
                  @click="$router.push({ path: '/admin/books', query: { category_id: row.id } })"
                >
                  <span :class="row.books_count > 0 ? 'text-[#00A9C3]' : 'text-slate-400'">
                    {{ row.books_count }} Judul
                  </span>
                  <Icon icon="solar:alt-arrow-right-linear" class="ml-1" />
                </el-button>
              </el-tooltip>
            </template>
          </el-table-column>

          <!-- AKSI (EDIT & HAPUS BERPROTEKSI) -->
          <el-table-column label="Aksi" width="150" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-1.5">
                <el-button size="small" @click="openEdit(row)">Edit</el-button>
                
                <!-- Tooltip Proteksi jika kategori masih ada bukunya -->
                <el-tooltip 
                  v-if="row.books_count > 0" 
                  :content="`Tidak dapat dihapus karena masih memuat ${row.books_count} buku aktif`" 
                  placement="top"
                >
                  <span>
                    <el-button size="small" type="danger" plain disabled>Hapus</el-button>
                  </span>
                </el-tooltip>

                <!-- Tombol Hapus aktif jika koleksi 0 buku -->
                <el-button 
                  v-else 
                  size="small" 
                  type="danger" 
                  plain 
                  @click="handleDelete(row)"
                >
                  Hapus
                </el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- FORM DIALOG -->
    <el-dialog 
      v-model="isDialogOpen" 
      :title="isEditing ? 'Edit Kategori' : 'Tambah Kategori Baru'" 
      width="450px" 
      append-to-body
      align-center
    >
      <el-form label-position="top">
        <el-form-item label="Nama Kategori" required>
          <el-input 
            v-model="form.name" 
            placeholder="Misal: Sekolah Sabat" 
            @keyup.enter="submitForm" 
          />
        </el-form-item>
        <el-form-item label="Deskripsi">
          <el-input 
            v-model="form.description" 
            type="textarea" 
            :rows="3" 
            placeholder="Penjelasan singkat mengenai kategori ini..." 
          />
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
  } finally { 
    loading.value = false; 
  }
};

const openCreate = () => {
  isEditing.value = false;
  editingId.value = null;
  form.name = ''; 
  form.description = '';
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
  if (!form.name.trim()) return ElMessage.warning('Nama kategori wajib diisi.');
  
  loadingSubmit.value = true;
  try {
    const method = isEditing.value ? 'PUT' : 'POST';
    const url = isEditing.value ? `/categories/${editingId.value}` : '/categories';
    await apiFetch(url, { method, body: form });
    
    ElMessage.success(isEditing.value ? 'Kategori berhasil diperbarui!' : 'Kategori baru berhasil ditambahkan!');
    isDialogOpen.value = false;
    loadCategories();
  } catch (e: any) {
    const msg = e.data?.message || 'Gagal menyimpan kategori';
    ElMessage.error(msg);
  } finally { 
    loadingSubmit.value = false; 
  }
};

const handleDelete = async (row: any) => {
  // Pengaman lapis pertama di frontend
  if (row.books_count > 0) {
    return ElMessageBox.alert(
      `Kategori "${row.name}" tidak dapat dihapus karena masih memuat ${row.books_count} judul buku aktif. Pindahkan buku terlebih dahulu ke kategori lain.`,
      'Perlindungan Data',
      { type: 'warning', confirmButtonText: 'Saya Mengerti' }
    );
  }

  try {
    await ElMessageBox.confirm(`Hapus kategori "${row.name}"?`, 'Konfirmasi Hapus', { 
      type: 'warning',
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal'
    });
    
    await apiFetch(`/categories/${row.id}`, { method: 'DELETE' });
    ElMessage.success('Kategori berhasil dihapus');
    loadCategories();
  } catch (e: any) {
    if (e !== 'cancel') {
      const msg = e.data?.message || 'Terjadi kesalahan saat menghapus data';
      ElNotification({ title: 'Gagal Hapus', message: msg, type: 'error' });
    }
  }
};

watch(searchQuery, (v) => { 
  if (v === '') loadCategories(); 
});

onMounted(loadCategories);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { 
  @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; 
}
</style>