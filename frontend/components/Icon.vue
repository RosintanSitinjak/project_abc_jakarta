<template>
  <!-- <Icon :icon="props.icon" :class="`text-lg ${props.class}`"/> -->
   <span v-if="icon" class="inline-block">
    <img 
      :src="`https://api.iconify.design/${icon.replace(':', '/')}.svg`" 
      :class="props.class"
      :style="{ filter: isWhite ? 'brightness(0) invert(1)' : '' }"
      style="display: inline-block; vertical-align: middle;"
    />
  </span>
</template>

<!-- <script lang="ts" setup>
import { Icon } from "@iconify/vue";

const props = defineProps<{
  icon: string;
  class?: string;
}>();
</script> -->

<script setup>
const props = defineProps({
  icon: String,
  class: String
})

const isWhite = computed(() => props.class?.includes('text-white'))
</script>