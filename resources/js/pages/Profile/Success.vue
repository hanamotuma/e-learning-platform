<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { CheckCircle, Award, BookOpen, ArrowRight } from 'lucide-vue-next'

const props = defineProps({
  reference: String,
  transactionId: String
})

const countdown = ref(5)

onMounted(() => {
  // Clear pending payment from session storage
  sessionStorage.removeItem('pendingPayment')
  
  // Auto redirect after 5 seconds
  const interval = setInterval(() => {
    countdown.value--
    if (countdown.value === 0) {
      clearInterval(interval)
      window.location.href = route('my-courses')
    }
  }, 1000)
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 dark:from-slate-900 dark:to-slate-800 flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
      <!-- Success Animation -->
      <div class="relative p-8 text-center">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
          <CheckCircle class="w-12 h-12 text-green-600" />
        </div>
        
        <h1 class="text-3xl font-black text-slate-800 dark:text-white mb-2">Payment Successful! 🎉</h1>
        <p class="text-slate-500 dark:text-slate-400 mb-6">
          You have been successfully enrolled in your course(s)
        </p>
        
        <!-- Transaction Details -->
        <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-4 mb-6 text-left">
          <h3 class="font-semibold text-slate-700 dark:text-slate-300 mb-2">Transaction Details</h3>
          <div class="space-y-1 text-sm">
            <p class="text-slate-500"><span class="font-medium">Reference:</span> {{ reference || 'N/A' }}</p>
            <p class="text-slate-500"><span class="font-medium">Transaction ID:</span> {{ transactionId || 'N/A' }}</p>
            <p class="text-slate-500"><span class="font-medium">Date:</span> {{ new Date().toLocaleString() }}</p>
          </div>
        </div>
        
        <!-- What's Next -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 mb-6">
          <h3 class="font-semibold text-blue-800 dark:text-blue-300 mb-2 flex items-center justify-center space-x-2">
            <Award class="w-5 h-5" />
            <span>What's Next?</span>
          </h3>
          <div class="space-y-2 text-sm text-blue-700 dark:text-blue-300">
            <div class="flex items-center space-x-2">
              <BookOpen class="w-4 h-4" />
              <span>Start learning immediately from your dashboard</span>
            </div>
            <div class="flex items-center space-x-2">
              <Award class="w-4 h-4" />
              <span>Earn certificates upon completion</span>
            </div>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="space-y-3">
          <Link 
            :href="route('my-courses')" 
            class="block w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all flex items-center justify-center space-x-2"
          >
            <span>Go to My Courses</span>
            <ArrowRight class="w-4 h-4" />
          </Link>
          <Link 
            :href="route('home')" 
            class="block w-full py-3 border-2 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all"
          >
            Continue Exploring
          </Link>
        </div>
        
        <p class="text-sm text-slate-400 mt-6">
          Redirecting to My Courses in {{ countdown }} seconds...
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}
.animate-bounce {
  animation: bounce 0.8s ease-in-out infinite;
}
</style>