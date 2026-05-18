<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { 
  TrendingUp, DollarSign, Users, BookOpen, Clock, AlertCircle,
  Calendar, CheckCircle, XCircle, Eye, Star, Award, ShoppingBag,
  Wallet, CreditCard, UserPlus, BarChart3
} from 'lucide-vue-next'
import Chart from 'chart.js/auto'

const props = defineProps({
  stats: Object,
  revenueChart: Array,
  courseSalesChart: Array,
  studentGrowth: Array,
  topInstructors: Array,
  topCourses: Array,
  recentActivities: Array
})

const revenueChartRef = ref(null)
const courseSalesChartRef = ref(null)
const studentGrowthChartRef = ref(null)
let revenueChart = null
let courseSalesChart = null
let studentGrowthChart = null

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US').format(amount || 0) + ' ETB'
}

onMounted(() => {
  // Revenue Chart
  if (revenueChartRef.value) {
    revenueChart = new Chart(revenueChartRef.value, {
      type: 'line',
      data: {
        labels: props.revenueChart?.map(item => item.month) || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Revenue',
          data: props.revenueChart?.map(item => item.revenue) || [0, 0, 0, 0, 0, 0],
          borderColor: '#4f46e5',
          backgroundColor: 'rgba(79, 70, 229, 0.1)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top' },
          tooltip: { callbacks: { label: (ctx) => `ETB ${ctx.raw.toLocaleString()}` } }
        }
      }
    })
  }
  
  // Course Sales Chart
  if (courseSalesChartRef.value) {
    courseSalesChart = new Chart(courseSalesChartRef.value, {
      type: 'bar',
      data: {
        labels: props.courseSalesChart?.map(item => item.title?.substring(0, 15)) || ['Course 1', 'Course 2', 'Course 3'],
        datasets: [{
          label: 'Enrollments',
          data: props.courseSalesChart?.map(item => item.sales) || [0, 0, 0],
          backgroundColor: '#10b981',
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { position: 'top' } }
      }
    })
  }
  
  // Student Growth Chart
  if (studentGrowthChartRef.value) {
    studentGrowthChart = new Chart(studentGrowthChartRef.value, {
      type: 'line',
      data: {
        labels: props.studentGrowth?.map(item => item.month) || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'New Students',
          data: props.studentGrowth?.map(item => item.students) || [0, 0, 0, 0, 0, 0],
          borderColor: '#8b5cf6',
          backgroundColor: 'rgba(139, 92, 246, 0.1)',
          fill: true,
          tension: 0.4
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    })
  }
})
</script>

