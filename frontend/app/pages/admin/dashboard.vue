<template>
  <AdminShell>
    <!-- Statistics Cards -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
      <div v-for="stat in statCards" :key="stat.key" class="stat-card group">
        <div class="stat-icon" :style="{ background: stat.color }">
          <Icon :icon="stat.icon" class="text-xl text-white" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">
            {{ stat.label }}
          </p> 

          <!-- <p class="text-[9px] font-bold uppercase tracking-tight text-slate-400 leading-tight">
            {{ stat.label }}
          </p> -->
          <p class="mt-0.5 text-lg font-black text-[#1B293C]">
            <span v-if="loading" class="inline-block h-5 w-12 animate-pulse rounded bg-slate-100" />
            <span v-else>{{ stat.value }}</span>
          </p>
        </div>
      </div>
    </section>

    <!-- Grafik Penjualan -->
    <section class="glass-panel mt-6 p-6 bg-white">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h2 class="text-lg font-bold text-[#1B293C]">Statistik Literasi</h2>
          <p class="text-xs text-slate-500">Data bulanan tahun {{ chartYear }}</p>
        </div>
        <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-1.5 text-[10px] font-bold text-[#1B293C] border border-slate-200">
          <Icon icon="solar:graph-new-up-linear" class="text-sm text-[#00A9C3]" />
          {{ stats.visitors }} TOTAL PENGUNJUNG
        </div>
      </div>

      <div v-if="loading" class="mt-6 flex h-80 items-center justify-center">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest animate-pulse">Memuat Grafik…</p>
      </div>
      <ClientOnly v-else>
        <div class="mt-6" style="width: 100%; height: 320px">
          <v-chart style="width: 100%; height: 100%" :option="chartOption" autoresize />
        </div>
      </ClientOnly>
    </section>
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
const stats = ref({ services: 0, clients: 0, products: 0, portfolios: 0, visitors: 0 });
const monthlyVisitors = ref<any[]>([]);

const statCards = computed(() => [
  { key: "cat", label: "Kategori", value: stats.value.services, icon: "solar:folder-2-bold", color: "#00A9C3" },
  { key: "cli", label: "Gereja & Jemaat", value: stats.value.clients, icon: "solar:users-group-rounded-bold", color: "#1B293C" },
  { key: "book", label: "Stok Buku", value: stats.value.products, icon: "solar:book-bold", color: "#00A9C3" },
  { key: "ord", label: "Pesanan Baru", value: stats.value.portfolios, icon: "solar:mailbox-bold", color: "#1B293C" },
  { key: "rop", label: "Stok Kritis", value: stats.value.visitors, icon: "solar:bell-bing-bold", color: "#f43f5e" },
]);

const chartOption = computed(() => ({
  tooltip: { trigger: "axis", backgroundColor: "#1B293C", textStyle: { color: "#fff" } },
  grid: { left: "0%", right: "2%", bottom: "0%", containLabel: true },
  xAxis: { type: "category", data: monthlyVisitors.value.map(m => m.label) },
  yAxis: { type: "value" },
  series: [
    { name: "Pesanan", type: "bar", data: monthlyVisitors.value.map(m => m.total), itemStyle: { color: "#00A9C3", borderRadius: [4, 4, 0, 0] } },
    { name: "Tren", type: "line", data: monthlyVisitors.value.map(m => m.total), smooth: true, lineStyle: { color: "#1B293C" } }
  ],
}));

onMounted(async () => {
  try {
    const data: any = await apiFetch("/dashboard");
    stats.value = data.stats;
    monthlyVisitors.value = data.monthly_visitors;
  } finally { loading.value = false; }
});
</script>

<!-- <style scoped>
@reference "tailwindcss";
.stat-card { @apply flex items-center gap-3 p-5 rounded-2xl bg-white border border-slate-100 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md; }
.stat-icon { @apply flex items-center justify-center w-11 h-11 rounded-xl flex-shrink-0 shadow-lg shadow-slate-200; }
.glass-panel { @apply rounded-[1.5rem] border border-slate-200; }
</style> -->

<style scoped>
@reference "tailwindcss";

.stat-card { 
  @apply flex items-center gap-2 p-3 rounded-2xl bg-white border border-slate-100 shadow-sm transition-all hover:-translate-y-1; 
  /* gap dikecilkan dari 3 ke 2, padding dikecilkan dari 5 ke 3 agar ruang teks lebih luas */
  min-width: 0; 
}

.stat-icon { 
  @apply flex items-center justify-center w-9 h-9 rounded-xl flex-shrink-0; 
  /* Ukuran icon dikecilkan sedikit dari w-11 ke w-9 */
}

/* Tambahan khusus untuk teks label */
.stat-card p {
  display: -webkit-box;
  -webkit-line-clamp: 2; /* Jika kepanjangan, teks akan turun ke bawah (maks 2 baris) */
  -webkit-box-orient: vertical;  
  overflow: hidden;
  white-space: normal; /* Mengizinkan teks turun ke baris baru */
}
</style>