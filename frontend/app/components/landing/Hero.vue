<template>
  <!-- <section class="relative pt-20 pb-32 lg:pt-32 lg:pb-48 overflow-hidden bg-white"> -->
    <section class="relative overflow-hidden bg-[#f2f2f2] pt-16 pb-20 lg:pt-24 lg:pb-32">
    <!-- Dekorasi Latar Belakang (Subtle Blur) -->
    <div class="absolute top-0 right-0 -translate-y-12 translate-x-12 w-96 h-96 bg-blue-50 rounded-full blur-3xl opacity-50 -z-10"></div>
    <div class="absolute bottom-0 left-0 translate-y-12 -translate-x-12 w-72 h-72 bg-slate-50 rounded-full blur-3xl opacity-70 -z-10"></div>

    <div class="container mx-auto px-6 lg:px-20 relative z-10">
      <div class="flex flex-col lg:flex-row items-center gap-16">
        
        <!-- BAGIAN TEKS (KIRI) -->
        <div class="flex-1 text-center lg:text-left">
          <!-- Badge Kecil di Atas -->
          <span class="inline-block px-4 py-1.5 rounded-full bg-blue-50 text-corporate-blue text-xs font-bold uppercase tracking-widest mb-8 border border-blue-100">
            Temukan insprasi anda
          </span>
          
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-corporate-dark leading-[1.1] mb-8 tracking-tight">
           Balai  <span class="text-corporate-blue">Buku Advent</span> Jakarta
          </h1>
          
          <p class="text-lg lg:text-xl text-corporate-body leading-relaxed mb-10 max-w-2xl mx-auto lg:mx-0 font-medium">
“If a book were kept at hand, and these fragments of time were improved in study, reading, or careful thought, what might not be accomplished!”         </p>


<p class="text-lg lg:text-xl text-corporate-body leading-relaxed mb-10 max-w-2xl mx-auto lg:mx-0 font-medium">
 – Christ Object Lessons, 343-344          </p>
          <!-- Action Buttons -->
          <div class="flex flex-wrap justify-center lg:justify-start gap-5">
            <NuxtLink 
              to="/contact" 
              class="px-10 py-4 bg-corporate-dark text-white bg-black font-bold rounded-xl shadow-2xl shadow-slate-900/30 hover:text-black hover:bg-white hover:-translate-y-1 transition-all duration-300"
            >
             Start Consultation
            </NuxtLink>
            
            <NuxtLink 
              to="/services" 
              class="px-10 py-4 border-2 border-slate-200 text-corporate-dark font-bold rounded-xl hover:bg-slate-800 hover:text-white hover:border-corporate-blue/20 transition-all duration-300"
            >
              View Services
            </NuxtLink>
          </div>

          <!-- Statistik Singkat (Opsional untuk menambah kredibilitas) -->
<div class="mt-10 flex gap-12 border-t border-gray-100 pt-8 justify-center lg:justify-start">
  <div>
    <h4 class="text-2xl font-bold text-heading">100+</h4>
    <p class="text-[11px] uppercase tracking-widest font-bold text-body">Clients</p>
  </div>
  <div class="border-l border-gray-100 pl-12">
    <h4 class="text-2xl font-bold text-heading">150+</h4>
    <p class="text-[11px] uppercase tracking-widest font-bold text-body">Projects</p>
  </div>
</div>
        </div>

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