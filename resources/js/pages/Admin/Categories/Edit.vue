<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
  category: Object
})

const form = useForm({
  name: props.category.name,
  description: props.category.description,
  icon: props.category.icon,
})

const submit = () => {
  form.put(route('admin.categories.update', props.category.id))
}
</script>

<template>
  <div class="p-6 text-white max-w-xl mx-auto">
    <h1 class="text-2xl mb-4">Edit Category</h1>

    <form @submit.prevent="submit" class="space-y-4">

      <input v-model="form.name" class="w-full p-3 bg-black border" />
      <p v-if="form.errors.name">{{ form.errors.name }}</p>

      <textarea v-model="form.description" class="w-full p-3 bg-black border" />

      <input v-model="form.icon" class="w-full p-3 bg-black border" />

      <button class="bg-blue-600 px-4 py-2 rounded">Update</button>

    </form>

    <Link :href="route('admin.categories.index')">Back</Link>
  </div>
</template>