<template>
  <div class="w-full bg-slate-50 px-6 py-14 flex-1 flex items-center justify-center">

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
            class="!bg-[#1A439C] !border-[#1A439C] hover:!bg-[#4367b3] text-white"
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

const onSubmit = async () => {
  const formEl = formRef.value
  if (!formEl) return

  await formEl.validate(async (valid) => {
    if (valid) {
      console.log('Form Data Submitted:', form)
      try {
        const response = await $fetch('http://localhost:8000/api/contact', {
          method: 'POST',
          body: { ...form }
        })

        ElMessage({
          message: 'Inquiry Form Submitted & Email Sent Successfully!',
          type: 'success',
        })

        formEl.resetFields()
      } catch (error) {
        console.error('Gagal mengirim data ke Laravel:', error)
        ElNotification({
          title: 'Submission Failed',
          message: 'Could not connect to the server or database error occur.',
          type: 'error',
        })
      }
    } else {
      ElNotification({
        title: 'Error',
        message: 'Please Complete the Form Correctly',
        type: 'error',
      })
    }
  })
}
</script>