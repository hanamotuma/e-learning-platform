<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import InstructorLayout from '@/layouts/InstructorLayout.vue'
import { HelpCircle, BookOpen, Video, Mail, MessageCircle, ChevronDown, ChevronUp, LifeBuoy } from 'lucide-vue-next'

const faqs = ref([
    { question: 'How to create a course?', answer: 'Click "Create Course" button, fill in the details, add sections and lessons, then submit for review.', open: false },
    { question: 'How long does course approval take?', answer: 'Course approval typically takes 24-48 hours. You will be notified once reviewed.', open: false },
    { question: 'How do I get paid?', answer: 'Earnings are paid monthly via Chapa. Minimum payout is 500 ETB.', open: false },
    { question: 'How to edit my course?', answer: 'Go to My Courses, click Edit on the course you want to modify.', open: false },
    { question: 'What is the commission rate?', answer: 'Platform commission is 30%. You receive 70% of each sale.', open: false }
])

const toggleFaq = (index) => {
    faqs.value[index].open = !faqs.value[index].open
}
</script>

<template>
    <Head title="Help & Support | Instructor" />
    
    <InstructorLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold dark:text-white">Help & Support</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Get help with platform management</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-slate-800 rounded-xl border p-6 text-center hover:shadow-md transition-all">
                    <BookOpen class="w-12 h-12 text-blue-600 mx-auto mb-3" />
                    <h3 class="font-bold mb-1">Documentation</h3>
                    <p class="text-sm text-slate-500">Read full documentation</p>
                    <a href="#" class="text-xs text-blue-600 mt-2 inline-block">View Docs →</a>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl border p-6 text-center hover:shadow-md transition-all">
                    <Video class="w-12 h-12 text-purple-600 mx-auto mb-3" />
                    <h3 class="font-bold mb-1">Video Tutorials</h3>
                    <p class="text-sm text-slate-500">Watch video guides</p>
                    <a href="#" class="text-xs text-blue-600 mt-2 inline-block">Watch →</a>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl border p-6 text-center hover:shadow-md transition-all">
                    <Mail class="w-12 h-12 text-green-600 mx-auto mb-3" />
                    <h3 class="font-bold mb-1">Contact Support</h3>
                    <p class="text-sm text-slate-500">support@learnhub.com</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <HelpCircle class="w-5 h-5 text-blue-600" />
                    Frequently Asked Questions
                </h2>
                <div class="space-y-3">
                    <div v-for="(faq, index) in faqs" :key="index" class="border rounded-lg overflow-hidden">
                        <button @click="toggleFaq(index)" class="w-full flex justify-between items-center p-4 text-left hover:bg-slate-50 dark:hover:bg-slate-700/50">
                            <span class="font-medium">{{ faq.question }}</span>
                            <ChevronDown v-if="!faq.open" class="w-5 h-5" />
                            <ChevronUp v-else class="w-5 h-5 text-blue-600" />
                        </button>
                        <div v-if="faq.open" class="p-4 pt-0 border-t">
                            <p class="text-slate-600 dark:text-slate-300">{{ faq.answer }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 text-white text-center">
                <LifeBuoy class="w-16 h-16 mx-auto mb-4 opacity-80" />
                <h2 class="text-2xl font-bold mb-2">Still need help?</h2>
                <p class="text-blue-100 mb-6">Our support team is ready to assist you</p>
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="mailto:support@learnhub.com" class="px-6 py-3 bg-white/20 rounded-xl hover:bg-white/30 transition-all">✉️ support@learnhub.com</a>
                    <Link href="/instructor/support" class="px-6 py-3 bg-white/20 rounded-xl hover:bg-white/30 transition-all">💬 Open Ticket</Link>
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>