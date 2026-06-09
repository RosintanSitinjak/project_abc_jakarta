<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="section-title">Site Settings</h2>
          <p class="text-sm text-[var(--slate-500)]">Manage primary configuration for the website.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Search settings..." clearable class="w-full sm:w-64">
            <template #prefix>
              <Icon icon="solar:magnifer-linear" />
            </template>
          </el-input>
          <el-button type="primary" @click="openCreate">Add Setting</el-button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
        <el-table :data="paginatedSettings" row-key="id" stripe class="min-w-[600px] w-full" @sort-change="handleSort" :default-sort="{ prop: 'siteName', order: 'ascending' }">
          <el-table-column prop="siteName" sortable="custom" label="Site Name" min-width="180" show-overflow-tooltip />
          <el-table-column prop="email" sortable="custom" label="Email" min-width="200" show-overflow-tooltip />
          <el-table-column prop="phone" sortable="custom" label="Phone" min-width="140" />
          <el-table-column prop="whatsappNumber" sortable="custom" label="WhatsApp" min-width="160" />
          <el-table-column label="Actions" width="150" align="center">
            <template #default="{ row }">
              <el-button text type="primary" aria-label="Edit" @click="openEdit(row)">
                <Icon icon="solar:pen-2-outline" class="text-lg" />
              </el-button>
              <el-button text type="danger" aria-label="Delete" @click="confirmDelete(row)">
                <Icon icon="solar:trash-bin-trash-outline" class="text-lg" />
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>

      <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-sm text-[var(--slate-500)]">
        <span>Showing {{ showingFrom }}–{{ showingTo }} of {{ siteSettings.length }}</span>
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 25, 50, 100]"
          :total="siteSettings.length"
          layout="sizes, prev, pager, next"
          size="small"
          background
        />
      </div>
    </section>

    <el-dialog
      v-model="isDialogOpen"
      :title="dialogTitle"
      width="90%"
      style="max-width: 760px;"
      align-center
      destroy-on-close
      @closed="resetForm"
    >
      <div class="max-h-[70vh] overflow-y-auto pr-2">
        <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
          <div class="grid gap-4 md:grid-cols-2">
            <el-form-item label="Site Name" prop="siteName">
              <el-input v-model="form.siteName" placeholder="Admin Studio" />
            </el-form-item>
            <el-form-item label="Email" prop="email">
              <el-input v-model="form.email" type="email" placeholder="hello@Admin.id" />
            </el-form-item>
            <el-form-item label="Phone">
              <el-input v-model="form.phone" type="tel" placeholder="+62 812 3456 7890" />
            </el-form-item>
            <el-form-item label="WhatsApp Number">
              <el-input v-model="form.whatsappNumber" type="tel" placeholder="+62 811 1111 2222" />
            </el-form-item>
          </div>

          <el-form-item label="Address" class="mt-2">
            <el-input v-model="form.address" type="textarea" :rows="2" placeholder="Jl. Sudirman No. 45, Jakarta" />
          </el-form-item>

          <div class="grid gap-4 md:grid-cols-2">
            <el-form-item label="LinkedIn URL">
              <el-input v-model="form.linkedinUrl" type="url" placeholder="https://linkedin.com/company/Admin" />
            </el-form-item>
            <el-form-item label="Facebook URL">
              <el-input v-model="form.facebookUrl" type="url" placeholder="https://facebook.com/Admin" />
            </el-form-item>
            <el-form-item label="Twitter URL">
              <el-input v-model="form.twitterUrl" type="url" placeholder="https://x.com/Admin" />
            </el-form-item>
            <el-form-item label="Instagram URL">
              <el-input v-model="form.instagramUrl" type="url" placeholder="https://instagram.com/Admin" />
            </el-form-item>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <el-form-item label="Vision">
              <el-input v-model="form.vision" type="textarea" :rows="3" />
            </el-form-item>
            <el-form-item label="Mission">
              <el-input v-model="form.mission" type="textarea" :rows="3" />
            </el-form-item>
          </div>

          <el-form-item label="About Us">
            <el-input v-model="form.aboutUs" type="textarea" :rows="3" />
          </el-form-item>

          <el-form-item label="Site Description">
            <el-input v-model="form.siteDescription" type="textarea" :rows="3" />
          </el-form-item>

          <el-form-item label="Logo" prop="logo">
            <el-upload
              class="w-full"
              drag
              action="#"
              :auto-upload="false"
              :limit="1"
              accept="image/*"
              v-model:file-list="logoFiles"
              :on-change="handleLogoChange"
              :on-remove="handleLogoRemove"
            >
              <div class="flex flex-col items-center justify-center gap-2 py-6 text-[var(--slate-500)]">
                <span class="text-lg font-semibold text-[var(--blue-900)]">Upload</span>
                <p class="text-xs">SVG, PNG, JPG up to 2MB</p>
              </div>
            </el-upload>
          </el-form-item>
        </el-form>
      </div>

      <template #footer>
        <el-button @click="closeDialog">Cancel</el-button>
        <el-button type="primary" @click="submitForm">
          {{ isEditing ? 'Update' : 'Create' }}
        </el-button>
      </template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { ElMessage, ElMessageBox, ElNotification } from 'element-plus'
