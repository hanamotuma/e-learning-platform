<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
//import Pagination from '@/components/Pagination.vue'
import debounce from 'lodash/debounce'

// Type Definitions
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

// Search Functionality
const search = ref(props.filters?.search || '')

watch(search, debounce((value: string) => {
  router.get(route('admin.courses.index'), { search: value }, {
    preserveState: true,
    replace: true
  })
}, 300))

// Actions
const deleteCourse = (id: number) => {
  if (confirm('Are you sure? This will delete all related lessons and quizzes.')) {
    router.delete(route('admin.courses.destroy', { course: id }))
  }
}

const togglePublish = (course: Course) => {
  router.patch(route('admin.courses.update', { course: course.id }), {
    is_published: !course.is_published
  }, { preserveScroll: true })
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 p-6">
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <p class="text-slate-500 text-xs uppercase font-bold tracking-widest">Total Courses</p>
        <p class="text-2xl font-black text-slate-900">{{ courses.total }}</p>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <p class="text-slate-500 text-xs uppercase font-bold tracking-widest">Published</p>
        <p class="text-2xl font-black text-emerald-600">
            {{ courses.data.filter(c => c.is_published).length }}
        </p>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <p class="text-slate-500 text-xs uppercase font-bold tracking-widest">Revenue Model</p>
        <p class="text-2xl font-black text-blue-600">Paid</p>
      </div>
    </div>

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-bold text-slate-900">Course Library</h1>
        <p class="text-slate-500 text-sm">Curate and manage your educational content</p>
      </div>

      <div class="flex items-center gap-4 w-full md:w-auto">
        <input 
          v-model="search"
          type="text" 
          placeholder="Search courses..." 
          class="bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none w-full md:w-64"
        />
        <Link
          :href="route('admin.courses.create')"
          class="bg-blue-600 px-6 py-2 rounded-xl font-bold text-white hover:bg-blue-700 transition whitespace-nowrap"
        >
          + Create
        </Link>
      </div>
    </div>

    <!-- Courses Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="course in courses.data"
        :key="course.id"
        class="bg-white rounded-2xl overflow-hidden border border-slate-200 flex flex-col group shadow-sm hover:shadow-md transition"
      >
        <div class="relative h-48 bg-slate-100 overflow-hidden">
          <img
            v-if="course.image"
            :src="`/storage/${course.image}`"
            class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
          />
          <div v-else class="h-full flex items-center justify-center text-slate-400 italic text-sm">
            No Preview Image
          </div>

          <Link 
            :href="route('admin.courses.reviews', { course: course.id })"
            class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-[10px] font-bold border border-slate-200 hover:bg-blue-600 hover:text-white transition"
          >
            ★ {{ course.reviews_count ?? 0 }} Reviews
          </Link>

          <div class="absolute top-4 right-4">
            <button
              @click="togglePublish(course)"
              class="w-10 h-5 flex items-center rounded-full p-1 transition shadow-sm"
              :class="course.is_published ? 'bg-emerald-500' : 'bg-slate-300'"
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
            <span class="text-[10px] text-blue-600 font-black uppercase tracking-widest">
                {{ course.category ?? 'Uncategorized' }}
            </span>
            <span class="text-sm font-bold text-slate-900">${{ course.price }}</span>
          </div>

          <h2 class="text-xl font-bold text-slate-900 mb-2 line-clamp-1">{{ course.title }}</h2>
          
          <div class="flex items-center gap-4 text-xs text-slate-500 mb-4">
             <span>⏱ {{ course.duration_weeks ?? 0 }} Weeks</span>
             <span>📚 {{ course.lessons_count ?? 0 }} Lessons</span>
          </div>

          <p class="text-xs text-slate-500 line-clamp-2 mb-6 italic">
            {{ course.requirements || 'No specific requirements listed for this course.' }}
          </p>

          <div class="mt-auto pt-4 border-t border-slate-100 grid grid-cols-2 gap-2">
            <Link
              :href="route('admin.courses.edit', { course: course.id })"
              class="bg-slate-100 hover:bg-slate-200 text-center py-2 rounded-xl text-xs font-bold text-slate-700 transition"
            >
              Edit Details
            </Link>
            <Link
              :href="route('admin.courses.show', { course: course.id })"
              class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white text-center py-2 rounded-xl text-xs font-bold transition"
            >
              Curriculum
            </Link>
            <Link
              :href="route('admin.courses.reviews', { course: course.id })"
              class="bg-slate-100 hover:bg-slate-200 text-center py-2 rounded-xl text-xs font-bold text-slate-700 transition col-span-1"
            >
              Reviews
            </Link>
            <button
              @click="deleteCourse(course.id)"
              class="bg-red-50 hover:bg-red-500 text-red-600 hover:text-white py-2 rounded-xl text-xs font-bold transition"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="courses.data.length === 0" class="text-center py-20 bg-white rounded-2xl border border-dashed border-slate-200">
        <p class="text-slate-500">No courses found matching your criteria.</p>
    </div>

    <!-- Pagination -->
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