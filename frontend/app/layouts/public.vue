<script setup>
// 1. Ambil data login dan fungsi logout dari composable
const { authUser, logout } = useAuth()

const handleLogout = async () => {
  try {
    await logout()
    // Setelah logout, authUser otomatis jadi null dan tombol Sign In muncul kembali
  } catch (err) {
    console.error('Gagal logout', err)
  }
}


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
]
</script>

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
        <!-- GANTI BLOK SIGN IN LAMA DENGAN INI -->
<div class="flex items-center gap-4">
  <!-- JIKA SUDAH LOGIN -->
  <template v-if="authUser">
    <div class="flex items-center gap-4">
      <div class="text-right hidden sm:block leading-none">
        <p class="text-[10px] font-bold text-[#00a9c3] uppercase tracking-widest mb-1">Selamat Datang,</p>
        <p class="text-sm font-black text-[#1e293b]">{{ authUser.name }}</p>
      </div>
      <button 
        @click="handleLogout"
        class="px-5 py-2 border-2 border-red-100 text-red-500 text-[12px] font-bold rounded-full hover:bg-red-50 transition-all active:scale-95"
      >
        Logout
      </button>
    </div>
  </template>

  <!-- JIKA BELUM LOGIN (Tampilan awal kamu) -->
  <NuxtLink 
    v-else
    to="/login" 
    class="flex items-center gap-2.5 px-7 py-2.5 bg-[#00a9c3] text-white text-[14px] font-bold rounded-full shadow-lg hover:bg-[#475c80] transition-all duration-300"
  >
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path><path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path><path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855"></path></svg>
    <span>Sign In</span>
  </NuxtLink>
