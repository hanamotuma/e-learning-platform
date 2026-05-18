<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { 
  BookOpen, Play, Clock, Award, Star, TrendingUp, Calendar, 
  ChevronRight, CheckCircle, Users, Video, FileText, Target, 
  Eye, Heart, Download, Share2, Bell, Settings, LogOut,
  Moon, Sun, Menu, X, Home, MessageCircle, HelpCircle, Zap,
  Flame, Trophy, BarChart3, Activity, Coffee, Gift, Sparkles,
  GraduationCap, ThumbsUp, AlertCircle, Loader2, User, Mail, Phone, Camera, Save
} from 'lucide-vue-next'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  user: Object,
  stats: Object,
  enrollments: Array,
  certificates: Array,
  wishlist: Array,
  recentActivities: Array,
  recentQuizzes: Array,
  unreadNotifications: Number,
  continueCourse: Object,
  weeklyData: Array,
  monthlyData: Array,
  achievements: Array,
  upcomingDeadlines: Array,
  recommendedCourses: Array
})

// State
const isDarkMode = ref(false)
const isMobileMenuOpen = ref(false)
const showNotifications = ref(false)
const activeTab = ref('dashboard')
const isLoading = ref(false)
const notifications = ref([])
const learningChart = ref(null)
const quizChart = ref(null)
const showSuccess = ref(false)
const showError = ref(false)
const errorMessage = ref('')
const imagePreview = ref(props.user?.profile_picture_url || null)

// Settings Form
const settingsForm = useForm({
  name: props.user?.name || '',
  email: props.user?.email || '',
  phone: props.user?.phone || '',
  bio: props.user?.bio || '',
  profile_picture: null
})

const browseCourses = () => {
  router.get('/')
}

// Methods
const formatDate = (date) => {
  if (!date) return 'Not started'
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US').format(price || 0) + ' ETB'
}

const getProgressColor = (percentage) => {
  if (percentage >= 100) return 'bg-emerald-500'
  if (percentage >= 70) return 'bg-blue-500'
  if (percentage >= 40) return 'bg-amber-500'
  return 'bg-rose-500'
}

const getLevelBadge = (level) => {
  const badges = {
    beginner: 'bg-emerald-100 text-emerald-700',
    intermediate: 'bg-blue-100 text-blue-700',
    advanced: 'bg-purple-100 text-purple-700'
  }
  return badges[level?.toLowerCase()] || 'bg-gray-100 text-gray-700'
}

const continueLearning = (courseId) => {
  router.get(`/course/${courseId}/learn`)
}

const viewCourse = (courseId) => {
  router.get(`/course/${courseId}`)
}

const downloadCertificate = (certificate) => {
  if (certificate.certificate_url) {
    window.open(certificate.certificate_url, '_blank')
  } else {
    router.post(`/certificate/generate/${certificate.course_id}`)
  }
}

const addToCart = (course) => {
  let cart = JSON.parse(localStorage.getItem('savedCart') || '[]')
  if (!cart.includes(course.course_id)) {
    cart.push(course.course_id)
    localStorage.setItem('savedCart', JSON.stringify(cart))
    alert(`${course.title} added to cart!`)
  }
}

const removeFromWishlist = (courseId) => {
  let cart = JSON.parse(localStorage.getItem('savedCart') || '[]')
  cart = cart.filter(id => id !== courseId)
  localStorage.setItem('savedCart', JSON.stringify(cart))
  window.location.reload()
}

const getActivityIcon = (type) => {
  const icons = {
    enrollment: BookOpen,
    completion: CheckCircle,
    quiz: Award,
    certificate: Trophy
  }
  return icons[type] || Bell
}

