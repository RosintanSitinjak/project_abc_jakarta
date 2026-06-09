<template>
  <div class="min-h-[70vh] bg-slate-50">
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-blue-50">
      <div class="pointer-events-none absolute -top-32 right-0 h-80 w-80 rounded-full bg-blue-200/40 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-32 left-0 h-96 w-96 rounded-full bg-blue-100/60 blur-3xl"></div>

      <div class="mx-auto flex min-h-[70vh] max-w-2xl flex-col items-center justify-center px-4 sm:px-6 py-10">
        <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white/90 p-8 shadow-xl backdrop-blur">
          <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Admin Access</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Sign In</h1>
          </div>

          <el-form ref="formRef" :model="form" :rules="rules" label-position="top" class="mt-8">
            <el-form-item label="Email" prop="email">
              <el-input @keydown.enter="submitForm" v-model="form.email" placeholder="admin@lamsolusi.com" />
            </el-form-item>
            <el-form-item label="Password" prop="password">
              <el-input @keydown.enter="submitForm" v-model="form.password" type="password" show-password placeholder="Masukkan password" />
            </el-form-item>

            <div class="mb-6 flex items-center justify-between text-sm">
              <el-checkbox v-model="form.remember">Remember me</el-checkbox>
              <span class="text-slate-400">Secure access</span>
            </div>

            <el-button type="primary" class="w-full" :loading="isSubmitting" @click="submitForm">
              Masuk
            </el-button>
          </el-form>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ElMessage, ElNotification } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
// import { useApi } from '~/composables/useApi'
import { useApi } from '../../composables/useApi'

definePageMeta({
  layout: 'public',
})

type LoginForm = {
  email: string
  password: string
  remember: boolean
}

const formRef = ref<FormInstance>()
const form = reactive<LoginForm>({
  email: '',
  password: '',
  remember: false,
})

const rules = reactive<FormRules<LoginForm>>({
  email: [
    { required: true, message: 'Email wajib diisi', trigger: 'change' },
    { type: 'email' as const, message: 'Format email tidak valid', trigger: 'change' },
  ],
  password: [{ required: true, message: 'Password wajib diisi', trigger: 'change' }],
})

const isSubmitting = ref(false)
const router = useRouter()
const { apiFetch, getErrorMessage } = useApi()

const submitForm = async () => {
  const formEl = formRef.value
  if (!formEl) return

  await formEl.validate(async (valid) => {
    if (valid) {
      isSubmitting.value = true
      try {
        await apiFetch('/auth/login', {
          method: 'POST',
          body: {
            email: form.email,
            password: form.password,
            remember: form.remember,
          },
        })
        ElMessage.success('Login berhasil')
        await router.push('/admin/dashboard')
      } catch (error) {
        ElMessage.error(getErrorMessage(error, 'Login gagal.'))
      } finally {
        isSubmitting.value = false
      }
    } else {
      ElNotification({
        title: 'Error',
        message: 'Email dan Password wajib diisi dengan benar',
        type: 'error',
      })
    }
  })
}
</script>
