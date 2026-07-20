<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- HEADER DENGAN FILTER & SEARCH -->
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Katalog Buku</h2>
          <p class="text-sm text-slate-500">Kelola informasi literatur, stok, dan gambar sampul buku.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <!-- DROPDOWN FILTER KATEGORI -->
          <el-select v-model="filterCategory" placeholder="Semua Kategori" clearable class="w-44" @change="loadData">
            <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
          </el-select>

          <!-- SEARCH BAR (ENTER TO SEARCH) -->
          <el-input 
            v-model="searchQuery" 
            placeholder="Cari judul, penulis, atau ISBN..." 
            clearable 
            class="w-full sm:w-64"
            @keyup.enter="loadData"
            @clear="loadData"
          >
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          
          <!-- TOMBOL CETAK (Placeholder fitur Export) -->
          <el-button type="warning" plain @click="handleExport">
            <Icon icon="solar:printer-minimalistic-bold" class="mr-1" /> Cetak Stok
          </el-button>

          <el-button type="primary" color="#00A9C3" @click="openCreate">
            + Tambah Buku
          </el-button>
        </div>
      </div>

      <!-- TABEL DAFTAR BUKU -->
      <div class="mt-6 overflow-x-auto">
        <el-table :data="books" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
          <el-table-column label="Gambar" width="90" align="center">
            <template #default="{ row }">
              <el-image 
                v-if="row.image_url" 
                :src="row.image_url" 
                class="h-12 w-8 rounded shadow-sm border border-slate-100" 
                fit="cover" 
              />
              <Icon v-else icon="solar:book-2-linear" class="text-2xl text-slate-200" />
            </template>
          </el-table-column>
          
          <el-table-column prop="title" label="Judul Buku" min-width="220" show-overflow-tooltip>
            <template #default="{ row }">
              <div class="flex flex-col">
                <span class="font-bold text-[#1B293C] leading-tight">{{ row.title }}</span>
                <span class="text-[10px] text-slate-400">ISBN: {{ row.isbn || '-' }}</span>
              </div>
            </template>
          </el-table-column>

          <el-table-column prop="author" label="Penulis" width="140" />
          
          <el-table-column label="Stok" width="90" align="center">
            <template #default="{ row }">
              <el-tag :type="row.stock <= row.rop_point ? 'danger' : 'success'" effect="dark" class="font-bold">
                {{ row.stock }}
              </el-tag>
            </template>
          </el-table-column>

          <!-- TAMPILAN HARGA GANDA (UMUM & PL) -->
          <el-table-column label="Informasi Harga" width="160">
            <template #default="{ row }">
              <div class="flex flex-col text-[11px] gap-0.5">
                <div class="flex justify-between">
                  <span class="text-slate-500">Umum:</span>
                  <span class="font-bold text-slate-700">Rp{{ formatPrice(row.price) }}</span>
                </div>
                <div class="flex justify-between border-t border-slate-50 pt-0.5">
                  <span class="text-[#00A9C3]">PL:</span>
                  <span class="font-bold text-[#00A9C3]">Rp{{ formatPrice(row.member_price) }}</span>
                </div>
              </div>
            </template>
          </el-table-column>

          <el-table-column label="Aksi" width="110" align="center">
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
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit Informasi Buku' : 'Tambah Buku Baru'" width="800px" top="5vh" append-to-body>
      <el-form :model="form" label-position="top">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
              <el-form-item label="Penulis"><el-input v-model="form.author" /></el-form-item>
              <el-form-item label="ISBN"><el-input v-model="form.isbn" @keyup.enter="submitForm" /></el-form-item>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <el-form-item label="Harga Umum (Rp)" required><el-input-number v-model="form.price" :min="0" class="!w-full" /></el-form-item>
              <el-form-item label="Harga PL (Rp)"><el-input-number v-model="form.member_price" :min="0" class="!w-full" @keyup.enter="submitForm" /></el-form-item>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <el-form-item label="Stok Gudang" required><el-input-number v-model="form.stock" :min="0" class="!w-full" /></el-form-item>
              <el-form-item label="Batas ROP (Waspada)"><el-input-number v-model="form.rop_point" :min="0" class="!w-full" @keyup.enter="submitForm" /></el-form-item>
            </div>
          </div>
          <div class="space-y-4">
            <el-form-item label="Gambar Sampul" required>
              <el-upload action="#" list-type="picture-card" :auto-upload="false" :limit="1" accept="image/*" v-model:file-list="imageFiles" :on-change="handleImageChange" :on-remove="handleImageRemove">
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
          {{ isEditing ? 'Update Katalog' : 'Simpan ke Katalog' }}
        </el-button>
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

