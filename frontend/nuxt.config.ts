import tailwindcss from "@tailwindcss/vite";

export default defineNuxtConfig({
  compatibilityDate: "2025-07-15",
  devtools: { enabled: true },
  
  srcDir: 'app',
  
  // WAJIB: Tambahkan daftar CSS Syncfusion agar Editor muncul
  css: [
    '~/assets/css/main.css',
    '@syncfusion/ej2-base/styles/material.css',
    '@syncfusion/ej2-buttons/styles/material.css',
    '@syncfusion/ej2-inputs/styles/material.css',
    '@syncfusion/ej2-popups/styles/material.css',
    '@syncfusion/ej2-lists/styles/material.css',
    '@syncfusion/ej2-navigations/styles/material.css',
    '@syncfusion/ej2-splitbuttons/styles/material.css',
    '@syncfusion/ej2-vue-richtexteditor/styles/material.css'
  ],
  
  modules: [
    '@element-plus/nuxt'
  ],

  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000'
    }
  },

  vite: {
    plugins: [
      tailwindcss(),
    ],
  },
});