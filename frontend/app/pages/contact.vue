<template>
  <div class="min-h-screen bg-[#f2f2f2] text-gray-700 pb-24 relative overflow-hidden">
    <!-- Elemen Ornamen Halus Latar Belakang -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-1/4 left-0 w-80 h-80 bg-[#00a9c3]/5 rounded-full blur-3xl -z-10"></div>

    <!-- 1. HEADER BANNER HUBUNGI KAMI -->
    <section class="py-16 md:py-20 text-center relative z-10">
      <div class="container mx-auto px-6">
        <!-- Badge Tagline -->
        <span class="inline-block px-3 py-1 rounded-full bg-[#00a9c3]/10 text-[#00a9c3] text-[10px] font-black uppercase tracking-[0.2em] mb-4 border border-[#00a9c3]/20">
          Get In Touch
        </span>

        <!-- Judul Utama -->
        <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 tracking-tight leading-tight max-w-3xl mx-auto">
          Hubungi <span class="text-[#00a9c3] italic font-serif">Tim Kami</span>
        </h1>
        
        <!-- Garis Pembatas Khas Tema -->
        <div class="w-12 h-[3px] bg-[#00a9c3] mx-auto mt-4 mb-5 rounded-full"></div>

        <!-- Deskripsi -->
        <p class="text-gray-500 max-w-2xl mx-auto text-sm md:text-base leading-relaxed">
          Punya pertanyaan tentang katalog buku, kerja sama literasi, atau butuh bantuan lainnya? Kami siap mendengar dan membantu Anda.
        </p>
      </div>
    </section>

    <!-- 2. KONTEN UTAMA: GRID INFO & FORM -->
    <div class="max-w-5xl mx-auto px-6 relative z-20">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
        
        <!-- KOLOM KIRI: KARTU INFORMASI KONTAK (Grid 5 Kolom) -->
        <div class="md:col-span-5 space-y-4">
          <div class="bg-white rounded-2xl border border-gray-200/50 p-6 md:p-8 shadow-sm space-y-6">
            <h2 class="text-lg font-bold text-gray-900 tracking-tight mb-4">Informasi Kontak</h2>
            
            <!-- Saluran 1: WhatsApp / Telepon -->
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-xl bg-[#00a9c3]/10 flex items-center justify-center text-[#00a9c3] shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Layanan Pengguna</h3>
                <p class="text-sm font-bold text-gray-900 mt-0.5">+62 812-3456-7890</p>
                <p class="text-xs text-gray-400 mt-0.5">Senin - Jumat, 08.00 - 16.00 WIB</p>
              </div>
            </div>

            <!-- Saluran 2: Email Resmi -->
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-xl bg-[#00a9c3]/10 flex items-center justify-center text-[#00a9c3] shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Surel / Email</h3>
                <p class="text-sm font-bold text-gray-900 mt-0.5">info@abcjakarta.com</p>
                
              </div>
            </div>

            <!-- Saluran 3: Alamat Kantor / Studio -->
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-xl bg-[#00a9c3]/10 flex items-center justify-center text-[#00a9c3] shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Alamat Kantor</h3>
                <p class="text-sm font-bold text-gray-900 mt-0.5 leading-relaxed">
                  Adventist Book Center Jakarta, Jl. Dr. Saharjo No.48 4, RT.4/RW.8, Ps. Manggis, Kecamatan Setiabudi, 
                  Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12960

                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- KOLOM KANAN: FORMULIR PESAN DIGITAL (Grid 7 Kolom) -->
        <div class="md:col-span-7">
          <div class="bg-white rounded-2xl border border-gray-200/50 p-6 md:p-8 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 tracking-tight mb-6">Kirim Pesan Langsung</h2>
            
            <form @submit.prevent="handleSubmit" class="space-y-5">
              <!-- Baris Gabungan: Nama & Email -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="flex flex-col space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</label>
                  <input 
                    v-model="form.name" 
                    type="text" 
                    required
                    placeholder="Nama Anda"
                    class="w-full px-4 py-2.5 rounded-xl bg-[#f2f2f2]/60 border border-transparent focus:border-[#00a9c3] focus:bg-white text-sm text-gray-900 outline-none transition-all"
                  />
                </div>
                <div class="flex flex-col space-y-1.5">
                  <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Alamat Email</label>
                  <input 
                    v-model="form.email" 
                    type="email" 
                    required
                    placeholder="email@anda.com"
                    class="w-full px-4 py-2.5 rounded-xl bg-[#f2f2f2]/60 border border-transparent focus:border-[#00a9c3] focus:bg-white text-sm text-gray-900 outline-none transition-all"
                  />
                </div>
              </div>

              <!-- Input: Subjek Pesan -->
              <div class="flex flex-col space-y-1.5">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Subjek</label>
                <input 
                  v-model="form.subject" 
                  type="text" 
                  required
                  placeholder="Contoh: Pertanyaan Pembelian Buku"
                  class="w-full px-4 py-2.5 rounded-xl bg-[#f2f2f2]/60 border border-transparent focus:border-[#00a9c3] focus:bg-white text-sm text-gray-900 outline-none transition-all"
                />
              </div>

              <!-- Input: Isi Pesan Tekstual -->
              <div class="flex flex-col space-y-1.5">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Isi Pesan</label>
                <textarea 
                  v-model="form.message" 
                  rows="4" 
                  required
                  placeholder="Tuliskan detail pertanyaan atau pesan Anda di sini..."
                  class="w-full px-4 py-2.5 rounded-xl bg-[#f2f2f2]/60 border border-transparent focus:border-[#00a9c3] focus:bg-white text-sm text-gray-900 outline-none transition-all resize-none"
                ></textarea>
              </div>

              <!-- Tombol Submit / Kirim -->
              <button 
                type="submit" 
                :disabled="loading"
                class="w-full sm:w-auto px-6 py-3 bg-[#00a9c3] text-white font-bold text-xs uppercase tracking-widest rounded-xl hover:bg-[#0092a8] transition-colors shadow-sm inline-flex items-center justify-center gap-2 disabled:opacity-50"
              >
                <span v-if="loading">Mengirim...</span>
                <span v-else>Kirim Pesan</span>
                <svg v-if="!loading" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l7 7m-7 7h18"/>
                </svg>
              </button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'

definePageMeta({
  layout: 'public',
})

const loading = ref(false)

const form = reactive({
  name: '',
  email: '',
  subject: '',
  message: ''
})

// Fungsi penanganan pengiriman pesan (Bisa dikoneksikan ke endpoint backend Laravel Anda nanti)
const handleSubmit = async () => {
  loading.value = true
  try {
    // Simulasi pengiriman data ke server
    await new Promise((resolve) => setTimeout(resolve, 1000))
    
    alert('Pesan Anda berhasil terkirim! Tim kami akan segera menghubungi Anda kembali.')
    
    // Reset isian formulir setelah sukses
    form.name = ''
    form.email = ''
    form.subject = ''
    form.message = ''
  } catch (error) {
    console.error('Gagal mengirim pesan:', error)
    alert('Maaf, terjadi kesalahan teknis. Silakan coba kembali beberapa saat lagi.')
  } finally {
    loading.value = false
  }
}
</script>