<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps({
  course: Object,
})

/* ---------------- SECTION ---------------- */
const sectionForm = useForm({ title: '' })

const addSection = () => {
  sectionForm.post(route('admin.sections.store', props.course.id), {
    onSuccess: () => sectionForm.reset()
  })
}

const deleteSection = (id) => {
  router.delete(route('admin.sections.destroy', id))
}

/* ---------------- LESSON ---------------- */
const lessonForms = reactive({})

const getLessonForm = (sectionId) => {
  if (!lessonForms[sectionId]) {
    lessonForms[sectionId] = useForm({
      title: '',
      video: null
    })
  }
  return lessonForms[sectionId]
}

const addLesson = (section) => {
  const form = getLessonForm(section.id)

  form.post(route('admin.lessons.store', section.id), {
    forceFormData: true,
    onSuccess: () => form.reset()
  })
}

const deleteLesson = (id) => {
  router.delete(route('admin.lessons.destroy', id))
}

/* ---------------- RESOURCE ---------------- */
const resourceForms = reactive({})

const getResourceForm = (lessonId) => {
  if (!resourceForms[lessonId]) {
    resourceForms[lessonId] = useForm({
      title: '',
      file: null
    })
  }
  return resourceForms[lessonId]
}

const addResource = (lesson) => {
  const form = getResourceForm(lesson.id)

  form.post(route('admin.resources.store', lesson.id), {
    forceFormData: true,
    onSuccess: () => form.reset()
  })
}

const deleteResource = (id) => {
  router.delete(route('admin.resources.destroy', id))
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
      <input
        v-model="getLessonForm(section.id).title"
        placeholder="Lesson title"
        class="bg-gray-800 p-2"
      />

      <input
        type="file"
        @change="e => getLessonForm(section.id).video = e.target.files[0]"
      />

      <button @click="addLesson(section)" class="bg-blue-600 px-3">
        Add Lesson
      </button>
    </div>

    <!-- LESSONS -->
    <div
      v-for="lesson in section.lessons"
      :key="lesson.id"
      class="ml-4 mt-2"
    >

      <!-- LESSON HEADER -->
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

      <!-- ADD RESOURCE -->
      <div class="mt-2 flex gap-2">

        <input
          v-model="getResourceForm(lesson.id).title"
          placeholder="Resource title"
          class="bg-gray-800 p-2"
        />

        <input
          type="file"
          @change="e => getResourceForm(lesson.id).file = e.target.files[0]"
        />

        <button
          @click="addResource(lesson)"
          class="bg-green-600 px-3"
        >
          Add File
        </button>

      </div>

    </div>

  </div>

</div>
</template>