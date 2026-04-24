<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  categories: Array
})

const previewImage = ref<string | null>(null)

const form = useForm({
  title: '',
  description: '',
  what_you_will_learn: '',
  requirements: '',
  price: '',
  category_id: '',
  difficulty_level: 'beginner',
  duration_weeks: '',
  video: null as File | null,
  image: null as File | null,
  is_published: false,
})

// handle image preview
const handleImage = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) {
    form.image = file
    previewImage.value = URL.createObjectURL(file)
  }
}

const submit = () => {
  form.post(route('admin.courses.store'))
}
</script>

<template>
  <div class="min-h-screen bg-[#0f0a24] text-white p-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Create Course</h1>

      <Link
        :href="route('admin.courses.index')"
        class="text-sm text-gray-400 hover:underline"
      >
        Back
      </Link>
    </div>

    <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- LEFT SIDE -->
      <div class="space-y-4">

        <input v-model="form.title"
          placeholder="Course Title"
          class="w-full p-3 bg-[#16103a] border border-white/10 rounded-lg"
        />

        <textarea v-model="form.description"
          placeholder="Description"
          class="w-full p-3 bg-[#16103a] border border-white/10 rounded-lg"
        />

        <textarea v-model="form.what_you_will_learn"
          placeholder="What you will learn"
          class="w-full p-3 bg-[#16103a] border border-white/10 rounded-lg"
        />

        <textarea v-model="form.requirements"
          placeholder="Requirements"
          class="w-full p-3 bg-[#16103a] border border-white/10 rounded-lg"
        />

        <!-- VIDEO UPLOAD -->
<div>
  <label class="text-sm text-gray-400">Upload Video</label>

  <input
    type="file"
    accept="video/*"
    @change="handleVideo"
    class="w-full p-3 bg-[#16103a] border border-white/10 rounded-lg"
  />

  <video
    v-if="videoPreview"
    :src="videoPreview"
    controls
    class="mt-3 w-full rounded-lg border border-white/10"
  />
</div>
      </div>

      <!-- RIGHT SIDE -->
      <div class="space-y-4">

        <!-- CATEGORY -->
        <select v-model="form.category_id"
          class="w-full p-3 bg-[#16103a] border border-white/10 rounded-lg"
        >
          <option value="">Select Category</option>
          <option
            v-for="cat in categories"
            :key="cat.id"
            :value="cat.id"
          >
            {{ cat.name }}
          </option>
        </select>

        <!-- PRICE -->
        <input v-model="form.price"
          type="number"
          placeholder="Price"
          class="w-full p-3 bg-[#16103a] border border-white/10 rounded-lg"
        />

        <!-- DIFFICULTY -->
        <select v-model="form.difficulty_level"
          class="w-full p-3 bg-[#16103a] border border-white/10 rounded-lg"
        >
          <option>beginner</option>
          <option>intermediate</option>
          <option>advanced</option>
        </select>

        <!-- DURATION -->
        <input v-model="form.duration_weeks"
          type="number"
          placeholder="Duration (weeks)"
          class="w-full p-3 bg-[#16103a] border border-white/10 rounded-lg"
        />

        <!-- IMAGE UPLOAD -->
        <div>
          <input type="file" @change="handleImage"
            class="w-full text-sm"
          />

          <div v-if="previewImage" class="mt-3">
            <img :src="previewImage"
              class="w-full h-40 object-cover rounded-lg border border-white/10"
            />
          </div>
        </div>

        <!-- PUBLISH -->
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="form.is_published" />
          <span>Publish Course</span>
        </label>

        <!-- BUTTON -->
        <button
          type="submit"
          class="w-full bg-purple-600 py-3 rounded-lg font-semibold hover:bg-purple-700"
          :disabled="form.processing"
        >
          Create Course
        </button>

      </div>

    </form>
  </div>
</template>