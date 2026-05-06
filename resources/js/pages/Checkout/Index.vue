<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { 
  CreditCard, Lock, Shield, Truck, ChevronLeft, 
  Users, BookOpen, Star, CheckCircle, ArrowRight
} from 'lucide-vue-next'

const props = defineProps({
  cartItems: Array,
  cartTotal: Number,
  isAuthenticated: Boolean
})

const selectedPaymentMethod = ref('card')
const showAuthModal = ref(!props.isAuthenticated)
const selectedCourseForPayment = ref(null)

// Separate payment for individual courses
const payForCourse = (course) => {
  selectedCourseForPayment.value = course
  if (!props.isAuthenticated) {
    showAuthModal.value = true
  } else {
    // Proceed to payment for single course
    window.location.href = route('payment.process', { course_id: course.id, type: 'single' })
  }
}

// Pay for all courses
const payForAll = () => {
  selectedCourseForPayment.value = null
  if (!props.isAuthenticated) {
    showAuthModal.value = true
  } else {
    window.location.href = route('payment.process', { type: 'all' })
  }
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US').format(price) + ' ETB'
}

// When proceeding to payment
const proceedToPayment = (type, course = null) => {
  // Save payment session data
  const paymentData = {
    payment_type: type,
    amount: type === 'single' ? course.price : cartTotal.value,
    courses: type === 'all' ? cartItems.value : [course],
    course_id: course?.id
  }
  
  // Store in session storage
  sessionStorage.setItem('pendingPayment', JSON.stringify(paymentData))
  
  // Redirect to payment page
  window.location.href = route('payment.page', {
    type: type,
    course_id: course?.id
  })
}

</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <Link :href="route('home')" class="text-blue-600 hover:text-blue-700 flex items-center space-x-2 mb-4">
          <ChevronLeft class="w-4 h-4" />
          <span>Back to Shopping</span>
        </Link>
        <h1 class="text-3xl font-black text-slate-800 dark:text-white">Checkout</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Complete your purchase</p>
      </div>

      <div class="grid lg:grid-cols-3 gap-8">
        <!-- Payment Options Column -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Pay for All Courses Card -->
          <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-xl font-bold dark:text-white">Pay for All Courses</h2>
                  <p class="text-slate-500 text-sm mt-1">Complete payment for all items in your cart</p>
                </div>
                <div class="text-right">
                  <div class="text-2xl font-black text-blue-600">{{ formatPrice(cartTotal) }}</div>
                  <div class="text-sm text-slate-400">{{ cartItems.length }} courses</div>
                </div>
              </div>
            </div>
            <div class="p-6">
              <button 
                @click="payForAll"
                class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all flex items-center justify-center space-x-2"
              >
                <CreditCard class="w-5 h-5" />
                <span>Pay {{ formatPrice(cartTotal) }} for All Courses</span>
              </button>
            </div>
          </div>

          <!-- Pay Separately for Each Course -->
          <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700">
              <h2 class="text-xl font-bold dark:text-white">Or Pay Per Course</h2>
              <p class="text-slate-500 text-sm mt-1">Choose individual courses to purchase separately</p>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
              <div v-for="course in cartItems" :key="course.id" class="p-6 flex items-center justify-between">
                <div class="flex-1">
                  <div class="flex items-start space-x-4">
                    <img :src="course.image" class="w-16 h-16 object-cover rounded-lg" />
                    <div>
                      <h3 class="font-semibold dark:text-white">{{ course.title }}</h3>
                      <p class="text-sm text-slate-500">{{ course.instructor }}</p>
                      <div class="flex items-center space-x-2 mt-1">
                        <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 px-2 py-0.5 rounded">{{ course.badge }}</span>
                        <div class="flex items-center space-x-1">
                          <Star class="w-3 h-3 text-yellow-400 fill-yellow-400" />
                          <span class="text-xs">{{ course.rating }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-xl font-bold text-blue-600">{{ formatPrice(course.price) }}</div>
                  <button 
                    @click="payForCourse(course)"
                    class="mt-2 px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-all"
                  >
                    Pay for This Course
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary Column -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-700 sticky top-24">
            <h3 class="text-xl font-bold dark:text-white mb-4">Order Summary</h3>
            <div class="space-y-3 pb-4 border-b border-slate-200 dark:border-slate-700">
              <div class="flex justify-between">
                <span class="text-slate-500">Subtotal ({{ cartItems.length }} items)</span>
                <span class="dark:text-white">{{ formatPrice(cartTotal) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Discount</span>
                <span class="text-green-600">- {{ formatPrice(0) }}</span>
              </div>
            </div>
            <div class="flex justify-between pt-4 mb-6">
              <span class="font-bold dark:text-white">Total</span>
              <span class="text-2xl font-black text-blue-600">{{ formatPrice(cartTotal) }}</span>
            </div>
            
            <div class="flex items-center justify-center space-x-4 mt-4 text-xs text-slate-400">
              <span class="flex items-center space-x-1"><Shield class="w-3 h-3" /> Secure Payment</span>
              <span class="flex items-center space-x-1"><Truck class="w-3 h-3" /> Lifetime Access</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Auth Modal -->
      <div v-if="showAuthModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
          <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <Lock class="w-8 h-8 text-blue-600" />
            </div>
            <h2 class="text-2xl font-bold dark:text-white">Login Required</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2">Please login or create an account to complete your purchase</p>
          </div>
          
          <div class="space-y-3">
            <Link 
              :href="route('login')" 
              class="block w-full text-center py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all"
            >
              Log In
            </Link>
            <Link 
              :href="route('register')" 
              class="block w-full text-center py-3 border-2 border-blue-600 text-blue-600 font-bold rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all"
            >
              Create Free Account
            </Link>
            <button 
              @click="showAuthModal = false"
              class="block w-full text-center py-2 text-slate-500 hover:text-slate-700 transition-all"
            >
              Continue Shopping
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>