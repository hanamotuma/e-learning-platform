<!-- resources/js/Pages/Instructor/Dashboard.vue -->
<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { 
  BookOpen, 
  Calendar, 
  MessageCircle, 
  Bell, 
  Settings,
  ChevronRight,
  TrendingUp,
  Award,
  Clock,
  Play,
  LogOut,
  HelpCircle,
  BarChart3,
  FileText,
  Moon,
  Sun,
  Home,
  Users,
  Video,
  DollarSign,
  Star,
  PlusCircle,
  Edit,
  Eye,
  Upload,
  Download
} from 'lucide-vue-next'

const props = defineProps({
    auth: Object
})

// Theme state
const isDarkMode = ref(false)

// Mobile sidebar state
const isMobileMenuOpen = ref(false)

// Active tab
const activeTab = ref('dashboard')

// Sample data - replace with your actual data from backend
const courses = ref([
    { 
        id: 1, 
        title: 'Modern Web Development with Vue', 
        students: 156, 
        progress: 72, 
        revenue: 12450,
        rating: 4.8,
        status: 'published',
        thumbnail: 'https://images.pexels.com/photos/1181244/pexels-photo-1181244.jpeg?auto=compress&cs=tinysrgb&w=400',
        lastUpdated: '2 days ago'
    },
    { 
        id: 2, 
        title: 'Advanced UI/UX Design Systems', 
        students: 89, 
        progress: 45, 
        revenue: 6675,
        rating: 4.9,
        status: 'published',
        thumbnail: 'https://images.pexels.com/photos/196644/pexels-photo-196644.jpeg?auto=compress&cs=tinysrgb&w=400',
        lastUpdated: '5 days ago'
    },
    { 
        id: 3, 
        title: 'Python Programming Masterclass', 
        students: 234, 
        progress: 30, 
        revenue: 18720,
        rating: 4.7,
        status: 'draft',
        thumbnail: 'https://images.pexels.com/photos/373543/pexels-photo-373543.jpeg?auto=compress&cs=tinysrgb&w=400',
        lastUpdated: '1 week ago'
    },
])

const recentEnrollments = ref([
    { id: 1, student: 'Sarah Johnson', course: 'Modern Web Development with Vue', date: 'Mar 28, 2026', amount: 89.99, status: 'completed' },
    { id: 2, student: 'Michael Chen', course: 'Advanced UI/UX Design Systems', date: 'Mar 27, 2026', amount: 74.99, status: 'completed' },
    { id: 3, student: 'Emily Rodriguez', course: 'Python Programming Masterclass', date: 'Mar 26, 2026', amount: 99.99, status: 'pending' },
])

const pendingReviews = ref([
    { id: 1, student: 'Jessica Williams', course: 'Modern Web Development with Vue', rating: 5, comment: 'Excellent course! Very well structured.', date: '2 hours ago' },
    { id: 2, student: 'Robert Taylor', course: 'Advanced UI/UX Design Systems', rating: 4, comment: 'Great content, would recommend.', date: '1 day ago' },
])

const upcomingLiveSessions = ref([
    { id: 1, title: 'Vue.js Q&A Session', course: 'Modern Web Development with Vue', date: 'Today', time: '3:00 PM', attendees: 45 },
    { id: 2, title: 'Design Critique Workshop', course: 'Advanced UI/UX Design Systems', date: 'Tomorrow', time: '11:00 AM', attendees: 32 },
])

// Stats
const stats = computed(() => [
    { label: 'Total Students', value: '479', icon: Users, change: '+12%', color: 'blue' },
    { label: 'Total Revenue', value: '$38,845', icon: DollarSign, change: '+8%', color: 'emerald' },
    { label: 'Active Courses', value: courses.value.filter(c => c.status === 'published').length, icon: BookOpen, change: '+2', color: 'purple' },
    { label: 'Avg. Rating', value: '4.8', icon: Star, change: '+0.3', color: 'yellow' },
])

