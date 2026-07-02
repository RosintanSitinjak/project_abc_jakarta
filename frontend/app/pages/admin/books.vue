<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="text-lg font-bold text-[#1B293C]">Manajemen Stok Buku</h2>
          <p class="text-sm text-slate-500">Kelola katalog buku, harga, dan peringatan stok otomatis (ROP).</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Cari judul atau penulis..." clearable class="w-full sm:w-64">
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          <el-button type="primary" color="#00A9C3" @click="openCreate">Tambah Buku</el-button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto">
        <el-table v-loading="loading" :data="books" stripe class="w-full">
          <el-table-column prop="title" label="Judul Buku" min-width="200" show-overflow-tooltip />
          <el-table-column prop="author" label="Penulis" min-width="150" />
          <el-table-column label="Stok" width="100" align="center">
            <template #default="{ row }">
              <el-tag :type="row.stock <= row.rop_point ? 'danger' : 'success'">
                {{ row.stock }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Harga (Rp)" width="150">
            <template #default="{ row }">
              {{ new Intl.NumberFormat('id-ID').format(row.price) }}
            </template>
          </el-table-column>
          <el-table-column label="Aksi" width="150" align="center">
            <template #default="{ row }">
              <el-button text type="primary" @click="openEdit(row)"><Icon icon="solar:pen-2-outline" /></el-button>
              <el-button text type="danger" @click="confirmDelete(row)"><Icon icon="solar:trash-bin-trash-outline" /></el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- Dialog Form Buku -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit Buku' : 'Tambah Buku Baru'" width="700px" align-center>
      <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
        <div class="grid gap-4 md:grid-cols-2">
          <el-form-item label="Judul Buku" prop="title">
            <el-input v-model="form.title" />
          </el-form-item>
          <el-form-item label="Kategori" prop="category_id">
            <el-select v-model="form.category_id" class="w-full">
              <el-option v-for="cat in categories" :key="cat.id" :label="cat.name" :value="cat.id" />
            </el-select>
          </el-form-item>
          <el-form-item label="Penulis">
            <el-input v-model="form.author" />
          </el-form-item>
          <el-form-item label="ISBN">
            <el-input v-model="form.isbn" />
          </el-form-item>
          <el-form-item label="Harga Umum (Rp)" prop="price">
            <el-input-number v-model="form.price" :min="0" class="!w-full" />
          </el-form-item>
          <el-form-item label="Harga Penginjil (Rp)">
            <el-input-number v-model="form.member_price" :min="0" class="!w-full" />
          </el-form-item>
          <el-form-item label="Stok Saat Ini" prop="stock">
            <el-input-number v-model="form.stock" :min="0" class="!w-full" />
          </el-form-item>
          <el-form-item label="Titik Pesan Ulang (ROP Point)" prop="rop_point">
            <el-input-number v-model="form.rop_point" :min="0" class="!w-full" />
          </el-form-item>
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

const { apiFetch } = useApi();
const books = ref([]);
const categories = ref([]);
const loading = ref(false);
const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const searchQuery = ref('');

const form = reactive({
  title: '', category_id: null, author: '', isbn: '',
  price: 0, member_price: 0, stock: 0, rop_point: 10
});

const rules = {
  title: [{ required: true, message: 'Judul wajib diisi', trigger: 'blur' }],
  category_id: [{ required: true, message: 'Pilih kategori', trigger: 'change' }],
  price: [{ required: true, message: 'Harga wajib diisi', trigger: 'blur' }],
  stock: [{ required: true, message: 'Stok wajib diisi', trigger: 'blur' }],
};

const loadData = async () => {
  loading.value = true;
  try {
    const [booksRes, catsRes] = await Promise.all([
      apiFetch(`/books?search=${searchQuery.value}`),
      apiFetch('/categories')
    ]);
    books.value = Array.isArray(booksRes) ? booksRes : booksRes.data;
    categories.value = Array.isArray(catsRes) ? catsRes : catsRes.data;
  } catch (e) { ElMessage.error('Gagal memuat data'); }
  finally { loading.value = false; }
};

const openCreate = () => {
  isEditing.value = false;
  Object.assign(form, { title: '', category_id: null, author: '', isbn: '', price: 0, member_price: 0, stock: 0, rop_point: 10 });
  isDialogOpen.value = true;
};

const openEdit = (row) => {
  isEditing.value = true;
  editingId.value = row.id;
  Object.assign(form, { ...row });
  isDialogOpen.value = true;
};

const submitForm = async () => {
  try {
    const url = isEditing.value ? `/books/${editingId.value}` : '/books';
    await apiFetch(url, { method: isEditing.value ? 'PUT' : 'POST', body: form });
    ElMessage.success('Data buku berhasil disimpan');
    isDialogOpen.value = false;
    loadData();
  } catch (e) { ElMessage.error('Gagal menyimpan data'); }
};

const confirmDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`Hapus buku ${row.title}?`, 'Peringatan', { type: 'warning' });
    await apiFetch(`/books/${row.id}`, { method: 'DELETE' });
    ElMessage.success('Buku dihapus');
    loadData();
  } catch (e) {}
};

watch(searchQuery, () => loadData());
onMounted(loadData);
</script>