const { apiFetch, uploadAttachment } = useApi();
const books = ref([]);
const categories = ref([]);
const loading = ref(false);
const loadingSave = ref(false);
const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const searchQuery = ref('');
const filterCategory = ref(null);
const imageFiles = ref([]);

const form = reactive({
  title: '', category_id: null, author: '', isbn: '',
  price: 0, member_price: 0, stock: 0, rop_point: 10,
  description: '', thumbnail_id: null
});

// Helper format harga
const formatPrice = (val) => new Intl.NumberFormat('id-ID').format(val || 0);

const loadData = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (searchQuery.value) params.append('search', searchQuery.value);
    if (filterCategory.value) params.append('category_id', filterCategory.value);
    
    const [bRes, cRes] = await Promise.all([
      apiFetch(`/books?${params.toString()}`),
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
    ElMessage.success('Gambar berhasil diunggah');
  } catch (e) { ElMessage.error('Gagal unggah gambar'); }
};

const handleImageRemove = () => { form.thumbnail_id = null; imageFiles.value = []; };

const openCreate = () => {
  isEditing.value = false; editingId.value = null;
  Object.assign(form, { title: '', category_id: null, author: '', isbn: '', price: 0, member_price: 0, stock: 0, rop_point: 10, description: '', thumbnail_id: null });
  imageFiles.value = []; isDialogOpen.value = true;
};

const openEdit = (row) => {
  isEditing.value = true; editingId.value = row.id;
  Object.assign(form, { ...row });
  imageFiles.value = row.image_url ? [{ url: row.image_url }] : [];
  isDialogOpen.value = true;
};

const submitForm = async () => {
  if(!form.title || !form.category_id) return ElMessage.warning('Lengkapi Judul dan Kategori');
  loadingSave.value = true;
  try {
    const method = isEditing.value ? 'PUT' : 'POST';
    const url = isEditing.value ? `/books/${editingId.value}` : '/books';
    await apiFetch(url, { method, body: form });
    ElMessage.success('Selesai disimpan ke katalog');
    isDialogOpen.value = false;
    loadData();
  } catch (e: any) { 
    const msg = e.data?.message || 'Gagal simpan data';
    ElNotification({ title: 'Kesalahan', message: msg, type: 'error' });
  }
  finally { loadingSave.value = false; }
};

const handleExport = () => {
  ElMessage.info('Sedang menyiapkan dokumen PDF...');
  
  // Arahkan ke URL download API kita
  // Karena ini download file, kita bisa pakai window.open
  // Pastikan URL-nya sesuai dengan alamat API kamu
  window.open('http://localhost:8000/api/books/export/pdf', '_blank');
};

const confirmDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`Hapus ${row.title}? Seluruh data transaksi buku ini tetap tersimpan di riwayat.`, 'Hapus Buku', { 
      type: 'warning',
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal'
    });
    await apiFetch(`/books/${row.id}`, { method: 'DELETE' });
    ElMessage.success('Buku telah dihapus'); loadData();
  } catch (e) {}
};

// Hanya panggil data otomatis saat input pencarian dikosongkan (X button)
watch(searchQuery, (newVal) => {
  if (newVal === '') loadData();
});

onMounted(loadData);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; }
:deep(.el-table .cell) { @apply py-2; }
</style>