const getActivityColor = (color) => {
  const colors = {
    blue: 'bg-blue-100 text-blue-600',
    green: 'bg-emerald-100 text-emerald-600',
    yellow: 'bg-amber-100 text-amber-600',
    purple: 'bg-purple-100 text-purple-600'
  }
  return colors[color] || 'bg-gray-100 text-gray-600'
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

const logout = () => {
  if (confirm('Are you sure you want to logout?')) {
    router.post(route('logout'))
  }
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg']
    if (!allowedTypes.includes(file.type)) {
      errorMessage.value = 'Please upload JPG or PNG image'
      showError.value = true
      setTimeout(() => showError.value = false, 3000)
      return
    }
    
    if (file.size > 5 * 1024 * 1024) {
      errorMessage.value = 'Image size must be less than 5MB'
      showError.value = true
      setTimeout(() => showError.value = false, 3000)
      return
    }
    
    settingsForm.profile_picture = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const updateProfile = () => {
  settingsForm.patch('/profile', {
    preserveScroll: true,
    onSuccess: () => {
      showSuccess.value = true
      setTimeout(() => {
        showSuccess.value = false
        router.reload()
      }, 2000)
    },
    onError: (errors) => {
      errorMessage.value = Object.values(errors).join(', ')
      showError.value = true
      setTimeout(() => showError.value = false, 3000)
    }
  })
}

const initTheme = () => {
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'dark') {
    isDarkMode.value = true
    document.documentElement.classList.add('dark')
  }
}

// Chart initialization
const initCharts = async () => {
  if (typeof window !== 'undefined') {
    const Chart = (await import('chart.js')).default
    
    const weeklyCtx = document.getElementById('weeklyChart')?.getContext('2d')
    if (weeklyCtx && props.weeklyData) {
      new Chart(weeklyCtx, {
        type: 'line',
        data: {
          labels: props.weeklyData.map(d => d.day),
          datasets: [{
            label: 'Learning Hours',
            data: props.weeklyData.map(d => d.hours),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#fff',
            pointRadius: 4,
            pointHoverRadius: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'top' } }
        }
      })
    }
    
    const quizCtx = document.getElementById('quizChart')?.getContext('2d')
    if (quizCtx && props.recentQuizzes) {
      new Chart(quizCtx, {
        type: 'bar',
        data: {
          labels: props.recentQuizzes.slice(0, 5).map((_, i) => `Quiz ${i + 1}`),
          datasets: [{
            label: 'Score (%)',
            data: props.recentQuizzes.slice(0, 5).map(q => q.score),
            backgroundColor: '#8b5cf6',
            borderRadius: 8,
            barPercentage: 0.6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: { y: { max: 100, beginAtZero: true } }
        }
      })
    }
  }
}

onMounted(() => {
  initTheme()
  initCharts()
})
</script>

<template>
  <Head title="Student Dashboard | LearnHub" />
  
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900">
    
    <!-- Mobile Menu Button -->
    <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="lg:hidden fixed top-4 left-4 z-50 w-11 h-11 bg-white dark:bg-slate-800 rounded-xl shadow-lg flex items-center justify-center border border-slate-200">
      <Menu v-if="!isMobileMenuOpen" class="w-5 h-5" />
      <X v-else class="w-5 h-5" />
    </button>
    
    <!-- Theme Toggle -->
    <button @click="toggleTheme" class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-white dark:bg-slate-800 rounded-full shadow-xl flex items-center justify-center hover:scale-110 transition-all duration-300 border">
      <Sun v-if="isDarkMode" class="w-5 h-5 text-yellow-500" />
      <Moon v-else class="w-5 h-5 text-slate-700" />
    </button>
    
    <!-- Sidebar -->
    <aside :class="[
      'fixed left-0 top-0 h-full bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300 z-40 flex flex-col',
      isMobileMenuOpen ? 'translate-x-0 w-72' : '-translate-x-full lg:translate-x-0 lg:w-72'
    ]">
      <!-- Logo -->
      <div class="p-6 border-b">
        <Link href="/" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-500 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg group-hover:scale-105 transition-transform">
            L
          </div>
          <div>
            <span class="text-2xl font-black tracking-tight dark:text-white">Learn<span class="text-blue-600">Hub</span></span>
            <p class="text-xs text-slate-500 mt-0.5">Student Portal</p>
          </div>
        </Link>
      </div>
      
      <!-- User Profile Card -->
      <div class="m-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 rounded-2xl border border-blue-100 dark:border-blue-900/30">
        <div class="flex items-center space-x-3">
          <div class="relative">
            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-md">
              {{ user?.name?.charAt(0) || 'S' }}
            </div>
            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white dark:border-slate-900"></div>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-bold dark:text-white truncate">{{ user?.name || 'Student' }}</p>
            <p class="text-xs text-slate-500 truncate">{{ user?.email }}</p>
            <div class="flex items-center gap-1 mt-1">
              <Flame class="w-3 h-3 text-orange-500" />
              <span class="text-xs font-medium text--600">{{ user?.learning_streak || 0 }} day streak</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Navigation -->
      <nav class="flex-1 p-4 space-y-1">
        <button @click="activeTab = 'dashboard'" :class="[
          'flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left',
          activeTab === 'dashboard' 
            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 shadow-sm' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'
        ]">
          <Home class="w-5 h-5" />
          <span class="font-medium">Dashboard</span>
        </button>
        
        <button @click="activeTab = 'courses'" :class="[
          'flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left',
          activeTab === 'courses' 
            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 shadow-sm' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'
        ]">
          <BookOpen class="w-5 h-5" />
          <span class="font-medium">My Courses</span>
          <span v-if="stats?.in_progress > 0" class="ml-auto text-xs bg-blue-500 text-white px-2 py-0.5 rounded-full">{{ stats.in_progress }}</span>
        </button>
        
        <button @click="activeTab = 'certificates'" :class="[
          'flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left',
          activeTab === 'certificates' 
            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 shadow-sm' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'
        ]">
          <Award class="w-5 h-5" />
          <span class="font-medium">Certificates</span>
          <span v-if="stats?.certificates_earned > 0" class="ml-auto text-xs text-emerald-600">{{ stats.certificates_earned }}</span>
        </button>
        
        <button @click="activeTab = 'wishlist'" :class="[
          'flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left',
          activeTab === 'wishlist' 
            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 shadow-sm' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'
        ]">
          <Heart class="w-5 h-5" />
          <span class="font-medium">Wishlist</span>
          <span v-if="stats?.wishlist_count > 0" class="ml-auto text-xs text-rose-500">{{ stats.wishlist_count }}</span>
        </button>
        
        <button @click="activeTab = 'achievements'" :class="[
          'flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left',
          activeTab === 'achievements' 
            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 shadow-sm' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'
        ]">
          <Trophy class="w-5 h-5" />
          <span class="font-medium">Achievements</span>
        </button>
        
        <button @click="activeTab = 'analytics'" :class="[
          'flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left',
          activeTab === 'analytics' 
            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 shadow-sm' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'
        ]">
          <BarChart3 class="w-5 h-5" />
          <span class="font-medium">Analytics</span>
        </button>
        
        <!-- Settings Button - ADDED -->
        <button @click="activeTab = 'settings'" :class="[
          'flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left',
          activeTab === 'settings' 
            ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 shadow-sm' 
            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'
        ]">
          <Settings class="w-5 h-5" />
          <span class="font-medium">Settings</span>
        </button>
      </nav>
      
      <!-- Bottom Actions -->
      <div class="p-4 border-t space-y-2">
        <button @click="logout" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all">
          <LogOut class="w-5 h-5" />
          <span class="font-medium">Logout</span>
        </button>
      </div>
    </aside>
    
    <!-- Overlay -->
    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>
    
    <!-- Main Content -->
    <main class="lg:ml-72">
      
      <!-- ==================== DASHBOARD VIEW ==================== -->
      <div v-if="activeTab === 'dashboard'" class="p-4 md:p-6 lg:p-8">
        <!-- Welcome Hero Banner -->
        <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 rounded-2xl p-6 md:p-8 mb-8 text-white">
          <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
          <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full -ml-24 -mb-24"></div>
          
          <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
              <h1 class="text-2xl md:text-3xl font-bold">Welcome back, {{ user?.name?.split(' ')[0] || 'Student' }}! </h1>
              <p class="text-blue-100 mt-1">Continue your learning journey. You're making great progress!</p>
              <div class="flex items-center gap-3 mt-3">
              </div>
            </div>
            
          </div>
        </div>
        
        <!-- Stats Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
          <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-slate-500">Enrolled</p>
                <p class="text-2xl font-bold dark:text-white">{{ stats?.total_courses || 0 }}</p>
              </div>
              <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <BookOpen class="w-5 h-5 text-blue-600" />
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-slate-500">Completed</p>
                <p class="text-2xl font-bold text-emerald-600">{{ stats?.completed_courses || 0 }}</p>
              </div>
              <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                <CheckCircle class="w-5 h-5 text-emerald-600" />
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-slate-500">In Progress</p>
                <p class="text-2xl font-bold text-amber-600">{{ stats?.in_progress || 0 }}</p>
              </div>
              <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                <TrendingUp class="w-5 h-5 text-amber-600" />
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-slate-500">Avg Progress</p>
                <p class="text-2xl font-bold text-purple-600">{{ stats?.average_progress || 0 }}%</p>
              </div>
              <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <Target class="w-5 h-5 text-purple-600" />
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-slate-500">Certificates</p>
                <p class="text-2xl font-bold text-amber-600">{{ stats?.certificates_earned || 0 }}</p>
              </div>
              <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                <Award class="w-5 h-5 text-amber-600" />
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-slate-500">Learning Hours</p>
                <p class="text-2xl font-bold text-cyan-600">{{ stats?.total_hours || 0 }}</p>
              </div>
              <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-xl flex items-center justify-center">
                <Clock class="w-5 h-5 text-cyan-600" />
              </div>
            </div>
          </div>
        </div>
        
        <!-- Two Column Layout -->
        <div class="grid lg:grid-cols-3 gap-6">
          <!-- Left Column -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Continue Learning Section -->
            <div v-if="continueCourse" class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl overflow-hidden shadow-lg">
              <div class="flex flex-col md:flex-row">
                <div class="flex-1 p-6 text-white">
                  <p class="text-sm text-blue-100 mb-1">Continue Learning</p>
                  <h3 class="text-xl font-bold mb-2">{{ continueCourse.title }}</h3>
                  <div class="mb-3">
                    <div class="flex justify-between text-sm mb-1">
                      <span>{{ continueCourse.progress }}% Complete</span>
                    </div>
                    <div class="bg-white/30 rounded-full h-2">
                      <div class="bg-white h-2 rounded-full" :style="{ width: continueCourse.progress + '%' }"></div>
                    </div>
                  </div>
                  <Link :href="`/course/${continueCourse.id}/learn`" class="inline-flex items-center gap-2 mt-2 px-4 py-2 bg-white text-blue-600 rounded-lg font-medium hover:shadow-md transition-all">
                    <Play class="w-4 h-4" /> Resume Course
                  </Link>
                </div>
                <div class="w-full md:w-32 h-32 md:h-auto bg-cover bg-center" :style="{ backgroundImage: `url(${continueCourse.image})` }"></div>
              </div>
            </div>
            
            <!-- My Courses Section -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border shadow-sm">
              <div class="p-5 border-b flex justify-between items-center">
                <div>
                  <h2 class="text-lg font-bold dark:text-white">My Courses</h2>
                  <p class="text-xs text-slate-500 mt-0.5">Continue where you left off</p>
                </div>
                <button @click="activeTab = 'courses'" class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1">
                  View All <ChevronRight class="w-4 h-4" />
                </button>
              </div>
              
              <div class="p-5">
                <div v-if="enrollments?.length === 0" class="text-center py-12">
                  <BookOpen class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                  <p class="text-slate-500 mb-2">No courses enrolled yet</p>
                  <button @click="browseCourses" class="inline-block px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
                    Browse Courses
                  </button>
                </div>
                
                <div v-else class="space-y-4">
                  <div v-for="course in enrollments.slice(0, 3)" :key="course.id" class="flex gap-4 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all cursor-pointer" @click="continueLearning(course.course_id)">
                    <img :src="course.image" class="w-20 h-20 rounded-lg object-cover" />
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2 mb-1">
                        <span :class="['text-xs px-2 py-0.5 rounded-full', getLevelBadge(course.level)]">{{ course.level }}</span>
                        <div class="flex items-center gap-1">
                          <Star class="w-3 h-3 text-yellow-400 fill-yellow-400" />
                          <span class="text-sm">{{ course.rating }}</span>
                        </div>
                      </div>
                      <h3 class="font-bold dark:text-white line-clamp-1">{{ course.title }}</h3>
                      <p class="text-sm text-slate-500">{{ course.instructor }}</p>
                      <div class="mt-2">
                        <div class="flex justify-between text-xs mb-1">
                          <span class="text-slate-500">{{ course.completed_lessons }}/{{ course.total_lessons }} lessons</span>
                          <span class="font-medium" :class="getProgressColor(course.progress)">{{ course.progress }}%</span>
                        </div>
                        <div class="bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                          <div class="h-1.5 rounded-full transition-all" :class="getProgressColor(course.progress)" :style="{ width: course.progress + '%' }"></div>
                        </div>
                      </div>
                    </div>
                    <button class="self-center w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center hover:bg-blue-100 transition-all">
                      <Play class="w-4 h-4 text-blue-600" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Learning Progress Chart -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border shadow-sm p-5">
              <div class="flex justify-between items-center mb-4">
                <div>
                  <h2 class="text-lg font-bold dark:text-white">Weekly Learning Activity</h2>
                  <p class="text-xs text-slate-500">Your learning hours over the past week</p>
                </div>
                <div class="flex items-center gap-2 text-sm">
                  <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                  <span class="text-slate-600 dark:text-slate-400">Hours studied</span>
                </div>
              </div>
              <div class="h-64">
                <canvas id="weeklyChart"></canvas>
              </div>
            </div>
          </div>
          
          <!-- Right Column -->
          <div class="lg:col-span-1 space-y-6">
            <!-- Recent Activity Feed -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border shadow-sm">
              <div class="p-4 border-b">
                <h3 class="font-bold flex items-center gap-2">
                  <Activity class="w-4 h-4 text-blue-500" />
                  Recent Activity
                </h3>
              </div>
              <div class="max-h-96 overflow-y-auto divide-y">
                <div v-for="activity in recentActivities?.slice(0, 8)" :key="activity.date" class="p-4 flex items-start gap-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-all">
                  <div :class="['w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0', getActivityColor(activity.color)]">
                    <component :is="getActivityIcon(activity.type)" class="w-5 h-5" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm dark:text-white">{{ activity.title }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ activity.message }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ activity.time_ago }}</p>
                  </div>
                </div>
                <div v-if="!recentActivities?.length" class="p-8 text-center text-slate-500">
                  <Bell class="w-12 h-12 mx-auto mb-3 text-slate-300" />
                  <p>No recent activity</p>
                </div>
              </div>
            </div>
            
            <!-- Quiz Performance -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border shadow-sm">
              <div class="p-4 border-b">
                <h3 class="font-bold flex items-center gap-2">
                  <Trophy class="w-4 h-4 text-amber-500" />
                  Quiz Performance
                </h3>
              </div>
              <div class="p-4">
                <div class="h-48">
                  <canvas id="quizChart"></canvas>
                </div>
                <div class="mt-4 text-center">
                  <p class="text-sm text-slate-500">Your recent quiz scores</p>
                  <p class="text-xs text-slate-400 mt-1">Keep practicing to improve your scores!</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- ==================== SETTINGS VIEW ==================== -->
      <div v-if="activeTab === 'settings'" class="p-4 md:p-6 lg:p-8">
        <!-- Success Alert -->
        <div v-if="showSuccess" class="fixed top-24 right-4 z-50 bg-green-50 border border-green-200 rounded-xl p-4 shadow-lg animate-slide-down">
          <div class="flex items-center gap-3">
            <CheckCircle class="w-5 h-5 text-green-600" />
            <div>
              <p class="font-medium text-green-800">Profile Updated!</p>
              <p class="text-sm text-green-600">Your changes have been saved</p>
            </div>
          </div>
        </div>

        <!-- Error Alert -->
        <div v-if="showError" class="fixed top-24 right-4 z-50 bg-red-50 border border-red-200 rounded-xl p-4 shadow-lg animate-slide-down">
          <div class="flex items-center gap-3">
            <AlertCircle class="w-5 h-5 text-red-600" />
            <div>
              <p class="font-medium text-red-800">Update Failed</p>
              <p class="text-sm text-red-600">{{ errorMessage }}</p>
            </div>
          </div>
        </div>

        <div class="max-w-3xl mx-auto">
          <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8">
              <h1 class="text-2xl font-bold text-white">Account Settings</h1>
              <p class="text-blue-100 mt-1">Update your profile information</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="updateProfile" class="p-6 space-y-6">
              <!-- Profile Picture -->
              <div class="flex items-center gap-6 pb-6 border-b">
                <div class="relative">
                  <div class="w-24 h-24 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white text-3xl font-bold overflow-hidden">
                    <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" />
                    <span v-else>{{ settingsForm.name?.charAt(0) || 'U' }}</span>
                  </div>
                  <label class="absolute bottom-0 right-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-700 transition-colors shadow-lg">
                    <Camera class="w-4 h-4 text-white" />
                    <input type="file" accept="image/*" class="hidden" @change="handleImageUpload" />
                  </label>
                </div>
                <div>
                  <p class="font-medium text-slate-800 dark:text-white">Profile Picture</p>
                  <p class="text-sm text-slate-500">JPG or PNG, max 5MB</p>
                </div>
              </div>

              <!-- Personal Information -->
              <div>
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                  <User class="w-5 h-5 text-blue-600" />
                  Personal Information
                </h2>
                
                <div class="grid md:grid-cols-2 gap-5">
                  <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Full Name</label>
                    <input 
                      type="text" 
                      v-model="settingsForm.name" 
                      class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white"
                      required
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Email Address</label>
                    <input 
                      type="email" 
                      v-model="settingsForm.email" 
                      class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white bg-slate-50 dark:bg-slate-800"
                      readonly
                    />
                    <p class="text-xs text-slate-400 mt-1">Email cannot be changed</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Phone Number</label>
                    <input 
                      type="tel" 
                      v-model="settingsForm.phone" 
                      class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white"
                      placeholder="09XXXXXXXX"
                    />
                  </div>
                </div>
              </div>

              <!-- Bio -->
              <div class="border-t pt-6">
                <h2 class="text-lg font-bold mb-4 dark:text-white">About You</h2>
                <div>
                  <label class="block text-sm font-medium mb-1 dark:text-white">Bio</label>
                  <textarea 
                    v-model="settingsForm.bio" 
                    rows="4" 
                    class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white resize-none"
                    placeholder="Tell us about yourself..."
                  ></textarea>
                </div>
              </div>

              <!-- Save Button -->
              <div class="border-t pt-6 flex gap-4">
                <button 
                  type="submit" 
                  :disabled="settingsForm.processing"
                  class="flex-1 px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                >
                  <Save class="w-4 h-4" />
                  {{ settingsForm.processing ? 'Saving...' : 'Save Changes' }}
                </button>
                <button 
                  type="button" 
                  @click="activeTab = 'dashboard'"
                  class="px-6 py-3 border rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all font-medium dark:text-white"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <!-- ==================== MY COURSES VIEW ==================== -->
      <div v-if="activeTab === 'courses'" class="p-4 md:p-6 lg:p-8">
        <div class="mb-6">
          <h1 class="text-2xl font-bold dark:text-white">My Courses</h1>
          <p class="text-slate-500 mt-1">All your enrolled courses in one place</p>
        </div>
        
        <div v-if="enrollments?.length === 0" class="bg-white dark:bg-slate-800 rounded-xl p-12 text-center border">
          <BookOpen class="w-20 h-20 text-slate-300 mx-auto mb-4" />
          <p class="text-slate-500 mb-2">You haven't enrolled in any courses yet</p>
          <button @click="browseCourses" class="inline-block px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
            Browse Courses
          </button>
        </div>
        
        <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="course in enrollments" :key="course.id" class="bg-white dark:bg-slate-800 rounded-xl border overflow-hidden hover:shadow-lg transition-all group">
            <div class="relative h-48 overflow-hidden">
              <img :src="course.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
              <div class="absolute top-3 right-3">
                <span class="text-xs px-2 py-1 bg-black/60 text-white rounded-full backdrop-blur-sm">
                  {{ course.progress }}% Complete
                </span>
              </div>
              <div class="absolute bottom-0 left-0 right-0 h-1 bg-slate-200/30">
                <div class="h-full bg-blue-500 transition-all" :style="{ width: course.progress + '%' }"></div>
              </div>
            </div>
            <div class="p-5">
              <div class="flex items-center gap-2 mb-2">
                <span :class="['text-xs px-2 py-0.5 rounded-full', getLevelBadge(course.level)]">{{ course.level }}</span>
                <div class="flex items-center gap-1">
                  <Star class="w-3 h-3 text-yellow-400 fill-yellow-400" />
                  <span class="text-sm">{{ course.rating }}</span>
                </div>
              </div>
              <h3 class="font-bold text-lg mb-1 line-clamp-2">{{ course.title }}</h3>
              <p class="text-sm text-slate-500 mb-3">{{ course.instructor }}</p>
              <div class="flex items-center justify-between text-sm text-slate-500 mb-3">
                <span class="flex items-center gap-1"><Clock class="w-3 h-3" /> {{ course.hours }} hours</span>
                <span class="flex items-center gap-1"><Calendar class="w-3 h-3" /> {{ formatDate(course.enrolled_at) }}</span>
              </div>
              <div class="flex gap-2 mt-3">
                <button @click="continueLearning(course.course_id)" class="flex-1 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all text-sm font-medium flex items-center justify-center gap-2">
                  <Play class="w-4 h-4" />
                  {{ course.progress > 0 ? 'Continue' : 'Start Course' }}
                </button>
                <button @click="viewCourse(course.course_id)" class="px-4 py-2.5 border rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-all text-sm">
                  Details
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- ==================== CERTIFICATES VIEW ==================== -->
      <div v-if="activeTab === 'certificates'" class="p-4 md:p-6 lg:p-8">
        <div class="mb-6">
          <h1 class="text-2xl font-bold dark:text-white">My Certificates</h1>
          <p class="text-slate-500 mt-1">Your achievements and certifications</p>
        </div>
        
        <div v-if="certificates?.length === 0" class="bg-white dark:bg-slate-800 rounded-xl p-12 text-center border">
          <Award class="w-20 h-20 text-slate-300 mx-auto mb-4" />
          <p class="text-slate-500 mb-2">Complete courses to earn certificates</p>
          <button @click="browseCourses" class="inline-block px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
            Browse Courses
          </button>
        </div>
        
        <div v-else class="grid md:grid-cols-2 gap-6">
          <div v-for="cert in certificates" :key="cert.id" class="bg-white dark:bg-slate-800 rounded-xl border p-6 hover:shadow-lg transition-all group">
            <div class="flex items-start gap-4">
              <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl flex items-center justify-center shadow-md">
                <Award class="w-8 h-8 text-white" />
              </div>
              <div class="flex-1">
                <h3 class="font-bold text-lg">{{ cert.course_title }}</h3>
                <p class="text-sm text-slate-500">Issued: {{ formatDate(cert.issued_at) }}</p>
                <p class="text-xs text-slate-400 font-mono mt-1">Certificate #: {{ cert.certificate_number }}</p>
                <div class="flex gap-3 mt-4">
                  <button @click="downloadCertificate(cert)" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 text-sm flex items-center gap-2 transition-all">
                    <Download class="w-4 h-4" /> Download PDF
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- ==================== WISHLIST VIEW ==================== -->
      <div v-if="activeTab === 'wishlist'" class="p-4 md:p-6 lg:p-8">
        <div class="mb-6">
          <h1 class="text-2xl font-bold dark:text-white">My Wishlist</h1>
          <p class="text-slate-500 mt-1">Courses you're interested in</p>
        </div>
        
        <div v-if="wishlist?.length === 0" class="bg-white dark:bg-slate-800 rounded-xl p-12 text-center border">
          <Heart class="w-20 h-20 text-slate-300 mx-auto mb-4" />
          <p class="text-slate-500 mb-2">Your wishlist is empty</p>
          <button @click="browseCourses" class="inline-block px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
            Browse Courses
          </button>
        </div>
        
        <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="course in wishlist" :key="course.id" class="bg-white dark:bg-slate-800 rounded-xl border overflow-hidden hover:shadow-lg transition-all">
            <img :src="course.image" class="w-full h-48 object-cover" />
            <div class="p-5">
              <h3 class="font-bold text-lg mb-1">{{ course.title }}</h3>
              <p class="text-sm text-slate-500 mb-2">{{ course.instructor }}</p>
              <div class="flex items-center justify-between mb-4">
                <span class="text-xl font-bold text-blue-600">{{ formatPrice(course.price) }}</span>
              </div>
              <div class="flex gap-2">
                <button @click="addToCart(course)" class="flex-1 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all text-sm font-medium">
                  Add to Cart
                </button>
                <button @click="removeFromWishlist(course.course_id)" class="px-4 py-2.5 border border-rose-300 text-rose-600 rounded-lg hover:bg-rose-50 transition-all text-sm">
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- ==================== ACHIEVEMENTS VIEW ==================== -->
      <div v-if="activeTab === 'achievements'" class="p-4 md:p-6 lg:p-8">
        <div class="mb-6">
          <h1 class="text-2xl font-bold dark:text-white">Achievements</h1>
          <p class="text-slate-500 mt-1">Your badges and milestones</p>
        </div>
        
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="achievement in achievements" :key="achievement.name" :class="[
            'bg-white dark:bg-slate-800 rounded-xl border p-5 transition-all',
            achievement.earned ? 'border-emerald-200 dark:border-emerald-800' : 'opacity-60'
          ]">
            <div class="flex items-center gap-3 mb-3">
              <div :class="[
                'w-12 h-12 rounded-xl flex items-center justify-center text-2xl',
                achievement.earned ? 'bg-gradient-to-br from-amber-400 to-amber-500' : 'bg-slate-100 dark:bg-slate-700'
              ]">
                {{ achievement.icon }}
              </div>
              <div>
                <h3 class="font-bold">{{ achievement.name }}</h3>
                <p class="text-xs text-slate-500">{{ achievement.description }}</p>
              </div>
            </div>
            <div v-if="achievement.progress !== undefined" class="mt-3">
              <div class="flex justify-between text-xs mb-1">
                <span>{{ achievement.current }}/{{ achievement.target }}</span>
                <span>{{ Math.round(achievement.progress) }}%</span>
              </div>
              <div class="bg-slate-200 rounded-full h-2">
                <div class="bg-blue-500 h-2 rounded-full" :style="{ width: achievement.progress + '%' }"></div>
              </div>
            </div>
            <div v-if="achievement.earned && achievement.date" class="mt-3">
              <p class="text-xs text-emerald-600">Earned {{ formatDate(achievement.date) }}</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- ==================== ANALYTICS VIEW ==================== -->
      <div v-if="activeTab === 'analytics'" class="p-4 md:p-6 lg:p-8">
        <div class="mb-6">
          <h1 class="text-2xl font-bold dark:text-white">Learning Analytics</h1>
          <p class="text-slate-500 mt-1">Track your learning progress and performance</p>
        </div>
        
        <div class="grid md:grid-cols-2 gap-6">
          <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
            <h3 class="font-bold mb-4">Monthly Learning Hours</h3>
            <div class="space-y-3">
              <div v-for="month in monthlyData" :key="month.month" class="flex items-center gap-3">
                <span class="text-sm w-12">{{ month.month }}</span>
                <div class="flex-1 bg-slate-200 rounded-full h-8">
                  <div class="bg-blue-500 h-8 rounded-full flex items-center justify-end px-3 text-white text-xs" :style="{ width: (month.hours / 40 * 100) + '%' }">
                    {{ month.hours }}h
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
            <h3 class="font-bold mb-4">Course Completion Stats</h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <span>Completed</span>
                <div class="flex-1 mx-4 bg-slate-200 rounded-full h-6">
                  <div class="bg-emerald-500 h-6 rounded-full flex items-center justify-end px-3 text-white text-xs" :style="{ width: (stats.completed_courses / stats.total_courses * 100) + '%' }">
                    {{ stats.completed_courses }}
                  </div>
                </div>
              </div>
              <div class="flex justify-between items-center">
                <span>In Progress</span>
                <div class="flex-1 mx-4 bg-slate-200 rounded-full h-6">
                  <div class="bg-amber-500 h-6 rounded-full flex items-center justify-end px-3 text-white text-xs" :style="{ width: (stats.in_progress / stats.total_courses * 100) + '%' }">
                    {{ stats.in_progress }}
                  </div>
                </div>
              </div>
              <div class="flex justify-between items-center">
                <span>Not Started</span>
                <div class="flex-1 mx-4 bg-slate-200 rounded-full h-6">
                  <div class="bg-slate-500 h-6 rounded-full flex items-center justify-end px-3 text-white text-xs" :style="{ width: (stats.not_started / stats.total_courses * 100) + '%' }">
                    {{ stats.not_started }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
    </main>
  </div>
</template>

<style scoped>
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-slide-down {
  animation: slideDown 0.3s ease-out;
}
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>