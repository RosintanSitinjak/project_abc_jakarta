<!-- <template>
    <NuxtLayout>
    <NuxtPage />
    </NuxtLayout>
</template> -->

<script setup>
// Fungsi untuk mencatat pengunjung saat website pertama kali dimuat
onMounted(async () => {
  try {
    const config = useRuntimeConfig()
    const baseUrl = config.public.apiBase || 'http://localhost:8000'

    // Kirim sinyal ke backend bahwa ada pengunjung baru
    // Kita panggil endpoint public/visitor yang sudah kamu buat di Laravel
    await $fetch(`${baseUrl}/api/public/visitor`, {
      method: 'POST'
    })
    
    console.log('Visitor recorded successfully')
  } catch (err) {
    // Kita gunakan warn agar tidak muncul error merah yang mengganggu jika server mati
    console.warn('Visitor tracking skipped.')
  }
})
</script>

<template>
  <div>
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>