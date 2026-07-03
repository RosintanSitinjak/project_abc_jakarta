<template>
    <section class="py-16 bg-white relative overflow-hidden">
  <!-- Elemen Dekoratif Latar Belakang -->
  <div class="absolute -top-40 -right-40 w-96 h-96 bg-[#00a9c3]/5 rounded-full blur-3xl"></div>
  <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-[#00a9c3]/5 rounded-full blur-3xl"></div>

  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <!-- Judul Bagian -->
    <div class="text-center mb-12">
      <span class="text-xs font-bold uppercase tracking-widest text-[#00a9c3] bg-[#00a9c3]/10 px-4 py-1.5 rounded-full">Koleksi Terpopuler</span>
      <h2 class="text-3xl md:text-4xl font-black text-gray-900 mt-3 tracking-tight">
        Buku <span class="text-[#00a9c3]">Best Seller</span> Kami
      </h2>
      <p class="mt-3 text-sm md:text-base text-gray-600 max-w-xl mx-auto">
        Daftar literatur kesehatan dan gaya hidup yang paling banyak dicari dan mengubah kehidupan ribuan pembaca.
      </p>
    </div>

    <!-- Grid Buku (3 Atas, 3 Bawah - Kotak Lebih Ringkas) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
      
      <!-- KARTU BUKU 1 -->
      <div class="group bg-green rounded-2xl p-5 shadow-[0_4px_15px_rgba(0,0,0,0.02)] border border-gray-100 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_15px_30px_rgba(0,169,195,0.12)] hover:border-[#00a9c3]/30">
        <div>
          <!-- Wadah Gambar Buku Lebih Kecil -->
          <div class="bg-[#f2f2f2] rounded-xl p-6 mb-4 flex justify-center items-center h-60 relative overflow-hidden group-hover:bg-[#00a9c3]/5 transition-colors duration-300">
            <span class="absolute top-3 left-3 bg-[#00a9c3] text-[10px] text-white font-bold uppercase tracking-wider px-2.5 py-1 rounded-full z-10 shadow-sm">Best Seller</span>
            <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=600&auto=format&fit=crop" alt="Cover Buku 1" class="h-full object-contain shadow-sm rounded transition-transform duration-500 group-hover:scale-105 group-hover:rotate-1">
          </div>
          <!-- Info Buku -->
          <span class="text-[11px] font-bold text-[#00a9c3] tracking-wider uppercase">Kategori Kesehatan</span>
          <h3 class="text-lg font-bold text-gray-900 mt-1 line-clamp-2 group-hover:text-[#00a9c3] transition-colors duration-200">Judul Buku Best Seller 1</h3>
          <p class="text-xs text-gray-500 mt-2 line-clamp-3 leading-relaxed">Deskripsi singkat mengenai isi buku, manfaat, atau keunggulan utama yang akan didapatkan pembaca secara garis besar.</p>
        </div>
        <!-- Tombol Aksi (Tanpa Harga) -->
        <div class="mt-5 pt-4 border-t border-gray-50">
          <a href="#" class="w-full inline-flex items-center justify-center bg-[#00a9c3] text-white text-xs font-semibold py-2.5 rounded-xl transition-all duration-300 hover:bg-[#00a9c3] hover:shadow-sm">
            Detail Buku
          </a>
        </div>
      </div>

      <!-- KARTU BUKU 2 -->
      <div class="group bg-white rounded-2xl p-5 shadow-[0_4px_15px_rgba(0,0,0,0.02)] border border-gray-100 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_15px_30px_rgba(0,169,195,0.12)] hover:border-[#00a9c3]/30">
        <div>
          <div class="bg-[#f2f2f2] rounded-xl p-6 mb-4 flex justify-center items-center h-60 relative overflow-hidden group-hover:bg-[#00a9c3]/5 transition-colors duration-300">
            <span class="absolute top-3 left-3 bg-[#00a9c3] text-[10px] text-white font-bold uppercase tracking-wider px-2.5 py-1 rounded-full z-10 shadow-sm">Best Seller</span>
            <img src="https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=600&auto=format&fit=crop" alt="Cover Buku 2" class="h-full object-contain shadow-sm rounded transition-transform duration-500 group-hover:scale-105 group-hover:rotate-1">
          </div>
          <span class="text-[11px] font-bold text-[#00a9c3] tracking-wider uppercase">Gaya Hidup</span>
          <h3 class="text-lg font-bold text-gray-900 mt-1 line-clamp-2 group-hover:text-[#00a9c3] transition-colors duration-200">Judul Buku Best Seller 2</h3>
          <p class="text-xs text-gray-500 mt-2 line-clamp-3 leading-relaxed">Deskripsi singkat mengenai isi buku, manfaat, atau keunggulan utama yang akan didapatkan pembaca secara garis besar.</p>
        </div>
        <div class="mt-5 pt-4 border-t border-gray-50">
          <a href="#" class="w-full inline-flex items-center justify-center bg-[#00a9c3] text-white text-xs font-semibold py-2.5 rounded-xl transition-all duration-300 hover:bg-[#00a9c3] hover:shadow-sm">
            Detail Buku
          </a>
        </div>
      </div>

      <!-- KARTU BUKU 3 -->
      <div class="group bg-white rounded-2xl p-5 shadow-[0_4px_15px_rgba(0,0,0,0.02)] border border-gray-100 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_15px_30px_rgba(0,169,195,0.12)] hover:border-[#00a9c3]/30">
        <div>
          <div class="bg-[#f2f2f2] rounded-xl p-6 mb-4 flex justify-center items-center h-60 relative overflow-hidden group-hover:bg-[#00a9c3]/5 transition-colors duration-300">
            <span class="absolute top-3 left-3 bg-[#00a9c3] text-[10px] text-white font-bold uppercase tracking-wider px-2.5 py-1 rounded-full z-10 shadow-sm">Best Seller</span>
            <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=600&auto=format&fit=crop" alt="Cover Buku 3" class="h-full object-contain shadow-sm rounded transition-transform duration-500 group-hover:scale-105 group-hover:rotate-1">
          </div>
          <span class="text-[11px] font-bold text-[#00a9c3] tracking-wider uppercase">Nutrisi & Diet</span>
          <h3 class="text-lg font-bold text-gray-900 mt-1 line-clamp-2 group-hover:text-[#00a9c3] transition-colors duration-200">Judul Buku Best Seller 3</h3>
          <p class="text-xs text-gray-500 mt-2 line-clamp-3 leading-relaxed">Deskripsi singkat mengenai isi buku, manfaat, atau keunggulan utama yang akan didapatkan pembaca secara garis besar.</p>
        </div>
        <div class="mt-5 pt-4 border-t border-gray-50">
          <a href="#" class="w-full inline-flex items-center justify-center bg-[#00a9c3] text-white text-xs font-semibold py-2.5 rounded-xl transition-all duration-300 hover:bg-[#00a9c3] hover:shadow-sm">
            Detail Buku
          </a>
        </div>
      </div>

      <!-- KARTU BUKU 4 -->
      <div class="group bg-white rounded-2xl p-5 shadow-[0_4px_15px_rgba(0,0,0,0.02)] border border-gray-100 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_15px_30px_rgba(0,169,195,0.12)] hover:border-[#00a9c3]/30">
        <div>
          <div class="bg-[#f2f2f2] rounded-xl p-6 mb-4 flex justify-center items-center h-60 relative overflow-hidden group-hover:bg-[#00a9c3]/5 transition-colors duration-300">
            <span class="absolute top-3 left-3 bg-[#00a9c3] text-[10px] text-white font-bold uppercase tracking-wider px-2.5 py-1 rounded-full z-10 shadow-sm">Best Seller</span>
            <img src="https://images.unsplash.com/photo-1495640388908-05fa85288e61?q=80&w=600&auto=format&fit=crop" alt="Cover Buku 4" class="h-full object-contain shadow-sm rounded transition-transform duration-500 group-hover:scale-105 group-hover:rotate-1">
          </div>
          <span class="text-[11px] font-bold text-[#00a9c3] tracking-wider uppercase">Mental Health</span>
          <h3 class="text-lg font-bold text-gray-900 mt-1 line-clamp-2 group-hover:text-[#00a9c3] transition-colors duration-200">Judul Buku Best Seller 4</h3>
          <p class="text-xs text-gray-500 mt-2 line-clamp-3 leading-relaxed">Deskripsi singkat mengenai isi buku, manfaat, atau keunggulan utama yang akan didapatkan pembaca secara garis besar.</p>
        </div>
        <div class="mt-5 pt-4 border-t border-gray-50">
          <a href="#" class="w-full inline-flex items-center justify-center bg-[#00a9c3] text-white text-xs font-semibold py-2.5 rounded-xl transition-all duration-300 hover:bg-[#00a9c3] hover:shadow-sm">
            Detail Buku
          </a>
        </div>
      </div>

      <!-- KARTU BUKU 5 -->
      <div class="group bg-white rounded-2xl p-5 shadow-[0_4px_15px_rgba(0,0,0,0.02)] border border-gray-100 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_15px_30px_rgba(0,169,195,0.12)] hover:border-[#00a9c3]/30">
        <div>
          <div class="bg-[#f2f2f2] rounded-xl p-6 mb-4 flex justify-center items-center h-60 relative overflow-hidden group-hover:bg-[#00a9c3]/5 transition-colors duration-300">
            <span class="absolute top-3 left-3 bg-[#00a9c3] text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full z-10 shadow-sm">Best Seller</span>
            <img src="https://images.unsplash.com/photo-1516979187457-637abb4f9353?q=80&w=600&auto=format&fit=crop" alt="Cover Buku 5" class="h-full object-contain shadow-sm rounded transition-transform duration-500 group-hover:scale-105 group-hover:rotate-1">
          </div>
          <span class="text-[11px] font-bold text-[#00a9c3] tracking-wider uppercase">Pencegahan Penyakit</span>
          <h3 class="text-lg font-bold text-gray-900 mt-1 line-clamp-2 group-hover:text-[#00a9c3] transition-colors duration-200">Judul Buku Best Seller 5</h3>
          <p class="text-xs text-gray-500 mt-2 line-clamp-3 leading-relaxed">Deskripsi singkat mengenai isi buku, manfaat, atau keunggulan utama yang akan didapatkan pembaca secara garis besar.</p>
        </div>
        <div class="mt-5 pt-4 border-t border-gray-50">
          <a href="#" class="w-full inline-flex items-center justify-center bg-[#00a9c3] text-white text-xs font-semibold py-2.5 rounded-xl transition-all duration-300 hover:bg-[#00a9c3] hover:shadow-sm">
            Detail Buku
          </a>
        </div>
      </div>

      <!-- KARTU BUKU 6 -->
      <div class="group bg-white rounded-2xl p-5 shadow-[0_4px_15px_rgba(0,0,0,0.02)] border border-gray-100 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_15px_30px_rgba(0,169,195,0.12)] hover:border-[#00a9c3]/30">
        <div>
          <div class="bg-[#f2f2f2] rounded-xl p-6 mb-4 flex justify-center items-center h-60 relative overflow-hidden group-hover:bg-[#00a9c3]/5 transition-colors duration-300">
            <span class="absolute top-3 left-3 bg-[#00a9c3] text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full z-10 shadow-sm">Best Seller</span>
            <img src="https://images.unsplash.com/photo-1532012197267-da84d127e765?q=80&w=600&auto=format&fit=crop" alt="Cover Buku 6" class="h-full object-contain shadow-sm rounded transition-transform duration-500 group-hover:scale-105 group-hover:rotate-1">
          </div>
          <span class="text-[11px] font-bold text-[#00a9c3] tracking-wider uppercase">Spiritual Kebugaran</span>
          <h3 class="text-lg font-bold text-gray-900 mt-1 line-clamp-2 group-hover:text-[#00a9c3] transition-colors duration-200">Judul Buku Best Seller 6</h3>
          <p class="text-xs text-gray-500 mt-2 line-clamp-3 leading-relaxed">Deskripsi singkat mengenai isi buku, manfaat, atau keunggulan utama yang akan didapatkan pembaca secara garis besar.</p>
        </div>
        <div class="mt-5 pt-4 border-t border-gray-50">
          <a href="#" class="w-full inline-flex items-center justify-center bg-[#00a9c3] text-white text-xs font-semibold py-2.5 rounded-xl transition-all duration-300 hover:bg-[#00a9c3] hover:shadow-sm">
            Detail Buku
          </a>
        </div>
      </div>

    </div>
  </div>
</section>

</template>