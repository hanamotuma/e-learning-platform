<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { PlusCircle, Edit, Eye, Trash2, BookOpen } from 'lucide-vue-next'

const props = defineProps({
    courses: {
        type: Object,
        default: () => ({ data: [] })
    }
})

const deleteCourse = (id, title) => {
    if (confirm(`Delete "${title}"? This action cannot be undone.`)) {
        router.delete(route('instructor.courses.destroy', { course: id }))
    }
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0)
}
</script>

<template>
    <Head title="My Courses | Instructor Portal" />
    
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-black dark:text-white">My Courses</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Manage and organize your courses
                    </p>
                </div>
                <Link 
                    :href="route('instructor.courses.create')" 
                    class="inline-flex items-center space-x-2 px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20"
                >
                    <PlusCircle class="w-4 h-4" />
                    <span>Create New Course</span>
                </Link>
            </div>
            
            <!-- Courses Grid -->
            <div v-if="courses.data.length === 0" class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                <BookOpen class="w-16 h-16 text-slate-400 mx-auto mb-4" />
                <h3 class="text-xl font-bold dark:text-white mb-2">No courses yet</h3>
                <p class="text-slate-500 mb-6">Start creating your first course and share your knowledge</p>
                <Link :href="route('instructor.courses.create')" class="px-6 py-3 bg-blue-600 text-white rounded-xl">
                    Create Your First Course
                </Link>
            </div>
            
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="course in courses.data" :key="course.id" 
                    class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden hover:shadow-xl transition-all group">
                    
                    <!-- Thumbnail -->
                    <div class="relative h-48 overflow-hidden">
                        <img 
                            :src="course.thumbnail || 'https://placehold.co/600x400?text=No+Thumbnail'" 
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                        <span :class="[
                            'absolute top-3 right-3 px-2 py-1 text-xs font-bold rounded-lg',
                            course.status === 'published' ? 'bg-emerald-500 text-white' :
                            course.status === 'draft' ? 'bg-yellow-500 text-white' :
                            'bg-slate-500 text-white'
                        ]">
                            {{ course.status }}
                        </span>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-5">
                        <h3 class="font-bold text-lg dark:text-white mb-1 line-clamp-1">{{ course.title }}</h3>
                        <p class="text-xs text-slate-500 mb-2">{{ course.category?.name || 'Uncategorized' }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400 line-clamp-2 mb-3">
                            {{ course.description || 'No description' }}
                        </p>
                        
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-2xl font-black text-blue-600">{{ formatCurrency(course.price) }}</span>
                            <span class="text-xs text-slate-500">{{ course.enrollments_count || 0 }} students</span>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center space-x-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <Link 
                                :href="route('instructor.courses.edit', { course: course.id })"
                                class="flex-1 flex items-center justify-center space-x-1 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors"
                            >
                                <Edit class="w-4 h-4" />
                                <span class="text-sm font-medium">Edit</span>
                            </Link>
                            <button 
                                @click="deleteCourse(course.id, course.title)"
                                class="flex-1 flex items-center justify-center space-x-1 px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 rounded-xl hover:bg-red-100 transition-colors"
                            >
                                <Trash2 class="w-4 h-4" />
                                <span class="text-sm font-medium">Delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pagination -->
            <div v-if="courses.links && courses.links.length > 3" class="mt-8 flex justify-center">
                <div class="flex space-x-2">
                    <Link 
                        v-for="link in courses.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                            link.active 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </div>
</template>