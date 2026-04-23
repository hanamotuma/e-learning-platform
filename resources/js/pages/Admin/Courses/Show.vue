<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  course: Object,
})
</script>

<template>
  <div class="min-h-screen bg-[#0f0a24] text-white p-6">

    <!-- HEADER -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold">{{ course.title }}</h1>
      <p class="text-gray-400 mt-2">
        {{ course.description }}
      </p>
    </div>

    <!-- IMAGE -->
    <div v-if="course.image" class="mb-6">
      <img
        :src="`/storage/${course.image}`"
        class="w-full max-w-xl rounded-xl"
      />
    </div>

    <!-- VIDEO -->
    <div v-if="course.video_url" class="mb-8">
      <video
        :src="`/storage/${course.video_url}`"
        controls
        class="w-full max-w-xl rounded-xl"
      />
    </div>

    <!-- INFO -->
    <div class="grid grid-cols-2 gap-4 mb-10 text-sm text-gray-300">
      <p>💰 Price: ${{ course.price }}</p>
      <p>📚 Category: {{ course.category?.name }}</p>
      <p>⏱ Duration: {{ course.duration_weeks }} weeks</p>
      <p>📊 Level: {{ course.difficulty_level }}</p>
      <p>
        Status:
        <span :class="course.is_published ? 'text-green-400' : 'text-yellow-400'">
          {{ course.is_published ? 'Published' : 'Draft' }}
        </span>
      </p>
    </div>

    <!-- SECTIONS -->
    <div class="space-y-6">
      <h2 class="text-2xl font-bold">Course Content</h2>

      <div v-if="course.sections?.length">

        <div
          v-for="section in course.sections"
          :key="section.id"
          class="bg-[#16103a] p-5 rounded-xl border border-white/10"
        >

          <!-- SECTION TITLE -->
          <h3 class="text-lg font-semibold text-purple-300 mb-3">
            {{ section.title }}
          </h3>

          <!-- LESSONS -->
          <div v-if="section.lessons?.length" class="space-y-2">

            <div
              v-for="lesson in section.lessons"
              :key="lesson.id"
              class="bg-[#0f0a24] p-3 rounded-lg border border-white/5"
            >

              <div class="flex justify-between items-center">
                <div>
                  <p class="font-medium">{{ lesson.title }}</p>
                  <p class="text-xs text-gray-400">
                    {{ lesson.duration }} mins
                  </p>
                </div>

                <Link
                  :href="`/lessons/${lesson.id}`"
                  class="text-blue-400 text-sm hover:underline"
                >
                  View
                </Link>
              </div>

              <!-- ✅ VIDEO (FIXED LOCATION) -->
              <div v-if="lesson.video_url" class="mt-2">
                <video
                  :src="`/storage/${lesson.video_url}`"
                  controls
                  class="w-full max-w-md rounded-lg"
                />
              </div>

            </div>

          </div>

          <p v-else class="text-gray-500 text-sm">
            No lessons in this section.
          </p>

        </div>

      </div>

      <p v-else class="text-gray-500">
        No sections available.
      </p>
    </div>

    <!-- BACK BUTTON -->
    <div class="mt-10">
      <Link
        :href="route('admin.courses.index')"
        class="bg-gray-600 px-5 py-2 rounded"
      >
        ← Back to Courses
      </Link>
    </div>

  </div>
</template>