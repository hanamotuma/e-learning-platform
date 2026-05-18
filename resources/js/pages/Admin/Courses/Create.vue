<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ChevronLeft, Upload, Save, BookOpen, DollarSign, Award, Video, Plus, Trash2, Play } from 'lucide-vue-next'

const props = defineProps({
  categories: Array,
  instructors: Array
})

const form = useForm({
  title: '',
  description: '',
  what_you_will_learn: '',
  requirements: '',
  price: '',
  category_id: '',
  difficulty_level: 'beginner',
  image: null,
  instructor_id: '',
})

const imagePreview = ref(null)
const sections = ref([])
const uploadingVideo = ref(false)
const uploadProgress = ref({})

const onImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.image = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Section Management
const addSection = () => {
  sections.value.push({
    id: 'temp_' + Date.now(),
    title: 'New Section',
    description: '',
    lessons: []
  })
}

const removeSection = (index) => {
  sections.value.splice(index, 1)
}

// Lesson Management
const addLesson = (sectionIndex) => {
  sections.value[sectionIndex].lessons.push({
    id: 'temp_' + Date.now(),
    title: 'New Lesson',
    content: '',
    video_file: null,
    video_url: '',
    video_preview: null,
    duration_minutes: 0,
    is_free: false,
    uploading: false,
    uploadProgress: 0
  })
}

const removeLesson = (sectionIndex, lessonIndex) => {
  sections.value[sectionIndex].lessons.splice(lessonIndex, 1)
}

const onVideoSelect = (sectionIndex, lessonIndex, event) => {
  const file = event.target.files[0]
  if (!file) return
  
  const lesson = sections.value[sectionIndex].lessons[lessonIndex]
  lesson.video_file = file
  lesson.video_preview = URL.createObjectURL(file)
}

const uploadVideoToServer = async (sectionIndex, lessonIndex) => {
  const lesson = sections.value[sectionIndex].lessons[lessonIndex]
  if (!lesson.video_file) return
  
  lesson.uploading = true
  const formData = new FormData()
  formData.append('video', lesson.video_file)
  formData.append('title', lesson.title)
  formData.append('content', lesson.content || '')
  formData.append('duration_minutes', lesson.duration_minutes || 0)
  formData.append('is_free', lesson.is_free ? 1 : 0)
  
  try {
    // Store video temporarily - will be linked after course creation
    const response = await axios.post('/admin/temp-upload-video', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (progressEvent) => {
        const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        lesson.uploadProgress = percent
      }
    })
    
    lesson.temp_video_path = response.data.path
    lesson.uploading = false
    alert('Video uploaded successfully!')
  } catch (error) {
    console.error('Upload error:', error)
    lesson.uploading = false
    alert('Error uploading video. File may be too large.')
  }
}

const removeVideo = (sectionIndex, lessonIndex) => {
  const lesson = sections.value[sectionIndex].lessons[lessonIndex]
  lesson.video_file = null
  lesson.video_preview = null
  lesson.temp_video_path = null
  lesson.video_url = ''
}

const submit = () => {
  // Add sections and lessons data to form
  form.sections = sections.value
  
  form.post('/admin/courses', {
    onSuccess: () => {
      // Set flag to trigger refresh on index page
      sessionStorage.setItem('course_created', 'true')
      router.get('/admin/courses')
    },
    onError: (errors) => {
      console.error('Form errors:', errors)
      alert('Please fix the errors: ' + JSON.stringify(errors))
    }
  })
}

const goBack = () => {
  router.get('/admin/courses')
}
</script>

