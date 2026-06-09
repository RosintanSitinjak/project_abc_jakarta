<template>
  <header class="sticky top-0 z-50">
    <!-- Top bar — hidden on small screens -->
    <div class="hidden md:flex items-center justify-between bg-blue-950/95 text-white text-sm px-6 lg:px-20 xl:px-34 py-1.5">
      <div class="flex items-center gap-5">
        <div class="flex items-center gap-2">
          <Icon icon="tabler:mail"/>
          <span>info@lamsolusi.com</span>
        </div>

        <div class="flex items-center gap-2">
          <Icon icon="tabler:phone"/>
          <span>+62 881 0822 03778</span>
        </div>
      </div>

      <div>
        <div class="flex items-center justify-end gap-2">
          <Icon icon="tabler:map-pin"/>
          <span>Jakarta, Indonesia</span>
        </div>
      </div>
    </div>

    <div class="bg-white/80 backdrop-blur-sm shadow-md">
      <div class="flex items-center justify-between px-4 sm:px-6 lg:px-20 xl:px-34 py-3">
        <NuxtLink to="/" class="flex items-center gap-3">
          <img src="/images/logo.png" alt="Logo" class="h-8 sm:h-10" width="94" height="32" />
        </NuxtLink>

        <!-- Desktop nav -->
        <div class="hidden lg:flex items-center gap-10">
          <nav>
            <ul class="flex items-center gap-5 text-sm">
              <li>
                <NuxtLink to="/" class="text-gray-500 hover:text-blue-900 transition">Beranda</NuxtLink>
              </li>
              <li>
                <NuxtLink to="/about" class="text-gray-500 hover:text-blue-900 transition">Tentang Kami</NuxtLink>
              </li>
              <li>
                <NuxtLink to="/service" class="text-gray-500 hover:text-blue-900 transition">Layanan</NuxtLink>
              </li>
              <li>
                <NuxtLink to="/product" class="text-gray-500 hover:text-blue-900 transition">Produk</NuxtLink>
              </li>
              <!-- <li>
                <NuxtLink to="/portfolio" class="text-gray-500 hover:text-blue-900 transition">Portofolio</NuxtLink>
              </li> -->
              <li>
                <NuxtLink to="/articles" class="text-gray-500 hover:text-blue-900 transition">Artikel</NuxtLink>
              </li>
              <li>
                <NuxtLink to="/contact" class="text-gray-500 hover:text-blue-900 transition">Kontak</NuxtLink>
              </li>
            </ul>
          </nav>

          <div class="flex items-center gap-5">
            <ClientOnly>
              <el-dropdown>
                <div class="flex items-center justify-center gap-3 cursor-pointer py-2 px-3.5 bg-gray-100 rounded-full" aria-label="Language Menu">
                  <Icon icon="tabler:language" class="text-gray-500"/>
                  <Icon icon="twemoji:flag-indonesia"/>
                </div>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item>
                      <Icon icon="twemoji:flag-indonesia" class="drop-shadow"/>
                      <span class="ml-3">Bahasa Indonesia</span>
                    </el-dropdown-item>
                    <el-dropdown-item>
                      <Icon icon="twemoji:flag-united-states" class="drop-shadow"/>
                      <span class="ml-3">English</span>
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </ClientOnly>
            <NuxtLink to="/contact">
              <el-button type="primary" class="rounded-full!">Ajukan Penawaran</el-button>
            </NuxtLink>
          </div>
        </div>

        <!-- Mobile hamburger -->
        <button class="lg:hidden flex items-center justify-center w-10 h-10 rounded-xl text-slate-700" aria-label="Toggle menu" @click="toggleMobile">
          <Icon :icon="isMobileOpen ? 'tabler:x' : 'tabler:menu-2'" class="text-2xl" />
        </button>
      </div>

      <!-- Mobile menu drawer -->
      <Transition name="slide-down">
        <div v-if="isMobileOpen" class="lg:hidden border-t border-slate-100 bg-white px-4 pb-5">
          <nav class="flex flex-col gap-1 pt-3">
            <NuxtLink
              v-for="item in mobileNavItems"
              :key="item.to"
              :to="item.to"
              class="block rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-blue-900"
              @click="closeMobile"
            >
              {{ item.label }}
            </NuxtLink>
          </nav>

          <div class="mt-4 flex flex-col gap-3 px-4">
            <NuxtLink to="/contact" @click="closeMobile">
              <el-button type="primary" class="rounded-full! w-full">Ajukan Penawaran</el-button>
            </NuxtLink>
          </div>
        </div>
      </Transition>
    </div>
  </header>
</template>

<script lang="ts" setup>
import { ref } from 'vue'

const isMobileOpen = ref(false)

const toggleMobile = () => {
  isMobileOpen.value = !isMobileOpen.value
}
const closeMobile = () => {
  isMobileOpen.value = false
}

const mobileNavItems = [
  { label: 'Beranda', to: '/' },
  { label: 'Tentang Kami', to: '/about' },
  { label: 'Layanan', to: '/service' },
  { label: 'Produk', to: '/product' },
  // { label: 'Portofolio', to: '/portfolio' },
  { label: 'Artikel', to: '/articles' },
  { label: 'Kontak', to: '/contact' },
]
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.25s ease;
  overflow: hidden;
}
.slide-down-enter-from,
.slide-down-leave-to {
  max-height: 0;
  opacity: 0;
}
.slide-down-enter-to,
.slide-down-leave-from {
  max-height: 500px;
  opacity: 1;
}
</style>