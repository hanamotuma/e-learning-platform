<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { 
  ChevronLeft, CheckCircle, XCircle, Star, Edit2, Trash2, 
  Users, DollarSign, Clock, Trophy, Plus, Eye, FileText
} from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  course: Object
})

const activeTab = ref('overview')
const quizzes = ref(props.course.quizzes || [])
const showQuizModal = ref(false)
const editingQuiz = ref(null)

const quizForm = ref({
  title: '',
  description: '',
  time_limit_minutes: '',
  passing_score: 70,
  max_attempts: 3,
  is_published: false
})

const approveCourse = () => {
  if (confirm(`Approve "${props.course.title}"?`)) {
    router.post(`/admin/courses/${props.course.id}/approve`)
  }
}

const rejectCourse = () => {
  const reason = prompt('Provide rejection reason:')
  if (reason) {
    router.post(`/admin/courses/${props.course.id}/reject`, { reason })
  }
}

const featureCourse = () => {
  router.post(`/admin/courses/${props.course.id}/feature`)
}

const deleteCourse = () => {
  if (confirm(`Delete "${props.course.title}"? This cannot be undone.`)) {
    router.delete(`/admin/courses/${props.course.id}`)
  }
}

// Quiz Management Functions
const openCreateQuiz = () => {
  editingQuiz.value = null
  quizForm.value = {
    title: '',
    description: '',
    time_limit_minutes: '',
    passing_score: 70,
    max_attempts: 3,
    is_published: false
  }
  showQuizModal.value = true
}

const editQuiz = (quiz) => {
  editingQuiz.value = quiz
  quizForm.value = {
    title: quiz.title,
    description: quiz.description || '',
    time_limit_minutes: quiz.time_limit_minutes || '',
    passing_score: quiz.passing_score,
    max_attempts: quiz.max_attempts,
    is_published: quiz.is_published
  }
  showQuizModal.value = true
}

const saveQuiz = async () => {
  if (!quizForm.value.title) {
    alert('Please enter quiz title')
    return
  }

  try {
    if (editingQuiz.value) {
      const response = await axios.put(`/admin/courses/${props.course.id}/quizzes/${editingQuiz.value.id}`, quizForm.value)
      if (response.data.success) {
        const index = quizzes.value.findIndex(q => q.id === editingQuiz.value.id)
        quizzes.value[index] = { ...editingQuiz.value, ...quizForm.value }
      }
    } else {
      const response = await axios.post(`/admin/courses/${props.course.id}/quizzes`, quizForm.value)
      if (response.data.success) {
        quizzes.value.push(response.data.quiz)
      }
    }
    showQuizModal.value = false
    alert('Quiz saved successfully')
  } catch (error) {
    alert('Error saving quiz')
  }
}

const deleteQuiz = async (quizId) => {
  if (confirm('Delete this quiz? All questions will also be deleted.')) {
    await axios.delete(`/admin/courses/${props.course.id}/quizzes/${quizId}`)
    quizzes.value = quizzes.value.filter(q => q.id !== quizId)
    alert('Quiz deleted')
  }
}

