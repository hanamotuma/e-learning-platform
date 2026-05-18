<script setup>
import { ref, onMounted } from 'vue'
import { Head, useForm, router, Link } from '@inertiajs/vue3'
import InstructorLayout from '@/layouts/InstructorLayout.vue'
import { ChevronLeft, Save, Upload, X, Plus, Trash2, Video, Play, Trophy, Edit2, Eye, CheckCircle } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
    course: Object,
    categories: Array
})

const activeTab = ref('content')
const showQuizModal = ref(false)
const editingQuiz = ref(null)
const quizzes = ref(props.course.quizzes || [])

const form = useForm({
    title: props.course.title,
    description: props.course.description,
    what_you_will_learn: props.course.what_you_will_learn,
    requirements: props.course.requirements,
    price: props.course.price,
    category_id: props.course.category_id,
    difficulty_level: props.course.difficulty_level,
    image: null,
})

const quizForm = ref({
    title: '',
    description: '',
    time_limit_minutes: '',
    passing_score: 70,
    max_attempts: 3,
    is_published: false
})

const imagePreview = ref(props.course.image || null)
const sections = ref(props.course.sections || [])
const uploadingVideo = ref(false)
const uploadProgress = ref({})

// Quiz Functions
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
            const response = await axios.put(`/instructor/courses/${props.course.id}/quizzes/${editingQuiz.value.id}`, quizForm.value)
            if (response.data.success) {
                const index = quizzes.value.findIndex(q => q.id === editingQuiz.value.id)
                quizzes.value[index] = { ...editingQuiz.value, ...quizForm.value }
            }
        } else {
            const response = await axios.post(`/instructor/courses/${props.course.id}/quizzes`, quizForm.value)
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
        await axios.delete(`/instructor/courses/${props.course.id}/quizzes/${quizId}`)
        quizzes.value = quizzes.value.filter(q => q.id !== quizId)
        alert('Quiz deleted')
    }
}

const addQuestionToQuiz = (quizId) => {
    router.get(`/instructor/courses/${props.course.id}/quizzes/${quizId}/edit`)
}

// Section & Lesson Functions
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
    form.put(`/instructor/courses/${props.course.id}`, {
        onSuccess: () => {
            alert('Course updated successfully')
        },
        onError: (errors) => {
            alert('Error: ' + JSON.stringify(errors))
        }
    })
}

