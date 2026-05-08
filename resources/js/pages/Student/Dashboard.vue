<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { 
  BookOpen, Bell, ChevronRight, TrendingUp, 
  Award, Play, LogOut, User, Moon, Sun, 
  Home, CheckCircle, Heart, Menu, Star
} from 'lucide-vue-next'

const page = usePage()

const props = defineProps({
  stats: Object,
  enrolledCourses: Array,
  recommendedCourses: Array,
  auth: Object
})

// Get user name
const userFullName = computed(() => {
  if (props.auth?.user?.name) return props.auth.user.name
  if (page.props.auth?.user?.name) return page.props.auth.user.name
  if (props.auth?.user?.full_name) return props.auth.user.full_name
  return 'Student'
})

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
const enrolledCoursesList = ref([])
const cartItems = ref([])
const notifications = ref([])
const unreadCount = ref(0)
const isLoading = ref(true)

// Fetch enrolled courses from database
const fetchEnrolledCourses = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('/api/user/enrollments')
    enrolledCoursesList.value = response.data.data || []
    console.log('Loaded courses:', enrolledCoursesList.value.length)
  } catch (error) {
    console.error('Error fetching enrollments', error)
  } finally {
    isLoading.value = false
  }
}

// Fetch cart/wishlist
const fetchCart = () => {
  const savedCart = localStorage.getItem('savedCart')
  if (savedCart) {
    const cartIds = JSON.parse(savedCart)
    cartItems.value = cartIds
  }
}

const cartCount = computed(() => cartItems.value.length)

// Stats
const statsData = computed(() => ({
  total_courses: enrolledCoursesList.value.length,
  completed_courses: enrolledCoursesList.value.filter(c => c.progress_percentage >= 100).length,
  avg_progress: enrolledCoursesList.value.length > 0 
    ? Math.round(enrolledCoursesList.value.reduce((sum, c) => sum + (c.progress_percentage || 0), 0) / enrolledCoursesList.value.length) 
    : 0,
  certificates: enrolledCoursesList.value.filter(c => c.certificate_issued).length
}))

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
  router.get('/')
}

const logout = () => {
  if (confirm('Are you sure you want to logout?')) {
    router.post(route('logout'))
  }
}

const formatDate = (date) => {
  if (!date) return 'Not started'
  return new Date(date).toLocaleDateString()
}

const getInitials = (name) => {
  if (!name) return 'S'
  return name.charAt(0).toUpperCase()
}

const generateCertificate = (courseId) => {
    router.get(`/certificate/generate/${courseId}`)
}

// Refresh data periodically
const refreshData = () => {
  fetchEnrolledCourses()
}

onMounted(() => {
  initTheme()
  
  
  // Check if we need to refresh after enrollment
  const shouldRefresh = sessionStorage.getItem('refresh_dashboard') === 'true'
  const enrollmentCompleted = sessionStorage.getItem('enrollment_completed') === 'true'
  
  if (shouldRefresh || enrollmentCompleted) {
    sessionStorage.removeItem('refresh_dashboard')
    sessionStorage.removeItem('enrollment_completed')
    // Force refresh the data
    setTimeout(() => {
      fetchEnrolledCourses()
    }, 500)
  } else {
    fetchEnrolledCourses()
  }
  
  fetchCart()
  fetchNotifications()
  setInterval(fetchNotifications, 30000)
  setInterval(refreshData, 10000) // Refresh every 10 seconds
})
</script>

