<script setup>
const navLinks = [
  { name: 'Beranda', path: '/' },
  { name: 'Katalog', path: '/katalog' },
  { name: 'Literasi', path: '/articles' },
  { name: 'Hubungi kami', path: '/contact' }
]

const quickLinks = [
  { name: 'Home', path: '/' },
  { name: 'Services', path: '/services' },
  { name: 'Articles', path: '/articles' },
  { name: 'Contact Us', path: '/contact' }
]

const servicesList = [
  'SAP Development',
  'Web & Mobile App',
  'IT Manpower',
  'Digital Consulting'
]

const socialLinks = [
  { icon: 'fb', url: '#', svg: '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>' },
  { icon: 'in', url: '#', svg: '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle>' },
  { icon: 'ig', url: '#', svg: '<rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>' }
]</script>

<template>
  <div class="min-h-screen bg-[#f2f2f2] text-[#1e293b] font-sans flex flex-col justify-between selection:bg-primary/10">
    
    <!-- 1. TOP BAR (Info Kontak) - STATIS -->
    <div class="hidden md:block w-full bg-[#00a9c3] text-white py-2.5 border-b border-white/5 relative z-[60]">
      <div class="container mx-auto px-6 lg:px-10 flex justify-between items-center text-[13px] font-medium">
        <div class="flex items-center gap-8">
          <!-- Item Email -->
          <div class="flex items-center gap-2 cursor-default">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white opacity-80"><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z"></path><path d="M3 7l9 6l9 -6"></path></svg>
            <span class="opacity-90">info@abcjakarta.com</span>
          </div>
          <!-- Item Telepon -->
          <div class="flex items-center gap-2 cursor-default">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white opacity-80"><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2"></path></svg>
            <span class="opacity-90">+62 0813 7699 0897</span>
          </div>
        </div>
        <!-- Item Lokasi -->
        <div class="flex items-center gap-2 cursor-default">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white opacity-80"><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z"></path></svg>
          <span class="opacity-90">Jakarta, Indonesia</span>
        </div>
      </div>
    </div>

    <!-- 2. STICKY NAVBAR -->
    <header class="w-full bg-[#f2f2f2] backdrop-blur-xl sticky top-0 z-[100] border-b border-gray-100 shadow-sm shadow-gray-200/20">
      <div class="container mx-auto px-6 lg:px-10 py-4 flex justify-between items-center">
        
        <NuxtLink to="/" class="flex items-center transition-transform hover:scale-105 active:scale-95">
          <img src="/images/logo-abc.png" alt="Logo" class="h-12 md:h-16 w-auto object-contain" />
        </NuxtLink>

        <nav class="hidden md:flex items-center gap-10">
          <NuxtLink 
            v-for="link in navLinks" 
            :key="link.name" 
            :to="link.path" 
            class="text-[15px] font-semibold text-gray-500 hover:text-[#1e293b] transition-colors"
            exact-active-class="!text-[#1e293b] !font-bold"
          >
            {{ link.name }}
          </NuxtLink>
        </nav>

        <!-- Button Sign In dengan SVG User -->
        <NuxtLink 
          to="/login" 
          class="flex items-center gap-2.5 px-7 py-2.5 bg-[#00a9c3] text-white text-[14px] font-bold rounded-full shadow-lg hover:bg-[#475c80] transition-all duration-300"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855"></path></svg>
          <span>Sign In</span>
        </NuxtLink>
      </div>
    </header>

    <main class="flex-1 w-full relative">
      <slot />
    </main>

  







    <!-- Back to Top dengan SVG Arrow -->
    <el-backtop :right="25" :bottom="35">
      <div class="bg-primary text-white w-12 h-12 rounded-2xl flex items-center justify-center shadow-2xl hover:bg-primary-dark transition duration-500">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5l0 14"></path><path d="M18 11l-6 -6"></path><path d="M6 11l6 -6"></path></svg>
      </div>
    </el-backtop>

  </div>
</template>

<style>
html, body { overflow-x: clip; height: auto; }
.page-enter-active, .page-leave-active { transition: all 0.3s; }
.page-enter-from { opacity: 0; transform: translateY(5px); }
.page-leave-to { opacity: 0; transform: translateY(-5px); }
</style>