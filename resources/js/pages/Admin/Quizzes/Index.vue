<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Plus, Eye, Edit, Trash2, Clock, Trophy, Users, CheckCircle, XCircle } from 'lucide-vue-next'

const props = defineProps({
    quizzes: Object,
    stats: Object
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const deleteQuiz = (id) => {
    if (confirm('Are you sure you want to delete this quiz? All questions and attempts will be deleted.')) {
        router.delete(`/admin/quizzes/${id}`)
    }
}
</script>

<template>
    <Head title="Quiz Management | Admin" />
    
    <AdminLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold dark:text-white">Quiz Management</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Manage all quizzes across the platform</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Total Quizzes</p>
                    <p class="text-2xl font-bold">{{ stats.total_quizzes || 0 }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Published</p>
                    <p class="text-2xl font-bold text-green-600">{{ stats.published_quizzes || 0 }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Draft</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ stats.draft_quizzes || 0 }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Total Attempts</p>
                    <p class="text-2xl font-bold text-purple-600">{{ stats.total_attempts || 0 }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Avg Score</p>
                    <p class="text-2xl font-bold text-blue-600">{{ stats.average_score || 0 }}%</p>
                </div>
            </div>

            <!-- Quizzes Table -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium">Quiz Title</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Course</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Instructor</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Questions</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Attempts</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Passing</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="quiz in quizzes.data" :key="quiz.id">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium">{{ quiz.title }}</p>
                                        <p class="text-xs text-slate-500">{{ quiz.description?.substring(0, 50) }}...</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ quiz.course?.title }}</td>
                                <td class="px-6 py-4">{{ quiz.course?.instructor?.full_name || 'Admin' }}</td>
                                <td class="px-6 py-4">{{ quiz.questions_count || 0 }}</td>
                                <td class="px-6 py-4">{{ quiz.attempts_count || 0 }}</td>
                                <td class="px-6 py-4">{{ quiz.passing_score }}%</td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2 py-1 text-xs rounded-full', quiz.is_published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700']">
                                        {{ quiz.is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <Link :href="`/admin/quizzes/${quiz.id}`" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                        <Link :href="`/admin/quizzes/${quiz.id}/edit`" class="p-2 text-green-600 hover:bg-green-50 rounded-lg">
                                            <Edit class="w-4 h-4" />
                                        </Link>
                                        <Link :href="`/admin/quizzes/${quiz.id}/attempts`" class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg">
                                            <Users class="w-4 h-4" />
                                        </Link>
                                        <button @click="deleteQuiz(quiz.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-4 border-t">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-slate-500">Showing {{ quizzes.from || 0 }} to {{ quizzes.to || 0 }} of {{ quizzes.total }} results</p>
                        <div class="flex gap-2">
                            <Link v-for="link in quizzes.links" :key="link.label" :href="link.url || '#'" v-html="link.label" 
                                class="px-3 py-1 rounded-lg border hover:bg-slate-50" 
                                :class="{ 'bg-blue-600 text-white border-blue-600': link.active }" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>