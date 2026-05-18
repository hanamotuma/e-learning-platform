<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import InstructorLayout from '@/layouts/InstructorLayout.vue'
import { Users, Mail, Calendar, User, Play, CheckCircle } from 'lucide-vue-next'

const props = defineProps({
    students: Object
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>

<template>
    <Head title="My Students | Instructor" />
    
    <InstructorLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold dark:text-white">My Students</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Track your students' progress</p>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium">Student</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Course</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Progress</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Enrolled</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="student in students?.data" :key="student.id">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                            <User class="w-4 h-4 text-blue-600" />
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ student.user?.name }}</p>
                                            <p class="text-xs text-slate-500">{{ student.user?.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ student.course?.title }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-slate-200 rounded-full">
                                            <div class="h-2 bg-orange-600 rounded-full" :style="{ width: (student.progress_percentage || 0) + '%' }"></div>
                                        </div>
                                        <span class="text-sm">{{ student.progress_percentage || 0 }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ formatDate(student.enrolled_at) }}</td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'px-2 py-1 text-xs rounded-full',
                                        student.progress_percentage >= 100 ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'
                                    ]">
                                        {{ student.progress_percentage >= 100 ? 'Completed' : 'In Progress' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>