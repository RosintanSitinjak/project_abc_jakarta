<template>
  <!-- Main Wrapper dengan background abu tipis agar Footer/Header terlihat terpisah -->
  <div class="">
    
      
      <!-- Box Utama: Menggunakan Grid 2 kolom dengan rounded corner & shadow agar rapi di dalam layout -->
      <div class="flex flex-col lg:grid lg:grid-cols-2 overflow-hidden shadow-2xl shadow-slate-200/60 ">
        
        <!-- SISI KIRI: Visual Branding Modern -->
<div class="hidden lg:flex relative bg-slate-900 items-center justify-center overflow-hidden min-h-[600px]">
  
  <!-- 1. Background Image dengan Zoom Effect & Parallax Feel -->
  

  <!-- 2. Overlay Gradasi Dinamis (Warna Corporate) -->
  <div class="absolute inset-0 bg-gradient-to-br from-[#00A9C3] "></div>

  <!-- 3. Decorative Geometric Pattern (Subtle Dots) -->
  <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#white 1px, transparent 1px); background-size: 30px 30px;"></div>

  <!-- 4. Konten Utama -->
  <div class="relative z-10 w-full max-w-lg px-12 text-left">
    
    <!-- Lencana/Badge Kecil -->
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 backdrop-blur-md mb-8">
      <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
      <span class="text-white text-[10px] font-bold uppercase tracking-[0.2em]">Official Portal</span>
    </div>

    <!-- Judul dengan Font Play -->
    <h1 class="text-6xl font-black text-white leading-none tracking-tighter mb-6">
      Literacy <br/>
      <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-white/50">Excellence</span> <br/>
      Online
    </h1>

    <div class="w-24 h-1.5 bg-white mb-8 rounded-full"></div>

    <!-- Deskripsi/Quote Pendek -->
    <p class="text-white/80 text-lg font-medium leading-relaxed mb-10 max-w-sm">
      "Membawa terang melalui literatur berkualitas untuk pertumbuhan rohani dan intelektual jemaat."
    </p>

    <!-- Footer Kiri: Info Lokasi/Instansi -->
    <div class="flex items-center gap-4 pt-8 border-t border-white/10">
      <div class="p-2 bg-white/10 rounded-lg backdrop-blur-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18 18.246 18.477 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
      </div>
      <div>
        <p class="text-white font-bold text-sm leading-none">ABC JAKARTA</p>
        <p class="text-white/50 text-[11px] uppercase tracking-widest mt-1">Konferens DKI Jakarta</p>
      </div>
    </div>
  </div>
  
  <!-- 5. Decorative Blurs (Soft Glows) -->
  <div class="absolute top-[-10%] right-[-10%] w-[400px] h-[400px] bg-[#00A9C3] rounded-full blur-[120px] opacity-20"></div>
  <div class="absolute bottom-[-10%] left-[-10%] w-[300px] h-[300px] bg-blue-400 rounded-full blur-[100px] opacity-20"></div>

</div>
        <!-- SISI KANAN: Area Form -->
        <div class="flex items-center justify-center p-8 sm:p-12 md:p-16">
          <div class="w-full max-w-sm">
            <!-- Mobile Header Logo -->
            <div class="lg:hidden text-center mb-8">
              <h2 class="text-2xl font-black text-[#00A9C3]">ABC JAKARTA</h2>
            </div>

            <div class="mb-10">
              <h2 class="text-3xl font-bold text-gray-900 mb-2">Sign In</h2>
              <p class="text-gray-500 text-sm">Akses portal untuk Gereja, Jemaat, dan Penginjil.</p>
            </div>

            <!-- FORM LOGIN -->
            <el-form 
              ref="formRef" 
              :model="form" 
              :rules="rules" 
              label-position="top" 
              class="custom-clean-form"
            >
              <el-form-item label="Email Address" prop="email">
                <el-input 
                  v-model="form.email" 
                  placeholder="nama@email.com" 
                  size="large"
                  class="modern-input"
                />
              </el-form-item>
              
              <el-form-item label="Password" prop="password">
                <el-input 
                  v-model="form.password" 
                  type="password" 
                  show-password 
                  placeholder="••••••••" 
                  size="large"
                  class="modern-input"
                />
              </el-form-item>

              <div class="mb-8 flex items-center justify-between">
                <el-checkbox v-model="form.remember" class="custom-checkbox">Remember me</el-checkbox>
                <NuxtLink to="/forgot-password" class="text-xs font-bold text-[#00A9C3] uppercase tracking-wider hover:opacity-70">
                  Lupa?
                </NuxtLink>
              </div>

              <el-button 
                type="primary" 
                class="w-full !h-12 !rounded-xl !bg-[#00A9C3] !border-none font-bold uppercase tracking-widest text-xs shadow-lg shadow-[#00A9C3]/20" 
                :loading="isSubmitting" 
                @click="submitForm"
              >
                Masuk ke Portal
              </el-button>
            </el-form>

            <div class="mt-10 pt-8 border-t border-slate-100 text-center">
              <p class="text-gray-500 text-sm">
                Belum punya akun mitra?
                <NuxtLink to="/register" class="text-[#00A9C3] font-bold hover:underline ml-1">Buat Akun</NuxtLink>
              </p>
            </div>
          </div>
        </div>

      </div>
    </div>
  
</template>

<script lang="ts" setup>
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '../../composables/useApi'
import { useAuth } from '../composables/useAuth'

// Gunakan layout auth (tanpa footer)
definePageMeta({
  layout: 'auth',
})

const formRef = ref<FormInstance>()
const isSubmitting = ref(false)
const router = useRouter()
const { apiFetch, getErrorMessage } = useApi()

// 1. Ambil state global dari composable useAuth (Sesuai Poin 3)
const { authUser } = useAuth()

const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const rules = reactive<FormRules>({
  email: [
    { required: true, message: 'Wajib diisi', trigger: 'blur' },
    { type: 'email', message: 'Email tidak valid', trigger: 'blur' },
  ],
  password: [{ required: true, message: 'Wajib diisi', trigger: 'blur' }],
})

const submitForm = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      isSubmitting.value = true
      try {
        // 2. Jalankan API Login
        const response = await apiFetch<any>('/auth/login', { 
          method: 'POST', 
          body: form 
        })

        // 3. Simpan data ke Global State agar bisa diakses Katalog & Checkout (Poin 3)
        authUser.value = response.user || response

        ElMessage.success('Login berhasil')

        // 4. Logika Navigasi: Pilih salah satu tujuan (Admin ke Dashboard, User ke Home)
        if (authUser.value.role === 'admin' || authUser.value.role === 2) {
          await router.push('/admin/dashboard')
        } else {
          await router.push('/')
        }

      } catch (error) {
        ElMessage.error(getErrorMessage(error, 'Login gagal. Periksa kembali email dan password.'))
      } finally {
        isSubmitting.value = false
      }
    }
  })
}
</script>

<style scoped>
/* Styling Input & Checkbox tetap sama dengan sebelumnya agar konsisten */
:deep(.modern-input .el-input__wrapper) {
  padding: 8px 12px;
  border-radius: 10px;
  background-color: #f8fafc;
  box-shadow: none !important;
  border: 1px solid #e2e8f0;
}
:deep(.modern-input .el-input__wrapper.is-focus) {
  border-color: #00A9C3;
  background-color: white;
}
:deep(.custom-checkbox .el-checkbox__input.is-checked .el-checkbox__inner) {
  background-color: #00A9C3;
  border-color: #00A9C3;
}
</style>