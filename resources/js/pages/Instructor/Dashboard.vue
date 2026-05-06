<!-- resources/js/Pages/Instructor/Dashboard.vue -->
<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { 
  BookOpen, Calendar, MessageCircle, Bell, Settings, ChevronRight,
  TrendingUp, Award, Clock, Play, LogOut, HelpCircle, BarChart3,
  Moon, Sun, Home, Users, Video, DollarSign, Star, PlusCircle,
  Edit, Trash2, Eye, CheckCircle, XCircle, Download, Upload, Filter
} from 'lucide-vue-next'

const props = defineProps({
    auth: Object,
    courses: { type: Array, default: () => [] },
    recentEnrollments: { type: Array, default: () => [] },
    pendingReviews: { type: Array, default: () => [] },
    upcomingLiveSessions: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) }
})

// Theme state
const isDarkMode = ref(false)
const isMobileMenuOpen = ref(false)
const activeTab = ref('dashboard')
const showNotificationDropdown = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
let notificationInterval = null

// Loading states
const loading = ref({
  createCourse: false,
  deleteCourse: false,
  approveReview: false
})

// Computed stats
const computedStats = computed(() => [
    { label: 'Total Students', value: props.stats?.total_students || 0, icon: Users, change: '+12%', color: 'blue' },
    { label: 'Total Revenue', value: formatCurrency(props.stats?.total_revenue || 0), icon: DollarSign, change: '+8%', color: 'emerald' },
    { label: 'Active Courses', value: props.stats?.active_courses || 0, icon: BookOpen, change: '+2', color: 'purple' },
    { label: 'Avg. Rating', value: props.stats?.avg_rating || '0', icon: Star, change: '+0.3', color: 'yellow' },
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
    }
}

// Format date for notifications
const formatDate = (date) => {
    if (!date) return ''
    const d = new Date(date)
    const now = new Date()
    const diff = Math.floor((now - d) / 1000 / 60)
    
    if (diff < 1) return 'Just now'
    if (diff < 60) return `${diff} minute${diff > 1 ? 's' : ''} ago`
    if (diff < 1440) return `${Math.floor(diff / 60)} hour${Math.floor(diff / 60) > 1 ? 's' : ''} ago`
    if (diff < 43200) return `${Math.floor(diff / 1440)} day${Math.floor(diff / 1440) > 1 ? 's' : ''} ago`
    return d.toLocaleDateString()
}

// ========== HANDLER FUNCTIONS ==========

// Create Course Handler
const createCourse = () => {
    router.visit(route('instructor.courses.create'))
}

// Edit Course Handler - FIXED to use instructor routes
const editCourse = (courseId) => {
    router.visit(route('instructor.courses.edit', { course: courseId }))
}

// View Course Handler
const viewCourse = (courseId) => {
    router.visit(route('instructor.courses.edit', { course: courseId }))
}

// Delete Course Handler
const deleteCourse = async (courseId, courseTitle) => {
    if (confirm(`Are you sure you want to delete "${courseTitle}"?`)) {
        loading.value.deleteCourse = true
        try {
            await router.delete(route('instructor.courses.destroy', { course: courseId }))
            router.reload()
        } catch (error) {
            console.error('Error deleting course:', error)
            alert('Failed to delete course.')
        } finally {
            loading.value.deleteCourse = false
        }
    }
}

// Approve Review Handler
const approveReview = async (reviewId) => {
    loading.value.approveReview = true
    try {
        await router.patch(route('admin.reviews.toggle', { review: reviewId }))
        router.reload()
    } catch (error) {
        console.error('Error approving review:', error)
        alert('Failed to approve review.')
    } finally {
        loading.value.approveReview = false
    }
}

// Dismiss Review Handler
const dismissReview = async (reviewId) => {
    if (confirm('Dismiss this review?')) {
        try {
            await router.delete(route('admin.reviews.destroy', { review: reviewId }))
            router.reload()
        } catch (error) {
            console.error('Error dismissing review:', error)
            alert('Failed to dismiss review.')
        }
    }
}

// Start Live Session Handler
const startLiveSession = (sessionId) => {
    router.visit(route('admin.courses.show', { course: sessionId }))
}

// Schedule Live Session Handler
const scheduleLiveSession = () => {
    router.visit(route('admin.courses.index'))
}

// Upload Content Handler
const uploadContent = () => {
    router.visit(route('admin.courses.index'))
}

// Export Reports Handler
const exportReports = () => {
    window.location.href = '/admin/reports/export'
}

// Send Announcement Handler
const sendAnnouncement = () => {
    router.visit('/instructor/announcements')
}

// Request Payout Handler
const requestPayout = () => {
    router.visit('/instructor/payouts')
}

