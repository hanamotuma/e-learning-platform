<!-- resources/js/pages/Student/Dashboard.vue -->
<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
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
  CheckCircle,
  Video,
  Users,
  X
} from 'lucide-vue-next'

const props = defineProps({
    auth: Object
})

// Theme state
const isDarkMode = ref(false)
const isMobileMenuOpen = ref(false)
const activeTab = ref('dashboard')
const showNotifications = ref(false)

// Notification state
const notifications = ref([])
const unreadCount = ref(0)
const loading = ref(false)

// Sample data - replace with your actual data from backend
const enrolledCourses = ref([
    { id: 1, title: 'Modern Web Development with Vue', progress: 72, instructor: 'Dr. Sarah Johnson', thumbnail: 'https://images.pexels.com/photos/1181244/pexels-photo-1181244.jpeg?auto=compress&cs=tinysrgb&w=400', lastAccessed: '2 hours ago' },
    { id: 2, title: 'Advanced UI/UX Design Systems', progress: 45, instructor: 'Marcus Rhoades', thumbnail: 'https://images.pexels.com/photos/196644/pexels-photo-196644.jpeg?auto=compress&cs=tinysrgb&w=400', lastAccessed: '1 day ago' },
    { id: 3, title: 'Python Programming Masterclass', progress: 30, instructor: 'Prof. Elena Fisher', thumbnail: 'https://images.pexels.com/photos/373543/pexels-photo-373543.jpeg?auto=compress&cs=tinysrgb&w=400', lastAccessed: '3 days ago' },
])

const upcomingActivities = ref([
    { id: 1, title: 'Quiz 2: Advanced Vue Generics', date: 'Jun 20, 2026', time: '2:00 PM', type: 'quiz' },
    { id: 2, title: 'Assignment 4: REST API Project', date: 'Jun 25, 2026', time: '2:15 PM', type: 'assignment' },
    { id: 3, title: 'Live Session: Career Prep', date: 'Jun 28, 2026', time: '10:00 AM', type: 'live' },
])

const discussions = ref([
    { id: 1, title: 'Best Practices for Vue Code', replies: 24, lastActive: '2 hours ago' },
    { id: 2, title: 'Understanding REST APIs', replies: 18, lastActive: '5 hours ago' },
    { id: 3, title: 'Advanced UI Design Tips', replies: 32, lastActive: '1 day ago' },
])

// Stats
const stats = computed(() => [
    { label: 'Courses Enrolled', value: enrolledCourses.value.length, icon: BookOpen },
    { label: 'Hours Learned', value: '47', icon: Clock },
    { label: 'Certificates', value: '3', icon: Award },
    { label: 'Current Streak', value: '12 days', icon: TrendingUp },
])

// Navigation items - NOTIFICATIONS REMOVED FROM HERE
const navItems = [
    { id: 'dashboard', label: 'Dashboard', icon: Home },
    { id: 'courses', label: 'My Courses', icon: BookOpen },
    { id: 'assignments', label: 'Assignments', icon: FileText },
    { id: 'progress', label: 'Progress', icon: TrendingUp },
    { id: 'discussions', label: 'Discussions', icon: MessageCircle },
    // Notifications item REMOVED from sidebar
]

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

// Notification functions
const fetchNotifications = async () => {
    loading.value = true
    try {
        const response = await fetch('/notifications')
        const data = await response.json()
        notifications.value = data.notifications?.data || []
        unreadCount.value = data.unread_count || 0
    } catch (error) {
        console.error('Error:', error)
    } finally {
        loading.value = false
    }
}

const fetchUnreadCount = async () => {
    try {
        const response = await fetch('/notifications/unread-count')
        const data = await response.json()
        unreadCount.value = data.count || 0
    } catch (error) {
        console.error('Error:', error)
    }
}

