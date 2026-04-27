<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import debounce from 'lodash/debounce'

// 1. Updated Type Definitions
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
  reviews_count?: number
  lessons_count?: number
}

const props = defineProps<{
  courses: {
    data: Course[]
    links: any[]
    total: number
  },
  filters?: {
    search: string
  }
}>()

// 2. Search Functionality
const search = ref(props.filters?.search || '')
watch(search, debounce((value) => {
  router.get(route('admin.courses.index'), { search: value }, {
    preserveState: true,
    replace: true
  })
}, 300))

// 3. Actions
const deleteCourse = (id: number) => {
  if (confirm('Are you sure? This will delete all related lessons and quizzes.')) {
    router.delete(route('admin.courses.destroy', id))
  }
}

const togglePublish = (course: Course) => {
  router.patch(route('admin.courses.update', course.id), {
    is_published: !course.is_published
  }, { preserveScroll: true })
}
</script>

<template>
  <div class="min-h-screen bg-[#0f0a24] text-white p-6">
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-[#16103a] p-4 rounded-2xl border border-white/5">
        <p class="text-gray-400 text-xs uppercase font-bold tracking-widest">Total Courses</p>
        <p class="text-2xl font-black">{{ courses.total }}</p>
      </div>
      <div class="bg-[#16103a] p-4 rounded-2xl border border-white/5">
        <p class="text-gray-400 text-xs uppercase font-bold tracking-widest">Published</p>
        <p class="text-2xl font-black text-green-400">
            {{ courses.data.filter(c => c.is_published).length }}
        </p>
      </div>
      <div class="bg-[#16103a] p-4 rounded-2xl border border-white/5">
        <p class="text-gray-400 text-xs uppercase font-bold tracking-widest">Revenue Model</p>
        <p class="text-2xl font-black text-purple-400">Paid</p>
      </div>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-bold">Course Library</h1>
        <p class="text-gray-400 text-sm">Curate and manage your educational content</p>
      </div>

      <div class="flex items-center gap-4 w-full md:w-auto">
        <input 
          v-model="search"
          type="text" 
          placeholder="Search courses..." 
          class="bg-[#16103a] border border-white/10 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none w-full md:w-64"
        />
        <Link
          :href="route('admin.courses.create')"
          class="bg-purple-600 px-6 py-2 rounded-xl font-bold hover:bg-purple-700 transition whitespace-nowrap"
        >
          + Create
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="course in courses.data"
        :key="course.id"
        class="bg-[#16103a] rounded-3xl overflow-hidden border border-white/10 flex flex-col group"
      >
        <div class="relative h-48 bg-black overflow-hidden">
          <img
            v-if="course.image"
            :src="`/storage/${course.image}`"
            class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
          />
          <div v-else class="h-full flex items-center justify-center text-gray-600 italic">
            No Preview Image
          </div>

          <Link 
            :href="route('admin.courses.reviews', course.id)"
            class="absolute top-4 left-4 bg-black/60 backdrop-blur-md px-3 py-1 rounded-full text-[10px] font-bold border border-white/10 hover:bg-purple-600 transition"
          >
            ★ {{ course.reviews_count ?? 0 }} Reviews
          </Link>

          <div class="absolute top-4 right-4">
            <button
              @click="togglePublish(course)"
              class="w-10 h-5 flex items-center rounded-full p-1 transition shadow-lg"
              :class="course.is_published ? 'bg-green-500' : 'bg-gray-600'"
            >
              <div
                class="bg-white w-3.5 h-3.5 rounded-full transition-transform"
                :class="course.is_published ? 'translate-x-5' : ''"
              />
            </button>
          </div>
        </div>

        <div class="p-6 flex-1 flex flex-col">
          <div class="flex justify-between items-start mb-2">
            <span class="text-[10px] text-purple-400 font-black uppercase tracking-widest">
                {{ course.category ?? 'Uncategorized' }}
            </span>
            <span class="text-sm font-bold">${{ course.price }}</span>
          </div>

          <h2 class="text-xl font-bold mb-2 line-clamp-1">{{ course.title }}</h2>
          
          <div class="flex items-center gap-4 text-xs text-gray-500 mb-4">
             <span>⏱ {{ course.duration_weeks ?? 0 }} Weeks</span>
             <span>📚 {{ course.lessons_count ?? 0 }} Lessons</span>
          </div>

          <p class="text-xs text-gray-400 line-clamp-2 mb-6 italic">
            {{ course.requirements || 'No specific requirements listed for this course.' }}
          </p>

          <div class="mt-auto pt-4 border-t border-white/5 grid grid-cols-2 gap-2">
            <Link
              :href="route('admin.courses.edit', course.id)"
              class="bg-white/5 hover:bg-white/10 text-center py-2 rounded-xl text-xs font-bold transition"
            >
              Edit Details
            </Link>
            <Link
              :href="route('admin.courses.show', course.id)"
              class="bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600 hover:text-white text-center py-2 rounded-xl text-xs font-bold transition"
            >
              Curriculum
            </Link>
            <Link
              :href="route('admin.courses.reviews', course.id)"
              class="bg-white/5 hover:bg-white/10 text-center py-2 rounded-xl text-xs font-bold transition col-span-1"
            >
              Reviews
            </Link>
            <button
              @click="deleteCourse(course.id)"
              class="bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white py-2 rounded-xl text-xs font-bold transition"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="courses.data.length === 0" class="text-center py-20 bg-[#16103a] rounded-3xl border border-dashed border-white/10">
        <p class="text-gray-500">No courses found matching your criteria.</p>
    </div>

    <div class="mt-12 flex justify-center">
      <Pagination :links="courses.links" />
    </div>
  </div>
</template>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
</style>