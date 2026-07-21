<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Katalog Buku</h2>
          <p class="text-sm text-slate-500">Kelola informasi literatur, stok, dan restock cepat.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <!-- TOGGLE FILTER STOK KRITIS (NEW) -->
          <el-button 
            :type="lowStockOnly ? 'danger' : 'info'" 
            :plain="!lowStockOnly" 
            @click="toggleLowStock"
            class="!rounded-xl"
          >
            <Icon icon="solar:bell-bing-bold" class="mr-1" /> 
            {{ lowStockOnly ? 'Semua Buku' : 'Stok Kritis' }}
          </el-button>

          <el-select v-model="filterCategory" placeholder="Semua Kategori" clearable class="w-40" @change="loadData">
            <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
          </el-select>

          <el-input v-model="searchQuery" placeholder="Cari..." clearable class="w-52" @keyup.enter="loadData" @clear="loadData">
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          
          <el-button type="warning" plain @click="handleExport"><Icon icon="solar:printer-minimalistic-bold" /></el-button>
          <el-button type="primary" color="#00A9C3" @click="openCreate">+ Tambah</el-button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto">
        <el-table :data="books" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden">
          <el-table-column label="Gambar" width="90" align="center">
            <template #default="{ row }">
              <el-image v-if="row.image_url" :src="row.image_url" class="h-12 w-8 rounded shadow-sm" fit="cover" />
              <Icon v-else icon="solar:book-2-linear" class="text-2xl text-slate-200" />
            </template>
          </el-table-column>
          
          <el-table-column prop="title" label="Judul Buku" min-width="220">
            <template #default="{ row }">
              <div class="flex flex-col">
                <span class="font-bold text-[#1B293C] leading-tight">{{ row.title }}</span>
                <span class="text-[10px] text-slate-400 italic">ISBN: {{ row.isbn || '-' }}</span>
              </div>
            </template>
          </el-table-column>
          
          <el-table-column label="Stok" width="90" align="center">
            <template #default="{ row }">
              <el-tag :type="row.stock <= row.rop_point ? 'danger' : 'success'" effect="dark" class="font-bold">{{ row.stock }}</el-tag>
            </template>
          </el-table-column>

          <el-table-column label="Harga (Umum / PL)" width="180">
            <template #default="{ row }">
              <div class="text-[11px]">
                <div class="flex justify-between"><span>Umum:</span><span class="font-bold text-slate-700">Rp{{ formatPrice(row.price) }}</span></div>
                <div class="flex justify-between text-[#00A9C3] font-bold"><span>PL:</span><span>Rp{{ formatPrice(row.member_price) }}</span></div>
              </div>
            </template>
          </el-table-column>

          <el-table-column label="Aksi" width="150" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-1">
                <!-- TOMBOL RESTOCK CEPAT (NEW) -->
                <el-tooltip content="Restock Cepat" placement="top">
                  <el-button circle size="small" type="success" @click="openRestock(row)"><Icon icon="solar:add-circle-bold" /></el-button>
                </el-tooltip>
                <el-button circle size="small" type="primary" plain @click="openEdit(row)"><Icon icon="solar:pen-2-bold" /></el-button>
                <el-button circle size="small" type="danger" plain @click="confirmDelete(row)"><Icon icon="solar:trash-bin-trash-bold" /></el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- MODAL RESTOCK (NEW) -->
    <el-dialog v-model="isRestockOpen" title="Restock Buku" width="350px" align-center>
      <div v-if="selectedBook" class="mb-4 text-center">
        <p class="text-xs text-slate-500 uppercase font-bold">Stok Saat Ini: {{ selectedBook.stock }}</p>
        <h3 class="font-bold text-[#1B293C]">{{ selectedBook.title }}</h3>
      </div>
      <el-form label-position="top">
        <el-form-item label="Jumlah Stok Masuk" required>
          <el-input-number v-model="restockQty" :min="1" class="!w-full" @keyup.enter="submitRestock" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button type="primary" color="#00A9C3" @click="submitRestock" :loading="loadingRestock">Tambah Stok</el-button>
      </template>
    </el-dialog>

    <!-- DIALOG EDIT/TAMBAH (EXISTING) -->
    <el-dialog v-model="isDialogOpen" :title="isEditing ? 'Edit' : 'Tambah'" width="800px" top="5vh">
       <!-- ... Isi Form Dialog kamu yang lama ... -->
       <el-form :model="form" label-position="top">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <el-form-item label="Judul Buku" required><el-input v-model="form.title" /></el-form-item>
            <el-form-item label="Kategori" required>
              <el-select v-model="form.category_id" class="w-full">
                <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
              </el-select>
            </el-form-item>
            <div class="grid grid-cols-2 gap-4">
              <el-form-item label="Penulis"><el-input v-model="form.author" /></el-form-item>
              <el-form-item label="ISBN"><el-input v-model="form.isbn" @keyup.enter="submitForm" /></el-form-item>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <el-form-item label="Harga Umum"><el-input-number v-model="form.price" class="!w-full" /></el-form-item>
              <el-form-item label="Harga PL"><el-input-number v-model="form.member_price" class="!w-full" @keyup.enter="submitForm" /></el-form-item>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <el-form-item label="Stok"><el-input-number v-model="form.stock" class="!w-full" /></el-form-item>
              <el-form-item label="Batas ROP"><el-input-number v-model="form.rop_point" class="!w-full" @keyup.enter="submitForm" /></el-form-item>
            </div>
          </div>
          <div class="space-y-4">
            <el-form-item label="Gambar"><el-upload action="#" list-type="picture-card" :auto-upload="false" :limit="1" v-model:file-list="imageFiles" :on-change="handleImageChange" :on-remove="handleImageRemove"><Icon icon="solar:camera-add-linear" /></el-upload></el-form-item>
            <el-form-item label="Sinopsis"><el-input v-model="form.description" type="textarea" :rows="8" /></el-form-item>
          </div>
        </div>
      </el-form>
      <template #footer><el-button type="primary" color="#00A9C3" @click="submitForm">Simpan</el-button></template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { ElMessage, ElMessageBox, ElNotification } from 'element-plus';
