<script setup>
import { ref, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { CheckCircle, BookOpen, Award, Calendar, ChevronRight } from 'lucide-vue-next'
import axios from 'axios'

const paymentInfo = ref(null)
const isSaving = ref(true)

onMounted(async () => {
  const savedPayment = localStorage.getItem('last_payment')
  if (savedPayment) {
    paymentInfo.value = JSON.parse(savedPayment)
    
    // Save enrollment to database for each course
    if (paymentInfo.value && paymentInfo.value.items) {
      for (const course of paymentInfo.value.items) {
        try {
          console.log('Saving enrollment for course:', course.id, course.title)
          const response = await axios.post('/api/enroll', {
            course_id: course.id,
            amount: course.price,
            payment_method: paymentInfo.value.paymentMethod,
            transaction_id: paymentInfo.value.transactionId
          })
          console.log('Enrollment response:', response.data)
        } catch (error) {
          console.error('Enrollment error:', error.response?.data || error.message)
        }
      }
    }
  }
  
  isSaving.value = false
  
  // Clear storage
  localStorage.removeItem('last_payment')
  localStorage.removeItem('savedCart')
  sessionStorage.removeItem('checkout_cart')
  
  // Set flags for dashboard refresh
  sessionStorage.setItem('refresh_dashboard', 'true')
  sessionStorage.setItem('enrollment_completed', 'true')
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US').format(price) + ' ETB'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const goToDashboard = () => {
  sessionStorage.setItem('refresh_dashboard', 'true')
  router.get('/student/dashboard')
}

const goToMyCourses = () => {
  sessionStorage.setItem('refresh_dashboard', 'true')
  router.get('/my-courses')
}

const getPaymentMethodName = (method) => {
  switch(method) {
    case 'telebirr': return 'TeleBirr'
    case 'cbe': return 'CBE Birr'
    case 'chapa': return 'Chapa'
    case 'card': return 'Card'
    default: return method
  }
}
</script>

<template>
  <Head title="Payment Successful | LearnHub" />
  
  <div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 dark:from-slate-900 dark:to-slate-950 py-12">
    <div class="max-w-4xl mx-auto px-4">
      <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-8 text-center">
          <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-full mb-4 shadow-lg">
            <CheckCircle class="w-14 h-14 text-green-500" />
          </div>
          <h1 class="text-3xl font-bold text-white">Payment Successful! 🎉</h1>
          <p class="text-green-100 mt-2">Your transaction has been completed successfully</p>
        </div>
        
        <div class="p-8">
          <!-- Loading state -->
          <div v-if="isSaving" class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600 mx-auto mb-4"></div>
            <p class="text-slate-500">Enrolling you in courses...</p>
          </div>
          
          <!-- Success content -->
          <div v-else>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-2xl p-6 mb-8">
              <div class="flex flex-col md:flex-row justify-between gap-4">
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Transaction ID</p>
                  <p class="font-mono font-bold dark:text-white">{{ paymentInfo?.transactionId || 'TXN-' + Math.random().toString(36).substr(2, 10).toUpperCase() }}</p>
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Date</p>
                  <p class="font-bold dark:text-white">{{ formatDate(paymentInfo?.date || new Date()) }}</p>
                </div>
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Payment Method</p>
                  <p class="font-bold dark:text-white">{{ getPaymentMethodName(paymentInfo?.paymentMethod) }}</p>
                </div>
              </div>
            </div>
            
            <h2 class="text-xl font-bold mb-4 dark:text-white">Order Summary</h2>
            <div class="space-y-3 mb-6">
              <div v-for="item in paymentInfo?.items" :key="item.id" class="flex justify-between py-2 border-b">
                <div>
                  <p class="font-medium dark:text-white">{{ item.title }}</p>
                  <p class="text-sm text-slate-500">{{ item.instructor }}</p>
                </div>
                <p class="font-bold text-blue-600">{{ formatPrice(item.price) }}</p>
              </div>
            </div>
            
            <div class="bg-slate-100 dark:bg-slate-800 rounded-2xl p-6 mb-8">
              <div class="flex justify-between mb-3">
                <span class="text-slate-600 dark:text-slate-400">Subtotal</span>
                <span class="dark:text-white">{{ formatPrice(paymentInfo?.amount || 0) }}</span>
              </div>
              <div class="flex justify-between mb-3">
                <span class="text-slate-600 dark:text-slate-400">Discount</span>
                <span class="text-green-600">- {{ formatPrice(0) }}</span>
              </div>
              <div class="flex justify-between pt-3 border-t border-slate-200 dark:border-slate-700">
                <span class="text-xl font-bold dark:text-white">Total Paid</span>
                <span class="text-2xl font-bold text-blue-600">{{ formatPrice(paymentInfo?.amount || 0) }}</span>
              </div>
            </div>
            
            <h2 class="text-xl font-bold mb-4 dark:text-white">What's Next?</h2>
            <div class="grid md:grid-cols-3 gap-4 mb-8">
              <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-4 text-center">
                <BookOpen class="w-10 h-10 text-blue-600 mx-auto mb-2" />
                <p class="font-bold dark:text-white">Access Courses</p>
                <p class="text-xs text-slate-500">Start learning immediately</p>
              </div>
              <div class="bg-purple-50 dark:bg-purple-900/20 rounded-2xl p-4 text-center">
                <Award class="w-10 h-10 text-purple-600 mx-auto mb-2" />
                <p class="font-bold dark:text-white">Get Certified</p>
                <p class="text-xs text-slate-500">Earn certificates on completion</p>
              </div>
              <div class="bg-green-50 dark:bg-green-900/20 rounded-2xl p-4 text-center">
                <Calendar class="w-10 h-10 text-green-600 mx-auto mb-2" />
                <p class="font-bold dark:text-white">Lifetime Access</p>
                <p class="text-xs text-slate-500">Learn at your own pace</p>
              </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4">
              <button @click="goToDashboard" class="flex-1 py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
                Go to Dashboard
                <ChevronRight class="w-4 h-4" />
              </button>
              <button @click="goToMyCourses" class="flex-1 py-4 border-2 border-blue-600 text-blue-600 font-bold rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all flex items-center justify-center gap-2">
                <BookOpen class="w-4 h-4" />
                My Courses
              </button>
            </div>
            
            <p class="text-center text-xs text-slate-400 mt-6">
              A confirmation email has been sent to your registered email address
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>