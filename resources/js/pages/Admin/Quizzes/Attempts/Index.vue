<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

const props = defineProps<{
    attempts: {
        data: Array<{
            id: number
            score: number
            total_points: number
            status: string
            created_at_human: string
            user: { full_name: string; email: string }
            quiz: { title: string; section: { course: { title: string } } }
        }>
    }
}>()

const getStatusClass = (status: string) => {
    switch (status.toLowerCase()) {
        case 'completed': return 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30'
        case 'pending': return 'bg-amber-500/15 text-amber-400 border-amber-500/30'
        case 'failed': return 'bg-rose-500/15 text-rose-400 border-rose-500/30'
        default: return 'bg-slate-500/15 text-slate-400 border-slate-500/30'
    }
}
</script>

<template>
    <div class="min-h-screen bg-[#09091a] text-white p-8">
        <div class="max-w-7xl mx-auto">
            <header class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Quiz Attempts</h1>
                    <p class="text-slate-400">Review and grade student quiz submissions.</p>
                </div>
            </header>

            <div class="rounded-[24px] border border-white/10 bg-[#15193f]/40 overflow-hidden backdrop-blur-md">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/5 text-slate-400 border-b border-white/5">
                        <tr>
                            <th class="px-6 py-4 font-semibold uppercase text-[11px]">Student</th>
                            <th class="px-6 py-4 font-semibold uppercase text-[11px]">Quiz / Course</th>
                            <th class="px-6 py-4 font-semibold uppercase text-[11px]">Score</th>
                            <th class="px-6 py-4 font-semibold uppercase text-[11px]">Status</th>
                            <th class="px-6 py-4 font-semibold uppercase text-[11px]">Date</th>
                            <th class="px-6 py-4 font-semibold uppercase text-[11px] text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="attempt in attempts.data" :key="attempt.id" class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-200">{{ attempt.user.full_name }}</div>
                                <div class="text-[10px] text-slate-500">{{ attempt.user.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-300 font-medium">{{ attempt.quiz.title }}</div>
                                <div class="text-[10px] text-violet-400 uppercase">{{ attempt.quiz.section.course.title }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-cyan-400">{{ attempt.score }}/{{ attempt.total_points }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-lg border text-[10px] font-bold uppercase" :class="getStatusClass(attempt.status)">
                                    {{ attempt.attempt_status || 'Submitted' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-400 text-xs">
                                {{ attempt.created_at_human || 'Recently' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('admin.attempts.show', attempt.id)" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600/20 border border-violet-500/30 text-violet-300 hover:bg-violet-600 hover:text-white transition-all text-xs font-bold">
                                    Review
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!attempts.data.length">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">No attempts found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>