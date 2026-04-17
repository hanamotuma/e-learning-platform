<script setup lang="ts">
import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <AuthSimpleLayout :title="title" :description="description">
        <div class="w-full">
            <slot />
        </div>
    </AuthSimpleLayout>
</template>