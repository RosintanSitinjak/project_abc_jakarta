<template>
  <section class="py-16 bg-white">
    <div class="container mx-auto px-6 lg:px-20">
      <!-- Container: Padding dikurangi dari p-20 menjadi p-10 md:p-12 agar ramping -->
      <div class="bg-[#1e293b] p-10 md:p-12 rounded-[2.5rem] relative overflow-hidden flex flex-col lg:flex-row items-center justify-between gap-8 border border-white/5 shadow-xl">
        
        <!-- Decorative Glow Effects (Dikecilkan juga) -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-primary/10 blur-[60px] rounded-full pointer-events-none"></div>
        
        <div class="relative z-10 text-center lg:text-left max-w-lg">
          <!-- Ukuran font dikurangi dari 5xl menjadi 3xl -->
          <h2 class="text-2xl md:text-3xl font-extrabold text-white mb-3 tracking-tight">
            Ready to <span class="text-primary italic">accelerate</span> your business?
          </h2>
          <!-- Ukuran font deskripsi dikurangi menjadi base (standar) -->
          <p class="text-gray-400 text-base font-medium leading-relaxed">
            Partner with our expert team to build scalable, innovative IT solutions tailored to your unique challenges.
          </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-6 relative z-10">
          <!-- Tombol Konsultasi: Lebih ramping -->
          <NuxtLink 
            to="/contact" 
            class="px-8 py-3.5 bg-primary text-white font-bold text-sm rounded-full shadow-lg shadow-primary/20 hover:bg-white hover:text-[#1e293b] transition-all duration-300"
          >
            Start Free Consultation
          </NuxtLink>

          <!-- WhatsApp Link -->
          <a 
            href="https://wa.me/6281376990897?text=Hi%20Intan-S%20Digital,%20I'd%20like%20to%20consult%20about%20IT%20Solutions." 
            target="_blank"
            class="group flex items-center gap-2 text-white font-bold text-base transition-all duration-300 relative"
          >
            <span>Chat with us</span>
            <Icon icon="tabler:brand-whatsapp" class="w-5 h-5 text-green-400" />
            <span class="absolute -bottom-1 left-0 w-full h-[1px] bg-white/20 group-hover:bg-primary transition-colors"></span>
          </a>
        </div>

      </div>
    </div>
  </section>
</template>

<script setup>
/**
 * CTA Component - Ramping Version
 */
</script>