<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Berita & Literasi</h2>
          <p class="text-sm text-slate-500">Kelola informasi buku terbaru dan pengumuman untuk jemaat.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Cari berita..." clearable class="w-full sm:w-64">
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          <el-button type="primary" color="#00A9C3" @click="openCreate">Tambah Tulisan</el-button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto">
        <el-table :data="articles" v-loading="loading" stripe class="w-full">
          <el-table-column prop="title" label="Judul Berita" min-width="250" />
          <el-table-column prop="author.name" label="Penulis" width="150" />
          <el-table-column label="Aksi" width="150" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-2">
                <el-button size="small" type="primary" plain @click="openEdit(row)">Edit</el-button>
                <el-button size="small" type="danger" plain @click="confirmDelete(row)">Hapus</el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>
  </AdminShell>
</template>

<script lang="ts" setup>
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';
import { ElMessage, ElMessageBox } from 'element-plus';
import { Icon } from '@iconify/vue';

const { apiFetch, unwrap } = useApi();
const articles = ref([]);
const loading = ref(false);
const searchQuery = ref('');

const loadArticles = async () => {
  loading.value = true;
  try {
    const res = await apiFetch(`/articles?search=${searchQuery.value}`);
    articles.value = unwrap(res);
  } catch (e) { ElMessage.error('Gagal memuat berita'); }
  finally { loading.value = false; }
};

const openCreate = () => { useRouter().push('/admin/articles/create'); };
const openEdit = (row: any) => { useRouter().push(`/admin/articles/${row.id}`); };

onMounted(loadArticles);
</script>