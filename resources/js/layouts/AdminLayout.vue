<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
    LayoutDashboard, Users, GraduationCap, BookOpen, FolderTree,
  CreditCard, Wallet, BarChart3, Star, Headphones, Tag,
  Megaphone, Settings, HelpCircle, LogOut, Bell, Sun, Moon,
  ChevronDown, Menu, X, Home, User, Eye, MessageCircle, Flag, ThumbsUp, Filter
} from 'lucide-vue-next'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const isDarkMode = ref(false)
const isMobileMenuOpen = ref(false)
const showUserMenu = ref(false)

// Main navigation items
const navItems = [
  { name: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
  { name: 'Students', href: '/admin/students', icon: Users },
  { name: 'Instructors', href: '/admin/instructors', icon: GraduationCap },
  { name: 'Courses', href: '/admin/courses', icon: BookOpen },
  { name: 'Categories', href: '/admin/categories', icon: FolderTree },
  { name: 'Payments', href: '/admin/payments', icon: CreditCard },
  { name: 'Withdrawals', href: '/admin/withdrawals', icon: Wallet },
  { name: 'Analytics', href: '/admin/analytics', icon: BarChart3 },
  { name: 'Reviews', href: '/admin/reviews', icon: Star },
  { name: 'Support', href: '/admin/support', icon: Headphones },
  { name: 'Coupons', href: '/admin/coupons', icon: Tag },
]

// Footer items
const footerItems = [
  { name: 'Announcements', href: '/admin/announcements', icon: Megaphone },
  { name: 'Settings', href: '/admin/settings', icon: Settings },
  { name: 'Help', href: '/admin/help', icon: HelpCircle },
]

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
  else {
    isDarkMode.value = false
    document.documentElement.classList.remove('dark')
  }
}

const setupSystemThemeListener = () => {
  const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
  darkModeMediaQuery.addEventListener('change', (e) => {
    if (!localStorage.getItem('theme')) {
      if (e.matches) {
        isDarkMode.value = true
        document.documentElement.classList.add('dark')
      } else {
        isDarkMode.value = false
        document.documentElement.classList.remove('dark')
      }
    }
  })
}

const logout = () => {
  if (confirm('Are you sure you want to logout?')) {
    router.post(route('logout'))
  }
}

const goToProfile = () => {
  router.get(`/admin/profile/${user.value?.id}`)
}

onMounted(() => {
  initTheme()
  setupSystemThemeListener()
})

initTheme()
setupSystemThemeListener()
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <!-- Mobile Menu Button -->
     <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="lg:hidden fixed top-4 left-4 z-50 w-10 h-10 bg-white dark:bg-slate-800 rounded-xl shadow-md flex items-center justify-center border border-slate-200 dark:border-slate-700">
      <Menu v-if="!isMobileMenuOpen" class="w-5 h-5 text-slate-600 dark:text-slate-300" />
      <X v-else class="w-5 h-5 text-slate-600 dark:text-slate-300" />
    </button>
    
   <!-- Sidebar -->
    <aside :class="[
      'fixed left-0 top-0 h-full bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300 z-40 flex flex-col',
      isMobileMenuOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0 lg:w-64'
    ]">
      <!-- Logo -->
      <div class="p-6 border-b border-slate-200 dark:border-slate-800">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg">L</div>
          <div>
            <span class="text-2xl font-black dark:text-white">Learn<span class="text-blue-600">Hub</span></span>
            <p class="text-xs text-slate-500 mt-0.5">Administrator Panel</p>
          </div>
        </div>
      </div>
      
      <!-- User Info - Clickable for Profile -->
      <div class="p-6 border-b border-slate-200 dark:border-slate-800">
        <button @click="goToProfile" class="w-full flex items-center space-x-3 hover:bg-slate-50 dark:hover:bg-slate-800 p-2 rounded-xl transition-all">
          <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-md">
            {{ user?.full_name?.charAt(0) || user?.name?.charAt(0) || 'A' }}
          </div>
          <div class="flex-1 text-left">
            <p class="font-bold dark:text-white truncate">{{ user?.full_name || user?.name || 'Admin' }}</p>
            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Administrator</p>
          </div>
          <ChevronDown class="w-4 h-4 text-slate-400" />
        </button>
      </div>
      
      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto p-4 space-y-1">
        <Link v-for="item in navItems" :key="item.name" :href="item.href" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-600 transition-all group">
          <component :is="item.icon" class="w-5 h-5 group-hover:scale-110 transition-transform" />
          <span class="font-medium">{{ item.name }}</span>
        </Link>
      </nav>
      
      <!-- Theme Toggle at Bottom - FIXED -->
      <div class="p-4 border-t border-slate-200 dark:border-slate-800">
        <button @click="toggleTheme" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
          <Sun v-if="isDarkMode" class="w-5 h-5 text-yellow-500" />
          <Moon v-else class="w-5 h-5 text-blue-600" />
          <span class="font-medium">{{ isDarkMode ? 'Light Mode' : 'Dark Mode' }}</span>
        </button>
        
        <!-- Logout Button -->
        <button @click="logout" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all mt-2">
          <LogOut class="w-5 h-5" />
          <span class="font-medium">Logout</span>
        </button>
      </div>
    </aside>
    
     
    <!-- Overlay -->
    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>
    
    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
      <slot />
    </main>
  </div>
</template>