</div>
      </div>
    </header>

    <main class="flex-1 w-full relative">
      <slot />
    </main>

   <footer class="bg-[#f2f2f2] text-gray-700 border-t border-gray-300/50 pt-16 pb-8 relative overflow-hidden">
  <!-- Elemen Dekoratif Halus -->
  <div class="absolute bottom-0 right-0 w-72 h-72 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10"></div>

  <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
    <!-- Grid Utama Footer -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
      
      <!-- KOLOM 1: LOGO & TENTANG -->
      <div class="flex flex-col gap-4">
        <!-- Logo ABC Jakarta -->
        <div class="flex items-center gap-2">
          <img 
            src="/images/logo-abc.png" 
            alt="Logo ABC Jakarta" 
            class="h-16 w-auto object-contain"
          >
          
        </div>
        <p class="text-sm text-gray-500 leading-relaxed mt-2">
          Pusat literatur resmi yang menyediakan buku-buku kesehatan, pertumbuhan mental, keluarga, dan spiritual terpercaya untuk kehidupan yang sehat seutuhnya.
        </p>
      </div>

      <!-- KOLOM 2: TAUTAN NAVIGASI -->
      <div>
        <h4 class="text-sm font-bold uppercase tracking-wider text-gray-900 mb-5">Navigasi</h4>
        <ul class="space-y-3 text-sm">
          <li>
            <NuxtLink to="/" class="text-gray-500 hover:text-[#00a9c3] transition-colors duration-200 flex items-center gap-1.5 group">
              <span class="w-1.5 h-1.5 rounded-full bg-gray-300 group-hover:bg-[#00a9c3]"></span> Beranda
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/katalog" class="text-gray-500 hover:text-[#00a9c3] transition-colors duration-200 flex items-center gap-1.5 group">
              <span class="w-1.5 h-1.5 rounded-full bg-gray-300 group-hover:bg-[#00a9c3]"></span> Katalog Buku
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/testimoni" class="text-gray-500 hover:text-[#00a9c3] transition-colors duration-200 flex items-center gap-1.5 group">
              <span class="w-1.5 h-1.5 rounded-full bg-gray-300 group-hover:bg-[#00a9c3]"></span> Literasi
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/testimoni" class="text-gray-500 hover:text-[#00a9c3] transition-colors duration-200 flex items-center gap-1.5 group">
              <span class="w-1.5 h-1.5 rounded-full bg-gray-300 group-hover:bg-[#00a9c3]"></span> Kesan Pembaca
            </NuxtLink>
          </li>
          <li>
            <NuxtLink to="/contact" class="text-gray-500 hover:text-[#00a9c3] transition-colors duration-200 flex items-center gap-1.5 group">
              <span class="w-1.5 h-1.5 rounded-full bg-gray-300 group-hover:bg-[#00a9c3]"></span> Hubungi Kami
            </NuxtLink>
          </li>
        </ul>
      </div>

      <!-- KOLOM 3: MEDIA SOSIAL -->
      <div>
        <h4 class="text-sm font-bold uppercase tracking-wider text-gray-900 mb-5">Media Sosial</h4>
        <p class="text-sm text-gray-500 mb-4 leading-relaxed">Ikuti kami untuk mendapatkan info buku terbaru dan tips hidup sehat harian:</p>
        <div class="flex flex-col gap-3 text-sm">
         <a href="#" class="px-3 py-2 bg-[#00a9c3] text-white hover:text-[#00a9c3] hover:bg-white rounded-xl flex items-center gap-2 shadow-sm transition-all duration-300 border border-gray-200 group" aria-label="Instagram">
    <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
      <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
    </svg>
    <span class="text-xs font-semibold tracking-wide uppercase text-white group-hover:text-[#00a9c3] transition-colors duration-300">Instagram</span>
  </a>

  <!-- Facebook -->
  <a href="#" class="px-3 py-2 bg-[#00a9c3] text-white hover:text-[#00a9c3] hover:bg-white rounded-xl flex items-center gap-2 shadow-sm transition-all duration-300 border border-gray-200 group" aria-label="Facebook">
    <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
      <path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/>
    </svg>
    <span class="text-xs font-semibold tracking-wide uppercase text-white group-hover:text-[#00a9c3] transition-colors duration-300">Facebook</span>
  </a>

  <!-- YouTube -->
  <a href="#" class="px-3 py-2 bg-[#00a9c3] text-white hover:text-[#00a9c3] hover:bg-white rounded-xl flex items-center gap-2 shadow-sm transition-all duration-300 border border-gray-200 group" aria-label="YouTube">
    <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
      <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
    </svg>
    <span class="text-xs font-semibold tracking-wide uppercase text-white group-hover:text-[#00a9c3] transition-colors duration-300">YouTube</span>
  </a>
        </div>
      </div>

      <!-- KOLOM 4: KONTAK & LOKASI -->
      <div>
        <h4 class="text-sm font-bold uppercase tracking-wider text-gray-900 mb-5">LOKASI & KONTAK</h4>
        <ul class="space-y-3.5 text-sm text-gray-500">
          <li class="flex items-start gap-2.5">
            <svg class="w-5 h-5 text-[#00a9c3] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Adventist Book Center Jakarta, Jl. Dr. Saharjo No.48 4, RT.4/RW.8, Ps. Manggis, Kecamatan Setiabudi, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12960</span>
          </li>
          <li class="flex items-center gap-2.5">
            <svg class="w-5 h-5 text-[#00a9c3] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span class="font-semibold text-gray-800">+62 812-3456-7890</span>
          </li>
          <li class="flex items-start gap-2.5 pt-2 border-t border-gray-300/40">
            <svg class="w-5 h-5 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="text-xs">
              <p class="font-bold text-gray-700">Senin - Kamis:</p>
              <p>08:00 - 16:00 WIB</p>
              <p class="font-bold text-gray-700 mt-1">Jumat:</p>
              <p>08:00 - 13:00 WIB</p>
            </div>
          </li>
        </ul>
      </div>

    </div>

    <!-- Bagian Hak Cipta (Copyright) -->
    <div class="border-t border-gray-300/50 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-400 font-medium">
      <p>© 2026 Adventist Book Center (ABC). Hak Cipta Dilindungi.</p>
      <div class="flex gap-6">
        <a href="#" class="hover:text-[#00a9c3]">Kebijakan Privasi</a>
        <a href="#" class="hover:text-[#00a9c3]">Syarat & Ketentuan</a>
      </div>
    </div>
  </div>
</footer>








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