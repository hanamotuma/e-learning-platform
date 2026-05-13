<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { 
  BookOpen, Calendar, Bell, Settings, ChevronRight, TrendingUp, 
  Award, Clock, Play, LogOut, HelpCircle, FileText, Moon, Sun, 
  Home, CheckCircle, Users, X, Star, User, ShoppingCart, Heart,
  ChevronLeft, ChevronDown, Menu, Globe, Shield, Headphones,
  Zap, Target, BarChart3, Briefcase, Code, Palette, Database,
  CreditCard, Gift, Truck, PlusCircle, ExternalLink, Download
} from 'lucide-vue-next'

const page = usePage()
const authUser = computed(() => page.props.auth?.user)
const userFullName = computed(() => authUser.value?.name || authUser.value?.full_name || 'Student')
const userFirstName = computed(() => {
  const name = userFullName.value
  if (name === 'Student') return 'Student'
  return name.split(' ')[0]
})

// State
const isDarkMode = ref(false)
const isMobileMenuOpen = ref(false)
const activeTab = ref('dashboard')
const showNotifications = ref(false)
const myEnrolledCourses = ref([])
const cartItems = ref([])
const notifications = ref([])
const unreadCount = ref(0)
const isLoading = ref(false)
const recentActivity = ref([])

// Fetch enrolled courses from database
const fetchEnrolledCourses = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('/api/user/enrollments')
    myEnrolledCourses.value = response.data.data || []
    console.log('Enrolled courses loaded:', myEnrolledCourses.value.length)
    
    if (myEnrolledCourses.value.length > 0) {
      recentActivity.value = myEnrolledCourses.value.slice(0, 3).map(course => ({
        id: course.id,
        title: `Enrolled in ${course.course?.title}`,
        date: course.enrolled_at,
        type: 'enrollment'
      }))
    }
  } catch (error) {
    console.error('Error fetching enrollments', error)
  } finally {
    isLoading.value = false
  }
}

// Stats
const stats = computed(() => ({
  total_courses: myEnrolledCourses.value.length,
  completed_courses: myEnrolledCourses.value.filter(c => c.progress_percentage >= 100).length,
  in_progress: myEnrolledCourses.value.filter(c => c.progress_percentage > 0 && c.progress_percentage < 100).length,
  avg_progress: myEnrolledCourses.value.length > 0 
    ? Math.round(myEnrolledCourses.value.reduce((sum, c) => sum + (c.progress_percentage || 0), 0) / myEnrolledCourses.value.length) 
    : 0,
  certificates: myEnrolledCourses.value.filter(c => c.progress_percentage >= 100).length,
  total_hours: myEnrolledCourses.value.reduce((sum, c) => sum + (c.course?.hours || 0), 0)
}))

// Fetch cart
const fetchCart = () => {
  const savedCart = localStorage.getItem('savedCart')
  if (savedCart) {
    const cartIds = JSON.parse(savedCart)
    cartItems.value = cartIds
  }
}

const cartCount = computed(() => cartItems.value.length)

// Fetch notifications
const fetchNotifications = async () => {
  try {
    const response = await axios.get('/notifications/json')
    notifications.value = response.data.notifications?.data || []
    unreadCount.value = response.data.unread_count || 0
  } catch (error) {
    console.error('Error fetching notifications', error)
  }
}

// Theme
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

// Actions
const continueLearning = (course) => {
  router.get(`/course/${course.course_id}/learn`)
}

const browseCourses = () => {
  sessionStorage.setItem('scroll_to_courses', 'true')
  router.get('/')
}

const viewCertificate = (course) => {
  if (course.certificate_url) {
    window.open(course.certificate_url, '_blank')
  }
}

const logout = () => {
  if (confirm('Are you sure you want to logout?')) {
    router.post(route('logout'))
  }
}

const formatDate = (date) => {
  if (!date) return 'Not started'
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}

const formatRelativeTime = (date) => {
  if (!date) return ''
  const now = new Date()
  const then = new Date(date)
  const diffDays = Math.floor((now - then) / (1000 * 60 * 60 * 24))
  
  if (diffDays === 0) return 'Today'
  if (diffDays === 1) return 'Yesterday'
  if (diffDays < 7) return `${diffDays} days ago`
  return formatDate(date)
}

