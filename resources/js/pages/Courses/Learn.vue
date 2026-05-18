<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { 
  Play, Clock, CheckCircle, Video, FileText, Trophy, 
  ChevronDown, ChevronRight, BookOpen, Users, Star,
  Award, MessageCircle, Download, Shield, ChevronLeft
} from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  course: Object,
  enrollment: Object,
  completedLessons: Array,
  passedQuizzes: Array,
  firstLesson: Object,
  firstQuiz: Object,
  progressPercentage: Number
})

const activeLesson = ref(null)
const expandedSections = ref([])
const showQuizModal = ref(false)
const selectedQuiz = ref(null)
const isPlaying = ref(false)
const currentVideo = ref(null)

// Initialize expanded sections - expand first section by default
onMounted(() => {
  if (props.course?.sections?.length > 0) {
    expandedSections.value.push(props.course.sections[0].id)
  }
  
  // Auto-select first lesson or quiz
  if (props.firstLesson) {
    activeLesson.value = props.firstLesson
  }
})

const toggleSection = (sectionId) => {
  const index = expandedSections.value.indexOf(sectionId)
  if (index > -1) {
    expandedSections.value.splice(index, 1)
  } else {
    expandedSections.value.push(sectionId)
  }
}

const selectLesson = (lesson) => {
  activeLesson.value = lesson
  // Reset video playing state
  isPlaying.value = false
  if (lesson.video_url) {
    currentVideo.value = lesson.video_url
  }
}

const openQuiz = (quiz) => {
  selectedQuiz.value = quiz
  showQuizModal.value = true
}

const closeQuizModal = () => {
  showQuizModal.value = false
  selectedQuiz.value = null
}

const markLessonComplete = async (lesson) => {
  try {
    await axios.post(`/courses/${props.course.id}/lessons/${lesson.id}/complete`)
    // Reload page to update progress
    router.reload()
  } catch (error) {
    console.error('Error marking lesson complete:', error)
  }
}

const isLessonCompleted = (lessonId) => {
  return props.completedLessons?.includes(lessonId) || false
}

const isQuizPassed = (quizId) => {
  return props.passedQuizzes?.includes(quizId) || false
}

const formatDuration = (minutes) => {
  if (!minutes) return '0 min'
  if (minutes < 60) return `${minutes} min`
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  return `${hours}h ${mins}min`
}

const calculateSectionProgress = (section) => {
  const totalItems = (section.lessons?.length || 0) + (section.quizzes?.length || 0)
  if (totalItems === 0) return 0
  
  let completedItems = 0
  section.lessons?.forEach(lesson => {
    if (isLessonCompleted(lesson.id)) completedItems++
  })
  section.quizzes?.forEach(quiz => {
    if (isQuizPassed(quiz.id)) completedItems++
  })
  
  return Math.round((completedItems / totalItems) * 100)
}

const goBack = () => {
  router.get(`/course/${props.course.id}`)
}

const formatDate = (date) => {
  if (!date) return 'Not started'
  return new Date(date).toLocaleDateString()
}
</script>

