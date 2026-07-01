<template>
  <!-- pt-0 karena Hero sudah punya padding bawah yang cukup, pb-24 untuk jarak ke Services -->
  <section class="pb-24 bg-white overflow-hidden">
    <div class="container mx-auto px-6">
      
      <!-- Judul Seksi: Dibuat lebih Clean & Minimalis -->
      <div class="flex flex-col items-center mb-16">
        <h2 class="text-slate-500 text-[11px] lg:text-xl font-bold uppercase tracking-[0.3em] flex flex-wrap justify-center items-center gap-2 text-center">
          Trusted by Global Partners & 
          <!-- <span class="text-corporate-blue font-black italic lowercase tracking-normal">Industry Leaders </span> -->
           <span>Industry Leaders</span>
        </h2>
        <!-- Garis dekoratif tipis -->
        <div class="w-12 h-[1px] bg-slate-200 mt-4"></div>
      </div>

      <!-- Kontainer Logo: Menggunakan Grid agar lebih presisi daripada Flex-wrap -->
      <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8 lg:gap-12 items-center justify-items-center">
          
          <div 
            v-for="brand in brands" 
            :key="brand.name" 
            class="w-full h-16 flex items-center justify-center group"
          >
            <img 
              :src="brand.src" 
              :alt="brand.name" 
              class="max-h-10 lg:max-h-12 w-auto object-contain filter grayscale opacity-40 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-500 hover:scale-110"
            />
          </div>

        </div>
      </div>
      
    </div>
  </section>
</template>

<script setup>
const brands = [
  { name: 'UNAI', src: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT86KM2onmdmGeGSk3VF3a8Jlhn7JrPr_vkTT5xzVvNqg&s=10' },
  { name: 'GMAHK', src: '/images/gmahk.png' },
  { name: 'HopeChannel', src: '/images/hci.png' },
  { name: 'Telkom', src: '/images/logofti.png' }, // Pakai logo FTI yang lebih modern
  { name: 'Lamjaya', src: 'https://shorturl.at/WaUZY' }
]

</script>

<style scoped>
/* Memastikan transisi grayscale smooth di semua browser */
img {
  backface-visibility: hidden;
  -webkit-filter: grayscale(100%);
  filter: grayscale(100%);
}
.group:hover img {
  -webkit-filter: grayscale(0%);
  filter: grayscale(0%);
}
</style>
