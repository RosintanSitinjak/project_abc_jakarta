<template>
  <ArticleForm
    @save="onSave"
  />
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import ArticleForm from '~/components/Admin/ArticleForm.vue'
import { useApi } from '~/composables/useApi'
import type { ArticleApi } from '~/types/admin'

const router = useRouter()
const { apiFetch, getErrorMessage } = useApi()

const onSave = async (payload: any) => {
  try {
    await apiFetch<ArticleApi>('/articles', {
      method: 'POST',
      body: payload,
    })
    ElMessage.success('Article created successfully.')
    router.push('/admin/articles')
  } catch (error) {
    ElMessage.error(getErrorMessage(error, 'Failed to create article.'))
  }
}
</script>