<template>
  <Head :title="`${course.title} - Learn`" />
  
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <!-- Header -->
    <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40">
      <div class="flex items-center justify-between px-4 py-3 lg:px-6">
        <div class="flex items-center gap-4">
          <button @click="goBack" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
            <ChevronLeft class="w-5 h-5" />
          </button>
          <div>
            <h1 class="font-bold text-lg lg:text-xl line-clamp-1">{{ course.title }}</h1>
            <div class="flex items-center gap-3 text-xs text-slate-500">
              <span class="flex items-center gap-1"><Users class="w-3 h-3" /> {{ course.students || 0 }} students</span>
              <span class="flex items-center gap-1"><Star class="w-3 h-3 fill-yellow-400 text-yellow-400" /> {{ course.rating || 4.8 }}</span>
            </div>
          </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="hidden md:flex items-center gap-3">
          <div class="w-48 bg-slate-200 dark:bg-slate-700 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full transition-all" :style="{ width: (progressPercentage || 0) + '%' }"></div>
          </div>
          <span class="text-sm font-medium">{{ progressPercentage || 0 }}% Complete</span>
        </div>
      </div>
    </header>

    <div class="flex flex-col lg:flex-row">
      <!-- Sidebar - Course Content -->
      <aside class="lg:w-96 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 lg:h-screen lg:overflow-y-auto">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
          <h2 class="font-bold text-lg">Course Content</h2>
          <p class="text-sm text-slate-500">{{ course.sections?.length || 0 }} sections • {{ course.lessons_count || 0 }} lectures</p>
        </div>
        
        <div class="divide-y divide-slate-200 dark:divide-slate-800">
          <div v-for="section in course.sections" :key="section.id" class="course-section">
            <!-- Section Header -->
            <button 
              @click="toggleSection(section.id)"
              class="w-full flex justify-between items-center p-4 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
            >
              <div class="text-left">
                <span class="font-semibold">{{ section.title }}</span>
                <div class="flex items-center gap-2 text-xs text-slate-500 mt-1">
                  <span>{{ section.lessons?.length || 0 }} lessons</span>
                  <span v-if="section.quizzes?.length">• {{ section.quizzes.length }} quizzes</span>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="w-16 h-1.5 bg-slate-200 rounded-full">
                  <div class="h-full bg-blue-600 rounded-full" :style="{ width: calculateSectionProgress(section) + '%' }"></div>
                </div>
                <ChevronDown class="w-5 h-5 text-slate-400 transition-transform" :class="{ 'rotate-180': expandedSections.includes(section.id) }" />
              </div>
            </button>
            
            <!-- Section Content -->
            <div v-show="expandedSections.includes(section.id)" class="border-t border-slate-100 dark:border-slate-800">
              <!-- Lessons -->
              <div v-for="lesson in section.lessons" :key="lesson.id">
                <button 
                  @click="selectLesson(lesson)"
                  :class="[
                    'w-full flex items-center justify-between p-4 pl-8 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors',
                    activeLesson?.id === lesson.id ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-l-blue-600' : ''
                  ]"
                >
                  <div class="flex items-center gap-3">
                    <Video class="w-4 h-4 text-slate-400" />
                    <span class="text-sm text-left">{{ lesson.title }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">{{ formatDuration(lesson.duration_minutes) }}</span>
                    <CheckCircle v-if="isLessonCompleted(lesson.id)" class="w-4 h-4 text-green-500" />
                  </div>
                </button>
              </div>
              
              <!-- Quizzes -->
              <div v-for="quiz in section.quizzes" :key="quiz.id">
                <button 
                  @click="openQuiz(quiz)"
                  class="w-full flex items-center justify-between p-4 pl-8 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                >
                  <div class="flex items-center gap-3">
                    <Trophy class="w-4 h-4 text-purple-500" />
                    <span class="text-sm text-left">Quiz: {{ quiz.title }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">{{ quiz.questions?.length || 0 }} questions</span>
                    <CheckCircle v-if="isQuizPassed(quiz.id)" class="w-4 h-4 text-green-500" />
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- Main Content - Video Player -->
      <main class="flex-1">
        <div v-if="activeLesson" class="p-4 lg:p-6">
          <!-- Video Player -->
          <div class="bg-black rounded-xl overflow-hidden aspect-video mb-6">
            <video 
              v-if="activeLesson.video_url"
              :key="activeLesson.id"
              controls
              class="w-full h-full"
              :poster="course.image"
            >
              <source :src="activeLesson.video_url" type="video/mp4" />
              Your browser does not support the video tag.
            </video>
            <div v-else class="w-full h-full bg-slate-800 flex items-center justify-center">
              <div class="text-center">
                <Video class="w-16 h-16 text-slate-600 mx-auto mb-3" />
                <p class="text-slate-400">No video available for this lesson</p>
              </div>
            </div>
          </div>

          <!-- Lesson Info -->
          <div class="mb-6">
            <h2 class="text-2xl font-bold mb-2">{{ activeLesson.title }}</h2>
            <div class="flex items-center gap-4 text-sm text-slate-500">
              <span class="flex items-center gap-1"><Clock class="w-4 h-4" /> {{ formatDuration(activeLesson.duration_minutes) }}</span>
              <span v-if="activeLesson.is_free" class="text-green-600 font-medium">Free Preview</span>
            </div>
          </div>

          <!-- Lesson Content -->
          <div class="prose dark:prose-invert max-w-none mb-8">
            <div v-html="activeLesson.content"></div>
            <p v-if="!activeLesson.content" class="text-slate-500">No content available for this lesson.</p>
          </div>

          <!-- Mark Complete Button -->
          <div class="flex justify-between items-center pt-6 border-t">
            <button 
              v-if="!isLessonCompleted(activeLesson.id)"
              @click="markLessonComplete(activeLesson)"
              class="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors flex items-center gap-2"
            >
              <CheckCircle class="w-5 h-5" />
              Mark as Complete
            </button>
            <div v-else class="text-green-600 flex items-center gap-2">
              <CheckCircle class="w-5 h-5" />
              <span class="font-medium">Completed</span>
            </div>
          </div>
        </div>

        <!-- No Lesson Selected State -->
        <div v-else class="flex items-center justify-center h-full min-h-[400px] p-6">
          <div class="text-center">
            <BookOpen class="w-20 h-20 text-slate-300 mx-auto mb-4" />
            <h3 class="text-xl font-bold mb-2">Select a lesson to begin</h3>
            <p class="text-slate-500">Choose a lesson from the sidebar to start learning</p>
          </div>
        </div>
      </main>
    </div>

    <!-- Quiz Modal -->
    <div v-if="showQuizModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 flex justify-between items-center">
          <div>
            <h3 class="text-xl font-bold text-white">{{ selectedQuiz?.title }}</h3>
            <p class="text-purple-100 text-sm">{{ selectedQuiz?.questions?.length || 0 }} questions</p>
          </div>
          <button @click="closeQuizModal" class="text-white hover:bg-white/20 p-1 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6">
          <div class="mb-6 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
            <div class="flex items-center gap-4 text-sm">
              <span class="flex items-center gap-1"><Clock class="w-4 h-4" /> Time limit: {{ selectedQuiz?.time_limit_minutes || 'No' }} min</span>
              <span class="flex items-center gap-1"><Trophy class="w-4 h-4" /> Passing score: {{ selectedQuiz?.passing_score || 70 }}%</span>
              <span class="flex items-center gap-1"><Award class="w-4 h-4" /> Max attempts: {{ selectedQuiz?.max_attempts || 3 }}</span>
            </div>
          </div>
          
          <p class="text-slate-600 dark:text-slate-300 mb-6">{{ selectedQuiz?.description || 'Test your knowledge with this quiz.' }}</p>
          
          <Link 
            :href="`/courses/${course.id}/quizzes/${selectedQuiz?.id}`"
            class="block w-full py-3 bg-purple-600 text-white text-center rounded-lg font-semibold hover:bg-purple-700 transition-colors"
          >
            Start Quiz
          </Link>
          
          <button @click="closeQuizModal" class="block w-full mt-3 py-3 border rounded-lg text-center hover:bg-slate-50 transition-colors">
            Close
          </button>
        </div>
      </div>
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

.course-section {
  transition: all 0.2s ease;
}
</style>