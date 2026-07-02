<template>
<section class="py-20 bg-white relative overflow-hidden">
  <!-- Ornamen Latar Belakang (Aksen Tambahan agar tidak monoton) -->
  <div class="absolute top-0 left-1/4 w-96 h-96 bg-[#00a93c]/5 rounded-full blur-3xl -z-10"></div>
  <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-[#00a93c]/5 rounded-full blur-3xl -z-10"></div>

  <div class="max-w-6xl mx-auto px-4 relative">
    <!-- Judul Bagian -->
    <div class="text-center mb-16">
      <span class="text-xs font-bold uppercase tracking-widest text-[#00a9c3] bg-[#00a9c3]/10 px-4 py-1.5 rounded-full">Testimoni</span>
      <h2 class="text-3xl md:text-5xl font-black text-[#1b293c] mt-4 tracking-tight">
        Inspirasi & Kesan Pembaca
      </h2>
      <p class="mt-4 text-gray-600 max-w-xl mx-auto text-base md:text-lg leading-relaxed">
        Apa yang mereka katakan mengenai literatur kesehatan dan pertumbuhan hidup yang tersedia di <span class="font-semibold text-[#00a9c3]">ABC Jakarta</span>.
      </p>
    </div>

    <!-- Container Testimoni (Grid Asimetris) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
      
      <!-- Testimoni 1: dr. Shandy Iskandar -->
      <div class="group bg-[#f2f2f2] p-8 md:p-10 rounded-3xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-between transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_10px_30px_rgba(0,169,60,0.1)] hover:border-[#00a9c3]/30">
        <div>
          <!-- Ikon Tanda Kutip Modern -->
          <div class="w-12 h-12 bg-gradient-to-br from-[#00a9c3]/20 to-[#00a9c3]/5 rounded-2xl flex items-center justify-center mb-6 text-[#00a9c3] transition-colors duration-300 group-hover:from-[#00a9c3] group-hover:to-[#00a9c3] group-hover:text-white">
            <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32">
              <path d="M10 8v8h6v6H8V8h2zm12 0v8h6v6h-8V8h2z" />
            </svg>
          </div>
          <p class="text-gray-700 leading-relaxed text-base md:text-lg italic mb-8 relative z-10">
            "Sebagai abdi masyarakat dalam bidang kesehatan, saya mau mengajarkan kepada orang-orang tentang pola hidup sehat untuk mencegah dan mengendalikan berbagai penyakit melalui buku-buku kesehatan, orang bisa belajar dan menerapkannya. Buku-buku berjudul <span class="font-semibold text-gray-900 not-italic">Makanan Menyehatkan</span>, <span class="font-semibold text-gray-900 not-italic">Ensiklopedia Jus Buah dan Sayur untuk Penyembuhan</span>, saya anjurkan dibaca oleh Anda yang menghendaki kehidupan yang sehat seutuhnya."
          </p>
        </div>
        
        <div class="flex items-center gap-4 border-t border-gray-100 pt-6 mt-2">
          <div class="relative">
            <img 
              src="https://bukuadvent.com/wp-content/uploads/2020/07/50-Beautiful-and-Minimalist-Presentation-Backgrounds-022.jpg" 
              alt="dr. Shandy Iskandar"
              class="w-14 h-14 rounded-2xl object-cover ring-4 ring-[#00a9c3]/10 transition-all duration-300 group-hover:ring-[#00a9c3]/30"
            >
          </div>
          <div>
            <h4 class="font-bold text-gray-900 text-lg transition-colors duration-300 group-hover:text-[#00a9c3]">dr. Shandy Iskandar</h4>
            <p class="text-xs text-[#00a9c3] uppercase tracking-wider font-bold mt-0.5">Dokter Spesialis</p>
          </div>
        </div>
      </div>

      <!-- Testimoni 2: Ibu Fiona (Dibuat agak turun pada layar besar untuk efek dinamis/tidak kaku) -->
      <div class="group bg-[#00a9c3] p-8 md:p-10 rounded-3xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-gray-100 flex flex-col justify-between transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_10px_30px_rgba(0,169,60,0.1)] hover:border-[#00a9c3]/30 md:mt-6">
        <div>
          <!-- Ikon Tanda Kutip Modern -->
                    <div class="w-12 h-12 bg-gradient-to-br from-white/20 to-white/5 rounded-2xl flex items-center justify-center mb-6 text-white transition-colors duration-300 group-hover:from-white group-hover:to-[#00a9c3] group-hover:text-white">
            <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32">
              <path d="M10 8v8h6v6H8V8h2zm12 0v8h6v6h-8V8h2z" />
            </svg>
          </div>
          <p class="text-white leading-relaxed text-base md:text-lg italic mb-8 relative z-10">
            "Buku-buku ini menginspirasi dan menguatkan saya dan suami utk menjalani pola hidup yg sehat. Juga mengajarkan bahwa kami bertanggungjawab untuk memelihara kesehatan kami secara mandiri. Selama ini kami banyak berfokus mempelajari banyak hal dalam hidup kami, namun aset yg paling berharga, yaitu kesehatan, justru tidak kami perhatikan. Melalui buku-buku ini kami belajar langkah-langkah konkret utk menjaga kesehatan dan kebugaran tubuh kami."
          </p>
        </div>
        
        <div class="flex items-center gap-4 border-t border-gray-100 pt-6 mt-2">
          <div class="relative">
            <img 
              src="https://bukuadvent.com/wp-content/uploads/2020/07/IMG-20190407-WA0007-1-1024x768-1.jpg" 
              alt="Ibu Fiona"
              class="w-14 h-14 rounded-2xl object-cover ring-4 ring-[#00a9c3]/10 transition-all duration-300 group-hover:ring-[#00a9c3]/30"
            >
          </div>
          <div>
            <h4 class="font-bold text-white text-lg transition-colors duration-300 group-hover:text-black">Ibu Fiona</h4>
            <p class="text-xs text-white uppercase tracking-wider font-bold mt-0.5">Ibu Rumah Tangga</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
</template>