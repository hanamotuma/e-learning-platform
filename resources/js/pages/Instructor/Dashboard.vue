<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { 
  BookOpen, Users, DollarSign, Star, TrendingUp, Calendar, 
  MessageCircle, Settings, LogOut, Bell, Sun, Moon, Home,
  PlusCircle, ChevronRight, Award, Clock, BarChart3, 
  Download, Eye, Edit, Trash2, CheckCircle
} from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
    stats: Object,
    courses: Array,
    recentEnrollments: Array,
    recentReviews: Array,
    monthlyEarnings: Array,
    auth: Object
})

const isDarkMode = ref(false)
const isMobileMenuOpen = ref(false)
const activeTab = ref('dashboard')
const showNotifications = ref(false)

const userFullName = computed(() => props.auth?.user?.name || 'Instructor')
const userInitial = computed(() => userFullName.value.charAt(0).toUpperCase())

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US').format(amount || 0) + ' ETB'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
    } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('theme', 'light')
    }
}

const initTheme = () => {
    const savedTheme = localStorage.getItem('theme')
    if (savedTheme === 'dark') {
        isDarkMode.value = true
        document.documentElement.classList.add('dark')
    }
}

const goToCourse = (courseId) => {
    router.get(`/instructor/courses/${courseId}/edit`)
}

const logout = () => {
    if (confirm('Are you sure you want to logout?')) {
        router.post(route('logout'))
    }
}

onMounted(() => {
    initTheme()
})
</script>

