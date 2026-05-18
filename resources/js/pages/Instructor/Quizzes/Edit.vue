<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import InstructorLayout from '@/layouts/InstructorLayout.vue'
import { ChevronLeft, Plus, Trash2, Save, Eye, X } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
    course: Object,
    quiz: Object
})

const showQuestionModal = ref(false)
const editingQuestion = ref(null)

const form = useForm({
    title: props.quiz.title,
    description: props.quiz.description || '',
    time_limit_minutes: props.quiz.time_limit_minutes || '',
    passing_score: props.quiz.passing_score || 70,
    max_attempts: props.quiz.max_attempts || 3
})

const questionForm = ref({
    question_text: '',
    type: 'multiple_choice',
    points: 1,
    options: ['', '', '', ''],
    correct_answer: '',
    explanation: ''
})

const questions = ref(props.quiz.questions || [])

const updateQuiz = () => {
    form.put(`/instructor/courses/${props.course.id}/quizzes/${props.quiz.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            alert('Quiz updated successfully')
        }
    })
}

const openAddQuestion = () => {
    editingQuestion.value = null
    questionForm.value = {
        question_text: '',
        type: 'multiple_choice',
        points: 1,
        options: ['', '', '', ''],
        correct_answer: '',
        explanation: ''
    }
    showQuestionModal.value = true
}

const editQuestion = (question) => {
    editingQuestion.value = question
    questionForm.value = {
        question_text: question.question_text,
        type: question.type,
        points: question.points,
        options: question.options || ['', '', '', ''],
        correct_answer: question.correct_answer,
        explanation: question.explanation || ''
    }
    showQuestionModal.value = true
}

const saveQuestion = async () => {
    if (!questionForm.value.question_text) {
        alert('Please enter question text')
        return
    }
    if (!questionForm.value.correct_answer) {
        alert('Please select correct answer')
        return
    }

    try {
        if (editingQuestion.value) {
            const response = await axios.put(
                `/instructor/courses/${props.course.id}/quizzes/${props.quiz.id}/questions/${editingQuestion.value.id}`,
                questionForm.value
            )
            if (response.data.success) {
                const index = questions.value.findIndex(q => q.id === editingQuestion.value.id)
                questions.value[index] = { ...editingQuestion.value, ...questionForm.value }
            }
        } else {
            const response = await axios.post(
                `/instructor/courses/${props.course.id}/quizzes/${props.quiz.id}/questions`,
                questionForm.value
            )
            if (response.data.success) {
                questions.value.push(response.data.question)
            }
        }
        showQuestionModal.value = false
        alert('Question saved successfully')
    } catch (error) {
        alert('Error saving question')
    }
}

const deleteQuestion = async (questionId) => {
    if (confirm('Delete this question?')) {
        await axios.delete(`/instructor/courses/${props.course.id}/quizzes/${props.quiz.id}/questions/${questionId}`)
        questions.value = questions.value.filter(q => q.id !== questionId)
        alert('Question deleted')
    }
}

const publishQuiz = () => {
    if (questions.value.length === 0) {
        alert('Add at least one question before publishing')
        return
    }
    router.post(`/instructor/courses/${props.course.id}/quizzes/${props.quiz.id}/publish`)
}

const goBack = () => {
    router.get(`/instructor/courses/${props.course.id}/quizzes`)
}
</script>

<template>
    <Head title="Edit Quiz | Instructor" />
    
    <InstructorLayout>
        <div class="p-6">
            <button @click="goBack" class="flex items-center gap-2 text-slate-600 mb-6 hover:text-blue-600">
                <ChevronLeft class="w-5 h-5" /> Back to Quizzes
            </button>

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Quiz Settings -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border p-6 sticky top-6">
                        <h2 class="text-xl font-bold mb-4">Quiz Settings</h2>
                        <form @submit.prevent="updateQuiz" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Quiz Title</label>
                                <input v-model="form.title" type="text" class="w-full px-4 py-2 border rounded-lg" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Description</label>
                                <textarea v-model="form.description" rows="3" class="w-full px-4 py-2 border rounded-lg"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Time Limit (minutes)</label>
                                <input v-model="form.time_limit_minutes" type="number" class="w-full px-4 py-2 border rounded-lg" placeholder="No limit" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Passing Score (%)</label>
                                <input v-model="form.passing_score" type="number" class="w-full px-4 py-2 border rounded-lg" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Max Attempts</label>
                                <input v-model="form.max_attempts" type="number" class="w-full px-4 py-2 border rounded-lg" required />
                            </div>
                            <div class="flex gap-2 pt-4">
                                <button type="submit" :disabled="form.processing" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Save Settings
                                </button>
                                <button type="button" @click="publishQuiz" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    Publish
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Questions List -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border">
                        <div class="p-6 border-b flex justify-between items-center">
                            <div>
                                <h2 class="text-xl font-bold">Questions ({{ questions.length }})</h2>
                                <p class="text-sm text-slate-500">Total points: {{ questions.reduce((sum, q) => sum + q.points, 0) }}</p>
                            </div>
                            <button @click="openAddQuestion" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                                <Plus class="w-4 h-4" /> Add Question
                            </button>
                        </div>

                        <div v-if="questions.length === 0" class="p-12 text-center">
                            <p class="text-slate-500">No questions yet. Click "Add Question" to start building your quiz.</p>
                        </div>

                        <div v-else class="divide-y">
                            <div v-for="(question, idx) in questions" :key="question.id" class="p-6 hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="font-bold text-blue-600">Q{{ idx + 1 }}.</span>
                                            <span class="text-sm px-2 py-1 bg-slate-100 rounded">{{ question.type }}</span>
                                            <span class="text-sm text-slate-500">{{ question.points }} point(s)</span>
                                        </div>
                                        <p class="font-medium mb-2">{{ question.question_text }}</p>
                                        
                                        <!-- Show options for multiple choice -->
                                        <div v-if="question.type === 'multiple_choice' && question.options" class="ml-6 space-y-1">
                                            <div v-for="(opt, optIdx) in question.options" :key="optIdx" class="text-sm flex items-center gap-2">
                                                <span class="font-mono">{{ String.fromCharCode(65 + optIdx) }}.</span>
                                                <span :class="opt === question.correct_answer ? 'text-green-600 font-medium' : 'text-slate-600'">
                                                    {{ opt }}
                                                    <span v-if="opt === question.correct_answer" class="ml-2 text-green-600">✓ Correct</span>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Show for true/false -->
                                        <div v-if="question.type === 'true_false'" class="ml-6">
                                            <span class="text-sm">Correct answer: <span class="text-green-600 font-medium">{{ question.correct_answer }}</span></span>
                                        </div>
                                        
                                        <!-- Explanation -->
                                        <div v-if="question.explanation" class="mt-2 text-sm text-slate-500 bg-slate-50 p-2 rounded">
                                            📝 {{ question.explanation }}
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="editQuestion(question)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                            <Save class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteQuestion(question.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Question Modal -->
        <div v-if="showQuestionModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white">{{ editingQuestion ? 'Edit Question' : 'Add Question' }}</h3>
                    <button @click="showQuestionModal = false" class="text-white hover:bg-white/20 p-1 rounded-lg">
                        <X class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Question Text</label>
                        <textarea v-model="questionForm.question_text" rows="3" class="w-full px-4 py-2 border rounded-lg" required></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Question Type</label>
                            <select v-model="questionForm.type" class="w-full px-4 py-2 border rounded-lg">
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="true_false">True / False</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Points</label>
                            <input v-model="questionForm.points" type="number" class="w-full px-4 py-2 border rounded-lg" min="1" />
                        </div>
                    </div>

                    <!-- Multiple Choice Options -->
                    <div v-if="questionForm.type === 'multiple_choice'">
                        <label class="block text-sm font-medium mb-2">Options</label>
                        <div class="space-y-2">
                            <div v-for="(opt, idx) in questionForm.options" :key="idx" class="flex gap-2">
                                <span class="w-8 py-2 font-mono">{{ String.fromCharCode(65 + idx) }}.</span>
                                <input v-model="questionForm.options[idx]" type="text" class="flex-1 px-4 py-2 border rounded-lg" :placeholder="`Option ${String.fromCharCode(65 + idx)}`" />
                                <button type="button" @click="questionForm.correct_answer = opt" :class="['px-3 py-2 rounded-lg text-sm', questionForm.correct_answer === opt ? 'bg-green-600 text-white' : 'bg-slate-100']">
                                    Correct
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- True/False Options -->
                    <div v-if="questionForm.type === 'true_false'">
                        <label class="block text-sm font-medium mb-2">Correct Answer</label>
                        <div class="flex gap-4">
                            <button type="button" @click="questionForm.correct_answer = 'True'" :class="['flex-1 py-3 rounded-lg font-medium', questionForm.correct_answer === 'True' ? 'bg-green-600 text-white' : 'bg-slate-100']">
                                True
                            </button>
                            <button type="button" @click="questionForm.correct_answer = 'False'" :class="['flex-1 py-3 rounded-lg font-medium', questionForm.correct_answer === 'False' ? 'bg-green-600 text-white' : 'bg-slate-100']">
                                False
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Explanation (Optional)</label>
                        <textarea v-model="questionForm.explanation" rows="2" class="w-full px-4 py-2 border rounded-lg" placeholder="Explain why this answer is correct..."></textarea>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button @click="saveQuestion" class="flex-1 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Save Question
                        </button>
                        <button @click="showQuestionModal = false" class="px-6 py-3 border rounded-lg hover:bg-slate-50">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>