<template>
  <Head title="Create Course | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <button @click="goBack" class="flex items-center gap-2 text-slate-600 mb-6 hover:text-blue-600">
        <ChevronLeft class="w-5 h-5" />
        Back to Courses
      </button>
      
      <div class="max-w-5xl mx-auto">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8">
            <h1 class="text-2xl font-bold text-white">Create New Course</h1>
            <p class="text-blue-100 mt-1">Fill in the details to create a new course with video lessons</p>
          </div>
          
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- Basic Information -->
            <div>
              <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <BookOpen class="w-5 h-5 text-blue-600" />
                Basic Information
              </h2>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Course Title *</label>
                  <input type="text" v-model="form.title" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900 focus:ring-2 focus:ring-blue-500" required />
                  <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
                </div>
                
                <div>
                  <label class="block text-sm font-medium mb-1">Description *</label>
                  <textarea v-model="form.description" rows="4" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" required></textarea>
                  <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
                </div>
              </div>
            </div>
            
            <!-- Pricing & Category -->
            <div class="border-t pt-6">
              <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <DollarSign class="w-5 h-5 text-green-600" />
                Pricing & Category
              </h2>
              
              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Price (ETB) *</label>
                  <input type="number" v-model="form.price" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Category *</label>
                  <select v-model="form.category_id" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900">
                    <option value="">Select Category</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Difficulty Level</label>
                  <select v-model="form.difficulty_level" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900">
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                  </select>
                </div>
                
              </div>
            </div>
            
            <!-- Learning Outcomes -->
            <div class="border-t pt-6">
              <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <Award class="w-5 h-5 text-purple-600" />
                Learning Outcomes
              </h2>
              
              <div>
                <label class="block text-sm font-medium mb-1">What Students Will Learn</label>
                <textarea v-model="form.what_you_will_learn" rows="3" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" placeholder="One per line"></textarea>
              </div>
              
              <div class="mt-4">
                <label class="block text-sm font-medium mb-1">Requirements / Prerequisites</label>
                <textarea v-model="form.requirements" rows="2" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" placeholder="One per line"></textarea>
              </div>
            </div>
            
            <!-- Course Thumbnail -->
            <div class="border-t pt-6">
              <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <Upload class="w-5 h-5 text-orange-600" />
                Course Thumbnail
              </h2>
              
              <div class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer hover:border-blue-500 transition-all" @click="$refs.imageInput.click()">
                <div v-if="imagePreview" class="mb-2">
                  <img :src="imagePreview" class="h-40 mx-auto rounded-lg object-cover shadow-md" />
                  <p class="text-sm text-green-600 mt-2">✓ Image selected</p>
                  <button @click.stop="imagePreview = null; form.image = null" class="text-xs text-red-600 mt-1">Remove</button>
                </div>
                <Upload v-else class="w-12 h-12 mx-auto text-slate-400 mb-3" />
                <p class="text-sm text-slate-500">Click to upload course thumbnail</p>
                <p class="text-xs text-slate-400 mt-1">Recommended: 1280x720px, JPG or PNG (Max 5MB)</p>
              </div>
              <input type="file" ref="imageInput" @change="onImageChange" accept="image/*" class="hidden" />
            </div>
            
            <!-- Course Content with Video Upload -->
            <div class="border-t pt-6">
              <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold flex items-center gap-2">
                  <Video class="w-5 h-5 text-red-600" />
                  Course Content (Sections & Video Lessons)
                </h2>
                <button type="button" @click="addSection" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                  <Plus class="w-4 h-4" />
                  Add Section
                </button>
              </div>
              
              <div v-if="sections.length === 0" class="text-center py-12 border-2 border-dashed rounded-xl">
                <Video class="w-16 h-16 text-slate-300 mx-auto mb-3" />
                <p class="text-slate-500">No sections yet. Click "Add Section" to start building your course.</p>
              </div>
              
              <div v-else class="space-y-4">
                <div v-for="(section, sIndex) in sections" :key="section.id" class="border rounded-xl overflow-hidden">
                  <!-- Section Header -->
                  <div class="bg-slate-50 dark:bg-slate-700/30 p-4">
                    <div class="flex justify-between items-start gap-4">
                      <div class="flex-1">
                        <input v-model="section.title" type="text" class="text-lg font-bold bg-transparent border-0 focus:ring-0 w-full" placeholder="Section Title" />
                        <textarea v-model="section.description" class="text-sm bg-transparent border-0 focus:ring-0 w-full mt-1" rows="1" placeholder="Section description (optional)"></textarea>
                      </div>
                      <button type="button" @click="removeSection(sIndex)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                  
                  <!-- Lessons -->
                  <div class="p-4 space-y-3">
                    <div v-for="(lesson, lIndex) in section.lessons" :key="lesson.id" class="border rounded-lg p-4">
                      <div class="flex justify-between items-start mb-3">
                        <input v-model="lesson.title" type="text" class="flex-1 font-bold bg-transparent border-0 focus:ring-0" placeholder="Lesson Title" />
                        <button type="button" @click="removeLesson(sIndex, lIndex)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                          <Trash2 class="w-4 h-4" />
                        </button>
                      </div>
                      
                      <!-- Video Upload Section -->
                      <div class="mb-3">
                        <label class="block text-sm font-medium mb-2">Lesson Video</label>
                        
                        <!-- Video Preview -->
                        <div v-if="lesson.video_preview || lesson.video_url" class="mb-3">
                          <video :src="lesson.video_preview || lesson.video_url" class="w-full max-h-48 rounded-lg" controls></video>
                          <button type="button" @click="removeVideo(sIndex, lIndex)" class="text-xs text-red-600 mt-1">Remove video</button>
                        </div>
                        
                        <!-- Video Upload Input -->
                        <div class="border-2 border-dashed rounded-lg p-4 text-center cursor-pointer hover:border-blue-500 transition-all" @click="$refs['videoInput_' + sIndex + '_' + lIndex][0].click()">
                          <Play v-if="!lesson.video_file && !lesson.video_url" class="w-8 h-8 mx-auto text-slate-400 mb-2" />
                          <Upload v-else class="w-8 h-8 mx-auto text-green-500 mb-2" />
                          <p class="text-sm text-slate-500">{{ lesson.video_file ? lesson.video_file.name : 'Click to upload video (MP4, MOV, AVI)' }}</p>
                          <p class="text-xs text-slate-400">Max file size: 200MB</p>
                        </div>
                        <input :ref="'videoInput_' + sIndex + '_' + lIndex" type="file" accept="video/mp4,video/mov,video/avi,video/mkv" class="hidden" @change="(e) => onVideoSelect(sIndex, lIndex, e)" />
                        
                        <!-- Upload Progress -->
                        <div v-if="lesson.uploading" class="mt-2">
                          <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" :style="{ width: lesson.uploadProgress + '%' }"></div>
                          </div>
                          <p class="text-xs text-slate-500 mt-1">Uploading: {{ lesson.uploadProgress }}%</p>
                        </div>
                        
                        <!-- Upload Button -->
                        <button v-if="lesson.video_file && !lesson.uploading" type="button" @click="uploadVideoToServer(sIndex, lIndex)" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                          Upload Video
                        </button>
                        
                        <!-- Or YouTube URL -->
                        <div class="mt-3">
                          <label class="block text-sm font-medium mb-1">Or use YouTube/Vimeo URL</label>
                          <input v-model="lesson.video_url" type="url" class="w-full px-4 py-2 border rounded-lg" placeholder="https://www.youtube.com/watch?v=..." />
                        </div>
                      </div>
                      
                      <!-- Lesson Content -->
                      <div>
                        <label class="block text-sm font-medium mb-1">Lesson Content / Transcript</label>
                        <textarea v-model="lesson.content" rows="3" class="w-full px-4 py-2 border rounded-lg" placeholder="Write the lesson content or transcript here..."></textarea>
                      </div>
                      
                      <div class="flex gap-4 mt-3">
                        <label class="flex items-center gap-2">
                          <input type="checkbox" v-model="lesson.is_free" />
                          <span class="text-sm">Free Preview Lesson</span>
                        </label>
                        <div>
                          <label class="text-sm">Duration (minutes)</label>
                          <input v-model="lesson.duration_minutes" type="number" class="w-24 px-2 py-1 border rounded ml-2" placeholder="10" />
                        </div>
                      </div>
                    </div>
                    
                    <!-- Add Lesson Button -->
                    <button type="button" @click="addLesson(sIndex)" class="w-full py-3 border-2 border-dashed rounded-lg text-slate-500 hover:text-blue-600 hover:border-blue-600 transition-all flex items-center justify-center gap-2">
                      <Plus class="w-4 h-4" />
                      Add Lesson to this Section
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="border-t pt-6 flex gap-4">
              <button type="submit" :disabled="form.processing" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                <Save class="w-4 h-4" />
                {{ form.processing ? 'Creating Course...' : 'Create Course' }}
              </button>
              <button type="button" @click="goBack" class="px-6 py-3 border rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>