<template>
    <Head title="Instructor Dashboard | LearnHub" />
    
    <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
        <!-- Theme Toggle -->
        <button @click="toggleTheme" class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center hover:scale-105 transition-all border border-slate-200 dark:border-slate-700">
            <Sun v-if="isDarkMode" class="w-5 h-5 text-yellow-500" />
            <Moon v-else class="w-5 h-5 text-slate-700" />
        </button>

        <!-- Mobile Menu Button -->
        <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="lg:hidden fixed top-4 left-4 z-50 w-10 h-10 bg-white dark:bg-slate-800 rounded-xl shadow-md flex items-center justify-center border border-slate-200 dark:border-slate-700">
            <Menu class="w-5 h-5 text-slate-600 dark:text-slate-300" />
        </button>

        <!-- Sidebar -->
        <aside :class="[
            'fixed left-0 top-0 h-full bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300 z-40',
            isMobileMenuOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0 lg:w-64'
        ]">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-black text-xl">L</div>
                    <span class="text-2xl font-black dark:text-white">Learn<span class="text-blue-600">Hub</span></span>
                </div>
            </div>

            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                        {{ userInitial }}
                    </div>
                    <div>
                        <p class="font-bold dark:text-white">{{ userFullName }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Instructor</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-4 space-y-1">
                <button @click="activeTab = 'dashboard'" :class="['flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all', activeTab === 'dashboard' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50']">
                    <Home class="w-5 h-5" /><span>Dashboard</span>
                </button>
                <button @click="activeTab = 'courses'" :class="['flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all', activeTab === 'courses' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50']">
                    <BookOpen class="w-5 h-5" /><span>My Courses</span>
                </button>
                <button @click="activeTab = 'students'" :class="['flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all', activeTab === 'students' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50']">
                    <Users class="w-5 h-5" /><span>Students</span>
                </button>
                <button @click="activeTab = 'earnings'" :class="['flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all', activeTab === 'earnings' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50']">
                    <DollarSign class="w-5 h-5" /><span>Earnings</span>
                </button>
            </nav>

            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-200 dark:border-slate-800">
                <button @click="logout" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                    <LogOut class="w-5 h-5" /><span>Logout</span>
                </button>
            </div>
        </aside>

        <!-- Overlay -->
        <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

        <!-- Main Content -->
        <main class="lg:ml-64">
            <header class="bg-white dark:bg-slate-900 sticky top-0 z-20 border-b border-slate-200 dark:border-slate-800">
                <div class="px-4 lg:px-8 py-4 flex items-center justify-between">
                    <div>
                        <h1 class="text-xl lg:text-2xl font-black dark:text-white">Instructor Dashboard</h1>
                        <p class="text-xs text-slate-500">Manage your courses and students</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button class="relative p-2 hover:bg-slate-100 rounded-xl">
                            <Bell class="w-5 h-5" />
                        </button>
                        <Link href="/instructor/courses/create" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm flex items-center gap-2 hover:bg-blue-700">
                            <PlusCircle class="w-4 h-4" /> New Course
                        </Link>
                    </div>
                </div>
            </header>

            <div class="p-4 lg:p-8">
                <!-- Dashboard View -->
                <div v-if="activeTab === 'dashboard'">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border">
                            <p class="text-xs text-slate-500">Total Courses</p>
                            <p class="text-2xl font-bold">{{ stats?.total_courses || 0 }}</p>
                        </div>
                        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border">
                            <p class="text-xs text-slate-500">Total Students</p>
                            <p class="text-2xl font-bold">{{ stats?.total_students || 0 }}</p>
                        </div>
                        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border">
                            <p class="text-xs text-slate-500">Total Revenue</p>
                            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(stats?.total_revenue) }}</p>
                        </div>
                        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border">
                            <p class="text-xs text-slate-500">Average Rating</p>
                            <p class="text-2xl font-bold text-yellow-500">{{ stats?.average_rating || 0 }} ★</p>
                        </div>
                        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border">
                            <p class="text-xs text-slate-500">Pending Payout</p>
                            <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(stats?.pending_payout) }}</p>
                        </div>
                    </div>

                    <!-- Monthly Earnings Chart -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border p-6 mb-8">
                        <h2 class="text-xl font-bold mb-4">Monthly Earnings</h2>
                        <div class="flex items-end gap-4 h-64">
                            <div v-for="item in monthlyEarnings" :key="item.month" class="flex-1 flex flex-col items-center">
                                <div class="w-full bg-blue-100 dark:bg-blue-900/30 rounded-t-lg transition-all" :style="{ height: (item.earnings / (monthlyEarnings[0]?.earnings || 1) * 200) + 'px' }"></div>
                                <p class="text-xs text-slate-500 mt-2">{{ item.month }}</p>
                                <p class="text-xs font-bold">{{ formatCurrency(item.earnings) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid lg:grid-cols-2 gap-8">
                        <!-- Recent Enrollments -->
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border">
                            <div class="p-6 border-b">
                                <h2 class="text-xl font-bold">Recent Enrollments</h2>
                            </div>
                            <div class="divide-y">
                                <div v-for="enrollment in recentEnrollments?.slice(0,5)" :key="enrollment.id" class="p-4 flex justify-between items-center">
                                    <div>
                                        <p class="font-medium">{{ enrollment.user?.name }}</p>
                                        <p class="text-sm text-slate-500">{{ enrollment.course?.title }}</p>
                                    </div>
                                    <span class="text-sm text-green-600">+{{ formatCurrency(enrollment.amount_paid) }}</span>
                                </div>
                                <div v-if="!recentEnrollments?.length" class="p-8 text-center text-slate-500">No recent enrollments</div>
                            </div>
                        </div>

                        <!-- Recent Reviews -->
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border">
                            <div class="p-6 border-b">
                                <h2 class="text-xl font-bold">Recent Reviews</h2>
                            </div>
                            <div class="divide-y">
                                <div v-for="review in recentReviews?.slice(0,5)" :key="review.id" class="p-4">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="flex text-yellow-400">
                                            <span v-for="i in review.rating" :key="i">★</span>
                                            <span v-for="i in (5 - review.rating)" :key="i" class="text-slate-300">★</span>
                                        </div>
                                        <span class="text-xs text-slate-500">{{ review.user?.name }}</span>
                                    </div>
                                    <p class="text-sm text-slate-600">{{ review.review }}</p>
                                    <p class="text-xs text-slate-400 mt-1">{{ review.course?.title }}</p>
                                </div>
                                <div v-if="!recentReviews?.length" class="p-8 text-center text-slate-500">No reviews yet</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Courses View -->
                <div v-if="activeTab === 'courses'">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">My Courses</h1>
                        <Link href="/instructor/courses/create" class="px-4 py-2 bg-blue-600 text-white rounded-lg flex items-center gap-2 hover:bg-blue-700">
                            <PlusCircle class="w-4 h-4" /> Create Course
                        </Link>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="course in courses" :key="course.id" class="bg-white dark:bg-slate-900 rounded-2xl overflow-hidden border hover:shadow-lg transition-all cursor-pointer" @click="goToCourse(course.id)">
                            <img :src="course.image" class="w-full h-48 object-cover" />
                            <div class="p-5">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-full">{{ course.status || 'Draft' }}</span>
                                    <div class="flex items-center gap-1 text-yellow-400">
                                        <Star class="w-4 h-4 fill-yellow-400" />
                                        <span class="text-sm">{{ course.rating || 4.5 }}</span>
                                    </div>
                                </div>
                                <h3 class="font-bold text-lg mb-2">{{ course.title }}</h3>
                                <p class="text-sm text-slate-500 mb-3">{{ course.enrollments?.length || 0 }} students enrolled</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-xl font-bold text-blue-600">{{ formatCurrency(course.price) }}</span>
                                    <div class="flex gap-2">
                                        <button class="p-2 text-slate-400 hover:text-blue-600"><Edit class="w-4 h-4" /></button>
                                        <button class="p-2 text-slate-400 hover:text-green-600"><Eye class="w-4 h-4" /></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students View -->
                <div v-if="activeTab === 'students'">
                    <h1 class="text-2xl font-bold mb-6">My Students</h1>
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-slate-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-medium">Student</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium">Course</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium">Progress</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium">Enrolled</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="student in recentEnrollments" :key="student.id">
                                    <td class="px-6 py-4">{{ student.user?.name }}</td>
                                    <td class="px-6 py-4">{{ student.course?.title }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-2 bg-slate-200 rounded-full">
                                                <div class="h-2 bg-blue-600 rounded-full" :style="{ width: (student.progress_percentage || 0) + '%' }"></div>
                                            </div>
                                            <span class="text-sm">{{ student.progress_percentage || 0 }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">{{ formatDate(student.enrolled_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Earnings View -->
                <div v-if="activeTab === 'earnings'">
                    <h1 class="text-2xl font-bold mb-6">Earnings Overview</h1>
                    <div class="grid md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border text-center">
                            <p class="text-sm text-slate-500">Total Revenue</p>
                            <p class="text-3xl font-bold text-green-600">{{ formatCurrency(stats?.total_revenue) }}</p>
                        </div>
                        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border text-center">
                            <p class="text-sm text-slate-500">Platform Fee (30%)</p>
                            <p class="text-3xl font-bold text-orange-600">{{ formatCurrency(stats?.total_revenue * 0.3) }}</p>
                        </div>
                        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border text-center">
                            <p class="text-sm text-slate-500">Your Earnings (70%)</p>
                            <p class="text-3xl font-bold text-blue-600">{{ formatCurrency(stats?.total_revenue * 0.7) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>