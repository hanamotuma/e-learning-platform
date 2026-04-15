<template>
    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Welcome Section -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 text-white mb-8">
                <h1 class="text-3xl font-bold mb-2">Welcome back, {{ $page.props.auth.user.full_name }}! 👋</h1>
                <p class="text-indigo-100">Continue your learning journey and expand your mind.</p>
            </div>
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Courses</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total_courses }}</p>
                        </div>
                        <div class="h-12 w-12 bg-indigo-100 rounded-full flex items-center justify-center">
                            <BookOpenIcon class="h-6 w-6 text-indigo-600" />
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Completed</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.completed_courses }}</p>
                        </div>
                        <div class="h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                            <CheckCircleIcon class="h-6 w-6 text-green-600" />
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">In Progress</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.in_progress_courses }}</p>
                        </div>
                        <div class="h-12 w-12 bg-yellow-100 rounded-full flex items-center justify-center">
                            <ClockIcon class="h-6 w-6 text-yellow-600" />
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Avg. Progress</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.average_progress }}%</p>
                        </div>
                        <div class="h-12 w-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <TrendingUpIcon class="h-6 w-6 text-purple-600" />
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Active Courses -->
            <div class="mb-12">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">My Active Courses</h2>
                    <Link href="/my-courses" class="text-indigo-600 hover:text-indigo-700">View All →</Link>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="enrollment in enrolledCourses" :key="enrollment.course.course_id" 
                         class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-lg transition">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-xs rounded-full">
                                    {{ enrollment.course.category.category_name }}
                                </span>
                                <span class="text-sm text-gray-500">{{ enrollment.course.difficulty_level }}</span>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ enrollment.course.title }}</h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ enrollment.course.description }}</p>
                            
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Progress</span>
                                    <span>{{ enrollment.progress_percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full transition-all" 
                                         :style="{ width: `${enrollment.progress_percentage}%` }"></div>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-1">
                                    <UserIcon class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm text-gray-600">{{ enrollment.course.instructor.full_name }}</span>
                                </div>
                                <Link :href="`/courses/${enrollment.course.course_id}/learn`" 
                                      class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm">
                                    Continue Learning
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recommended Courses -->
            <div>
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Recommended For You</h2>
                    <Link href="/courses" class="text-indigo-600 hover:text-indigo-700">Browse All →</Link>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="course in recommendedCourses" :key="course.course_id" 
                         class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-lg transition">
                        <div class="relative h-48 bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                            <div class="text-white text-center">
                                <div class="text-4xl mb-2">📚</div>
                                <p class="text-sm">Course Preview</p>
                            </div>
                            <div class="absolute top-2 right-2 bg-white rounded-full px-2 py-1 text-xs font-semibold text-indigo-600">
                                {{ course.price === 0 ? 'FREE' : `$${course.price}` }}
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ course.title }}</h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ course.description }}</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-1">
                                    <StarIcon class="h-4 w-4 text-yellow-400 fill-current" />
                                    <span class="text-sm text-gray-600">{{ course.average_rating || 'No ratings' }}</span>
                                </div>
                                <Link :href="`/courses/${course.course_id}`" 
                                      class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                                    View Course →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import MainLayout from '../../layouts/MainLayout.vue';
import { BookOpenIcon } from '@heroicons/vue/24/outline';
defineProps({
    stats: Object,
    enrolledCourses: Array,
    recommendedCourses: Array,
});
</script>