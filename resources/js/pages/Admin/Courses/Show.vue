<script setup>
import { Link, router, usePage, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

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

/* ---------------- FORM INITIALIZATION ---------------- */
const quizForm = useForm({
    title: '',
    time_limit_minutes: 30,
    passing_score: 80,
    description: '',
})

/* ---------------- UPDATE PROGRESS ---------------- */
const updateProgress = (lesson, status) => {
  if (!lesson?.id) return

  router.post(route('admin.progress.update', lesson.id), {
    status,
    progress_percentage: status === 'completed' ? 100 : 0
  }, {
    preserveScroll: true,
  })
}

const toggleComplete = (lesson, e) => {
  const checked = e.target.checked
  updateProgress(lesson, checked ? 'completed' : 'in_progress')
}

const markCompleted = (lesson) => {
  if (progressMap.value[lesson.id]?.status !== 'completed') {
    updateProgress(lesson, 'completed')
  }
}

/* ---------------- COURSE PROGRESS CALCULATION ---------------- */
const courseProgress = computed(() => {
  let totalLessons = 0
  let completedLessons = 0

  props.course?.sections?.forEach(section => {
    section.lessons?.forEach(lesson => {
      totalLessons++
      if (progressMap.value[lesson.id]?.status === 'completed') {
        completedLessons++
      }
    })
  })

  return totalLessons ? Math.round((completedLessons / totalLessons) * 100) : 0
})

/* ---------------- HELPERS ---------------- */
const formatBytes = (bytes, decimals = 2) => {
  if (!bytes) return '0 Bytes'
  const k = 1024
  const dm = decimals < 0 ? 0 : decimals
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
}

const getStorageUrl = (path) => {
    if (!path) return '';
    const sanitizedPath = path.replace(/^\//, '');
    return `/storage/${sanitizedPath}`;
}

/* ---------------- QUIZ MANAGEMENT ---------------- */
const selectedSectionForQuiz = ref(null) 
const showQuizModal = ref(false)

const openQuizModal = (section) => {
    selectedSectionForQuiz.value = section
    showQuizModal.value = true
}

const submitQuiz = () => {
    if (!selectedSectionForQuiz.value) return;

    quizForm.post(route('admin.sections.quizzes.store', { 
        section: selectedSectionForQuiz.value.id 
    }), {
        preserveScroll: true,
        onSuccess: () => {
            showQuizModal.value = false;
            quizForm.reset();
            selectedSectionForQuiz.value = null;
        }
    });
}
</script>

<template>
  <div class="min-h-screen bg-[#0f0a24] text-white p-6 font-sans">
    
    <div class="mb-8 flex justify-between items-center">
      <Link :href="route('admin.courses.index')" class="text-sm text-purple-400 hover:text-purple-300 flex items-center gap-2">
        ← Back to Courses
      </Link>
      <div v-if="course?.category" class="bg-purple-500/10 text-purple-400 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
        {{ course.category.name }}
      </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8 mb-10">
      <div class="lg:col-span-2">
        <h1 class="text-4xl font-extrabold mb-4 bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
          {{ course?.title }}
        </h1>
        <p class="text-gray-400 text-lg leading-relaxed">
          {{ course?.description }}
        </p>
        
        <div class="mt-8 rounded-3xl overflow-hidden border border-white/10 shadow-2xl bg-black aspect-video flex items-center justify-center">
           <video v-if="course?.video_url" :src="getStorageUrl(course.video_url)" controls class="w-full h-full" />
           <img v-else-if="course?.image" :src="getStorageUrl(course.image)" class="w-full h-full object-cover" />
           <div v-else class="text-slate-600 italic">No media available</div>
        </div>
      </div>

      <div class="bg-[#16103a] p-6 rounded-3xl border border-white/5 h-fit lg:sticky lg:top-6">
        <h2 class="text-xl font-bold mb-4">Course Progress</h2>
        <div class="flex items-end justify-between mb-2">
          <span class="text-3xl font-mono text-green-400">{{ courseProgress }}%</span>
          <span class="text-gray-400 text-sm">Completed</span>
        </div>
        <div class="w-full bg-white/5 h-3 rounded-full overflow-hidden mb-6">
          <div class="bg-gradient-to-r from-green-500 to-emerald-400 h-full transition-all duration-1000" :style="{ width: courseProgress + '%' }"></div>
        </div>

        <div v-if="courseProgress === 100" class="p-4 bg-green-500/10 border border-green-500/20 rounded-2xl text-center">
          <p class="text-green-400 text-sm font-medium mb-3">Course Completed!</p>
          <Link :href="route('admin.certificates.show', course.id)" class="block w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-green-500/20">
            🎓 Claim Certificate
          </Link>
        </div>
      </div>
    </div>

    <div class="max-w-4xl">
      <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
        <span class="w-2 h-8 bg-purple-600 rounded-full"></span>
        Curriculum
      </h2>

      <div v-if="course?.sections?.length" class="space-y-6">
        <div v-for="(section, sIndex) in course.sections" :key="section.id" class="bg-[#16103a]/50 rounded-2xl border border-white/5 overflow-hidden">
          
          <div class="bg-white/5 px-6 py-4 border-b border-white/5 flex justify-between items-center">
            <h3 class="font-bold text-purple-300">
              Section {{ sIndex + 1 }}: {{ section.title }}
            </h3>
            <button @click="openQuizModal(section)" type="button" class="text-[10px] uppercase tracking-widest bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded-lg font-black transition text-white">
                + Add Quiz
            </button>
          </div>

          <div class="divide-y divide-white/5">
            <div v-for="lesson in section.lessons" :key="lesson.id" class="p-6 hover:bg-white/[0.02] transition">
              
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-1">
                    <span class="text-xs font-mono text-gray-500">L{{ lesson.order_position }}</span>
                    <h4 class="font-semibold text-gray-100">{{ lesson.title }}</h4>
                  </div>
                  <p class="text-sm text-gray-400 line-clamp-1">{{ lesson.content || 'No content provided.' }}</p>
                </div>

                <div class="flex items-center gap-4">
                  <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" :checked="progressMap[lesson.id]?.status === 'completed'" @change="e => toggleComplete(lesson, e)"
                      class="w-5 h-5 rounded border-white/10 bg-white/5 text-purple-600 focus:ring-purple-500 focus:ring-offset-[#0f0a24]" />
                    <span class="text-xs text-gray-400 group-hover:text-gray-200 transition">Completed</span>
                  </label>
                  <Link :href="route('admin.lessons.show', lesson.id)" class="px-4 py-2 bg-white/5 hover:bg-white/10 rounded-lg text-sm font-medium transition">
                    Open
                  </Link>
                </div>
              </div>

              <div v-if="lesson.video_url" class="mt-4 rounded-xl overflow-hidden border border-white/5 bg-black max-w-2xl">
                <video :src="getStorageUrl(lesson.video_url)" controls @ended="markCompleted(lesson)" class="w-full aspect-video" />
              </div>

              <div v-if="lesson.resources?.length" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div v-for="res in lesson.resources" :key="res.id" class="flex items-center justify-between bg-[#0f0a24] p-3 rounded-xl border border-white/5">
                  <div class="flex items-center gap-3 overflow-hidden">
                    <span class="text-lg">{{ res.file_type === 'pdf' ? '📕' : '📄' }}</span>
                    <div class="overflow-hidden">
                      <p class="text-[11px] font-medium text-gray-200 truncate">{{ res.title }}</p>
                      <p class="text-[9px] text-gray-500 uppercase">{{ formatBytes(res.file_size) }}</p>
                    </div>
                  </div>
                  <a :href="getStorageUrl(res.file_path)" target="_blank" class="text-[10px] text-blue-400 font-bold ml-2">Download</a>
                </div>
              </div>
            </div>

            <div v-if="section.quizzes?.length" class="p-4 bg-indigo-600/5 space-y-2">
              <div v-for="quiz in section.quizzes" :key="quiz.id" 
                   class="flex items-center gap-3 p-4 bg-white/5 rounded-2xl border border-white/5 hover:border-indigo-500/30 transition">
                  <div class="p-2 bg-amber-500/20 rounded-xl text-amber-500">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  </div>
                  <div class="flex-1">
                      <p class="text-sm font-bold text-white">{{ quiz.title }}</p>
                      <p class="text-[10px] text-slate-500 uppercase">{{ quiz.time_limit_minutes }} Mins • Pass: {{ quiz.passing_score }}%</p>
                  </div>
                  <Link :href="route('admin.quizzes.show', quiz.id)" class="text-xs bg-indigo-600/20 hover:bg-indigo-600 px-3 py-1.5 rounded-lg transition text-white">
                    Manage
                  </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-20 bg-[#16103a]/30 rounded-3xl border border-dashed border-white/10">
        <p class="text-gray-500 italic">No curriculum items found.</p>
      </div>
    </div>

    <div v-if="showQuizModal" class="fixed inset-0 bg-[#09091a]/95 backdrop-blur-md z-[100] flex items-center justify-center p-4">
        <div v-if="selectedSectionForQuiz" class="bg-[#161937] border border-white/10 w-full max-w-md rounded-[2.5rem] p-10 shadow-2xl">
            <h2 class="text-2xl font-bold mb-6 text-white text-center">New Quiz for {{ selectedSectionForQuiz.title }}</h2>
            
            <form @submit.prevent="submitQuiz" class="space-y-5">
                <div>
                    <label class="block text-[10px] uppercase tracking-widest font-black text-slate-500 mb-2 ml-1">Quiz Title</label>
                    <input v-model="quizForm.title" type="text" placeholder="e.g. Chapter 1 Quiz" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 focus:border-indigo-500 outline-none transition text-white" required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] uppercase tracking-widest font-black text-slate-500 mb-2 ml-1">Limit (Mins)</label>
                        <input v-model="quizForm.time_limit_minutes" type="number" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 focus:border-indigo-500 outline-none transition text-white" />
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase tracking-widest font-black text-slate-500 mb-2 ml-1">Pass Score %</label>
                        <input v-model="quizForm.passing_score" type="number" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 focus:border-indigo-500 outline-none transition text-white" />
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="showQuizModal = false" class="flex-1 py-4 font-bold text-slate-400 hover:text-white transition">Cancel</button>
                    <button type="submit" :disabled="quizForm.processing" class="flex-1 bg-indigo-600 hover:bg-indigo-700 py-4 rounded-2xl font-bold shadow-lg shadow-indigo-500/20 transition text-white">
                        {{ quizForm.processing ? 'Saving...' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
  </div>
</template>

<style scoped>
::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: #0f0a24; }
::-webkit-scrollbar-thumb { background: #2d245a; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #3b2f75; }
</style>