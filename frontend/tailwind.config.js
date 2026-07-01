/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./app/**/*.{js,vue,ts}",
    "./app/components/**/*.{js,vue,ts}",
    "./app/layouts/**/*.vue",
    "./app/pages/**/*.vue",
    "./app/plugins/**/*.{js,ts}",
    "./nuxt.config.{js,ts}",
  ],
  theme: {
    extend: {
      colors: {
        // Biru Navy Gelap (Top Bar & Hover)
        'corporate-dark': '#1e293b', 
        
        // Warna aksen biru untuk tombol/CTA
        'corporate-blue': '#2563eb', 
        
        
        // Warna abu-abu untuk teks deskripsi/body
        'corporate-body': '#64748b',

        // Opsional: Tetap mempertahankan warna heading jika diperlukan
        'heading': '#0f172a',
      },
    },
  },
  plugins: [],
}