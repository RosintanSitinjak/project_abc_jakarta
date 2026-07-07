<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- HEADER -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Berita & Literasi</h2>
          <p class="text-sm text-slate-500">Kelola informasi buku terbaru dan pengumuman.</p>
        </div>
        <div class="flex items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Cari judul..." clearable class="w-full sm:w-64">
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          <!-- Tombol Tambah Berita -->
          <el-button type="primary" color="#00A9C3" @click="goToCreate">
            + Tambah Berita/Literasi
          </el-button>
        </div>
      </div>

      <!-- TABEL DAFTAR BERITA -->
      <div class="mt-6 overflow-x-auto">
        <el-table :data="articles" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
          <el-table-column prop="title" label="Judul Berita" min-width="250" />
          
          <el-table-column label="Penulis" width="150">
            <template #default="{ row }">
              {{ row.author?.name || 'Admin' }}
            </template>
          </el-table-column>

<el-table-column label="Gambar" width="120" align="center">
  <template #default="{ row }">
    <!-- GANTI INI: panggil row.thumbnail.url -->
    <el-image 
      v-if="row.thumbnail?.url" 
      :src="row.thumbnail.url" 
      class="h-10 w-16 rounded shadow-sm" 
      fit="cover" 
    />
    <span v-else class="text-[10px] text-slate-400 italic">No Image</span>
  </template>
</el-table-column>

          <el-table-column label="Aksi" width="120" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-1">
                <el-button circle size="small" type="primary" plain @click="goToEdit(row.id)">
                  <Icon icon="solar:pen-2-bold" />
                </el-button>
                <el-button circle size="small" type="danger" plain @click="handleDelete(row)">
                  <Icon icon="solar:trash-bin-trash-bold" />
                </el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { ElMessage, ElMessageBox } from 'element-plus';
import { onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router'; // Wajib import router
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

// Inisialisasi
const router = useRouter();
const { apiFetch, unwrap } = useApi();

// State
const articles = ref([]);
const loading = ref(false);
const searchQuery = ref('');

// Fungsi ambil data dari Backend
const loadArticles = async () => {
  loading.value = true;
  try {
    const res = await apiFetch(`/articles?search=${searchQuery.value}`);
    articles.value = unwrap(res);
  } catch (e) {
    ElMessage.error('Gagal mengambil data berita');
  } finally {
    loading.value = false;
  }
};

// Navigasi ke halaman buat (create.vue)
const goToCreate = () => {
  router.push('/admin/articles/create');
};

// Navigasi ke halaman edit ([id].vue)
const goToEdit = (id: string) => {
  router.push(`/admin/articles/${id}`);
};

// Fungsi Hapus
const handleDelete = async (row: any) => {
  try {
    await ElMessageBox.confirm(`Hapus berita "${row.title}"?`, 'Peringatan', {
      confirmButtonText: 'Hapus',
      cancelButtonText: 'Batal',
      type: 'warning'
    });
    
    await apiFetch(`/articles/${row.id}`, { method: 'DELETE' });
    ElMessage.success('Berita berhasil dihapus');
    loadArticles(); // Refresh tabel
  } catch (e) {
    // User cancel delete
  }
};

// Pantau kolom pencarian
watch(searchQuery, () => {
  loadArticles();
});

onMounted(loadArticles);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel {
  @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm;
}
</style>