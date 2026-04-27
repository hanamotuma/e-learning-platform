<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    quiz: Object
});

const showModal = ref(false);

// 1. Initialize form to match the Controller's validation rules
const form = useForm({
    question_text: '',
    question_type: 'mcq',
    points: 1,
    // The controller expects options to be an array of objects
    options: [
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false },
    ],
    correct_text: '', // For short_answer
});

// 2. Watch for type changes to reset data structure
watch(() => form.question_type, (newType) => {
    form.correct_text = '';
    if (newType === 'mcq') {
        form.options = [
            { option_text: '', is_correct: false },
            { option_text: '', is_correct: false },
            { option_text: '', is_correct: false },
            { option_text: '', is_correct: false },
        ];
    } else if (newType === 'true_false') {
        form.options = [
            { option_text: 'True', is_correct: false },
            { option_text: 'False', is_correct: false },
        ];
    } else {
        form.options = []; // Short answer uses correct_text instead
    }
});

const addOption = () => {
    form.options.push({ option_text: '', is_correct: false });
};

const removeOption = (index) => {
    form.options.splice(index, 1);
};

// 3. The Submit Function
const submit = () => {
    form.post(route('admin.quizzes.questions.store', props.quiz.id), {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};
</script>


<template>
    <div class="mt-12 bg-[#16103a] rounded-3xl border border-white/5 p-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Quiz Questions ({{ quiz.questions?.length || 0 }})</h2>
            <button @click="showModal = true" class="bg-indigo-600 hover:bg-indigo-700 px-6 py-2 rounded-xl font-bold transition">
                + New Question
            </button>
        </div>

        <div class="space-y-4">
            <div v-for="(q, idx) in quiz.questions" :key="q.id" class="p-6 bg-white/5 rounded-2xl border border-white/10 flex justify-between items-center">
                <div>
                    <div class="flex gap-2 mb-2">
                        <span class="text-[10px] uppercase font-black px-2 py-1 bg-indigo-500/20 text-indigo-400 rounded-md">
                            {{ q.question_type }}
                        </span>
                        <span class="text-[10px] uppercase font-black px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded-md">
                            {{ q.points }} Points
                        </span>
                    </div>
                    <p class="text-gray-100 font-medium">{{ idx + 1 }}. {{ q.question_text }}</p>
                </div>
                <button @click="removeQuestion(q.id)" class="text-red-400 hover:bg-red-400/10 p-2 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-[#09091a]/95 backdrop-blur-md z-[100] flex items-center justify-center p-4">
            <div class="bg-[#161937] border border-white/10 w-full max-w-2xl rounded-[2.5rem] p-10 shadow-2xl overflow-y-auto max-h-[90vh]">
                <h3 class="text-2xl font-bold mb-8">Add Question</h3>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-[10px] uppercase font-black text-gray-500 mb-2">Question Text</label>
                        <textarea v-model="form.question_text" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white outline-none focus:border-indigo-500" rows="3" required></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] uppercase font-black text-gray-500 mb-2">Type</label>
                            <select v-model="form.question_type" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white outline-none">
                                <option value="mcq">Multiple Choice</option>
                                <option value="true_false">True/False</option>
                                <option value="short_answer">Short Answer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase font-black text-gray-500 mb-2">Points</label>
                            <input v-model="form.points" type="number" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white outline-none" />
                        </div>
                    </div>

                    <div v-if="form.question_type === 'mcq'" class="space-y-3">
                        <label class="block text-[10px] uppercase font-black text-gray-500">Options & Correct Answers</label>
                        <div v-for="(opt, index) in form.options" :key="index" class="flex gap-3">
                            <input type="checkbox" :value="form.options[index]" v-model="form.correct_answer" class="w-6 h-6 rounded bg-white/5 border-white/10 text-indigo-600 focus:ring-0" />
                            <input v-model="form.options[index]" type="text" placeholder="Option text" class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white outline-none" />
                        </div>
                    </div>

                    <div v-if="form.question_type === 'true_false'" class="flex gap-4">
                        <button type="button" v-for="val in ['True', 'False']" :key="val" 
                            @click="form.correct_answer = [val]"
                            :class="form.correct_answer.includes(val) ? 'bg-indigo-600' : 'bg-white/5'"
                            class="flex-1 py-4 rounded-2xl border border-white/10 font-bold transition">
                            {{ val }}
                        </button>
                    </div>

                    <div v-if="form.question_type === 'short_answer'">
                        <label class="block text-[10px] uppercase font-black text-gray-500 mb-2">Expected Answer</label>
                        <input v-model="form.correct_answer[0]" type="text" placeholder="The exact correct text" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white outline-none" />
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase font-black text-gray-500 mb-2">Explanation (Optional)</label>
                        <textarea v-model="form.explanation" class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-white outline-none" rows="2"></textarea>
                    </div>

                    <div class="flex gap-4 pt-6">
                        <button type="button" @click="showModal = false" class="flex-1 py-4 text-gray-400 font-bold">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="flex-1 bg-indigo-600 py-4 rounded-2xl font-bold shadow-lg shadow-indigo-500/20">
                            {{ form.processing ? 'Saving...' : 'Add Question' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>