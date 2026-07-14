<template>
  <div class="min-h-screen flex flex-col lg:grid lg:grid-cols-2 bg-[#f2f2f2]">
    <!-- SISI KIRI: Visual Branding (Sama dengan Login) -->
    <div class="hidden lg:flex relative bg-slate-900 items-center justify-center overflow-hidden">
     
      <div class="absolute inset-0 bg-gradient-to-br from-[#00A9C3]"></div>
      <div class="relative z-10 w-full max-w-lg px-12">
        <h1 class="text-6xl font-black text-white leading-none tracking-tighter mb-6">
          Join Our <br/>Community
        </h1>
        <p class="text-white/80 text-lg font-medium">Mulai perjalanan literasi rohani dan kesehatan Anda bersama ABC Jakarta.</p>
      </div>
    </div>

    <!-- SISI KANAN: Form Register -->
    <div class="flex-1 flex items-center justify-center p-8 lg:p-16">
      <div class="w-full max-w-md">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">Daftar Akun</h2>

        <el-form ref="formRef" :model="form" :rules="rules" label-position="top" class="custom-clean-form">
          <el-form-item label="Nama Lengkap / Instansi" prop="name">
            <el-input v-model="form.name" placeholder="Masukkan nama Anda" size="large" />
          </el-form-item>

          <el-form-item label="Email" prop="email">
            <el-input v-model="form.email" placeholder="nama@email.com" size="large" />
          </el-form-item>

          <!-- POIN 2: DROPDOWN TIPE AKUN -->
          <el-form-item label="Tipe Akun" prop="type">
            <el-select v-model="form.type" placeholder="Pilih tipe akun" class="w-full" size="large">
              <el-option label="Jemaat (Umum)" value="jemaat" />
              <el-option label="Gereja" value="gereja" />
              <el-option label="Sekolah" value="sekolah" />
              <el-option label="Penginjil Literatur (LE)" value="penginjil" />
            </el-select>
          </el-form-item>

          <!-- POIN 2: ALERT KHUSUS PENGINJIL -->
          <el-alert
            v-if="form.type === 'penginjil'"
            title="Informasi Verifikasi"
            type="warning"
            description="Akun Penginjil memerlukan verifikasi Admin sebelum harga khusus aktif."
            show-icon
            :closable="false"
            class="mb-6"
          />

          <el-form-item label="Password" prop="password">
            <el-input v-model="form.password" type="password" show-password size="large" />
          </el-form-item>

          <el-form-item label="Konfirmasi Password" prop="password_confirmation">
            <el-input v-model="form.password_confirmation" type="password" show-password size="large" />
          </el-form-item>

          <el-button 
            type="primary" 
            class="w-full !h-12 !rounded-xl !bg-[#00A9C3] font-bold mt-4" 
            @click="handleRegister"
            :loading="isSubmitting"
          >
            DAFTAR SEKARANG
          </el-button>
        </el-form>

        <p class="mt-8 text-center text-gray-500">
          Sudah punya akun? <NuxtLink to="/login" class="text-[#00A9C3] font-bold">Masuk di sini</NuxtLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'auth' })
const { apiFetch, getErrorMessage } = useApi()
const isSubmitting = ref(false)

const form = reactive({
  name: '',
  email: '',
  type: 'jemaat',
  password: '',
  password_confirmation: ''
})

const rules = {
  name: [{ required: true, message: 'Nama wajib diisi', trigger: 'blur' }],
  email: [{ required: true, type: 'email', message: 'Email tidak valid', trigger: 'blur' }],
  type: [{ required: true, message: 'Pilih tipe akun', trigger: 'change' }],
  password: [{ required: true, min: 8, message: 'Minimal 8 karakter', trigger: 'blur' }],
  password_confirmation: [
    { required: true, message: 'Konfirmasi password wajib diisi', trigger: 'blur' },
    { validator: (rule: any, value: any, callback: any) => {
        if (value !== form.password) callback(new Error('Password tidak cocok'))
        else callback()
      }, trigger: 'blur' 
    }
  ]
}

const handleRegister = async () => {
  isSubmitting.value = true
  try {
    await apiFetch('/auth/register', {
      method: 'POST',
      body: form
    })
    ElNotification({ title: 'Berhasil', message: 'Akun Anda telah terdaftar. Silakan login.', type: 'success' })
    navigateTo('/login')
  } catch (e) {
    ElMessage.error(getErrorMessage(e, 'Pendaftaran gagal.'))
  } finally {
    isSubmitting.value = false
  }
}
</script>