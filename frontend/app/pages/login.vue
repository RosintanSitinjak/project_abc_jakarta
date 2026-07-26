<template>
  <div class="">
      <div class="flex flex-col lg:grid lg:grid-cols-2 overflow-hidden shadow-2xl shadow-slate-200/60 ">
        <!-- SISI KIRI: Visual Branding -->
        <div class="hidden lg:flex relative bg-slate-900 items-center justify-center overflow-hidden min-h-[600px]">
          <div class="absolute inset-0 bg-gradient-to-br from-[#00A9C3] to-[#1B293C] opacity-90"></div>
          <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(white 1px, transparent 1px); background-size: 30px 30px;"></div>
          <div class="relative z-10 w-full max-w-lg px-12 text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 backdrop-blur-md mb-8">
              <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
              <span class="text-white text-[10px] font-bold uppercase tracking-[0.2em]">Official Portal</span>
            </div>
            <h1 class="text-6xl font-black text-white leading-none tracking-tighter mb-6">
              Literacy <br/>
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-white/50">Excellence</span> <br/>
              Online
            </h1>
            <div class="w-24 h-1.5 bg-white mb-8 rounded-full"></div>
            <p class="text-white/80 text-lg font-medium leading-relaxed mb-10 max-w-sm">
              "Membawa terang melalui literatur berkualitas untuk pertumbuhan rohani dan intelektual jemaat."
            </p>
            <div class="flex items-center gap-4 pt-8 border-t border-white/10">
              <div class="p-2 bg-white/10 rounded-lg backdrop-blur-md">
                <Icon icon="solar:book-bookmark-bold" class="text-white text-xl" />
              </div>
              <div>
                <p class="text-white font-bold text-sm leading-none">ABC JAKARTA</p>
                <p class="text-white/50 text-[11px] uppercase tracking-widest mt-1">Konferens DKI Jakarta</p>
              </div>
            </div>
          </div>
        </div>

        <!-- SISI KANAN: Form Login -->
        <div class="flex items-center justify-center p-8 sm:p-12 md:p-16 bg-white">
          <div class="w-full max-w-sm">
            <div class="lg:hidden text-center mb-8">
              <h2 class="text-2xl font-black text-[#00A9C3]">ABC JAKARTA</h2>
            </div>
            <div class="mb-10">
              <h2 class="text-3xl font-bold text-gray-900 mb-2">Sign In</h2>
              <p class="text-gray-500 text-sm">Akses portal untuk Gereja, Jemaat, dan Penginjil.</p>
            </div>

            <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
              <el-form-item label="Email Address" prop="email">
                <el-input v-model="form.email" placeholder="nama@email.com" size="large" class="modern-input" />
              </el-form-item>
              
              <el-form-item label="Password" prop="password">
                <el-input v-model="form.password" type="password" show-password placeholder="••••••••" size="large" class="modern-input" @keyup.enter="submitForm" />
              </el-form-item>

              <div class="mb-8 flex items-center justify-between">
                <el-checkbox v-model="form.remember">Remember me</el-checkbox>
                <NuxtLink to="/forgot-password" class="text-xs font-bold text-[#00A9C3] uppercase hover:opacity-70">Lupa?</NuxtLink>
              </div>

              <el-button 
                type="primary" 
                class="w-full !h-12 !rounded-xl !bg-[#00A9C3] !border-none font-bold uppercase tracking-widest text-xs" 
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
import { Icon } from "@iconify/vue";
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '../../composables/useApi'
import { useAuth } from '../composables/useAuth'

definePageMeta({ layout: 'auth' })

const formRef = ref<FormInstance>()
const isSubmitting = ref(false)
const router = useRouter()
const { apiFetch, getErrorMessage } = useApi()
const { authUser } = useAuth()

const form = reactive({ email: '', password: '', remember: false })

const rules = reactive<FormRules>({
  email: [{ required: true, message: 'Wajib diisi', trigger: 'blur' }, { type: 'email', message: 'Email tidak valid', trigger: 'blur' }],
  password: [{ required: true, message: 'Wajib diisi', trigger: 'blur' }],
})

const submitForm = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (valid) {
      isSubmitting.value = true
      try {
        const response = await apiFetch<any>('/auth/login', { method: 'POST', body: form })
        authUser.value = response.user || response

        ElMessage.success(`Selamat datang, ${authUser.value.name}`)

        // FIX REDIRECT: Role 1 (Pimpinan) dan Role 2 (Admin) diarahkan ke Dashboard
        const role = Number(authUser.value.role)
        if (role === 1 || role === 2) {
          await router.push('/admin/dashboard')
        } else {
          await router.push('/') // Jemaat/PL ke halaman depan
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