import { onMounted, reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const route = useRoute();
const { apiFetch, uploadAttachment } = useApi();

const books = ref([]);
const categories = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const filterCategory = ref(route.query.category_id || null);
const lowStockOnly = ref(false); // NEW STATE

// Restock State
const isRestockOpen = ref(false);
const loadingRestock = ref(false);
const selectedBook = ref(null);
const restockQty = ref(1);

const formatPrice = (val) => new Intl.NumberFormat('id-ID').format(val || 0);

const loadData = async () => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (searchQuery.value) params.append('search', searchQuery.value);
    if (filterCategory.value) params.append('category_id', String(filterCategory.value));
    if (lowStockOnly.value) params.append('low_stock', 'true'); // NEW PARAM

    const [bRes, cRes] = await Promise.all([apiFetch(`/books?${params.toString()}`), apiFetch('/categories')]);
    books.value = bRes.data || bRes;
    categories.value = cRes.data || cRes;
  } finally { loading.value = false; }
};

const toggleLowStock = () => {
  lowStockOnly.value = !lowStockOnly.value;
  loadData();
};

const openRestock = (row) => {
  selectedBook.value = row;
  restockQty.value = 1;
  isRestockOpen.value = true;
};

const submitRestock = async () => {
  loadingRestock.value = true;
  try {
    await apiFetch(`/books/${selectedBook.value.id}/restock`, { method: 'PATCH', body: { quantity: restockQty.value } });
    ElMessage.success('Stok berhasil ditambahkan');
    isRestockOpen.value = false;
    loadData();
  } catch (e) { ElMessage.error('Gagal restock'); }
  finally { loadingRestock.value = false; }
};

// ... (Sisanya openCreate, openEdit, submitForm, handleImage tetap sama) ...
const isDialogOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const loadingSave = ref(false);
const imageFiles = ref([]);
const form = reactive({ title: '', category_id: null, author: '', isbn: '', price: 0, member_price: 0, stock: 0, rop_point: 10, description: '', thumbnail_id: null });

const openCreate = () => { isEditing.value = false; Object.assign(form, { title: '', category_id: null, author: '', isbn: '', price: 0, member_price: 0, stock: 0, rop_point: 10, description: '', thumbnail_id: null }); isDialogOpen.value = true; };
const openEdit = (row) => { isEditing.value = true; editingId.value = row.id; Object.assign(form, { ...row }); isDialogOpen.value = true; };
const submitForm = async () => { 
  loadingSave.value = true; 
  try { await apiFetch(isEditing.value ? `/books/${editingId.value}` : '/books', { method: isEditing.value ? 'PUT' : 'POST', body: form }); ElMessage.success('Tersimpan'); isDialogOpen.value = false; loadData(); } 
  catch (e:any) { ElNotification({ title: 'Error', message: e.data?.message || 'Gagal', type: 'error' }); } 
  finally { loadingSave.value = false; } 
};
const handleImageChange = async (file) => { const res = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Book', type: 'thumbnail' }); form.thumbnail_id = res.id; };
const handleImageRemove = () => { form.thumbnail_id = null; };
const handleExport = () => window.open('http://localhost:8000/api/books/export/pdf', '_blank');
const confirmDelete = async (row) => { await ElMessageBox.confirm(`Hapus ${row.title}?`, 'Hapus'); await apiFetch(`/books/${row.id}`, { method: 'DELETE' }); loadData(); };

onMounted(loadData);
</script>