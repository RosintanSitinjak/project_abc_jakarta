<template>
  <AdminShell>
    <section class="glass-panel p-6" v-loading="loading">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h2 class="section-title">{{ title }}</h2>
          <p class="text-sm text-[var(--slate-500)]">Maintain service details, scopes, and content.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <el-button @click="goBack">Back</el-button>
          <el-button type="primary" :loading="saving" @click="submitForm">
            {{ isEditing ? 'Update' : 'Create' }}
          </el-button>
        </div>
      </div>

      <el-form ref="formRef" :model="form" :rules="rules" label-position="top" class="mt-6">
        <div class="grid gap-4 md:grid-cols-2">
          <el-form-item label="Service Name" prop="name">
            <el-input v-model="form.name" placeholder="IT Development" />
          </el-form-item>
          <el-form-item label="Slug">
            <el-input :model-value="slugPreview" disabled />
          </el-form-item>
        </div>

        <el-form-item label="Description">
          <el-input v-model="form.description" type="textarea" :rows="3" />
        </el-form-item>

        <el-form-item label="Scopes">
          <el-select
            v-model="form.scopes"
            multiple
            filterable
            allow-create
            default-first-option
            collapse-tags
            :max-collapse-tags="3"
            placeholder="Add scopes"
            class="w-full"
          >
            <el-option v-for="option in scopeOptions" :key="option" :label="option" :value="option" />
          </el-select>
        </el-form-item>

        <el-form-item label="Thumbnail" prop="thumbnail">
          <el-upload
            action="#"
            list-type="picture-card"
            :auto-upload="false"
            :limit="1"
            accept="image/*"
            v-model:file-list="thumbnailFiles"
            :on-change="handleThumbnailChange"
            :on-remove="handleThumbnailRemove"
          >
            <div class="flex h-20 w-20 flex-col items-center justify-center text-xs text-[var(--slate-500)]">
              <span class="text-lg font-semibold text-[var(--blue-900)]">+</span>
              <span>Upload</span>
            </div>
          </el-upload>
        </el-form-item>

        <el-form-item label="Content (SFDT)">
          <ClientOnly>
            <SfdtEditor v-model="form.content" height="520px" />
            <template #fallback>
              <div class="rounded-2xl border border-[var(--slate-100)] bg-white p-6 text-sm text-[var(--slate-500)]">
                Loading editor...
              </div>
            </template>
          </ClientOnly>
        </el-form-item>
      </el-form>
    </section>
  </AdminShell>
</template>

<script lang="ts" setup>
import { ElMessage, ElNotification } from 'element-plus'
import type { FormInstance, FormRules, UploadFile, UploadUserFile } from 'element-plus'
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AdminShell from '~/components/Admin/Shell.vue'
import SfdtEditor from '~/components/Syncfusion/SfdtEditor.client.vue'
import type { Service, ServiceScope } from '~/types/admin'
import { useApi } from '~/composables/useApi'
import { registerLicense } from '@syncfusion/ej2-base'


const props = defineProps<{
  serviceId?: string
}>()

type ServiceForm = {
  name: string
  description: string
  scopes: string[]
  thumbnail: UploadUserFile | null
  content: string
}

type ServiceScopeApi = {
  id: string
  service_id: string
  scope: string
}

type AttachmentApi = {
  id: string
  name: string
  path: string
}

type ServiceApi = {
  id: string
  name: string
  slug: string | null
  description: string | null
  content: string | null
  scopes: ServiceScopeApi[]
  thumbnail: AttachmentApi | null
}

const router = useRouter()
const { apiFetch, getErrorMessage, uploadAttachment } = useApi()

const formRef = ref<FormInstance>()
const saving = ref(false)
const loading = ref(false)
const editingScopes = ref<ServiceScope[]>([])
const thumbnailFiles = ref<UploadUserFile[]>([])
const slug = ref('')

const createDefaultForm = (): ServiceForm => ({
  name: '',
  description: '',
  scopes: [],
  thumbnail: null,
  content: '',
})

const form = reactive<ServiceForm>(createDefaultForm())

const scopeOptions = [
  'Concept',
  '3D Modeling',
  'Construction',
  'Material Selection',
  'Installation',
  'Maintenance',
]

const isEditing = computed(() => Boolean(props.serviceId))
const title = computed(() => (isEditing.value ? 'Edit Service' : 'Add Service'))

const rules: FormRules<ServiceForm> = {
  name: [{ required: true, message: 'Service name is required', trigger: 'blur' }],
  thumbnail: [
    {
      required: true,
      validator: (_rule, value, callback) => {
        if (!value) callback(new Error('Thumbnail harus diisi!'))
        else callback()
      },
      trigger: ['change', 'blur'],
    },
  ],
}

const slugify = (value: string) =>
  value
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)+/g, '')

const slugPreview = computed(() => {
  if (isEditing.value && slug.value) return slug.value
  return slugify(form.name || '')
})

const resetForm = () => {
  Object.assign(form, createDefaultForm())
  editingScopes.value = []
  thumbnailFiles.value = []
  slug.value = ''
  formRef.value?.clearValidate()
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
    name: attachment.name,
    url: attachment.path,
  }
}

