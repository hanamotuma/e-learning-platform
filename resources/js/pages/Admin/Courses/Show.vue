<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  course: Object,
  progressData: {
    type: Array,
    default: () => []
  }
})

/* ---------------- PROGRESS MAP ---------------- */
const progressMap = computed(() => {
  const map = {}

  props.progressData.forEach(p => {
    map[p.lesson_id] = p
  })

  return map
})

/* ---------------- UPDATE PROGRESS ---------------- */
const updateProgress = (lesson, status) => {
  router.post(route('admin.progress.update', lesson.id), {
    status,
    progress_percentage: status === 'completed' ? 100 : 0
  }, {
    preserveScroll: true
  })
}

/* ---------------- CHECKBOX ---------------- */
const toggleComplete = (lesson, e) => {
  const checked = e.target.checked
  updateProgress(lesson, checked ? 'completed' : 'in_progress')
}

/* ---------------- VIDEO AUTO COMPLETE ---------------- */
const markCompleted = (lesson) => {
  updateProgress(lesson, 'completed')
}

/* ---------------- COURSE PROGRESS ---------------- */
const courseProgress = computed(() => {
  let total = 0
  let completed = 0

  props.course?.sections?.forEach(section => {
    section.lessons?.forEach(lesson => {
      total++

      if (progressMap.value[lesson.id]?.status === 'completed') {
        completed++
      }
    })
  })

  return total ? Math.round((completed / total) * 100) : 0
})
</script>

<template>
<div class="min-h-screen bg-[#0f0a24] text-white p-6">

  <!-- HEADER -->
  <div class="mb-6">
    <h1 class="text-3xl font-bold">{{ course.title }}</h1>
    <p class="text-gray-400 mt-2">{{ course.description }}</p>
  </div>

  <!-- COURSE PROGRESS -->
  <div class="mb-6">
    <p class="text-sm mb-1">Progress: {{ courseProgress }}%</p>

    <div class="w-full bg-gray-700 h-3 rounded">
      <div
        class="bg-green-500 h-3 rounded"
        :style="{ width: courseProgress + '%' }"
      ></div>
    </div>
  </div>

  <!-- COURSE VIDEO (FIXED) -->
  <div v-if="course.video_url" class="mb-8">
    <video
      :src="`/storage/${course.video_url}`"
      controls
      @ended="markCompleted({ id: 0 })"
      class="w-full max-w-xl rounded"
    />
  </div>

  <!-- IMAGE -->
  <div v-if="course.image" class="mb-6">
    <img :src="`/storage/${course.image}`" class="w-full max-w-xl rounded-xl" />
  </div>

  <!-- SECTIONS -->
  <div v-if="course.sections?.length" class="space-y-6">

    <div
      v-for="section in course.sections"
      :key="section.id"
      class="bg-[#16103a] p-5 rounded-xl"
    >

      <h3 class="text-lg text-purple-300 mb-3">
        {{ section.title }}
      </h3>

      <!-- LESSONS -->
      <div v-for="lesson in section.lessons" :key="lesson.id"
           class="bg-[#0f0a24] p-4 rounded-lg mb-3">

        <!-- HEADER -->
        <div class="flex justify-between items-center">
          <div>
            <p class="font-medium">{{ lesson.title }}</p>
            <p class="text-xs text-gray-400">
              {{ lesson.duration ?? 0 }} mins
            </p>
          </div>

          <Link
            :href="route('admin.lessons.show', lesson.id)"
            class="text-blue-400 text-sm"
          >
            View
          </Link>
        </div>

        <!-- LESSON VIDEO -->
        <div v-if="lesson.video_url" class="mt-2">
          <video
            :src="`/storage/${lesson.video_url}`"
            controls
            @ended="markCompleted(lesson)"
            class="w-full max-w-md rounded"
          />
        </div>

        <!-- CHECKBOX -->
        <div class="flex items-center gap-2 mt-3">
          <input
            type="checkbox"
            :checked="progressMap[lesson.id]?.status === 'completed'"
            @change="e => toggleComplete(lesson, e)"
          />

          <span class="text-sm text-gray-300">
            Mark as completed
          </span>
        </div>

        <!-- STATUS -->
        <div class="text-xs mt-1">
          <span v-if="progressMap[lesson.id]?.status === 'completed'" class="text-green-400">
            ✔ Completed
          </span>

          <span v-else-if="progressMap[lesson.id]" class="text-yellow-400">
            In Progress
          </span>

          <span v-else class="text-gray-500">
            Not Started
          </span>
        </div>

        <!-- PROGRESS BAR -->
        <div v-if="progressMap[lesson.id]" class="mt-2">
          <div class="w-full bg-gray-700 h-2 rounded">
            <div
              class="bg-green-500 h-2 rounded"
              :style="{ width: progressMap[lesson.id].progress_percentage + '%' }"
            ></div>
          </div>
        </div>

        <!-- RESOURCES -->
        <div v-if="lesson.resources?.length" class="mt-3 space-y-1">

          <div
            v-for="res in lesson.resources"
            :key="res.id"
            class="flex justify-between text-sm bg-[#1a1445] p-2 rounded"
          >
            <span>
              📎 {{ res.title }} ({{ res.file_type }})
            </span>

            <a
              :href="`/storage/${res.file_path}`"
              target="_blank"
              class="text-blue-400"
            >
              Download
            </a>
          </div>

        </div>

      </div>
    </div>
  </div>

</div>
</template>