<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import InstructorLayout from '@/layouts/InstructorLayout.vue'
import { PlusCircle, Edit, Eye, Trash2, BookOpen, CheckCircle, Clock, XCircle, AlertCircle } from 'lucide-vue-next'

const props = defineProps({
    courses: Object,
    stats: Object
})

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US').format(amount || 0) + ' ETB'
}

const getStatusBadge = (course) => {
    if (course.approval_status === 'pending') {
        return { text: 'Pending Review', color: 'bg-yellow-100 text-yellow-700', icon: Clock }
    } else if (course.approval_status === 'approved') {
        return { text: 'Approved', color: 'bg-green-100 text-green-700', icon: CheckCircle }
    } else if (course.approval_status === 'rejected') {
        return { text: 'Rejected', color: 'bg-red-100 text-red-700', icon: XCircle }
    }
    return { text: 'Draft', color: 'bg-gray-100 text-gray-700', icon: AlertCircle }
}
</script>

<template>
    <Head title="My Courses | Instructor" />
    
    <InstructorLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold dark:text-white">My Courses</h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-1">Manage and track your course approval status</p>
                </div>
                <Link href="/instructor/courses/create" class="px-5 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 flex items-center gap-2">
                    <PlusCircle class="w-4 h-4" />
                    Create New Course
                </Link>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Total Courses</p>
                    <p class="text-2xl font-bold">{{ stats?.total_courses || 0 }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Pending Approval</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ stats?.pending || 0 }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Approved</p>
                    <p class="text-2xl font-bold text-green-600">{{ stats?.approved || 0 }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Rejected</p>
                    <p class="text-2xl font-bold text-red-600">{{ stats?.rejected || 0 }}</p>
                </div>
            </div>

            <!-- Courses Grid -->
            <div v-if="courses?.data?.length === 0" class="bg-white dark:bg-slate-800 rounded-xl p-12 text-center border">
                <BookOpen class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                <p class="text-slate-500 mb-2">No courses created yet</p>
                <Link href="/instructor/courses/create" class="px-5 py-2 bg-orange-600 text-white rounded-lg">Create Your First Course</Link>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="course in courses?.data" :key="course.id" class="bg-white dark:bg-slate-800 rounded-xl border overflow-hidden hover:shadow-lg transition-all">
                    <img :src="course.image || '/default-course.jpg'" class="w-full h-48 object-cover" />
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span :class="['px-2 py-1 text-xs rounded-full flex items-center gap-1', getStatusBadge(course).color]">
                                <component :is="getStatusBadge(course).icon" class="w-3 h-3" />
                                {{ getStatusBadge(course).text }}
                            </span>
                            <div class="flex items-center gap-1 text-yellow-400">
                                <Star class="w-4 h-4 fill-yellow-400" />
                                <span class="text-sm">{{ course.rating || 0 }}</span>
                            </div>
                        </div>
                        <h3 class="font-bold text-lg mb-2">{{ course.title }}</h3>
                        <p class="text-sm text-slate-500 mb-3">{{ course.enrollments?.length || 0 }} students</p>
                        
                        <!-- Show rejection reason if rejected -->
                        <div v-if="course.approval_status === 'rejected' && course.rejection_reason" class="mb-3 p-2 bg-red-50 rounded-lg">
                            <p class="text-xs text-red-600 font-medium">Rejection Reason:</p>
                            <p class="text-xs text-red-500">{{ course.rejection_reason }}</p>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xl font-bold text-orange-600">{{ formatCurrency(course.price) }}</span>
                            <div class="flex gap-2">
                                <Link :href="`/instructor/courses/${course.id}/edit`" class="p-2 text-slate-400 hover:text-blue-600 rounded-lg">
                                    <Edit class="w-4 h-4" />
                                </Link>
                                <button v-if="course.approval_status === 'rejected'" @click="editAgain(course.id)" class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg">
                                    <Edit class="w-4 h-4" /> Resubmit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>