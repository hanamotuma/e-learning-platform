<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import InstructorLayout from '@/layouts/InstructorLayout.vue'
import { 
  PlusCircle, BookOpen, Users, DollarSign, Star, Eye, 
  X, Upload, Video, Plus, Trash2, ChevronLeft, Save
} from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
    stats: Object,
    courses: Array,
    recentEnrollments: Array,
    recentReviews: Array,
    monthlyEarnings: Array,
    categories: Array
})

const showCreateModal = ref(false)
const imagePreview = ref(null)
const sections = ref([])
const uploadingVideo = ref(false)
const uploadProgress = ref({})

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

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US').format(amount || 0) + ' ETB'
}

const openCreateModal = () => {
    showCreateModal.value = true
}

const closeCreateModal = () => {
    showCreateModal.value = false
    form.reset()
    imagePreview.value = null
    sections.value = []
}

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

const submitCourse = () => {
    form.sections = sections.value
    form.post('/instructor/courses', {
        onSuccess: () => {
            closeCreateModal()
            router.reload()
        },
        onError: (errors) => {
            alert('Error: ' + JSON.stringify(errors))
        }
    })
}
</script>

<template>
    <Head title="Instructor Dashboard | LearnHub" />
    
    <InstructorLayout>
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold dark:text-white">Instructor Dashboard</h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-1">Manage your courses and students</p>
                </div>
                <button @click="openCreateModal" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <PlusCircle class="w-4 h-4" />
                    New Course
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Total Students</p>
                    <p class="text-2xl font-bold">{{ stats?.total_students || 0 }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-600">{{ formatCurrency(stats?.total_revenue) }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Average Rating</p>
                    <p class="text-2xl font-bold text-yellow-500">{{ stats?.average_rating || 0 }} ★</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Pending Payout</p>
                    <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(stats?.pending_payout || 0) }}</p>
                </div>
            </div>

            <!-- Monthly Earnings Chart -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border p-6 mb-8">
                <h2 class="text-xl font-bold mb-4">Monthly Earnings</h2>
                <div class="flex items-end gap-4 h-64">
                    <div v-for="item in monthlyEarnings" :key="item.month" class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-blue-100 dark:bg-blue-900/30 rounded-t-lg transition-all" :style="{ height: (item.earnings / (monthlyEarnings[0]?.earnings || 1) * 200) + 'px' }"></div>
                        <p class="text-xs text-slate-500 mt-2">{{ item.month }}</p>
                        <p class="text-xs font-bold">{{ formatCurrency(item.earnings) }}</p>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Recent Enrollments -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold">Recent Enrollments</h2>
                    </div>
                    <div class="divide-y">
                        <div v-for="enrollment in recentEnrollments?.slice(0, 5)" :key="enrollment.id" class="p-4">
                            <p class="font-medium">{{ enrollment.user?.name }}</p>
                            <p class="text-sm text-slate-500">{{ enrollment.course?.title }}</p>
                            <p class="text-xs text-green-600 mt-1">+{{ formatCurrency(enrollment.amount_paid) }}</p>
                        </div>
                        <div v-if="!recentEnrollments?.length" class="p-8 text-center text-slate-500">
                            No recent enrollments
                        </div>
                    </div>
                </div>

                <!-- Recent Reviews -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold">Recent Reviews</h2>
                    </div>
                    <div class="divide-y">
                        <div v-for="review in recentReviews?.slice(0, 5)" :key="review.id" class="p-4">
                            <div class="flex text-yellow-400 text-sm mb-1">
                                <span v-for="i in review.rating" :key="i">★</span>
                                <span v-for="i in (5 - review.rating)" :key="i" class="text-slate-300">★</span>
                            </div>
                            <p class="text-sm">{{ review.review }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ review.course?.title }}</p>
                        </div>
                        <div v-if="!recentReviews?.length" class="p-8 text-center text-slate-500">
                            No reviews yet
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Course Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white">Create New Course</h2>
                    <button @click="closeCreateModal" class="text-white hover:bg-white/20 p-1 rounded-lg">
                        <X class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submitCourse" class="p-6 space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-bold mb-4 dark:text-white">Basic Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1 dark:text-white">Course Title *</label>
                                <input v-model="form.title" type="text" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1 dark:text-white">Description *</label>
                                <textarea v-model="form.description" rows="3" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Category -->
                    <div class="border-t pt-6 dark:border-slate-700">
                        <h3 class="text-lg font-bold mb-4 dark:text-white">Pricing & Category</h3>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1 dark:text-white">Price (ETB)</label>
                                <input v-model="form.price" type="number" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1 dark:text-white">Category</label>
                                <select v-model="form.category_id" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900">
                                    <option value="">Select Category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1 dark:text-white">Difficulty Level</label>
                                <select v-model="form.difficulty_level" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Learning Outcomes -->
                    <div class="border-t pt-6 dark:border-slate-700">
                        <h3 class="text-lg font-bold mb-4 dark:text-white">Learning Outcomes</h3>
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-white">What Students Will Learn</label>
                            <textarea v-model="form.what_you_will_learn" rows="2" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900"></textarea>
                        </div>
                        <div class="mt-3">
                            <label class="block text-sm font-medium mb-1 dark:text-white">Requirements</label>
                            <textarea v-model="form.requirements" rows="2" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900"></textarea>
                        </div>
                    </div>

                    <!-- Thumbnail -->
                    <div class="border-t pt-6 dark:border-slate-700">
                        <h3 class="text-lg font-bold mb-4 dark:text-white">Course Thumbnail</h3>
                        <div class="border-2 border-dashed rounded-xl p-4 text-center cursor-pointer hover:border-blue-500" @click="$refs.imageInput.click()">
                            <div v-if="imagePreview" class="mb-2">
                                <img :src="imagePreview" class="h-32 mx-auto rounded-lg object-cover" />
                            </div>
                            <Upload v-else class="w-10 h-10 mx-auto text-slate-400 mb-2" />
                            <p class="text-sm text-slate-500">Click to upload thumbnail</p>
                        </div>
                        <input type="file" ref="imageInput" @change="onImageChange" accept="image/*" class="hidden" />
                    </div>

                    <!-- Course Content -->
                    <div class="border-t pt-6 dark:border-slate-700">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold dark:text-white">Course Content</h3>
                            <button type="button" @click="addSection" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                                <Plus class="w-4 h-4 inline" /> Add Section
                            </button>
                        </div>

                        <div v-if="sections.length === 0" class="text-center py-8 border-2 border-dashed rounded-xl">
                            <Video class="w-12 h-12 text-slate-300 mx-auto mb-2" />
                            <p class="text-slate-500 text-sm">No sections yet</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="(section, sIndex) in sections" :key="section.id" class="border rounded-lg overflow-hidden">
                                <div class="bg-slate-50 dark:bg-slate-700/30 p-3 flex justify-between">
                                    <input v-model="section.title" class="flex-1 font-bold bg-transparent border-0" placeholder="Section Title" />
                                    <button type="button" @click="removeSection(sIndex)" class="text-red-500 text-sm">Remove</button>
                                </div>
                                <div class="p-3 space-y-2">
                                    <div v-for="(lesson, lIndex) in section.lessons" :key="lesson.id" class="border rounded-lg p-3">
                                        <input v-model="lesson.title" class="w-full font-medium mb-2 border-0 bg-transparent" placeholder="Lesson Title" />
                                        <div class="flex gap-2 mb-2">
                                            <input type="file" accept="video/*" @change="(e) => onVideoSelect(sIndex, lIndex, e)" class="text-xs" />
                                        </div>
                                        <div class="flex gap-2">
                                            <label class="flex items-center gap-1 text-xs">
                                                <input type="checkbox" v-model="lesson.is_free" /> Free
                                            </label>
                                            <input v-model="lesson.duration_minutes" type="number" placeholder="Minutes" class="w-20 px-2 py-1 border rounded text-sm" />
                                            <button type="button" @click="removeLesson(sIndex, lIndex)" class="text-red-500 text-xs">Remove</button>
                                        </div>
                                    </div>
                                    <button type="button" @click="addLesson(sIndex)" class="w-full py-2 text-sm border-2 border-dashed rounded-lg text-blue-600 hover:border-blue-600">
                                        <Plus class="w-3 h-3 inline" /> Add Lesson
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t">
                        <button type="submit" :disabled="form.processing" class="flex-1 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ form.processing ? 'Submitting...' : 'Submit for Review' }}
                        </button>
                        <button type="button" @click="closeCreateModal" class="px-6 py-2 border rounded-lg hover:bg-slate-50">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </InstructorLayout>
</template>