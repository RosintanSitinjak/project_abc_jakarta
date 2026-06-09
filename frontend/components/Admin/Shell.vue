<template>
  <div class="min-h-screen bg-[#f4f6fa] font-sans p-6 flex flex-col gap-6">
    
    <!-- HEADER UTAMA (Logo Kiri, Judul & Info Profil Kanan) -->
    <header class="flex items-center justify-between bg-transparent px-4 shrink-0">
      <!-- Sisi Kiri: Logo Brand -->
      <div class="flex items-center gap-3">
        <div class="flex flex-col text-left">
          <span class="text-xs font-black uppercase tracking-wider text-blue-900 leading-tight">Lamjaya</span>
          <span class="text-[9px] font-bold tracking-widest text-slate-400 uppercase leading-none mt-0.5">Global Solusi</span>
        </div>
      </div>

      <!-- Sisi Kanan: Judul Halaman & Kontrol Akun -->
      <div class="flex items-center gap-6">
        <h2 class="text-xl font-bold text-slate-800 tracking-tight">Admin Dashboard</h2>
        
        <div class="flex items-center gap-3">
          <!-- Widget Tanggal -->
          <div class="flex items-center gap-2 rounded-xl bg-white border border-slate-100/80 px-4 py-2 text-xs font-semibold text-slate-500 shadow-xs">
            <Icon icon="solar:calendar-minimalistic-outline" class="text-sm text-slate-400" />
            <span>Tuesday, June 2</span>
          </div>
          <!-- Profile Dropdown -->
          <div class="flex items-center gap-3 rounded-xl bg-white border border-slate-100/80 px-4 py-1.5 shadow-xs">
            <div class="h-7 w-7 rounded-full bg-blue-100 flex items-center justify-center text-blue-900 font-bold text-xs">A</div>
            <div class="text-left">
              <p class="text-xs font-bold text-slate-800 leading-none">Admin</p>
              <p class="text-[9px] font-medium text-slate-400 mt-0.5">Admin</p>
            </div>
            <Icon icon="solar:alt-arrow-down-linear" class="text-xs text-slate-400 ml-1" />
          </div>
        </div>
      </div>
    </header>

    <!-- AREA KONTEN BAWAH -->
    <div class="flex-1 flex gap-6 items-start min-h-0">
      
      <!-- SIDEBAR (Floating Card Putih Melayang) -->
      <aside class="w-64 bg-white rounded-2xl border border-slate-100/70 p-4 shadow-sm flex flex-col shrink-0 sticky top-6">
        <nav class="space-y-1">
          <NuxtLink 
            v-for="item in navItems" 
            :key="item.label" 
            :to="item.to"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-xs font-bold transition-all duration-200 no-underline"
            :class="isActiveRoute(item.to) 
              ? 'bg-blue-900 text-white! shadow-md shadow-blue-900/20' 
              : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'"
          >
            <Icon :icon="item.icon" class="text-base shrink-0" />
            <span>{{ item.label }}</span>
          </NuxtLink>
        </nav>
      </aside>

      <!-- MAIN AREA SLOT -->
      <main class="flex-1 min-w-0 flex flex-col gap-6">
        <slot />
      </main>

    </div>
  </div>
</template>

<script setup>
import { Icon } from '@iconify/vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const navItems = [
  { label: 'Dashboard', to: '/admin/dashboard', icon: 'solar:widget-bold' },
  { label: 'Services', to: '/admin/services', icon: 'solar:suitcase-outline' },
  { label: 'Clients', to: '/admin/clients', icon: 'solar:users-group-two-rounded-outline' },
  { label: 'Products', to: '/admin/products', icon: 'solar:bag-2-outline' },
  { label: 'Articles', to: '/admin/articles', icon: 'solar:document-text-outline' },
  { label: 'Inquiries', to: '/admin/inquiries', icon: 'solar:chat-square-line-outline' },
  { label: 'Users', to: '/admin/users', icon: 'solar:user-circle-outline' }
]

const isActiveRoute = (path) => {
  return route.path === path || route.path === `${path}/`
}
</script>