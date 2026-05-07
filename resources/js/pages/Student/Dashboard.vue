<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-slate-900 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Welcome Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Welcome back, {{ user.name }}!
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Continue your learning journey
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Total Courses</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalCourses }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <BookOpen class="w-6 h-6 text-blue-600" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Completed</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ completedCourses }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <CheckCircle class="w-6 h-6 text-green-600" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Avg Progress</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalProgress }}%</p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <TrendingUp class="w-6 h-6 text-purple-600" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Certificates</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ certificates.length }}</p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                <Award class="w-6 h-6 text-yellow-600" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Courses Section -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">My Courses</h2>
                        <Link :href="route('my-courses')" class="text-blue-600 hover:text-blue-700 text-sm">
                            View All →
                        </Link>
                    </div>
                    
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div v-for="enrollment in enrollments" :key="enrollment.id" class="p-6 hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <span v-if="enrollment.course.badge" class="text-xs px-2 py-1 bg-blue-100 text-blue-600 rounded">
                                            {{ enrollment.course.badge }}
                                        </span>
                                        <div class="flex items-center space-x-1">
                                            <Star class="w-4 h-4 text-yellow-400 fill-yellow-400" />
                                            <span class="text-sm">{{ enrollment.course.rating }}</span>
                                        </div>
                                    </div>
                                    
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ enrollment.course.title }}
                                    </h3>
                                    
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        Instructor: {{ enrollment.course.instructor }}
                                    </p>
                                    
                                    <div class="flex items-center space-x-4 text-sm mb-3">
                                        <span class="text-gray-500 dark:text-gray-400">
                                            Enrolled: {{ formatDate(enrollment.enrolled_at) }}
                                        </span>
                                        <span class="text-blue-600 dark:text-blue-400">
                                            Progress: {{ enrollment.progress }}%
                                        </span>
                                    </div>
                                    
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full transition-all" 
                                             :style="{ width: enrollment.progress + '%' }"></div>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-3">
                                    <Link :href="route('student.course.learn', enrollment.course)" 
                                          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                        Continue Learning
                                    </Link>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="enrollments.length === 0" class="p-12 text-center">
                            <BookOpen class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                            <p class="text-gray-500 dark:text-gray-400 mb-4">You haven't enrolled in any courses yet</p>
                            <Link :href="route('home')" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Browse Courses
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity & Recommendations -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                    <!-- Recent Activity -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-bold mb-4 dark:text-white">Recent Activity</h3>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3 text-sm">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <span class="text-gray-600 dark:text-gray-400">
                                    You enrolled in {{ recentEnrollments }} new {{ recentEnrollments === 1 ? 'course' : 'courses' }} this month
                                </span>
                            </div>
                            <div class="flex items-center space-x-3 text-sm">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <span class="text-gray-600 dark:text-gray-400">
                                    Average progress: {{ totalProgress }}% across all courses
                                </span>
                            </div>
                            <div class="flex items-center space-x-3 text-sm">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ certificates.length }} {{ certificates.length === 1 ? 'certificate' : 'certificates' }} earned
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Learning Stats -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-bold mb-4 dark:text-white">Learning Stats</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">Overall Progress</span>
                                    <span class="font-semibold dark:text-white">{{ totalProgress }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" :style="{ width: totalProgress + '%' }"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">Courses Completed</span>
                                    <span class="font-semibold dark:text-white">{{ completedCourses }} / {{ totalCourses }}</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" :style="{ width: (completedCourses / totalCourses * 100) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { BookOpen, CheckCircle, TrendingUp, Award, Star } from 'lucide-vue-next'

const props = defineProps({
    enrollments: Array,
    totalCourses: Number,
    completedCourses: Number,
    totalProgress: Number,
    recentEnrollments: Number,
    certificates: Array,
    user: Object
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}
</script>