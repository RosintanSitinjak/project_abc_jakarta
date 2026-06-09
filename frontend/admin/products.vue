<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="section-title">Products</h2>
          <p class="text-sm text-[var(--slate-500)]">Create and manage product catalog entries.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Search products..." clearable class="w-full sm:w-64">
            <template #prefix>
              <Icon icon="solar:magnifer-linear" />
            </template>
          </el-input>
          <el-button type="primary" @click="openCreate">Add Product</el-button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
        <el-table :data="paginatedProducts" row-key="id" stripe class="min-w-[600px] w-full" @sort-change="handleSort" :default-sort="{ prop: 'position', order: 'ascending' }">
          <el-table-column prop="position" sortable="custom" label="No" width="100" align="center">
            <template #default="{ row }">
              <el-input-number v-model="row.position" :min="1" :step="1" size="small" :controls="false" @change="updatePosition(row)" class="seamless-input !w-full" />
            </template>
          </el-table-column>
          <el-table-column prop="title" sortable="custom" label="Product" min-width="200" show-overflow-tooltip />
          <el-table-column prop="category.name" sortable="custom" label="Category" min-width="140">
            <template #default="{ row }">
              {{ row.category?.name || '-' }}
            </template>
          </el-table-column>
          <el-table-column prop="area" sortable="custom" label="Area" min-width="140" />
          <el-table-column label="Image" min-width="160">
            <template #default="{ row }">
              <span class="text-sm text-[var(--slate-600)]">
                {{ row.image?.name || 'No image' }}
              </span>
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
        <span>Showing {{ showingFrom }}–{{ showingTo }} of {{ products.length }}</span>
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 25, 50, 100]"
          :total="products.length"
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
      style="max-width: 720px;"
      align-center
      destroy-on-close
      @closed="resetForm"
    >
      <div class="max-h-[70vh] overflow-y-auto pr-2">
        <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
          <div class="grid gap-4 md:grid-cols-2">
            <el-form-item label="Title" prop="title">
              <el-input v-model="form.title" placeholder="Product title" />
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
            <el-form-item label="Area">
              <el-input v-model="form.area" placeholder="Area" />
            </el-form-item>
            <el-form-item label="Feature">
              <el-input v-model="form.feature" placeholder="Feature" />
            </el-form-item>
            <el-form-item label="Number" prop="position">
              <el-input-number v-model="form.position" :min="1" :step="1" class="!w-full" placeholder="Ex: 1" />
            </el-form-item>
          </div>

          <el-form-item label="Description">
            <el-input v-model="form.description" type="textarea" :rows="3" />
          </el-form-item>

          <el-form-item label="Product Image" prop="image">
            <el-upload
              action="#"
              list-type="picture-card"
              :auto-upload="false"
              :limit="1"
              accept="image/*"
              v-model:file-list="imageFiles"
              :on-change="handleImageChange"
              :on-remove="handleImageRemove"
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
import type { Category, Product } from '~/types/admin'
import { useApi } from '~/composables/useApi'

type ProductForm = {
  id: string
  title: string
  categoryId: string | null
  area: string
  feature: string
  description: string
  position: number | null
  image: UploadUserFile | null
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

type ProductApi = {
  id: string
  category_id: string | null
  title: string
  description: string | null
  feature: string | null
  area: string | null
  position: number | null
  category: CategoryApi | null
  thumbnail: AttachmentApi | null
}

const createDefaultForm = (): ProductForm => ({
  id: '',
  title: '',
  categoryId: null,
  area: '',
  feature: '',
  description: '',
  position: null,
  image: null,
})

const products = ref<Product[]>([])
const categories = ref<Category[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const searchTimeout = ref<ReturnType<typeof setTimeout> | null>(null)

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return products.value.slice(start, start + pageSize.value)
})

const showingFrom = computed(() => products.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1)
const showingTo = computed(() => Math.min(currentPage.value * pageSize.value, products.value.length))

const sortProp = ref<string>('position')
const sortOrder = ref<string>('ascending')

const handleSort = ({ prop, order }: { prop: string | null, order: string | null }) => {
  sortProp.value = prop || 'position'
  sortOrder.value = order || 'ascending'
  loadProducts()
}

const isDialogOpen = ref(false)
const isEditing = ref(false)
const editingId = ref<string | null>(null)
const formRef = ref<FormInstance>()
const form = reactive<ProductForm>(createDefaultForm())
const imageFiles = ref<UploadUserFile[]>([])

const dialogTitle = computed(() => (isEditing.value ? 'Edit Product' : 'Add Product'))