const toggleQuizPublish = async (quiz) => {
  try {
    await axios.post(`/admin/courses/${props.course.id}/quizzes/${quiz.id}/toggle-publish`)
    quiz.is_published = !quiz.is_published
    alert(quiz.is_published ? 'Quiz published' : 'Quiz unpublished')
  } catch (error) {
    alert('Error updating quiz status')
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US').format(amount || 0) + ' ETB'
}

const goBack = () => {
  router.get('/admin/courses')
}
</script>

<template>
  <Head :title="course.title + ' | Admin'" />
  
  <AdminLayout>
    <div class="p-6">
      <button @click="goBack" class="flex items-center gap-2 text-slate-600 mb-6 hover:text-blue-600">
        <ChevronLeft class="w-5 h-5" />
        Back to Courses
      </button>
      
      <!-- Tabs -->
      <div class="border-b mb-6">
        <div class="flex gap-6">
          <button @click="activeTab = 'overview'" :class="['pb-3 font-medium transition-colors', activeTab === 'overview' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-500']">
            Overview
          </button>
          <button @click="activeTab = 'quizzes'" :class="['pb-3 font-medium transition-colors', activeTab === 'quizzes' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-500']">
            Quizzes ({{ quizzes.length }})
          </button>
          <button @click="activeTab = 'content'" :class="['pb-3 font-medium transition-colors', activeTab === 'content' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-500']">
            Course Content
          </button>
        </div>
      </div>

      <!-- Overview Tab -->
      <div v-if="activeTab === 'overview'" class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border overflow-hidden">
            <img :src="course.image || '/default-course.jpg'" class="w-full h-64 object-cover" />
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <div class="flex gap-2">
                  <span :class="[
                    'px-3 py-1 text-sm rounded-full',
                    course.is_published && course.status === 'approved' ? 'bg-green-100 text-green-700' :
                    course.status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700'
                  ]">
                    {{ course.is_published && course.status === 'approved' ? 'Published' : course.status }}
                  </span>
                  <span v-if="course.is_featured" class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">Featured</span>
                </div>
                <div class="flex gap-2">
                  <Link :href="`/admin/courses/${course.id}/edit`" class="p-2 text-green-600 hover:bg-green-50 rounded-lg">
                    <Edit2 class="w-5 h-5" />
                  </Link>
                  <button @click="deleteCourse" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                    <Trash2 class="w-5 h-5" />
                  </button>
                </div>
              </div>
              
              <h1 class="text-2xl font-bold mb-4">{{ course.title }}</h1>
              <p class="text-slate-600 dark:text-slate-300 mb-6">{{ course.description }}</p>
              
              <div class="border-t pt-6">
                <h2 class="text-lg font-bold mb-4">Course Content</h2>
                <div v-for="section in course.sections" :key="section.id" class="mb-4 border rounded-lg p-4">
                  <h3 class="font-bold mb-2">{{ section.title }}</h3>
                  <div class="pl-4 space-y-1">
                    <div v-for="lesson in section.lessons" :key="lesson.id" class="text-sm text-slate-500">
                      📹 {{ lesson.title }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Sidebar -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-slate-800 rounded-2xl border p-6 sticky top-24">
            <h3 class="text-lg font-bold mb-4">Course Details</h3>
            
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-slate-500">Instructor</span>
                <span class="font-medium">{{ course.instructor?.full_name || 'Admin' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Price</span>
                <span class="font-bold text-emerald-600">{{ formatCurrency(course.price) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Category</span>
                <span>{{ course.category?.name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Level</span>
                <span class="capitalize">{{ course.difficulty_level }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Enrolled Students</span>
                <span>{{ course.enrollments?.length || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Created</span>
                <span>{{ new Date(course.created_at).toLocaleDateString() }}</span>
              </div>
            </div>
            
            <div class="border-t mt-6 pt-6 space-y-3">
              <button v-if="course.status === 'pending'" @click="approveCourse" class="w-full py-3 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 flex items-center justify-center gap-2">
                <CheckCircle class="w-4 h-4" />
                Approve & Publish
              </button>
              <button v-if="course.status === 'pending'" @click="rejectCourse" class="w-full py-3 border border-red-600 text-red-600 rounded-xl hover:bg-red-50 flex items-center justify-center gap-2">
                <XCircle class="w-4 h-4" />
                Reject Course
              </button>
              <button @click="featureCourse" class="w-full py-3 border border-purple-600 text-purple-600 rounded-xl hover:bg-purple-50 flex items-center justify-center gap-2">
                <Star class="w-4 h-4" :class="{ 'fill-purple-600': course.is_featured }" />
                {{ course.is_featured ? 'Remove from Featured' : 'Feature Course' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Quizzes Tab -->
      <div v-if="activeTab === 'quizzes'" class="max-w-5xl mx-auto">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-6 flex justify-between items-center">
            <div>
              <h1 class="text-2xl font-bold text-white">Course Quizzes</h1>
              <p class="text-purple-100 mt-1">Manage quizzes for {{ course.title }}</p>
            </div>
            <button @click="openCreateQuiz" class="px-5 py-2.5 bg-white text-purple-600 rounded-lg hover:bg-purple-50 flex items-center gap-2 font-semibold">
              <Plus class="w-4 h-4" /> Create Quiz
            </button>
          </div>

          <div v-if="quizzes.length === 0" class="p-12 text-center">
            <Trophy class="w-20 h-20 text-slate-300 mx-auto mb-4" />
            <p class="text-slate-500 mb-2">No quizzes created for this course yet</p>
            <button @click="openCreateQuiz" class="px-5 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
              Create First Quiz
            </button>
          </div>

          <div v-else class="divide-y">
            <div v-for="quiz in quizzes" :key="quiz.id" class="p-6 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-all">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <Trophy class="w-5 h-5 text-purple-600" />
                    <h3 class="text-xl font-bold">{{ quiz.title }}</h3>
                    <button @click="toggleQuizPublish(quiz)" :class="['px-2 py-1 text-xs rounded-full cursor-pointer', quiz.is_published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700']">
                      {{ quiz.is_published ? 'Published' : 'Draft' }}
                    </button>
                  </div>
                  <p class="text-slate-500 mb-3">{{ quiz.description || 'No description' }}</p>
                  <div class="flex flex-wrap gap-4 text-sm">
                    <span class="flex items-center gap-1 text-slate-500"><Clock class="w-4 h-4" /> {{ quiz.time_limit_minutes || 'No' }} min limit</span>
                    <span class="flex items-center gap-1 text-slate-500"><Trophy class="w-4 h-4" /> Passing: {{ quiz.passing_score }}%</span>
                    <span class="flex items-center gap-1 text-slate-500"><Users class="w-4 h-4" /> Max attempts: {{ quiz.max_attempts }}</span>
                    <span class="flex items-center gap-1 text-slate-500"><FileText class="w-4 h-4" /> {{ quiz.questions_count || 0 }} questions</span>
                  </div>
                </div>
                <div class="flex gap-2">
                  <Link :href="`/admin/quizzes/${quiz.id}`" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                    <Eye class="w-4 h-4" />
                  </Link>
                  <button @click="editQuiz(quiz)" class="p-2 text-green-600 hover:bg-green-50 rounded-lg">
                    <Edit2 class="w-4 h-4" />
                  </button>
                  <button @click="deleteQuiz(quiz.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Tab -->
      <div v-if="activeTab === 'content'" class="max-w-5xl mx-auto">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6">
            <h1 class="text-2xl font-bold text-white">Course Content</h1>
            <p class="text-blue-100 mt-1">View course sections and lessons</p>
          </div>

          <div class="p-6">
            <div v-for="section in course.sections" :key="section.id" class="mb-6 border rounded-lg overflow-hidden">
              <div class="bg-slate-50 dark:bg-slate-700/30 p-4">
                <h3 class="font-bold">{{ section.title }}</h3>
              </div>
              <div class="p-4 space-y-2">
                <div v-for="lesson in section.lessons" :key="lesson.id" class="flex items-center justify-between p-2 hover:bg-slate-50 rounded">
                  <div class="flex items-center gap-2">
                    <Video class="w-4 h-4 text-slate-400" />
                    <span>{{ lesson.title }}</span>
                  </div>
                  <span class="text-xs text-slate-500">{{ lesson.duration_minutes || 0 }} min</span>
                </div>
                <div v-for="quiz in section.quizzes" :key="quiz.id" class="flex items-center justify-between p-2 hover:bg-slate-50 rounded">
                  <div class="flex items-center gap-2">
                    <Trophy class="w-4 h-4 text-purple-500" />
                    <span>Quiz: {{ quiz.title }}</span>
                  </div>
                  <span :class="['px-2 py-1 text-xs rounded-full', quiz.is_published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700']">
                    {{ quiz.is_published ? 'Published' : 'Draft' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Quiz Modal -->
    <div v-if="showQuizModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 flex justify-between items-center">
          <h3 class="text-xl font-bold text-white">{{ editingQuiz ? 'Edit Quiz' : 'Create Quiz' }}</h3>
          <button @click="showQuizModal = false" class="text-white hover:bg-white/20 p-1 rounded-lg">
            <XCircle class="w-6 h-6" />
          </button>
        </div>

        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Quiz Title *</label>
            <input v-model="quizForm.title" type="text" class="w-full px-4 py-2 border rounded-lg" placeholder="e.g., Chapter 1 Quiz" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea v-model="quizForm.description" rows="3" class="w-full px-4 py-2 border rounded-lg" placeholder="Describe what this quiz covers..."></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Time Limit (minutes)</label>
              <input v-model="quizForm.time_limit_minutes" type="number" class="w-full px-4 py-2 border rounded-lg" placeholder="No limit" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Passing Score (%)</label>
              <input v-model="quizForm.passing_score" type="number" class="w-full px-4 py-2 border rounded-lg" min="0" max="100" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Max Attempts</label>
              <input v-model="quizForm.max_attempts" type="number" class="w-full px-4 py-2 border rounded-lg" min="1" />
            </div>
            <div class="flex items-center pt-6">
              <label class="flex items-center gap-2">
                <input v-model="quizForm.is_published" type="checkbox" class="w-4 h-4" />
                <span class="text-sm font-medium">Publish immediately</span>
              </label>
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button @click="saveQuiz" class="flex-1 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
              {{ editingQuiz ? 'Update Quiz' : 'Create Quiz' }}
            </button>
            <button @click="showQuizModal = false" class="px-6 py-3 border rounded-lg hover:bg-slate-50">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>