const goBack = () => {
    router.get('/instructor/courses')
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>

<template>
    <Head :title="`Edit Course - ${course.title}`" />
    
    <InstructorLayout>
        <div class="p-6">
            <button @click="goBack" class="flex items-center gap-2 text-slate-600 mb-6 hover:text-blue-600">
                <ChevronLeft class="w-5 h-5" /> Back to Courses
            </button>

            <!-- Tabs -->
            <div class="border-b mb-6">
                <div class="flex gap-6">
                    <button @click="activeTab = 'content'" :class="['pb-3 font-medium transition-colors', activeTab === 'content' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-500']">
                        Course Content
                    </button>
                    <button @click="activeTab = 'quizzes'" :class="['pb-3 font-medium transition-colors', activeTab === 'quizzes' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-500']">
                        Quizzes ({{ quizzes.length }})
                    </button>
                    <button @click="activeTab = 'settings'" :class="['pb-3 font-medium transition-colors', activeTab === 'settings' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-500']">
                        Settings
                    </button>
                </div>
            </div>

            <!-- Course Content Tab -->
            <div v-if="activeTab === 'content'" class="max-w-5xl mx-auto">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6">
                        <h1 class="text-2xl font-bold text-white">Edit Course Content</h1>
                        <p class="text-blue-100 mt-1">Manage sections, lessons, and videos</p>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Course Content with Video -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-bold">Sections & Video Lessons</h2>
                                <button type="button" @click="addSection" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                                    <Plus class="w-4 h-4" /> Add Section
                                </button>
                            </div>

                            <div v-if="sections.length === 0" class="text-center py-12 border-2 border-dashed rounded-xl">
                                <Video class="w-16 h-16 text-slate-300 mx-auto mb-3" />
                                <p class="text-slate-500">No sections yet. Click "Add Section" to start building your course.</p>
                            </div>

                            <div v-else class="space-y-4">
                                <div v-for="(section, sIndex) in sections" :key="section.id" class="border rounded-xl overflow-hidden">
                                    <div class="bg-slate-50 dark:bg-slate-700/30 p-4">
                                        <div class="flex justify-between items-start gap-4">
                                            <div class="flex-1">
                                                <input v-model="section.title" type="text" class="text-lg font-bold bg-transparent border-0 w-full" placeholder="Section Title" />
                                            </div>
                                            <button type="button" @click="removeSection(sIndex)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="p-4 space-y-3">
                                        <div v-for="(lesson, lIndex) in section.lessons" :key="lesson.id" class="border rounded-lg p-4">
                                            <div class="flex justify-between items-start mb-3">
                                                <input v-model="lesson.title" type="text" class="flex-1 font-bold bg-transparent" placeholder="Lesson Title" />
                                                <button type="button" @click="removeLesson(sIndex, lIndex)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="block text-sm font-medium mb-2">Lesson Video</label>
                                                <div v-if="lesson.video_preview || lesson.video_url" class="mb-3">
                                                    <video :src="lesson.video_preview || lesson.video_url" class="w-full max-h-48 rounded-lg" controls></video>
                                                    <button type="button" @click="removeVideo(sIndex, lIndex)" class="text-xs text-red-600 mt-1">Remove video</button>
                                                </div>
                                                <div class="border-2 border-dashed rounded-lg p-4 text-center cursor-pointer" @click="$refs['videoInput_' + sIndex + '_' + lIndex][0].click()">
                                                    <Upload class="w-8 h-8 mx-auto text-slate-400 mb-2" />
                                                    <p class="text-sm text-slate-500">{{ lesson.video_file ? lesson.video_file.name : 'Click to upload video' }}</p>
                                                </div>
                                                <input :ref="'videoInput_' + sIndex + '_' + lIndex" type="file" accept="video/*" class="hidden" @change="(e) => onVideoSelect(sIndex, lIndex, e)" />
                                                
                                                <div v-if="lesson.uploading" class="mt-2">
                                                    <div class="w-full bg-slate-200 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full" :style="{ width: lesson.uploadProgress + '%' }"></div>
                                                    </div>
                                                </div>
                                                <button v-if="lesson.video_file && !lesson.uploading" type="button" @click="uploadVideoToServer(sIndex, lIndex)" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">
                                                    Upload Video
                                                </button>
                                                
                                                <div class="mt-3">
                                                    <label class="block text-sm font-medium mb-1">Or use YouTube/Vimeo URL</label>
                                                    <input v-model="lesson.video_url" type="url" class="w-full px-4 py-2 border rounded-lg" placeholder="https://www.youtube.com/watch?v=..." />
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Lesson Content</label>
                                                <textarea v-model="lesson.content" rows="3" class="w-full px-4 py-2 border rounded-lg"></textarea>
                                            </div>
                                            
                                            <div class="flex gap-4 mt-3">
                                                <label class="flex items-center gap-2">
                                                    <input type="checkbox" v-model="lesson.is_free" />
                                                    <span class="text-sm">Free Preview Lesson</span>
                                                </label>
                                                <div>
                                                    <label class="text-sm">Duration (minutes)</label>
                                                    <input v-model="lesson.duration_minutes" type="number" class="w-24 px-2 py-1 border rounded ml-2" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="button" @click="addLesson(sIndex)" class="w-full py-3 border-2 border-dashed rounded-lg text-slate-500 hover:text-blue-600 hover:border-blue-600 transition-all">
                                            <Plus class="w-4 h-4 inline mr-2" /> Add Lesson
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t pt-6 flex gap-4">
                            <button type="submit" :disabled="form.processing" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                                {{ form.processing ? 'Saving...' : 'Save Course Content' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quizzes Tab -->
            <div v-if="activeTab === 'quizzes'" class="max-w-5xl mx-auto">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-6 flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-white">Course Quizzes</h1>
                            <p class="text-purple-100 mt-1">Create and manage quizzes for your students</p>
                        </div>
                        <button @click="openCreateQuiz" class="px-5 py-2.5 bg-white text-purple-600 rounded-lg hover:bg-purple-50 flex items-center gap-2 font-semibold">
                            <Plus class="w-4 h-4" /> Create Quiz
                        </button>
                    </div>

                    <div v-if="quizzes.length === 0" class="p-12 text-center">
                        <Trophy class="w-20 h-20 text-slate-300 mx-auto mb-4" />
                        <p class="text-slate-500 mb-2">No quizzes created yet</p>
                        <button @click="openCreateQuiz" class="px-5 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Create Your First Quiz
                        </button>
                    </div>

                    <div v-else class="divide-y">
                        <div v-for="quiz in quizzes" :key="quiz.id" class="p-6 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-all">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <Trophy class="w-5 h-5 text-purple-600" />
                                        <h3 class="text-xl font-bold">{{ quiz.title }}</h3>
                                        <span :class="['px-2 py-1 text-xs rounded-full', quiz.is_published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700']">
                                            {{ quiz.is_published ? 'Published' : 'Draft' }}
                                        </span>
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
                                    <Link :href="`/instructor/courses/${course.id}/quizzes/${quiz.id}/edit`" class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg">
                                        <Edit2 class="w-4 h-4" />
                                    </Link>
                                    <button @click="editQuiz(quiz)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                        <Save class="w-4 h-4" />
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

            <!-- Settings Tab -->
            <div v-if="activeTab === 'settings'" class="max-w-5xl mx-auto">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6">
                        <h1 class="text-2xl font-bold text-white">Course Settings</h1>
                        <p class="text-blue-100 mt-1">Update course information and pricing</p>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <h2 class="text-lg font-bold mb-4">Basic Information</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Course Title</label>
                                    <input v-model="form.title" type="text" class="w-full px-4 py-2 border rounded-lg" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Description</label>
                                    <textarea v-model="form.description" rows="4" class="w-full px-4 py-2 border rounded-lg"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="border-t pt-6">
                            <h2 class="text-lg font-bold mb-4">Pricing & Category</h2>
                            <div class="grid md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Price (ETB)</label>
                                    <input v-model="form.price" type="number" class="w-full px-4 py-2 border rounded-lg" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Category</label>
                                    <select v-model="form.category_id" class="w-full px-4 py-2 border rounded-lg">
                                        <option value="">Select Category</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Difficulty Level</label>
                                    <select v-model="form.difficulty_level" class="w-full px-4 py-2 border rounded-lg">
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="border-t pt-6">
                            <h2 class="text-lg font-bold mb-4">Learning Outcomes</h2>
                            <div>
                                <label class="block text-sm font-medium mb-1">What Students Will Learn</label>
                                <textarea v-model="form.what_you_will_learn" rows="3" class="w-full px-4 py-2 border rounded-lg"></textarea>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Requirements</label>
                                <textarea v-model="form.requirements" rows="2" class="w-full px-4 py-2 border rounded-lg"></textarea>
                            </div>
                        </div>

                        <div class="border-t pt-6">
                            <h2 class="text-lg font-bold mb-4">Course Thumbnail</h2>
                            <div class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer" @click="$refs.imageInput.click()">
                                <div v-if="imagePreview" class="mb-2">
                                    <img :src="imagePreview" class="h-40 mx-auto rounded-lg object-cover" />
                                </div>
                                <Upload v-else class="w-12 h-12 mx-auto text-slate-400 mb-3" />
                                <p class="text-sm text-slate-500">Click to upload course thumbnail</p>
                            </div>
                            <input type="file" ref="imageInput" @change="onImageChange" accept="image/*" class="hidden" />
                        </div>

                        <div class="border-t pt-6 flex gap-4">
                            <button type="submit" :disabled="form.processing" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                                {{ form.processing ? 'Saving...' : 'Save Settings' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Create/Edit Quiz Modal -->
        <div v-if="showQuizModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white">{{ editingQuiz ? 'Edit Quiz' : 'Create Quiz' }}</h3>
                    <button @click="showQuizModal = false" class="text-white hover:bg-white/20 p-1 rounded-lg">
                        <X class="w-6 h-6" />
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

                    <p v-if="editingQuiz" class="text-xs text-slate-400 text-center">
                        After saving, click "Edit" again to add questions
                    </p>
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>