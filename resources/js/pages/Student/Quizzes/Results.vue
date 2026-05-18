<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { CheckCircle, XCircle, Trophy, Clock, ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
    attempt: Object,
    quiz: Object
})

const getScoreColor = () => {
    if (props.attempt.is_passed) return 'text-green-600'
    return 'text-red-600'
}

const formatDate = (date) => {
    return new Date(date).toLocaleString()
}
</script>

<template>
    <Head :title="`Quiz Results - ${quiz.title}`" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 py-8">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Results Header -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border p-8 text-center mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4" :class="attempt.is_passed ? 'bg-green-100' : 'bg-red-100'">
                    <Trophy v-if="attempt.is_passed" class="w-10 h-10 text-green-600" />
                    <XCircle v-else class="w-10 h-10 text-red-600" />
                </div>
                <h1 class="text-3xl font-bold mb-2">{{ attempt.is_passed ? 'Congratulations!' : 'Quiz Completed' }}</h1>
                <p class="text-slate-500">You scored <span :class="['text-2xl font-bold', getScoreColor()]">{{ attempt.score }}%</span></p>
                <p class="text-sm text-slate-400 mt-2">Passing score: {{ quiz.passing_score }}%</p>
            </div>

            <!-- Score Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl border p-4 text-center">
                    <p class="text-xs text-slate-500">Your Score</p>
                    <p class="text-2xl font-bold" :class="getScoreColor()">{{ attempt.score }}%</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl border p-4 text-center">
                    <p class="text-xs text-slate-500">Points Earned</p>
                    <p class="text-2xl font-bold">{{ attempt.score ? (attempt.score / 100 * (attempt.total_points || 0)).toFixed(0) : 0 }} / {{ attempt.total_points || 0 }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl border p-4 text-center">
                    <p class="text-xs text-slate-500">Completed</p>
                    <p class="text-2xl font-bold">{{ formatDate(attempt.completed_at).split(',')[0] }}</p>
                </div>
            </div>

            <!-- Answer Review -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold">Answer Review</h2>
                </div>
                <div class="divide-y">
                    <div v-for="(result, questionId, idx) in attempt.answers" :key="questionId" class="p-6">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <CheckCircle v-if="result.is_correct" class="w-5 h-5 text-green-600" />
                                <XCircle v-else class="w-5 h-5 text-red-600" />
                            </div>
                            <div class="flex-1">
                                <p class="font-medium mb-2">Q{{ idx + 1 }}. {{ result.question_text || 'Question' }}</p>
                                <div class="text-sm space-y-1">
                                    <p><span class="text-slate-500">Your answer:</span> 
                                        <span :class="result.is_correct ? 'text-green-600' : 'text-red-600'">{{ result.user_answer || 'Not answered' }}</span>
                                    </p>
                                    <p v-if="!result.is_correct"><span class="text-slate-500">Correct answer:</span> 
                                        <span class="text-green-600">{{ result.correct_answer }}</span>
                                    </p>
                                    <p v-if="result.explanation" class="mt-2 p-2 bg-slate-50 rounded">
                                        📝 {{ result.explanation }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium">{{ result.is_correct ? '+' + result.points : '0' }} / {{ result.points }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex gap-4">
                <Link :href="`/courses/${quiz.course_id}/learn`" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl text-center font-medium hover:bg-blue-700">
                    Back to Course
                </Link>
                <Link :href="`/courses/${quiz.course_id}/quizzes/${quiz.id}`" class="px-6 py-3 border rounded-xl text-center font-medium hover:bg-slate-50">
                    Try Again
                </Link>
            </div>
        </div>
    </div>
</template>