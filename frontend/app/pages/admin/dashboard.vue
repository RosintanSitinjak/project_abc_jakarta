<template>
  <AdminShell>
    <!-- Statistics Cards -->
    <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-5">
      <div v-for="stat in statCards" :key="stat.key" class="stat-card group">
        <div class="stat-icon" :style="{ background: stat.gradient }">
          <Icon :icon="stat.icon" class="text-2xl text-white" />
        </div>
        <div>
          <p
            class="text-xs font-medium uppercase tracking-widest text-[var(--slate-400)]"
          >
            {{ stat.label }}
          </p>
          <p class="mt-1 text-2xl font-bold text-[var(--slate-800)]">
            <span
              v-if="loading"
              class="inline-block h-7 w-16 animate-pulse rounded bg-slate-200"
            />
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
          <p class="text-sm text-[var(--slate-500)]">
            Monthly visitors in {{ chartYear }}
          </p>
        </div>
        <div
          class="flex items-center gap-2 rounded-xl bg-[var(--blue-50)] px-3 py-1.5 text-xs font-semibold text-[var(--blue-700)]"
        >
          <Icon icon="solar:graph-new-up-outline" class="text-base" />
          {{ totalVisitors }} total visitors
        </div>
      </div>

      <div v-if="loading" class="mt-6 flex h-80 items-center justify-center">
        <div class="animate-pulse text-[var(--slate-400)]">Loading chart…</div>
      </div>
      <ClientOnly v-else>
        <div class="mt-6" style="width: 100%; height: 320px">
          <v-chart
            style="width: 100%; height: 100%"
            :option="chartOption"
            autoresize
          />
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
import {
  TitleComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
} from "echarts/components";
import VChart from "vue-echarts";

import AdminShell from "~/components/Admin/Shell.vue";
import { useApi } from "~/composables/useApi";

use([
  CanvasRenderer,
  LineChart,
  BarChart,
  TitleComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
]);

type MonthlyData = { month: number; label: string; total: number };
type DashboardResponse = {
  stats: {
    services: number;
    clients: number;
    products: number;
    portfolios: number;
    visitors: number;
  };
  monthly_visitors: MonthlyData[];
  year: number;
};

const { apiFetch, getErrorMessage } = useApi();

const loading = ref(true);
const stats = ref<DashboardResponse["stats"]>({
  services: 0,
  clients: 0,
  products: 0,
  portfolios: 0,
  visitors: 0,
});
const monthlyVisitors = ref<MonthlyData[]>([]);
const chartYear = ref(new Date().getFullYear());

const statCards = computed(() => [
  {
    key: "services",
    label: "Services",
    value: stats.value.services,
    icon: "solar:suitcase-bold",
    gradient: "linear-gradient(135deg, #6366f1, #818cf8)",
  },
  {
    key: "clients",
    label: "Clients",
    value: stats.value.clients,
    icon: "solar:users-group-two-rounded-bold",
    gradient: "linear-gradient(135deg, #0ea5e9, #38bdf8)",
  },
  {
    key: "products",
    label: "Products",
    value: stats.value.products,
    icon: "solar:bag-2-bold",
    gradient: "linear-gradient(135deg, #f59e0b, #fbbf24)",
  },
  {
    key: "portfolios",
    label: "Portfolios",
    value: stats.value.portfolios,
    icon: "solar:gallery-bold",
    gradient: "linear-gradient(135deg, #10b981, #34d399)",
  },
  {
    key: "visitors",
    label: "Visitors",
    value: stats.value.visitors,
    icon: "solar:eye-bold",
    gradient: "linear-gradient(135deg, #f43f5e, #fb7185)",
  },
]);

const totalVisitors = computed(() => formatNumber(stats.value.visitors));

const formatNumber = (n: number) =>
  n >= 1000 ? `${(n / 1000).toFixed(1)}k` : String(n);

const chartOption = computed(() => {
  const labels = monthlyVisitors.value.map((m) => m.label);
  const data = monthlyVisitors.value.map((m) => m.total);

  return {
    tooltip: {
      trigger: "axis",
      backgroundColor: "rgba(15, 23, 42, 0.85)",
      borderColor: "transparent",
      textStyle: { color: "#f1f5f9", fontSize: 13 },
      axisPointer: {
        type: "cross",
        crossStyle: { color: "#94a3b8" },
      },
    },
    grid: {
      left: "3%",
      right: "4%",
      bottom: "3%",
      containLabel: true,
    },
    xAxis: {
      type: "category",
      data: labels,
      boundaryGap: true,
      axisLine: { lineStyle: { color: "#e2e8f0" } },
      axisLabel: { color: "#64748b", fontSize: 12, fontWeight: 500 },
      axisTick: { show: false },
    },
    yAxis: {
      type: "value",
      minInterval: 1,
      splitLine: { lineStyle: { color: "#f1f5f9", type: "dashed" } },
      axisLabel: { color: "#94a3b8", fontSize: 12 },
    },
    series: [
      {
        name: "Visitors",
        type: "bar",
        data,
        barWidth: "45%",
        itemStyle: {
          borderRadius: [6, 6, 0, 0],
          color: {
            type: "linear",
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: "#6366f1" },
              { offset: 1, color: "#818cf8" },
            ],
          },
        },
        emphasis: {
          itemStyle: {
            color: {
              type: "linear",
              x: 0,
              y: 0,
              x2: 0,
              y2: 1,
              colorStops: [
                { offset: 0, color: "#4f46e5" },
                { offset: 1, color: "#6366f1" },
              ],
            },
          },
        },
      },
      {
        name: "Trend",
        type: "line",
        data,
        smooth: true,
        symbol: "circle",
        symbolSize: 8,
        lineStyle: { width: 3, color: "#f43f5e" },
        itemStyle: { color: "#f43f5e", borderWidth: 2, borderColor: "#fff" },
        areaStyle: {
          color: {
            type: "linear",
            x: 0,
            y: 0,
            x2: 0,
            y2: 1,
            colorStops: [
              { offset: 0, color: "rgba(244,63,94,0.25)" },
              { offset: 1, color: "rgba(244,63,94,0.02)" },
            ],
          },
        },
      },
    ],
    animationDuration: 1200,
    animationEasing: "cubicOut" as const,
  };
});

onMounted(async () => {
  try {
    const data = await apiFetch<DashboardResponse>("/dashboard");
    stats.value = data.stats;
    monthlyVisitors.value = data.monthly_visitors;
    chartYear.value = data.year;
  } catch (error) {
    console.error(
      "Failed to load dashboard:",
      getErrorMessage(error, "Unknown error")
    );
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-radius: 1.25rem;
  background: rgba(255, 255, 255, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(12px);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 3rem;
  height: 3rem;
  border-radius: 0.875rem;
  flex-shrink: 0;
}
</style>
