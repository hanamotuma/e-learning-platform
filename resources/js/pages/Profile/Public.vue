<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { User, Mail, Phone, BookOpen, Award, Calendar, ChevronLeft, Star, Users, Clock } from 'lucide-vue-next'

const props = defineProps({
    user: Object,
    enrolledCourses: Array,
    certificates: Array
})

const isDarkMode = ref(false)
const activeTab = ref('info')

const formatDate = (date) => {
    if (!date) return 'Not yet'
    return new Date(date).toLocaleDateString()
}

const formatNumber = (num) => {
    if (num >= 1000) return (num / 1000).toFixed(1) + 'k'
    return num.toString()
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

const userFullName = computed(() => props.user?.name || 'User')
const userEmail = computed(() => props.user?.email || 'No email')
const userUsername = computed(() => props.user?.username || 'No username')
const userPhone = computed(() => props.user?.phone || 'Not provided')
const userBio = computed(() => props.user?.bio || 'No bio added yet')
const userEducation = computed(() => props.user?.education || 'Not specified')
const userInterests = computed(() => props.user?.interests || 'Not specified')
</script>

<template>
    <Head :title="(user?.name || 'Profile') + ' | LearnHub'" />
    
    <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white">
            <div class="max-w-6xl mx-auto px-4 py-6">
                <div class="flex items-center justify-between">
                    <button @click="goBack" class="flex items-center gap-2 text-white/80 hover:text-white">
                        <ChevronLeft class="w-5 h-5" />
                        Back to Home
                    </button>
                    <div class="flex items-center gap-3">
                        <Link href="/student/dashboard" class="px-4 py-2 bg-white/20 rounded-xl hover:bg-white/30 transition-colors text-sm font-medium">
                            Dashboard
                        </Link>
                        <button @click="goToEditProfile" class="px-4 py-2 bg-white/20 rounded-xl hover:bg-white/30 transition-colors text-sm font-medium">
                            Edit Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 py-8">
            <!-- Profile Header -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border p-8 mb-8 text-center">
                <div class="w-28 h-28 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <span class="text-4xl font-bold text-white">{{ userInitials }}</span>
                </div>
                <h1 class="text-2xl font-bold dark:text-white">{{ userFullName }}</h1>
                <p class="text-slate-500 dark:text-slate-400">{{ userEmail }}</p>
                <p class="text-sm text-slate-400 mt-2">Member since {{ formatDate(user?.created_at) }}</p>
            </div>

            <!-- Tabs -->
            <div class="flex gap-4 border-b mb-6">
                <button @click="activeTab = 'info'" :class="['px-4 py-2 font-medium transition-colors', activeTab === 'info' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-500 hover:text-slate-700']">
                    Personal Info
                </button>
                <button @click="activeTab = 'courses'" :class="['px-4 py-2 font-medium transition-colors', activeTab === 'courses' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-500 hover:text-slate-700']">
                    My Courses ({{ enrolledCourses?.length || 0 }})
                </button>
                <button @click="activeTab = 'certificates'" :class="['px-4 py-2 font-medium transition-colors', activeTab === 'certificates' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-500 hover:text-slate-700']">
                    Certificates ({{ certificates?.length || 0 }})
                </button>
            </div>

            <!-- Personal Info Tab -->
            <div v-if="activeTab === 'info'" class="bg-white dark:bg-slate-900 rounded-2xl border p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <User class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div>
                            <p class="text-sm text-slate-500">Full Name</p>
                            <p class="font-medium dark:text-white">{{ userFullName }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <User class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div>
                            <p class="text-sm text-slate-500">Username</p>
                            <p class="font-medium dark:text-white">{{ userUsername }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <Mail class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div>
                            <p class="text-sm text-slate-500">Email Address</p>
                            <p class="font-medium dark:text-white">{{ userEmail }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <Phone class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div>
                            <p class="text-sm text-slate-500">Phone Number</p>
                            <p class="font-medium dark:text-white">{{ userPhone }}</p>
                        </div>
                    </div>
                    <div class="md:col-span-2 flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <User class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div>
                            <p class="text-sm text-slate-500">Bio</p>
                            <p class="font-medium dark:text-white">{{ userBio }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <Award class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div>
                            <p class="text-sm text-slate-500">Education</p>
                            <p class="font-medium dark:text-white">{{ userEducation }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <Star class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div>
                            <p class="text-sm text-slate-500">Area of Interest</p>
                            <p class="font-medium dark:text-white">{{ userInterests }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Courses Tab -->
            <div v-if="activeTab === 'courses'" class="bg-white dark:bg-slate-900 rounded-2xl border p-6">
                <div v-if="enrolledCourses && enrolledCourses.length > 0" class="space-y-4">
                    <div v-for="course in enrolledCourses" :key="course.id" class="flex gap-4 p-4 border rounded-xl hover:shadow-md transition-all cursor-pointer" @click="goToCourse(course.course_id)">
                        <img :src="course.course?.image" class="w-24 h-24 rounded-lg object-cover" />
                        <div class="flex-1">
                            <h3 class="font-bold dark:text-white">{{ course.course?.title }}</h3>
                            <p class="text-sm text-slate-500">{{ course.course?.instructor?.name || 'Expert Instructor' }}</p>
                            <div class="flex items-center gap-4 mt-2 text-sm text-slate-500">
                                <span class="flex items-center gap-1"><Clock class="w-3 h-3" /> {{ course.course?.hours || 0 }} hours</span>
                                <span class="flex items-center gap-1"><Users class="w-3 h-3" /> {{ formatNumber(course.course?.students || 0) }} students</span>
                            </div>
                            <div class="mt-2">
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Progress</span>
                                    <span class="font-bold text-blue-600">{{ course.progress_percentage || 0 }}%</span>
                                </div>
                                <div class="bg-slate-200 rounded-full h-2">
                                    <div class="bg-blue-600 rounded-full h-2" :style="{ width: (course.progress_percentage || 0) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <span class="text-xs text-slate-400">Enrolled: {{ formatDate(course.enrolled_at) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12">
                    <BookOpen class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                    <p class="text-slate-500">No courses enrolled yet</p>
                    <Link href="/courses" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg">Browse Courses</Link>
                </div>
            </div>

            <!-- Certificates Tab -->
            <div v-if="activeTab === 'certificates'" class="bg-white dark:bg-slate-900 rounded-2xl border p-6">
                <div v-if="certificates && certificates.length > 0" class="grid md:grid-cols-2 gap-4">
                    <div v-for="cert in certificates" :key="cert.id" class="p-4 border rounded-xl text-center hover:shadow-md transition-all">
                        <Award class="w-12 h-12 text-purple-600 mx-auto mb-3" />
                        <h3 class="font-bold dark:text-white">{{ cert.course?.title }}</h3>
                        <p class="text-sm text-slate-500">Issued: {{ formatDate(cert.issued_at) }}</p>
                        <p class="text-xs text-slate-400 mt-1">Certificate ID: {{ cert.certificate_number }}</p>
                        <button class="mt-3 px-4 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 transition-colors">
                            Download
                        </button>
                    </div>
                </div>
                <div v-else class="text-center py-12">
                    <Award class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                    <p class="text-slate-500">No certificates earned yet</p>
                    <p class="text-sm text-slate-400 mt-1">Complete courses to earn certificates</p>
                </div>
            </div>
        </div>
    </div>
</template>