// Theme functions
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
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    
    if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
        isDarkMode.value = true
        document.documentElement.classList.add('dark')
    } else {
        isDarkMode.value = false
        document.documentElement.classList.remove('dark')
    }
}

onMounted(() => {
    initTheme()
})

// Navigation items
const navItems = [
    { id: 'dashboard', label: 'Dashboard', icon: Home },
    { id: 'courses', label: 'My Courses', icon: BookOpen },
    { id: 'students', label: 'Students', icon: Users },
    { id: 'analytics', label: 'Analytics', icon: BarChart3 },
    { id: 'earnings', label: 'Earnings', icon: DollarSign },
    { id: 'live-sessions', label: 'Live Sessions', icon: Video },
]

// User info
const userName = computed(() => {
    if (props.auth?.user?.name) {
        return props.auth.user.name.split(' ')[0]
    }
    return 'Instructor'
})

const fullName = computed(() => {
    return props.auth?.user?.name || 'Dr. Sarah Johnson'
})

const userEmail = computed(() => {
    return props.auth?.user?.email || 'instructor@learnhub.com'
})

const logout = () => {
    if (confirm('Are you sure you want to logout?')) {
        router.post(route('logout'))
    }
}

import { router } from '@inertiajs/vue3'

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount)
}
</script>