import type { FormInstance, FormRules, UploadFile, UploadUserFile } from 'element-plus'
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue'
import AdminShell from '~/components/Admin/Shell.vue'
import type { SiteSetting } from '~/types/admin'
import { useApi } from '~/composables/useApi'

type AttachmentApi = {
  id: string
  name: string
  path: string
}

type SiteSettingApi = {
  id: string
  site_name: string | null
  email: string | null
  phone: string | null
  whatsapp_number: string | null
  address: string | null
  linkedin_url: string | null
  facebook_url: string | null
  twitter_url: string | null
  instagram_url: string | null
  vision: string | null
  mission: string | null
  about_us: string | null
  site_description: string | null
  logo: AttachmentApi | null
}

const createDefaultForm = (): SiteSetting => ({
  id: '',
  siteName: '',
  email: '',
  phone: '',
  whatsappNumber: '',
  address: '',
  linkedinUrl: '',
  facebookUrl: '',
  twitterUrl: '',
  instagramUrl: '',
  vision: '',
  mission: '',
  aboutUs: '',
  siteDescription: '',
  logo: null,
})

const siteSettings = ref<SiteSetting[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const searchTimeout = ref<ReturnType<typeof setTimeout> | null>(null)

const paginatedSettings = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return siteSettings.value.slice(start, start + pageSize.value)
})

const showingFrom = computed(() => siteSettings.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1)
const showingTo = computed(() => Math.min(currentPage.value * pageSize.value, siteSettings.value.length))

const sortProp = ref<string>('siteName')
const sortOrder = ref<string>('ascending')

const handleSort = ({ prop, order }: { prop: string | null, order: string | null }) => {
  sortProp.value = prop || 'siteName'
  sortOrder.value = order || 'ascending'
  loadSiteSettings()
}

const isDialogOpen = ref(false)
const isEditing = ref(false)
const editingId = ref<string | null>(null)
const formRef = ref<FormInstance>()
const form = reactive<SiteSetting>(createDefaultForm())
const logoFiles = ref<UploadUserFile[]>([])

const dialogTitle = computed(() => (isEditing.value ? 'Edit Site Setting' : 'Add Site Setting'))

const rules: FormRules<SiteSetting> = {
  siteName: [{ required: true, message: 'Site name is required', trigger: 'blur' }],
  email: [
    { required: true, message: 'Email is required', trigger: 'blur' },
    { type: 'email', message: 'Enter a valid email', trigger: 'blur' },
  ],
  logo: [
    {
      required: true,
      validator: (_rule, value, callback) => {
        if (!value) callback(new Error('Logo harus diisi!'))
        else callback()
      },
      trigger: ['change', 'blur'],
    },
  ],
}

const resetForm = () => {
  Object.assign(form, createDefaultForm())
  logoFiles.value = []
  formRef.value?.clearValidate()
}

const closeDialog = () => {
  isDialogOpen.value = false
}

const openCreate = () => {
  isEditing.value = false
  editingId.value = null
  resetForm()
  isDialogOpen.value = true
}

const openEdit = (setting: SiteSetting) => {
  isEditing.value = true
  editingId.value = setting.id
  Object.assign(form, {
    ...setting,
    logo: setting.logo ?? null,
  })
  logoFiles.value = setting.logo ? [setting.logo] : []
  isDialogOpen.value = true
  nextTick(() => formRef.value?.clearValidate())
}

const handleLogoChange = async (file: UploadFile) => {
  if (!file.raw) return
  try {
    const attachment = await uploadAttachment(file.raw, {
      attachmentableType: 'App\\Models\\SiteSetting',
      type: 'logo',
    })
    const uploaded: any = {
      uid: attachment.id as any,
      attachmentId: attachment.id,
      name: attachment.name,
      url: attachment.path,
    }
    logoFiles.value = [uploaded]
    form.logo = uploaded
    ElMessage.success('Logo uploaded')
  } catch (error) {
    logoFiles.value = []
    form.logo = null
    ElMessage.error(getErrorMessage(error, 'Failed to upload logo.'))
  }
}

const handleLogoRemove = () => {
  logoFiles.value = []
  form.logo = null
}

