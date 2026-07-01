<template>
  <section class="py-24 bg-white relative overflow-hidden">
    <!-- Dekorasi Latar Belakang Halus -->
    <div class="absolute top-0 left-0 w-full h-full opacity-30 pointer-events-none">
      <div class="absolute top-10 right-10 w-64 h-64 bg-blue-50 rounded-full blur-3xl"></div>
      <div class="absolute bottom-10 left-10 w-64 h-64 bg-slate-50 rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-6 lg:px-20 relative z-10">
      <!-- Header Bagian -->
      <div class="text-center mb-20">
        <h2 class="text-3xl md:text-5xl font-black text-corporate-dark mb-6 tracking-tight">
          What Our Clients Say
        </h2>
        <!-- Garis Aksen Bawah Judul -->
        <div class="w-20 h-1.5 bg-corporate-blue mx-auto rounded-full"></div>
        <p class="mt-6 text-corporate-body max-w-xl mx-auto font-medium">
          Honest testimonials from business partners who have transformed their businesses with our digital solutions
        </p>
      </div>

      <!-- Grid Testimonial -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
        <div 
          v-for="r in 3" 
          :key="r" 
          class="group relative p-10 rounded-[2.5rem] bg-slate-50 border border-slate-100 hover:bg-white hover:shadow-[0_30px_60px_-15px_rgba(0,0,0,0.05)] transition-all duration-500"
        >
          <!-- Simbol Kutip Besar (Dekoratif) -->
          <span class="absolute top-6 left-8 text-7xl text-blue-200/50 font-serif leading-none group-hover:text-corporate-blue/20 transition-colors duration-500">
            “
          </span>

          <div class="relative z-10">
            <!-- Konten Testimoni -->
            <p class="text-corporate-dark text-lg leading-relaxed mb-10 italic">
“We are very satisfied with the system implementation by Intan-S Digital. The implementation process was very structured, and the team was very responsive to our specific business needs.”
            </p>

            <!-- Profil Klien -->
            <div class="flex items-center gap-4">
              <!-- Avatar Placeholder -->
              <div class="w-14 h-14 rounded-full bg-slate-200 border-2 border-white shadow-sm overflow-hidden flex-shrink-0">
                <img 
                  :src="`https://i.pravatar.cc/150?u=${r}`" 
                  alt="Client Profile"
                  class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"
                />
              </div>
              
              <div>
                <h4 class="font-extrabold text-corporate-dark text-base">Nama Klien {{ r }}</h4>
                <p class="text-xs font-bold text-corporate-blue uppercase tracking-widest">CEO @ Perusahaan Klien</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
// Jika data ingin diambil dari API, kamu bisa menggunakan logika fetch yang sama seperti di Services.vue
</script>

<style scoped>
/* Menghaluskan tampilan font untuk quote */
blockquote, p {
  text-rendering: optimizeLegibility;
}
</style>