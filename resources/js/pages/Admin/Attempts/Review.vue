<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    attempt: Object
});

const getOptionClass = (answer, option) => {
    // If this is the option the student picked
    const isPicked = answer.option_id === option.id;
    // If this is the correct option (assuming your quiz_options table has is_correct)
    const isCorrect = option.is_correct;

    if (isPicked && isCorrect) return 'bg-emerald-500/20 border-emerald-500 text-emerald-200';
    if (isPicked && !isCorrect) return 'bg-red-500/20 border-red-500 text-red-200';
    if (!isPicked && isCorrect) return 'border-emerald-500/50 border-dashed text-emerald-400';
    return 'bg-white/5 border-white/10 text-gray-400';
};
</script>

<template>
    <div class="min-h-screen bg-[#09091a] text-white p-8">
        <div class="max-w-5xl mx-auto mb-10 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-bold mb-2">Attempt Review</h1>
                <p class="text-indigo-400 font-medium">
                    Student: {{ attempt.student.name }} • Quiz: {{ attempt.quiz.title }}
                </p>
            </div>
            <div class="text-right bg-white/5 border border-white/10 p-5 rounded-3xl">
                <p class="text-xs uppercase tracking-widest text-gray-500 font-bold">Final Score</p>
                <div class="text-4xl font-black text-indigo-500">
                    {{ attempt.total_marks }} / {{ attempt.quiz_total_marks }}
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto space-y-8">
            <div v-for="(ans, index) in attempt.answers" :key="ans.id" 
                class="bg-[#16103a] border border-white/5 rounded-[2rem] p-8 shadow-xl">
                
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <span class="text-[10px] bg-white/10 px-3 py-1 rounded-full uppercase font-black text-gray-400">
                            Question {{ index + 1 }}
                        </span>
                        <h3 class="text-xl mt-3 font-medium">{{ ans.question.question_text }}</h3>
                    </div>
                    <div :class="ans.is_correct ? 'text-emerald-400' : 'text-red-400'" class="text-sm font-bold uppercase italic">
                        {{ ans.is_correct ? 'Correct' : 'Incorrect' }} (+{{ ans.marks_obtained }} Pts)
                    </div>
                </div>

                <div v-if="ans.question.question_type !== 'short_answer'" class="grid gap-3">
                    <div v-for="opt in ans.question.quiz_options" :key="opt.id"
                        :class="getOptionClass(ans, opt)"
                        class="p-4 rounded-2xl border transition flex justify-between items-center text-sm">
                        <span>{{ opt.option_text }}</span>
                        
                        <div class="flex gap-2">
                            <span v-if="opt.id === ans.option_id" class="text-[9px] font-black uppercase bg-white/10 px-2 py-0.5 rounded">Student's Choice</span>
                            <span v-if="opt.is_correct" class="text-[9px] font-black uppercase bg-emerald-500 text-white px-2 py-0.5 rounded">Correct Answer</span>
                        </div>
                    </div>
                </div>

                <div v-else class="space-y-4">
                    <div class="bg-white/5 border border-white/10 p-5 rounded-2xl">
                        <p class="text-[10px] uppercase text-gray-500 font-bold mb-2">Student's Written Answer</p>
                        <p class="italic">"{{ ans.answer_text || 'No answer provided' }}"</p>
                    </div>
                </div>

                <div v-if="ans.question.explanation" class="mt-6 p-4 bg-indigo-500/5 rounded-xl border border-indigo-500/20 text-xs text-indigo-300">
                    <strong>Explanation:</strong> {{ ans.question.explanation }}
                </div>
            </div>
        </div>
    </div>
</template>