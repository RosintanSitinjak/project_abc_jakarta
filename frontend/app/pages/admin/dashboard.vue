<template>
  <AdminShell>
    <!-- 1. BARIS KOTAK STATISTIK (Gaya Compact / Padat) -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 mb-6">
      <div 
        v-for="stat in statCards" 
        :key="stat.key" 
        class="stat-card-compact group cursor-pointer"
        @click="$router.push(stat.to)"
      >
        <!-- Icon di sisi kiri dengan background solid -->
        <div class="stat-icon-box" :style="{ backgroundColor: stat.color }">
          <Icon :icon="stat.icon" class="text-xl text-white" />
        </div>
        
        <div class="flex-1 min-w-0">
          <p class="text-[9px] font-black uppercase tracking-wider text-slate-400 truncate">
            {{ stat.label }}
          </p>
          <p class="text-sm font-black text-[#1B293C] truncate">
            <span v-if="loading" class="inline-block h-4 w-12 animate-pulse rounded bg-slate-100" />
            <span v-else>{{ stat.value }}</span>
          </p>
        </div>

        <!-- Indikator panah kecil di sisi kanan -->
        <Icon icon="solar:alt-arrow-right-linear" class="text-slate-200 group-hover:text-slate-400 transition-colors" />
      </div>
    </section>

    <!-- 2. AREA UTAMA -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- KIRI: GRAFIK PENJUALAN + AGING INFO -->
      <div class="lg:col-span-2 space-y-6">
        <section class="glass-panel p-6 bg-white border border-slate-200">
          <div class="flex items-center justify-between mb-8">
            <div>
              <h2 class="text-lg font-black text-[#1B293C]">Statistik Penjualan Buku</h2>
              <p class="text-xs text-slate-500">Data bulanan tahun {{ chartYear }}</p>
            </div>
            <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-1.5 text-[10px] font-bold text-[#1B293C] border border-slate-200">
              <Icon icon="solar:graph-new-up-linear" class="text-sm text-[#00A9C3]" />
              TOTAL PEMASUKAN: Rp {{ formatPrice(stats.total_income) }}
            </div>
          </div>

          <div style="height: 300px; width: 100%;">
            <v-chart v-if="!loading" :option="chartOption" autoresize />
          </div>
        </section>

        <!-- FITUR SAKTI: AGING PIUTANG (Gaya Compact Putih) -->
        <section class="glass-panel p-5 bg-white border border-slate-200">
          <div class="flex items-center gap-2 mb-4">
            <Icon icon="solar:shield-warning-bold-duotone" class="text-xl text-orange-400" />
            <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Analisis Kolektibilitas Piutang</h3>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
              <div class="flex items-center gap-3">
                <span class="flex h-2 w-2 rounded-full bg-red-500 animate-ping"></span>
                <span class="text-[11px] font-bold text-slate-600">Piutang Macet (>30 Hari)</span>
              </div>
              <span class="text-sm font-black text-red-500">Rp {{ formatPrice(stats.aging_macet) }}</span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
              <div class="flex items-center gap-3">
                <span class="flex h-2 w-2 rounded-full bg-yellow-400"></span>
                <span class="text-[11px] font-bold text-slate-600">Mendekati Jatuh Tempo</span>
              </div>
              <span class="text-sm font-black text-yellow-600">Rp {{ formatPrice(stats.aging_tempo) }}</span>
            </div>
          </div>
        </section>
      </div>

      <!-- KANAN: AKTIVITAS TERAKHIR -->
      <section class="glass-panel p-6 bg-white border border-slate-200 h-full flex flex-col">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-lg font-black text-[#1B293C]">Aktivitas Terakhir</h2>
          <Icon icon="solar:history-linear" class="text-slate-300 text-xl" />
        </div>
        
        <div v-if="loading" class="space-y-4">
          <div v-for="i in 5" :key="i" class="h-14 w-full animate-pulse bg-slate-50 rounded-xl" />
        </div>

        <div v-else class="space-y-4 flex-1">
          <div v-for="order in recentOrders" :key="order.id" class="flex items-center gap-3 pb-3 border-b border-slate-50 last:border-0">
            <div class="h-9 w-9 rounded-lg bg-slate-50 flex items-center justify-center text-[#00A9C3]">
              <Icon icon="solar:cart-large-minimalistic-bold" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-[#1B293C] truncate">{{ order.customer?.name }}</p>
              <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">{{ order.order_number }}</p>
            </div>
            <div class="text-right">
              <p class="text-[10px] font-black">Rp{{ formatPrice(order.total_amount) }}</p>
              <p class="text-[8px] font-black uppercase" :class="order.payment_status === 'paid' ? 'text-green-500' : 'text-red-400'">
                {{ order.payment_status === 'paid' ? 'LUNAS' : 'PIUTANG' }}
              </p>
            </div>
          </div>
        </div>
        <el-button class="w-full mt-6 !rounded-xl" size="small" plain @click="$router.push('/admin/orders')">
          Lihat Semua Pesanan
        </el-button>
      </section>
    </div>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { computed, onMounted, ref } from "vue";
import { use } from "echarts/core";
import { CanvasRenderer } from "echarts/renderers";
import { LineChart, BarChart } from "echarts/charts";
import { TitleComponent, TooltipComponent, GridComponent, LegendComponent } from "echarts/components";
import VChart from "vue-echarts";
import AdminShell from "~/components/Admin/Shell.vue";
import { useApi } from "~/composables/useApi";

use([CanvasRenderer, LineChart, BarChart, TitleComponent, TooltipComponent, GridComponent, LegendComponent]);

const { apiFetch } = useApi();
const loading = ref(true);
const chartYear = ref(new Date().getFullYear());
const stats = ref({ categories: 0, customers: 0, books: 0, new_orders: 0, low_stock: 0, total_piutang: 0, total_income: 0, aging_macet: 0, aging_tempo: 0 });
const recentOrders = ref<any[]>([]);
const monthlySales = ref<any[]>([]);

const formatPrice = (n: number) => new Intl.NumberFormat('id-ID').format(n || 0);

const statCards = computed(() => [
  { key: "cat", label: "Kategori", value: stats.value.categories, icon: "solar:folder-2-bold", color: "#00A9C3", to: "/admin/categories" },
  { key: "book", label: "Total Buku", value: stats.value.books, icon: "solar:book-bold", color: "#1B293C", to: "/admin/books" },
  { key: "piutang", label: "Total Piutang", value: 'Rp ' + formatPrice(stats.value.total_piutang), icon: "solar:wad-of-money-bold", color: "#f59e0b", to: "/admin/receivables" },
  { key: "ord", label: "Belum Lunas", value: stats.value.new_orders, icon: "solar:clipboard-list-bold", color: "#6366f1", to: "/admin/receivables" },
  { key: "rop", label: "Stok Kritis", value: stats.value.low_stock, icon: "solar:bell-bing-bold", color: "#f43f5e", to: "/admin/books" },
]);

const chartOption = computed(() => ({
  tooltip: { trigger: "axis", backgroundColor: "#1B293C", textStyle: { color: "#fff" }, borderRadius: 8 },
  grid: { left: "0%", right: "2%", bottom: "0%", containLabel: true },
  xAxis: { type: "category", data: monthlySales.value.map(m => m.label), axisLine: { show: false } },
  yAxis: { type: "value", splitLine: { lineStyle: { type: 'dashed', color: '#f1f5f9' } } },
  series: [
    { name: "Penjualan", type: "bar", data: monthlySales.value.map(m => m.total), itemStyle: { color: "#00A9C3", borderRadius: [4, 4, 0, 0] }, barWidth: '35%' },
    { name: "Trend", type: "line", data: monthlySales.value.map(m => m.total), smooth: true, lineStyle: { color: "#1B293C", width: 3 }, showSymbol: false }
  ],
}));

onMounted(async () => {
  try {
    const data: any = await apiFetch("/dashboard");
    if (data) {
      stats.value = data.stats;
      recentOrders.value = data.recent_orders || [];
      monthlySales.value = data.monthly_sales || [];
      chartYear.value = data.year;
    }
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
@reference "tailwindcss";

.stat-card-compact {
  @apply flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-200 shadow-sm transition-all hover:border-[#00A9C3]/40 hover:shadow-md;
}

.stat-icon-box {
  @apply flex items-center justify-center w-10 h-10 rounded-lg flex-shrink-0 shadow-sm;
}

.glass-panel {
  @apply rounded-[1.5rem] shadow-sm bg-white border border-slate-200;
}
</style>