// View All Courses Handler
const viewAllCourses = () => {
    router.visit(route('instructor.courses.index'))
}

// View All Enrollments Handler
const viewAllEnrollments = () => {
    router.visit(route('admin.enrollments.index'))
}

// Browse Courses (for create course banner)
const browseCourses = () => {
    router.visit(route('instructor.courses.create'))
}

// Format currency to ETB
const formatCurrency = (amount) => {
    if (!amount) return '0 ብር'
    return new Intl.NumberFormat('en-US').format(amount) + ' ብር'
}

// User info
const userName = computed(() => props.auth?.user?.name?.split(' ')[0] || 'Instructor')
const fullName = computed(() => props.auth?.user?.name || 'Instructor')
const userEmail = computed(() => props.auth?.user?.email || 'instructor@learnhub.com')

// Navigation items
const navItems = [
    { id: 'dashboard', label: 'Dashboard', icon: Home, href: route('instructor.dashboard') },
    { id: 'courses', label: 'My Courses', icon: BookOpen, href: route('instructor.courses.index') },
    { id: 'students', label: 'Students', icon: Users, href: route('admin.enrollments.index') },
    { id: 'analytics', label: 'Analytics', icon: BarChart3, href: '#' },
    { id: 'earnings', label: 'Earnings', icon: DollarSign, href: '#' },
    { id: 'live-sessions', label: 'Live Sessions', icon: Video, href: '#' },
]

const scrollTo = (id) => {
    activeTab.value = id
    isMobileMenuOpen.value = false
}

const logout = () => {
    if (confirm('Are you sure you want to logout?')) {
        router.post(route('logout'))
    }
}

// ========== NOTIFICATION FUNCTIONS ==========

// Fetch notifications from backend
const fetchNotifications = async () => {
    try {
        const response = await fetch('/notifications/json')
        const data = await response.json()
        notifications.value = data.notifications?.data || []
        unreadCount.value = data.unread_count || 0
    } catch (error) {
        console.error('Error fetching notifications:', error)
    }
}

// Mark single notification as read
const markNotificationAsRead = async (id) => {
    try {
        await fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json'
            }
        })
        await fetchNotifications()
    } catch (error) {
        console.error('Error marking notification as read:', error)
        // Optimistic update
        notifications.value = notifications.value.filter(n => n.id !== id)
        unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
}

// Mark all notifications as read
const markAllAsRead = async () => {
    try {
        await fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json'
            }
        })
        await fetchNotifications()
    } catch (error) {
        console.error('Error marking all as read:', error)
        notifications.value = []
        unreadCount.value = 0
    }
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (showNotificationDropdown.value && !event.target.closest('.notifications-container')) {
        showNotificationDropdown.value = false
    }
}

// Navigate to notification action URL
const handleNotificationClick = (notification) => {
    markNotificationAsRead(notification.id)
    if (notification.action_url) {
        router.visit(notification.action_url)
    }
    showNotificationDropdown.value = false
}

