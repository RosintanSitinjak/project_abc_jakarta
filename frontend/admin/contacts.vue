<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="section-title">Inquiries List</h2>
          <p class="text-sm text-[var(--slate-500)]">Manage contact inquiries from the website.</p>
        </div>
        <div class="flex items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Search inquiries..." clearable class="w-full sm:w-64">
            <template #prefix>
              <Icon icon="solar:magnifer-linear" />
            </template>
          </el-input>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
        <el-table :data="paginatedContacts" row-key="id" stripe v-loading="loading" class="min-w-[800px] w-full" :row-class-name="tableRowClassName" @sort-change="handleSort" :default-sort="{ prop: 'created_at', order: 'descending' }">
          <el-table-column label="Status" min-width="120" sortable="custom" prop="status">
            <template #default="{ row }">
              <el-tag :type="row.status === 1 ? 'danger' : 'success'" effect="plain" class="font-semibold">
                {{ row.status === 1 ? 'Open' : 'Closed' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="name" sortable="custom" label="Name" min-width="160" show-overflow-tooltip />
          <el-table-column prop="company" sortable="custom" label="Company" min-width="140" show-overflow-tooltip />
          <el-table-column prop="subject" sortable="custom" label="Subject" min-width="200" show-overflow-tooltip />
          <el-table-column prop="created_at" sortable="custom" label="Date" min-width="150" show-overflow-tooltip>
            <template #default="{ row }">
              {{ new Date(row.created_at).toLocaleDateString() }}
            </template>
          </el-table-column>
          <el-table-column label="Actions" width="150" align="center">
            <template #default="{ row }">
              <el-button text type="primary" aria-label="View" @click="openView(row)">
                <Icon icon="solar:eye-outline" class="text-lg" />
              </el-button>
              <el-button v-if="row.status === 1" text type="success" aria-label="Close Inquiry" @click="closeContact(row)">
                <Icon icon="solar:check-circle-outline" class="text-lg" />
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>

      <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-sm text-[var(--slate-500)]">
        <span>Showing {{ showingFrom }}–{{ showingTo }} of {{ contacts.length }}</span>
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 25, 50, 100]"
          :total="contacts.length"
          layout="sizes, prev, pager, next"
          size="small"
          background
        />
      </div>
    </section>

    <!-- Detailed View Dialog -->
    <el-dialog
      v-model="isViewOpen"
      title="Contact Detail"
      width="90%"
      style="max-width: 640px;"
      align-center
    >
      <div v-if="selectedContact" class="space-y-4 max-h-[70vh] overflow-y-auto pr-2 text-[var(--slate-700)]">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-xs uppercase text-[var(--slate-500)]">Name</p>
            <p class="font-medium">{{ selectedContact.name }}</p>
          </div>
          <div>
            <p class="text-xs uppercase text-[var(--slate-500)]">Company</p>
            <p class="font-medium">{{ selectedContact.company || '-' }}</p>
          </div>
          <div>
            <p class="text-xs uppercase text-[var(--slate-500)]">Email</p>
            <p class="font-medium">{{ selectedContact.email }}</p>
          </div>
          <div>
            <p class="text-xs uppercase text-[var(--slate-500)]">Phone</p>
            <p class="font-medium">{{ selectedContact.phone || '-' }}</p>
          </div>
          <div class="col-span-2">
            <p class="text-xs uppercase text-[var(--slate-500)]">Subject</p>
            <p class="font-medium">{{ selectedContact.subject }}</p>
          </div>
          <div class="col-span-2">
            <p class="text-xs uppercase text-[var(--slate-500)]">Message</p>
            <div class="mt-1 whitespace-pre-wrap rounded-lg bg-[var(--slate-50)] p-4 text-sm">{{ selectedContact.message }}</div>
          </div>
        </div>
      </div>
      <template #footer>
        <el-button @click="isViewOpen = false">Close</el-button>
        <el-button v-if="selectedContact?.status === 1" type="success" @click="closeContact(selectedContact!)">
          Mark as Closed
        </el-button>
      </template>
    </el-dialog>
  </AdminShell>
</template>

<script lang="ts" setup>
import { ElMessage, ElMessageBox } from 'element-plus'
import { computed, onMounted, ref, watch } from 'vue'
import AdminShell from '~/components/Admin/Shell.vue'
import { useApi } from '~/composables/useApi'
import { Icon } from '@iconify/vue'

type ContactApi = {
  id: string
  name: string
  company: string | null
  email: string
  phone: string | null
  subject: string
  message: string
  status: number
  created_at: string
}

const { apiFetch, unwrap, getErrorMessage } = useApi()

const contacts = ref<ContactApi[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const searchTimeout = ref<ReturnType<typeof setTimeout> | null>(null)

const isViewOpen = ref(false)
const selectedContact = ref<ContactApi | null>(null)

const tableRowClassName = ({ row }: { row: ContactApi, rowIndex: number }) => {
  if (row.status === 1) {
    return 'open-inquiry-row'
  }
  return ''
}

const paginatedContacts = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return contacts.value.slice(start, start + pageSize.value)
})

const showingFrom = computed(() => contacts.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1)
const showingTo = computed(() => Math.min(currentPage.value * pageSize.value, contacts.value.length))

const sortProp = ref<string>('created_at')
const sortOrder = ref<string>('descending')

const handleSort = ({ prop, order }: { prop: string | null, order: string | null }) => {
  sortProp.value = prop || 'created_at'
  sortOrder.value = order || 'descending'
  loadContacts()
}

const loadContacts = async () => {
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
    const response = await apiFetch<ContactApi[] | { data: ContactApi[] }>(`/contacts?${query.toString()}`)
    contacts.value = unwrap(response)
    currentPage.value = 1
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to load contacts.'))
  } finally {
    loading.value = false
  }
}

watch(searchQuery, () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    loadContacts()
  }, 300)
})

const openView = (contact: ContactApi) => {
  selectedContact.value = contact
  isViewOpen.value = true
}

const closeContact = async (contact: ContactApi) => {
  try {
    await ElMessageBox.confirm(
      `Are you sure you want to mark this inquiry from ${contact.name} as closed?`,
      'Close Inquiry',
      {
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel',
        type: 'warning',
      },
    )
  } catch {
    return
  }

  try {
    const updated = await apiFetch<ContactApi>(`/contacts/${contact.id}`, {
      method: 'PUT',
      body: { status: 2 },
    })
    
    const index = contacts.value.findIndex(c => c.id === updated.id)
    if (index !== -1) {
      contacts.value[index] = updated
    }
    
    if (selectedContact.value?.id === updated.id) {
      selectedContact.value = updated
    }

    ElMessage.success('Inquiry closed')
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to update status.'))
  }
}

onMounted(() => {
  loadContacts()
})
</script>

<style scoped>
:deep(.el-table .open-inquiry-row) {
  --el-table-tr-bg-color: #fef2f2;
}
:deep(.el-table--striped .el-table__body tr.el-table__row--striped.open-inquiry-row td.el-table__cell) {
  background-color: #fef2f2;
}
</style>
