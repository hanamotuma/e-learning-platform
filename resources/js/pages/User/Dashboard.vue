<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Mock data for the dashboard - replace with actual props from your controller later
const stats = [
    { name: 'Enrolled Courses', value: '12', icon: '📚', color: 'text-blue-400' },
    { name: 'Completed Lessons', value: '45', icon: '✅', color: 'text-emerald-400' },
    { name: 'Certificates', value: '3', icon: '🏆', color: 'text-amber-400' },
    { name: 'Quiz Avg.', value: '88%', icon: '📊', color: 'text-violet-400' },
];

const currentCourses = [
    { id: 1, title: 'Full-Stack Web Development 2026', progress: 65, instructor: 'Dr. Sarah Jenkins' },
    { id: 2, title: 'Advanced UI/UX Design Systems', progress: 30, instructor: 'Marcus Rhoades' },
];
</script>

<template>
    <Head title="Student Dashboard" />

    <div class="min-h-screen bg-[#09091a] text-white flex">
        <aside class="w-64 border-r border-white/10 bg-[#15193f]/20 backdrop-blur-xl hidden md:block">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-violet-500">
                    LearnHub
                </h2>
            </div>
            <nav class="mt-6 px-4 space-y-2">
                <Link href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-violet-600/20 text-violet-300 border border-violet-500/30">
                    <span>🏠</span> Dashboard
                </Link>
                <Link href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-white/5 transition-all">
                    <span>📖</span> My Courses
                </Link>
                <Link href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-white/5 transition-all">
                    <span>📝</span> Quiz Results
                </Link>
                <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-rose-400 hover:bg-rose-500/10 transition-all">
                    <span>🚪</span> Logout
                </Link>
            </nav>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">
            <header class="flex justify-between items-center mb-10">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Welcome back, {{ user.name }}! 👋</h1>
                    <p class="text-slate-400 mt-1">Ready to continue your learning journey?</p>
                </div>
                <div class="flex items-center gap-4">
                    <button class="p-2 bg-white/5 rounded-full border border-white/10 hover:bg-white/10">🔔</button>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-violet-600 flex items-center justify-center font-bold">
                        {{ user.name.charAt(0) }}
                    </div>
                </div>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div v-for="stat in stats" :key="stat.name" class="p-6 rounded-[24px] border border-white/10 bg-[#15193f]/40 backdrop-blur-md">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-2xl">{{ stat.icon }}</span>
                        <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Live Data</span>
                    </div>
                    <div class="text-2xl font-bold mb-1">{{ stat.value }}</div>
                    <div class="text-sm text-slate-400">{{ stat.name }}</div>
                </div>
            </div>

            <section>
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <span>🔥</span> Continue Learning
                </h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div v-for="course in currentCourses" :key="course.id" class="p-6 rounded-[24px] border border-white/10 bg-[#15193f]/40 hover:border-violet-500/50 transition-all group">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h4 class="font-bold text-lg group-hover:text-violet-400 transition-colors">{{ course.title }}</h4>
                                <p class="text-xs text-slate-500">{{ course.instructor }}</p>
                            </div>
                            <span class="text-xs font-mono text-violet-400">{{ course.progress }}%</span>
                        </div>
                        
                        <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden mb-6">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-violet-600 transition-all duration-1000" :style="{ width: course.progress + '%' }"></div>
                        </div>

                        <Link href="#" class="inline-flex items-center justify-center w-full py-3 rounded-xl bg-white text-slate-900 text-sm font-bold hover:bg-violet-400 hover:text-white transition-all">
                            Resume Lesson
                        </Link>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<style scoped>
/* Custom scrollbar for a clean look */
main::-webkit-scrollbar {
    width: 6px;
}
main::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
</style>