const getInitials = (name) => {
  if (!name) return 'S'
  return name.charAt(0).toUpperCase()
}

const getProgressColor = (percentage) => {
  if (percentage >= 100) return 'bg-green-500'
  if (percentage >= 70) return 'bg-blue-500'
  if (percentage >= 40) return 'bg-yellow-500'
  return 'bg-red-500'
}

onMounted(() => {
  initTheme()
  fetchEnrolledCourses()
  fetchCart()
  fetchNotifications()
  
  const shouldRefresh = sessionStorage.getItem('refresh_dashboard') === 'true'
  const enrollmentCompleted = sessionStorage.getItem('enrollment_completed') === 'true'
  
  if (shouldRefresh || enrollmentCompleted) {
    sessionStorage.removeItem('refresh_dashboard')
    sessionStorage.removeItem('enrollment_completed')
    setTimeout(() => {
      fetchEnrolledCourses()
    }, 500)
  }
})
</script>

<template>
  <Head title="Student Dashboard | LearnHub" />
  
  <div class="min-h-screen bg-gray-50 dark:bg-slate-950 transition-colors duration-200">
    <!-- Theme Toggle Button -->
    <button @click="toggleTheme" class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-all duration-300 border border-slate-200 dark:border-slate-700">
      <Sun v-if="isDarkMode" class="w-5 h-5 text-yellow-500" />
      <Moon v-else class="w-5 h-5 text-blue-600" />
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
      <!-- Logo -->
      <Link href="/" class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center space-x-3">
        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-md">L</div>
        <span class="text-2xl font-black tracking-tighter dark:text-white">Learn<span class="text-blue-600">Hub</span></span>
      </Link>

      <!-- User Profile -->
      <div class="p-6 border-b border-slate-200 dark:border-slate-800">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-md">
            {{ getInitials(userFullName) }}
          </div>
          <div>
            <p class="font-bold dark:text-white">{{ userFirstName }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">Student</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 p-4 space-y-1">
        <button @click="activeTab = 'dashboard'" :class="['flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left', activeTab === 'dashboard' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
          <Home class="w-5 h-5" /><span class="font-medium">Dashboard</span>
        </button>
        <button @click="activeTab = 'courses'" :class="['flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left', activeTab === 'courses' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
          <BookOpen class="w-5 h-5" /><span class="font-medium">My Courses</span>
        </button>
        <button @click="activeTab = 'wishlist'" :class="['flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left', activeTab === 'wishlist' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
          <Heart class="w-5 h-5" /><span class="font-medium">Wishlist <span v-if="cartCount > 0" class="ml-1 text-xs bg-red-500 text-white px-1.5 py-0.5 rounded-full">{{ cartCount }}</span></span>
        </button>
        <button @click="activeTab = 'certificates'" :class="['flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left', activeTab === 'certificates' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
          <Award class="w-5 h-5" /><span class="font-medium">Certificates</span>
        </button>
      </nav>

      <!-- Bottom Actions -->
      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-200 dark:border-slate-800 space-y-1">
        <Link :href="route('profile.edit')" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all w-full">
          <User class="w-5 h-5" /><span class="font-medium">Profile</span>
        </Link>
        <button @click="logout" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
          <LogOut class="w-5 h-5" /><span class="font-medium">Logout</span>
        </button>
      </div>
    </aside>

    <!-- Overlay for mobile -->
    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    <!-- Main Content -->
    <main class="lg:ml-64">
      <!-- Header -->
      <header class="bg-white dark:bg-slate-900 sticky top-0 z-20 border-b border-slate-200 dark:border-slate-800">
        <div class="px-4 lg:px-8 py-4 flex items-center justify-between">
          <div>
            <h1 class="text-xl lg:text-2xl font-black dark:text-white">Welcome back, {{ userFirstName }}</h1>
            <p class="text-xs lg:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Track your learning journey and continue where you left off</p>
          </div>
          
          <!-- Notification Bell -->
          <div class="relative">
            <button @click="showNotifications = !showNotifications" class="relative p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
              <Bell class="w-5 h-5 text-slate-600 dark:text-slate-400" />
              <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1">
                {{ unreadCount > 9 ? '9+' : unreadCount }}
              </span>
            </button>

            <!-- Notifications Dropdown -->
            <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 z-50 overflow-hidden">
              <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="font-bold dark:text-white">Notifications</h3>
                <button @click="showNotifications = false" class="text-slate-400 hover:text-slate-600">×</button>
              </div>
              <div class="max-h-96 overflow-y-auto">
                <div v-if="notifications.length === 0" class="p-8 text-center text-slate-500">No notifications</div>
                <div v-for="notif in notifications.slice(0,5)" :key="notif.id" class="p-4 border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer transition-colors">
                  <p class="font-medium text-sm dark:text-white">{{ notif.title }}</p>
                  <p class="text-xs text-slate-500 mt-1">{{ notif.message }}</p>
                  <p class="text-xs text-slate-400 mt-1">{{ formatRelativeTime(notif.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Content Area -->
      <div class="p-4 lg:p-8">
        
        <!-- ==================== DASHBOARD VIEW ==================== -->
        <div v-if="activeTab === 'dashboard'">
          <!-- Welcome Banner -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-6 mb-8 text-white">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
              <div>
                <h2 class="text-2xl font-bold">Ready to learn something new</h2>
                <p class="text-blue-100 mt-1">You have made great progress. Keep up the momentum</p>
              </div>
              <button @click="browseCourses" class="px-6 py-3 bg-white text-blue-600 font-bold rounded-xl hover:shadow-lg transition-all flex items-center gap-2">
                <PlusCircle class="w-4 h-4" /> Browse New Courses
              </button>
            </div>
          </div>

          <!-- Stats Cards -->
          <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Enrolled</p>
                  <p class="text-2xl font-bold dark:text-white">{{ stats.total_courses }}</p>
                </div>
                <BookOpen class="w-8 h-8 text-blue-500 opacity-50" />
              </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Completed</p>
                  <p class="text-2xl font-bold text-green-600">{{ stats.completed_courses }}</p>
                </div>
                <CheckCircle class="w-8 h-8 text-green-500 opacity-50" />
              </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs text-slate-500 dark:text-slate-400">In Progress</p>
                  <p class="text-2xl font-bold text-yellow-600">{{ stats.in_progress }}</p>
                </div>
                <TrendingUp class="w-8 h-8 text-yellow-500 opacity-50" />
              </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Average Progress</p>
                  <p class="text-2xl font-bold text-blue-600">{{ stats.avg_progress }}%</p>
                </div>
                <Target class="w-8 h-8 text-blue-500 opacity-50" />
              </div>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Certificates</p>
                  <p class="text-2xl font-bold text-purple-600">{{ stats.certificates }}</p>
                </div>
                <Award class="w-8 h-8 text-purple-500 opacity-50" />
              </div>
            </div>
          </div>

          <!-- My Courses Section -->
          <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden mb-8">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
              <h2 class="text-xl font-bold dark:text-white">My Courses</h2>
              <button @click="activeTab = 'courses'" class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1">
                View All <ChevronRight class="w-4 h-4" />
              </button>
            </div>
            
            <div class="p-6">
              <div v-if="isLoading" class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="text-slate-500 mt-2">Loading your courses...</p>
              </div>
              
              <div v-else-if="myEnrolledCourses.length > 0" class="space-y-4">
                <div v-for="course in myEnrolledCourses.slice(0, 3)" :key="course.id" class="flex flex-col sm:flex-row gap-4 p-4 border border-slate-200 dark:border-slate-700 rounded-xl hover:shadow-md transition-all">
                  <img :src="course.course?.image" class="w-full sm:w-28 h-28 rounded-xl object-cover" />
                  <div class="flex-1">
                    <h3 class="font-bold text-lg dark:text-white">{{ course.course?.title }}</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">By {{ course.course?.instructor?.name || 'Expert Instructor' }}</p>
                    <div class="flex items-center gap-2 mt-2">
                      <span class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full">{{ course.course?.level || 'Beginner' }}</span>
                      <span class="text-xs text-slate-500">{{ course.course?.hours || 0 }} total hours</span>
                    </div>
                    <div class="mt-3">
                      <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-600 dark:text-slate-400">Progress</span>
                        <span class="font-bold text-blue-600">{{ course.progress_percentage || 0 }}%</span>
                      </div>
                      <div class="bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500" 
                             :class="getProgressColor(course.progress_percentage || 0)"
                             :style="{ width: (course.progress_percentage || 0) + '%' }"></div>
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <button @click="continueLearning(course)" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all text-sm font-medium">
                      {{ (course.progress_percentage || 0) > 0 ? 'Continue' : 'Start Learning' }}
                    </button>
                    <button v-if="course.progress_percentage >= 100" @click="viewCertificate(course)" class="p-2.5 border border-purple-500 text-purple-600 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all">
                      <Award class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
              
              <div v-else class="text-center py-12">
                <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                  <BookOpen class="w-10 h-10 text-slate-400" />
                </div>
                <p class="text-slate-500 dark:text-slate-400">No courses enrolled yet</p>
                <p class="text-sm text-slate-400 mt-1">Complete a payment to see your courses here</p>
                <button @click="browseCourses" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">Browse Courses</button>
              </div>
            </div>
          </div>

          <!-- Recent Activity & Quiz Attempts Row -->
          <div class="grid lg:grid-cols-2 gap-6">
            <!-- Recent Activity -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700">
              <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-xl font-bold dark:text-white">Recent Activity</h2>
              </div>
              <div class="p-6">
                <div v-if="recentActivity.length > 0" class="space-y-4">
                  <div v-for="activity in recentActivity" :key="activity.id" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                      <Play class="w-5 h-5 text-blue-600" />
                    </div>
                    <div class="flex-1">
                      <p class="font-medium dark:text-white">{{ activity.title }}</p>
                      <p class="text-xs text-slate-500">{{ formatRelativeTime(activity.date) }}</p>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8">
                  <p class="text-slate-500">No recent activity</p>
                  <button @click="browseCourses" class="mt-2 text-sm text-blue-600">Start learning today</button>
                </div>
              </div>
            </div>

            <!-- Quiz Attempts -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700">
              <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-xl font-bold dark:text-white">Quiz Attempts</h2>
              </div>
              <div class="p-6">
                <div class="text-center py-8">
                  <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <FileText class="w-8 h-8 text-purple-500" />
                  </div>
                  <p class="text-slate-500 dark:text-slate-400">No quizzes attempted yet</p>
                  <p class="text-xs text-slate-400 mt-1">Complete quizzes in your enrolled courses</p>
                  <button @click="browseCourses" class="mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition-all">
                    Browse Courses
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== MY COURSES VIEW ==================== -->
        <div v-if="activeTab === 'courses'">
          <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
            <div>
              <h1 class="text-2xl font-bold dark:text-white">My Courses</h1>
              <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Continue your learning journey</p>
            </div>
            <button @click="browseCourses" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all flex items-center gap-2">
              <PlusCircle class="w-4 h-4" /> Enroll in New Course
            </button>
          </div>
          
          <div v-if="isLoading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600 mx-auto"></div>
            <p class="text-slate-500 mt-4">Loading your courses...</p>
          </div>
          
          <div v-else-if="myEnrolledCourses.length === 0" class="text-center py-12 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700">
            <div class="w-24 h-24 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
              <BookOpen class="w-12 h-12 text-slate-400" />
            </div>
            <h3 class="text-xl font-bold dark:text-white mb-2">No courses yet</h3>
            <p class="text-slate-500 dark:text-slate-400 mb-6">Start your learning journey by enrolling in a course</p>
            <button @click="browseCourses" class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all flex items-center gap-2 mx-auto">
              <PlusCircle class="w-4 h-4" /> Browse Courses
            </button>
          </div>
          
          <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="course in myEnrolledCourses" :key="course.id" class="bg-white dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all group">
              <div class="relative overflow-hidden h-48">
                <img :src="course.course?.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                <div class="absolute top-3 left-3">
                  <span class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg">{{ course.course?.badge || 'Enrolled' }}</span>
                </div>
                <div class="absolute bottom-3 right-3">
                  <span class="text-xs px-2 py-1 bg-black/50 text-white rounded-lg">{{ course.progress_percentage || 0 }}% Complete</span>
                </div>
              </div>
              <div class="p-5">
                <div class="flex items-center gap-2 mb-2">
                  <Star class="w-4 h-4 text-yellow-400 fill-yellow-400" />
                  <span class="text-sm font-medium">{{ course.course?.rating || 4.8 }}</span>
                  <span class="text-xs text-slate-400">({{ course.course?.reviews || 0 }} reviews)</span>
                </div>
                <h3 class="font-bold text-lg mb-2 line-clamp-2 dark:text-white group-hover:text-blue-600 transition-colors cursor-pointer" @click="continueLearning(course)">
                  {{ course.course?.title }}
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">{{ course.course?.instructor?.name || 'Expert Instructor' }}</p>
                <div class="flex items-center justify-between text-xs text-slate-500 mb-3">
                  <div class="flex items-center gap-1">
                    <Clock class="w-3 h-3" />
                    <span>{{ course.course?.hours || 0 }} hours</span>
                  </div>
                  <div class="flex items-center gap-1">
                    <Users class="w-3 h-3" />
                    <span>{{ course.course?.students || 0 }} students</span>
                  </div>
                </div>
                <div class="mb-4">
                  <div class="bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500" 
                         :class="getProgressColor(course.progress_percentage || 0)"
                         :style="{ width: (course.progress_percentage || 0) + '%' }"></div>
                  </div>
                </div>
                <div class="flex gap-2">
                  <button @click="continueLearning(course)" class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all text-sm font-medium">
                    {{ (course.progress_percentage || 0) > 0 ? 'Continue Learning' : 'Start Course' }}
                  </button>
                  <button v-if="course.progress_percentage >= 100 && !course.certificate_issued" @click="viewCertificate(course)" class="px-4 py-2.5 border border-purple-500 text-purple-600 rounded-xl hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all">
                    <Award class="w-4 h-4" />
                  </button>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-400">
                  Enrolled: {{ formatDate(course.enrolled_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== WISHLIST VIEW ==================== -->
        <div v-if="activeTab === 'wishlist'">
          <h1 class="text-2xl font-bold mb-6 dark:text-white">My Wishlist</h1>
          <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
            <div v-if="cartCount === 0" class="text-center py-12">
              <Heart class="w-20 h-20 text-slate-300 mx-auto mb-4" />
              <p class="text-slate-500 dark:text-slate-400">Your wishlist is empty</p>
              <button @click="browseCourses" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">Browse Courses</button>
            </div>
            <div v-else class="space-y-4">
              <div v-for="courseId in cartItems" :key="courseId" class="flex justify-between items-center p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
                <div>
                  <p class="font-bold dark:text-white">Course ID: {{ courseId }}</p>
                  <p class="text-sm text-slate-500">Added to your wishlist</p>
                </div>
                <Link :href="`/course/${courseId}`" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all flex items-center gap-2">
                  View Course <ExternalLink class="w-4 h-4" />
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== CERTIFICATES VIEW ==================== -->
        <div v-if="activeTab === 'certificates'">
          <h1 class="text-2xl font-bold mb-6 dark:text-white">My Certificates</h1>
          <div class="grid md:grid-cols-2 gap-6">
            <div v-for="course in myEnrolledCourses.filter(c => c.progress_percentage >= 100)" :key="'cert-' + course.id" class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 text-center hover:shadow-lg transition-all">
              <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <Award class="w-10 h-10 text-white" />
              </div>
              <h3 class="font-bold text-lg dark:text-white mb-2">{{ course.course?.title }}</h3>
              <p class="text-sm text-slate-500 mb-2">Completed: {{ formatDate(course.completed_at || course.updated_at) }}</p>
              <p class="text-xs text-slate-400 mb-4">Certificate ID: CERT-{{ course.id }}-{{ course.user_id }}</p>
              <button @click="viewCertificate(course)" class="px-5 py-2.5 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all flex items-center justify-center gap-2 mx-auto">
                <Download class="w-4 h-4" /> Download Certificate
              </button>
            </div>
            <div v-if="myEnrolledCourses.filter(c => c.progress_percentage >= 100).length === 0" class="col-span-2 text-center py-12 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700">
              <Award class="w-20 h-20 text-slate-300 mx-auto mb-4" />
              <p class="text-slate-500 dark:text-slate-400">No certificates yet</p>
              <p class="text-sm text-slate-400 mt-1">Complete courses to earn certificates</p>
              <button @click="browseCourses" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">Browse Courses</button>
            </div>
          </div>
        </div>
        
      </div>
    </main>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
.animate-spin {
  animation: spin 1s linear infinite;
}
</style>