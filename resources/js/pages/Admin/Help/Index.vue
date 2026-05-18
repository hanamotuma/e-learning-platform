<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { HelpCircle, BookOpen, Video, Mail, MessageCircle, ChevronDown, ChevronUp, ExternalLink, Headphones, FileText, LifeBuoy } from 'lucide-vue-next'

const faqs = ref([
  { question: 'How do I approve a course?', answer: 'Go to Courses → Find the pending course → Click the Approve button. The course will become visible to students immediately.', open: false },
  { question: 'How do I change platform commission?', answer: 'Go to Settings → Commission → Adjust the platform commission percentage. Changes apply to future sales only.', open: false },
  { question: 'How do I issue a refund?', answer: 'Go to Payments → Find the transaction → Click Refund. The refund will be processed and the student will be notified.', open: false },
  { question: 'How do I manage instructor withdrawals?', answer: 'Go to Withdrawals → Review pending requests → Approve or Reject. Approved requests can then be marked as paid.', open: false },
  { question: 'How do I create a coupon?', answer: 'Go to Coupons → Create Coupon → Set discount type, value, and expiry date. Share the code with users.', open: false },
  { question: 'How do I send announcements?', answer: 'Go to Announcements → New Announcement → Write your message → Choose audience → Publish.', open: false }
])

const openFaq = ref(null)

const toggleFaq = (index) => {
  if (openFaq.value === index) {
    openFaq.value = null
  } else {
    openFaq.value = index
  }
}
</script>

<template>
  <Head title="Help & Support | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-3xl font-bold dark:text-white">Help & Support</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Get help with platform management</p>
      </div>
      
      <!-- Quick Links Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 text-center hover:shadow-md transition-all">
          <BookOpen class="w-10 h-10 text-blue-600 dark:text-blue-400 mx-auto mb-3" />
          <h3 class="font-bold dark:text-white mb-1">Documentation</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400">Read full documentation</p>
          <a href="#" class="text-xs text-blue-600 dark:text-blue-400 mt-2 inline-block hover:underline">View Docs →</a>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 text-center hover:shadow-md transition-all">
          <Video class="w-10 h-10 text-purple-600 dark:text-purple-400 mx-auto mb-3" />
          <h3 class="font-bold dark:text-white mb-1">Video Tutorials</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400">Watch video guides</p>
          <a href="#" class="text-xs text-blue-600 dark:text-blue-400 mt-2 inline-block hover:underline">Watch →</a>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 text-center hover:shadow-md transition-all">
          <MessageCircle class="w-10 h-10 text-green-600 dark:text-green-400 mx-auto mb-3" />
          <h3 class="font-bold dark:text-white mb-1">Support Tickets</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400">Submit a support request</p>
          <Link href="/admin/support" class="text-xs text-blue-600 dark:text-blue-400 mt-2 inline-block hover:underline">Open Ticket →</Link>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 text-center hover:shadow-md transition-all">
          <Mail class="w-10 h-10 text-orange-600 dark:text-orange-400 mx-auto mb-3" />
          <h3 class="font-bold dark:text-white mb-1">Contact Support</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400">Email our support team</p>
          <a href="mailto:support@learnhub.com" class="text-xs text-blue-600 dark:text-blue-400 mt-2 inline-block hover:underline">support@learnhub.com</a>
        </div>
      </div>
      
      <!-- FAQ Section -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 mb-8">
        <div class="flex items-center gap-3 mb-6">
          <HelpCircle class="w-6 h-6 text-blue-600 dark:text-blue-400" />
          <h2 class="text-xl font-bold dark:text-white">Frequently Asked Questions</h2>
        </div>
        
        <div class="space-y-3">
          <div v-for="(faq, index) in faqs" :key="index" class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
            <button @click="toggleFaq(index)" class="w-full flex justify-between items-center p-4 text-left hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all">
              <span class="font-medium dark:text-white">{{ faq.question }}</span>
              <ChevronDown v-if="openFaq !== index" class="w-5 h-5 text-slate-400 dark:text-slate-500" />
              <ChevronUp v-else class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            </button>
            <div v-if="openFaq === index" class="p-4 pt-0 border-t border-slate-200 dark:border-slate-700">
              <p class="text-slate-600 dark:text-slate-300">{{ faq.answer }}</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Contact Banner -->
      <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 text-white text-center">
        <LifeBuoy class="w-16 h-16 mx-auto mb-4 opacity-80" />
        <h2 class="text-2xl font-bold mb-2">Still need help?</h2>
        <p class="text-blue-100 mb-6">Our support team is ready to assist you</p>
        <div class="flex flex-wrap gap-4 justify-center">
          <a href="#" class="px-6 py-3 bg-white/20 rounded-xl hover:bg-white/30 transition-all">📞 +251-XXX-XXXX</a>
          <a href="mailto:support@learnhub.com" class="px-6 py-3 bg-white/20 rounded-xl hover:bg-white/30 transition-all">✉️ support@learnhub.com</a>
          <Link href="/admin/support" class="px-6 py-3 bg-white/20 rounded-xl hover:bg-white/30 transition-all">💬 Live Chat</Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>