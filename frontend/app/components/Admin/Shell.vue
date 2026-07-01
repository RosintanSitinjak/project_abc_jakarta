<template>
  <div class="min-h-screen bg-[#f8fafc]">
    <div class="relative overflow-hidden">
      <!-- Ornamen Background -->
      <div class="pointer-events-none absolute -left-32 top-24 h-72 w-72 rounded-full bg-blue-100/40 blur-3xl" />
      <div class="pointer-events-none absolute right-0 top-40 h-80 w-80 rounded-full bg-blue-50/50 blur-3xl" />

      <div class="relative mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <!-- 1. HEADER: Logo & Teks Sejajar Presisi -->
        <header class="flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <!-- Mobile Toggle -->
            <button
              class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/60 bg-white/80 text-[#1e293b] shadow-sm lg:hidden hover:bg-white transition-all"
              @click="toggleSidebar"
            >
              <Icon icon="solar:hamburger-menu-outline" class="text-xl" />
            </button>

            <!-- BRANDING AREA -->
            <div class="flex items-center">
              <NuxtLink to="/admin/dashboard" class="flex items-center transition-transform hover:scale-105">
                <img 
                  src="/images/logofixx.png" 
                  class="h-10 md:h-12 w-auto object-contain" 
                  alt="Intan-S Digital Logo" 
                />
              </NuxtLink>

              <!-- Garis Pembatas Vertikal -->
              <div class="hidden md:block h-10 w-px bg-slate-200 mx-5"></div>

              <!-- Teks Branding -->
              <div class="hidden md:flex flex-col justify-center">
                <p class="text-[9px] font-bold uppercase tracking-[0.25em] text-slate-400 leading-none mb-1.5">
                  Intan-S Digital Solutions
                </p>
                <h1 class="text-lg md:text-xl font-black text-[#1e293b] leading-none tracking-tight">
                  Admin Dashboard
                </h1>
              </div>
            </div>
          </div>

          <!-- Bagian Kanan: Tanggal & User Profile -->
          <div class="flex items-center gap-3">
            <!-- TANGGAL -->
            <div class="hidden items-center gap-2 rounded-2xl border border-white/60 bg-white/80 px-4 py-2.5 text-[11px] font-bold text-slate-500 shadow-sm md:flex uppercase tracking-wider">
              <Icon icon="solar:calendar-outline" class="text-lg text-[#3b5d95]" />
              <span>{{ todayLabel }}</span>
            </div>

            <!-- USER DROPDOWN -->
            <ClientOnly>
              <el-dropdown trigger="click">
                <div class="flex items-center gap-3 rounded-2xl border border-white/60 bg-white/80 px-3 py-2 shadow-sm cursor-pointer hover:bg-white transition-all">
                  <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#3b5d95] text-xs font-bold text-white shadow-lg">
                    {{ userInitials }}
                  </div>
                  <div class="hidden text-sm sm:block">
                    <p class="font-bold text-[#1e293b] leading-tight">{{ userName }}</p>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-tighter">{{ userRoleLabel }}</p>
                  </div>
                  <Icon icon="solar:alt-arrow-down-outline" class="text-lg text-slate-400" />
                </div>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item @click="openSettings">
                      <Icon icon="solar:settings-outline" class="mr-2" /> User Settings
                    </el-dropdown-item>
                    <el-dropdown-item divided @click="logout" class="!text-red-500">
                      <Icon icon="solar:logout-outline" class="mr-2" /> Logout
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </ClientOnly>
          </div>
        </header>

        <div class="mt-8 grid gap-6 lg:grid-cols-[260px_1fr]">
          <!-- 2. SIDEBAR MENU -->
          <aside
            :class="[
              'glass-panel fixed inset-y-0 left-0 z-50 w-64 -translate-x-full p-5 transition-transform lg:sticky lg:top-8 lg:self-start lg:translate-x-0',
              isSidebarOpen ? 'translate-x-0 shadow-2xl' : '',
            ]"
          >
            <div class="flex items-center justify-between lg:hidden mb-6">
              <p class="text-xs uppercase tracking-[0.3em] text-slate-400 font-bold">Navigation</p>
              <button class="text-slate-400" @click="toggleSidebar">
                <Icon icon="solar:close-circle-outline" class="text-2xl" />
              </button>
            </div>

            <ClientOnly>
              <nav class="space-y-1.5">
                <NuxtLink
                  v-for="item in navItems"
                  :key="item.label"
                  :to="item.to"
                  class="flex w-full items-center gap-3 rounded-2xl px-4 py-3.5 text-left text-sm font-semibold no-underline transition-all duration-300"
                  :class="
                    isActiveRoute(item.to)
                      ? 'bg-[#3b5d95] text-white shadow-xl shadow-blue-900/20 translate-x-1'
                      : 'text-slate-500 hover:bg-white hover:text-[#3b5d95]'
                  "
                  @click="closeSidebar"
                >
                  <Icon :icon="item.icon" class="text-xl" />
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

    <!-- Dialog User Settings -->
    <el-dialog v-model="isSettingsOpen" title="User Settings" width="90%" style="max-width: 520px" align-center>
      <el-form ref="settingsFormRef" :model="settingsForm" :rules="settingsRules" label-position="top">
        <el-form-item label="Name" prop="name"><el-input v-model="settingsForm.name" /></el-form-item>
        <el-form-item label="Email" prop="email"><el-input v-model="settingsForm.email" /></el-form-item>
        <el-form-item label="New Password" prop="password"><el-input v-model="settingsForm.password" type="password" show-password /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="isSettingsOpen = false">Cancel</el-button>
        <el-button type="primary" :loading="isSavingSettings" @click="submitSettings">Save</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { ElMessage } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import { computed, onMounted, reactive, ref } from "vue";
