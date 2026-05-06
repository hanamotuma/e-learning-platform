<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { BookOpen, Play, Clock, CheckCircle, Award } from 'lucide-vue-next'

const props = defineProps({
  enrolledCourses: Array
})

const formatNumber = (num) => {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'k'
  return num.toString()
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-800 dark:text-white">My Courses</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Continue your learning journey</p>
      </div>

      <div v-if="enrolledCourses.length === 0" class="text-center py-20">
        <BookOpen class="w-20 h-20 text-slate-300 mx-auto mb-4" />
        <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">No courses yet</h3>
        <p class="text-slate-500 mb-6">Start learning by enrolling in a course</p>
        <Link :href="route('home')" class="px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700">
          Browse Courses
        </Link>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="course in enrolledCourses" :key="course.id" class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all">
          <img :src="course.image" class="w-full h-48 object-cover" />
          <div class="p-5">
            <div class="flex items-center space-x-2 mb-2">
              <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-600 text-xs font-semibold rounded-lg">
                Enrolled
              </span>
              <div class="flex items-center space-x-1">
                <CheckCircle class="w-3 h-3 text-green-500" />
                <span class="text-xs text-green-600">Lifetime Access</span>
              </div>
            </div>
            
            <h3 class="font-bold text-lg dark:text-white mb-2 line-clamp-2">{{ course.title }}</h3>
            <p class="text-sm text-slate-500 mb-3">{{ course.instructor }}</p>
            
            <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700">
              <div class="flex items-center space-x-2 text-xs text-slate-500">
                <Clock class="w-3 h-3" />
                <span>{{ course.hours }} hours</span>
              </div>
              <Link :href="route('course.learn', course.id)" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-all flex items-center space-x-1">
                <Play class="w-4 h-4" />
                <span>Continue</span>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>