<template>
  <AdminShell>
    <!-- Statistics Cards -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
      <div v-for="stat in statCards" :key="stat.key" class="stat-card group">
        <!-- Stat Icon - Ukuran dikecilkan sedikit -->
        <div class="stat-icon" :style="{ background: stat.gradient }">
          <Icon :icon="stat.icon" class="text-xl text-white" />
        </div>
        <div class="flex-1 min-w-0"> <!-- Tambahan agar teks tidak meluap -->
          <!-- Label: Dikecilkan ke 10px -->
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">
            {{ stat.label }}
          </p>
          <!-- Value: Dikecilkan dari 2xl ke lg -->
          <p class="mt-0.5 text-lg font-black text-slate-800">
            <span v-if="loading" class="inline-block h-5 w-12 animate-pulse rounded bg-slate-100" />
            <span v-else>{{ formatNumber(stat.value) }}</span>
          </p>
        </div>
      </div>
    </section>

    <!-- Visitor Chart -->
    <section class="glass-panel mt-6 p-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h2 class="section-title">Visitor Analytics</h2>
          <p class="text-xs text-slate-500">
            Monthly visitors in {{ chartYear }}
          </p>
        </div>
        <div class="flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-1.5 text-[10px] font-bold text-[#1e293b] border border-slate-200">
          <Icon icon="solar:graph-new-up-outline" class="text-sm text-[#3b5d95]" />
          {{ totalVisitors }} TOTAL VISITORS
        </div>
      </div>

      <div v-if="loading" class="mt-6 flex h-80 items-center justify-center">
        <div class="flex flex-col items-center gap-2">
           <div class="w-6 h-6 border-2 border-slate-200 border-t-[#3b5d95] rounded-full animate-spin"></div>
           <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Loading chart…</p>
        </div>
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

type MonthlyData = { month: number; label: string; total: number };
type DashboardResponse = {
  stats: { services: number; clients: number; products: number; portfolios: number; visitors: number; };
  monthly_visitors: MonthlyData[];
  year: number;
};

const { apiFetch, getErrorMessage } = useApi();
const loading = ref(true);
const stats = ref<DashboardResponse["stats"]>({ services: 0, clients: 0, products: 0, portfolios: 0, visitors: 0 });
const monthlyVisitors = ref<MonthlyData[]>([]);
const chartYear = ref(new Date().getFullYear());

const statCards = computed(() => [
  { key: "services", label: "Services", value: stats.value.services, icon: "solar:suitcase-bold", gradient: "linear-gradient(135deg, #3b5d95, #5a7bb5)" },
  { key: "clients", label: "Clients", value: stats.value.clients, icon: "solar:users-group-two-rounded-bold", gradient: "linear-gradient(135deg, #1e293b, #334155)" },
  { key: "products", label: "Products", value: stats.value.products, icon: "solar:bag-2-bold", gradient: "linear-gradient(135deg, #475569, #64748b)" },
  { key: "portfolios", label: "Portfolios", value: stats.value.portfolios, icon: "solar:gallery-bold", gradient: "linear-gradient(135deg, #3b5d95, #1e293b)" },
  { key: "visitors", label: "Visitors", value: stats.value.visitors, icon: "solar:eye-bold", gradient: "linear-gradient(135deg, #0f172a, #1e293b)" },
]);

const totalVisitors = computed(() => formatNumber(stats.value.visitors));
const formatNumber = (n: number) => n >= 1000 ? `${(n / 1000).toFixed(1)}k` : String(n);

const chartOption = computed(() => {
  const labels = monthlyVisitors.value.map((m) => m.label);
  const data = monthlyVisitors.value.map((m) => m.total);
  return {
    tooltip: { trigger: "axis", backgroundColor: "#1e293b", textStyle: { color: "#fff", fontSize: 12 } },
    grid: { left: "0%", right: "2%", bottom: "0%", containLabel: true },
    xAxis: { type: "category", data: labels, axisLine: { lineStyle: { color: "#f1f5f9" } }, axisLabel: { color: "#94a3b8", fontSize: 10, fontWeight: 600 } },
    yAxis: { type: "value", splitLine: { lineStyle: { color: "#f8fafc" } }, axisLabel: { color: "#94a3b8", fontSize: 10 } },
    series: [
      { name: "Visitors", type: "bar", data, barWidth: "40%", itemStyle: { borderRadius: [4, 4, 0, 0], color: { type: "linear", x: 0, y: 0, x2: 0, y2: 1, colorStops: [{ offset: 0, color: "#3b5d95" }, { offset: 1, color: "#1e293b" }] } } },
      { name: "Trend", type: "line", data, smooth: true, showSymbol: false, lineStyle: { width: 2, color: "#3b5d95" } }
    ],
  };
});

onMounted(async () => {
  try {
    const data = await apiFetch<DashboardResponse>("/dashboard");
    stats.value = data.stats;
    monthlyVisitors.value = data.monthly_visitors;
    chartYear.value = data.year;
  } catch (error) {
    console.error("Dashboard error:", getErrorMessage(error, "Unknown error"));
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
@reference "tailwindcss";

.stat-card {
  display: flex;
  align-items: center;
  gap: 0.75rem; /* Gap diperkecil agar tidak terlalu lebar */
  padding: 1.25rem;
  border-radius: 1.5rem;
  background: white;
  border: 1px solid #f1f5f9;
  box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.75rem; /* Ikon dikecilkan sedikit */
  height: 2.75rem;
  border-radius: 1rem;
  flex-shrink: 0;
}

.section-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1e293b;
  letter-spacing: -0.02em;
}

.glass-panel {
  border-radius: 2rem;
  border: 1px solid white;
  background-color: white;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.02);
}
</style>