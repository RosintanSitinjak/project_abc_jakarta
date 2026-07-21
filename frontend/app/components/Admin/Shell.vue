<template>
  <div class="min-h-screen bg-[#f8fafc]">
    <div class="relative overflow-hidden">
      <!-- Ornamen Background Lembut -->
      <div class="pointer-events-none absolute -left-32 top-24 h-72 w-72 rounded-full bg-[#00A9C3]/5 blur-3xl" />
      <div class="pointer-events-none absolute right-0 top-40 h-80 w-80 rounded-full bg-[#1B293C]/5 blur-3xl" />

      <div class="relative mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <!-- 1. HEADER -->
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-6">
          <div class="flex items-center gap-4">
            <!-- Mobile Toggle -->
            <button
              class="flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-[#1B293C] shadow-sm lg:hidden hover:bg-slate-50"
              @click="toggleSidebar"
            >
              <Icon icon="solar:hamburger-menu-outline" class="text-xl" />
            </button>

            <!-- BRANDING AREA -->
            <div class="flex items-center">
              <NuxtLink to="/admin/dashboard" class="flex items-center transition-transform hover:scale-105">
                <img 
                  src="/images/logo-abc.png" 
                  class="h-10 md:h-11 w-auto object-contain" 
                  alt="Logo ABC Jakarta" 
                />
              </NuxtLink>

              <div class="hidden md:block h-8 w-px bg-slate-200 mx-5"></div>

              <div class="hidden md:flex flex-col justify-center">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#00A9C3] leading-none mb-1">
                  Adventist Book Center
                </p>
                <h1 class="text-lg font-black text-[#1B293C] leading-none tracking-tight uppercase">
                  Jakarta
                </h1>
              </div>
            </div>
          </div>

          <!-- Bagian Kanan: Tanggal & User Profile -->
          <div class="flex items-center gap-3">
            <!-- TANGGAL -->
            <div class="hidden items-center gap-2 rounded-xl bg-white border border-slate-100 px-4 py-2 text-[11px] font-bold text-slate-600 shadow-sm md:flex uppercase tracking-wider">
              <Icon icon="solar:calendar-outline" class="text-base text-[#00A9C3]" />
              <span>{{ todayLabel }}</span>
            </div>

            <!-- USER DROPDOWN -->
            <ClientOnly>
              <el-dropdown trigger="click">
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm cursor-pointer hover:border-[#00A9C3]/40 transition-all">
                  <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#00A9C3] text-[10px] font-bold text-white shadow-md">
                    {{ userInitials }}
                  </div>
                  <div class="hidden text-xs sm:block text-left">
                    <p class="font-bold text-[#1B293C]">{{ userName }}</p>
                    <p class="text-[9px] uppercase font-bold text-[#00A9C3] tracking-wider">{{ userRoleLabel }}</p>
                  </div>
                  <Icon icon="solar:alt-arrow-down-outline" class="text-base text-slate-400" />
                </div>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item @click="logout" class="!text-red-500">
                      <Icon icon="solar:logout-outline" class="mr-2" /> Keluar
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </ClientOnly>
          </div>
        </header>

        <div class="mt-8 grid gap-6 lg:grid-cols-[250px_1fr]">
          <!-- 2. SIDEBAR MENU -->
          <aside
            :class="[
              'glass-panel fixed inset-y-0 left-0 z-50 w-64 -translate-x-full p-5 transition-transform lg:sticky lg:top-8 lg:self-start lg:translate-x-0',
              isSidebarOpen ? 'translate-x-0 shadow-2xl' : '',
            ]"
          >
            <div class="flex items-center justify-between lg:hidden mb-6">
              <p class="text-[10px] uppercase tracking-[0.2em] text-[#00A9C3] font-bold">Navigasi</p>
              <button class="text-slate-400" @click="toggleSidebar">
                <Icon icon="solar:close-circle-outline" class="text-2xl" />
              </button>
            </div>

            <ClientOnly>
              <nav class="space-y-1">
                <NuxtLink
                  v-for="item in navItems"
                  :key="item.label"
                  :to="item.to"
                  class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-semibold transition-all duration-300"
                  :class="
                    isActiveRoute(item.to)
                      ? 'bg-[#00A9C3] text-white shadow-lg shadow-cyan-500/20 translate-x-1'
                      : 'text-slate-500 hover:bg-slate-50 hover:text-[#1B293C]'
                  "
                  @click="closeSidebar"
                >
                  <Icon 
                    :icon="item.icon" 
                    class="text-xl" 
                    :class="isActiveRoute(item.to) ? 'text-white' : 'text-slate-400'" 
                  />
                  <span>{{ item.label }}</span>
                </NuxtLink>
              </nav>
            </ClientOnly>
          </aside>

          <!-- 3. MAIN CONTENT -->
          <main class="min-w-0">
            <slot />
          </main>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { computed, onMounted, ref } from "vue";
import { useApi } from "~/composables/useApi";
import { Role, roleLabels } from "~/types/enums";

// UI Logic
const isSidebarOpen = ref(false);
const toggleSidebar = () => { isSidebarOpen.value = !isSidebarOpen.value; };
const closeSidebar = () => { isSidebarOpen.value = false; };
const todayLabel = computed(() => {
  return new Date().toLocaleDateString("id-ID", { weekday: "long", month: "long", day: "numeric" });
});

// Auth & Router
const route = useRoute();
const router = useRouter();
const { apiFetch } = useApi();
const authUser = useState<any>("auth-user", () => null);

const userName = computed(() => authUser.value?.name || "Admin");
const userRoleLabel = computed(() => {
  const roleValue = authUser.value?.role as Role | undefined;
  return roleValue ? roleLabels[roleValue] : "User";
});
const userInitials = computed(() => {
  const name = userName.value.trim();
  return name ? name.split(" ").slice(0, 2).map(p => p[0]?.toUpperCase()).join("") : "A";
});

const logout = async () => {
  try { await apiFetch("/auth/logout", { method: "POST" }); } finally { authUser.value = null; router.push("/login"); }
};

// Navigation (URUTAN TERBARU & MENU PIUTANG)
const isActiveRoute = (path: string) => route.path === path;
const navItems = computed(() => {
  const items = [
    { label: "Dashboard", icon: "solar:widget-2-linear", to: "/admin/dashboard" },
    
    // Kelompok Master Data
    { label: "Kategori", icon: "solar:folder-2-linear", to: "/admin/categories" },
    { label: "Katalog Buku", icon: "solar:book-linear", to: "/admin/books" },
    { label: "Gereja & Jemaat", icon: "solar:users-group-rounded-linear", to: "/admin/customers" },
    
    // Kelompok Operasional & Keuangan
    { label: "Pesanan Masuk", icon: "solar:mailbox-linear", to: "/admin/orders" },
    { label: "Buku Piutang", icon: "solar:bill-list-linear", to: "/admin/receivables" },
    
    // Kelompok Konten
    { label: "Literasi/Berita", icon: "solar:document-text-linear", to: "/admin/articles" },
  ];
  
  // Manajemen User di taruh paling bawah (Khusus Admin/Owner)
  if (authUser.value?.role === Role.Owner || authUser.value?.role === Role.Admin) {
    items.push({ label: "Manajemen User", icon: "solar:user-id-linear", to: "/admin/users" });
  }
  return items;
});

onMounted(async () => {
  if (authUser.value) return;
  try { authUser.value = await apiFetch("/user"); } catch { authUser.value = null; }
});
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel {
  @apply rounded-[1.5rem] border border-slate-200 bg-white shadow-sm transition-all;
}
</style>
