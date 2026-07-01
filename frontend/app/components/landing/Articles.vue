<template>
  <section class="py-24 bg-white">
    <div class="container mx-auto px-6 lg:px-20">
      <div class="flex justify-between items-end mb-12">
        <h2 class="text-3xl md:text-5xl font-extrabold text-[#1e293b] tracking-tight">Articles & Insights</h2>
        <NuxtLink to="/articles" class="text-blue font-bold text-sm hover:underline">See All →</NuxtLink>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <article v-for="item in articles" :key="item.id" class="group flex flex-col">
          <div class="aspect-[16/10] overflow-hidden rounded-3xl mb-6 shadow-sm border border-gray-100">
            <img :src="getThumbnailUrl(item)" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
          </div>
          <h3 class="text-xl font-bold text-[#1e293b] mb-3 line-clamp-2 group-hover:text-slate-300 transition">{{ item.title }}</h3>
          <p class="text-gray-500 text-sm mb-6 line-clamp-2">{{ stripHtml(item.content) }}</p>
          <NuxtLink :to="`/articles/${item.slug}`" class="text-[#1e293b] font-bold text-xs uppercase tracking-widest mt-auto border-b-2 border-primary/20 w-fit pb-1 hover:border-primary transition-all">
            Read More
          </NuxtLink>
        </article>
      </div>
    </div>
  </section>
</template>


<script setup>
const baseUrlLaravel = 'http://localhost:8000'
const { data: response, status } = await useFetch(`${baseUrlLaravel}/api/public/articles`, { query: { limit: 3 } })
const articles = computed(() => response.value?.data || response.value || [])
const stripHtml = (html) => html?.replace(/<[^>]*>?/gm, '') || ''
const getThumbnailUrl = (item) => {
  const rawPath = item.thumbnail?.path || ''
  if (!rawPath) return '/images/placeholder.png'
  const originalPath = rawPath.startsWith('public/') ? rawPath.replace('public/', 'storage/') : rawPath
  return originalPath.startsWith('http') ? originalPath : `${baseUrlLaravel}${originalPath.startsWith('/') ? originalPath : '/' + originalPath}`
}
</script>