const rules: FormRules<ProductForm> = {
  title: [{ required: true, message: 'Title is required', trigger: 'blur' }],
  image: [
    {
      required: true,
      validator: (_rule, value, callback) => {
        if (!value) callback(new Error('Thumbnail (Image) harus diisi!'))
        else callback()
      },
      trigger: ['change', 'blur'],
    },
  ],
}

const resetForm = () => {
  Object.assign(form, createDefaultForm())
  imageFiles.value = []
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

const openEdit = (product: Product) => {
  isEditing.value = true
  editingId.value = product.id
  Object.assign(form, {
    id: product.id,
    title: product.title,
    categoryId: product.categoryId ?? null,
    area: product.area,
    feature: product.feature,
    description: product.description,
    position: product.position || null,
    image: product.image ?? null,
  })
  imageFiles.value = product.image ? [product.image] : []
  isDialogOpen.value = true
  nextTick(() => formRef.value?.clearValidate())
}

const handleImageChange = async (file: UploadFile) => {
  if (!file.raw) return
  try {
    const attachment = await uploadAttachment(file.raw, {
      attachmentableType: 'App\\Models\\Product',
      type: 'thumbnail',
    })
    const uploaded: any = {
      uid: attachment.id as any,
      attachmentId: attachment.id,
      name: attachment.name,
      url: attachment.path,
    }
    imageFiles.value = [uploaded]
    form.image = uploaded
    ElMessage.success('Image uploaded')
  } catch (error) {
    imageFiles.value = []
    form.image = null
    ElMessage.error(getErrorMessage(error, 'Failed to upload image.'))
  }
}

const handleImageRemove = () => {
  imageFiles.value = []
  form.image = null
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

const mapProduct = (product: ProductApi): Product => ({
  id: product.id,
  title: product.title ?? '',
  categoryId: product.category_id ?? null,
  category: product.category
    ? {
        id: product.category.id,
        name: product.category.name,
        description: product.category.description ?? null,
      }
    : null,
  area: product.area ?? '',
  feature: product.feature ?? '',
  description: product.description ?? '',
  position: product.position !== null ? Number(product.position) : null,
  image: mapAttachment(product.thumbnail),
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
  area: form.area || null,
  feature: form.feature || null,
  description: form.description || null,
  position: form.position !== null ? Number(form.position) : null,
  thumbnail_id: resolveAttachmentId(form.image),
})

const loadProducts = async () => {
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
    const response = await apiFetch<ProductApi[] | { data: ProductApi[] }>(`/products?${query.toString()}`)
    products.value = unwrap(response).map(mapProduct)
    currentPage.value = 1
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to load products.'))
  } finally {
    loading.value = false
  }
}

watch(searchQuery, () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    loadProducts()
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
        if (form.image && !resolveAttachmentId(form.image)) {
          ElMessage.warning('Image upload belum terhubung, simpan tanpa thumbnail.')
        }

        if (isEditing.value && editingId.value) {
          const updated = await apiFetch<ProductApi>(`/products/${editingId.value}`, {
            method: 'PUT',
            body: payload,
          })
          const next = mapProduct(updated)
          const index = products.value.findIndex((item) => item.id === updated.id)
          if (index !== -1) {
            products.value[index] = next
          } else {
            products.value.unshift(next)
          }
          ElMessage.success('Product updated')
        } else {
          const created = await apiFetch<ProductApi>('/products', {
            method: 'POST',
            body: payload,
          })
          products.value.unshift(mapProduct(created))
          ElMessage.success('Product created')
        }
        closeDialog()
      } catch (error) {
        ElMessage.error(getErrorMessage(error, 'Failed to save product.'))
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

const updatePosition = async (row: Product) => {
  try {
    const thumbUid = row.image?.uid ? String(row.image.uid) : ''
    await apiFetch(`/products/${row.id}`, {
      method: 'PUT',
      body: {
        title: row.title,
        category_id: row.categoryId,
        area: row.area || null,
        feature: row.feature || null,
        description: row.description || null,
        position: row.position !== null ? Number(row.position) : null,
        thumbnail_id: isUuid(thumbUid) ? thumbUid : null,
      },
    })
    ElMessage.success('Position updated')
    loadProducts()
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to update position.'))
    loadProducts()
  }
}

const confirmDelete = async (product: Product) => {
  try {
    await ElMessageBox.confirm(
      `Delete ${product.title}?`,
      'Delete Product',
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
    await apiFetch(`/products/${product.id}`, { method: 'DELETE' })
    products.value = products.value.filter((item) => item.id !== product.id)
    ElMessage.success('Product deleted')
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to delete product.'))
  }
}

onMounted(() => {
  loadProducts()
  loadCategories()
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