<template>
  <Head title="Student Dashboard | LearnHub" />
  
  <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
    <!-- Theme Toggle -->
    <button @click="toggleTheme" class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-white dark:bg-slate-800 rounded-xl shadow-lg flex items-center justify-center hover:scale-105 transition-all">
      <Sun v-if="isDarkMode" class="w-5 h-5 text-yellow-500" />
      <Moon v-else class="w-5 h-5 text-slate-700" />
    </button>

    <!-- Mobile Menu Button -->
    <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="lg:hidden fixed top-4 left-4 z-50 w-10 h-10 bg-white dark:bg-slate-800 rounded-xl shadow-md flex items-center justify-center">
      <Menu class="w-5 h-5" />
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
          <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600 font-bold text-lg">
            {{ getInitials(userFullName) }}
          </div>
          <div>
            <p class="font-bold dark:text-white">{{ userFirstName }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">Student</p>
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
        <button @click="activeTab = 'wishlist'" :class="['flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all', activeTab === 'wishlist' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50']">
          <Heart class="w-5 h-5" /><span>Wishlist ({{ cartCount }})</span>
        </button>
        <button @click="activeTab = 'certificates'" :class="['flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all', activeTab === 'certificates' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50']">
          <Award class="w-5 h-5" /><span>Certificates</span>
        </button>
      </nav>

      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-200 dark:border-slate-800">
        <Link :href="route('profile.edit')" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 w-full">
          <User class="w-5 h-5" /><span>Profile</span>
        </Link>
        <button @click="logout" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50">
          <LogOut class="w-5 h-5" /><span>Logout</span>
        </button>
      </div>
    </aside>

    <!-- Overlay -->
    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    <!-- Main Content -->
    <main class="lg:ml-64">
      <header class="bg-white dark:bg-slate-900 sticky top-0 z-20 border-b">
        <div class="px-4 lg:px-8 py-4 flex items-center justify-between">
          <div>
            <h1 class="text-xl lg:text-2xl font-black dark:text-white">Welcome back, {{ userFirstName }}!</h1>
            <p class="text-xs text-slate-500">Track your learning journey</p>
          </div>
          <div class="relative">
            <button @click="showNotifications = !showNotifications" class="relative p-2 hover:bg-slate-100 rounded-xl">
              <Bell class="w-5 h-5" />
              <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
            </button>
            <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border z-50">
              <div class="p-4 border-b">
                <h3 class="font-bold">Notifications</h3>
              </div>
              <div class="max-h-96 overflow-y-auto">
                <div v-if="notifications.length === 0" class="p-8 text-center text-slate-500">No notifications</div>
                <div v-for="notif in notifications.slice(0,5)" :key="notif.id" class="p-4 border-b">
                  <p class="font-medium text-sm">{{ notif.title }}</p>
                  <p class="text-xs text-slate-500">{{ notif.message }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <div class="p-4 lg:p-8">
        <!-- Dashboard View -->
        <div v-if="activeTab === 'dashboard'">
          <!-- Stats Cards -->
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border">
              <p class="text-xs text-slate-500">Enrolled</p>
              <p class="text-2xl font-bold">{{ statsData.total_courses }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border">
              <p class="text-xs text-slate-500">Completed</p>
              <p class="text-2xl font-bold text-green-600">{{ statsData.completed_courses }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border">
              <p class="text-xs text-slate-500">Avg Progress</p>
              <p class="text-2xl font-bold text-blue-600">{{ statsData.avg_progress }}%</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border">
              <p class="text-xs text-slate-500">Certificates</p>
              <p class="text-2xl font-bold text-purple-600">{{ statsData.certificates }}</p>
            </div>
          </div>

          <!-- My Courses Section - DYNAMICALLY SHOWS ENROLLED COURSES -->
          <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border">
            <div class="flex justify-between mb-4">
              <h2 class="text-xl font-bold">My Courses</h2>
              <button @click="activeTab = 'courses'" class="text-sm text-blue-600">View All →</button>
            </div>
            
            <!-- Show enrolled courses after payment -->
            <div v-if="isLoading" class="text-center py-8">Loading your courses...</div>
            <div v-else-if="enrolledCoursesList.length > 0" class="space-y-4">
              <div v-for="course in enrolledCoursesList.slice(0,3)" :key="course.id" class="flex gap-4 p-4 border rounded-xl">
                <img :src="course.course?.image" class="w-24 h-24 rounded-xl object-cover" />
                <div class="flex-1">
                  <h3 class="font-bold">{{ course.course?.title }}</h3>
                  <p class="text-sm text-slate-500">{{ course.course?.instructor?.name || 'Expert Instructor' }}</p>
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
                <button @click="continueLearning(course)" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">{{ (course.progress_percentage || 0) > 0 ? 'Continue' : 'Start' }}</button>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <BookOpen class="w-16 h-16 text-slate-300 mx-auto mb-4" />
              <p class="text-slate-500">No courses enrolled yet</p>
              <p class="text-sm text-slate-400 mt-2">After successful payment, your courses will appear here</p>
              <button @click="browseCourses" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg">Browse Courses</button>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="mt-6 bg-white dark:bg-slate-900 rounded-2xl p-6 border">
            <h2 class="text-xl font-bold mb-4">Recent Activity</h2>
            <div class="space-y-3">
              <div v-for="course in enrolledCoursesList.slice(0,2)" :key="'activity-'+course.id" class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                  <Play class="w-5 h-5 text-blue-600" />
                </div>
                <div class="flex-1">
                  <p class="font-medium">Enrolled in {{ course.course?.title }}</p>
                  <p class="text-sm text-slate-500">{{ formatDate(course.enrolled_at) }}</p>
                </div>
              </div>
              <div v-if="enrolledCoursesList.length === 0" class="text-center py-8 text-slate-500">No recent activity</div>
            </div>
          </div>
        </div>

        <!-- My Courses View - FULL LIST -->
        <div v-if="activeTab === 'courses'">
          <h1 class="text-2xl font-bold mb-6">My Courses</h1>
          <div v-if="isLoading" class="text-center py-12">Loading your courses...</div>
          <div v-else-if="enrolledCoursesList.length === 0" class="text-center py-12">
            <BookOpen class="w-20 h-20 text-slate-300 mx-auto mb-4" />
            <p class="text-slate-500">No courses enrolled yet</p>
            <button @click="browseCourses" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg">Browse Courses</button>
          </div>
          <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="course in enrolledCoursesList" :key="course.id" class="bg-white dark:bg-slate-900 rounded-xl overflow-hidden border">
              <img :src="course.course?.image" class="w-full h-48 object-cover" />
              <div class="p-5">
                <h3 class="font-bold text-lg mb-1 line-clamp-2">{{ course.course?.title }}</h3>
                <p class="text-sm text-slate-500 mb-3">{{ course.course?.instructor?.name || 'Expert Instructor' }}</p>
                <div class="mb-3">
                  <div class="flex justify-between text-sm mb-1">
                    <span>Progress</span>
                    <span class="font-bold text-blue-600">{{ course.progress_percentage || 0 }}%</span>
                  </div>
                  <div class="bg-slate-200 rounded-full h-2">
                    <div class="bg-blue-600 rounded-full h-2" :style="{ width: (course.progress_percentage || 0) + '%' }"></div>
                  </div>
                </div>
                <button @click="continueLearning(course)" class="w-full py-2 bg-blue-600 text-white rounded-lg text-sm">{{ (course.progress_percentage || 0) > 0 ? 'Continue' : 'Start' }}</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Wishlist View -->
        <div v-if="activeTab === 'wishlist'">
          <h1 class="text-2xl font-bold mb-6">My Wishlist</h1>
          <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border">
            <div v-if="cartCount === 0" class="text-center py-12">
              <Heart class="w-20 h-20 text-slate-300 mx-auto mb-4" />
              <p class="text-slate-500">Your wishlist is empty</p>
              <button @click="browseCourses" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg">Browse Courses</button>
            </div>
            <div v-else class="space-y-4">
              <div v-for="courseId in cartItems" :key="courseId" class="flex justify-between items-center p-4 border rounded-xl">
                <div>
                  <p class="font-bold">Course ID: {{ courseId }}</p>
                  <p class="text-sm text-slate-500">Added to your wishlist</p>
                </div>
                <Link :href="`/course/${courseId}`" class="px-4 py-2 bg-blue-600 text-white rounded-lg">View Course</Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Certificates View -->
       <!-- Certificates View -->
<div v-if="activeTab === 'certificates'">
    <h1 class="text-2xl font-bold mb-6">My Certificates</h1>
    <div class="grid md:grid-cols-2 gap-6">
        <!-- Show certificates for enrolled courses -->
        <div v-for="course in enrolledCoursesList" :key="'cert-' + course.id" class="bg-white dark:bg-slate-900 rounded-xl p-6 border text-center">
            <Award class="w-16 h-16 text-purple-600 mx-auto mb-4" />
            <h3 class="font-bold text-lg">{{ course.course?.title }}</h3>
            <p class="text-sm text-slate-500 mb-4">Progress: {{ course.progress_percentage || 0 }}%</p>
            <button 
                @click="generateCertificate(course.course_id)" 
                :disabled="course.progress_percentage < 100"
                class="px-4 py-2 rounded-lg"
                :class="course.progress_percentage >= 100 ? 'bg-purple-600 text-white hover:bg-purple-700' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
            >
                {{ course.progress_percentage >= 100 ? 'Download Certificate' : 'Complete course first' }}
            </button>
        </div>
        <div v-if="enrolledCoursesList.length === 0" class="col-span-2 text-center py-12">
            <Award class="w-20 h-20 text-slate-300 mx-auto mb-4" />
            <p class="text-slate-500">No certificates yet. Enroll in courses to earn certificates!</p>
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
</style>