<template>
  <div class="popular-articles-card">
    <div class="popular-header">
      <h3>Artikel Populer</h3>
    </div>

    <div v-if="loadingPopular" class="popular-loading">
      <div class="popular-skeleton" v-for="i in 3" :key="i">
        <div class="skeleton-rank"></div>
        <div class="skeleton-content">
          <div class="skeleton-line w-full"></div>
          <div class="skeleton-line w-3/4"></div>
        </div>
      </div>
    </div>

    <div v-else-if="popularArticles.length === 0" class="popular-empty">
      <p>Belum ada artikel populer.</p>
    </div>

    <div v-else class="popular-list">
      <NuxtLink
        v-for="(article, index) in popularArticles"
        :key="article.id"
        :to="`/articles/${article.slug}`"
        class="popular-item"
      >
        <div class="popular-rank" :class="`rank-${index + 1}`">
          {{ index + 1 }}
        </div>
        <div class="popular-item-content">
          <div class="popular-thumb" v-if="article.thumbnail?.path">
            <img v-lazy-src="article.thumbnail.path" :alt="article.title" />
          </div>
          <div class="popular-info">
            <h4>{{ article.title }}</h4>
          </div>
        </div>
      </NuxtLink>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { storeToRefs } from 'pinia'
import { onMounted } from 'vue'
import { useArticleStore } from '~/stores/article'

const props = defineProps<{
  excludeSlug?: string
}>()

const articleStore = useArticleStore()
const { popularArticles, loadingPopular } = storeToRefs(articleStore)

onMounted(() => {
  articleStore.getPopularArticles(props.excludeSlug)
})
</script>

<style scoped>
.popular-articles-card {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  padding: 1.5rem;
  position: sticky;
  top: 7rem;
}

.popular-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1.25rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e2e8f0;
}

.popular-header h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.popular-icon {
  font-size: 1.5rem;
  color: #f59e0b;
}

/* Loading skeleton */
.popular-loading {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.popular-skeleton {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.skeleton-rank {
  width: 2rem;
  height: 2rem;
  border-radius: 0.5rem;
  background: #e2e8f0;
  animation: pulse 1.5s ease-in-out infinite;
}

.skeleton-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.skeleton-line {
  height: 0.75rem;
  border-radius: 0.25rem;
  background: #e2e8f0;
  animation: pulse 1.5s ease-in-out infinite;
}

.skeleton-line.w-full { width: 100%; }
.skeleton-line.w-3\/4 { width: 75%; }

/* List */
.popular-list {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.popular-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 0.75rem;
  transition: all 0.2s ease;
  text-decoration: none;
  color: inherit;
}

.popular-item:hover {
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transform: translateX(2px);
}

.popular-rank {
  width: 2rem;
  height: 2rem;
  min-width: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
  font-weight: 800;
  font-size: 0.85rem;
  color: white;
  background: #94a3b8;
}

.popular-rank.rank-1 {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.popular-rank.rank-2 {
  background: linear-gradient(135deg, #6b7280, #4b5563);
}

.popular-rank.rank-3 {
  background: linear-gradient(135deg, #b45309, #92400e);
}

.popular-item-content {
  flex: 1;
  display: flex;
  gap: 0.625rem;
  min-width: 0;
}

.popular-thumb {
  width: 3rem;
  height: 3rem;
  min-width: 3rem;
  border-radius: 0.5rem;
  overflow: hidden;
}

.popular-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.popular-info {
  flex: 1;
  min-width: 0;
}

.popular-info h4 {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1e293b;
  line-height: 1.3;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Empty state */
.popular-empty {
  text-align: center;
  padding: 1rem 0;
  color: #94a3b8;
  font-size: 0.875rem;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>
