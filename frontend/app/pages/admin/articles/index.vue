<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <h2 class="section-title">Articles</h2>
          <p class="text-sm text-[var(--slate-500)]">Manage articles and insightful content.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
          <el-input v-model="searchQuery" placeholder="Search articles..." clearable class="w-full sm:w-64">
            <template #prefix>
              <Icon icon="solar:magnifer-linear" />
            </template>
          </el-input>
          <el-button type="primary" @click="openCreate">Add Article</el-button>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto -mx-4 sm:-mx-6 px-4 sm:px-6">
        <el-table :data="paginatedArticles" row-key="id" stripe v-loading="loading" class="min-w-[700px] w-full" @sort-change="handleSort" :default-sort="{ prop: 'title', order: 'ascending' }">
         <el-table-column prop="title" sortable="custom" label="Title" min-width="200" show-overflow-tooltip />
<el-table-column prop="author.name" sortable="custom" label="Author" min-width="150" show-overflow-tooltip>
  <template #default="{ row }">
    {{ row.author?.name || 'Unknown' }}
  </template>
</el-table-column>

<el-table-column label="Thumbnail" min-width="180">
  <template #default="scope">
    <div class="flex items-center gap-3">
      <el-image
        v-if="scope.row.thumbnail?.url"
        :src="scope.row.thumbnail.url"
        fit="cover"
        class="h-10 w-16 rounded-lg border border-slate-100 bg-white shadow-sm"
      >
        <template #error>
          <div class="flex h-full w-full items-center justify-center bg-slate-100 text-xs text-slate-400">
            Error Url
          </div>
        </template>
      </el-image>
      <span v-else class="text-sm text-slate-400">No Thumbnail</span>
    </div>
  </template>
</el-table-column>
          

          <el-table-column label="Actions" width="160" align="center">
  <template #default="scope">
    <div class="flex items-center justify-center gap-2">
      
      <el-button 
        type="primary" 
        size="small"
        plain
        aria-label="Edit" 
        @click="openEdit(scope.row)"
      >
        <el-icon class="mr-1"><Edit /></el-icon> Edit
      </el-button>
      
      <el-button 
        type="danger" 
        size="small"
        aria-label="Delete" 
        @click="confirmDelete(scope.row)"
      >
        <el-icon class="mr-1"><Delete /></el-icon> Hapus
      </el-button>

    </div>
  </template>
</el-table-column>

        </el-table>
      </div>

      <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-sm text-[var(--slate-500)]">
        <span>Showing {{ showingFrom }}–{{ showingTo }} of {{ articles.length }}</span>
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[10, 25, 50, 100]"
          :total="articles.length"
          layout="sizes, prev, pager, next"
          size="small"
          background
        />
      </div>
    </section>
  </AdminShell>
</template>

<script lang="ts" setup>
import { ElMessage, ElMessageBox } from 'element-plus'
import { Edit, Delete } from '@element-plus/icons-vue' 

import type { UploadUserFile } from 'element-plus'
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AdminShell from '~/components/Admin/Shell.vue'
import type { Article, ArticleApi, AttachmentApi } from '~/types/admin'
import { useApi } from '~/composables/useApi'

const router = useRouter()
const { apiFetch, unwrap, getErrorMessage } = useApi()

const articles = ref<Article[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const searchQuery = ref('')
const searchTimeout = ref<ReturnType<typeof setTimeout> | null>(null)

const paginatedArticles = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return articles.value.slice(start, start + pageSize.value)
})

const showingFrom = computed(() => articles.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize.value + 1)
const showingTo = computed(() => Math.min(currentPage.value * pageSize.value, articles.value.length))

const sortProp = ref<string>('title')
const sortOrder = ref<string>('ascending')

const handleSort = ({ prop, order }: { prop: string | null, order: string | null }) => {
  sortProp.value = prop || 'title'
  sortOrder.value = order || 'ascending'
  loadArticles()
}

// const mapAttachment = (attachment: AttachmentApi | null): UploadUserFile | null => {
//   if (!attachment) return null
//   return {
//     uid: attachment.id as any,
//     name: attachment.name,
//     url: attachment.path,
//   }
// }

const mapAttachment = (attachment: AttachmentApi | null): UploadUserFile | null => {
  if (!attachment) return null
  
  const originalPath = attachment.path || ''

  // Jika path sudah lengkap (mengandung http), langsung pakai.
  // Jika berupa path relatif (misal: /storage/attachments/abc.jpg atau attachments/abc.jpg), 
  // kita sambungkan dengan domain utama Laravel Backend kamu.
  let fullUrl = originalPath
  if (!originalPath.startsWith('http')) {
    const basePath = originalPath.startsWith('/') ? originalPath : `/${originalPath}`
    
    // Seringkali Laravel menyimpan ke public storage, pastikan url mengarah ke port Laravel-mu (misal: 8000)
    fullUrl = `http://127.0.0.1:8000${basePath}` 
  }

  return {
    uid: attachment.id as any,
    name: attachment.name,
    url: fullUrl,
  }
}

const mapArticle = (article: ArticleApi): Article => ({
  id: article.id,
  title: article.title,
  slug: article.slug,
  author: article.author ? { id: article.author.id, name: article.author.name } as any : null,
  description: article.description,
  thumbnail: mapAttachment(article.thumbnail),
})

const openCreate = () => {
  router.push('/admin/articles/create')
}

const openEdit = (article: Article) => {
  router.push(`/admin/articles/${article.id}`)
}

const loadArticles = async () => {
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
    // We send request to index, since it has pagination
    const response = await apiFetch<ArticleApi[] | { data: ArticleApi[] }>(`/articles?${query.toString()}`)
    articles.value = unwrap(response).map(mapArticle)
    currentPage.value = 1
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to load articles.'))
  } finally {
    loading.value = false
  }
}

watch(searchQuery, () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    loadArticles()
  }, 300)
})



const confirmDelete = async (article: Article) => {
  try {
    await ElMessageBox.confirm(
      `Delete "${article.title}"?`,
      'Delete Article',
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
    await apiFetch(`/articles/${article.id}`, { method: 'DELETE' })
    articles.value = articles.value.filter((item) => item.id !== article.id)
    ElMessage.success('Article deleted')
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to delete article.'))
  }
}

onMounted(() => {
  loadArticles()
})
</script>