const mapService = (service: ServiceApi): Service => ({
  id: service.id,
  name: service.name ?? '',
  slug: service.slug ?? '',
  description: service.description ?? '',
  content: service.content ?? '',
  scopes: (service.scopes ?? []).map((scope) => ({
    id: scope.id,
    serviceId: scope.service_id,
    scope: scope.scope,
  })),
  thumbnail: mapAttachment(service.thumbnail),
})

const buildPayload = () => ({
  name: form.name,
  description: form.description || null,
  content: form.content || null,
  thumbnail_id: resolveAttachmentId(form.thumbnail),
})

const normalizeScopes = (values: string[]) =>
  Array.from(new Set(values.map((value) => value.trim()).filter(Boolean)))

const syncScopes = async (serviceId: string, nextScopes: string[], currentScopes: ServiceScope[]) => {
  const normalized = normalizeScopes(nextScopes)
  const current = currentScopes ?? []
  const currentNames = new Set(current.map((scope) => scope.scope))

  const toCreate = normalized.filter((scope) => !currentNames.has(scope))
  const toDelete = current.filter((scope) => !normalized.includes(scope.scope))

  await Promise.all(
    toDelete.map((scope) =>
      apiFetch(`/service-scopes/${scope.id}`, {
        method: 'DELETE',
      }),
    ),
  )

  await Promise.all(
    toCreate.map((scope) =>
      apiFetch('/service-scopes', {
        method: 'POST',
        body: { service_id: serviceId, scope },
      }),
    ),
  )
}

const loadService = async (id: string) => {
  loading.value = true
  try {
    const service = await apiFetch<ServiceApi>(`/services/${id}`)
    const mapped = mapService(service)
    Object.assign(form, {
      name: mapped.name,
      description: mapped.description,
      scopes: mapped.scopes.map((scope) => scope.scope),
      thumbnail: mapped.thumbnail ?? null,
      content: mapped.content ?? '',
    })
    editingScopes.value = [...mapped.scopes]
    thumbnailFiles.value = mapped.thumbnail ? [mapped.thumbnail] : []
    slug.value = mapped.slug
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to load service.'))
  } finally {
    loading.value = false
  }
}

const handleThumbnailChange = async (file: UploadFile) => {
  if (!file.raw) return
  try {
    const attachment = await uploadAttachment(file.raw, {
      attachmentableType: 'App\\Models\\Service',
      type: 'thumbnail',
    })
    const uploaded: any = {
      uid: attachment.id as any,
      attachmentId: attachment.id,
      name: attachment.name,
      url: attachment.path,
    }
    thumbnailFiles.value = [uploaded]
    form.thumbnail = uploaded
    ElMessage.success('Thumbnail uploaded')
  } catch (error) {
    thumbnailFiles.value = []
    form.thumbnail = null
    ElMessage.error(getErrorMessage(error, 'Failed to upload thumbnail.'))
  }
}

const handleThumbnailRemove = () => {
  thumbnailFiles.value = []
  form.thumbnail = null
}

const submitForm = async () => {
  const formEl = formRef.value
  if (!formEl) return

  await formEl.validate(async (valid) => {
    if (valid) {
      if (form.thumbnail && !resolveAttachmentId(form.thumbnail)) {
        ElMessage.warning('Upload thumbnail belum terhubung, simpan tanpa thumbnail.')
      }

      saving.value = true
      try {
        const payload = buildPayload()
        const scopeValues = normalizeScopes(form.scopes)

        if (isEditing.value && props.serviceId) {
          const updated = await apiFetch<ServiceApi>(`/services/${props.serviceId}`, {
            method: 'PUT',
            body: payload,
          })
          await syncScopes(updated.id, scopeValues, editingScopes.value)
          const refreshed = await apiFetch<ServiceApi>(`/services/${updated.id}`)
          const mapped = mapService(refreshed)
          editingScopes.value = [...mapped.scopes]
          slug.value = mapped.slug
          ElMessage.success('Service updated')
        } else {
          const created = await apiFetch<ServiceApi>('/services', {
            method: 'POST',
            body: payload,
          })
          await syncScopes(created.id, scopeValues, [])
          ElMessage.success('Service created')
          await router.push(`/admin/services/${created.id}`)
          return
        }
      } catch (error) {
        ElMessage.error(getErrorMessage(error, 'Failed to save service.'))
      } finally {
        saving.value = false
      }
    } else {
      ElNotification({
        title: 'Error',
        message: 'Harap lengkapi form dengan benar, termasuk Thumbnail!',
        type: 'error',
      })
    }
  })
}

const goBack = () => {
  router.push('/admin/services')
}

watch(
  () => props.serviceId,
  (value) => {
    resetForm()
    if (value) {
      loadService(value)
    }
  },
)

onMounted(() => {
  if (props.serviceId) {
    loadService(props.serviceId)
  }
  registerLicense('Ngo9BigBOggjHTQxAR8/V1JFaF5cXGRCf1FpRmJGdld5fUVHYVZUTXxaS00DNHVRdkdmWH5cdnRWQmZfUkF0X0dWYEg=')
})
</script>
