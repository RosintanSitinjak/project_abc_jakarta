<template>
  <div>
    <ArticleForm
      v-if="!loading && initialData"
      :is-edit="true"
      :initial-data="initialData"
      @save="onSave"
    />
    <AdminShell v-else>
      <div class="glass-panel p-6 mb-6 flex items-center justify-center min-h-[400px]">
        <el-icon class="is-loading text-4xl text-gray-400"><Loading /></el-icon>
      </div>
    </AdminShell>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Loading } from '@element-plus/icons-vue'
import AdminShell from '~/components/Admin/Shell.vue'
import ArticleForm from '~/components/Admin/ArticleForm.vue'
import { useApi } from '~/composables/useApi'
import type { ArticleApi, Article } from '~/types/admin'

const route = useRoute()
const router = useRouter()
const { apiFetch, getErrorMessage } = useApi()

const loading = ref(true)
const initialData = ref<Article | null>(null)

const mapAttachment = (attachment: any) => {
  if (!attachment) return null
  return {
    uid: attachment.id as any,
    attachmentId: attachment.id,
    name: attachment.name,
    url: attachment.path,
  } as any
}

const loadArticle = async () => {
  const id = route.params.id as string
  try {
    const response = await apiFetch<ArticleApi>(`/articles/${id}`)
    initialData.value = {
      id: response.id,
      title: response.title,
      slug: response.slug,
      author: response.author ? { id: response.author.id, name: response.author.name } as any : null,
      description: response.description,
      thumbnail: mapAttachment(response.thumbnail),
    }
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to fully load article.'))
    router.push('/admin/articles')
  } finally {
    loading.value = false
  }
}

const onSave = async (payload: any) => {
  const id = route.params.id as string
  try {
    await apiFetch<ArticleApi>(`/articles/${id}`, {
      method: 'PUT',
      body: payload,
    })
    ElMessage.success('Article updated successfully.')
    router.push('/admin/articles')
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to update article.'))
  }
}

onMounted(() => {
  loadArticle()
})
</script>
