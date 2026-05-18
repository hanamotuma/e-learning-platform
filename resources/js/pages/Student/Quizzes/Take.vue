<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { Clock, ChevronLeft, ChevronRight, CheckCircle } from 'lucide-vue-next'

const props = defineProps({
    attempt: Object,
    quiz: Object
})

const currentQuestionIndex = ref(0)
const answers = ref({})
const timeLeft = ref(props.quiz.time_limit_minutes ? props.quiz.time_limit_minutes * 60 : null)
let timer = null

const questions = computed(() => props.quiz.questions || [])
const currentQuestion = computed(() => questions.value[currentQuestionIndex.value])
const progress = computed(() => ((currentQuestionIndex.value + 1) / questions.value.length) * 100)

const formattedTime = computed(() => {
    if (!timeLeft.value) return 'Unlimited'
    const mins = Math.floor(timeLeft.value / 60)
    const secs = timeLeft.value % 60
    return `${mins}:${secs < 10 ? '0' : ''}${secs}`
})

const selectAnswer = (answer) => {
    answers.value[currentQuestion.value.id] = answer
}

const nextQuestion = () => {
    if (currentQuestionIndex.value < questions.value.length - 1) {
        currentQuestionIndex.value++
    }
}

const prevQuestion = () => {
    if (currentQuestionIndex.value > 0) {
        currentQuestionIndex.value--
    }
}

const submitQuiz = () => {
    if (confirm('Are you sure you want to submit? You cannot change answers after submission.')) {
        form.answers = answers.value
        form.post(route('student.quizzes.submit', [props.quiz.course_id, props.quiz.id, props.attempt.id]))
    }
}

const form = useForm({ answers: {} })

onMounted(() => {
    if (timeLeft.value) {
        timer = setInterval(() => {
            if (timeLeft.value > 0) {
                timeLeft.value--
            } else {
                clearInterval(timer)
                submitQuiz()
            }
        }, 1000)
    }
})

onUnmounted(() => {
    if (timer) clearInterval(timer)
})
</script>

<template>
    <Head :title="`Taking Quiz: ${quiz.title}`" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 py-8">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Header -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold dark:text-white">{{ quiz.title }}</h1>
                        <p class="text-sm text-slate-500 mt-1">Question {{ currentQuestionIndex + 1 }} of {{ questions.length }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-500 uppercase">Time Remaining</p>
                        <p class="text-2xl font-mono font-bold text-blue-600">{{ formattedTime }}</p>
                    </div>
                </div>
                <div class="mt-4 h-2 bg-slate-200 rounded-full">
                    <div class="h-full bg-blue-600 rounded-full transition-all" :style="{ width: progress + '%' }"></div>
                </div>
            </div>

            <!-- Question Card -->
            <div v-if="currentQuestion" class="bg-white dark:bg-slate-800 rounded-xl border p-8 mb-6">
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-full">{{ currentQuestion.points }} points</span>
                        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-700 rounded-full">{{ currentQuestion.type }}</span>
                    </div>
                    <h2 class="text-xl font-semibold dark:text-white">{{ currentQuestion.question_text }}</h2>
                </div>

                <!-- Multiple Choice Options -->
                <div v-if="currentQuestion.type === 'multiple_choice'" class="space-y-3">
                    <button 
                        v-for="(option, idx) in currentQuestion.options" 
                        :key="idx"
                        @click="selectAnswer(option)"
                        :class="[
                            'w-full text-left p-4 rounded-xl border transition-all',
                            answers[currentQuestion.id] === option 
                                ? 'bg-blue-600 text-white border-blue-600' 
                                : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-blue-500'
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <div :class="[
                                'w-6 h-6 rounded-full border flex items-center justify-center text-sm',
                                answers[currentQuestion.id] === option ? 'bg-white text-blue-600 border-white' : 'border-slate-400'
                            ]">
                                {{ String.fromCharCode(65 + idx) }}
                            </div>
                            {{ option }}
                        </div>
                    </button>
                </div>

                <!-- True/False Options -->
                <div v-if="currentQuestion.type === 'true_false'" class="flex gap-4">
                    <button 
                        v-for="option in ['True', 'False']" 
                        :key="option"
                        @click="selectAnswer(option)"
                        :class="[
                            'flex-1 py-4 rounded-xl border font-semibold transition-all',
                            answers[currentQuestion.id] === option 
                                ? 'bg-blue-600 text-white border-blue-600' 
                                : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-blue-500'
                        ]"
                    >
                        {{ option }}
                    </button>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between gap-4">
                <button 
                    @click="prevQuestion" 
                    :disabled="currentQuestionIndex === 0"
                    class="px-6 py-3 rounded-xl border font-medium disabled:opacity-50 hover:bg-slate-50 dark:hover:bg-slate-800"
                >
                    <ChevronLeft class="w-4 h-4 inline mr-1" /> Previous
                </button>
                
                <button 
                    v-if="currentQuestionIndex < questions.length - 1"
                    @click="nextQuestion" 
                    class="px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700"
                >
                    Next <ChevronRight class="w-4 h-4 inline ml-1" />
                </button>
                
                <button 
                    v-else
                    @click="submitQuiz"
                    :disabled="form.processing"
                    class="px-6 py-3 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 flex items-center gap-2"
                >
                    <CheckCircle class="w-4 h-4" />
                    Submit Quiz
                </button>
            </div>
        </div>
    </div>
</template>