<script setup>
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  course: Object,
})

const sectionForm = useForm({ title: '' })
const lessonForm = useForm({ title: '', video: null })

const addSection = () => {
  sectionForm.post(route('admin.sections.store', props.course.id), {
    onSuccess: () => sectionForm.reset()
  })
}

const addLesson = (section) => {
  lessonForm.post(route('admin.lessons.store', section.id), {
    forceFormData: true,
    onSuccess: () => lessonForm.reset()
  })
}

const deleteSection = (id) => {
  router.delete(route('admin.sections.destroy', id))
}

const deleteLesson = (id) => {
  router.delete(route('admin.lessons.destroy', id))
}
</script>

<template>
<div class="p-6 text-white">

  <h1 class="text-2xl mb-6">{{ course.title }}</h1>

  <!-- ADD SECTION -->
  <div class="flex gap-2 mb-6">
    <input v-model="sectionForm.title" placeholder="New section" class="bg-gray-800 p-2"/>
    <button @click="addSection" class="bg-purple-600 px-3">Add</button>
  </div>

  <!-- SECTIONS -->
  <div v-for="section in course.sections" :key="section.id" class="mb-6">

    <div class="flex justify-between">
      <h2 class="text-purple-400">{{ section.title }}</h2>
      <button @click="deleteSection(section.id)" class="text-red-400">Delete</button>
    </div>

    <!-- ADD LESSON -->
<div class="flex gap-2 mt-2">
  <input v-model="lessonForm.title" placeholder="Lesson title" class="bg-gray-800 p-2"/>
  <input type="file" @change="e => lessonForm.video = e.target.files[0]" />
  <button @click="addLesson(section)" class="bg-blue-600 px-3">Add Lesson</button>
</div>

<!-- LESSONS -->
<div
  v-for="lesson in section.lessons"
  :key="lesson.id"
  class="ml-4 mt-2"
>

  <div class="flex justify-between">
    <span>▶ {{ lesson.title }}</span>
    <button @click="deleteLesson(lesson.id)" class="text-red-400">X</button>
  </div>

  <!-- VIDEO -->
  <div v-if="lesson.video_url" class="mt-2">
    <video
      :src="`/storage/${lesson.video_url}`"
      controls
      class="w-full max-w-md rounded"
    />
  </div>

</div>
  </div>

</div>
</template>