<template>
  <Head title="Admin Dashboard | LearnHub" />
  
  <AdminLayout>
    <div class="p-4 md:p-6">
      <!-- Page Header -->
      <div class="mb-6 md:mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-slate-800 dark:text-white">Dashboard</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Welcome back, Admin</p>
      </div>
      
      <!-- Stats Cards - 8 Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-3 md:gap-4 mb-6 md:mb-8">
        <!-- Total Revenue -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 dark:text-slate-400">Total Revenue</p>
              <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(stats?.total_revenue) }}</p>
            </div>
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
              <DollarSign class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
            </div>
          </div>
        </div>
        
        <!-- Today's Revenue -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 dark:text-slate-400">Today's Revenue</p>
              <p class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ formatCurrency(stats?.today_revenue) }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
              <Calendar class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>
        
        <!-- Monthly Revenue -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 dark:text-slate-400">Monthly Revenue</p>
              <p class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ formatCurrency(stats?.monthly_revenue) }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
              <TrendingUp class="w-5 h-5 text-purple-600 dark:text-purple-400" />
            </div>
          </div>
        </div>
        
        <!-- Total Students -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 dark:text-slate-400">Total Students</p>
              <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ stats?.total_students || 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
              <Users class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
            </div>
          </div>
        </div>
        
        <!-- Total Instructors -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 dark:text-slate-400">Instructors</p>
              <p class="text-xl font-bold text-orange-600 dark:text-orange-400">{{ stats?.total_instructors || 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
              <Award class="w-5 h-5 text-orange-600 dark:text-orange-400" />
            </div>
          </div>
        </div>
        
        <!-- Total Courses -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 dark:text-slate-400">Total Courses</p>
              <p class="text-xl font-bold text-cyan-600 dark:text-cyan-400">{{ stats?.total_courses || 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-xl flex items-center justify-center">
              <BookOpen class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
            </div>
          </div>
        </div>
        
        <!-- Pending Approvals -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 dark:text-slate-400">Pending Approvals</p>
              <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats?.pending_approvals || 0 }}</p>
            </div>
            <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center">
              <Clock class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
            </div>
          </div>
        </div>
        
        <!-- Pending Withdrawals -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-slate-500 dark:text-slate-400">Pending Withdrawals</p>
              <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ formatCurrency(stats?.pending_withdrawals) }}</p>
            </div>
            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
              <Wallet class="w-5 h-5 text-red-600 dark:text-red-400" />
            </div>
          </div>
        </div>
      </div>
      
      <!-- Charts Row -->
      <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 md:p-6">
          <h3 class="text-base md:text-lg font-bold mb-4 text-slate-800 dark:text-white">Revenue Overview</h3>
          <div class="h-64 md:h-80">
            <canvas ref="revenueChartRef"></canvas>
          </div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 md:p-6">
          <h3 class="text-base md:text-lg font-bold mb-4 text-slate-800 dark:text-white">Top Selling Courses</h3>
          <div class="h-64 md:h-80">
            <canvas ref="courseSalesChartRef"></canvas>
          </div>
        </div>
      </div>
      
      <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 md:p-6">
          <h3 class="text-base md:text-lg font-bold mb-4 text-slate-800 dark:text-white">Student Growth</h3>
          <div class="h-64 md:h-80">
            <canvas ref="studentGrowthChartRef"></canvas>
          </div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 md:p-6">
          <h3 class="text-base md:text-lg font-bold mb-4 text-slate-800 dark:text-white">Top Instructors</h3>
          <div class="space-y-3">
            <div v-for="instructor in (topInstructors || [])" :key="instructor.id" class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
              <div>
                <p class="font-medium text-slate-800 dark:text-white">{{ instructor.full_name }}</p>
                <p class="text-sm text-slate-500">{{ instructor.courses_count }} courses</p>
              </div>
              <div class="text-right">
                <p class="font-bold text-emerald-600">{{ formatCurrency(instructor.total_sales || 0) }}</p>
                <div class="flex text-yellow-400 text-sm">
                  <span v-for="i in 5" :key="i">★</span>
                </div>
              </div>
            </div>
            <div v-if="!topInstructors?.length" class="text-center py-8 text-slate-500">
              No instructors yet
            </div>
          </div>
        </div>
      </div>
      
      <!-- Recent Activities -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="p-4 md:p-6 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-base md:text-lg font-bold text-slate-800 dark:text-white">Recent Activities</h3>
        </div>
        <div class="divide-y divide-slate-200 dark:divide-slate-700">
          <div v-for="activity in (recentActivities || [])" :key="activity.created_at + activity.message" class="p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
              <CheckCircle v-if="activity.type === 'enrollment'" class="w-5 h-5 text-blue-600" />
              <Clock v-else-if="activity.type === 'course_submission'" class="w-5 h-5 text-yellow-600" />
              <UserPlus v-else class="w-5 h-5 text-green-600" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-slate-600 dark:text-slate-300">{{ activity.message }}</p>
              <p class="text-xs text-slate-400 mt-1">{{ new Date(activity.created_at).toLocaleString() }}</p>
            </div>
          </div>
          <div v-if="!recentActivities?.length" class="p-8 text-center text-slate-500">
            No recent activities
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>