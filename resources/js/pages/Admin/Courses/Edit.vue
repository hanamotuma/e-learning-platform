<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps({
  course: Object,
})

/* ---------------- SECTION LOGIC ---------------- */
const sectionForm = useForm({ title: '' })

const addSection = () => {
  sectionForm.post(route('admin.sections.store', props.course.id), {
    preserveScroll: true,
    onSuccess: () => sectionForm.reset()
  })
}

const deleteSection = (id) => {
  if (confirm('Delete this section and all its lessons?')) {
    router.delete(route('admin.sections.destroy', id), { preserveScroll: true })
  }
}

/* ---------------- LESSON LOGIC ---------------- */
const lessonForms = reactive({})

const getLessonForm = (sectionId) => {
  if (!lessonForms[sectionId]) {
    lessonForms[sectionId] = useForm({
      title: '',
      video: null,
      content: 'No content yet' // Matching your LessonController default
    })
  }
  return lessonForms[sectionId]
}

const addLesson = (section) => {
  const form = getLessonForm(section.id)
  form.post(route('admin.lessons.store', section.id), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      // Manual reset for the file input if needed
    }
  })
}

const deleteLesson = (id) => {
  if (confirm('Delete this lesson?')) {
    router.delete(route('admin.lessons.destroy', id), { preserveScroll: true })
  }
}

/* ---------------- RESOURCE LOGIC ---------------- */
const resourceForms = reactive({})

const getResourceForm = (lessonId) => {
  if (!resourceForms[lessonId]) {
    resourceForms[lessonId] = useForm({
      title: '',
      file: null
    })
  }
  return resourceForms[lessonId]
}

const addResource = (lesson) => {
  const form = getResourceForm(lesson.id)
  form.post(route('admin.resources.store', lesson.id), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => form.reset()
  })
}

const deleteResource = (id) => {
  router.delete(route('admin.resources.destroy', id), { preserveScroll: true })
}
</script>

<template>
  <div class="min-h-screen bg-[#09091a] text-white p-8">
    <div class="max-w-5xl mx-auto">
      
      <div class="flex justify-between items-center mb-8 border-b border-white/10 pb-6">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-violet-400 to-fuchsia-400 bg-clip-text text-transparent">
            Course Builder: {{ course.title }}
          </h1>
          <p class="text-slate-400 text-sm">Manage curriculum, videos, and resources.</p>
        </div>
        <Link :href="route('admin.courses.index')" class="text-slate-400 hover:text-white transition">
          Back to List
        </Link>
      </div>

      <div class="bg-[#15193f]/80 border border-white/10 rounded-2xl p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4">Add New Section</h3>
        <div class="flex gap-3">
          <input 
            v-model="sectionForm.title" 
            placeholder="e.g. Introduction to Backend" 
            class="flex-1 bg-black/20 border border-white/10 rounded-xl px-4 py-2 outline-none focus:border-violet-500 transition"
          />
          <button 
            @click="addSection" 
            :disabled="sectionForm.processing"
            class="bg-violet-600 hover:bg-violet-700 disabled:opacity-50 px-6 py-2 rounded-xl font-bold transition"
          >
            {{ sectionForm.processing ? 'Adding...' : 'Add Section' }}
          </button>
        </div>
      </div>

      <div class="space-y-6">
        <div v-for="section in course.sections" :key="section.id" class="bg-[#15193f]/40 border border-white/5 rounded-3xl overflow-hidden">
          
          <div class="bg-white/5 px-6 py-4 flex justify-between items-center border-b border-white/5">
            <h2 class="text-xl font-bold text-violet-300 flex items-center gap-2">
              <span class="text-slate-500 text-sm">Section:</span> {{ section.title }}
            </h2>
            <button @click="deleteSection(section.id)" class="text-rose-400 hover:text-rose-300 text-sm font-medium">
              Remove Section
            </button>
          </div>

          <div class="p-6">
            <div class="bg-black/20 rounded-2xl p-4 mb-6 border border-white/5">
              <p class="text-xs font-bold text-slate-500 uppercase mb-3 tracking-widest">New Lesson</p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input 
                  v-model="getLessonForm(section.id).title" 
                  placeholder="Lesson Title" 
                  class="bg-[#09091a] border border-white/10 rounded-lg px-4 py-2 outline-none focus:border-blue-500"
                />
                <input 
                  type="file" 
                  @change="e => getLessonForm(section.id).video = e.target.files[0]"
                  class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-500/10 file:text-blue-400 hover:file:bg-blue-500/20"
                />
              </div>
              <button 
                @click="addLesson(section)" 
                :disabled="getLessonForm(section.id).processing"
                class="mt-4 w-full bg-blue-600 hover:bg-blue-700 py-2 rounded-xl font-bold transition disabled:opacity-50"
              >
                {{ getLessonForm(section.id).processing ? 'Uploading Video...' : 'Upload Lesson' }}
              </button>
            </div>

            <div class="space-y-4">
              <div v-for="lesson in section.lessons" :key="lesson.id" class="bg-white/5 border border-white/5 rounded-2xl p-5">
                
                <div class="flex justify-between items-start mb-4">
                  <div>
                    <h4 class="font-bold text-lg flex items-center gap-2">
                      <span class="bg-blue-500 w-2 h-2 rounded-full"></span>
                      {{ lesson.title }}
                    </h4>
                    <p class="text-slate-500 text-xs mt-1">ID: {{ lesson.id }} • Video: {{ lesson.video_url ? 'Attached' : 'None' }}</p>
                  </div>
                  <button @click="deleteLesson(lesson.id)" class="text-slate-500 hover:text-rose-400 transition text-sm">
                    Delete Lesson
                  </button>
                </div>

                <video v-if="lesson.video_url" :src="`/storage/${lesson.video_url}`" controls class="w-full max-w-md rounded-xl mb-4 bg-black" />

                <div class="border-t border-white/5 pt-4">
                  <h5 class="text-xs font-bold text-slate-400 mb-3">RESOURCES & DOWNLOADS</h5>
                  
                  <div class="flex flex-wrap gap-2 mb-4">
                    <div v-for="res in lesson.resources" :key="res.id" class="bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-lg flex items-center gap-2">
                      <span class="text-xs text-emerald-400">{{ res.title }}</span>
                      <button @click="deleteResource(res.id)" class="text-rose-400 hover:text-rose-300 text-xs font-bold">×</button>
                    </div>
                  </div>

                  <div class="flex flex-col sm:flex-row gap-2">
                    <input 
                      v-model="getResourceForm(lesson.id).title" 
                      placeholder="File Title (e.g. Source Code)" 
                      class="flex-1 bg-white/5 border border-white/10 rounded-lg px-3 py-1 text-sm"
                    />
                    <input 
                      type="file" 
                      @change="e => getResourceForm(lesson.id).file = e.target.files[0]"
                      class="text-xs text-slate-400 file:bg-white/10 file:text-white file:border-0 file:rounded-md file:px-2"
                    />
                    <button 
                      @click="addResource(lesson)" 
                      :disabled="getResourceForm(lesson.id).processing"
                      class="bg-emerald-600 hover:bg-emerald-700 px-4 py-1 rounded-lg text-sm font-bold transition disabled:opacity-50"
                    >
                      {{ getResourceForm(lesson.id).processing ? '...' : 'Add' }}
                    </button>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</template>