const markAsRead = async (id) => {
    try {
        await fetch(`/notifications/${id}/read`, { method: 'POST' })
        const notification = notifications.value.find(n => n.id === id)
        if (notification) {
            notification.read_at = new Date().toISOString()
            unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
    } catch (error) {
        console.error('Error:', error)
    }
}

const markAllAsRead = async () => {
    try {
        await fetch('/notifications/mark-all-read', { method: 'POST' })
        notifications.value.forEach(n => n.read_at = new Date().toISOString())
        unreadCount.value = 0
    } catch (error) {
        console.error('Error:', error)
    }
}

const formatDate = (date) => {
    if (!date) return ''
    const d = new Date(date)
    const now = new Date()
    const diff = Math.floor((now - d) / 1000 / 60)
    
    if (diff < 1) return 'Just now'
    if (diff < 60) return `${diff} min ago`
    if (diff < 1440) return `${Math.floor(diff / 60)} hours ago`
    if (diff < 43200) return `${Math.floor(diff / 1440)} days ago`
    return d.toLocaleDateString()
}

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value
    if (showNotifications.value && notifications.value.length === 0) {
        fetchNotifications()
    }
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (showNotifications.value && !event.target.closest('.notifications-container')) {
        showNotifications.value = false
    }
}

// User info
const userName = computed(() => {
    if (props.auth?.user?.name) {
        return props.auth.user.name.split(' ')[0]
    }
    return 'Student'
})

const fullName = computed(() => {
    return props.auth?.user?.name || 'John Carter'
})

const userEmail = computed(() => {
    return props.auth?.user?.email || 'student@learnhub.com'
})

const logout = () => {
    if (confirm('Are you sure you want to logout?')) {
        router.post(route('logout'))
    }
}

const scrollTo = (id) => {
    isMobileMenuOpen.value = false
    const el = document.getElementById(id)
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' })
    }
}

