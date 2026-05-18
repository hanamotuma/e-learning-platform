<script setup>
import { Head, Link } from '@inertiajs/vue3'
import InstructorLayout from '@/layouts/InstructorLayout.vue'
import { Plus, Edit, Trash2, Eye, Clock, Trophy } from 'lucide-vue-next'

const props = defineProps({
    course: Object,
    quizzes: Array
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>

<template>
    <Head :title="`Quizzes - ${course.title}`" />
    
    <InstructorLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold dark:text-white">Quizzes</h1>
                    <p class="text-slate-500 mt-1">{{ course.title }}</p>
                </div>
                <Link :href="`/instructor/courses/${course.id}/quizzes/create`" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <Plus class="w-4 h-4" />
                    Create Quiz
                </Link>
            </div>

            <div v-if="quizzes.length === 0" class="bg-white dark:bg-slate-800 rounded-xl border p-12 text-center">
                <Trophy class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                <p class="text-slate-500">No quizzes created yet</p>
                <Link :href="`/instructor/courses/${course.id}/quizzes/create`" class="inline-block mt-3 text-blue-600">Create First Quiz</Link>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="quiz in quizzes" :key="quiz.id" class="bg-white dark:bg-slate-800 rounded-xl border p-6 hover:shadow-lg transition-all">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="text-xl font-bold dark:text-white">{{ quiz.title }}</h3>
                            <p class="text-sm text-slate-500 mt-1">{{ quiz.questions_count || 0 }} questions</p>
                        </div>
                        <span :class="['px-2 py-1 text-xs rounded-full', quiz.is_published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700']">
                            {{ quiz.is_published ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">{{ quiz.description || 'No description' }}</p>
                    <div class="flex items-center gap-4 text-sm text-slate-500 mb-4">
                        <span class="flex items-center gap-1"><Clock class="w-4 h-4" /> {{ quiz.time_limit_minutes || 'No' }} min limit</span>
                        <span class="flex items-center gap-1"><Trophy class="w-4 h-4" /> Passing: {{ quiz.passing_score }}%</span>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="`/instructor/courses/${course.id}/quizzes/${quiz.id}/edit`" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-center hover:bg-blue-700">
                            Edit Quiz
                        </Link>
                        <Link :href="`/instructor/courses/${course.id}/quizzes/${quiz.id}/delete`" method="delete" class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 text-center">
                            Delete
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>