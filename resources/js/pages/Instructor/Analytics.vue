<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import InstructorLayout from '@/layouts/InstructorLayout.vue'
import { TrendingUp, Users, DollarSign, BookOpen, Eye, Clock, Calendar } from 'lucide-vue-next'
import axios from 'axios'

const analytics = ref({
    total_views: 0,
    total_students: 0,
    total_revenue: 0,
    total_courses: 0,
    completion_rate: 0,
    popular_courses: []
})
const isLoading = ref(true)

const fetchAnalytics = async () => {
    isLoading.value = true
    try {
        const response = await axios.get('/instructor/analytics/data')
        analytics.value = response.data
    } catch (error) {
        console.error('Error fetching analytics:', error)
    } finally {
        isLoading.value = false
    }
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US').format(amount || 0) + ' ETB'
}

onMounted(() => {
    fetchAnalytics()
})
</script>

<template>
    <Head title="Analytics | Instructor" />
    
    <InstructorLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold dark:text-white">Analytics</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Track your course performance</p>
            </div>

            <div v-if="isLoading" class="text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600 mx-auto"></div>
            </div>

            <div v-else>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-500">Total Views</p>
                                <p class="text-2xl font-bold">{{ analytics.total_views || 0 }}</p>
                            </div>
                            <Eye class="w-8 h-8 text-blue-500 opacity-50" />
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-xl border p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-500">Total Students</p>
                                <p class="text-2xl font-bold">{{ analytics.total_students || 0 }}</p>
                            </div>
                            <Users class="w-8 h-8 text-green-500 opacity-50" />
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-xl border p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-500">Total Revenue</p>
                                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(analytics.total_revenue) }}</p>
                            </div>
                            <DollarSign class="w-8 h-8 text-green-500 opacity-50" />
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-xl border p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-500">Completion Rate</p>
                                <p class="text-2xl font-bold text-blue-600">{{ analytics.completion_rate || 0 }}%</p>
                            </div>
                            <TrendingUp class="w-8 h-8 text-blue-500 opacity-50" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
                    <h2 class="text-xl font-bold mb-4">Popular Courses</h2>
                    <div class="space-y-3">
                                <div v-for="course in analytics.popular_courses" :key="course.id" class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                                    <div>
                                        <p class="font-medium">{{ course.title }}</p>
                                        <p class="text-sm text-slate-500">{{ course.students }} students enrolled</p>
                                    </div>
                                    <p class="font-bold text-blue-600">{{ formatCurrency(course.revenue) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    </InstructorLayout>
</template>