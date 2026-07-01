<template>
  <section class="py-24 bg-white overflow-hidden">
    <div class="container mx-auto px-6 lg:px-20">
      <div class="flex flex-col lg:flex-row items-center gap-16 lg:gap-28">
        
        <!-- LEFT SIDE: CONTENT -->
        <div class="w-full lg:w-1/2 order-2 lg:order-1">
          <!-- Badge -->
          <span class="inline-block px-4 py-1.5 rounded-full bg-blue-50 text-[#1e293b] text-[10px] font-black uppercase tracking-[0.2em] mb-6">
            Innovation & Insight
          </span>
          
          <!-- Headline -->
          <h2 class="text-4xl md:text-5xl font-extrabold text-[#1e293b] leading-tight mb-8 tracking-tight">
            Centralized Business <br/>
            <span class="text-[#3b5d95] italic font-serif">Intelligence</span>
          </h2>
          
          <!-- Professional English Description -->
          <p class="text-lg text-gray-500 leading-relaxed mb-10 font-medium opacity-90">
            Take full control of your digital infrastructure with custom dashboards designed for clarity and precision. We turn complex backend data into actionable insights, allowing you to monitor health, performance, and growth in real-time.
          </p>
          
          <!-- CTA Link -->
          <NuxtLink 
            to="/services" 
            class="inline-flex items-center gap-3 text-[#1e293b] font-black text-xs uppercase tracking-[0.2em] group border-b-2 border-transparent hover:border-[#3b5d95] pb-1 transition-all duration-300"
          >
            Explore Solutions
            <Icon icon="tabler:arrow-narrow-right" class="w-5 h-5 transition-transform group-hover:translate-x-2" />
          </NuxtLink>
        </div>

        <!-- RIGHT SIDE: DIRECT IMAGE (No Card) -->
        <div class="w-full lg:w-1/2 order-1 lg:order-2 flex justify-center lg:justify-end">
          <div class="relative w-full max-w-2xl group">
            <!-- Subtle Glow behind image to add depth (Dihapus container card-nya) -->
            <div class="absolute -inset-4 bg-primary/5 blur-[80px] rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
            
            <!-- Gambar Dashboard Melayang -->
            <img 
              src="https://themes.coderthemes.com/prompt_tr/assets/desktop1-86d16dc9.gif" 
              alt="Intan-S Digital Intelligence Dashboard" 
              class="w-full h-auto rounded-2xl shadow-[0_35px_80px_-15px_rgba(30,41,59,0.15)] transition-transform duration-700 hover:scale-[1.02] relative z-10"
            />
          </div>
        </div>

      </div>
    </div>
  </section>
</template>