const isUuid = (value: string) =>
  /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(value)

const resolveAttachmentId = (file: any | null) => {
  if (!file) return null
  const uidStr = String(file.attachmentId || file.uid || '')
  return isUuid(uidStr) ? uidStr : null
}

const mapAttachment = (attachment: AttachmentApi | null): UploadUserFile | null => {
  if (!attachment) return null
  return {
    uid: attachment.id as any,
    attachmentId: attachment.id,
    name: attachment.name,
    url: attachment.path,
  } as any
}

const mapSiteSetting = (setting: SiteSettingApi): SiteSetting => ({
  id: setting.id,
  siteName: setting.site_name ?? '',
  email: setting.email ?? '',
  phone: setting.phone ?? '',
  whatsappNumber: setting.whatsapp_number ?? '',
  address: setting.address ?? '',
  linkedinUrl: setting.linkedin_url ?? '',
  facebookUrl: setting.facebook_url ?? '',
  twitterUrl: setting.twitter_url ?? '',
  instagramUrl: setting.instagram_url ?? '',
  vision: setting.vision ?? '',
  mission: setting.mission ?? '',
  aboutUs: setting.about_us ?? '',
  siteDescription: setting.site_description ?? '',
  logo: mapAttachment(setting.logo),
})

const buildPayload = () => ({
  site_name: form.siteName || null,
  email: form.email || null,
  phone: form.phone || null,
  whatsapp_number: form.whatsappNumber || null,
  address: form.address || null,
  linkedin_url: form.linkedinUrl || null,
  facebook_url: form.facebookUrl || null,
  twitter_url: form.twitterUrl || null,
  instagram_url: form.instagramUrl || null,
  vision: form.vision || null,
  mission: form.mission || null,
  about_us: form.aboutUs || null,
  site_description: form.siteDescription || null,
  logo_id: resolveAttachmentId(form.logo),
})

const { apiFetch, unwrap, getErrorMessage, uploadAttachment } = useApi()

const loadSiteSettings = async () => {
  loading.value = true
  try {
    const query = new URLSearchParams()
    if (searchQuery.value) {
      query.append('search', searchQuery.value)
    }
    if (sortProp.value) {
      query.append('sort_by', sortProp.value)
      query.append('sort_order', sortOrder.value === 'descending' ? 'desc' : 'asc')
    }
    const response = await apiFetch<SiteSettingApi[] | { data: SiteSettingApi[] }>(`/site-settings?${query.toString()}`)
    siteSettings.value = unwrap(response).map(mapSiteSetting)
    currentPage.value = 1
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to load site settings.'))
  } finally {
    loading.value = false
  }
}

watch(searchQuery, () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    loadSiteSettings()
  }, 300)
})

const submitForm = async () => {
  const formEl = formRef.value
  if (!formEl) return

  await formEl.validate(async (valid) => {
    if (valid) {
      try {
        const payload = buildPayload()
        if (form.logo && !resolveAttachmentId(form.logo)) {
          ElMessage.warning('Upload logo belum terhubung, simpan tanpa logo.')
        }

        if (isEditing.value && editingId.value) {
          const updated = await apiFetch<SiteSettingApi>(`/site-settings/${editingId.value}`, {
            method: 'PUT',
            body: payload,
          })
          const next = mapSiteSetting(updated)
          const index = siteSettings.value.findIndex((item) => item.id === updated.id)
          if (index !== -1) {
            siteSettings.value[index] = next
          } else {
            siteSettings.value.unshift(next)
          }
          ElMessage.success('Site setting updated')
        } else {
          const created = await apiFetch<SiteSettingApi>('/site-settings', {
            method: 'POST',
            body: payload,
          })
          siteSettings.value.unshift(mapSiteSetting(created))
          ElMessage.success('Site setting created')
        }

        closeDialog()
      } catch (error) {
        ElMessage.error(getErrorMessage(error, 'Failed to save site setting.'))
      }
    } else {
      ElNotification({
        title: 'Error',
        message: 'Harap lengkapi form dengan benar, termasuk opsi Logo/Gambar!',
        type: 'error',
      })
    }
  })
}

const confirmDelete = async (setting: SiteSetting) => {
  try {
    await ElMessageBox.confirm(
      `Delete ${setting.siteName}?`,
      'Delete Setting',
      {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning',
      },
    )
  } catch {
    return
  }

  try {
    await apiFetch(`/site-settings/${setting.id}`, { method: 'DELETE' })
    siteSettings.value = siteSettings.value.filter((item) => item.id !== setting.id)
    ElMessage.success('Site setting deleted')
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to delete site setting.'))
  }
}

onMounted(() => {
  loadSiteSettings()
})
</script>
