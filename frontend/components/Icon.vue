<template>
  <Icon :icon="props.icon" :class="`text-lg ${props.class}`"/>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";

const props = defineProps<{
  icon: string;
  class?: string;
}>();
</script>