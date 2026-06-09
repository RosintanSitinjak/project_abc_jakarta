<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="section-title">Portfolios</h2>
          <p class="text-sm text-[var(--slate-500)]">Manage portfolio entries and documentation images.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Search portfolios..." clearable class="w-full sm:w-64">
            <template #prefix>
              <Icon icon="solar:magnifer-linear" />
            </template>
          </el-input>
          <el-button type="primary" @click="openCreate">Add Portfolio</el-button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
        <el-table :data="paginatedPortfolios" row-key="id" stripe class="min-w-[700px] w-full" @sort-change="handleSort" :default-sort="{ prop: 'title', order: 'ascending' }">
          <el-table-column prop="title" sortable="custom" label="Title" min-width="200" show-overflow-tooltip />
          <el-table-column prop="clientName" sortable="custom" label="Client" min-width="160" show-overflow-tooltip />
          <el-table-column prop="year" sortable="custom" label="Year" width="100" />
          <el-table-column prop="category.name" sortable="custom" label="Category" min-width="140">
            <template #default="{ row }">
              {{ row.category?.name || '-' }}
            </template>
          </el-table-column>
          <el-table-column label="Media" min-width="180">
            <template #default="{ row }">
              <div class="text-sm text-[var(--slate-600)]">
                <p>{{ row.thumbnail?.name || 'No thumbnail' }}</p>
                <p class="text-xs text-[var(--slate-500)]">
                  {{ row.documentations.length }} documentation file(s)
                </p>
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
        <span>Showing {{ showingFrom }}–{{ showingTo }} of {{ portfolios.length }}</span>
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 25, 50, 100]"
          :total="portfolios.length"
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
            <el-form-item label="Title" prop="title">
              <el-input v-model="form.title" placeholder="Luxury Villa Interior" />
            </el-form-item>
            <el-form-item label="Client Name">
              <el-input v-model="form.clientName" placeholder="PT Arjuna" />
            </el-form-item>
            <el-form-item label="Year">
              <el-input-number v-model="form.year" :min="1900" :max="2100" class="w-full" />
            </el-form-item>
            <el-form-item label="Category" prop="categoryId">
              <el-select
                v-model="form.categoryId"
                placeholder="Select or create category"
                class="w-full"
                filterable
                allow-create
                default-first-option
                clearable
              >
                <el-option v-for="category in categories" :key="category.id" :label="category.name" :value="category.id" />
              </el-select>
            </el-form-item>
          </div>

          <el-form-item label="Areas">
            <el-select
              v-model="form.areas"
              multiple
              filterable
              allow-create
              default-first-option
              collapse-tags
              :max-collapse-tags="3"
              placeholder="Add areas"
              class="w-full"
            >
              <el-option v-for="area in areaOptions" :key="area" :label="area" :value="area" />
            </el-select>
          </el-form-item>

          <el-form-item label="Description">
            <el-input v-model="form.description" type="textarea" :rows="3" />
          </el-form-item>

          <div class="grid gap-4 md:grid-cols-2">
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
            <el-form-item label="Documentations">
              <el-upload
                action="#"
                list-type="picture-card"
                :auto-upload="false"
                multiple
                v-model:file-list="documentationFiles"
                :on-change="handleDocumentationChange"
                :on-remove="handleDocumentationRemove"
              >
                <div class="flex h-20 w-20 flex-col items-center justify-center text-xs text-[var(--slate-500)]">
                  <span class="text-lg font-semibold text-[var(--blue-900)]">+</span>
                  <span>Upload</span>
                </div>
              </el-upload>
            </el-form-item>
          </div>
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
import type { Category, Portfolio } from '~/types/admin'
import { useApi } from '~/composables/useApi'

type PortfolioForm = {
  id: string
  title: string
  clientName: string
  year: number | null
  categoryId: string | null
  areas: string[]
  description: string
  thumbnail: UploadUserFile | null
  documentations: UploadUserFile[]
}

type AttachmentApi = {
  id: string
  name: string
  path: string
}

type CategoryApi = {
  id: string
  name: string
  description?: string | null
}

type PortfolioApi = {
  id: string
  category_id: string | null
  title: string
  description: string | null
  client_name: string | null
  year: number | null
  areas: string[] | null
  category: CategoryApi | null
  thumbnail: AttachmentApi | null
  photos: AttachmentApi[]
}

