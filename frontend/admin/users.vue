<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="section-title">User Management</h2>
          <p class="text-sm text-[var(--slate-500)]">Manage users and their roles.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Search users..." clearable class="w-full sm:w-64">
            <template #prefix>
              <Icon icon="solar:magnifer-linear" />
            </template>
          </el-input>
          <el-button type="primary" @click="openCreate">Add User</el-button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
        <el-table :data="paginatedUsers" row-key="id" stripe v-loading="loading" class="min-w-[600px] w-full" @sort-change="handleSort" :default-sort="{ prop: 'name', order: 'ascending' }">
          <el-table-column prop="name" sortable="custom" label="Name" min-width="200" show-overflow-tooltip />
          <el-table-column prop="email" sortable="custom" label="Email" min-width="240" show-overflow-tooltip />
          <el-table-column prop="role" sortable="custom" label="Role" min-width="140">
            <template #default="{ row }">
              <el-tag :type="roleTagType(row.role)" size="small" effect="plain">
                {{ roleLabelFor(row.role) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Actions" width="150" align="center">
            <template #default="{ row }">
              <el-button text type="primary" aria-label="Edit" @click="openEdit(row)">
                <Icon icon="solar:pen-2-outline" class="text-lg" />
              </el-button>
              <el-button
                v-if="row.id !== authUser?.id"
                text
                type="danger"
                aria-label="Delete"
                @click="confirmDelete(row)"
              >
                <Icon icon="solar:trash-bin-trash-outline" class="text-lg" />
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>

      <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-sm text-[var(--slate-500)]">
        <span>Showing {{ showingFrom }}–{{ showingTo }} of {{ users.length }}</span>
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 25, 50, 100]"
          :total="users.length"
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
            <el-form-item label="Name" prop="name">
              <el-input v-model="form.name" placeholder="John Doe" />
            </el-form-item>
            <el-form-item label="Email" prop="email">
              <el-input v-model="form.email" placeholder="john@lamsolusi.com" />
            </el-form-item>
          </div>

          <el-form-item label="Role" prop="role">
            <el-select v-model="form.role" placeholder="Select role" class="w-full">
              <el-option
                v-for="r in availableRoles"
                :key="r.value"
                :label="r.label"
                :value="r.value"
              />
            </el-select>
          </el-form-item>

          <div class="grid gap-4 md:grid-cols-2">
            <el-form-item label="Password" prop="password">
              <el-input
                v-model="form.password"
                type="password"
                show-password
                :placeholder="isEditing ? 'Leave blank to keep current' : 'Min 8 characters'"
              />
            </el-form-item>
            <el-form-item label="Confirm Password" prop="password_confirmation">
              <el-input
                v-model="form.password_confirmation"
                type="password"
                show-password
                placeholder="Confirm password"
              />
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
import type { FormInstance, FormRules } from 'element-plus'
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue'
import AdminShell from '~/components/Admin/Shell.vue'
import { useApi } from '~/composables/useApi'
import { Role, roleLabels } from '~/types/enums'

type UserForm = {
  name: string
  email: string
  role: number | null
  password: string
  password_confirmation: string
}

type UserApi = {
  id: string
  name: string
  email: string
  role: number
}

const createDefaultForm = (): UserForm => ({
  name: '',
  email: '',
  role: null,
  password: '',
  password_confirmation: '',
})

const { apiFetch, unwrap, getErrorMessage } = useApi()

const authUser = useState<{ id?: string; name?: string; email?: string; role?: number } | null>('auth-user', () => null)

const users = ref<UserApi[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const searchTimeout = ref<ReturnType<typeof setTimeout> | null>(null)

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return users.value.slice(start, start + pageSize.value)
})

const showingFrom = computed(() => users.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1)
const showingTo = computed(() => Math.min(currentPage.value * pageSize.value, users.value.length))

const sortProp = ref<string>('name')
const sortOrder = ref<string>('ascending')

const handleSort = ({ prop, order }: { prop: string | null, order: string | null }) => {
  sortProp.value = prop || 'name'
  sortOrder.value = order || 'ascending'
  loadUsers()
}

const isDialogOpen = ref(false)
const isEditing = ref(false)
const editingId = ref<string | null>(null)
const formRef = ref<FormInstance>()
const form = reactive<UserForm>(createDefaultForm())

