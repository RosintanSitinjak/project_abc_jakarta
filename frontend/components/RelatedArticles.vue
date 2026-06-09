<template>
  <section class="related-section" v-if="displayArticles.length > 0">
    <div class="related-header">
      <h2>Artikel yang Mungkin Anda Suka</h2>
    </div>

    <div class="related-viewport" ref="viewport">
      <div
        class="related-track"
        :style="trackStyle"
        @mouseenter="pauseAutoSlide"
        @mouseleave="resumeAutoSlide"
      >
        <NuxtLink
          v-for="(article, idx) in displayArticles"
          :key="`${article.id}-${idx}`"
          :to="`/articles/${article.slug}`"
          class="related-card"
        >
          <div class="related-thumb">
            <img
              v-if="article.thumbnail?.path"
              :src="article.thumbnail.path"
              :alt="article.title"
            />
            <div v-else class="related-thumb-placeholder">
              <Icon icon="solar:gallery-outline" />
            </div>
          </div>
          <div class="related-body">
            <h3>{{ article.title }}</h3>
            <p>{{ stripHtml(article.description) }}</p>
            <div class="related-foot">
              <span class="related-read">
                Baca <Icon icon="tabler:arrow-right" />
              </span>
            </div>
          </div>
        </NuxtLink>
      </div>
    </div>

    <div class="related-dots" v-if="totalSlides > 1">
      <button
        v-for="i in totalSlides"
        :key="i"
        :class="['dot', { active: currentSlide === i - 1 }]"
        @click="goToSlide(i - 1)"
      />
    </div>
  </section>
</template>

<script lang="ts" setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useArticleStore } from '~/stores/article'
import { storeToRefs } from 'pinia'
import { stripHtml } from '~/utils/string'

const props = defineProps<{
  excludeSlug?: string
}>()

const articleStore = useArticleStore()
const { popularArticles } = storeToRefs(articleStore)

const displayArticles = computed(() => popularArticles.value)

const viewport = ref<HTMLElement | null>(null)
const currentSlide = ref(0)
let autoSlideTimer: ReturnType<typeof setInterval> | null = null
const isPaused = ref(false)

const cardsPerView = ref(3)

const totalSlides = computed(() => {
  const total = displayArticles.value.length
  if (total <= cardsPerView.value) return 1
  return total - cardsPerView.value + 1
})

const trackStyle = computed(() => {
  const pct = (currentSlide.value / displayArticles.value.length) * 100
  return {
    transform: `translateX(-${pct}%)`,
    transition: 'transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)',
  }
})

const goToSlide = (index: number) => {
  currentSlide.value = Math.max(0, Math.min(index, totalSlides.value - 1))
}

const nextSlide = () => {
  if (currentSlide.value >= totalSlides.value - 1) {
    currentSlide.value = 0
  } else {
    currentSlide.value++
  }
}

const pauseAutoSlide = () => { isPaused.value = true }
const resumeAutoSlide = () => { isPaused.value = false }

const startAutoSlide = () => {
  autoSlideTimer = setInterval(() => {
    if (!isPaused.value) nextSlide()
  }, 4000)
}

const updateCardsPerView = () => {
  if (typeof window === 'undefined') return
  if (window.innerWidth < 640) cardsPerView.value = 1
  else if (window.innerWidth < 1024) cardsPerView.value = 2
  else cardsPerView.value = 3
}

onMounted(() => {
  updateCardsPerView()
  window.addEventListener('resize', updateCardsPerView)
  // Data is already fetched by PopularArticles, just start sliding
  startAutoSlide()
})

onBeforeUnmount(() => {
  if (autoSlideTimer) clearInterval(autoSlideTimer)
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', updateCardsPerView)
  }
})
</script>

<style scoped>
.related-section {
  max-width: 72rem;
  margin: 0 auto;
  padding: 3rem 1rem 4rem;
  width: 100%;
}

.related-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 2rem;
}

.related-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.related-icon {
  font-size: 1.75rem;
  color: #3b82f6;
}

.related-viewport {
  overflow: hidden;
  border-radius: 1rem;
}

.related-track {
  display: flex;
  gap: 1.5rem;
}

.related-card {
  min-width: calc(33.333% - 1rem);
  width: calc(33.333% - 1rem);
  flex-shrink: 0;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
}

.related-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  transform: translateY(-4px);
  border-color: #cbd5e1;
}

.related-thumb {
  width: 100%;
  height: 10rem;
  overflow: hidden;
  background: #e2e8f0;
}

.related-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.related-card:hover .related-thumb img {
  transform: scale(1.05);
}

.related-thumb-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #94a3b8;
}

.related-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.related-body h3 {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.4;
  word-break: break-word;
}

.related-body p {
  font-size: 0.85rem;
  color: #64748b;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin: 0 0 1rem;
  flex: 1;
  word-break: break-word;
}

.related-foot {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  margin-top: auto;
}

.related-read {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: #3b82f6;
  transition: color 0.2s;
}

.related-card:hover .related-read {
  color: #1d4ed8;
}

.related-dots {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 1.5rem;
}

.dot {
  width: 0.5rem;
  height: 0.5rem;
  border-radius: 50%;
  border: none;
  background: #cbd5e1;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0;
}

.dot.active {
  background: #3b82f6;
  width: 1.5rem;
  border-radius: 0.25rem;
}

/* Responsive */
@media (max-width: 1024px) {
  .related-card {
    min-width: calc(50% - 0.75rem);
    width: calc(50% - 0.75rem);
  }
}

@media (max-width: 640px) {
  .related-card {
    min-width: 100%;
    width: 100%;
  }
  
  .related-section {
    padding: 2rem 1rem 3rem;
  }

  .related-header h2 {
    font-size: 1.25rem;
  }
}
</style>
