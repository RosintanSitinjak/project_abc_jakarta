<template>
  <div class="min-h-screen bg-[var(--slate-50)]">
    <div class="relative overflow-hidden">
      <div
        class="pointer-events-none absolute -left-32 top-24 h-72 w-72 rounded-full bg-blue-100/60 blur-3xl"
      />
      <div
        class="pointer-events-none absolute right-0 top-40 h-80 w-80 rounded-full bg-blue-50/70 blur-3xl"
      />

      <div class="relative mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <header class="flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <button
              class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/60 bg-white/80 text-[var(--blue-900)] shadow-sm lg:hidden"
              @click="toggleSidebar"
            >
              <Icon icon="solar:hamburger-menu-outline" class="text-xl" />
            </button>
            <div class="flex items-center gap-3">
              <span class="">
                <img class="h-10" src="/logo.png" alt="Lamsolusi" />
              </span>
              <div class="hidden md:block">
                <p
                  class="text-xs uppercase tracking-[0.35em] text-[var(--blue-900)]"
                >
                  Lamjaya Global Solusi
                </p>
                <h1 class="text-2xl font-semibold">Admin Dashboard</h1>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <div
              class="hidden items-center gap-2 rounded-2xl border border-white/60 bg-white/80 px-4 py-2 text-sm text-[var(--slate-500)] shadow-sm md:flex"
            >
              <Icon icon="solar:calendar-outline" class="text-lg" />
              <span>{{ todayLabel }}</span>
            </div>

            <ClientOnly>
              <el-dropdown trigger="click">
                <div
                  class="flex items-center gap-3 rounded-2xl border border-white/60 bg-white/80 px-3 py-2 shadow-sm"
                >
                  <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--blue-100)] text-sm font-semibold text-[var(--blue-900)]"
                  >
                    {{ userInitials }}
                  </div>
                  <div class="hidden text-sm sm:block">
                    <p class="font-semibold text-[var(--slate-900)]">
                      {{ userName }}
                    </p>
                    <p class="text-xs text-[var(--slate-500)]">
                      {{ userRoleLabel }}
                    </p>
                  </div>
                  <Icon
                    icon="solar:alt-arrow-down-outline"
                    class="text-lg text-[var(--slate-500)]"
                  />
                </div>
                <template #fallback>
                  <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--blue-100)] text-sm font-semibold text-[var(--blue-900)]"
                  >
                    A
                  </div>
                </template>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item @click="openSettings"
                      >User Settings</el-dropdown-item
                    >
                    <el-dropdown-item divided @click="logout"
                      >Logout</el-dropdown-item
                    >
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </ClientOnly>
          </div>
        </header>

        <div class="mt-8 grid gap-6 lg:grid-cols-[260px_1fr] overflow-hidden">
          <aside
            :class="[
              'glass-panel fixed inset-y-0 left-0 z-30 w-64 -translate-x-full p-5 transition-transform lg:sticky lg:top-8 lg:self-start lg:translate-x-0',
              isSidebarOpen ? 'translate-x-0' : '',
            ]"
          >
            <div class="flex items-center justify-between lg:hidden">
              <p
                class="text-xs uppercase tracking-[0.3em] text-[var(--slate-500)]"
              >
                Navigation
              </p>
              <button class="text-[var(--slate-500)]" @click="toggleSidebar">
                <Icon icon="solar:close-circle-outline" class="text-xl" />
              </button>
            </div>

            <ClientOnly>
              <nav class="mt-6 space-y-2">
                <NuxtLink
                  v-for="item in navItems"
                  :key="item.label"
                  :to="item.to"
                  class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left text-sm font-medium no-underline transition"
                  :class="
                    isActiveRoute(item.to)
                      ? 'bg-blue-900 text-white shadow-lg shadow-blue-500/20'
                      : 'text-[var(--slate-600)] hover:bg-white'
                  "
                  @click="closeSidebar"
                >
                  <Icon :icon="item.icon" class="text-lg" />
                  <span>{{ item.label }}</span>
                </NuxtLink>
              </nav>
            </ClientOnly>
          </aside>

          <main class="min-w-0 space-y-6">
            <slot />
          </main>
        </div>
      </div>
    </div>

    <el-dialog
      v-model="isSettingsOpen"
      title="User Settings"
      width="90%"
      style="max-width: 520px"
      align-center
      destroy-on-close
      @closed="resetSettingsForm"
    >
      <el-form
        ref="settingsFormRef"
        :model="settingsForm"
        :rules="settingsRules"
        label-position="top"
      >
        <el-form-item label="Name" prop="name">
          <el-input v-model="settingsForm.name" placeholder="Admin Name" />
        </el-form-item>
        <el-form-item label="Email" prop="email">
          <el-input
            v-model="settingsForm.email"
            placeholder="admin@lamsolusi.com"
          />
        </el-form-item>
        <el-form-item label="New Password" prop="password">
          <el-input
            v-model="settingsForm.password"
            type="password"
            show-password
            placeholder="Leave blank to keep current"
          />
        </el-form-item>
        <el-form-item label="Confirm Password" prop="passwordConfirmation">
          <el-input
            v-model="settingsForm.passwordConfirmation"
            type="password"
            show-password
            placeholder="Confirm new password"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="isSettingsOpen = false">Cancel</el-button>
        <el-button
          type="primary"
          :loading="isSavingSettings"
          @click="submitSettings"
          >Save</el-button
        >
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

