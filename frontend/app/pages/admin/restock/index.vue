<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="mb-6">
        <h2 class="text-xl font-bold text-[#1B293C]">Log Pengajuan Restock</h2>
        <p class="text-sm text-slate-500">Alur pengadaan buku IPH: Pengajuan -> Persetujuan Pimpinan -> Konfirmasi Kedatangan.</p>
      </div>

      <div class="overflow-x-auto">
        <el-table :data="requests" v-loading="loading" stripe border class="w-full rounded-xl text-xs">
          
          <el-table-column label="Detail Pengajuan" min-width="220">
            <template #default="{ row }">
              <div class="font-bold text-[#1B293C] text-sm">{{ row.book?.title }}</div>
              <div class="text-[9px] text-slate-400 mt-0.5">Pemohon: <b>{{ row.user?.name }}</b> pada {{ formatDate(row.created_at) }}</div>
            </template>
          </el-table-column>

          <el-table-column prop="quantity" label="Qty" width="80" align="center">
            <template #default="{ row }"><span class="font-black text-lg text-[#00A9C3]">{{ row.quantity }}</span></template>
          </el-table-column>

          <el-table-column label="Status & Catatan Pak Andi" width="220">
            <template #default="{ row }">
              <el-tag :type="getStatusType(row.status)" effect="dark" size="small" class="font-bold mb-2">{{ getStatusLabel(row.status) }}</el-tag>
              <div v-if="row.pimpinan_notes" class="p-2 bg-white rounded border border-slate-100 text-[10px] text-slate-600 italic">"{{ row.pimpinan_notes }}"</div>
              <div v-else-if="row.status === 'pending'" class="text-[10px] text-slate-300 italic">Menunggu keputusan...</div>
            </template>
          </el-table-column>

          <el-table-column label="Aksi" width="200" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-2">
                <!-- AKSES PAK ANDI: Muncul Jika Role 1 dan Status Pending -->
                <template v-if="authUser?.role === 1 && row.status === 'pending'">
                  <el-button size="small" type="success" @click="handleApproval(row, 'approved')">SETUJUI</el-button>
                  <el-button size="small" type="danger" plain @click="handleApproval(row, 'rejected')">TOLAK</el-button>
                </template>

                <!-- AKSES ADMIN STAFF: Muncul Jika Status Approved & Belum Selesai -->
                <template v-if="authUser?.role !== 1 && row.status === 'approved' && !row.is_completed">
                  <el-button type="primary" size="small" color="#00A9C3" @click="confirmArrival(row.id)">
                    BARANG SUDAH DATANG
                  </el-button>
                </template>

                <!-- STATUS SELESAI (SUDAH MASUK GUDANG) -->
                <div v-if="row.is_completed" class="flex flex-col items-center">
                    <span class="text-green-500 font-bold text-[10px]"><Icon icon="solar:check-circle-bold" /> SELESAI</span>
                    <span class="text-[8px] text-slate-400">Masuk gudang: {{ formatDate(row.completed_at) }}</span>
                </div>

                <div v-else-if="row.status === 'rejected'" class="text-red-400 font-bold uppercase text-[9px]">Ditolak Pimpinan</div>
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
import { onMounted, ref } from 'vue';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const { apiFetch } = useApi();
const authUser = useState<any>("auth-user", () => null);
const requests = ref([]);
const loading = ref(false);

const formatDate = (d: string) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour:'2-digit', minute:'2-digit' });
const getStatusLabel = (s: string) => ({ pending: 'DIAJUKAN', approved: 'DISETUJUI', rejected: 'DITOLAK' }[s] || s);
const getStatusType = (s: string) => ({ pending: 'warning', approved: 'success', rejected: 'danger' }[s] || 'info');

const loadData = async () => {
  loading.value = true;
  try { requests.value = await apiFetch('/restock-requests'); } 
  finally { loading.value = false; }
};

const handleApproval = async (row: any, status: string) => {
  ElMessageBox.prompt('Berikan arahan atau catatan untuk pengajuan ini:', status === 'approved' ? 'Setujui Pengadaan' : 'Tolak Pengadaan', {
    confirmButtonText: 'Kirim', cancelButtonText: 'Batal', inputType: 'textarea'
  }).then(async ({ value }) => {
    try {
      await apiFetch(`/restock-requests/${row.id}/status`, { method: 'PATCH', body: { status, notes: value } });
      ElMessage.success('Keputusan pimpinan disimpan!'); loadData();
    } catch (e) { ElMessage.error('Gagal'); }
  });
};

const confirmArrival = async (id: string) => {
  try {
    await ElMessageBox.confirm('Konfirmasi bahwa buku fisik sudah sampai dan stok siap diupdate?', 'Terima Barang', { type: 'success' });
    await apiFetch(`/restock-requests/${id}/complete`, { method: 'PATCH' });
    ElMessage.success('Sukses! Stok buku di katalog resmi bertambah.'); loadData();
  } catch (e) {}
};

onMounted(loadData);
</script>