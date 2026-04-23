<script setup lang="ts">
import { Link, router, usePage, Head } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { 
  Moon, 
  Sun, 
  Users, 
  BookOpen, 
  DollarSign, 
  TrendingUp,
  Search,
  Filter,
  Edit,
  Shield,
  UserPlus,
  LogOut,
  Settings,
  Bell,
  Home,
  BarChart3,
  Calendar,
  MessageCircle
} from 'lucide-vue-next'

// FIX: Interfaces to resolve TypeScript "Property user/href does not exist" errors
interface User {
  id: number
  name: string
  full_name?: string
  username?: string
  email: string
  roles?: string[]
}

interface PageProps {
  auth: {
    user: User
  }
  [key: string]: any
}

type StatCard = {
  label: string
  value: string
  growth: number
  isPositive: boolean
  icon: any
}

const props = defineProps<{
  stats: {
    total_sales: number
    sales_increase: number
    active_students: number
    student_increase: number
    total_courses: number
    course_increase: number
    total_revenue?: number
  }
  users: {
    data: Array<{
      id: number
      full_name?: string
      username?: string
      email?: string
      status?: string
      created_at_human?: string
      last_login?: string
      course_enrollment?: string
      avatar?: string
      roles?: string[]
    }>
    current_page?: number
    last_page?: number
  }
  revenue_chart: Record<string, number>
  platform_overview?: {
    thoughts?: number
    students?: number
    total_enrollment?: number
    new_enrollments?: number
    total_courses?: number
    completion_rate?: number
    engagement_rate?: number
    average_grade?: number
  }
  course_performance?: {
    completion?: number
    engagement?: number
    average_grade?: number
  }
}>()

// FIX: Typed usePage to clear the red error on page.props.auth.user
const page = usePage<PageProps>()
const authUser = computed(() => page.props.auth?.user)

// Theme state
const isDarkMode = ref(false)
const isMobileMenuOpen = ref(false)
const searchQuery = ref('')
const selectedRole = ref('all')

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

const logout = () => {
  if (confirm('Are you sure you want to logout?')) {
    router.post(route('logout'))
  }
}

const formatCurrency = (value: number) =>
  new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
  }).format(value ?? 0)

const mainStats = computed<StatCard[]>(() => [
  {
    label: 'Total Sales',
    value: formatCurrency(props.stats?.total_sales ?? 0),
    growth: props.stats?.sales_increase ?? 0,
    isPositive: (props.stats?.sales_increase ?? 0) >= 0,
    icon: DollarSign,
  },
  {
    label: 'Active Students',
    value: (props.stats?.active_students ?? 0).toLocaleString(),
    growth: props.stats?.student_increase ?? 0,
    isPositive: (props.stats?.student_increase ?? 0) >= 0,
    icon: Users,
  },
  {
    label: 'Total Courses',
    value: String(props.stats?.total_courses ?? 0),
    growth: props.stats?.course_increase ?? 0,
    isPositive: (props.stats?.course_increase ?? 0) >= 0,
    icon: BookOpen,
  },
])

const userName = computed(() => {
  const name = authUser.value?.full_name || authUser.value?.name || 'Admin'
  return String(name).split(' ')[0]
})

const fullName = computed(() => {
  return authUser.value?.full_name || authUser.value?.name || 'Admin User'
})

const userEmail = computed(() => {
  return authUser.value?.email || 'admin@learnhub.com'
})

// Filter users
const filteredUsers = computed(() => {
  if (!props.users?.data) return []
  return props.users.data.filter(user => {
    const matchesSearch = searchQuery.value === '' || 
      user.full_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.username?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email?.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesRole = selectedRole.value === 'all' || 
      (user.roles && user.roles.includes(selectedRole.value))
    
    return matchesSearch && matchesRole
  })
})

// Assign role function
const assignRole = (userId: number, newRole: string) => {
  router.post(`/admin/users/${userId}/assign-role`, { role: newRole }, {
    onSuccess: () => {
      alert(`Role assigned successfully!`)
    },
    onError: () => {
      alert('Error assigning role')
    }
  })
}

const getRoleBadgeClass = (roles?: string[]) => {
  if (!roles || roles.length === 0) return 'bg-slate-100 text-slate-700'
  if (roles.includes('admin')) return 'bg-red-100 text-red-700'
  if (roles.includes('instructor')) return 'bg-purple-100 text-purple-700'
  return 'bg-blue-100 text-blue-700'
}

