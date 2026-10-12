<template>
  <AdminShell>
    <!-- 1. BARIS KOTAK STATISTIK (5 Kolom) -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 mb-8">
      <div v-for="stat in statCards" :key="stat.key" class="stat-card group">
        <div class="stat-icon" :style="{ background: stat.color }">
          <Icon :icon="stat.icon" class="text-xl text-white" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 truncate">
            {{ stat.label }}
          </p>
          <p class="mt-0.5 text-base font-black text-[#1B293C]">
            <span v-if="loading" class="inline-block h-5 w-12 animate-pulse rounded bg-slate-100" />
            <span v-else>{{ stat.value }}</span>
          </p>
        </div>
      </div>
    </section>

    <!-- 2. AREA UTAMA: GRAFIK & AKTIVITAS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- KIRI: GRAFIK PENJUALAN -->
      <section class="lg:col-span-2 glass-panel p-6 bg-white border border-slate-200">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-lg font-black text-[#1B293C]">Statistik Penjualan Buku</h2>
            <p class="text-xs text-slate-500">Data bulanan tahun {{ chartYear }}</p>
          </div>
          <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-1.5 text-[10px] font-bold text-[#1B293C] border border-slate-200">
            <Icon icon="solar:graph-new-up-linear" class="text-sm text-[#00A9C3]" />
            TOTAL: Rp {{ formatPrice(stats.total_income) }}
          </div>
        </div>

        <div v-if="loading" class="flex h-[300px] items-center justify-center">
          <p class="text-xs font-bold text-slate-300 uppercase tracking-widest animate-pulse">Memuat Grafik...</p>
        </div>
        <ClientOnly v-else>
          <div style="height: 300px; width: 100%;">
            <v-chart :option="chartOption" autoresize />
          </div>
        </ClientOnly>
      </section>

      <!-- KANAN: AKTIVITAS TERAKHIR (Penyelesaian Rasa Janggal) -->
      <section class="glass-panel p-6 bg-white border border-slate-200">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-black text-[#1B293C]">Aktivitas Terakhir</h2>
          <Icon icon="solar:history-linear" class="text-slate-300 text-xl" />
        </div>

        <div v-if="loading" class="space-y-4">
          <div v-for="i in 3" :key="i" class="h-12 w-full animate-pulse bg-slate-50 rounded-xl" />
        </div>
        
        <div v-else-if="recentOrders.length === 0" class="flex flex-col items-center py-10 text-center">
          <Icon icon="solar:box-minimalistic-linear" class="text-4xl text-slate-200 mb-2" />
          <p class="text-xs text-slate-400 font-medium">Belum ada transaksi masuk.</p>
        </div>

        <div v-else class="space-y-4">
          <div v-for="order in recentOrders" :key="order.id" class="flex items-center gap-3 pb-3 border-b border-slate-50 last:border-0">
            <div class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-[#00A9C3]">
              <Icon icon="solar:cart-large-minimalistic-bold" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-[#1B293C] truncate">{{ order.customer?.name || 'Pelanggan' }}</p>
              <p class="text-[9px] text-slate-400 font-mono">{{ order.order_number }}</p>
            </div>
            <div class="text-right">
              <p class="text-[10px] font-black text-[#1B293C]">Rp{{ formatPrice(order.total_amount) }}</p>
              <p class="text-[8px] font-black uppercase" :class="order.payment_status === 'paid' ? 'text-green-500' : 'text-red-400'">
                {{ order.payment_status === 'paid' ? 'LUNAS' : 'PIUTANG' }}
              </p>
            </div>
          </div>
          <el-button class="w-full mt-2 !rounded-xl" size="small" plain @click="$router.push('/admin/orders')">
            Lihat Semua Pesanan
          </el-button>
        </div>
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
const stats = ref({ categories: 0, customers: 0, books: 0, new_orders: 0, low_stock: 0, total_piutang: 0, total_income: 0 });
const recentOrders = ref<any[]>([]);
const monthlySales = ref<any[]>([]);

const formatPrice = (n: number) => new Intl.NumberFormat('id-ID').format(n);

const statCards = computed(() => [
  { key: "cat", label: "Kategori", value: stats.value.categories, icon: "solar:folder-2-bold", color: "#00A9C3" },
  { key: "book", label: "Total Buku", value: stats.value.books, icon: "solar:book-bold", color: "#1B293C" },
  { key: "piutang", label: "Total Piutang", value: 'Rp ' + formatPrice(stats.value.total_piutang), icon: "solar:wad-of-money-bold", color: "#f59e0b" },
  { key: "ord", label: "Belum Lunas", value: stats.value.new_orders, icon: "solar:clipboard-list-bold", color: "#1B293C" },
  { key: "rop", label: "Stok Kritis", value: stats.value.low_stock, icon: "solar:bell-bing-bold", color: "#f43f5e" },
]);

const chartOption = computed(() => ({
  tooltip: { trigger: "axis", backgroundColor: "#1B293C", textStyle: { color: "#fff" } },
  grid: { left: "0%", right: "2%", bottom: "0%", containLabel: true },
  xAxis: { type: "category", data: monthlySales.value.map(m => m.label), axisLine: { show: false } },
  yAxis: { type: "value", splitLine: { lineStyle: { type: 'dashed', color: '#f1f5f9' } } },
  series: [
    { name: "Penjualan", type: "bar", data: monthlySales.value.map(m => m.total), itemStyle: { color: "#00A9C3", borderRadius: [6, 6, 0, 0] }, barWidth: '35%' },
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
.stat-card { @apply flex items-center gap-3 p-5 rounded-2xl bg-white border border-slate-100 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md; }
.stat-icon { @apply flex items-center justify-center w-11 h-11 rounded-xl flex-shrink-0 shadow-lg shadow-slate-100; }
.glass-panel { @apply rounded-[1.5rem] shadow-sm; }
</style>