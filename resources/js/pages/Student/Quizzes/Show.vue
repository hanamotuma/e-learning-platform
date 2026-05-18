<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { Clock, Trophy, AlertCircle, Play } from 'lucide-vue-next'

const props = defineProps({
    quiz: Object,
    attempts: Array,
    hasPassed: Boolean,
    canAttempt: Boolean,
    remainingAttempts: Number
})

const startQuiz = () => {
    router.post(`/courses/${props.quiz.course_id}/quizzes/${props.quiz.id}/start`)
}
</script>

<template>
    <Head :title="quiz.title" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 py-8">
        <div class="max-w-3xl mx-auto px-4">
            <!-- Quiz Header -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border p-8 mb-6">
                <h1 class="text-3xl font-bold mb-3">{{ quiz.title }}</h1>
                <p class="text-slate-600 dark:text-slate-300 mb-6">{{ quiz.description || 'No description provided' }}</p>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <Clock class="w-5 h-5 mx-auto mb-1 text-blue-600" />
                        <p class="text-xs text-slate-500">Time Limit</p>
                        <p class="font-semibold">{{ quiz.time_limit_minutes || 'No' }} min</p>
                    </div>
                    <div class="text-center">
                        <Trophy class="w-5 h-5 mx-auto mb-1 text-yellow-600" />
                        <p class="text-xs text-slate-500">Passing Score</p>
                        <p class="font-semibold">{{ quiz.passing_score }}%</p>
                    </div>
                    <div class="text-center">
                        <Play class="w-5 h-5 mx-auto mb-1 text-green-600" />
                        <p class="text-xs text-slate-500">Attempts</p>
                        <p class="font-semibold">{{ quiz.max_attempts }}</p>
                    </div>
                    <div class="text-center">
                        <AlertCircle class="w-5 h-5 mx-auto mb-1 text-purple-600" />
                        <p class="text-xs text-slate-500">Questions</p>
                        <p class="font-semibold">{{ quiz.questions?.length || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Attempts History -->
            <div v-if="attempts.length > 0" class="bg-white dark:bg-slate-800 rounded-xl border p-6 mb-6">
                <h2 class="text-xl font-bold mb-4">Your Attempts</h2>
                <div class="space-y-3">
                    <div v-for="(attempt, idx) in attempts" :key="attempt.id" class="flex justify-between items-center p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                        <div>
                            <p class="font-medium">Attempt #{{ idx + 1 }}</p>
                            <p class="text-sm text-slate-500">{{ new Date(attempt.created_at).toLocaleString() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold" :class="attempt.is_passed ? 'text-green-600' : 'text-red-600'">{{ attempt.score || 0 }}%</p>
                            <p class="text-xs text-slate-500">{{ attempt.is_passed ? 'Passed' : 'Failed' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Start Quiz Button -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border p-6 text-center">
                <div v-if="hasPassed" class="text-green-600 mb-4">
                    <CheckCircle class="w-12 h-12 mx-auto mb-2" />
                    <p class="font-semibold">You have already passed this quiz!</p>
                </div>
                <div v-else-if="!canAttempt" class="text-red-600 mb-4">
                    <XCircle class="w-12 h-12 mx-auto mb-2" />
                    <p class="font-semibold">You have no attempts remaining</p>
                </div>
                <div v-else>
                    <p class="text-slate-500 mb-4">You have {{ remainingAttempts }} attempt(s) remaining</p>
                    <button @click="startQuiz" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">
                        Start Quiz
                    </button>
                </div>
                <Link :href="`/courses/${quiz.course_id}/learn`" class="inline-block mt-4 text-sm text-blue-600 hover:underline">
                    Back to Course
                </Link>
            </div>
        </div>
    </div>
</template>