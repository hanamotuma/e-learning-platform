<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Search, Eye, Edit2, Trash2, CheckCircle, XCircle, Star, Filter, Plus, ChevronDown } from 'lucide-vue-next'

const props = defineProps({
  courses: Object,
  stats: Object,
  filters: Object,
  categories: Array
})


// Add this inside script setup
onMounted(() => {
  const justCreated = sessionStorage.getItem('course_created')
  if (justCreated === 'true') {
    sessionStorage.removeItem('course_created')
    router.reload({ only: ['courses', 'stats'] })
  }
})

const searchForm = ref({ search: props.filters?.search || '' })
const statusFilter = ref(props.filters?.status || 'all')
const showFilters = ref(false)

const searchCourses = () => {
  router.get('/admin/courses', { 
    search: searchForm.value.search, 
    status: statusFilter.value 
  })
}

const approveCourse = (course) => {
  if (confirm(`Approve "${course.title}"?`)) {
    router.post(`/admin/courses/${course.id}/approve`)
  }
}

const rejectCourse = (course) => {
  const reason = prompt('Provide rejection reason:')
  if (reason) {
    router.post(`/admin/courses/${course.id}/reject`, { reason })
  }
}

const featureCourse = (course) => {
  router.post(`/admin/courses/${course.id}/feature`)
}

const deleteCourse = (course) => {
  if (confirm(`Delete "${course.title}"? This cannot be undone.`)) {
    router.delete(`/admin/courses/${course.id}`)
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US').format(amount || 0) + ' ETB'
}

const formatDate = (date) => new Date(date).toLocaleDateString()

const getStatusBadge = (status, isPublished) => {
  if (isPublished && status === 'approved') return 'Published'
  if (status === 'pending') return 'Pending Review'
  if (status === 'rejected') return 'Rejected'
  if (status === 'draft') return 'Draft'
  if (status === 'suspended') return 'Suspended'
  return 'Pending'
}

const getStatusColor = (status, isPublished) => {
  if (isPublished && status === 'approved') return 'bg-green-100 text-green-700'
  if (status === 'pending') return 'bg-yellow-100 text-yellow-700'
  if (status === 'rejected') return 'bg-red-100 text-red-700'
  if (status === 'draft') return 'bg-gray-100 text-gray-700'
  if (status === 'suspended') return 'bg-orange-100 text-orange-700'
  return 'bg-blue-100 text-blue-700'
}
</script>

<template>
  <Head title="Course Management | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold dark:text-white">Course Management</h1>
          <p class="text-slate-500 mt-1">Approve, edit, and manage all courses</p>
        </div>
        <Link href="/admin/courses/create" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center gap-2">
          <Plus class="w-4 h-4" />
          Create Course
        </Link>
      </div>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Total Courses</p>
          <p class="text-xl font-bold">{{ stats?.total || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Published</p>
          <p class="text-xl font-bold text-green-600">{{ stats?.published || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Pending</p>
          <p class="text-xl font-bold text-yellow-600">{{ stats?.pending || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Draft</p>
          <p class="text-xl font-bold text-gray-600">{{ stats?.draft || 0 }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Featured</p>
          <p class="text-xl font-bold text-purple-600">{{ stats?.featured || 0 }}</p>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-center">
          <div class="flex-1 min-w-[200px]">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input v-model="searchForm.search" @keyup.enter="searchCourses" type="text" placeholder="Search by title, instructor..." class="w-full pl-10 pr-4 py-2 border rounded-lg dark:bg-slate-900" />
            </div>
          </div>
          <select v-model="statusFilter" @change="searchCourses" class="px-4 py-2 border rounded-lg dark:bg-slate-900">
            <option value="all">All Status</option>
            <option value="pending">Pending Review</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="rejected">Rejected</option>
            <option value="suspended">Suspended</option>
          </select>
          <button @click="showFilters = !showFilters" class="px-4 py-2 border rounded-lg flex items-center gap-2">
            <Filter class="w-4 h-4" />
            Filters
            <ChevronDown class="w-4 h-4" :class="{ 'rotate-180': showFilters }" />
          </button>
          <button @click="searchCourses" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
        </div>
      </div>
      
      <!-- Courses Table -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-700/50">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-medium">Course</th>
                <th class="px-6 py-3 text-left text-sm font-medium">Instructor</th>
                <th class="px-6 py-3 text-left text-sm font-medium">Price</th>
                <th class="px-6 py-3 text-left text-sm font-medium">Students</th>
                <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
                <th class="px-6 py-3 text-left text-sm font-medium">Submitted</th>
                <th class="px-6 py-3 text-left text-sm font-medium">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="course in courses.data" :key="course.id">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <img :src="course.image || '/default-course.jpg'" class="w-12 h-12 rounded-lg object-cover" />
                    <div>
                      <p class="font-medium">{{ course.title }}</p>
                      <p class="text-sm text-slate-500">{{ course.category?.name }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">{{ course.instructor?.full_name }}</td>
                <td class="px-6 py-4 font-bold">{{ formatCurrency(course.price) }}</td>
                <td class="px-6 py-4">{{ course.enrollments_count || 0 }}</td>
                <td class="px-6 py-4">
                  <span :class="['px-2 py-1 text-xs rounded-full', getStatusColor(course.status, course.is_published)]">
                    {{ getStatusBadge(course.status, course.is_published) }}
                  </span>
                </td>
                <td class="px-6 py-4">{{ formatDate(course.created_at) }}</td>
                <td class="px-6 py-4">
                  <div class="flex gap-2">
                    <Link :href="`/admin/courses/${course.id}`" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                      <Eye class="w-4 h-4" />
                    </Link>
                    <Link :href="`/admin/courses/${course.id}/edit`" class="p-2 text-green-600 hover:bg-green-50 rounded-lg">
                      <Edit2 class="w-4 h-4" />
                    </Link>
                    <button v-if="course.status === 'pending'" @click="approveCourse(course)" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg">
                      <CheckCircle class="w-4 h-4" />
                    </button>
                    <button v-if="course.status === 'pending'" @click="rejectCourse(course)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                      <XCircle class="w-4 h-4" />
                    </button>
                    <button @click="featureCourse(course)" class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg">
                      <Star class="w-4 h-4" :class="{ 'fill-yellow-600': course.is_featured }" />
                    </button>
                    <button @click="deleteCourse(course)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="p-4 border-t">
          <div class="flex justify-between items-center">
            <p class="text-sm text-slate-500">Showing {{ courses.from || 0 }} to {{ courses.to || 0 }} of {{ courses.total }} results</p>
            <div class="flex gap-2">
              <Link v-for="link in courses.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 rounded-lg border hover:bg-slate-50" :class="{ 'bg-blue-600 text-white border-blue-600': link.active }" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>