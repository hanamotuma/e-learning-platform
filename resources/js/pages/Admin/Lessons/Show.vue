<script setup>
import { useForm, router, Link } from '@inertiajs/vue3'

const props = defineProps({
  lesson: Object,
  progress: Object, // optional (from backend)
})

// FORM FOR PROGRESS UPDATE
const progressForm = useForm({
  status: 'completed',
})

// MARK LESSON AS COMPLETED
const markCompleted = () => {
  progressForm.post(route('progress.update', props.lesson.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="min-h-screen bg-[#0f0a24] text-white p-6">

    <!-- BACK -->
    <Link
      :href="route('admin.courses.show', lesson.id)"
      class="text-blue-400 hover:underline"
    >
      ← Back to Course
    </Link>

    <!-- LESSON TITLE -->
    <h1 class="text-3xl font-bold mt-4">
      {{ lesson.title }}
    </h1>

    <!-- VIDEO -->
    <div v-if="lesson.video_url" class="mt-6">
      <video
        :src="`/storage/${lesson.video_url}`"
        controls
        class="w-full max-w-3xl rounded-lg"
      />
    </div>

    <!-- PROGRESS STATUS -->
    <div class="mt-4 flex items-center gap-4">

      <span
        class="px-3 py-1 rounded text-sm"
        :class="progress?.status === 'completed'
          ? 'bg-green-600'
          : progress?.status === 'in_progress'
            ? 'bg-yellow-600'
            : 'bg-gray-600'"
      >
        {{ progress?.status ?? 'not_started' }}
      </span>

      <button
        @click="markCompleted"
        class="bg-green-600 px-4 py-2 rounded"
      >
        Mark as Completed
      </button>

    </div>

    <!-- RESOURCES -->
    <div v-if="lesson.resources?.length" class="mt-8">

      <h2 class="text-xl font-semibold mb-3">
        Resources
      </h2>

      <div class="space-y-2">

        <div
          v-for="res in lesson.resources"
          :key="res.id"
          class="flex justify-between items-center bg-[#1a1445] p-3 rounded"
        >

          <div>
            📎 {{ res.title }}
            <span class="text-gray-400 text-sm">
              ({{ res.file_type }})
            </span>
          </div>

          <a
            :href="`/storage/${res.file_path}`"
            target="_blank"
            class="text-blue-400 hover:underline"
          >
            Download
          </a>

        </div>

      </div>

    </div>

  </div>
</template>