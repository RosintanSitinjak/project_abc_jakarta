<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <!-- HEADER & FILTER -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
          <h2 class="text-xl font-bold text-[#1B293C]">Database Gereja & Jemaat</h2>
          <p class="text-sm text-slate-500">Kelola profil, riwayat transaksi, dan limit kredit pelanggan.</p>
        </div>
        <div class="flex items-center gap-3">
          <el-select 
            v-model="filterType" 
            placeholder="Semua Tipe" 
            clearable 
            class="w-32" 
            @change="loadCustomers"
          >
            <el-option label="Jemaat" value="jemaat" />
            <el-option label="Gereja" value="gereja" />
            <el-option label="PL (Penginjil)" value="penginjil" />
            <el-option label="Sekolah" value="sekolah" />
          </el-select>
          <el-input 
            v-model="searchQuery" 
            placeholder="Cari nama/kontak..." 
            clearable 
            class="w-52" 
            @keyup.enter="loadCustomers"
            @clear="loadCustomers"
          >
            <template #prefix><Icon icon="solar:magnifer-linear" /></template>
          </el-input>
          <el-button type="primary" color="#00A9C3" @click="openCreate">+ Tambah</el-button>
        </div>
      </div>

      <!-- TABEL DATABASE PELANGGAN -->
      <el-table :data="customers" v-loading="loading" stripe border class="w-full rounded-xl overflow-hidden text-xs">
        <el-table-column prop="name" label="Nama Pelanggan" min-width="180" fixed="left">
          <template #default="{ row }">
            <span class="font-bold text-[#1B293C]">{{ row.name }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Kategori" width="120" align="center">
          <template #default="{ row }">
            <el-tag 
              size="small" 
              effect="plain" 
              class="uppercase font-bold"
              :type="row.type === 'penginjil' ? 'warning' : (row.type === 'gereja' ? 'primary' : 'info')"
            >
              {{ row.type === 'penginjil' ? 'PL' : row.type }}
            </el-tag>
          </template>
        </el-table-column>

        <!-- WHATSAPP DENGAN KLIK LANGSUNG -->
        <el-table-column label="WhatsApp" width="165">
          <template #default="{ row }">
            <button 
              v-if="row.phone" 
              type="button"
              @click="sendWhatsApp(row)" 
              class="flex items-center gap-1.5 text-emerald-600 hover:text-emerald-700 font-bold hover:underline cursor-pointer bg-transparent border-0 p-0"
              :title="row.current_debt > 0 ? 'Kirim Pengingat Tagihan Piutang' : 'Kirim Pesan WhatsApp'"
            >
              <Icon icon="logos:whatsapp-icon" class="text-sm flex-shrink-0" />
              <span>{{ row.phone }}</span>
            </button>
            <span v-else class="text-slate-300 italic">No Phone</span>
          </template>
        </el-table-column>

        <el-table-column label="Limit" width="130" align="right">
          <template #default="{ row }">
            <span v-if="row.type === 'jemaat'" class="text-slate-500 font-semibold">Cash Only</span>
            <span v-else class="font-semibold text-slate-700">Rp {{ formatNumber(row.credit_limit) }}</span>
          </template>
        </el-table-column>

        <!-- HUTANG (SHORTCUT KE BUKU PIUTANG) -->
        <el-table-column label="Hutang" width="140" align="right">
          <template #default="{ row }">
            <el-tooltip v-if="row.current_debt > 0" content="Klik untuk lihat rincian di Buku Piutang" placement="top">
              <el-button 
                type="danger" 
                link 
                class="!font-black !text-xs text-red-500 hover:underline"
                @click="$router.push({ path: '/admin/receivables', query: { search: row.name } })"
              >
                Rp {{ formatNumber(row.current_debt) }}
                <Icon icon="solar:alt-arrow-right-linear" class="ml-0.5 text-[10px]" />
              </el-button>
            </el-tooltip>
            <span v-else class="text-slate-400 font-semibold">Rp 0</span>
          </template>
        </el-table-column>
        
        <!-- KOLOM AKSI -->
        <el-table-column label="Aksi" width="170" align="center" fixed="right">
          <template #default="{ row }">
            <div class="flex justify-center gap-1">
              <!-- Riwayat Belanja -->
              <el-tooltip content="Riwayat Belanja (Faktur/Pesanan)" placement="top">
                <el-button circle size="small" type="primary" plain @click="openOrderHistory(row)">
                  <Icon icon="solar:bag-heart-bold" />
                </el-button>
              </el-tooltip>
              
              <!-- Kartu Piutang -->
              <el-tooltip content="Kartu Piutang & Riwayat Cicilan" placement="top">
                <el-button circle size="small" type="info" plain @click="viewHistory(row)">
                  <Icon icon="solar:history-bold" />
                </el-button>
              </el-tooltip>

              <!-- Edit Profil -->
              <el-tooltip content="Edit Data Pelanggan" placement="top">
                <el-button circle size="small" type="primary" plain @click="openEdit(row)">
                  <Icon icon="solar:pen-2-bold" />
                </el-button>
              </el-tooltip>

              <!-- Hapus Berproteksi Finansial -->
              <el-tooltip 
                v-if="row.current_debt > 0" 
                :content="`Tidak dapat dihapus karena masih ada piutang Rp ${formatNumber(row.current_debt)}`" 
                placement="top"
              >
                <span>
                  <el-button circle size="small" type="danger" plain disabled>
                    <Icon icon="solar:trash-bin-trash-bold" />
                  </el-button>
                </span>
              </el-tooltip>
              
              <el-tooltip v-else content="Hapus Pelanggan" placement="top">
                <el-button circle size="small" type="danger" plain @click="confirmDelete(row)">
                  <Icon icon="solar:trash-bin-trash-bold" />
                </el-button>
              </el-tooltip>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </section>

    <!-- DIALOG: RIWAYAT BELANJA PELANGGAN -->
    <el-dialog 
      v-model="isOrderHistoryOpen" 
      :title="'Riwayat Belanja: ' + selectedCustomer?.name" 
      width="780px"
      append-to-body
      align-center
    >
      <el-table :data="customerOrders" border stripe size="small" class="w-full text-xs">
        <el-table-column label="Waktu" width="130">
          <template #default="{ row }">{{ formatDateTime(row.created_at) }}</template>
        </el-table-column>
        <el-table-column prop="order_number" label="No. Invoice" width="170">
          <template #default="{ row }">
            <span class="font-bold text-[#1B293C]">{{ row.order_number }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Buku">
          <template #default="{ row }">
            <div v-for="i in row.items" :key="i.id" class="text-[11px] border-b border-slate-100 last:border-0 py-1">
              - <b>{{ i.book?.title }}</b> ({{ i.qty }}x)
            </div>
          </template>
        </el-table-column>
        <el-table-column label="Total Nota" width="120" align="right">
          <template #default="{ row }">
            <span class="font-bold">Rp {{ formatNumber(row.total_amount) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="payment_status" label="Status" width="110" align="center">
          <template #default="{ row }">
            <el-tag :type="row.payment_status === 'paid' ? 'success' : 'danger'" size="small" class="font-bold">
              {{ row.payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
            </el-tag>
          </template>
        </el-table-column>
      </el-table>
      <template #footer>
        <el-button @click="isOrderHistoryOpen = false">Tutup</el-button>
      </template>
    </el-dialog>
    
    <!-- DIALOG: FORM TAMBAH / EDIT PELANGGAN -->
    <el-dialog 
      v-model="isDialogOpen" 
      :title="isEditing ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru'" 
      width="500px"
      append-to-body
      align-center
    >
      <el-form :model="form" label-position="top">
        <el-form-item label="Nama Pelanggan / Institusi" required>
          <el-input v-model="form.name" placeholder="Misal: Jemaat Salemba / Bpk. Samuel" />
        </el-form-item>
        
        <div class="grid grid-cols-2 gap-4">
          <el-form-item label="Kategori Pelanggan" required>
            <el-select v-model="form.type" class="w-full" @change="handleTypeChange">
              <el-option label="Jemaat" value="jemaat" />
              <el-option label="Gereja" value="gereja" />
              <el-option label="PL (Penginjil Literatur)" value="penginjil" />
              <el-option label="Sekolah" value="sekolah" />
            </el-select>
          </el-form-item>
          
          <el-form-item label="Nomor WhatsApp">
            <el-input v-model="form.phone" placeholder="08xxxxxxxxxx" />
          </el-form-item>
        </div>

        <el-form-item v-if="form.type !== 'jemaat'" label="Limit Kredit (Maksimal Piutang)">
          <el-input-number 
            v-model="form.credit_limit" 
            :min="0" 
            :max="999000000" 
            :step="500000"
            class="!w-full" 
          />
          <span class="text-[10px] text-slate-400 mt-1">
            *Untuk Penginjil Literatur (PL) batas plafon standar Rp 5.000.000
          </span>
        </el-form-item>

        <el-form-item label="Alamat / Wilayah Pelayanan">
          <el-input v-model="form.address" type="textarea" :rows="3" placeholder="Alamat lengkap pengiriman buku..." />
        </el-form-item>
      </el-form>
      
      <template #footer>
        <el-button @click="isDialogOpen = false">Batal</el-button>
        <el-button type="primary" color="#00A9C3" :loading="loadingSubmit" @click="submitForm">
          {{ isEditing ? 'Simpan Perubahan' : 'Tambah Pelanggan' }}
        </el-button>
      </template>
    </el-dialog>

    <!-- DIALOG: KARTU PIUTANG & BUKTI CICILAN (SUDAH DIUPGRADE BEBAS GAMBAR FAILED) -->
    <el-dialog 
      v-model="isHistoryOpen" 
      :title="'Kartu Piutang / Riwayat Cicilan: ' + selectedCustomer?.name" 
      width="700px"
      append-to-body
      align-center
    >
      <div class="space-y-4">
        <!-- 1. KARTU INFORMASI PLAFON & SISA PIUTANG -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-2xl">
          <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400 block leading-tight">Total Hutang Berjalan</span>
            <span class="text-base font-black text-red-600 mt-0.5 block">
              Rp {{ formatNumber(selectedCustomer?.current_debt) }}
            </span>
          </div>

          <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400 block leading-tight">Plafon Kredit Maks</span>
            <span class="text-base font-black text-slate-700 mt-0.5 block">
              {{ selectedCustomer?.type === 'jemaat' ? 'Cash Only' : 'Rp ' + formatNumber(selectedCustomer?.credit_limit) }}
            </span>
          </div>

          <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400 block leading-tight">Sisa Plafon Tersedia</span>
            <span class="text-base font-black text-emerald-600 mt-0.5 block">
              {{ selectedCustomer?.type === 'jemaat' ? 'Rp 0' : 'Rp ' + formatNumber(Math.max(0, (selectedCustomer?.credit_limit || 0) - (selectedCustomer?.current_debt || 0))) }}
            </span>
          </div>
        </div>

        <!-- 2. TABEL RIWAYAT CICILAN & BUKTI -->
        <el-table :data="paymentHistoryData" border stripe size="small" class="w-full text-xs rounded-xl overflow-hidden">
          <el-table-column label="Tanggal" width="120">
            <template #default="{ row }">
              <span class="font-medium text-slate-700">
                {{ new Date(row.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
              </span>
            </template>
          </el-table-column>
          
          <el-table-column label="Nominal Bayar" width="140" align="right">
            <template #default="{ row }">
              <span class="font-bold text-emerald-600">Rp {{ formatNumber(row.amount) }}</span>
            </template>
          </el-table-column>
          
          <el-table-column label="Metode" align="center" width="150">
            <template #default="{ row }">
              <el-tag size="small" effect="plain" class="font-bold text-[10px]" :type="getPaymentMethodTag(row.payment_method)">
                {{ formatPaymentMethodLabel(row.payment_method) }}
              </el-tag>
            </template>
          </el-table-column>

          <!-- KOLOM BUKTI STRUK DENGAN SLOT ERROR CERDAS -->
          <el-table-column label="Bukti Struk" align="center" min-width="120">
            <template #default="{ row }">
              <div v-if="row.proof_url" class="inline-flex items-center justify-center">
                <el-image 
                  :src="row.proof_url" 
                  :preview-src-list="[row.proof_url]" 
                  preview-teleported
                  class="w-8 h-8 rounded-lg shadow-xs cursor-pointer border border-slate-200 hover:scale-105 transition-transform" 
                  fit="cover" 
                >
                  <!-- JIKA FILE GAMBAR RUSAK / TIDAK ADA DI DISK -->
                  <template #error>
                    <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400" title="Struk tidak dapat dimuat">
                      <Icon icon="solar:gallery-remove-bold" class="text-base text-slate-400" />
                    </div>
                  </template>
                </el-image>
              </div>
              <span v-else class="text-slate-400 text-[10px] italic bg-slate-100 px-2 py-0.5 rounded-full">
                Tanpa Struk (Kasir)
              </span>
            </template>
          </el-table-column>
        </el-table>

        <!-- KETERANGAN JIKA BELUM PERNAH NYICIL -->
        <div v-if="paymentHistoryData.length === 0" class="text-center py-6 text-slate-400 text-xs">
          Belum ada catatan pembayaran cicilan untuk pelanggan ini.
        </div>
      </div>

      <template #footer>
        <el-button @click="isHistoryOpen = false">Tutup</el-button>
      </template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { ElMessage, ElMessageBox, ElNotification } from 'element-plus';
import { onMounted, reactive, ref } from 'vue';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const { apiFetch, unwrap } = useApi();

const customers = ref([]); 
const loading = ref(false); 
const loadingSubmit = ref(false);
const searchQuery = ref(''); 
const filterType = ref('');

const isDialogOpen = ref(false); 
const isEditing = ref(false); 
const editingId = ref(null);
const form = reactive({ name: '', type: 'jemaat', phone: '', address: '', credit_limit: 0 });

const isHistoryOpen = ref(false); 
const paymentHistoryData = ref([]);

const isOrderHistoryOpen = ref(false); 
const customerOrders = ref([]); 
const selectedCustomer = ref<any>(null);

const formatNumber = (val: number) => new Intl.NumberFormat('id-ID').format(val || 0);
const formatDateTime = (d: string) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });

// FORMAT LABEL METODE PEMBAYARAN PADA KARTU PIUTANG
const formatPaymentMethodLabel = (method: string) => {
  if (!method) return 'Tunai';
  const m = method.toLowerCase();
  if (m === 'kredit') return 'Uang Muka (DP)';
  if (m === 'transfer') return 'Transfer Bank';
  if (m === 'cash') return 'Tunai (Kasir)';
  if (m === 'qris') return 'QRIS';
  return method.toUpperCase();
};

const getPaymentMethodTag = (method: string) => {
  if (!method) return 'info';
  const m = method.toLowerCase();
  if (m === 'kredit') return 'warning';
  if (m === 'transfer') return 'primary';
  if (m === 'cash') return 'success';
  return 'info';
};

// WHATSAPP GATEWAY
const sendWhatsApp = (customer: any) => {
  if (!customer.phone) {
    return ElMessage.warning('Nomor WhatsApp pelanggan tidak tersedia.');
  }

  let cleanPhone = customer.phone.replace(/[^0-9]/g, '');
  if (cleanPhone.startsWith('0')) {
    cleanPhone = '62' + cleanPhone.slice(1);
  }

  const debt = Number(customer.current_debt) || 0;
  let text = '';

  if (debt > 0) {
    text = `Salam sejahtera Bpk/Ibu *${customer.name}*,\n` +
           `Kami dari *Adventist Book Center (ABC) Jakarta* menyampaikan informasi terkait transaksi literatur Anda.\n\n` +
           `📌 *Catatan Sisa Tagihan / Piutang Berjalan:*\n` +
           `Total Piutang: *Rp ${formatNumber(debt)}*\n\n` +
           `Pembayaran cicilan atau pelunasan dapat ditransfer ke rekening resmi ABC Jakarta:\n` +
           `🏦 *Bank Mandiri*: 123-00-9876543-2\n` +
           `a.n. *Adventist Book Center Jakarta*\n\n` +
           `Mohon mengirimkan foto struk bukti transfer balasan di chat ini agar kami segera catatkan ke Kartu Piutang sistem. Apabila ada pertanyaan terkait rincian faktur nota, silakan membalas pesan ini.\n\n` +
           `Terima kasih atas pelayanannya. Tuhan memberkati! 🙏`;
  } else {
    text = `Salam sejahtera Bpk/Ibu *${customer.name}*,\n` +
           `Kami dari *Adventist Book Center (ABC) Jakarta*.\n\n` +
           `Ada yang dapat kami bantu terkait kebutuhan buku pelajaran Sekolah Sabat, Roh Nubuat, atau literatur rohani lainnya untuk jemaat/pelayanan Anda?\n\n` +
           `Terima kasih dan Tuhan memberkati! 🙏`;
  }

  const waUrl = `https://api.whatsapp.com/send?phone=${cleanPhone}&text=${encodeURIComponent(text)}`;
  window.open(waUrl, '_blank');
};

const loadCustomers = async () => {
  loading.value = true; 
  try { 
    const res = await apiFetch(`/customers?search=${encodeURIComponent(searchQuery.value)}&type=${filterType.value}`); 
    customers.value = unwrap(res); 
  } finally { 
    loading.value = false; 
  }
};

const handleTypeChange = (newType: string) => {
  if (newType === 'jemaat') {
    form.credit_limit = 0;
  } else if (newType === 'penginjil' && form.credit_limit === 0) {
    form.credit_limit = 5000000;
  }
};

const openOrderHistory = async (row: any) => {
  selectedCustomer.value = row;
  try { 
    const res = await apiFetch(`/customers/${row.id}/orders`); 
    customerOrders.value = res.data || res; 
    isOrderHistoryOpen.value = true; 
  } catch (e) { 
    ElMessage.error('Gagal mengambil data riwayat pesanan'); 
  }
};

const viewHistory = async (row: any) => { 
  selectedCustomer.value = row;
  try {
    const res = await apiFetch(`/customers/${row.id}/payment-history`); 
    paymentHistoryData.value = res; 
    isHistoryOpen.value = true; 
  } catch (e) {
    ElMessage.error('Gagal mengambil data kartu piutang');
  }
};

const openCreate = () => { 
  isEditing.value = false; 
  editingId.value = null;
  Object.assign(form, { name: '', type: 'jemaat', phone: '', address: '', credit_limit: 0 }); 
  isDialogOpen.value = true; 
};

const openEdit = (row: any) => { 
  isEditing.value = true; 
  editingId.value = row.id; 
  Object.assign(form, { 
    name: row.name,
    type: row.type,
    phone: row.phone || '',
    address: row.address || '',
    credit_limit: row.credit_limit || 0
  }); 
  isDialogOpen.value = true; 
};

const submitForm = async () => { 
  if (!form.name.trim()) return ElMessage.warning('Nama pelanggan wajib diisi.');
  
  loadingSubmit.value = true;
  try {
    await apiFetch(isEditing.value ? `/customers/${editingId.value}` : '/customers', { 
      method: isEditing.value ? 'PUT' : 'POST', 
      body: form 
    }); 
    ElMessage.success(isEditing.value ? 'Profil pelanggan berhasil diperbarui!' : 'Pelanggan baru berhasil ditambahkan!');
    isDialogOpen.value = false; 
    loadCustomers(); 
  } catch (e: any) {
    ElNotification({ 
      title: 'Gagal', 
      message: e.data?.message || 'Gagal menyimpan data pelanggan', 
      type: 'error' 
    });
  } finally {
    loadingSubmit.value = false;
  }
};

const confirmDelete = async (row: any) => { 
  if (row.current_debt > 0) {
    return ElMessageBox.alert(
      `Pelanggan "${row.name}" masih memiliki sisa piutang sebesar Rp ${formatNumber(row.current_debt)}. Harap selesaikan pelunasan piutang terlebih dahulu sebelum menghapus data.`,
      'Perlindungan Keuangan',
      { type: 'warning', confirmButtonText: 'Saya Mengerti' }
    );
  }

  try {
    await ElMessageBox.confirm(`Hapus pelanggan "${row.name}"? Data login pengguna terkait juga akan dihapus.`, 'Konfirmasi Hapus', {
      type: 'warning',
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal'
    }); 
    
    await apiFetch(`/customers/${row.id}`, { method: 'DELETE' }); 
    ElMessage.success('Data pelanggan berhasil dihapus.');
    loadCustomers(); 
  } catch (e: any) {
    if (e !== 'cancel') {
      const msg = e.data?.message || 'Terjadi kesalahan saat menghapus data';
      ElNotification({ title: 'Gagal Hapus', message: msg, type: 'error' });
    }
  }
};

onMounted(loadCustomers);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { 
  @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm; 
}
</style>