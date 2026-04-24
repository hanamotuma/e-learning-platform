<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'

type Course = {
  id: number
  title: string
  price: number
  is_published: boolean
  category: string | null
  duration_weeks: number | null
  requirements: string | null
  image: string | null
  video: string | null
}

const props = defineProps<{
  courses: {
    data: Course[]
    links: any[]
  }
}>()

// DELETE COURSE
const deleteCourse = (id: number) => {
  if (confirm('Delete this course?')) {
    router.delete(route('admin.courses.destroy', id))
  }
}

// TOGGLE PUBLISH
const togglePublish = (course: Course) => {
  router.patch(route('admin.courses.update', course.id), {
    is_published: !course.is_published
  }, {
    preserveScroll: true
  })
}
</script>

<template>
  <div class="min-h-screen bg-[#0f0a24] text-white p-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-3xl font-bold">Courses</h1>
        <p class="text-gray-400 text-sm">
          Manage your learning content
        </p>
      </div>

      <Link
        :href="route('admin.courses.create')"
        class="bg-purple-600 px-5 py-2 rounded-xl font-semibold hover:bg-purple-700"
      >
        + New Course
      </Link>
    </div>

    <!-- GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <div
        v-for="course in courses.data"
        :key="course.id"
        class="bg-[#16103a] rounded-2xl overflow-hidden border border-white/10 hover:scale-[1.02] transition"
      >

        <!-- IMAGE -->
        <div class="h-40 bg-black">
          <img
            v-if="course.image"
            :src="`/storage/${course.image}`"
            class="w-full h-full object-cover"
          />
          <div v-else class="h-full flex items-center justify-center text-gray-500">
            No Image
          </div>
        </div>

        <!-- VIDEO -->
        <div v-if="course.video" class="p-2">
          <video
            :src="`/storage/${course.video}`"
            controls
            class="w-full h-40 object-cover rounded-lg border border-white/10"
          />
        </div>

        <!-- CONTENT -->
        <div class="p-4 space-y-2">

          <!-- TITLE -->
          <h2 class="text-lg font-bold">
            {{ course.title }}
          </h2>

          <!-- CATEGORY -->
          <p class="text-sm text-gray-400">
            {{ course.category ?? 'No category' }}
          </p>

          <!-- PRICE -->
          <p class="text-sm">
            ${{ course.price }}
          </p>

          <!-- DURATION -->
          <p class="text-xs text-gray-400">
            ⏱ {{ course.duration_weeks ?? 0 }} weeks
          </p>

          <!-- REQUIREMENTS -->
          <p
            v-if="course.requirements"
            class="text-xs text-gray-500 line-clamp-2"
          >
            📌 {{ course.requirements }}
          </p>

          <!-- STATUS -->
          <div class="flex justify-between items-center mt-3">

            <span
              class="px-2 py-1 text-xs rounded"
              :class="course.is_published ? 'bg-green-600' : 'bg-yellow-600'"
            >
              {{ course.is_published ? 'Published' : 'Draft' }}
            </span>

            <!-- TOGGLE -->
            <button
              @click="togglePublish(course)"
              class="w-10 h-5 flex items-center rounded-full p-1 transition"
              :class="course.is_published ? 'bg-green-500' : 'bg-gray-600'"
            >
              <div
                class="bg-white w-4 h-4 rounded-full shadow-md transition-transform"
                :class="course.is_published ? 'translate-x-5' : ''"
              />
            </button>

          </div>

          <!-- ACTIONS -->
          <div class="flex justify-between mt-4">
              <!-- SHOW -->
  <Link
    :href="route('admin.courses.show', course.id)"
    class="text-green-400 text-sm hover:underline"
  >
    View
  </Link>

            <Link
              :href="route('admin.courses.edit', course.id)"
              class="text-blue-400 text-sm hover:underline"
            >
              Edit
            </Link>

            <button
              @click="deleteCourse(course.id)"
              class="text-red-400 text-sm hover:underline"
            >
              Delete
            </button>

          </div>

        </div>

      </div>

    </div>

    <!-- PAGINATION -->
    <div class="mt-8">
      <Pagination :links="courses.links" />
    </div>

  </div>
</template>