const createDefaultForm = (): PortfolioForm => ({
  id: '',
  title: '',
  clientName: '',
  year: null,
  categoryId: null,
  areas: [],
  description: '',
  thumbnail: null,
  documentations: [],
})

const areaOptions = ['']

const portfolios = ref<Portfolio[]>([])
const categories = ref<Category[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const searchTimeout = ref<ReturnType<typeof setTimeout> | null>(null)

const paginatedPortfolios = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return portfolios.value.slice(start, start + pageSize.value)
})

const showingFrom = computed(() => portfolios.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1)
const showingTo = computed(() => Math.min(currentPage.value * pageSize.value, portfolios.value.length))

const sortProp = ref<string>('title')
const sortOrder = ref<string>('ascending')

const handleSort = ({ prop, order }: { prop: string | null, order: string | null }) => {
  sortProp.value = prop || 'title'
  sortOrder.value = order || 'ascending'
  loadPortfolios()
}

const isDialogOpen = ref(false)
const isEditing = ref(false)
const editingId = ref<string | null>(null)
const formRef = ref<FormInstance>()
const form = reactive<PortfolioForm>(createDefaultForm())
const thumbnailFiles = ref<UploadUserFile[]>([])
const documentationFiles = ref<UploadUserFile[]>([])

const dialogTitle = computed(() => (isEditing.value ? 'Edit Portfolio' : 'Add Portfolio'))

