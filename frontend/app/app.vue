<script setup>
/**
 * PENTING: Karena kamu pakai srcDir: 'app', 
 * pastikan file useAuth.ts ada di folder: frontend/app/composables/useAuth.ts
 */

const auth = useAuth() // Ambil object auth
const config = useRuntimeConfig()

// Ambil Base URL tanpa /api di ujungnya
const baseUrl = config.public.apiBase?.replace(/\/api$/, '') || 'http://127.0.0.1:8000'

onMounted(async () => {
  // 1. Jalankan Pengecekan Profil (untuk auto-login)
  try {
    if (auth && auth.checkProfile) {
      await auth.checkProfile()
    }
  } catch (err) {
    console.error('Auth check failed:', err)
  }

  // 2. Jalankan Pencatatan Pengunjung
  try {
    // Gunakan $fetch langsung agar tidak terikat middleware useApi
    await $fetch(`${baseUrl}/api/public/visitor`, {
      method: 'POST'
    })
    console.log('Visitor recorded')
  } catch (err) {
    // Kita abaikan jika visitor gagal agar tidak muncul error di layar user
    console.warn('Visitor tracking skipped.')
  }
})
</script>

<template>
  <div>
    <!-- NuxtLayout akan mengambil file dari layouts/public.vue atau auth.vue -->
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>