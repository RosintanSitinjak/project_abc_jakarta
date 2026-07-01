<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="section-title">Services</h2>
          <p class="text-sm text-[var(--slate-500)]">Define service offerings and scopes.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
          <!-- TOMBOL BARU: Hapus Data Sampah -->
          <el-button 
            v-if="services.length > 5"
            type="danger" 
            plain 
            @click="bulkDeleteSpam"
          >
            Hapus Data Sampah
          </el-button>

          <el-input v-model="searchQuery" placeholder="Search services..." clearable class="w-full sm:w-64">
            <template #prefix>
              <Icon icon="tabler:search" />
            </template>
          </el-input>

          <el-button type="primary" @click="openCreate">Add Service</el-button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
        <el-table 
          v-loading="loading" 
          :data="paginatedServices" 
          row-key="id" 
          stripe 
          class="min-w-[700px] w-full" 
          @sort-change="handleSort" 
          :default-sort="{ prop: 'name', order: 'ascending' }"
        >
          <el-table-column prop="name" sortable="custom" label="Service Name" min-width="200" show-overflow-tooltip />
          <el-table-column prop="description" sortable="custom" label="Description" min-width="260" show-overflow-tooltip />
          
          <el-table-column label="Scopes" min-width="220">
            <template #default="{ row }">
              <div class="flex flex-wrap gap-2">
                <el-tag v-for="scope in row.scopes" :key="scope.id" size="small" type="info">
                  {{ scope.scope }}
                </el-tag>
                <span v-if="row.scopes.length === 0" class="text-xs text-[var(--slate-500)]">No scopes</span>
              </div>
            </template>
          </el-table-column>

          <el-table-column label="Thumbnail" min-width="160">
            <template #default="{ row }">
              <span class="text-sm text-[var(--slate-600)]">
                {{ row.thumbnail?.name || 'No thumbnail' }}
              </span>
            </template>
          </el-table-column>

          <!-- KOLOM AKSI: Diperbaiki agar tombol lebih terlihat dan rapi -->
          <el-table-column label="Actions" width="180" align="center">
            <template #default="{ row }">
              <div class="flex justify-center gap-2">
                <el-button size="small" type="primary" plain @click="openEdit(row)">
                  <Icon icon="solar:pen-2-outline" class="mr-1" /> Edit
                </el-button>
                <el-button size="small" type="danger" plain @click="confirmDelete(row)">
                  <Icon icon="solar:trash-bin-trash-outline" class="mr-1" /> Hapus
                </el-button>
              </div>
            </template>
          </el-table-column>
        </el-table>
      </div>

      <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-sm text-[var(--slate-500)]">
        <span>Showing {{ showingFrom }}–{{ showingTo }} of {{ services.length }} data</span>
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 25, 50, 100]"
          :total="services.length"
          layout="sizes, prev, pager, next"
          size="small"
          background
        />
      </div>
    </section>

    <!-- Dialog Form -->
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
          <el-form-item label="Service Name" prop="name">
            <el-input v-model="form.name" placeholder="Contoh: IT Development" />
          </el-form-item>

          <el-form-item label="Description">
            <el-input v-model="form.description" type="textarea" :rows="3" placeholder="Jelaskan detail layanan..." />
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
              placeholder="Tambahkan scope (Tekan Enter)"
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
        </el-form>
      </div>

      <template #footer>
        <el-button @click="closeDialog">Cancel</el-button>
        <el-button type="primary" @click="submitForm">
          {{ isEditing ? 'Update Service' : 'Create Service' }}
        </el-button>
      </template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { ElMessage, ElMessageBox, ElNotification } from 'element-plus'
import type { FormInstance, FormRules, UploadFile, UploadUserFile } from 'element-plus'
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import AdminShell from '~/components/Admin/Shell.vue'
import type { Service, ServiceScope } from '~/types/admin'
import { useApi } from '~/composables/useApi'

// --- Types ---
type ServiceForm = {
  name: string
  description: string
  scopes: string[]
  thumbnail: UploadUserFile | null
}

type ServiceScopeApi = { id: string; service_id: string; scope: string }
type AttachmentApi = { id: string; name: string; path: string }
type ServiceApi = {
  id: string
  name: string
  slug: string | null
  description: string | null
  content: string | null
  scopes: ServiceScopeApi[]
  thumbnail: AttachmentApi | null
}

// --- State & Constants ---
const route = useRoute()
const { apiFetch, unwrap, getErrorMessage, uploadAttachment } = useApi()

const scopeOptions = ['Concept', '3D Modeling', 'Construction', 'Material Selection', 'Installation', 'Maintenance']
const services = ref<Service[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const searchTimeout = ref<ReturnType<typeof setTimeout> | null>(null)
const sortProp = ref<string>('name')
const sortOrder = ref<string>('ascending')

const isDialogOpen = ref(false)
const isEditing = ref(false)
const editingId = ref<string | null>(null)
const editingScopes = ref<ServiceScope[]>([])
const formRef = ref<FormInstance>()
const thumbnailFiles = ref<UploadUserFile[]>([])

const createDefaultForm = (): ServiceForm => ({
  name: '',
  description: '',
  scopes: [],
  thumbnail: null,
})
const form = reactive<ServiceForm>(createDefaultForm())

// --- Computed ---
const paginatedServices = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return services.value.slice(start, start + pageSize.value)
})
const showingFrom = computed(() => (services.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1))
const showingTo = computed(() => Math.min(currentPage.value * pageSize.value, services.value.length))
const dialogTitle = computed(() => (isEditing.value ? 'Edit Service' : 'Add Service'))