const getRoleLabel = (roles?: string[]) => {
  if (!roles || roles.length === 0) return 'student'
  if (roles.includes('admin')) return 'admin'
  if (roles.includes('instructor')) return 'instructor'
  return 'student'
}

// FIX: Added 'href' to navItems to clear the template error
const navItems = [
  { id: 'dashboard', label: 'Dashboard', icon: Home, href: '#' },
  { id: 'users', label: 'Users', icon: Users, href: '#' },
  { id: 'courses', label: 'Courses', icon: BookOpen, href: '#' },
  { id: 'analytics', label: 'Analytics', icon: BarChart3, href: '#' },
  { id: 'reports', label: 'Reports', icon: TrendingUp, href: '#' },
  { id: 'settings', label: 'Settings', icon: Settings, href: '#' },
]

const activeTab = ref('dashboard')
</script>

<template>
  <Head title="Admin Dashboard | LearnHub" />
  
  <div class="min-h-screen bg-white dark:bg-slate-950">
    
    <button @click="toggleTheme" class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-white dark:bg-slate-800 rounded-xl shadow-lg flex items-center justify-center hover:scale-105 transition-all border border-slate-200 dark:border-slate-700">
      <Sun v-if="isDarkMode" class="w-5 h-5 text-yellow-500" />
      <Moon v-else class="w-5 h-5 text-slate-700" />
    </button>

    <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="lg:hidden fixed top-4 left-4 z-50 w-10 h-10 bg-white dark:bg-slate-800 rounded-xl shadow-md flex items-center justify-center border border-slate-200 dark:border-slate-700">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
      </svg>
    </button>

    <aside :class="[
      'fixed left-0 top-0 h-full bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300 z-40',
      isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0 lg:w-64'
    ]">
      <div class="p-6 border-b border-slate-200 dark:border-slate-800">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg">
            L
          </div>
          <span class="text-2xl font-black tracking-tighter dark:text-white">Learn<span class="text-blue-600">Hub</span></span>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Admin Portal</p>
      </div>

      <div class="p-6 border-b border-slate-200 dark:border-slate-800">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600 font-bold text-lg">
            {{ fullName.charAt(0) }}
          </div>
          <div>
            <p class="font-bold dark:text-white">{{ fullName }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">Administrator</p>
          </div>
        </div>
      </div>

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

      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-200 dark:border-slate-800 space-y-1">
        <Link :href="route('profile.edit')" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
          <Settings class="w-5 h-5" />
          <span class="font-medium">Profile Settings</span>
        </Link>
        <button @click="logout" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
          <LogOut class="w-5 h-5" />
          <span class="font-medium">Logout</span>
        </button>
      </div>
    </aside>

    <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    <main class="lg:ml-64">
      
      <header class="bg-white dark:bg-slate-900 sticky top-0 z-20 border-b border-slate-200 dark:border-slate-800">
        <div class="px-4 lg:px-8 py-4 flex items-center justify-between">
          <div>
            <h1 class="text-xl lg:text-2xl font-black dark:text-white">Welcome back, {{ userName }}!</h1>
            <p class="text-xs lg:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Here's what's happening with your platform today.</p>
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
                <p class="text-xs text-slate-500 dark:text-slate-400">Admin</p>
              </div>
            </div>
          </div>
        </div>
      </header>

      <div class="p-4 lg:p-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6 mb-8">
          <div v-for="stat in mainStats" :key="stat.label" 
            class="bg-white dark:bg-slate-900 rounded-xl p-4 lg:p-5 border border-slate-200 dark:border-slate-800 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">{{ stat.label }}</p>
                <p class="text-2xl lg:text-3xl font-black dark:text-white">{{ stat.value }}</p>
                <p :class="stat.isPositive ? 'text-emerald-600' : 'text-red-600'" class="text-xs mt-1">
                  {{ stat.isPositive ? '+' : '-' }}{{ Math.abs(stat.growth) }}% from last month
                </p>
              </div>
              <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                <component :is="stat.icon" class="w-5 h-5 text-blue-600" />
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden mb-8">
          <div class="p-6 border-b border-slate-200 dark:border-slate-800">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <h2 class="text-xl font-black dark:text-white">User Management</h2>
              <div class="flex items-center space-x-3">
                <div class="relative">
                  <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400" />
                  <input 
                    v-model="searchQuery"
                    type="text" 
                    placeholder="Search users..." 
                    class="pl-10 pr-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-sm dark:text-white"
                  />
                </div>
                <select v-model="selectedRole" class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-sm dark:text-white">
                  <option value="all">All Roles</option>
                  <option value="student">Students</option>
                  <option value="instructor">Instructors</option>
                  <option value="admin">Admins</option>
                </select>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors flex items-center space-x-1">
                  <UserPlus class="w-4 h-4" />
                  <span>Add User</span>
                </button>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">User</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Role</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Last Login</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-center space-x-3">
                      <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center text-blue-600 font-bold text-sm">
                        {{ (user.full_name || user.username || 'U').charAt(0).toUpperCase() }}
                      </div>
                      <span class="font-medium dark:text-white">{{ user.full_name || user.username || 'Unnamed' }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ user.email || '—' }}</td>
                  <td class="px-6 py-4">
                    <select 
                      @change="assignRole(user.id, ($event.target as HTMLSelectElement).value)"
                      :value="getRoleLabel(user.roles)"
                      class="text-sm border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 bg-white dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                      <option value="student">Student</option>
                      <option value="instructor">Instructor</option>
                      <option value="admin">Admin</option>
                    </select>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                      Active
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ user.last_login || 'Today' }}</td>
                  <td class="px-6 py-4">
                    <button class="p-1 text-slate-400 hover:text-blue-600 transition-colors">
                      <Edit class="w-4 h-4" />
                    </button>
                  </td>
                </tr>
                <tr v-if="!filteredUsers.length">
                  <td colspan="6" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                    No users found.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-sm text-slate-500 dark:text-slate-400">
            <div>Page {{ users?.current_page ?? 1 }} of {{ users?.last_page ?? 1 }}</div>
            <div class="flex items-center space-x-2">
              <button class="px-3 py-1 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">Previous</button>
              <button class="px-3 py-1 rounded-lg bg-blue-600 text-white">1</button>
              <button class="px-3 py-1 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800">Next</button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
          
          <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-xl font-black dark:text-white mb-4">Platform Overview</h2>
            <div class="grid grid-cols-2 gap-4">
              <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                <p class="text-2xl font-black dark:text-white">{{ platform_overview?.students ?? 853 }}</p>
                <p class="text-xs text-slate-500">Total Students</p>
              </div>
              <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                <p class="text-2xl font-black dark:text-white">{{ platform_overview?.total_enrollment ?? 7685 }}</p>
                <p class="text-xs text-slate-500">Total Enrollments</p>
              </div>
              <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                <p class="text-2xl font-black dark:text-white">{{ platform_overview?.new_enrollments ?? 197 }}</p>
                <p class="text-xs text-slate-500">New This Week</p>
              </div>
              <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                <p class="text-2xl font-black dark:text-white">{{ platform_overview?.total_courses ?? 125 }}</p>
                <p class="text-xs text-slate-500">Total Courses</p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800">
            <h2 class="text-xl font-black dark:text-white mb-4">Course Performance</h2>
            <div class="space-y-4">
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-slate-600 dark:text-slate-400">Completion Rate</span>
                  <span class="font-bold dark:text-white">{{ course_performance?.completion ?? 90 }}%</span>
                </div>
                <div class="bg-slate-100 dark:bg-slate-800 rounded-full h-2">
                  <div class="bg-blue-600 rounded-full h-2" :style="{ width: `${course_performance?.completion ?? 90}%` }"></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-slate-600 dark:text-slate-400">Engagement Rate</span>
                  <span class="font-bold dark:text-white">{{ course_performance?.engagement ?? 77 }}%</span>
                </div>
                <div class="bg-slate-100 dark:bg-slate-800 rounded-full h-2">
                  <div class="bg-blue-600 rounded-full h-2" :style="{ width: `${course_performance?.engagement ?? 77}%` }"></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-slate-600 dark:text-slate-400">Average Grade</span>
                  <span class="font-bold dark:text-white">{{ course_performance?.average_grade ?? 88 }}%</span>
                </div>
                <div class="bg-slate-100 dark:bg-slate-800 rounded-full h-2">
                  <div class="bg-blue-600 rounded-full h-2" :style="{ width: `${course_performance?.average_grade ?? 88}%` }"></div>
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