<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle, PlayCircle, LayoutDashboard, ArrowRight } from 'lucide-vue-next';
import { onMounted } from 'vue';
import confetti from 'canvas-confetti'; // Optional: npm install canvas-confetti

const props = defineProps({
    message: String
});

// Trigger confetti on mount for that "Premium" feel
onMounted(() => {
    confetti({
        particleCount: 150,
        spread: 70,
        origin: { y: 0.6 },
        colors: ['#2563eb', '#22d3ee', '#9333ea']
    });
});
</script>

<template>
    <Head title="Payment Successful" />

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full text-center">
            <div class="mb-8 flex justify-center">
                <div class="relative">
                    <div class="absolute inset-0 animate-ping bg-green-500/20 rounded-full"></div>
                    <div class="relative bg-white dark:bg-slate-900 p-4 rounded-full shadow-2xl border border-green-100 dark:border-green-900/30">
                        <CheckCircle class="w-16 h-16 text-green-500" />
                    </div>
                </div>
            </div>

            <h1 class="text-4xl font-black text-slate-900 dark:text-white mb-4 tracking-tight">
                Payment Received!
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-lg mb-10 leading-relaxed">
                {{ message || 'Your enrollment is confirmed. Welcome to the course!' }}
            </p>

            <div class="space-y-4">
                <Link 
                    :href="route('student.dashboard')" 
                    class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold text-lg shadow-xl shadow-blue-600/20 transition-all flex items-center justify-center gap-3 group"
                >
                    <PlayCircle class="w-6 h-6" />
                    Start Learning Now
                    <ArrowRight class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                </Link>

                <Link 
                    :href="route('dashboard')" 
                    class="w-full py-4 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800 rounded-2xl font-bold transition-all hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center gap-3"
                >
                    <LayoutDashboard class="w-5 h-5" />
                    Go to My Dashboard
                </Link>
            </div>

            <p class="mt-12 text-sm text-slate-400">
                A confirmation email has been sent to your inbox. <br>
                Need help? <Link :href="route('tickets.index')" class="text-blue-500 hover:underline">Contact Support</Link>
            </p>
        </div>
    </div>
</template>

<style scoped>
/* Add a smooth entry animation */
.min-h-screen {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>