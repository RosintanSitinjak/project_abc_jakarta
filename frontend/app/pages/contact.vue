<template>
<div class="min-h-screen bg-white text-[#1e293b]">
    <!-- 1. HEADER BANNER: Get In Touch -->
    <section class="py-16 lg:py-20 bg-[#1e293b] text-white text-center relative overflow-hidden">
      <!-- Ornamen Dekorasi -->
      <div class="absolute top-0 right-0 w-64 h-64 bg-[#3b5d95] blur-[100px] opacity-20 -translate-y-1/2 translate-x-1/2"></div>
      
      <div class="container mx-auto px-6 relative z-10">
        <!-- Badge Atas -->
        <span class="inline-block px-4 py-1.5 rounded-full bg-white/10 text-white text-[10px] font-black uppercase tracking-[0.2em] mb-4 border border-white/5">
          Get In Touch
        </span>

        <!-- Judul -->
        <h1 class="text-3xl md:text-5xl font-extrabold mb-5 tracking-tight leading-tight max-w-4xl mx-auto">
          Contact <span class="text-[#8b8e92] italic">Our</span> Expert Team <br class="hidden md:block" />
          <span class="text-xl md:text-2xl font-light opacity-80 uppercase tracking-[0.2em]">Let's Build Something Great Together</span>
        </h1>

        <!-- Deskripsi -->
        <p class="text-gray-200 max-w-2xl mx-auto text-base md:text-lg leading-relaxed font-medium">
          Have a project in mind or need technical consultation? Reach out to us and let's discuss how we can help your business thrive.
        </p>
      </div>
    </section>

    <div class="w-full max-w-4xl mx-auto rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">

      <div class="mb-10">
        <h1 class="text-2xl font-semibold text-black mb-3">
          Formulir Inquiry
        </h1>
        <p class="text-gray-500 text-sm">
          Please fill out the form below to request a quote or consultation.  
        </p>
      </div>

      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-position="top"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <el-form-item label="Full Name" prop="name">
            <el-input v-model="form.name" placeholder="Enter Your Full Name"/>
          </el-form-item>

          <el-form-item label="Company Name" prop="company">
            <el-input v-model="form.company" placeholder="Enter/Type Your Company"/>
          </el-form-item>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <el-form-item label="Business Email" prop="email">
            <el-input v-model="form.email" placeholder="name@company.com" />
          </el-form-item>

          <el-form-item label="Phone Number" prop="phone">
            <el-input v-model="form.phone" placeholder="+62 812..." />
          </el-form-item>
        </div>

        <el-form-item label="Subject / Purpose" prop="subject">
          <el-input v-model="form.subject" placeholder="Example: Server Procurement Proposal" />
        </el-form-item>

        <el-form-item label="Message / Requirements" prop="message">
          <el-input
            v-model="form.message"
            type="textarea"
            :rows="5"
            placeholder="Please describe your project specifications or details..."
          />
        </el-form-item>

        <el-form-item class="mt-6">
          <el-button
            type="primary"
            @click="onSubmit"
            class="!bg-[#0d0d0e] !border-[#333a49] hover:!bg-[#e5e6e9] text-white hover:!text-black"
          >
            Submit
          </el-button>
        </el-form-item>
      </el-form>
      
    </div>
  </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { ElNotification, ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'

definePageMeta({
  layout: 'public',
})

const formRef = ref<FormInstance>()

const form = reactive({
  name: '',
  company: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
})

const rules = reactive<FormRules>({
  name: [{ required: true, message: 'Full Name is Required', trigger: 'blur' }],
  email: [
    { required: true, message: 'Email is Required', trigger: 'blur' },
    { type: 'email', message: 'Please Enter a Valid Email Address', trigger: 'blur' }
  ],
  subject: [{ required: true, message: 'Subject is Required', trigger: 'blur' }],
  message: [{ required: true, message: 'Message is Required', trigger: 'blur' }]
})

const onSubmit = () => {
  const formEl = formRef.value
  if (!formEl) return

  formEl.validate(async (valid) => {
    if (valid) {
      try {
        // Pastikan await $fetch ini selesai dengan benar
        await $fetch('http://localhost:8000/api/contact', {
          method: 'POST',
          body: { ...form }
        });

        ElMessage({
          message: 'Inquiry Form Submitted & Email Sent Successfully!',
          type: 'success',
        });

        formEl.resetFields();
      } catch (error) {
        console.error('Gagal:', error);
        ElNotification({
          title: 'Submission Failed',
          message: 'Terjadi kesalahan pada server.',
          type: 'error',
        });
      }
    } else {
      ElNotification({
        title: 'Error',
        message: 'Please Complete the Form Correctly',
        type: 'error',
      });
    }
  })
}</script>