<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

defineProps({
  links: Array
})
</script>

<template>
  <div class="flex justify-center mt-6 space-x-1">

    <template v-for="(link, i) in links" :key="i">

      <Link
        v-if="link.url"
        :href="link.url"
        class="px-3 py-1 rounded-lg border text-sm"
        :class="link.active
          ? 'bg-purple-600 border-purple-500 text-white'
          : 'bg-[#16103a] border-white/10 text-gray-300 hover:bg-white/10'"
        v-html="link.label"
      />

      <span
        v-else
        class="px-3 py-1 text-gray-500 text-sm"
        v-html="link.label"
      />

    </template>

  </div>
</template>