const rules: FormRules<PortfolioForm> = {
  title: [{ required: true, message: 'Title is required', trigger: 'blur' }],
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

const resetForm = () => {
  Object.assign(form, createDefaultForm())
  thumbnailFiles.value = []
  documentationFiles.value = []
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

const openEdit = (portfolio: Portfolio) => {
  isEditing.value = true
  editingId.value = portfolio.id
  Object.assign(form, {
    id: portfolio.id,
    title: portfolio.title,
    clientName: portfolio.clientName,
    year: portfolio.year,
    categoryId: portfolio.categoryId ?? null,
    areas: [...portfolio.areas],
    description: portfolio.description,
    documentations: [...portfolio.documentations],
    thumbnail: portfolio.thumbnail ?? null,
  })
  thumbnailFiles.value = portfolio.thumbnail ? [portfolio.thumbnail] : []
  documentationFiles.value = [...portfolio.documentations]
  isDialogOpen.value = true
  nextTick(() => formRef.value?.clearValidate())
}

const handleThumbnailChange = async (file: UploadFile) => {
  if (!file.raw) return
  try {
    const attachment = await uploadAttachment(file.raw, {
      attachmentableType: 'App\\Models\\Portfolio',
      type: 'thumbnail',
    })
    const uploaded: UploadUserFile = {
      uid: attachment.id as any,
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

const handleDocumentationChange = async (file: UploadFile, fileList: UploadUserFile[]) => {
  if (!file.raw) {
    documentationFiles.value = [...fileList]
    form.documentations = [...fileList]
    return
  }
  try {
    const attachment = await uploadAttachment(file.raw, {
      attachmentableType: 'App\\Models\\Portfolio',
      type: 'photo',
    })
    const uploaded: UploadUserFile = {
      uid: attachment.id as any,
      name: attachment.name,
      url: attachment.path,
    }
    documentationFiles.value = [
      ...documentationFiles.value.filter((item) => String(item.uid) !== String(file.uid) && String(item.uid) !== attachment.id),
      uploaded,
    ]
    form.documentations = [...documentationFiles.value]
    ElMessage.success('Documentation uploaded')
  } catch (error) {
    documentationFiles.value = documentationFiles.value.filter((item) => String(item.uid) !== String(file.uid))
    form.documentations = [...documentationFiles.value]
    ElMessage.error(getErrorMessage(error, 'Failed to upload documentation.'))
  }
}

const handleDocumentationRemove = (_file: UploadFile, fileList: UploadUserFile[]) => {
  documentationFiles.value = [...fileList]
  form.documentations = [...fileList]
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

const mapPortfolio = (portfolio: PortfolioApi): Portfolio => ({
  id: portfolio.id,
  title: portfolio.title ?? '',
  clientName: portfolio.client_name ?? '',
  year: portfolio.year ?? null,
  categoryId: portfolio.category_id ?? null,
  category: portfolio.category
    ? {
        id: portfolio.category.id,
        name: portfolio.category.name,
        description: portfolio.category.description ?? null,
      }
    : null,
  areas: portfolio.areas ?? [],
  description: portfolio.description ?? '',
  thumbnail: mapAttachment(portfolio.thumbnail),
  documentations: (portfolio.photos ?? []).map((photo) => mapAttachment(photo)).filter(Boolean) as UploadUserFile[],
})

const { apiFetch, unwrap, getErrorMessage, uploadAttachment } = useApi()

const resolveCategoryId = async () => {
  if (!form.categoryId) return null
  if (isUuid(form.categoryId)) return form.categoryId

  const name = form.categoryId.trim()
  if (!name) return null

  const existing = categories.value.find(
    (category) => category.name.toLowerCase() === name.toLowerCase(),
  )
  if (existing) return existing.id

  try {
    const created = await apiFetch<CategoryApi>('/categories', {
      method: 'POST',
      body: {
        name,
      },
    })
    const next: Category = {
      id: created.id,
      name: created.name,
      description: created.description ?? null,
    }
    categories.value.unshift(next)
    return created.id
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to create category.'))
    return null
  }
}

const buildPayload = (categoryId: string | null) => ({
  title: form.title,
  category_id: categoryId,
  client_name: form.clientName || null,
  year: form.year ?? null,
  areas: form.areas.length ? form.areas : null,
  description: form.description || null,
  thumbnail_id: resolveAttachmentId(form.thumbnail),
  photo_ids: form.documentations
    .map((file) => resolveAttachmentId(file))
    .filter((value): value is string => Boolean(value)),
})

const loadPortfolios = async () => {
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
    const response = await apiFetch<PortfolioApi[] | { data: PortfolioApi[] }>(`/portfolios?${query.toString()}`)
    portfolios.value = unwrap(response).map(mapPortfolio)
    currentPage.value = 1
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to load portfolios.'))
  } finally {
    loading.value = false
  }
}

watch(searchQuery, () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    loadPortfolios()
  }, 300)
})

const loadCategories = async () => {
  try {
    const response = await apiFetch<CategoryApi[] | { data: CategoryApi[] }>('/categories')
    categories.value = unwrap(response).map((category) => ({
      id: category.id,
      name: category.name,
      description: category.description ?? null,
    }))
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to load categories.'))
  }
}

const submitForm = async () => {
  const formEl = formRef.value
  if (!formEl) return

  await formEl.validate(async (valid) => {
    if (valid) {
      try {
        const resolvedCategoryId = await resolveCategoryId()
        if (form.categoryId && !resolvedCategoryId) return
        form.categoryId = resolvedCategoryId
        const payload = buildPayload(resolvedCategoryId)
        if (form.thumbnail && !resolveAttachmentId(form.thumbnail)) {
          ElMessage.warning('Upload thumbnail belum terhubung, simpan tanpa thumbnail.')
        }
        if (form.documentations.some((file) => file && !resolveAttachmentId(file))) {
          ElMessage.warning('Upload dokumentasi belum terhubung, simpan tanpa dokumentasi.')
        }

        if (isEditing.value && editingId.value) {
          const updated = await apiFetch<PortfolioApi>(`/portfolios/${editingId.value}`, {
            method: 'PUT',
            body: payload,
          })
          const next = mapPortfolio(updated)
          const index = portfolios.value.findIndex((item) => item.id === updated.id)
          if (index !== -1) {
            portfolios.value[index] = next
          } else {
            portfolios.value.unshift(next)
          }
          ElMessage.success('Portfolio updated')
        } else {
          const created = await apiFetch<PortfolioApi>('/portfolios', {
            method: 'POST',
            body: payload,
          })
          portfolios.value.unshift(mapPortfolio(created))
          ElMessage.success('Portfolio created')
        }
        closeDialog()
      } catch (error) {
        ElMessage.error(getErrorMessage(error, 'Failed to save portfolio.'))
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

const confirmDelete = async (portfolio: Portfolio) => {
  try {
    await ElMessageBox.confirm(
      `Delete ${portfolio.title}?`,
      'Delete Portfolio',
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
    await apiFetch(`/portfolios/${portfolio.id}`, { method: 'DELETE' })
    portfolios.value = portfolios.value.filter((item) => item.id !== portfolio.id)
    ElMessage.success('Portfolio deleted')
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to delete portfolio.'))
  }
}

onMounted(() => {
  loadPortfolios()
  loadCategories()
})
</script>
