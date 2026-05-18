<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ArrowLeft, Edit, Trash2, Eye, Users, Trophy, Clock, CheckCircle, XCircle } from 'lucide-vue-next'

const props = defineProps({
    quiz: Object,
    recentAttempts: Array,
    passRate: Number
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const togglePublish = () => {
    router.post(`/admin/quizzes/${props.quiz.id}/toggle-publish`)
}

const deleteQuiz = () => {
    if (confirm('Are you sure you want to delete this quiz?')) {
        router.delete(`/admin/quizzes/${props.quiz.id}`)
    }
}
</script>

<template>
    <Head :title="`Quiz Details - ${quiz.title}`" />
    
    <AdminLayout>
        <div class="p-6">
            <div class="mb-6">
                <Link href="/admin/quizzes" class="flex items-center gap-2 text-slate-600 mb-4 hover:text-blue-600">
                    <ArrowLeft class="w-4 h-4" /> Back to Quizzes
                </Link>
                
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold dark:text-white">{{ quiz.title }}</h1>
                        <p class="text-slate-500 mt-1">{{ quiz.course?.title }} • {{ quiz.course?.instructor?.full_name }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="togglePublish" :class="['px-4 py-2 rounded-lg font-medium', quiz.is_published ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600']">
                            {{ quiz.is_published ? 'Unpublish' : 'Publish' }}
                        </button>
                        <Link :href="`/admin/quizzes/${quiz.id}/edit`" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Edit Quiz
                        </Link>
                        <button @click="deleteQuiz" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Questions</p>
                    <p class="text-2xl font-bold">{{ quiz.questions?.length || 0 }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Total Attempts</p>
                    <p class="text-2xl font-bold">{{ quiz.attempts_count || 0 }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Pass Rate</p>
                    <p class="text-2xl font-bold text-green-600">{{ passRate }}%</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Passing Score</p>
                    <p class="text-2xl font-bold text-blue-600">{{ quiz.passing_score }}%</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Time Limit</p>
                    <p class="text-2xl font-bold">{{ quiz.time_limit_minutes || 'No' }} min</p>
                </div>
            </div>

            <!-- Questions Section -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border mb-6">
                <div class="p-6 border-b flex justify-between items-center">
                    <h2 class="text-xl font-bold">Questions ({{ quiz.questions?.length || 0 }})</h2>
                    <Link :href="`/admin/quizzes/${quiz.id}/questions`" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Manage Questions
                    </Link>
                </div>
                <div class="divide-y">
                    <div v-for="(question, idx) in quiz.questions?.slice(0, 5)" :key="question.id" class="p-6">
                        <div class="flex items-start gap-3">
                            <span class="font-bold text-blue-600">{{ idx + 1 }}.</span>
                            <div class="flex-1">
                                <p class="font-medium">{{ question.question_text }}</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-full">{{ question.type }}</span>
                                    <span class="text-xs text-slate-500">{{ question.points }} points</span>
                                    <span class="text-xs text-green-600">Answer: {{ question.correct_answer }}</span>
                                </div>
                                <div v-if="question.options" class="mt-2">
                                    <p class="text-xs text-slate-500">Options: {{ question.options?.join(', ') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Attempts -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold">Recent Attempts</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium">Student</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Score</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Date</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="attempt in recentAttempts" :key="attempt.id">
                                <td class="px-6 py-4">{{ attempt.user?.name }}</td>
                                <td class="px-6 py-4 font-bold">{{ attempt.score }}%</td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2 py-1 text-xs rounded-full', attempt.is_passed ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                                        {{ attempt.is_passed ? 'Passed' : 'Failed' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ formatDate(attempt.created_at) }}</td>
                                <td class="px-6 py-4">
                                    <Link :href="`/admin/quizzes/${quiz.id}/attempts/${attempt.id}`" class="text-blue-600 hover:underline text-sm">
                                        View Details
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>