const isSidebarOpen = ref(false);
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

const closeSidebar = () => {
  isSidebarOpen.value = false;
};

const todayLabel = computed(() => {
  const now = new Date();
  return now.toLocaleDateString("en-US", {
    weekday: "long",
    month: "long",
    day: "numeric",
  });
});

const route = useRoute();
const router = useRouter();
const { apiFetch, getErrorMessage } = useApi();

const authUser = useState<{
  name?: string;
  email?: string;
  role?: number;
} | null>("auth-user", () => null);

const userName = computed(() => authUser.value?.name || "Admin");
const userRoleLabel = computed(() => {
  const roleValue = authUser.value?.role as Role | undefined;
  if (roleValue && roleLabels[roleValue]) {
    return roleLabels[roleValue];
  }
  return "User";
});
const userInitials = computed(() => {
  const name = userName.value.trim();
  if (!name) return "A";
  const parts = name.split(" ");
  const initials = parts
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join("");
  return initials || "A";
});

const isSettingsOpen = ref(false);
const isSavingSettings = ref(false);
const settingsFormRef = ref<FormInstance>();
const settingsForm = reactive({
  name: "",
  email: "",
  password: "",
  passwordConfirmation: "",
});

const settingsRules: FormRules<typeof settingsForm> = {
  name: [{ required: true, message: "Name is required", trigger: "blur" }],
  email: [
    { required: true, message: "Email is required", trigger: "blur" },
    { type: "email", message: "Invalid email", trigger: "blur" },
  ],
  password: [
    {
      validator: (_rule, value, callback) => {
        if (value && value.length < 8) {
          callback(new Error("Password must be at least 8 characters"));
          return;
        }
        callback();
      },
      trigger: "blur",
    },
  ],
  passwordConfirmation: [
    {
      validator: (_rule, value, callback) => {
        if (settingsForm.password && value !== settingsForm.password) {
          callback(new Error("Password confirmation does not match"));
          return;
        }
        callback();
      },
      trigger: "blur",
    },
  ],
};

const openSettings = () => {
  settingsForm.name = authUser.value?.name ?? "";
  settingsForm.email = authUser.value?.email ?? "";
  settingsForm.password = "";
  settingsForm.passwordConfirmation = "";
  isSettingsOpen.value = true;
};

const resetSettingsForm = () => {
  settingsFormRef.value?.clearValidate();
};

const submitSettings = async () => {
  const formEl = settingsFormRef.value;
  if (!formEl) return;
  const isValid = await formEl.validate().catch(() => false);
  if (!isValid) return;

  isSavingSettings.value = true;
  try {
    const payload: Record<string, string> = {
      name: settingsForm.name,
      email: settingsForm.email,
    };
    if (settingsForm.password) {
      payload.password = settingsForm.password;
      payload.password_confirmation = settingsForm.passwordConfirmation;
    }
    const updated = await apiFetch<{
      name?: string;
      email?: string;
      role?: number;
    }>("/user", {
      method: "PATCH",
      body: payload,
    });
    authUser.value = updated;
    ElMessage.success("User settings updated");
    isSettingsOpen.value = false;
  } catch (error) {
    ElMessage.error(getErrorMessage(error, "Failed to update user settings."));
  } finally {
    isSavingSettings.value = false;
  }
};

const logout = async () => {
  try {
    await apiFetch("/auth/logout", { method: "POST" });
  } catch (error) {
    ElMessage.error(getErrorMessage(error, "Failed to logout."));
  } finally {
    authUser.value = null;
    await router.push("/login");
  }
};

const isActiveRoute = (path: string) => route.path === path;

const navItems = computed(() => {
  const items = [
    {
      label: "Dashboard",
      icon: "solar:widget-2-outline",
      to: "/admin/dashboard",
    },
    // { label: 'Site Settings', icon: 'solar:settings-outline', to: '/admin/site-settings' },
    {
      label: "Services",
      icon: "solar:suitcase-outline",
      to: "/admin/services",
    },
    {
      label: "Clients",
      icon: "solar:users-group-two-rounded-outline",
      to: "/admin/clients",
    },
    { label: "Products", icon: "solar:bag-2-outline", to: "/admin/products" },
    // { label: 'Portfolios', icon: 'solar:gallery-outline', to: '/admin/portfolios' },
    {
      label: "Articles",
      icon: "solar:document-text-outline",
      to: "/admin/articles",
    },
    {
      label: "Inquiries",
      icon: "solar:mailbox-outline",
      to: "/admin/contacts",
    },
  ];

  const myRole = authUser.value?.role;
  if (myRole === Role.Owner || myRole === Role.Admin) {
    items.push({
      label: "Users",
      icon: "solar:user-id-outline",
      to: "/admin/users",
    });
  }

  return items;
});

onMounted(async () => {
  if (authUser.value) return;
  try {
    const user = await apiFetch<{
      name?: string;
      email?: string;
      role?: number;
    }>("/user");
    authUser.value = user;
  } catch {
    authUser.value = null;
  }
});
</script>
