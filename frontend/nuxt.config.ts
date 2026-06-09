// https://nuxt.com/docs/api/configuration/nuxt-config
// export default defineNuxtConfig({
//   compatibilityDate: '2025-07-15',
//   devtools: { enabled: true }
// })

// import tailwindcss from "@tailwindcss/vite";

// export default defineNuxtConfig({
//   compatibilityDate: "2025-07-15",
//   devtools: { enabled: true },
//   css: ['./app/assets/css/main.css'],
//   vite: {
//     plugins: [
//       tailwindcss(),
//     ],
//   },
// });


// import { fileURLToPath } from 'node:url'
// import { resolve } from 'node:path'
// import tailwindcss from "@tailwindcss/vite";

// const rootDir = fileURLToPath(new URL('.', import.meta.url))

// export default defineNuxtConfig({
//   compatibilityDate: "2025-07-15",                                                                                                                                                                                                                                                      
//   devtools: { enabled: true },
//   css: ['./app/assets/css/main.css'],
  
//   modules: [
//     '@element-plus/nuxt'
//   ],

  
  

//   runtimeConfig: {
//     public: {
//       apiBase: 'http://localhost:8000'
//     }
//   },

  // alias: {
  //   '~/components': resolve(rootDir, './components'),
  //   '~/composables': resolve(rootDir, './composables'),
  //   '~/stores': resolve(rootDir, './stores'),
  //   '~/utils': resolve(rootDir, './utils'),
  //   '~/plugins': resolve(rootDir, './plugins'),
  //   '@/components': resolve(rootDir, './components'),
  //   '@/composables': resolve(rootDir, './composables'),
  //   '@/stores': resolve(rootDir, './stores'),
  //   '@/utils': resolve(rootDir, './utils'),
  //   '@/plugins': resolve(rootDir, './plugins'),
  // },

  

//   vite: {
//     plugins: [
//       tailwindcss(),
//     ],
//   },
// });


import tailwindcss from "@tailwindcss/vite";

export default defineNuxtConfig({
  compatibilityDate: "2025-07-15",
  devtools: { enabled: true },
  
  srcDir: 'app',          // ← TAMBAHAN PENTING
  css: ['~/assets/css/main.css'],  // ← ganti ./ jadi ~/
  
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