import { useApi } from "~/composables/useApi";
import { Role, roleLabels } from "~/types/enums";

// UI Logic
const isSidebarOpen = ref(false);
const toggleSidebar = () => { isSidebarOpen.value = !isSidebarOpen.value; };
const closeSidebar = () => { isSidebarOpen.value = false; };
const todayLabel = computed(() => {
  return new Date().toLocaleDateString("en-US", { weekday: "long", month: "long", day: "numeric" });
});

// Auth & Router
const route = useRoute();
const router = useRouter();
const { apiFetch, getErrorMessage } = useApi();
const authUser = useState<any>("auth-user", () => null);

const userName = computed(() => authUser.value?.name || "Admin");
const userRoleLabel = computed(() => {
  const roleValue = authUser.value?.role as Role | undefined;
  return roleValue && roleLabels[roleValue] ? roleLabels[roleValue] : "User";
});
const userInitials = computed(() => {
  const name = userName.value.trim();
  return name ? name.split(" ").slice(0, 2).map(p => p[0]?.toUpperCase()).join("") : "A";
});

// Settings Logic
const isSettingsOpen = ref(false);
const isSavingSettings = ref(false);
const settingsFormRef = ref<FormInstance>();
const settingsForm = reactive({ name: "", email: "", password: "", passwordConfirmation: "" });
const openSettings = () => {
  settingsForm.name = authUser.value?.name ?? "";
  settingsForm.email = authUser.value?.email ?? "";
  isSettingsOpen.value = true;
};
const logout = async () => {
  try { await apiFetch("/auth/logout", { method: "POST" }); } finally { authUser.value = null; router.push("/login"); }
};

// Navigation
const isActiveRoute = (path: string) => route.path === path;
const navItems = computed(() => {
  const items = [
    { label: "Dashboard", icon: "solar:widget-2-outline", to: "/admin/dashboard" },
    { label: "Services", icon: "solar:suitcase-outline", to: "/admin/services" },
    { label: "Clients", icon: "solar:users-group-two-rounded-outline", to: "/admin/clients" },
    { label: "Products", icon: "solar:bag-2-outline", to: "/admin/products" },
    { label: "Articles", icon: "solar:document-text-outline", to: "/admin/articles" },
    { label: "Inquiries", icon: "solar:mailbox-outline", to: "/admin/contacts" },
  ];
  if (authUser.value?.role === Role.Owner || authUser.value?.role === Role.Admin) {
    items.push({ label: "Users", icon: "solar:user-id-outline", to: "/admin/users" });
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
  @apply rounded-[2rem] border border-white bg-white/70 shadow-[0_8px_30px_rgb(0,0,0,0.02)] backdrop-blur-xl;
}
</style>