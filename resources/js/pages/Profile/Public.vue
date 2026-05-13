<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { User, Mail, Phone, BookOpen, Award, Calendar, ChevronLeft, Star, Users, Clock, Heart, Target } from 'lucide-vue-next'

const props = defineProps({
    user: Object,
    enrolledCourses: Array,
    certificates: Array,
    wishlistCount: Number
})

const isDarkMode = ref(false)

const formatDate = (date) => {
    if (!date) return 'Not yet'
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}

const goBack = () => {
    router.get('/')
}

const goToCourse = (courseId) => {
    router.get(`/course/${courseId}`)
}

const goToEditProfile = () => {
    router.get('/profile/edit')
}

const goToDashboard = () => {
    router.get('/student/dashboard')
}

onMounted(() => {
    const savedTheme = localStorage.getItem('theme')
    if (savedTheme === 'dark') {
        isDarkMode.value = true
        document.documentElement.classList.add('dark')
    }
})

const userInitials = computed(() => {
    const name = props.user?.name || 'User'
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const totalCourses = computed(() => props.enrolledCourses?.length || 0)
const completedCourses = computed(() => props.enrolledCourses?.filter(c => c.progress_percentage >= 100).length || 0)
const inProgressCourses = computed(() => props.enrolledCourses?.filter(c => c.progress_percentage > 0 && c.progress_percentage < 100).length || 0)
</script>

<template>
    <Head :title="(user?.name || 'Profile') + ' | LearnHub'" />
    
    <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white">
            <div class="max-w-5xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <button @click="goBack" class="flex items-center gap-2 text-white/80 hover:text-white transition-colors">
                        <ChevronLeft class="w-5 h-5" />
                        Back to Home
                    </button>
                    <div class="flex items-center gap-3">
                        <button @click="goToDashboard" class="px-4 py-2 bg-white/20 rounded-xl hover:bg-white/30 transition-colors text-sm font-medium">
                            Dashboard
                        </button>
                        <button @click="goToEditProfile" class="px-4 py-2 bg-white/20 rounded-xl hover:bg-white/30 transition-colors text-sm font-medium">
                            Edit Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 py-8">
            <!-- Profile Header Card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border p-8 mb-8 text-center">
                <div class="relative inline-block">
                    <div class="w-28 h-28 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center mx-auto shadow-lg ring-4 ring-blue-100 dark:ring-blue-900/30">
                        <span class="text-4xl font-bold text-white">{{ userInitials }}</span>
                    </div>
                </div>
                <h1 class="text-2xl font-bold dark:text-white mt-4">{{ user?.name }}</h1>
                <p class="text-slate-500 dark:text-slate-400">Learning Enthusiast</p>
                <p class="text-sm text-slate-400 mt-2">Member since {{ formatDate(user?.created_at) }}</p>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-4 mb-8">
                <div class="bg-white dark:bg-slate-900 rounded-xl p-4 text-center border hover:shadow-md transition-all">
                    <BookOpen class="w-6 h-6 text-blue-600 mx-auto mb-2" />
                    <p class="text-2xl font-bold dark:text-white">{{ totalCourses }}</p>
                    <p class="text-xs text-slate-500">Enrolled</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl p-4 text-center border hover:shadow-md transition-all">
                    <CheckCircle class="w-6 h-6 text-green-600 mx-auto mb-2" />
                    <p class="text-2xl font-bold dark:text-white">{{ completedCourses }}</p>
                    <p class="text-xs text-slate-500">Completed</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl p-4 text-center border hover:shadow-md transition-all">
                    <Award class="w-6 h-6 text-purple-600 mx-auto mb-2" />
                    <p class="text-2xl font-bold dark:text-white">{{ certificates?.length || 0 }}</p>
                    <p class="text-xs text-slate-500">Certificates</p>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Personal Information Card -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border p-6">
                    <div class="flex items-center gap-2 mb-6 pb-3 border-b">
                        <User class="w-5 h-5 text-blue-600" />
                        <h2 class="text-lg font-bold dark:text-white">Personal Information</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <User class="w-5 h-5 text-slate-400 mt-0.5" />
                            <div>
                                <p class="text-xs text-slate-500">Full Name</p>
                                <p class="font-medium dark:text-white">{{ user?.name }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Mail class="w-5 h-5 text-slate-400 mt-0.5" />
                            <div>
                                <p class="text-xs text-slate-500">Email Address</p>
                                <p class="font-medium dark:text-white">{{ user?.email }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Phone class="w-5 h-5 text-slate-400 mt-0.5" />
                            <div>
                                <p class="text-xs text-slate-500">Phone Number</p>
                                <p class="font-medium dark:text-white">{{ user?.phone || 'Not provided' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Target class="w-5 h-5 text-slate-400 mt-0.5" />
                            <div>
                                <p class="text-xs text-slate-500">Area of Interest</p>
                                <p class="font-medium dark:text-white">{{ user?.interests || 'Not specified' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Award class="w-5 h-5 text-slate-400 mt-0.5" />
                            <div>
                                <p class="text-xs text-slate-500">Education</p>
                                <p class="font-medium dark:text-white">{{ user?.education || 'Not specified' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Courses Information Card -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border p-6">
                    <div class="flex items-center gap-2 mb-6 pb-3 border-b">
                        <BookOpen class="w-5 h-5 text-blue-600" />
                        <h2 class="text-lg font-bold dark:text-white">My Learning</h2>
                    </div>
                    
                    <div v-if="enrolledCourses && enrolledCourses.length > 0" class="space-y-3">
                        <div v-for="course in enrolledCourses.slice(0, 4)" :key="course.id" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                            <img :src="course.course?.image" class="w-12 h-12 rounded-lg object-cover" />
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm dark:text-white truncate">{{ course.course?.title }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="flex-1 h-1.5 bg-slate-200 rounded-full">
                                        <div class="h-full bg-blue-600 rounded-full" :style="{ width: (course.progress_percentage || 0) + '%' }"></div>
                                    </div>
                                    <span class="text-xs text-blue-600">{{ course.progress_percentage || 0 }}%</span>
                                </div>
                            </div>
                        </div>
                        <button @click="goToDashboard" class="w-full mt-3 text-center text-sm text-blue-600 hover:text-blue-700">
                            View all {{ totalCourses }} courses →
                        </button>
                    </div>
                    <div v-else class="text-center py-6">
                        <BookOpen class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                        <p class="text-slate-500 text-sm">No courses enrolled yet</p>
                        <Link href="/" class="inline-block mt-3 text-sm text-blue-600 hover:text-blue-700">Browse Courses →</Link>
                    </div>
                </div>
            </div>

            <!-- Wishlist / Planning to Study Section -->
            <div class="mt-8 bg-white dark:bg-slate-900 rounded-2xl border p-6">
                <div class="flex items-center gap-2 mb-6 pb-3 border-b">
                    <Heart class="w-5 h-5 text-blue-600" />
                    <h2 class="text-lg font-bold dark:text-white">Planning to Study</h2>
                </div>
                <div v-if="wishlistCount > 0" class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                    <div>
                        <p class="font-medium dark:text-white">{{ wishlistCount }} course(s) in your wishlist</p>
                        <p class="text-sm text-slate-500">Courses you plan to take</p>
                    </div>
                    <Link href="/" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                        View Wishlist
                    </Link>
                </div>
                <div v-else class="text-center py-6">
                    <Heart class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <p class="text-slate-500 text-sm">No courses in your wishlist yet</p>
                    <Link href="/" class="inline-block mt-3 text-sm text-blue-600 hover:text-blue-700">Browse Courses →</Link>
                </div>
            </div>
        </div>
    </div>
</template>