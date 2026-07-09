<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- HEADER -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Katalog Buku</h2>
          <p class="text-sm text-slate-500">Kelola informasi literatur, stok, dan gambar sampul buku.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
          <el-input v-model="searchQuery" placeholder="Cari judul atau penulis..." clearable class="w-full sm:w-64">
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          <el-button type="primary" color="#00A9C3" @click="openCreate">
            + Tambah Buku
          </el-button>
        </div>
      </div>

      <!-- TABEL DAFTAR BUKU -->
      <div class="mt-6 overflow-x-auto">
        <el-table :data="books" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
          <el-table-column label="Gambar" width="100" align="center">
            <template #default="{ row }">
              <el-image 
                v-if="row.image_url" 
                :src="row.image_url" 
                class="h-12 w-8 rounded shadow-sm" 
                fit="cover" 
              />
              <Icon v-else icon="solar:book-2-linear" class="text-2xl text-slate-200" />
            </template>
          </el-table-column>
          
          <el-table-column prop="title" label="Judul Buku" min-width="200" />
          <el-table-column prop="author" label="Penulis" width="150" />
          
          <el-table-column label="Stok" width="100" align="center">
            <template #default="{ row }">
              <el-tag :type="row.stock <= row.rop_point ? 'danger' : 'success'" effect="dark">
                {{ row.stock }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column label="Harga (Rp)" width="150">
            <template #default="{ row }">
              Rp {{ new Intl.NumberFormat('id-ID').format(row.price) }}
            </template>
          </el-table-column>

          <el-table-column label="Aksi" width="120" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-1">
                <el-button circle size="small" type="primary" plain @click="openEdit(row)">
                  <Icon icon="solar:pen-2-bold" />
                </el-button>
                <el-button circle size="small" type="danger" plain @click="confirmDelete(row)">
                  <Icon icon="solar:trash-bin-trash-bold" />
                </el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- FORM DIALOG LENGKAP -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit Informasi Buku' : 'Tambah Buku Baru'" width="800px" top="5vh">
      <el-form :model="form" label-position="top">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- SISI KIRI: DATA TEKNIS -->
          <div class="space-y-4">
            <el-form-item label="Judul Buku" required>
              <el-input v-model="form.title" placeholder="Contoh: Sejarah Para Nabi" />
            </el-form-item>

            <el-form-item label="Kategori" required>
              <el-select v-model="form.category_id" placeholder="Pilih Kategori" class="w-full">
                <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
              </el-select>
            </el-form-item>

            <div class="grid grid-cols-2 gap-4">
              <el-form-item label="Penulis">
                <el-input v-model="form.author" />
              </el-form-item>
              <el-form-item label="ISBN">
                <el-input v-model="form.isbn" />
              </el-form-item>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <el-form-item label="Harga Umum (Rp)" required>
                <el-input-number v-model="form.price" :min="0" class="!w-full" />
              </el-form-item>
              <el-form-item label="Harga PL (Rp)">
                <el-input-number v-model="form.member_price" :min="0" class="!w-full" />
              </el-form-item>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <el-form-item label="Stok Gudang" required>
                <el-input-number v-model="form.stock" :min="0" class="!w-full" />
              </el-form-item>
              <el-form-item label="Batas ROP (Waspada)">
                <el-input-number v-model="form.rop_point" :min="0" class="!w-full" />
              </el-form-item>
            </div>
          </div>

          <!-- SISI KANAN: KONTEN & GAMBAR -->
          <div class="space-y-4">
            <el-form-item label="Gambar Sampul" required>
              <el-upload
                action="#"
                list-type="picture-card"
                :auto-upload="false"
                :limit="1"
                accept="image/*"
                v-model:file-list="imageFiles"
                :on-change="handleImageChange"
                :on-remove="handleImageRemove"
              >
                <Icon icon="solar:camera-add-linear" class="text-2xl" />
              </el-upload>
            </el-form-item>

            <el-form-item label="Sinopsis / Deskripsi Buku">
              <el-input v-model="form.description" type="textarea" :rows="8" placeholder="Tulis ringkasan isi buku di sini..." />
            </el-form-item>
          </div>
        </div>
      </el-form>

      <template #footer>
        <el-button @click="isDialogOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" :loading="loadingSave" @click="submitForm">
          Simpan ke Katalog
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

const { apiFetch, unwrap, uploadAttachment } = useApi();
const books = ref([]);
const categories = ref([]);
const loading = ref(false);
const loadingSave = ref(false);
const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const searchQuery = ref('');
const imageFiles = ref([]);

const form = reactive({
  title: '', category_id: null, author: '', isbn: '',
  price: 0, member_price: 0, stock: 0, rop_point: 10,
  description: '', thumbnail_id: null
});

const loadData = async () => {
  loading.value = true;
  try {
    const [bRes, cRes] = await Promise.all([
      apiFetch(`/books?search=${searchQuery.value}`),
      apiFetch('/categories')
    ]);
    books.value = bRes.data || bRes;
    categories.value = cRes.data || cRes;
  } finally { loading.value = false; }
};

const handleImageChange = async (file) => {
  if (!file.raw) return;
  try {
    const attachment = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Book', type: 'thumbnail' });
    form.thumbnail_id = attachment.id;
    ElMessage.success('Gambar terunggah');
  } catch (e) { ElMessage.error('Gagal unggah gambar'); }
};

const handleImageRemove = () => { form.thumbnail_id = null; imageFiles.value = []; };

const openCreate = () => {
  isEditing.value = false;
  Object.assign(form, { title: '', category_id: null, author: '', isbn: '', price: 0, member_price: 0, stock: 0, rop_point: 10, description: '', thumbnail_id: null });
  imageFiles.value = [];
  isDialogOpen.value = true;
};

const openEdit = (row) => {
  isEditing.value = true;
  editingId.value = row.id;
  Object.assign(form, { ...row });
  imageFiles.value = row.image_url ? [{ url: row.image_url }] : [];
  isDialogOpen.value = true;
};

const submitForm = async () => {
  if(!form.title || !form.category_id) return ElMessage.warning('Judul dan Kategori wajib diisi');
  loadingSave.value = true;
  try {
    const method = isEditing.value ? 'PUT' : 'POST';
    const url = isEditing.value ? `/books/${editingId.value}` : '/books';
    await apiFetch(url, { method, body: form });
    ElMessage.success('Katalog berhasil diperbarui');
    isDialogOpen.value = false;
    loadData();
  } catch (e) { ElMessage.error('Gagal menyimpan data'); }
  finally { loadingSave.value = false; }
};

const confirmDelete = async (row) => {
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