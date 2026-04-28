<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import QuestionForm from './Partials/QuestionForm.vue';

const props = defineProps({
    quiz: Object
});

const isEditing = ref(false);
const showQuestionModal = ref(false);

const form = useForm({
    title: props.quiz.title,
    description: props.quiz.description,
    time_limit_minutes: props.quiz.time_limit_minutes,
    passing_score: props.quiz.passing_score,
    is_published: props.quiz.is_published,
});

const submitUpdate = () => {
    form.put(route('admin.quizzes.update', props.quiz.id), {
        onSuccess: () => isEditing.value = false,
        preserveScroll: true,
    });
};

const deleteQuestion = (id) => {
    if (confirm('Are you sure you want to delete this question?')) {
        useForm({}).delete(route('admin.questions.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="`Quiz: ${quiz.title}`" />

    <div class="min-h-screen bg-[#0f1129] text-white p-8">
        <div class="max-w-5xl mx-auto">
            <nav class="flex text-sm text-slate-400 mb-8 items-center gap-2">
                <Link :href="route('admin.courses.show', quiz.section.course_id)" class="hover:text-indigo-400 transition">
                    {{ quiz.section.course.title }}
                </Link>
                <span>/</span>
                <span class="text-slate-200">{{ quiz.title }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white/5 border border-white/10 rounded-3xl p-8 backdrop-blur-xl">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h1 class="text-3xl font-bold mb-2">{{ quiz.title }}</h1>
                                <p class="text-slate-400">{{ quiz.description || 'No description provided.' }}</p>
                            </div>
                            <button @click="isEditing = true" class="p-3 bg-white/5 hover:bg-white/10 rounded-2xl transition text-indigo-400 border border-white/5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-2xl p-4 text-center">
                                <div class="text-xs text-indigo-300 uppercase font-bold tracking-wider mb-1">Time</div>
                                <div class="text-xl font-bold">{{ quiz.time_limit_minutes }}m</div>
                            </div>
                            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-4 text-center">
                                <div class="text-xs text-emerald-300 uppercase font-bold tracking-wider mb-1">Pass %</div>
                                <div class="text-xl font-bold">{{ quiz.passing_score }}%</div>
                            </div>
                            <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 text-center">
                                <div class="text-xs text-amber-300 uppercase font-bold tracking-wider mb-1">Questions</div>
                                <div class="text-xl font-bold">{{ quiz.questions?.length || 0 }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/5 border border-white/10 rounded-3xl p-8 backdrop-blur-xl">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold">Quiz Questions</h2>
                            <button @click="showQuestionModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-lg shadow-indigo-600/20">
                                + Add Question
                            </button>
                        </div>
                        
                        <div v-if="quiz.questions?.length > 0" class="space-y-4">
                            <div v-for="(q, index) in quiz.questions" :key="q.id" class="p-6 bg-white/5 rounded-2xl border border-white/5 flex justify-between items-start hover:border-white/20 transition">
                                <div class="flex-1">
                                    <span class="text-[10px] text-indigo-400 font-black uppercase mb-1 block">Question {{ index + 1 }}</span>
                                    <p class="font-medium text-slate-200">{{ q.question_text }}</p>
                                    <div class="flex gap-2 mt-3">
                                        <span class="text-[10px] bg-white/10 px-2.5 py-1 rounded-lg uppercase tracking-tight">{{ q.question_type }}</span>
                                        <span class="text-[10px] bg-indigo-500/20 text-indigo-300 px-2.5 py-1 rounded-lg uppercase tracking-tight">{{ q.points }} Points</span>
                                    </div>
                                </div>
                                <button @click="deleteQuestion(q.id)" class="text-slate-500 hover:text-red-400 transition p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div v-else class="text-center py-12 border-2 border-dashed border-white/5 rounded-3xl">
                            <p class="text-slate-500 text-sm">No questions added yet. Start by clicking the button above.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white/5 border border-white/10 rounded-3xl p-6 backdrop-blur-xl">
                        <h3 class="font-bold mb-4">Quick Status</h3>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-400">Publish Status</span>
                            <div :class="quiz.is_published ? 'bg-emerald-500 text-white' : 'bg-slate-700 text-slate-300'" 
                                 class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                                {{ quiz.is_published ? 'Online' : 'Draft' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showQuestionModal" class="fixed inset-0 bg-[#09091a]/95 backdrop-blur-xl flex items-center justify-center z-50 p-4">
            <div class="bg-[#161937] border border-white/10 rounded-[2.5rem] w-full max-w-2xl p-10 shadow-2xl overflow-y-auto max-h-[90vh]">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold">New Question</h2>
                    <button @click="showQuestionModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>
                <QuestionForm :quiz-id="quiz.id" @close="showQuestionModal = false" />
            </div>
        </div>

        <div v-if="isEditing" class="fixed inset-0 bg-[#09091a]/90 backdrop-blur-xl flex items-center justify-center z-50 p-4">
            <div class="bg-[#161937] border border-white/10 rounded-[2.5rem] w-full max-w-lg p-10 shadow-2xl">
                <h2 class="text-2xl font-bold mb-6">Edit Quiz Settings</h2>
                <form @submit.prevent="submitUpdate" class="space-y-5">
                    <div>
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-2">Quiz Title</label>
                        <input v-model="form.title" type="text" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none transition" />
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-2">Time (Mins)</label>
                            <input v-model="form.time_limit_minutes" type="number" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none transition" />
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-2">Pass %</label>
                            <input v-model="form.passing_score" type="number" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none transition" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3 bg-white/5 p-4 rounded-2xl border border-white/10">
                        <input v-model="form.is_published" type="checkbox" id="published" class="w-5 h-5 rounded border-white/10 bg-white/5 text-indigo-600 focus:ring-indigo-500" />
                        <label for="published" class="text-sm font-medium text-slate-200">Publish this quiz to students</label>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="isEditing = false" class="flex-1 px-6 py-4 rounded-2xl bg-white/5 font-bold transition hover:bg-white/10">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="flex-1 px-6 py-4 rounded-2xl bg-indigo-600 font-bold transition shadow-lg shadow-indigo-600/20 disabled:opacity-50">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>