// --- Validation ---
const rules: FormRules<ServiceForm> = {
  name: [{ required: true, message: 'Service name is required', trigger: 'blur' }],
  thumbnail: [
    {
      required: true,
      validator: (_rule, value, callback) => {
        if (!value) callback(new Error('Thumbnail harus diisi!'))
        else callback()
      },
      trigger: 'change',
    },
  ],
}

// --- Helper Mappers ---
const mapAttachment = (attachment: AttachmentApi | null): UploadUserFile | null => {
  if (!attachment) return null
  return {
    uid: attachment.id as any,
    attachmentId: attachment.id,
    name: attachment.name,
    url: attachment.path,
  } as any
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

const isUuid = (value: string) => /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(value)
const resolveAttachmentId = (file: any | null) => {
  if (!file) return null
  const uidStr = String(file.attachmentId || file.uid || '')
  return isUuid(uidStr) ? uidStr : null
}

// --- Methods ---
const loadServices = async () => {
  loading.value = true
  try {
    const query = new URLSearchParams()
    if (searchQuery.value) query.append('search', searchQuery.value)
    if (sortProp.value) {
      query.append('sort_by', sortProp.value)
      query.append('sort_order', sortOrder.value === 'descending' ? 'desc' : 'asc')
    }
    const response = await apiFetch<ServiceApi[] | { data: ServiceApi[] }>(`/services?${query.toString()}`)
    services.value = unwrap(response).map(mapService)
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to load services.'))
  } finally {
    loading.value = false
  }
}

const handleSort = ({ prop, order }: { prop: string | null; order: string | null }) => {
  sortProp.value = prop || 'name'
  sortOrder.value = order || 'ascending'
  loadServices()
}

const bulkDeleteSpam = async () => {
  const spam = services.value.filter(s => 
    s.name.toUpperCase().includes('TEST') || 
    s.name.toLowerCase().includes('web service')
  )
  
  if (spam.length === 0) {
    ElMessage.info('Tidak ada data sampah (TEST/Web Service) yang ditemukan.')
    return
  }

  try {
    await ElMessageBox.confirm(
      `Hapus ${spam.length} data sampah yang mengandung nama "TEST" atau "Web Service"?`,
      'Pembersihan Massal',
      { type: 'warning', confirmButtonText: 'Ya, Hapus Semua' }
    )
    
    loading.value = true
    for (const item of spam) {
      await apiFetch(`/services/${item.id}`, { method: 'DELETE' })
    }
    ElMessage.success(`${spam.length} data sampah berhasil dihapus!`)
    loadServices()
  } catch (err) {
    if (err !== 'cancel') ElMessage.error('Gagal melakukan hapus masal')
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
      uid: attachment.id,
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

const syncScopes = async (serviceId: string, nextScopes: string[], currentScopes: ServiceScope[]) => {
  const normalized = Array.from(new Set(nextScopes.map(s => s.trim()).filter(Boolean)))
  const currentNames = new Set(currentScopes.map(s => s.scope))

  const toCreate = normalized.filter(s => !currentNames.has(s))
  const toDelete = currentScopes.filter(s => !normalized.includes(s.scope))

  await Promise.all([
    ...toDelete.map(s => apiFetch(`/service-scopes/${s.id}`, { method: 'DELETE' })),
    ...toCreate.map(s => apiFetch('/service-scopes', { method: 'POST', body: { service_id: serviceId, scope: s } }))
  ])
}

const submitForm = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (!valid) {
      ElNotification({ title: 'Error', message: 'Cek kembali form dan thumbnail!', type: 'error' })
      return
    }

    try {
      loading.value = true
      const payload = {
        name: form.name,
        description: form.description || null,
        thumbnail_id: resolveAttachmentId(form.thumbnail),
      }

      let serviceId = editingId.value
      if (isEditing.value && serviceId) {
        await apiFetch(`/services/${serviceId}`, { method: 'PUT', body: payload })
        await syncScopes(serviceId, form.scopes, editingScopes.value)
        ElMessage.success('Service updated')
      } else {
        const created = await apiFetch<ServiceApi>('/services', { method: 'POST', body: payload })
        serviceId = created.id
        await syncScopes(serviceId, form.scopes, [])
        ElMessage.success('Service created')
      }
      
      closeDialog()
      loadServices()
    } catch (error) {
      ElMessage.error(getErrorMessage(error, 'Failed to save service.'))
    } finally {
      loading.value = false
    }
  })
}

const openCreate = () => {
  isEditing.value = false
  editingId.value = null
  resetForm()
  isDialogOpen.value = true
}

const openEdit = (service: Service) => {
  isEditing.value = true
  editingId.value = service.id
  editingScopes.value = [...(service.scopes ?? [])]
  Object.assign(form, {
    name: service.name,
    description: service.description,
    scopes: service.scopes.map(s => s.scope),
    thumbnail: service.thumbnail,
  })
  thumbnailFiles.value = service.thumbnail ? [service.thumbnail] : []
  isDialogOpen.value = true
}

const confirmDelete = async (service: Service) => {
  try {
    await ElMessageBox.confirm(`Hapus layanan "${service.name}"?`, 'Konfirmasi Hapus', {
      type: 'warning',
      confirmButtonClass: 'el-button--danger'
    })
    await apiFetch(`/services/${service.id}`, { method: 'DELETE' })
    ElMessage.success('Service deleted')
    loadServices()
  } catch (err) { /* cancel */ }
}

const resetForm = () => {
  Object.assign(form, createDefaultForm())
  thumbnailFiles.value = []
  editingScopes.value = []
  formRef.value?.clearValidate()
}

const closeDialog = () => { isDialogOpen.value = false }

watch(searchQuery, () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(loadServices, 400)
})

onMounted(loadServices)
</script>