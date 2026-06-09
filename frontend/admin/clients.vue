<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="section-title">Clients</h2>
          <p class="text-sm text-[var(--slate-500)]">Manage client list and their brand logos.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Search clients..." clearable class="w-full sm:w-64">
            <template #prefix>
              <Icon icon="solar:magnifer-linear" />
            </template>
          </el-input>
          <el-button type="primary" @click="openCreate">Add Client</el-button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
        <el-table :data="paginatedClients" row-key="id" stripe v-loading="loading" class="min-w-[500px] w-full" @sort-change="handleSort" :default-sort="{ prop: 'position', order: 'ascending' }">
          <el-table-column prop="position" sortable="custom" label="No" width="100" align="center">
            <template #default="{ row }">
              <el-input-number v-model="row.position" :min="1" :step="1" size="small" :controls="false" @change="updatePosition(row)" class="seamless-input !w-full" />
            </template>
          </el-table-column>
          <el-table-column prop="name" sortable="custom" label="Client Name" min-width="240" show-overflow-tooltip />
          <el-table-column label="Logo" min-width="220">
            <template #default="{ row }">
              <div class="flex items-center gap-3">
                <el-image
                  v-if="row.logo?.url"
                  :src="row.logo.url"
                  fit="cover"
                  class="h-10 w-10 rounded-lg border border-slate-100 bg-white"
                />
                <span class="text-sm text-[var(--slate-600)]">
                  {{ row.logo?.name || 'No logo' }}
                </span>
              </div>
            </template>
          </el-table-column>
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
        <span>Showing {{ showingFrom }}–{{ showingTo }} of {{ clients.length }}</span>
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 25, 50, 100]"
          :total="clients.length"
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
      style="max-width: 640px;"
      align-center
      destroy-on-close
      @closed="resetForm"
    >
      <div class="max-h-[70vh] overflow-y-auto pr-2">
        <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
          <div class="grid gap-4 md:grid-cols-2">
            <el-form-item label="Client Name" prop="name">
              <el-input v-model="form.name" placeholder="Client name" />
            </el-form-item>
            <el-form-item label="Number" prop="position">
              <el-input-number v-model="form.position" :min="1" :step="1" class="!w-full" placeholder="Ex: 1" />
            </el-form-item>
          </div>

          <el-form-item label="Logo" prop="logo">
            <el-upload
              action="#"
              list-type="picture-card"
              :auto-upload="false"
              :limit="1"
              accept="image/*"
              v-model:file-list="logoFiles"
              :on-change="handleLogoChange"
              :on-remove="handleLogoRemove"
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
import type { Client } from '~/types/admin'
import { useApi } from '~/composables/useApi'

type ClientForm = {
  name: string
  position: number | null
  logo: UploadUserFile | null
}

type AttachmentApi = {
  id: string
  name: string
  path: string
}

type ClientApi = {
  id: string
  name: string
  position: number | null
  logo: AttachmentApi | null
}

const createDefaultForm = (): ClientForm => ({
  name: '',
  position: null,
  logo: null,
})

const { apiFetch, unwrap, getErrorMessage, uploadAttachment } = useApi()

const clients = ref<Client[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const searchTimeout = ref<ReturnType<typeof setTimeout> | null>(null)

const paginatedClients = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return clients.value.slice(start, start + pageSize.value)
})

const showingFrom = computed(() => clients.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1)
const showingTo = computed(() => Math.min(currentPage.value * pageSize.value, clients.value.length))

const sortProp = ref<string>('position')
const sortOrder = ref<string>('ascending')

const handleSort = ({ prop, order }: { prop: string | null, order: string | null }) => {
  sortProp.value = prop || 'position'
  sortOrder.value = order || 'ascending'
  loadClients()
}

const isDialogOpen = ref(false)
const isEditing = ref(false)
const editingId = ref<string | null>(null)
const formRef = ref<FormInstance>()
const form = reactive<ClientForm>(createDefaultForm())
const logoFiles = ref<UploadUserFile[]>([])

const dialogTitle = computed(() => (isEditing.value ? 'Edit Client' : 'Add Client'))

