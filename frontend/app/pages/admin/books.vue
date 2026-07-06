<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Manajemen Stok Buku</h2>
          <p class="text-sm text-slate-500">Kelola katalog, harga, dan algoritma stok ROP.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
          <el-input v-model="searchQuery" placeholder="Cari buku..." clearable class="w-full sm:w-64">
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          <el-button type="primary" color="#00A9C3" @click="openCreate">Tambah Buku</el-button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto">
        <el-table :data="books" v-loading="loading" stripe class="w-full">
          <el-table-column prop="title" label="Judul Buku" min-width="200" />
          <el-table-column prop="author" label="Penulis" width="150" />
          
          <!-- LOGIKA VISUAL ROP: Merah jika stok <= rop_point -->
          <el-table-column label="Stok" width="120" align="center">
            <template #default="{ row }">
              <el-tooltip :content="row.stock <= row.rop_point ? 'Stok di bawah batas ROP!' : 'Stok Aman'" placement="top">
                <el-tag :type="row.stock <= row.rop_point ? 'danger' : 'success'" effect="dark">
                  {{ row.stock }}
                </el-tag>
              </el-tooltip>
            </template>
          </el-table-column>

          <el-table-column label="Harga (Rp)" width="150">
            <template #default="{ row }">
              {{ new Intl.NumberFormat('id-ID').format(row.price) }}
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

    <!-- Form Dialog Buku -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit Buku' : 'Tambah Buku'" width="650px">
      <el-form :model="form" label-position="top">
        <div class="grid grid-cols-2 gap-4">
          <el-form-item label="Judul Buku" required><el-input v-model="form.title" /></el-form-item>
          <el-form-item label="Kategori" required>
  <el-select v-model="form.category_id" placeholder="Pilih Kategori" class="w-full">
    <!-- Perhatikan: 'c in categories' dan ':key="c.id"' harus sama-sama pake 'c' -->
    <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
  </el-select>
</el-form-item>
          <el-form-item label="Penulis"><el-input v-model="form.author" /></el-form-item>
          <el-form-item label="ISBN"><el-input v-model="form.isbn" /></el-form-item>
          <el-form-item label="Harga Umum"><el-input-number v-model="form.price" :min="0" class="!w-full"/></el-form-item>
          <el-form-item label="Harga Penginjil (PL)"><el-input-number v-model="form.member_price" :min="0" class="!w-full"/></el-form-item>
          <el-form-item label="Stok Gudang"><el-input-number v-model="form.stock" :min="0" class="!w-full"/></el-form-item>
          <el-form-item label="Batas ROP (Notifikasi)"><el-input-number v-model="form.rop_point" :min="0" class="!w-full"/></el-form-item>
        </div>
      </el-form>
      <template #footer>
        <el-button @click="isDialogOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" @click="submitForm">Simpan Buku</el-button>
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
const books = ref([]);
const categories = ref([]);
const loading = ref(false);
const isDialogOpen = ref(false);
// const isgOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const searchQuery = ref('');

const form = reactive({ title: '', category_id: null, author: '', isbn: '', price: 0, member_price: 0, stock: 0, rop_point: 10 });

const loadData = async () => {
  loading.value = true;
  try {
    const [bRes, cRes] = await Promise.all([
      apiFetch(`/books?search=${searchQuery.value}`),
      apiFetch('/categories')
    ]);
    books.value = unwrap(bRes);
    categories.value = unwrap(cRes);
  } catch (e) { ElMessage.error('Gagal memuat data'); }
  finally { loading.value = false; }
};

const openCreate = () => {
  isEditing.value = false;
  Object.assign(form, { title: '', category_id: null, author: '', isbn: '', price: 0, member_price: 0, stock: 0, rop_point: 10 });
  isDialogOpen.value = true;
};

const openEdit = (row: any) => {
  isEditing.value = true;
  editingId.value = row.id;
  Object.assign(form, { ...row });
  isDialogOpen.value = true;
};

const submitForm = async () => {
  try {
    const method = isEditing.value ? 'PUT' : 'POST';
    const url = isEditing.value ? `/books/${editingId.value}` : '/books';
    await apiFetch(url, { method, body: form });
    ElMessage.success('Berhasil disimpan');
    isDialogOpen.value = false;
    loadData();
  } catch (e) { ElMessage.error('Gagal menyimpan data'); }
};

const confirmDelete = async (row: any) => {
  try {
    await ElMessageBox.confirm(`Hapus buku ${row.title}?`, 'Peringatan', { type: 'warning' });
    await apiFetch(`/books/${row.id}`, { method: 'DELETE' });
    ElMessage.success('Terhapus');
    loadData();
  } catch (e) {}
};

watch(searchQuery, () => loadData());
onMounted(loadData);
</script>