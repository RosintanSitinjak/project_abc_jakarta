<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Pesanan Masuk</h2>
          <p class="text-sm text-slate-500">Daftar transaksi dan input pesanan manual (WhatsApp/Offline).</p>
        </div>
        <div class="flex items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Cari No. Invoice..." clearable class="w-64" />
          <el-button type="primary" color="#1B293C" @click="openManualOrder">
            + Pesanan Manual
          </el-button>
        </div>
      </div>

      <!-- Tabel Daftar Pesanan -->
      <div class="mt-6 overflow-x-auto">
        <el-table :data="orders" v-loading="loading" stripe class="w-full">
          <el-table-column prop="order_number" label="No. Invoice" width="160" />
          <el-table-column prop="customer.name" label="Pelanggan" min-width="180" />
          <el-table-column label="Metode" width="100">
            <template #default="{ row }">
              <el-tag size="small" effect="plain">{{ row.payment_method.toUpperCase() }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Total (Rp)" width="150">
            <template #default="{ row }">
              {{ new Intl.NumberFormat('id-ID').format(row.total_amount) }}
            </template>
          </el-table-column>
          <el-table-column prop="source" label="Sumber" width="120">
            <template #default="{ row }">
              <span class="text-[10px] font-bold uppercase">{{ row.source === 'admin_manual' ? 'WA/MANUAL' : 'WEB' }}</span>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </section>

    <!-- DIALOG FORM PESANAN MANUAL -->
    <el-dialog v-model="isDialogOpen" title="Buat Pesanan Baru (Manual)" width="600px">
      <el-form label-position="top">
        <el-form-item label="Pilih Pelanggan (Gereja/Jemaat)" required>
          <el-select v-model="form.customer_id" filterable placeholder="Ketik nama pelanggan..." class="w-full">
            <el-option v-for="c in customers" :key="c.id" :label="c.name" :value="c.id" />
          </el-select>
        </el-form-item>

        <div class="border-t border-slate-100 my-4 pt-4">
          <p class="text-xs font-bold text-slate-400 uppercase mb-3">Daftar Buku yang Dibeli</p>
          <div v-for="(item, index) in form.items" :key="index" class="flex gap-2 mb-2">
            <el-select v-model="item.book_id" placeholder="Pilih Buku" class="flex-1" @change="updatePrice(index)">
              <el-option v-for="b in books" :key="b.id" :label="b.title" :value="b.id" />
            </el-select>
            <el-input-number v-model="item.qty" :min="1" class="!w-24" />
            <el-button type="danger" plain @click="removeItem(index)">X</el-button>
          </div>
          <el-button type="primary" size="small" plain @click="addItem">+ Tambah Buku</el-button>
        </div>

        <el-form-item label="Metode Pembayaran" class="mt-4">
          <el-radio-group v-model="form.payment_method">
            <el-radio value="cash">Tunai</el-radio>
            <el-radio value="transfer">Transfer</el-radio>
            <el-radio value="kredit">Kredit (Tempo)</el-radio>
          </el-radio-group>
        </el-form-item>

        <div class="bg-slate-50 p-4 rounded-xl text-right">
          <p class="text-xs text-slate-500">Total Pembayaran:</p>
          <p class="text-xl font-black text-[#1B293C]">Rp {{ new Intl.NumberFormat('id-ID').format(totalBill) }}</p>
        </div>
      </el-form>
      <template #footer>
        <el-button @click="isDialogOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" @click="submitOrder">Simpan Pesanan</el-button>
      </template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { ElMessage } from 'element-plus';
import { computed, onMounted, reactive, ref } from 'vue';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const { apiFetch, unwrap } = useApi();
const orders = ref([]);
const customers = ref([]);
const books = ref([]);
const loading = ref(false);
const isDialogOpen = ref(false);
const searchQuery = ref('');

const form = reactive({
  customer_id: '',
  payment_method: 'cash',
  items: [{ book_id: '', qty: 1, price: 0 }]
});

const totalBill = computed(() => {
  return form.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
});

const loadInitialData = async () => {
  loading.value = true;
  try {
    const [oRes, cRes, bRes] = await Promise.all([
      apiFetch('/orders'),
      apiFetch('/customers'),
      apiFetch('/books')
    ]);
    orders.value = unwrap(oRes);
    customers.value = unwrap(cRes);
    books.value = unwrap(bRes);
  } finally { loading.value = false; }
};

const openManualOrder = () => {
  Object.assign(form, { customer_id: '', payment_method: 'cash', items: [{ book_id: '', qty: 1, price: 0 }] });
  isDialogOpen.value = true;
};

const addItem = () => form.items.push({ book_id: '', qty: 1, price: 0 });
const removeItem = (i: number) => form.items.splice(i, 1);

const updatePrice = (i: number) => {
  const selectedBook = books.value.find(b => b.id === form.items[i].book_id);
  if (selectedBook) form.items[i].price = selectedBook.price;
};

const submitOrder = async () => {
  if (!form.customer_id || form.items.some(i => !i.book_id)) return ElMessage.warning('Lengkapi data pesanan');
  try {
    await apiFetch('/orders', { 
      method: 'POST', 
      body: { ...form, total_amount: totalBill.value } 
    });
    ElMessage.success('Pesanan manual berhasil dibuat');
    isDialogOpen.value = false;
    loadInitialData();
  } catch (e) { ElMessage.error('Gagal menyimpan pesanan'); }
};

onMounted(loadInitialData);
</script>