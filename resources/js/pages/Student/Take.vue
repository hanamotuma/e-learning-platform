<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    quiz: Object,
    attempt: Object
});

const currentQuestionIndex = ref(0);
const answers = ref({}); // { question_id: [index] }

// Timer Logic
const timeLeft = ref(props.quiz.time_limit_minutes * 60);
const formattedTime = computed(() => {
    const mins = Math.floor(timeLeft.value / 60);
    const secs = timeLeft.value % 60;
    return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
});

onMounted(() => {
    const timer = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
        else {
            clearInterval(timer);
            submitQuiz(); // Auto-submit when time is up
        }
    }, 1000);
});

const currentQuestion = computed(() => props.quiz.questions[currentQuestionIndex.value]);

const selectOption = (questionId, optionIndex) => {
    answers.value[questionId] = [optionIndex.toString()];
};

const form = useForm({
    answers: {}
});

const submitQuiz = () => {
    form.answers = answers.value;
    form.post(route('student.quizzes.submit', props.attempt.id));
};
</script>

<template>
    <Head :title="`Taking Quiz: ${quiz.title}`" />

    <div class="min-h-screen bg-[#0f1129] text-white p-6">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center mb-8 bg-white/5 p-6 rounded-3xl border border-white/10">
                <div>
                    <h1 class="text-xl font-bold">{{ quiz.title }}</h1>
                    <p class="text-sm text-slate-400">Question {{ currentQuestionIndex + 1 }} of {{ quiz.questions.length }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-400 uppercase font-bold tracking-widest">Time Remaining</p>
                    <p class="text-2xl font-mono font-bold text-indigo-400">{{ formattedTime }}</p>
                </div>
            </div>

            <div v-if="currentQuestion" class="bg-white/5 border border-white/10 rounded-[2.5rem] p-10 shadow-2xl">
                <h2 class="text-2xl font-medium mb-8">{{ currentQuestion.question_text }}</h2>

                <div class="space-y-4">
                    <button 
                        v-for="(option, index) in currentQuestion.options" 
                        :key="index"
                        @click="selectOption(currentQuestion.id, index)"
                        :class="[
                            'w-full text-left p-5 rounded-2xl border transition-all duration-200',
                            answers[currentQuestion.id]?.[0] == index 
                                ? 'bg-indigo-600 border-indigo-400 shadow-lg shadow-indigo-500/20' 
                                : 'bg-white/5 border-white/10 hover:border-white/30'
                        ]"
                    >
                        <div class="flex items-center gap-4">
                            <div :class="[
                                'w-6 h-6 rounded-full border flex items-center justify-center text-xs',
                                answers[currentQuestion.id]?.[0] == index ? 'bg-white text-indigo-600 border-white' : 'border-white/20'
                            ]">
                                {{ String.fromCharCode(65 + index) }}
                            </div>
                            {{ option }}
                        </div>
                    </button>
                </div>
            </div>

            <div class="flex justify-between mt-8">
                <button 
                    @click="currentQuestionIndex--" 
                    :disabled="currentQuestionIndex === 0"
                    class="px-8 py-3 rounded-xl bg-white/5 font-bold disabled:opacity-20"
                >
                    Previous
                </button>

                <button 
                    v-if="currentQuestionIndex < quiz.questions.length - 1"
                    @click="currentQuestionIndex++" 
                    class="px-8 py-3 rounded-xl bg-indigo-600 font-bold hover:bg-indigo-700 transition"
                >
                    Next Question
                </button>

                <button 
                    v-else
                    @click="submitQuiz"
                    :disabled="form.processing"
                    class="px-8 py-3 rounded-xl bg-emerald-600 font-bold hover:bg-emerald-700 transition shadow-lg shadow-emerald-500/20"
                >
                    Finish Quiz
                </button>
            </div>
        </div>
    </div>
</template>