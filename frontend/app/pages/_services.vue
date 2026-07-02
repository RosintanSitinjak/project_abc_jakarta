<template>
  <div class="min-h-screen bg-white text-[#1e293b]">
    
    <!-- 1. HEADER BANNER (Dibuat lebih ringkas/compact) -->
    <!-- py-24 dikurangi menjadi py-16 di mobile dan py-20 di desktop -->
    <section class="py-16 lg:py-20 bg-[#1e293b] text-white text-center relative overflow-hidden">
      
      <!-- Ornamen Dekorasi (Disesuaikan posisinya agar tetap rapi) -->
      <div class="absolute top-0 right-0 w-64 h-64 bg-[#3b5d95] blur-[100px] opacity-20 -translate-y-1/2 translate-x-1/2"></div>
      
      <div class="container mx-auto px-6 relative z-10">
        <!-- Badge Atas -->
        <span class="inline-block px-4 py-1.5 rounded-full bg-white/10 text-white text-[10px] font-black uppercase tracking-[0.2em] mb-4 border border-white/5">
          Solutions & Expertise
        </span>

        <!-- Judul: Dibuat 2 baris agar lebih padat ke samping, bukan memanjang ke bawah -->
        <h1 class="text-3xl md:text-5xl font-extrabold mb-5 tracking-tight leading-tight max-w-4xl mx-auto">
          Integrated <span class="text-[#8b8e92] italic">Digital</span> Solutions <br class="hidden md:block" />
          <span class="text-xl md:text-2xl font-light opacity-80 uppercase tracking-[0.2em]">for Operational Excellence</span>
        </h1>

        <!-- Deskripsi: Font size dikecilkan sedikit (text-base) agar ringkas -->
        <p class="text-gray-200 max-w-2xl mx-auto text-base md:text-lg leading-relaxed font-medium">
          We provide cutting-edge technology solutions designed to optimize your business operations and accelerate digital growth.
        </p>
      </div>
    </section>


<section class="py-32">
      <!-- Container dibuat lebih lebar (max-w-7xl) agar gambar punya ruang untuk memanjang -->
      <div class="container mx-auto px-6 lg:px-10 max-w-7xl">
        <div v-if="status !== 'pending'" class="space-y-40 lg:space-y-56">
          <div 
            v-for="(service, index) in services" 
            :key="service.id" 
            class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16"
            :class="{ 'lg:flex-row-reverse': index % 2 !== 0 }"
          >
            <!-- KOLOM GAMBAR: Dibuat lebih lebar (lg:w-[58%]) -->
            <div class="w-full lg:w-[58%] shrink-0">
              <div class="relative group">
                <div class="absolute -inset-4 bg-gray-50 rounded-[2rem] -z-10 transition-transform group-hover:scale-[1.02]"></div>
                <!-- aspect-video membuat gambar lebar ke samping (16:9) -->
                <img 
                  :src="getThumbnailUrl(service)" 
                  class="w-full aspect-video lg:aspect-[16/10] object-cover rounded-2xl shadow-2xl border border-gray-100 transition-all duration-700"
                  alt="Service Detail"
                />
              </div>
            </div>

            <!-- KOLOM TEKS: Dibuat lebih ramping (lg:w-[42%]) agar seimbang -->
            <div class="w-full lg:w-[42%]">
              <h2 class="text-3xl md:text-4xl font-bold mb-6 leading-tight tracking-tight">
                {{ service.name }}
              </h2>
              <p class="text-slate-500 text-base md:text-lg leading-relaxed mb-10">
                {{ service.description }}
              </p>

              <!-- Scope of Work Box -->
              <div v-if="service.scopes?.length" class="bg-slate-50 p-8 rounded-xl border border-slate-100 mb-10">
                <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-[#1e293b] mb-6">
                  Scope of Work
                </h4>
                <!-- Grid diubah jadi 1 kolom agar lebih rapi di ruang yang lebih sempit -->
                <ul class="space-y-4">
                  <li v-for="sc in service.scopes" :key="sc.id" class="flex items-start gap-3 text-[15px] text-gray-700 font-bold">
                    <div class="w-1.5 h-1.5 rounded-full bg-[#3b5d95] shrink-0 mt-2"></div>
                    {{ sc.scope }}
                  </li>
                </ul>
              </div>

              <!-- Button -->
              <NuxtLink 
                to="/contact" 
                class="inline-block px-10 py-3.5 bg-[#1e293b] text-white font-bold rounded-lg hover:bg-[#3b5d95] shadow-xl shadow-gray-200 transition-all duration-300 active:scale-95 text-sm uppercase tracking-widest"
              >
                Start Consultation
              </NuxtLink>
            </div>
          </div>
        </div>

        <!-- Jika Data Kosong -->
        <div v-else class="text-center py-20 text-slate-400">
          Loading our professional solutions...
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
definePageMeta({ layout: 'public' })
const config = useRuntimeConfig()
const baseUrl = config.public.apiBase || 'http://localhost:8000'

const { data: response, status } = await useFetch(`${baseUrl}/api/public/services`)
const services = computed(() => response.value?.data || response.value || [])

const getThumbnailUrl = (service) => {
  const path = service.thumbnail?.path || ''
  if (!path) return 'https://placehold.co/1200x800?text=IT+Solutions'
  const cleanPath = path.startsWith('public/') ? path.replace('public/', 'storage/') : path
  return cleanPath.startsWith('http') ? cleanPath : `${baseUrl}/${cleanPath.startsWith('/') ? cleanPath.substring(1) : cleanPath}`
}
</script>