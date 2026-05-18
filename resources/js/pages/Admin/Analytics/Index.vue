<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { TrendingUp, Users, DollarSign, Award, Calendar } from 'lucide-vue-next'
import Chart from 'chart.js/auto'

const props = defineProps({
  revenueData: Array,
  enrollmentData: Array,
  topInstructors: Array,
  topCourses: Array
})

const revenueChartRef = ref(null)
const enrollmentChartRef = ref(null)
let revenueChart = null
let enrollmentChart = null

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US').format(amount || 0) + ' ETB'
}

onMounted(() => {
  if (revenueChartRef.value) {
    revenueChart = new Chart(revenueChartRef.value, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
          label: 'Revenue',
          data: props.revenueData || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
          borderColor: '#4f46e5',
          backgroundColor: 'rgba(79, 70, 229, 0.1)',
          fill: true,
          tension: 0.4
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    })
  }
  
  if (enrollmentChartRef.value) {
    enrollmentChart = new Chart(enrollmentChartRef.value, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
          label: 'New Students',
          data: props.enrollmentData || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
          backgroundColor: '#10b981',
          borderRadius: 8
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    })
  }
})
</script>

<template>
  <Head title="Analytics | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-3xl font-bold dark:text-white">Analytics Dashboard</h1>
        <p class="text-slate-500 mt-1">Track platform performance and growth metrics</p>
      </div>
      
      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Total Revenue</p>
              <p class="text-2xl font-bold text-emerald-600">{{ formatCurrency(0) }}</p>
            </div>
            <DollarSign class="w-10 h-10 text-emerald-500 opacity-50" />
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Total Students</p>
              <p class="text-2xl font-bold text-blue-600">0</p>
            </div>
            <Users class="w-10 h-10 text-blue-500 opacity-50" />
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Total Courses</p>
              <p class="text-2xl font-bold text-purple-600">0</p>
            </div>
            <Award class="w-10 h-10 text-purple-500 opacity-50" />
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Completion Rate</p>
              <p class="text-2xl font-bold text-orange-600">0%</p>
            </div>
            <TrendingUp class="w-10 h-10 text-orange-500 opacity-50" />
          </div>
        </div>
      </div>
      
      <!-- Charts -->
      <div class="grid lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
          <h3 class="text-lg font-bold mb-4">Revenue Trend</h3>
          <div class="h-80">
            <canvas ref="revenueChartRef"></canvas>
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
          <h3 class="text-lg font-bold mb-4">Student Growth</h3>
          <div class="h-80">
            <canvas ref="enrollmentChartRef"></canvas>
          </div>
        </div>
      </div>
      
      <div class="text-center py-12 bg-white dark:bg-slate-800 rounded-xl border">
        <p class="text-slate-500">Full analytics with real data coming soon. Connect your payment and enrollment systems for live metrics.</p>
      </div>
    </div>
  </AdminLayout>
</template>