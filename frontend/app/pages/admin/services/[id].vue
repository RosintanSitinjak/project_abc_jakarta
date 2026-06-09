<template>
  <div />
</template>

<script lang="ts" setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

onMounted(() => {
  const serviceId = String(route.params.id)
  router.replace({ path: '/admin/services', query: { edit: serviceId } })
})
</script>
