<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    quizId: Number
});

const emit = defineEmits(['close']);

const form = useForm({
    question_text: '',
    question_type: 'mcq',
    // Options are objects to match the relational DB structure
    options: [
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false },
    ],
    points: 1,
    explanation: ''
});

// Ensures only one choice is correct for MCQ/True-False
const toggleCorrect = (selectedIndex) => {
    form.options.forEach((option, index) => {
        option.is_correct = (index === selectedIndex);
    });
};

// Update options structure automatically when type changes
watch(() => form.question_type, (newType) => {
    if (newType === 'true_false') {
        form.options = [
            { option_text: 'True', is_correct: false },
            { option_text: 'False', is_correct: false }
        ];
    } else if (newType === 'mcq') {
        form.options = [
            { option_text: '', is_correct: false },
            { option_text: '', is_correct: false },
            { option_text: '', is_correct: false },
            { option_text: '', is_correct: false }
        ];
    } else {
        form.options = []; // For short answer
    }
});

const submit = () => {
    // Note: ensure this route name matches 'php artisan route:list'
    // Usually it is 'admin.questions.store'
    form.post(route('admin.admin.quizzes.questions.store', { quiz: props.quizId }), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <div>
            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Question</label>
            <textarea 
                v-model="form.question_text" 
                required 
                placeholder="What is the capital of..."
                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 mt-1 focus:border-indigo-500 outline-none transition text-white" 
                rows="2"
            ></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Type</label>
                <select v-model="form.question_type" class="w-full bg-[#1a1d3d] border border-white/10 rounded-xl px-4 py-3 mt-1 outline-none focus:border-indigo-500 text-white">
                    <option value="mcq">Multiple Choice</option>
                    <option value="true_false">True / False</option>
                    <option value="short_answer">Short Answer</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Points</label>
                <input v-model="form.points" type="number" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 mt-1 outline-none focus:border-indigo-500 text-white" />
            </div>
        </div>

        <div v-if="form.question_type !== 'short_answer'" class="space-y-3">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Options (Select the correct one)</label>
            
            <div v-for="(option, index) in form.options" :key="index" class="flex items-center gap-3 group">
                <input 
                    type="radio" 
                    name="correct_option"
                    :checked="option.is_correct"
                    @change="toggleCorrect(index)"
                    class="w-5 h-5 rounded-full border-white/10 text-indigo-600 focus:ring-indigo-500 bg-white/5 cursor-pointer"
                />
                
                <input 
                    v-model="option.option_text" 
                    type="text" 
                    required
                    placeholder="Enter option..." 
                    class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-2 outline-none focus:border-indigo-500 text-white group-hover:border-white/20 transition" 
                />
            </div>
        </div>

        <div>
            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Explanation (Optional)</label>
            <textarea 
                v-model="form.explanation" 
                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 mt-1 focus:border-indigo-500 outline-none transition text-white text-sm" 
                rows="2"
                placeholder="Why is this the correct answer?"
            ></textarea>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="button" @click="$emit('close')" class="flex-1 px-6 py-3 rounded-xl bg-white/5 hover:bg-white/10 font-bold transition">Cancel</button>
            <button type="submit" :disabled="form.processing" class="flex-1 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 font-bold transition shadow-lg shadow-indigo-600/20">
                <span v-if="form.processing">Saving...</span>
                <span v-else>Save Question</span>
            </button>
        </div>
    </form>
</template>