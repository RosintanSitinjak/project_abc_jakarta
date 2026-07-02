<template>
  <!-- <section class="relative pt-20 pb-32 lg:pt-32 lg:pb-48 overflow-hidden bg-white"> -->
   <section class="relative overflow-hidden bg-[#f2f2f2] pt-16 pb-20 lg:pt-24 lg:pb-32">
  <!-- Dekorasi Latar Belakang (Subtle Blur bertema #00a9c3) -->
  <div class="absolute top-0 right-0 -translate-y-12 translate-x-12 w-96 h-96 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10"></div>
  <div class="absolute bottom-0 left-0 translate-y-12 -translate-x-12 w-72 h-72 bg-[#00a9c3]/10 rounded-full blur-3xl -z-10"></div>

  <div class="container mx-auto px-6 lg:px-16 relative z-10">
    <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16">
      
      <!-- BAGIAN TEKS (KIRI) -->
      <div class="flex-1 text-center lg:text-left">
        <!-- Badge Kecil di Atas -->
        <span class="inline-block px-4 py-1.5 rounded-full bg-[#00a9c3]/10 text-[#00a9c3] text-xs font-bold uppercase tracking-widest mb-6 border border-[#00a9c3]/20">
          Temukan Inspirasi Anda
        </span>
        
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-gray-900 leading-[1.1.5] mb-6 tracking-tight">
          Balai Buku Advent Jakarta
        </h1>
        
        <!-- Wadah Kutipan Elegan -->
        <blockquote class="border-l-4 border-[#00a9c3] pl-4 my-6 text-left max-w-2xl mx-auto lg:mx-0">
          <p class="text-base md:text-lg text-gray-600 italic leading-relaxed font-medium">
            "If a book were kept at hand, and these fragments of time were improved in study, reading, or careful thought, what might not be accomplished!"
          </p>
          <cite class="block text-xs uppercase tracking-wider font-bold text-gray-400 mt-2 not-italic">
            – Christ's Object Lessons, 343-344
          </cite>
        </blockquote>

        <!-- Action Buttons -->
        <div class="flex flex-wrap justify-center lg:justify-start gap-4 mt-8">
          <NuxtLink 
            to="/katalog" 
            class="px-8 py-3.5 bg-[#00a9c3] text-white font-bold rounded-xl shadow-lg shadow-[#00a9c3]/20 hover:bg-gray-900 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300"
          >
            Katalog
          </NuxtLink>
          
          <NuxtLink 
            to="/contact" 
            class="px-8 py-3.5 border-2 border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-900 hover:text-white hover:border-gray-900 hover:-translate-y-0.5 transition-all duration-300"
          >
            Hubungi Kami
          </NuxtLink>
        </div>

<<<<<<< HEAD
        <!-- BAGIAN GAMBAR (KANAN) -->
        <div class="flex-1 relative w-full max-w-[550px] lg:max-w-none">
          <div class="relative group">
            <!-- Glow effect di belakang gambar -->
            <div class="absolute inset-0 bg-corporate-blue/5 blur-[100px] rounded-full scale-90 group-hover:scale-110 transition-transform duration-700"></div>
            
            <img 
              src="https://m.media-amazon.com/images/I/514Hss7jeLL._AC_UF1000,1000_QL80_.jpg" 
              class="w-full h-auto relative z-10 transform transition-transform duration-500 hover:scale-[1.02]" 
              alt="Hero Digital IT Solutions"
            >
=======
        <!-- Statistik Singkat Bersih -->
        <div class="mt-12 flex gap-10 border-t border-gray-300/40 pt-6 justify-center lg:justify-start">
          <div>
            <h4 class="text-2xl font-black text-gray-900">100+</h4>
            <p class="text-[11px] uppercase tracking-widest font-bold text-gray-400 mt-0.5">Koleksi Buku</p>
          </div>
          <div class="border-l border-gray-300/40 pl-10">
            <h4 class="text-2xl font-black text-gray-900">50+</h4>
            <p class="text-[11px] uppercase tracking-widest font-bold text-gray-400 mt-0.5">Kategori</p>
>>>>>>> main
          </div>
        </div>
      </div>

      <!-- BAGIAN GAMBAR (KANAN) -->
      <div class="flex-1 w-full max-w-[320px] md:max-w-[360px] lg:max-w-[400px] mx-auto">
        <div class="relative group">
          <!-- Glow effect di belakang gambar -->
          <div class="absolute inset-0 bg-[#00a9c3]/10 blur-[80px] rounded-full scale-90 group-hover:scale-105 transition-transform duration-700"></div>
          
          <!-- Frame Gambar dengan Bayangan Halus -->
          <div class="relative z-10 bg-white p-4 rounded-3xl shadow-[0_15px_40px_rgba(0,0,0,0.08)] transition-all duration-500 group-hover:scale-[1.02] group-hover:shadow-[0_20px_50px_rgba(0,169,195,0.15)]">
            <img 
              src="https://m.media-amazon.com/images/I/514Hss7jeLL._AC_UF1000,1000_QL80_.jpg" 
              class="w-full h-auto rounded-2xl object-cover" 
              alt="Balai Buku Advent Jakarta"
            >
          </div>
        </div>
      </div>

    </div>
  </div>
</section>




</template>

<script setup>
// Script setup jika dibutuhkan di masa depan
</script>

<style scoped>
/* Tambahkan smoothing untuk animasi jika diperlukan */
.transition-all {
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>