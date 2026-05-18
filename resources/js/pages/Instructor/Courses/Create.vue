<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import InstructorLayout from '@/layouts/InstructorLayout.vue'
import { ChevronLeft, Save, Upload, X, Plus, Trash2, Video, Play } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
    categories: Array
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
        reader.onload = (e) => { imagePreview.value = e.target.result }
        reader.readAsDataURL(file)
    }
}

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
    
    try {
        const response = await axios.post('/instructor/temp-upload-video', formData, {
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
        lesson.uploading = false
        alert('Error uploading video')
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
    form.sections = sections.value
    form.post('/instructor/courses', {
        onSuccess: () => {
            router.get('/instructor/courses')
        },
        onError: (errors) => {
            alert('Error: ' + JSON.stringify(errors))
        }
    })
}

const goBack = () => {
    router.get('/instructor/courses')
}
</script>

<template>
    <Head title="Create Course | Instructor" />
    
    <InstructorLayout>
        <div class="p-6">
            <div class="max-w-5xl mx-auto">
                <!-- Back Button -->
                <button @click="goBack" class="flex items-center gap-2 text-slate-600 dark:text-slate-400 mb-6 hover:text-blue-600 transition-colors">
                    <ChevronLeft class="w-5 h-5" />
                    Back to Courses
                </button>

                <!-- Form Container -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6">
                        <h1 class="text-2xl font-bold text-white">Create New Course</h1>
                        <p class="text-blue-100 mt-1">Fill in the details to create your course</p>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Basic Information -->
                        <div>
                            <h2 class="text-lg font-bold mb-4 text-slate-800 dark:text-white">Basic Information</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Course Title *</label>
                                    <input v-model="form.title" type="text" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Description *</label>
                                    <textarea v-model="form.description" rows="4" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white" required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing & Category -->
                        <div class="border-t pt-6 border-slate-200 dark:border-slate-700">
                            <h2 class="text-lg font-bold mb-4 text-slate-800 dark:text-white">Pricing & Category</h2>
                            <div class="grid md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Price (ETB)</label>
                                    <input v-model="form.price" type="number" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Category</label>
                                    <select v-model="form.category_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                                        <option value="">Select Category</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Difficulty Level</label>
                                    <select v-model="form.difficulty_level" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Learning Outcomes -->
                        <div class="border-t pt-6 border-slate-200 dark:border-slate-700">
                            <h2 class="text-lg font-bold mb-4 text-slate-800 dark:text-white">Learning Outcomes</h2>
                            <div>
                                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">What Students Will Learn</label>
                                <textarea v-model="form.what_you_will_learn" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white" placeholder="One per line"></textarea>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Requirements / Prerequisites</label>
                                <textarea v-model="form.requirements" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white" placeholder="One per line"></textarea>
                            </div>
                        </div>

                        <!-- Course Thumbnail -->
                        <div class="border-t pt-6 border-slate-200 dark:border-slate-700">
                            <h2 class="text-lg font-bold mb-4 text-slate-800 dark:text-white">Course Thumbnail</h2>
                            <div class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-6 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all" @click="$refs.imageInput.click()">
                                <div v-if="imagePreview" class="mb-2">
                                    <img :src="imagePreview" class="h-40 mx-auto rounded-lg object-cover" />
                                    <p class="text-sm text-green-600 mt-2">✓ Image selected</p>
                                </div>
                                <Upload v-else class="w-12 h-12 mx-auto text-slate-400 mb-3" />
                                <p class="text-sm text-slate-500">Click to upload course thumbnail</p>
                                <p class="text-xs text-slate-400 mt-1">Recommended: 1280x720px, JPG or PNG (Max 5MB)</p>
                            </div>
                            <input type="file" ref="imageInput" @change="onImageChange" accept="image/*" class="hidden" />
                        </div>

                        <!-- Course Content -->
                        <div class="border-t pt-6 border-slate-200 dark:border-slate-700">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-bold text-slate-800 dark:text-white">Course Content (Sections & Video Lessons)</h2>
                                <button type="button" @click="addSection" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 transition-all">
                                    <Plus class="w-4 h-4" />
                                    Add Section
                                </button>
                            </div>

                            <div v-if="sections.length === 0" class="text-center py-12 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl">
                                <Video class="w-16 h-16 text-slate-300 mx-auto mb-3" />
                                <p class="text-slate-500">No sections yet. Click "Add Section" to start building your course.</p>
                            </div>

                            <div v-else class="space-y-4">
                                <div v-for="(section, sIndex) in sections" :key="section.id" class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                    <div class="bg-slate-50 dark:bg-slate-700/30 p-4">
                                        <div class="flex justify-between items-start gap-4">
                                            <div class="flex-1">
                                                <input v-model="section.title" type="text" class="text-lg font-bold bg-transparent border-0 focus:ring-0 w-full dark:text-white" placeholder="Section Title" />
                                            </div>
                                            <button type="button" @click="removeSection(sIndex)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="p-4 space-y-3">
                                        <div v-for="(lesson, lIndex) in section.lessons" :key="lesson.id" class="border border-slate-200 dark:border-slate-700 rounded-lg p-4">
                                            <div class="flex justify-between items-start mb-3">
                                                <input v-model="lesson.title" type="text" class="flex-1 font-bold bg-transparent border-0 focus:ring-0 dark:text-white" placeholder="Lesson Title" />
                                                <button type="button" @click="removeLesson(sIndex, lIndex)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Lesson Video</label>
                                                <div v-if="lesson.video_preview || lesson.video_url" class="mb-3">
                                                    <video :src="lesson.video_preview || lesson.video_url" class="w-full max-h-48 rounded-lg" controls></video>
                                                    <button type="button" @click="removeVideo(sIndex, lIndex)" class="text-xs text-red-600 mt-1 hover:underline">Remove video</button>
                                                </div>
                                                <div class="border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg p-4 text-center cursor-pointer hover:border-blue-500 transition-all" @click="$refs['videoInput_' + sIndex + '_' + lIndex][0].click()">
                                                    <Upload class="w-8 h-8 mx-auto text-slate-400 mb-2" />
                                                    <p class="text-sm text-slate-500">{{ lesson.video_file ? lesson.video_file.name : 'Click to upload video (MP4, MOV, AVI)' }}</p>
                                                    <p class="text-xs text-slate-400">Max file size: 200MB</p>
                                                </div>
                                                <input :ref="'videoInput_' + sIndex + '_' + lIndex" type="file" accept="video/mp4,video/mov,video/avi,video/mkv" class="hidden" @change="(e) => onVideoSelect(sIndex, lIndex, e)" />
                                                
                                                <div v-if="lesson.uploading" class="mt-2">
                                                    <div class="w-full bg-slate-200 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full" :style="{ width: lesson.uploadProgress + '%' }"></div>
                                                    </div>
                                                    <p class="text-xs text-slate-500 mt-1">Uploading: {{ lesson.uploadProgress }}%</p>
                                                </div>
                                                
                                                <button v-if="lesson.video_file && !lesson.uploading" type="button" @click="uploadVideoToServer(sIndex, lIndex)" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-all">
                                                    Upload Video
                                                </button>
                                                
                                                <div class="mt-3">
                                                    <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Or use YouTube/Vimeo URL</label>
                                                    <input v-model="lesson.video_url" type="url" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white" placeholder="https://www.youtube.com/watch?v=..." />
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">Lesson Content / Transcript</label>
                                                <textarea v-model="lesson.content" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white" placeholder="Write the lesson content or transcript here..."></textarea>
                                            </div>
                                            
                                            <div class="flex gap-4 mt-3">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" v-model="lesson.is_free" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                                                    <span class="text-sm text-slate-700 dark:text-slate-300">Free Preview Lesson</span>
                                                </label>
                                                <div>
                                                    <label class="text-sm text-slate-700 dark:text-slate-300">Duration (minutes)</label>
                                                    <input v-model="lesson.duration_minutes" type="number" class="w-24 px-2 py-1 border border-slate-300 rounded-lg ml-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white" placeholder="10" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="button" @click="addLesson(sIndex)" class="w-full py-3 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg text-slate-500 hover:text-blue-600 hover:border-blue-600 transition-all flex items-center justify-center gap-2">
                                            <Plus class="w-4 h-4" />
                                            Add Lesson to this Section
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="border-t pt-6 border-slate-200 dark:border-slate-700 flex gap-4">
                            <button type="submit" :disabled="form.processing" class="flex-1 px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                                <Save class="w-4 h-4" />
                                {{ form.processing ? 'Creating Course...' : 'Create Course' }}
                            </button>
                            <button type="button" @click="goBack" class="px-6 py-3 border border-slate-300 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all font-medium text-slate-700 dark:text-slate-300">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>