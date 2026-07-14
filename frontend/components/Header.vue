<template>
  <header class="sticky top-0 z-50">
    <div class="bg-red-500 text-white text-center py-1">INI NAVBAR YANG SAYA EDIT</div>
    <!-- 1. TOP BAR (INFO KONTAK) -->
    <div class="hidden md:flex items-center justify-between bg-[#1b293c] text-white text-xs px-6 lg:px-20 py-2">
      <div class="flex items-center gap-6">
        <div class="flex items-center gap-2 opacity-80">
          <Icon icon="tabler:mail" class="text-[#00a9c3]"/>
          <span>info@abcjakarta.com</span>
        </div>
        <div class="flex items-center gap-2 opacity-80">
          <Icon icon="tabler:phone" class="text-[#00a9c3]"/>
          <span>+62 881 0822 03778</span>
        </div>
      </div>
      <div class="flex items-center gap-2 opacity-80">
        <Icon icon="tabler:map-pin" class="text-[#00a9c3]"/>
        <span>Jakarta, Indonesia</span>
      </div>
    </div>

    <!-- 2. MAIN NAVBAR -->
    <div class="bg-white/90 backdrop-blur-md shadow-sm border-b border-gray-100">
      <div class="flex items-center justify-between px-6 lg:px-20 py-4">
        
        <!-- LOGO -->
        <NuxtLink to="/" class="flex items-center">
          <img src="/images/logo.png" alt="ABC Logo" class="h-10 w-auto" />
        </NuxtLink>

        <!-- DESKTOP MENU -->
        <nav class="hidden lg:block">
          <ul class="flex items-center gap-8">
            <li v-for="item in navItems" :key="item.to">
              <NuxtLink :to="item.to" class="text-sm font-bold text-gray-600 hover:text-[#00a9c3] transition-colors uppercase tracking-widest">
                {{ item.label }}
              </NuxtLink>
            </li>
          </ul>
        </nav>

        <!-- DESKTOP AUTH & ACTIONS -->
        <div class="hidden lg:flex items-center gap-6">
          <!-- Tombol Sign In / Info User -->
          <div class="flex items-center gap-4 border-l pl-6 border-gray-100">
            <template v-if="authUser">
              <div class="flex items-center gap-4">
                <div class="text-right leading-none">
                  <p class="text-[10px] font-bold text-[#00a9c3] uppercase tracking-widest mb-1">Selamat Datang,</p>
                  <p class="text-sm font-black text-gray-900">{{ authUser.name }}</p>
                </div>
                <el-button @click="handleLogout" type="danger" size="small" plain round class="!font-bold">
                  LOGOUT
                </el-button>
              </div>
            </template>

            <template v-else>
              <NuxtLink to="/login">
                <el-button type="primary" class="!bg-[#00a9c3] !border-none font-bold px-8 !rounded-full uppercase tracking-widest text-xs h-10 shadow-lg shadow-[#00a9c3]/20">
                  Sign In
                </el-button>
              </NuxtLink>
            </template>
          </div>
        </div>

        <!-- MOBILE HAMBURGER -->
        <button class="lg:hidden flex items-center justify-center w-10 h-10 rounded-xl text-slate-700 bg-gray-50" @click="toggleMobile">
          <Icon :icon="isMobileOpen ? 'tabler:x' : 'tabler:menu-2'" class="text-2xl" />
        </button>
      </div>

      <!-- MOBILE MENU DRAWER -->
      <Transition name="slide-down">
        <div v-if="isMobileOpen" class="lg:hidden border-t border-slate-100 bg-white px-6 py-8 shadow-inner">
          <nav class="flex flex-col gap-2">
            <NuxtLink
              v-for="item in navItems"
              :key="item.to"
              :to="item.to"
              class="block rounded-xl px-4 py-3 text-base font-bold text-slate-600 hover:bg-slate-50 hover:text-[#00a9c3] transition-all"
              @click="closeMobile"
            >
              {{ item.label }}
            </NuxtLink>
          </nav>

          <!-- Mobile Auth Section -->
          <div class="mt-8 pt-8 border-t border-slate-100">
            <template v-if="authUser">
              <div class="mb-4">
                <p class="text-xs text-gray-400 uppercase tracking-widest">Masuk Sebagai:</p>
                <p class="text-lg font-black text-gray-900">{{ authUser.name }}</p>
              </div>
              <el-button @click="handleLogout" type="danger" class="w-full !h-12 font-bold rounded-xl uppercase">Logout</el-button>
            </template>
            <template v-else>
              <NuxtLink to="/login" @click="closeMobile">
                <el-button type="primary" class="w-full !h-12 !bg-[#00a9c3] !border-none font-bold rounded-xl uppercase tracking-widest">Sign In</el-button>
              </NuxtLink>
            </template>
          </div>
        </div>
      </Transition>
    </div>
  </header>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { ElMessage } from 'element-plus'

const isMobileOpen = ref(false)
const { authUser, logout } = useAuth() // Memanggil state user dari composable

const toggleMobile = () => {
  isMobileOpen.value = !isMobileOpen.value
}
const closeMobile = () => {
  isMobileOpen.value = false
}

const handleLogout = async () => {
  try {
    await logout()
    ElMessage.success('Berhasil keluar dari sistem')
    closeMobile()
  } catch (err) {
    ElMessage.error('Terjadi kesalahan saat logout')
  }
}

// Menu Navigasi yang sesuai dengan project ABC Jakarta
const navItems = [
  { label: 'Beranda', to: '/' },
  { label: 'Katalog', to: '/katalog' },
  { label: 'Literasi', to: '/articles' },
  { label: 'Hubungi Kami', to: '/contact' },
]
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease-out;
  overflow: hidden;
}
.slide-down-enter-from,
.slide-down-leave-to {
  max-height: 0;
  opacity: 0;
}
.slide-down-enter-to,
.slide-down-leave-from {
  max-height: 600px;
  opacity: 1;
}
/* Menghilangkan underline default NuxtLink */
a.router-link-active {
  color: #00a9c3;
}
</style>