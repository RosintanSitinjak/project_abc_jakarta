<template>
  <AdminShell>
    <!-- 1. BARIS KOTAK STATISTIK -->
    <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5 mb-6">
      <div 
        v-for="stat in statCards" 
        :key="stat.key" 
        class="stat-card-compact group cursor-pointer"
        @click="$router.push(stat.to)"
      >
        <!-- Icon di sisi kiri -->
        <div class="stat-icon-box" :style="{ backgroundColor: stat.color }">
          <Icon :icon="stat.icon" class="text-lg text-white" />
        </div>
        
        <div class="flex-1 min-w-0">
          <p class="text-[10px] font-black uppercase tracking-tight text-slate-400 whitespace-nowrap">
            {{ stat.label }}
          </p>
          <p class="text-xs sm:text-[13px] font-black text-[#1B293C] tracking-tight whitespace-nowrap">
            <span v-if="loading" class="inline-block h-4 w-12 animate-pulse rounded bg-slate-100" />
            <span v-else>{{ stat.value }}</span>
          </p>
        </div>

        <!-- Indikator panah kecil -->
        <Icon icon="solar:alt-arrow-right-linear" class="text-slate-200 group-hover:text-slate-400 transition-colors flex-shrink-0" />
      </div>
    </section>

    <!-- 2. PERINGATAN ROP / STOK KRITIS (Khusus ABC Jakarta) -->
    <transition name="el-fade-in">
      <div 
        v-if="!loading && stats.low_stock > 0" 
        class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 flex flex-wrap items-center justify-between gap-3 shadow-xs"
      >
        <div class="flex items-center gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm animate-pulse">
            <Icon icon="solar:bell-bing-bold" class="text-xl" />
          </div>
          <div>
            <h4 class="text-xs font-black text-rose-800 uppercase tracking-wide flex items-center gap-2">
              Peringatan Reorder Point (ROP)
              <span class="px-2 py-0.5 rounded-full bg-rose-200 text-rose-800 text-[9px] font-black">
                {{ stats.low_stock }} Buku Kritis
              </span>
            </h4>
            <p class="text-xs text-rose-600 mt-0.5">
              <span v-if="criticalBooks.length">
                Buku <b>"{{ criticalBooks[0]?.title }}"</b> tersisa {{ criticalBooks[0]?.stock }} pcs (Batas ROP: {{ criticalBooks[0]?.rop_point }}). Segera ajukan restock ke IPH.
              </span>
              <span v-else>
                Ada buku yang mencapai batas minimum stok. Silakan lakukan pengajuan pengadaan buku.
              </span>
            </p>
          </div>
        </div>
        <el-button 
          type="danger" 
          color="#f43f5e" 
          size="small" 
          class="!rounded-xl font-bold shadow-sm"
          @click="$router.push('/admin/books')"
        >
          <Icon icon="solar:box-minimalistic-bold" class="mr-1.5" />
          Lihat & Ajukan Restock
        </el-button>
      </div>
    </transition>

    <!-- 3. AREA UTAMA -->
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
              TOTAL PEMASUKAN KAS: Rp {{ formatPrice(stats.total_income) }}
            </div>
          </div>

          <div style="height: 300px; width: 100%;">
            <v-chart v-if="!loading" :option="chartOption" autoresize />
          </div>
        </section>

        <!-- ANALISIS AGING PIUTANG -->
        <section class="glass-panel p-5 bg-white border border-slate-200">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
              <Icon icon="solar:shield-warning-bold-duotone" class="text-xl text-orange-400" />
              <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Analisis Kolektibilitas Piutang</h3>
            </div>
            <span class="text-[10px] text-slate-400 font-bold">*Khusus Gereja & Penginjil Literatur</span>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
              <div class="flex items-center gap-3">
                <span class="flex h-2.5 w-2.5 rounded-full bg-red-500" :class="{ 'animate-ping': stats.aging_macet > 0 }"></span>
                <span class="text-[11px] font-bold text-slate-600">Piutang Macet (>30 Hari)</span>
              </div>
              <span class="text-sm font-black text-red-500">Rp {{ formatPrice(stats.aging_macet) }}</span>
            </div>
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
              <div class="flex items-center gap-3">
                <span class="flex h-2.5 w-2.5 rounded-full bg-yellow-400"></span>
                <span class="text-[11px] font-bold text-slate-600">Mendekati Jatuh Tempo</span>
              </div>
              <span class="text-sm font-black text-yellow-600">Rp {{ formatPrice(stats.aging_tempo) }}</span>
            </div>
          </div>
        </section>
      </div>

      <!-- KANAN: AKTIVITAS TERAKHIR DENGAN LABEL STATUS CERDAS -->
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
            <div class="h-9 w-9 rounded-lg bg-slate-50 flex items-center justify-center text-[#00A9C3] flex-shrink-0">
              <Icon icon="solar:cart-large-minimalistic-bold" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-[#1B293C] truncate">{{ order.customer?.name || 'Pelanggan Umum' }}</p>
              <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">{{ order.order_number }}</p>
            </div>
            <div class="text-right flex-shrink-0">
              <p class="text-[10px] font-black">Rp {{ formatPrice(order.total_amount) }}</p>
              <!-- STATUS CERDAS: MEMISAHKAN LUNAS, PIUTANG KREDIT, DAN BELUM LUNAS JEMAAT -->
              <p class="text-[8px] font-black uppercase" :class="getOrderStatusBadge(order).color">
                {{ getOrderStatusBadge(order).label }}
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
const stats = ref({ 
  categories: 0, 
  customers: 0, 
  books: 0, 
  new_orders: 0, 
  low_stock: 0, 
  total_piutang: 0, 
  total_income: 0, 
  aging_macet: 0, 
  aging_tempo: 0 
});
const criticalBooks = ref<any[]>([]);
const recentOrders = ref<any[]>([]);
const monthlySales = ref<any[]>([]);

const formatPrice = (n: number) => new Intl.NumberFormat('id-ID').format(n || 0);

// HELPER STATUS TRANSAKSI AKTIVITAS TERAKHIR
const getOrderStatusBadge = (order: any) => {
  if (order.payment_status === 'paid') {
    return { label: 'LUNAS', color: 'text-emerald-500' };
  }
  if (order.payment_status === 'waiting_shipping_fee') {
    return { label: 'INPUT ONGKIR', color: 'text-amber-500' };
  }
  if (order.payment_method === 'kredit' || order.payment_method === 'credit') {
    return { label: 'PIUTANG', color: 'text-red-500' };
  }
  return { label: 'BELUM LUNAS', color: 'text-amber-600' };
};

const statCards = computed(() => [
  { key: "cat", label: "Kategori", value: stats.value.categories, icon: "solar:folder-2-bold", color: "#00A9C3", to: "/admin/categories" },
  { key: "book", label: "Total Buku", value: stats.value.books, icon: "solar:book-bold", color: "#1B293C", to: "/admin/books" },
  { key: "piutang", label: "Total Piutang", value: 'Rp ' + formatPrice(stats.value.total_piutang), icon: "solar:wad-of-money-bold", color: "#f59e0b", to: "/admin/receivables" },
  { key: "ord", label: "Belum Lunas", value: stats.value.new_orders, icon: "solar:clipboard-list-bold", color: "#6366f1", to: "/admin/orders" },
  { key: "rop", label: "Stok Kritis", value: stats.value.low_stock, icon: "solar:bell-bing-bold", color: "#f43f5e", to: "/admin/books" },
]);

const chartOption = computed(() => ({
  tooltip: { 
    trigger: "axis", 
    backgroundColor: "#1B293C", 
    textStyle: { color: "#fff" }, 
    borderRadius: 8,
    formatter: (params: any) => {
      const p = params[0];
      return `${p.name}<br/><b>Rp ${formatPrice(p.value)}</b>`;
    }
  },
  grid: { left: "0%", right: "2%", bottom: "0%", containLabel: true },
  xAxis: { type: "category", data: monthlySales.value.map(m => m.label), axisLine: { show: false } },
  yAxis: { 
    type: "value", 
    splitLine: { lineStyle: { type: 'dashed', color: '#f1f5f9' } },
    axisLabel: {
      formatter: (val: number) => val >= 1000000 ? `${(val / 1000000).toFixed(1)}M` : `${val / 1000}k`
    }
  },
  series: [
    { 
      name: "Penjualan", 
      type: "bar", 
      data: monthlySales.value.map(m => m.total), 
      itemStyle: { color: "#00A9C3", borderRadius: [4, 4, 0, 0] }, 
      barWidth: '35%' 
    },
    { 
      name: "Trend", 
      type: "line", 
      data: monthlySales.value.map(m => m.total), 
      smooth: true, 
      lineStyle: { color: "#1B293C", width: 3 }, 
      showSymbol: false 
    }
  ],
}));

onMounted(async () => {
  try {
    const data: any = await apiFetch("/dashboard");
    if (data) {
      stats.value = data.stats || stats.value;
      criticalBooks.value = data.critical_books || [];
      recentOrders.value = data.recent_orders || [];
      monthlySales.value = data.monthly_sales || [];
      chartYear.value = data.year || new Date().getFullYear();
    }
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
@reference "tailwindcss";

.stat-card-compact {
  @apply flex items-center gap-2.5 sm:gap-3 p-2.5 sm:p-3 rounded-xl bg-white border border-slate-200 shadow-sm transition-all hover:border-[#00A9C3]/40 hover:shadow-md;
}

.stat-icon-box {
  @apply flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-lg flex-shrink-0 shadow-sm;
}

.glass-panel {
  @apply rounded-[1.5rem] shadow-sm bg-white border border-slate-200;
}
</style>