<template>
    <Head title="Instructor Dashboard | LearnHub" />
    
    <div class="min-h-screen bg-white dark:bg-slate-950">
        
        <!-- Theme Toggle -->
        <button @click="toggleTheme" class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-white dark:bg-slate-800 rounded-xl shadow-lg flex items-center justify-center hover:scale-105 transition-all border border-slate-200 dark:border-slate-700">
            <Sun v-if="isDarkMode" class="w-5 h-5 text-yellow-500" />
            <Moon v-else class="w-5 h-5 text-slate-700" />
        </button>

        <!-- Mobile Menu Button -->
        <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="lg:hidden fixed top-4 left-4 z-50 w-10 h-10 bg-white dark:bg-slate-800 rounded-xl shadow-md flex items-center justify-center border border-slate-200 dark:border-slate-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Sidebar -->
        <aside :class="[
            'fixed left-0 top-0 h-full bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300 z-40',
            isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0 lg:w-64'
        ]">
            <!-- Logo -->
            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg">
                        L
                    </div>
                    <span class="text-2xl font-black tracking-tighter dark:text-white">Learn<span class="text-blue-600">Hub</span></span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Instructor Portal</p>
            </div>

            <!-- User Profile -->
            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600 font-bold text-lg">
                        {{ fullName.charAt(0) }}
                    </div>
                    <div>
                        <p class="font-bold dark:text-white">{{ fullName }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ userEmail }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1">
                <Link v-for="item in navItems" :key="item.id"
                    :href="item.href || '#'"
                    @click="activeTab = item.id"
                    :class="[
                        'flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200',
                        activeTab === item.id 
                            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' 
                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'
                    ]">
                    <component :is="item.icon" class="w-5 h-5" />
                    <span class="font-medium">{{ item.label }}</span>
                </Link>
            </nav>

            <!-- Bottom Actions -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-200 dark:border-slate-800 space-y-1">
                <Link href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    <HelpCircle class="w-5 h-5" />
                    <span class="font-medium">Help & Support</span>
                </Link>
                <Link href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    <Settings class="w-5 h-5" />
                    <span class="font-medium">Settings</span>
                </Link>
                <button @click="logout" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                    <LogOut class="w-5 h-5" />
                    <span class="font-medium">Logout</span>
                </button>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

        <!-- Main Content -->
        <main class="lg:ml-64">
            
            <!-- Top Header -->
            <header class="bg-white dark:bg-slate-900 sticky top-0 z-20 border-b border-slate-200 dark:border-slate-800">
                <div class="px-4 lg:px-8 py-4 flex items-center justify-between">
                    <div>
                        <h1 class="text-xl lg:text-2xl font-black dark:text-white">Welcome back, {{ userName }}!</h1>
                        <p class="text-xs lg:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Here's what's happening with your courses today.</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button class="relative p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                            <Bell class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                        <div class="flex items-center space-x-2 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1.5 rounded-xl transition-colors">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600 font-bold text-sm">
                                {{ fullName.charAt(0) }}
                            </div>
                            <div class="hidden lg:block">
                                <p class="text-sm font-medium dark:text-white">{{ fullName }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Instructor</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="p-4 lg:p-8">
                
                <!-- Create Course Banner -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-6 lg:p-8 mb-8 text-white">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <p class="text-sm opacity-90 mb-1">Ready to share your knowledge?</p>
                            <h2 class="text-2xl lg:text-3xl font-black">Create a New Course</h2>
                            <p class="text-sm opacity-90 mt-2">Reach thousands of students worldwide and grow your impact.</p>
                        </div>
                        <button class="px-6 py-3 bg-white text-blue-600 font-bold rounded-xl hover:shadow-lg transition-all flex items-center space-x-2">
                            <PlusCircle class="w-4 h-4" />
                            <span>Create Course</span>
                        </button>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
                    <div v-for="stat in stats" :key="stat.label" 
                        class="bg-white dark:bg-slate-900 rounded-xl p-4 lg:p-5 border border-slate-200 dark:border-slate-800 hover:shadow-md transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">{{ stat.label }}</p>
                                <p class="text-2xl lg:text-3xl font-black dark:text-white">{{ stat.value }}</p>
                                <p class="text-xs text-emerald-600 mt-1">{{ stat.change }}</p>
                            </div>
                            <div :class="[
                                'w-10 h-10 rounded-xl flex items-center justify-center',
                                stat.color === 'blue' ? 'bg-blue-50 dark:bg-blue-900/20' :
                                stat.color === 'emerald' ? 'bg-emerald-50 dark:bg-emerald-900/20' :
                                stat.color === 'purple' ? 'bg-purple-50 dark:bg-purple-900/20' :
                                'bg-yellow-50 dark:bg-yellow-900/20'
                            ]">
                                <component :is="stat.icon" :class="[
                                    'w-5 h-5',
                                    stat.color === 'blue' ? 'text-blue-600' :
                                    stat.color === 'emerald' ? 'text-emerald-600' :
                                    stat.color === 'purple' ? 'text-purple-600' :
                                    'text-yellow-600'
                                ]" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Courses Section -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-black dark:text-white">My Courses</h2>
                        <Link href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All →</Link>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div v-for="course in courses" :key="course.id" 
                            class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden hover:shadow-lg transition-all">
                            <img :src="course.thumbnail" class="w-full h-40 object-cover" />
                            <div class="p-5">
                                <div class="flex items-center justify-between mb-2">
                                    <span :class="[
                                        'text-xs px-2 py-1 rounded-full font-medium',
                                        course.status === 'published' 
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                            : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'
                                    ]">
                                        {{ course.status === 'published' ? 'Published' : 'Draft' }}
                                    </span>
                                    <div class="flex items-center space-x-1">
                                        <Star class="w-3 h-3 text-yellow-400 fill-yellow-400" />
                                        <span class="text-sm font-medium dark:text-white">{{ course.rating }}</span>
                                    </div>
                                </div>
                                <h3 class="font-bold dark:text-white mb-2">{{ course.title }}</h3>
                                <div class="flex items-center justify-between text-sm text-slate-500 dark:text-slate-400 mb-3">
                                    <span>{{ course.students }} students</span>
                                    <span>{{ formatCurrency(course.revenue) }}</span>
                                </div>
                                <div class="mb-3">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span>Progress</span>
                                        <span>{{ course.progress }}%</span>
                                    </div>
                                    <div class="bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                                        <div class="bg-blue-600 rounded-full h-1.5" :style="{ width: course.progress + '%' }"></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                                    <span class="text-xs text-slate-500">Updated {{ course.lastUpdated }}</span>
                                    <div class="flex items-center space-x-2">
                                        <button class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors">
                                            <Eye class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Enrollments & Upcoming Live Sessions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-8">
                    
                    <!-- Recent Enrollments -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-black dark:text-white">Recent Enrollments</h2>
                            <Link href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All →</Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="enrollment in recentEnrollments" :key="enrollment.id" 
                                class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                                        <Users class="w-5 h-5 text-blue-600" />
                                    </div>
                                    <div>
                                        <p class="font-medium dark:text-white text-sm">{{ enrollment.student }}</p>
                                        <p class="text-xs text-slate-500">{{ enrollment.course }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-sm dark:text-white">{{ formatCurrency(enrollment.amount) }}</p>
                                    <p class="text-xs text-slate-500">{{ enrollment.date }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Live Sessions -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-black dark:text-white">Upcoming Live Sessions</h2>
                            <button class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center space-x-1">
                                <PlusCircle class="w-3 h-3" />
                                <span>Schedule</span>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div v-for="session in upcomingLiveSessions" :key="session.id" 
                                class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-bold dark:text-white text-sm">{{ session.title }}</h3>
                                        <p class="text-xs text-slate-500 mt-1">{{ session.course }}</p>
                                        <div class="flex items-center space-x-4 mt-2">
                                            <span class="text-xs text-blue-600 flex items-center">
                                                <Calendar class="w-3 h-3 mr-1" />
                                                {{ session.date }}
                                            </span>
                                            <span class="text-xs text-blue-600 flex items-center">
                                                <Clock class="w-3 h-3 mr-1" />
                                                {{ session.time }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-medium text-blue-600">{{ session.attendees }} attending</span>
                                        <button class="block mt-2 text-xs bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700">
                                            Start
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Reviews & Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                    
                    <!-- Pending Reviews -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-black dark:text-white">Pending Reviews</h2>
                            <Link href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All →</Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="review in pendingReviews" :key="review.id" 
                                class="p-4 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 bg-purple-50 dark:bg-purple-900/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <Star class="w-5 h-5 text-purple-600" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="font-medium dark:text-white text-sm">{{ review.student }}</p>
                                            <div class="flex items-center">
                                                <Star v-for="i in 5" :key="i" 
                                                    class="w-3 h-3" 
                                                    :class="i <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-slate-300'"
                                                />
                                            </div>
                                        </div>
                                        <p class="text-xs text-slate-500">{{ review.course }}</p>
                                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-2">"{{ review.comment }}"</p>
                                        <div class="flex items-center justify-between mt-3">
                                            <span class="text-xs text-slate-500">{{ review.date }}</span>
                                            <div class="flex items-center space-x-2">
                                                <button class="text-xs text-emerald-600 hover:text-emerald-700">Approve</button>
                                                <button class="text-xs text-red-600 hover:text-red-700">Dismiss</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <h2 class="text-xl font-black dark:text-white mb-4">Quick Actions</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <button class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all group">
                                <Upload class="w-6 h-6 text-blue-600 mx-auto mb-2" />
                                <p class="text-sm font-medium dark:text-white">Upload Content</p>
                                <p class="text-xs text-slate-500">Add course materials</p>
                            </button>
                            <button class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all group">
                                <Video class="w-6 h-6 text-blue-600 mx-auto mb-2" />
                                <p class="text-sm font-medium dark:text-white">Schedule Live</p>
                                <p class="text-xs text-slate-500">Create live session</p>
                            </button>
                            <button class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all group">
                                <Download class="w-6 h-6 text-blue-600 mx-auto mb-2" />
                                <p class="text-sm font-medium dark:text-white">Export Reports</p>
                                <p class="text-xs text-slate-500">Download analytics</p>
                            </button>
                            <button class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all group">
                                <MessageCircle class="w-6 h-6 text-blue-600 mx-auto mb-2" />
                                <p class="text-sm font-medium dark:text-white">Announcements</p>
                                <p class="text-xs text-slate-500">Message students</p>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.dark ::-webkit-scrollbar-track {
    background: #1e293b;
}

.dark ::-webkit-scrollbar-thumb {
    background: #475569;
}

/* Smooth transitions */
* {
    transition-property: background-color, border-color, color, fill, stroke, box-shadow;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>