const dialogTitle = computed(() => (isEditing.value ? 'Edit User' : 'Add User'))

const availableRoles = computed(() => {
  const myRole = authUser.value?.role
  const allRoles = [
    { value: Role.Owner, label: roleLabels[Role.Owner] },
    { value: Role.Admin, label: roleLabels[Role.Admin] },
    { value: Role.Writer, label: roleLabels[Role.Writer] },
  ]
  if (myRole === Role.Owner) return allRoles
  // Admin cannot assign Owner
  return allRoles.filter((r) => r.value !== Role.Owner)
})

const roleLabelFor = (role: number) => {
  return roleLabels[role as Role] || 'Unknown'
}

const roleTagType = (role: number) => {
  if (role === Role.Owner) return 'danger'
  if (role === Role.Admin) return 'warning'
  return 'info'
}

const rules: FormRules<UserForm> = {
  name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
  email: [
    { required: true, message: 'Email is required', trigger: 'blur' },
    { type: 'email', message: 'Invalid email format', trigger: 'blur' },
  ],
  role: [{ required: true, message: 'Role is required', trigger: 'change' }],
  password: [
    {
      validator: (_rule, value, callback) => {
        if (!isEditing.value && !value) {
          callback(new Error('Password is required'))
          return
        }
        if (value && value.length < 8) {
          callback(new Error('Password must be at least 8 characters'))
          return
        }
        callback()
      },
      trigger: 'blur',
    },
  ],
  password_confirmation: [
    {
      validator: (_rule, value, callback) => {
        if (form.password && value !== form.password) {
          callback(new Error('Password confirmation does not match'))
          return
        }
        callback()
      },
      trigger: 'blur',
    },
  ],
}

const resetForm = () => {
  Object.assign(form, createDefaultForm())
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

const openEdit = (user: UserApi) => {
  isEditing.value = true
  editingId.value = user.id
  Object.assign(form, {
    name: user.name,
    email: user.email,
    role: user.role,
    password: '',
    password_confirmation: '',
  })
  isDialogOpen.value = true
  nextTick(() => formRef.value?.clearValidate())
}

const loadUsers = async () => {
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
    const response = await apiFetch<UserApi[] | { data: UserApi[] }>(`/user-management?${query.toString()}`)
    users.value = unwrap(response)
    currentPage.value = 1
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to load users.'))
  } finally {
    loading.value = false
  }
}

watch(searchQuery, () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    loadUsers()
  }, 300)
})

const submitForm = async () => {
  const formEl = formRef.value
  if (!formEl) return

  await formEl.validate(async (valid) => {
    if (valid) {
      try {
        const payload: Record<string, unknown> = {
          name: form.name,
          email: form.email,
          role: form.role,
        }
        if (form.password) {
          payload.password = form.password
          payload.password_confirmation = form.password_confirmation
        }

        if (isEditing.value && editingId.value) {
          const updated = await apiFetch<UserApi>(`/user-management/${editingId.value}`, {
            method: 'PUT',
            body: payload,
          })
          const index = users.value.findIndex((item) => item.id === updated.id)
          if (index !== -1) {
            users.value[index] = updated
          } else {
            users.value.unshift(updated)
          }
          ElMessage.success('User updated')
        } else {
          const created = await apiFetch<UserApi>('/user-management', {
            method: 'POST',
            body: payload,
          })
          users.value.unshift(created)
          ElMessage.success('User created')
        }
        closeDialog()
      } catch (error) {
        ElMessage.error(getErrorMessage(error, 'Failed to save user.'))
      }
    } else {
      ElNotification({
        title: 'Error',
        message: 'Harap lengkapi form dengan benar!',
        type: 'error',
      })
    }
  })
}

const confirmDelete = async (user: UserApi) => {
  try {
    await ElMessageBox.confirm(
      `Delete ${user.name}?`,
      'Delete User',
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
    await apiFetch(`/user-management/${user.id}`, { method: 'DELETE' })
    users.value = users.value.filter((item) => item.id !== user.id)
    ElMessage.success('User deleted')
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to delete user.'))
  }
}

onMounted(() => {
  loadUsers()
})
</script>
