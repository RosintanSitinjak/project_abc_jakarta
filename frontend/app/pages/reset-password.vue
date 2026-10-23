<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-3xl p-8 sm:p-10 border border-gray-100 shadow-xl shadow-slate-200/60 text-center">
      
      <!-- Icon & Header -->
      <div class="w-16 h-16 mx-auto rounded-2xl bg-[#00A9C3]/10 text-[#00A9C3] flex items-center justify-center mb-4 border border-[#00A9C3]/20">
        <Icon icon="solar:key-minimalistic-square-3-bold" class="text-3xl" />
      </div>
      <h2 class="text-2xl font-black text-gray-900 tracking-tight">Buat Password Baru</h2>
      <p class="text-xs text-gray-500 mt-1 mb-8 leading-relaxed">
        Silakan masukkan password baru Anda untuk akun <b class="text-gray-700">{{ form.email }}</b>
      </p>

      <!-- Form Reset Password -->
      <el-form ref="formRef" :model="form" :rules="rules" label-position="top" class="text-left">
        
        <el-form-item label="Password Baru" prop="password">
          <el-input 
            v-model="form.password" 
            type="password" 
            show-password 
            placeholder="Minimal 8 karakter" 
            size="large" 
            class="modern-input" 
          />
        </el-form-item>

        <el-form-item label="Konfirmasi Password Baru" prop="password_confirmation">
          <el-input 
            v-model="form.password_confirmation" 
            type="password" 
            show-password 
            placeholder="Ulangi password baru" 
            size="large" 
            class="modern-input" 
            @keyup.enter="handleResetPassword" 
          />
        </el-form-item>

        <el-button 
          type="primary" 
          :loading="isSubmitting" 
          @click="handleResetPassword" 
          class="w-full !h-12 !rounded-xl !bg-[#00A9C3] !border-none font-bold uppercase tracking-widest text-xs shadow-md shadow-[#00A9C3]/20 hover:!bg-[#008ba0] mt-4"
        >
          Simpan Password Baru
        </el-button>
      </el-form>

      <div class="mt-8 pt-6 border-t border-slate-100 text-center">
        <NuxtLink to="/login" class="text-xs font-bold text-[#00A9C3] hover:underline flex items-center justify-center gap-1">
          <span>← Kembali ke Halaman Login</span>
        </NuxtLink>
      </div>

    </div>
  </div>
</template>

<script lang="ts" setup>
import { Icon } from '@iconify/vue'
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi } from '../../composables/useApi'

definePageMeta({ layout: 'auth' })

const route = useRoute()
const router = useRouter()
const { apiFetch, getErrorMessage } = useApi()

const formRef = ref<FormInstance>()
const isSubmitting = ref(false)

const form = reactive({
  token: '',
  email: '',
  password: '',
  password_confirmation: ''
})

// Ambil token dan email dari parameter URL yang diklik dari email
onMounted(() => {
  // Menangkap jika URL berbentuk: /reset-password?token=...&email=...
  // Atau /reset-password/abc123token?email=...
  form.token = (route.query.token as string) || (route.params.token as string) || ''
  form.email = (route.query.email as string) || ''

  if (!form.token) {
    ElMessage.warning('Tautan reset password tidak valid atau sudah kedaluwarsa.')
  }
})

const rules = reactive<FormRules>({
  password: [
    { required: true, message: 'Password baru wajib diisi', trigger: 'blur' },
    { min: 8, message: 'Password minimal 8 karakter', trigger: 'blur' }
  ],
  password_confirmation: [
    { required: true, message: 'Konfirmasi password wajib diisi', trigger: 'blur' },
    {
      validator: (rule, value, callback) => {
        if (value !== form.password) {
          callback(new Error('Konfirmasi password tidak cocok!'))
        } else {
          callback()
        }
      },
      trigger: 'blur'
    }
  ]
})

const handleResetPassword = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (valid) {
      isSubmitting.value = true
      try {
        await apiFetch('/auth/reset-password', {
          method: 'POST',
          body: {
            token: form.token,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation
          }
        })

        ElMessage.success('Password berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.')
        await router.push('/login')
      } catch (error) {
        ElMessage.error(getErrorMessage(error, 'Gagal memperbarui password. Tautan mungkin telah kedaluwarsa.'))
      } finally {
        isSubmitting.value = false
      }
    }
  })
}
</script>