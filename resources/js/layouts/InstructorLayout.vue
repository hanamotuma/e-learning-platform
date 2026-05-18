<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
  LayoutDashboard, BookOpen, Users, DollarSign, Star, 
  Settings, HelpCircle, LogOut, Sun, Moon, Menu, X,
  BarChart3
} from 'lucide-vue-next'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const isMobileMenuOpen = ref(false)
const isDarkMode = ref(false)

const navItems = [
  { name: 'Dashboard', href: '/instructor/dashboard', icon: LayoutDashboard },
  { name: 'My Courses', href: '/instructor/courses', icon: BookOpen },
  { name: 'Students', href: '/instructor/students', icon: Users },
  { name: 'Earnings', href: '/instructor/earnings', icon: DollarSign },
  { name: 'Reviews', href: '/instructor/reviews', icon: Star },
  { name: 'Analytics', href: '/instructor/analytics', icon: BarChart3 },
  { name: 'Settings', href: '/instructor/settings', icon: Settings },
  { name: 'Help', href: '/instructor/help', icon: HelpCircle },
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
  if (savedTheme === 'dark') {
    isDarkMode.value = true
    document.documentElement.classList.add('dark')
  }
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
        <Link href="/instructor/dashboard" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg group-hover:scale-105 transition-transform">
            L
          </div>
          <div>
            <span class="text-2xl font-black tracking-tight dark:text-white">Learn<span class="text-blue-600">Hub</span></span>
            <p class="text-xs text-slate-500 mt-0.5">Instructor Panel</p>
          </div>
        </Link>
      </div>

      <!-- User Info -->
      <div class="m-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 rounded-2xl border border-blue-100 dark:border-blue-900/30">
        <div class="flex items-center space-x-3">
          <div class="relative">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-md">
              {{ user?.full_name?.charAt(0) || user?.name?.charAt(0) || 'I' }}
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-bold dark:text-white truncate">{{ user?.full_name || user?.name || 'Instructor' }}</p>
            <p class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-0.5">Instructor</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 p-4 space-y-1">
        <Link v-for="item in navItems" :key="item.name" :href="item.href" class="flex items-center space-x-3 w-full px-4 py-3 rounded-xl transition-all duration-200 text-left group"
          :class="[$page.url === item.href ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']">
          <component :is="item.icon" class="w-5 h-5 group-hover:scale-110 transition-transform" />
          <span class="font-medium">{{ item.name }}</span>
        </Link>
      </nav>

      <!-- Bottom Actions -->
      <div class="p-4 border-t border-slate-200 dark:border-slate-800 space-y-2">
        <button @click="toggleTheme" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
          <Sun v-if="isDarkMode" class="w-5 h-5 text-yellow-500" />
          <Moon v-else class="w-5 h-5 text-blue-600" />
          <span class="font-medium">{{ isDarkMode ? 'Light Mode' : 'Dark Mode' }}</span>
        </button>
        <button @click="logout" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
          <LogOut class="w-5 h-5" />
          <span class="font-medium">Logout</span>
        </button>
      </div>
    </aside>

    <!-- Overlay -->
    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
      <div class="p-6">
        <slot />
      </div>
    </main>
  </div>
</template>