onMounted(() => {
    initTheme()
    fetchUnreadCount()
    document.addEventListener('click', handleClickOutside)
    setInterval(fetchUnreadCount, 30000)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
    <Head title="Student Dashboard | LearnHub" />
    
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

        <!-- Sidebar - WITHOUT Notifications -->
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
            </div>

            <!-- User Profile -->
            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600 font-bold text-lg">
                        {{ fullName.charAt(0) }}
                    </div>
                    <div>
                        <p class="font-bold dark:text-white">{{ fullName }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Student</p>
                    </div>
                </div>
            </div>

            <!-- Navigation - WITHOUT Notifications -->
            <nav class="flex-1 p-4 space-y-1">
                <button 
                    v-for="item in navItems" 
                    :key="item.id"
                    @click="scrollTo(item.id)"
                    :class="[
                        'flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left',
                        activeTab === item.id 
                            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' 
                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'
                    ]">
                    <component :is="item.icon" class="w-5 h-5" />
                    <span class="font-medium">{{ item.label }}</span>
                </button>
            </nav>

            <!-- Bottom Actions -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-200 dark:border-slate-800 space-y-1">
                <Link href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    <HelpCircle class="w-5 h-5" />
                    <span class="font-medium">Help Center</span>
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
            
            <!-- Top Header with Notification Bell -->
            <header class="bg-white dark:bg-slate-900 sticky top-0 z-20 border-b border-slate-200 dark:border-slate-800">
                <div class="px-4 lg:px-8 py-4 flex items-center justify-between">
                    <div>
                        <h1 class="text-xl lg:text-2xl font-black dark:text-white">Welcome back, {{ userName }}!</h1>
                        <p class="text-xs lg:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Ready to continue your learning journey?</p>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <!-- Notification Bell - Stays Here -->
                        <div class="relative notifications-container">
                            <button 
                                @click="toggleNotifications"
                                class="relative p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors"
                            >
                                <Bell class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                                <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1">
                                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                                </span>
                            </button>

                            <!-- Notifications Dropdown -->
                            <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 z-50 overflow-hidden">
                                <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700">
                                    <h3 class="font-bold dark:text-white">Notifications</h3>
                                    <div class="flex items-center space-x-2">
                                        <button 
                                            v-if="unreadCount > 0"
                                            @click="markAllAsRead"
                                            class="text-xs text-blue-600 hover:text-blue-700"
                                        >
                                            Mark all read
                                        </button>
                                        <button @click="showNotifications = false" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                                            <X class="w-4 h-4 text-slate-500" />
                                        </button>
                                    </div>
                                </div>

                                <div class="max-h-96 overflow-y-auto">
                                    <div v-if="loading" class="p-8 text-center">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                                    </div>
                                    <div v-else-if="notifications.length === 0" class="p-8 text-center text-slate-500">
                                        <Bell class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                        <p>No notifications yet</p>
                                        <p class="text-xs mt-1">We'll notify you when something arrives</p>
                                    </div>
                                    <div v-else>
                                        <div 
                                            v-for="notification in notifications" 
                                            :key="notification.id"
                                            :class="[
                                                'p-4 border-b border-slate-100 dark:border-slate-700 cursor-pointer transition-all',
                                                !notification.read_at ? 'bg-blue-50/50 dark:bg-blue-900/10 hover:bg-blue-100/70' : 'hover:bg-slate-50 dark:hover:bg-slate-700/50'
                                            ]"
                                            @click="markAsRead(notification.id)"
                                        >
                                            <p class="text-sm font-medium dark:text-white">{{ notification.title }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ notification.message }}</p>
                                            <p class="text-xs text-slate-400 mt-2">{{ formatDate(notification.created_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Avatar -->
                        <div class="flex items-center space-x-2 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1.5 rounded-xl transition-colors">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600 font-bold text-sm">
                                {{ fullName.charAt(0) }}
                            </div>
                            <div class="hidden lg:block">
                                <p class="text-sm font-medium dark:text-white">{{ fullName }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Student</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="p-4 lg:p-8">
                
                <!-- Welcome Banner -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-6 lg:p-8 mb-8 text-white">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <p class="text-sm opacity-90 mb-1">Welcome to LearnHub</p>
                            <h2 class="text-2xl lg:text-3xl font-black">Continue Your Learning Journey</h2>
                            <p class="text-sm opacity-90 mt-2">You're making great progress! Keep up the momentum.</p>
                        </div>
                        <button class="px-6 py-3 bg-white text-blue-600 font-bold rounded-xl hover:shadow-lg transition-all">
                            Browse Courses →
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
                            </div>
                            <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                                <component :is="stat.icon" class="w-5 h-5 text-blue-600" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resume Learning & Progress Orbs Row -->
                <div id="courses" class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-8">
                    
                    <!-- Resume Learning -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <h2 class="text-xl font-black dark:text-white mb-4 flex items-center">
                            <Play class="w-5 h-5 mr-2 text-blue-600" />
                            Resume Learning
                        </h2>
                        <div v-for="course in enrolledCourses.slice(0, 1)" :key="course.id" class="space-y-4">
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">COURSE</p>
                                <h3 class="text-lg font-black dark:text-white">{{ course.title }}</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Master {{ course.title.split(' ')[0] }} with an expert and work on real-world, hands-on projects.</p>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-slate-600 dark:text-slate-400">Progress</span>
                                    <span class="font-bold text-blue-600">{{ course.progress }}%</span>
                                </div>
                                <div class="bg-slate-100 dark:bg-slate-800 rounded-full h-2 mb-4">
                                    <div class="bg-blue-600 rounded-full h-2 transition-all duration-500" :style="{ width: course.progress + '%' }"></div>
                                </div>
                                <button class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-colors">
                                    Continue Learning
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Your Progress -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <h2 class="text-xl font-black dark:text-white mb-4">Your Progress</h2>
                        <div class="space-y-6">
                            <div v-for="course in enrolledCourses" :key="course.id" class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="font-medium dark:text-white text-sm">{{ course.title }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ course.lastAccessed }}</p>
                                </div>
                                <div class="relative w-14 h-14">
                                    <svg class="w-14 h-14 transform -rotate-90">
                                        <circle cx="28" cy="28" r="24" stroke="currentColor" stroke-width="3" fill="none" class="text-slate-200 dark:text-slate-700"/>
                                        <circle cx="28" cy="28" r="24" stroke="currentColor" stroke-width="3" fill="none" 
                                            :stroke-dasharray="`${course.progress * 1.5} 151`" 
                                            class="text-blue-600 transition-all duration-500"/>
                                    </svg>
                                    <span class="absolute inset-0 flex items-center justify-center text-xs font-bold dark:text-white">{{ course.progress }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Your Courses & Upcoming Activity Row -->
                <div id="assignments" class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-8">
                    
                    <!-- Your Courses -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-black dark:text-white">Your Courses</h2>
                            <Link href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All →</Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="course in enrolledCourses" :key="course.id" class="flex items-center space-x-4 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all cursor-pointer">
                                <img :src="course.thumbnail" class="w-14 h-14 rounded-xl object-cover" />
                                <div class="flex-1">
                                    <h3 class="font-bold dark:text-white text-sm">{{ course.title }}</h3>
                                    <p class="text-xs text-slate-500">{{ course.instructor }}</p>
                                    <div class="flex items-center mt-2">
                                        <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 mr-3">
                                            <div class="bg-blue-600 rounded-full h-1.5" :style="{ width: course.progress + '%' }"></div>
                                        </div>
                                        <span class="text-xs font-medium text-blue-600">{{ course.progress }}%</span>
                                    </div>
                                </div>
                                <ChevronRight class="w-4 h-4 text-slate-400" />
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Activity -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-black dark:text-white">Upcoming Activity</h2>
                            <Link href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View Calendar →</Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="activity in upcomingActivities" :key="activity.id" 
                                class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all cursor-pointer">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                                        <Calendar class="w-5 h-5 text-blue-600" />
                                    </div>
                                    <div>
                                        <p class="font-medium dark:text-white text-sm">{{ activity.title }}</p>
                                        <p class="text-xs text-slate-500">{{ activity.date }} • {{ activity.time }}</p>
                                    </div>
                                </div>
                                <ChevronRight class="w-4 h-4 text-slate-400" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discussions & Class Schedule Row -->
                <div id="discussions" class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                    
                    <!-- Discussions -->
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-black dark:text-white">Discussions</h2>
                            <Link href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All →</Link>
                        </div>
                        <div class="space-y-4">
                            <div v-for="discussion in discussions" :key="discussion.id" 
                                class="flex items-start space-x-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all cursor-pointer">
                                <MessageCircle class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" />
                                <div>
                                    <h3 class="font-medium dark:text-white text-sm">{{ discussion.title }}</h3>
                                    <p class="text-xs text-slate-500 mt-1">{{ discussion.replies }} replies • {{ discussion.lastActive }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Class Schedule Card -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-6 text-white">
                        <div class="flex flex-col h-full justify-between">
                            <div>
                                <p class="text-xs opacity-80 mb-1 flex items-center">
                                    <Video class="w-3 h-3 mr-1" />
                                    NEXT CLASS
                                </p>
                                <h3 class="text-xl font-black mt-2">Web Development</h3>
                                <p class="text-sm opacity-90 mt-1">Today • 3:00 PM - 5:00 PM</p>
                                <p class="text-xs opacity-75 mt-3 flex items-center">
                                    <Users class="w-3 h-3 mr-1" />
                                    Online Session • 24 students enrolled
                                </p>
                            </div>
                            <button class="mt-6 px-5 py-2.5 bg-white text-blue-600 font-bold rounded-xl hover:shadow-lg transition-all text-sm">
                                Join Session →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Card -->
                <div id="progress" class="mt-8 bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-black dark:text-white">Recent Activity</h2>
                        <span class="text-xs text-blue-600 font-medium">Last 7 days</span>
                    </div>
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center justify-center">
                                <CheckCircle class="w-5 h-5 text-emerald-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium dark:text-white">Completed Vue.js Module</p>
                                <p class="text-xs text-slate-500">2 hours ago</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-50 dark:bg-purple-900/20 rounded-xl flex items-center justify-center">
                                <Award class="w-5 h-5 text-purple-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium dark:text-white">Earned Certificate</p>
                                <p class="text-xs text-slate-500">3 days ago</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                                <MessageCircle class="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-sm font-medium dark:text-white">Posted in Discussions</p>
                                <p class="text-xs text-slate-500">5 days ago</p>
                            </div>
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