onMounted(() => {
    initTheme()
    fetchNotifications()
    // Refresh notifications every 30 seconds
    notificationInterval = setInterval(fetchNotifications, 30000)
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    if (notificationInterval) {
        clearInterval(notificationInterval)
    }
    document.removeEventListener('click', handleClickOutside)
})
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
        <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="lg:hidden fixed top-4 left-4 z-50 w-10 h-10 bg-white dark:bg-slate-800 rounded-xl shadow-md flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Sidebar -->
        <aside :class="[
            'fixed left-0 top-0 h-full bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300 z-40',
            isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0 lg:w-64'
        ]">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg">L</div>
                    <span class="text-2xl font-black tracking-tighter dark:text-white">Learn<span class="text-blue-600">Hub</span></span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Instructor Portal</p>
            </div>

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

            <nav class="flex-1 p-4 space-y-1">
                <button v-for="item in navItems" :key="item.id"
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

            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-200 dark:border-slate-800 space-y-1">
                <Link href="/help" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    <HelpCircle class="w-5 h-5" />
                    <span class="font-medium">Help & Support</span>
                </Link>
                <Link href="/settings" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    <Settings class="w-5 h-5" />
                    <span class="font-medium">Settings</span>
                </Link>
                <button @click="logout" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                    <LogOut class="w-5 h-5" />
                    <span class="font-medium">Logout</span>
                </button>
            </div>
        </aside>

        <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

        <!-- Main Content -->
        <main class="lg:ml-64">
            
            <header class="bg-white dark:bg-slate-900 sticky top-0 z-20 border-b border-slate-200 dark:border-slate-800">
                <div class="px-4 lg:px-8 py-4 flex items-center justify-between">
                    <div>
                        <h1 class="text-xl lg:text-2xl font-black dark:text-white">Welcome back, {{ userName }}!</h1>
                        <p class="text-xs lg:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Here's what's happening with your courses today.</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        
                        <!-- ========== UPDATED NOTIFICATION BELL ========== -->
                        <div class="relative notifications-container">
                            <button @click="showNotificationDropdown = !showNotificationDropdown" class="relative p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                                <Bell class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                                <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1">
                                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                                </span>
                            </button>

                            <!-- Notification Dropdown -->
                            <div v-if="showNotificationDropdown" class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 z-50 overflow-hidden">
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
                                        <button @click="showNotificationDropdown = false" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                                            <XCircle class="w-4 h-4 text-slate-500" />
                                        </button>
                                    </div>
                                </div>

                                <div class="max-h-96 overflow-y-auto">
                                    <div v-if="notifications.length === 0" class="p-8 text-center">
                                        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <Bell class="w-8 h-8 text-slate-400" />
                                        </div>
                                        <p class="text-base font-medium dark:text-white">No notifications</p>
                                        <p class="text-xs text-slate-500 mt-1">You're all caught up!</p>
                                    </div>
                                    
                                    <div v-else>
                                        <div 
                                            v-for="notification in notifications.slice(0, 5)" 
                                            :key="notification.id"
                                            :class="[
                                                'p-4 border-b border-slate-100 dark:border-slate-700 transition-all cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50',
                                                !notification.read_at ? 'bg-blue-50/30 dark:bg-blue-900/10' : ''
                                            ]"
                                            @click="handleNotificationClick(notification)"
                                        >
                                            <div class="flex items-start gap-3">
                                                <div class="flex-shrink-0">
                                                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                                                        <Bell class="w-5 h-5 text-blue-600" />
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold dark:text-white">{{ notification.title }}</p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ notification.message }}</p>
                                                    <p class="text-xs text-slate-400 mt-2">{{ formatDate(notification.created_at) }}</p>
                                                </div>
                                                <div v-if="!notification.read_at" class="flex-shrink-0">
                                                    <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="notifications.length > 0" class="p-3 border-t border-slate-200 dark:border-slate-700 text-center">
                                    <Link :href="route('notifications.index')" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                        View all notifications
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <!-- ========== END NOTIFICATION BELL ========== -->

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

            <div class="p-4 lg:p-8">
                
                <!-- Create Course Banner -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-6 lg:p-8 mb-8 text-white">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <p class="text-sm opacity-90 mb-1">Ready to share your knowledge?</p>
                            <h2 class="text-2xl lg:text-3xl font-black">Create a New Course</h2>
                            <p class="text-sm opacity-90 mt-2">Reach thousands of students worldwide and grow your impact.</p>
                        </div>
                        <button @click="createCourse" class="px-6 py-3 bg-white text-blue-600 font-bold rounded-xl hover:shadow-lg transition-all flex items-center space-x-2">
                            <PlusCircle class="w-4 h-4" />
                            <span>Create Course</span>
                        </button>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
                    <div v-for="stat in computedStats" :key="stat.label" 
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
                        <div class="flex items-center space-x-2">
                            <button class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                                <Filter class="w-4 h-4" />
                            </button>
                            <button @click="viewAllCourses" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All →</button>
                        </div>
                    </div>
                    <div v-if="courses.length === 0" class="text-center py-12 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                        <BookOpen class="w-12 h-12 text-slate-400 mx-auto mb-3" />
                        <p class="text-slate-500">No courses yet. Create your first course!</p>
                        <button @click="createCourse" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-xl">Create Course</button>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div v-for="course in courses" :key="course.id" 
                            class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden hover:shadow-lg transition-all">
                            <img :src="course.thumbnail || 'https://placehold.co/600x400?text=Course'" class="w-full h-40 object-cover" />
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
                                        <span class="text-sm font-medium dark:text-white">{{ course.rating || 0 }}</span>
                                    </div>
                                </div>
                                <h3 class="font-bold dark:text-white mb-2">{{ course.title }}</h3>
                                <div class="flex items-center justify-between text-sm text-slate-500 dark:text-slate-400 mb-3">
                                    <span>{{ course.students || 0 }} students</span>
                                    <span>{{ formatCurrency(course.revenue || 0) }}</span>
                                </div>
                                <div class="mb-3">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span>Progress</span>
                                        <span>{{ course.progress || 0 }}%</span>
                                    </div>
                                    <div class="bg-slate-100 dark:bg-slate-800 rounded-full h-1.5">
                                        <div class="bg-blue-600 rounded-full h-1.5" :style="{ width: (course.progress || 0) + '%' }"></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                                    <span class="text-xs text-slate-500">Updated {{ course.lastUpdated || 'recently' }}</span>
                                    <div class="flex items-center space-x-2">
                                        <button @click="editCourse(course.id)" class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button @click="viewCourse(course.id)" class="p-1.5 text-slate-400 hover:text-green-600 transition-colors">
                                            <Eye class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteCourse(course.id, course.title)" class="p-1.5 text-slate-400 hover:text-red-600 transition-colors">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Enrollments & Upcoming Live Sessions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-8">
                    
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-black dark:text-white">Recent Enrollments</h2>
                            <button @click="viewAllEnrollments" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All →</button>
                        </div>
                        <div v-if="recentEnrollments.length === 0" class="text-center py-8">
                            <p class="text-slate-500">No enrollments yet.</p>
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="enrollment in recentEnrollments.slice(0, 3)" :key="enrollment.id" 
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

                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-black dark:text-white">Upcoming Live Sessions</h2>
                            <button @click="scheduleLiveSession" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center space-x-1">
                                <PlusCircle class="w-3 h-3" />
                                <span>Schedule</span>
                            </button>
                        </div>
                        <div v-if="upcomingLiveSessions.length === 0" class="text-center py-8">
                            <p class="text-slate-500">No upcoming sessions.</p>
                        </div>
                        <div v-else class="space-y-4">
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
                                        <span class="text-xs font-medium text-blue-600">{{ session.attendees || 0 }} attending</span>
                                        <button @click="startLiveSession(session.id)" class="block mt-2 text-xs bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700">
                                            Start
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Reviews & Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-8">
                    
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-black dark:text-white">Pending Reviews</h2>
                            <Link href="/admin/reviews" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All →</Link>
                        </div>
                        <div v-if="pendingReviews.length === 0" class="text-center py-8">
                            <p class="text-slate-500">No pending reviews.</p>
                        </div>
                        <div v-else class="space-y-4">
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
                                                <button @click="approveReview(review.id)" class="text-xs text-emerald-600 hover:text-emerald-700">Approve</button>
                                                <button @click="dismissReview(review.id)" class="text-xs text-red-600 hover:text-red-700">Dismiss</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                        <h2 class="text-xl font-black dark:text-white mb-4">Quick Actions</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <button @click="uploadContent" class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all group">
                                <Upload class="w-6 h-6 text-blue-600 mx-auto mb-2" />
                                <p class="text-sm font-medium dark:text-white">Upload Content</p>
                                <p class="text-xs text-slate-500">Add course materials</p>
                            </button>
                            <button @click="scheduleLiveSession" class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all group">
                                <Video class="w-6 h-6 text-blue-600 mx-auto mb-2" />
                                <p class="text-sm font-medium dark:text-white">Schedule Live</p>
                                <p class="text-xs text-slate-500">Create live session</p>
                            </button>
                            <button @click="exportReports" class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all group">
                                <Download class="w-6 h-6 text-blue-600 mx-auto mb-2" />
                                <p class="text-sm font-medium dark:text-white">Export Reports</p>
                                <p class="text-xs text-slate-500">Download analytics</p>
                            </button>
                            <button @click="sendAnnouncement" class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all group">
                                <MessageCircle class="w-6 h-6 text-blue-600 mx-auto mb-2" />
                                <p class="text-sm font-medium dark:text-white">Announcements</p>
                                <p class="text-xs text-slate-500">Message students</p>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Earnings Overview -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-black dark:text-white">Earnings Overview</h2>
                        <select class="text-sm border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 bg-white dark:bg-slate-800">
                            <option>Last 30 days</option>
                            <option>Last 90 days</option>
                            <option>This year</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <p class="text-3xl font-black dark:text-white">{{ formatCurrency(props.stats?.total_revenue || 0) }}</p>
                            <p class="text-sm text-slate-500 mt-1">Total revenue from all courses</p>
                        </div>
                        <div class="flex items-center space-x-6">
                            <div>
                                <p class="text-sm font-medium dark:text-white">This month</p>
                                <p class="text-xl font-bold text-emerald-600">{{ formatCurrency(props.stats?.monthly_revenue || 0) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium dark:text-white">Last month</p>
                                <p class="text-xl font-bold dark:text-white">{{ formatCurrency(props.stats?.last_month_revenue || 0) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium dark:text-white">Growth</p>
                                <p class="text-xl font-bold text-emerald-600">+21%</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Available for payout</span>
                            <span class="font-bold dark:text-white">{{ formatCurrency(props.stats?.available_payout || 0) }}</span>
                            <button @click="requestPayout" class="text-blue-600 hover:text-blue-700 font-medium">Request Payout →</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
.dark ::-webkit-scrollbar-track { background: #1e293b; }
.dark ::-webkit-scrollbar-thumb { background: #475569; }
</style>