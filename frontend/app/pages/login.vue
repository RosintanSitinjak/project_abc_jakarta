<template>
  <div class="">
    <div class="flex flex-col lg:grid lg:grid-cols-2 overflow-hidden shadow-2xl shadow-slate-200/60">
      
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
              <!-- TOMBOL PEMICU MODAL LUPA PASSWORD -->
              <button 
                type="button" 
                @click="openForgotModal" 
                class="text-xs font-bold text-[#00A9C3] hover:text-[#008ba0] transition-colors uppercase tracking-wider"
              >
                Lupa Password?
              </button>
            </div>

            <el-button 
              type="primary" 
              class="w-full !h-12 !rounded-xl !bg-[#00A9C3] !border-none font-bold uppercase tracking-widest text-xs shadow-md shadow-[#00A9C3]/20 hover:!bg-[#008ba0]" 
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

    <!-- ============================================== -->
    <!-- MODAL / DIALOG: LUPA PASSWORD                  -->
    <!-- ============================================== -->
    <el-dialog 
      v-model="showForgotModal" 
      width="90%" 
      class="max-w-md !rounded-3xl overflow-hidden custom-forgot-modal" 
      :show-close="true"
    >
      <div class="p-2 space-y-5 text-gray-800">
        
        <!-- Header Modal -->
        <div class="text-center">
          <div class="w-14 h-14 mx-auto rounded-2xl bg-[#00A9C3]/10 text-[#00A9C3] flex items-center justify-center mb-3 border border-[#00A9C3]/20">
            <Icon icon="solar:lock-password-unlocked-bold" class="text-2xl" />
          </div>
          <h3 class="text-lg font-black text-gray-900 tracking-tight">Atur Ulang Password</h3>
          <p class="text-xs text-gray-500 mt-1 max-w-xs mx-auto">
            Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk membuat password baru.
          </p>
        </div>

        <!-- Form Input Email Reset -->
        <el-form 
          ref="forgotFormRef" 
          :model="forgotForm" 
          :rules="forgotRules" 
          label-position="top"
          @submit.prevent="handleForgotPassword"
        >
          <el-form-item label="Email Terdaftar" prop="email">
            <el-input 
              v-model="forgotForm.email" 
              placeholder="nama@email.com" 
              size="large" 
              class="modern-input"
              @keyup.enter="handleForgotPassword"
            >
              <template #prefix>
                <Icon icon="solar:letter-linear" class="text-gray-400 text-base" />
              </template>
            </el-input>
          </el-form-item>

          <el-button 
            type="primary" 
            :loading="isSendingReset" 
            @click="handleForgotPassword" 
            class="w-full !h-11 !rounded-xl !bg-[#00A9C3] !border-none font-bold uppercase tracking-wider text-xs shadow-md shadow-[#00A9C3]/20 hover:!bg-[#008ba0] mt-2"
          >
            Kirim Link Reset Password
          </el-button>
        </el-form>

        <!-- Bantuan Cepat WhatsApp Admin -->
        <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-100 text-xs text-gray-600 flex items-start gap-2.5">
          <span class="text-base shrink-0">💬</span>
          <div class="leading-relaxed">
            <span class="font-bold text-gray-900 block">Butuh bantuan langsung?</span>
            Hubungi Kasir/Admin ABC Jakarta melalui 
            <a 
              href="https://wa.me/6281376990897?text=Halo%20Admin%20ABC%20Jakarta,%20saya%20mengalami%20kendala%20lupa%20password%20akun." 
              target="_blank" 
              class="text-[#00A9C3] font-bold hover:underline"
            >
              WhatsApp (+62 813-7699-0897)
            </a>.
          </div>
        </div>

      </div>
    </el-dialog>

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

// State Login
const form = reactive({ email: '', password: '', remember: false })

const rules = reactive<FormRules>({
  email: [
    { required: true, message: 'Email wajib diisi', trigger: 'blur' },
    { type: 'email', message: 'Format email tidak valid', trigger: 'blur' }
  ],
  password: [{ required: true, message: 'Password wajib diisi', trigger: 'blur' }],
})

// Submit Login
const submitForm = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (valid) {
      isSubmitting.value = true
      try {
        const response = await apiFetch<any>('/auth/login', { method: 'POST', body: form })
        authUser.value = response.user || response

        ElMessage.success(`Selamat datang, ${authUser.value.name}`)

        // Role 1 (Pimpinan) dan Role 2 (Admin) diarahkan ke Dashboard
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

// ==========================================
// LOGIKA LUPA PASSWORD
// ==========================================
const showForgotModal = ref(false)
const isSendingReset = ref(false)
const forgotFormRef = ref<FormInstance>()

const forgotForm = reactive({
  email: ''
})

const forgotRules = reactive<FormRules>({
  email: [
    { required: true, message: 'Email wajib diisi', trigger: 'blur' },
    { type: 'email', message: 'Format email tidak valid', trigger: 'blur' }
  ]
})

// Buka modal & isi otomatis email jika sudah sempat diketik di form login
const openForgotModal = () => {
  if (form.email) {
    forgotForm.email = form.email
  }
  showForgotModal.value = true
}

// Kirim Permintaan Reset Password ke API
const handleForgotPassword = async () => {
  if (!forgotFormRef.value) return
  await forgotFormRef.value.validate(async (valid) => {
    if (valid) {
      isSendingReset.value = true
      try {
        await apiFetch('/auth/forgot-password', {
          method: 'POST',
          body: { email: forgotForm.email }
        })

        ElMessage.success('Tautan reset kata sandi telah dikirim ke email Anda. Silakan cek kotak masuk atau spam.')
        showForgotModal.value = false
        forgotForm.email = ''
      } catch (error) {
        ElMessage.error(getErrorMessage(error, 'Gagal memproses permintaan. Pastikan email terdaftar di sistem.'))
      } finally {
        isSendingReset.value = false
      }
    }
  })
}
</script>

<style scoped>
:deep(.custom-forgot-modal .el-dialog__header) {
  padding-bottom: 0px !important;
}
</style>