const rules: FormRules<ClientForm> = {
  name: [{ required: true, message: 'Client name is required', trigger: 'blur' }],
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

const mapAttachment = (attachment: AttachmentApi | null): UploadUserFile | null => {
  if (!attachment) return null
  return {
    uid: attachment.id as any,
    attachmentId: attachment.id,
    name: attachment.name,
    url: attachment.path,
  } as any
}

const mapClient = (client: ClientApi): Client => ({
  id: client.id,
  name: client.name ?? '',
  position: client.position !== null ? Number(client.position) : null,
  logo: mapAttachment(client.logo),
})

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

const openEdit = (client: Client) => {
  isEditing.value = true
  editingId.value = client.id
  Object.assign(form, {
    name: client.name,
    position: client.position || null,
    logo: client.logo ?? null,
  })
  logoFiles.value = client.logo ? [client.logo] : []
  isDialogOpen.value = true
  nextTick(() => formRef.value?.clearValidate())
}

const isUuid = (value: string) =>
  /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(value)

const resolveAttachmentId = (file: any | null) => {
  if (!file) return null
  const uidStr = String(file.attachmentId || file.uid || '')
  return isUuid(uidStr) ? uidStr : null
}

const handleLogoChange = async (file: UploadFile) => {
  if (!file.raw) return
  try {
    const attachment = await uploadAttachment(file.raw, {
      attachmentableType: 'App\\Models\\Client',
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

const buildPayload = () => ({
  name: form.name,
  position: form.position !== null ? Number(form.position) : null,
  logo_id: resolveAttachmentId(form.logo),
})

const loadClients = async () => {
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
    const response = await apiFetch<ClientApi[] | { data: ClientApi[] }>(`/clients?${query.toString()}`)
    clients.value = unwrap(response).map(mapClient)
    currentPage.value = 1
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to load clients.'))
  } finally {
    loading.value = false
  }
}

watch(searchQuery, () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    loadClients()
  }, 300)
})

const submitForm = async () => {
  const formEl = formRef.value
  if (!formEl) return

  await formEl.validate(async (valid) => {
    if (valid) {
      if (form.logo && !resolveAttachmentId(form.logo)) {
        ElMessage.warning('Upload logo belum terhubung, simpan tanpa logo.')
      }

      try {
        const payload = buildPayload()

        if (isEditing.value && editingId.value) {
          const updated = await apiFetch<ClientApi>(`/clients/${editingId.value}`, {
            method: 'PUT',
            body: payload,
          })
          const next = mapClient(updated)
          const index = clients.value.findIndex((item) => item.id === updated.id)
          if (index !== -1) {
            clients.value[index] = next
          } else {
            clients.value.unshift(next)
          }
          ElMessage.success('Client updated')
        } else {
          const created = await apiFetch<ClientApi>('/clients', {
            method: 'POST',
            body: payload,
          })
          clients.value.unshift(mapClient(created))
          ElMessage.success('Client created')
        }
        closeDialog()
      } catch (error) {
        ElMessage.error(getErrorMessage(error, 'Failed to save client.'))
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

const updatePosition = async (row: Client) => {
  try {
    const logoUid = row.logo?.uid ? String(row.logo.uid) : ''
    await apiFetch(`/clients/${row.id}`, {
      method: 'PUT',
      body: {
        name: row.name,
        position: row.position !== null ? Number(row.position) : null,
        logo_id: isUuid(logoUid) ? logoUid : null,
      },
    })
    ElMessage.success('Position updated')
    loadClients()
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to update position.'))
    loadClients()
  }
}

const confirmDelete = async (client: Client) => {
  try {
    await ElMessageBox.confirm(
      `Delete ${client.name}?`,
      'Delete Client',
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
    await apiFetch(`/clients/${client.id}`, { method: 'DELETE' })
    clients.value = clients.value.filter((item) => item.id !== client.id)
    ElMessage.success('Client deleted')
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to delete client.'))
  }
}

onMounted(() => {
  loadClients()
})
</script>

<style scoped>
:deep(.seamless-input .el-input__wrapper) {
  box-shadow: none !important;
  background-color: transparent !important;
  padding: 0;
}
:deep(.seamless-input:hover .el-input__wrapper),
:deep(.seamless-input:focus-within .el-input__wrapper) {
  box-shadow: 0 0 0 1px var(--el-border-color) inset !important;
  background-color: var(--el-fill-color-light) !important;
}
:deep(.seamless-input .el-input__inner) {
  text-align: center;
  font-weight: 600;
  color: var(--el-text-color-primary);
  transition: all 0.2s;
}
</style>
