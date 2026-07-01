<template>
  <section id="services" class="py-24 bg-white border-t border-gray-100">
    <div class="container mx-auto px-6 lg:px-20">
      
      <!-- Header Section -->
      <div class="text-center max-w-2xl mx-auto mb-16">
        <span class="text-corporate-blue font-bold text-[11px] uppercase tracking-[0.2em] bg-[#f8fafc] px-4 py-1.5 rounded-full mb-4 inline-block">
          Strategic Consulting
        </span>
        <h2 class="text-3xl md:text-2xl font-black text-corporate-dark tracking-tight">
          Empowering Your Business Through Strategic IT Solutions 
           <!-- <span class="text-[#3b5d95]">Solutions</span> -->
        </h2>
      </div>

      <!-- Grid Services (3 Kolom) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
        <div 
          v-for="s in services.slice(0, 3)" 
          :key="s.id" 
          class="flex flex-col bg-white group transition-all duration-300"
        >
          <!-- Gambar: Sudut rounded-xl (tegas & profesional) -->
          <div class="aspect-video overflow-hidden rounded-xl bg-gray-100 mb-8 shadow-sm border border-gray-100">
             <img 
              :src="getThumbnailUrl(s)" 
              class="w-full h-full object-cover group-hover:scale-105 transition duration-700" 
              alt="Service Image"
            />
          </div>

          <!-- Content -->
          <h3 class="text-2xl font-bold text-[#1e293b] mb-4 group-hover:text-[#3b5d95] transition-colors">
            {{ s.name }}
          </h3>
          
          <p class="text-slate-500 text-sm leading-relaxed mb-8 line-clamp-3">
            {{ s.description }}
          </p>

          <!-- Bullet Points Minimalis (Tanpa Hover) -->
          <ul class="space-y-3 mb-10 flex-1">
            <li v-for="scope in s.scopes?.slice(0, 3)" :key="scope.id" class="flex items-center gap-3 text-sm text-slate-600 font-medium">
              <div class="w-1.5 h-1.5 rounded-full bg-[#3b5d95] opacity-60 shrink-0"></div>
              {{ scope.scope }}
            </li>
            <li v-if="!s.scopes?.length" class="text-xs italic text-slate-400">
              Details coming soon.
            </li>
          </ul>

          <!-- Link: Garis Bawah Tipis -->
          <NuxtLink 
            to="/services" 
            class="text-[#1e293b] font-bold text-xs uppercase tracking-widest border-b-2 border-[#1e293b]/10 w-fit pb-1 hover:border-[#3b5d95] hover:text-[#3b5d95] transition-all"
          >
            Learn More
          </NuxtLink>
        </div>
      </div>

    </div>
  </section>
</template>

<script setup>
const config = useRuntimeConfig()
const baseUrl = config.public.apiBase || 'http://localhost:8000'

const { data: response, status } = await useFetch(`${baseUrl}/api/public/services`)
const services = computed(() => response.value?.data || response.value || [])

const getThumbnailUrl = (service) => {
  const path = service.thumbnail?.path || ''
  if (!path) return 'https://placehold.co/800x450?text=Intan-S+Digital'
  const cleanPath = path.startsWith('public/') ? path.replace('public/', 'storage/') : path
  return cleanPath.startsWith('http') ? cleanPath : `${baseUrl}/${cleanPath.startsWith('/') ? cleanPath.substring(1) : cleanPath}`
}
</script>