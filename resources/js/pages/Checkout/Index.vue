<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ChevronLeft, CreditCard, Shield, Smartphone, Lock, CheckCircle, AlertCircle, Wallet, Building2, Globe, Zap } from 'lucide-vue-next'

const page = usePage()
const authUser = computed(() => page.props.auth?.user)

const cartItems = ref([])
const cartTotal = ref(0)
const selectedPaymentMode = ref('all')
const selectedCourseIds = ref([])
const isProcessing = ref(false)
const showSuccess = ref(false)
const errorMessage = ref('')
const showOTPModal = ref(false)
const otpCode = ref('')
const phoneNumber = ref('')
const transactionId = ref('')

onMounted(() => {
  const singleCourse = sessionStorage.getItem('checkout_course')
  
  if (singleCourse) {
    const course = JSON.parse(singleCourse)
    cartItems.value = [course]
    cartTotal.value = course.price
    selectedCourseIds.value = [course.id]
  } else {
    const savedCart = sessionStorage.getItem('checkout_cart')
    if (savedCart) {
      cartItems.value = JSON.parse(savedCart)
      cartTotal.value = cartItems.value.reduce((sum, item) => sum + item.price, 0)
      selectedCourseIds.value = cartItems.value.map(item => item.id)
    }
  }
  
  if (authUser.value?.phone) {
    phoneNumber.value = authUser.value.phone
  }
})

const selectedTotal = computed(() => {
  if (selectedPaymentMode.value === 'all') {
    return cartTotal.value
  }
  return cartItems.value
    .filter(item => selectedCourseIds.value.includes(item.id))
    .reduce((sum, item) => sum + item.price, 0)
})

const hasSelectedCourses = computed(() => {
  if (selectedPaymentMode.value === 'all') return true
  return selectedCourseIds.value.length > 0
})

const toggleCourseSelection = (courseId) => {
  const index = selectedCourseIds.value.indexOf(courseId)
  if (index > -1) {
    selectedCourseIds.value.splice(index, 1)
  } else {
    selectedCourseIds.value.push(courseId)
  }
}

const selectAllCourses = () => {
  selectedCourseIds.value = cartItems.value.map(item => item.id)
  selectedPaymentMode.value = 'selected'
}

const deselectAllCourses = () => {
  selectedCourseIds.value = []
  selectedPaymentMode.value = 'selected'
}

const formatPrice = (price) => {
  if (!price && price !== 0) return '0 ETB'
  return new Intl.NumberFormat('en-US').format(price) + ' ETB'
}

const goBack = () => {
  router.get('/')
}

const generateTransactionId = () => {
  return 'CHAPA-' + Date.now() + '-' + Math.random().toString(36).substr(2, 8).toUpperCase()
}

const processPayment = () => {
  if (!phoneNumber.value) {
    errorMessage.value = 'Please enter your phone number'
    setTimeout(() => errorMessage.value = '', 3000)
    return
  }
  
  if (!hasSelectedCourses.value) {
    errorMessage.value = 'Please select at least one course to pay for'
    setTimeout(() => errorMessage.value = '', 3000)
    return
  }
  
  showOTPModal.value = true
}

const verifyOTP = () => {
  if (!otpCode.value) {
    errorMessage.value = 'Please enter OTP code'
    setTimeout(() => errorMessage.value = '', 3000)
    return
  }
  
  showOTPModal.value = false
  isProcessing.value = true
  
  const selectedItems = cartItems.value.filter(item => selectedCourseIds.value.includes(item.id))
  
  setTimeout(() => {
    const newTransactionId = generateTransactionId()
    transactionId.value = newTransactionId
    
    const paymentInfo = {
      amount: selectedTotal.value,
      items: selectedItems,
      paymentMethod: 'chapa',
      transactionId: newTransactionId,
      date: new Date().toISOString(),
      status: 'success',
      phoneNumber: phoneNumber.value
    }
    
    localStorage.setItem('last_payment', JSON.stringify(paymentInfo))
    
    const remainingItems = cartItems.value.filter(item => !selectedCourseIds.value.includes(item.id))
    if (remainingItems.length > 0) {
      sessionStorage.setItem('checkout_cart', JSON.stringify(remainingItems))
    } else {
      sessionStorage.removeItem('checkout_cart')
      sessionStorage.removeItem('checkout_course')
      localStorage.removeItem('savedCart')
    }
    
    isProcessing.value = false
    showSuccess.value = true
    
    setTimeout(() => {
      router.get('/payment/success')
    }, 1500)
  }, 2000)
}
</script>

<template>
  <Head title="Chapa Checkout | LearnHub" />
  
  <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
    <!-- Processing Modal -->
    <div v-if="isProcessing && !showSuccess" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 text-center max-w-md mx-4">
        <div class="w-20 h-20 mx-auto mb-4">
          <div class="animate-spin rounded-full h-20 w-20 border-b-4 border-green-600"></div>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <span class="text-2xl font-bold text-green-600">C</span>
        </div>
        <h3 class="text-xl font-bold mb-2 dark:text-white">Processing Payment</h3>
        <p class="text-slate-500 dark:text-slate-400">Please wait...</p>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccess" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 text-center max-w-md mx-4">
        <div class="w-20 h-20 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
          <CheckCircle class="w-12 h-12 text-green-500" />
        </div>
        <h3 class="text-xl font-bold mb-2 dark:text-white">Payment Successful!</h3>
        <p class="text-slate-500 dark:text-slate-400">Redirecting...</p>
      </div>
    </div>

    <!-- OTP Verification Modal -->
    <div v-if="showOTPModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 max-w-md mx-4 w-full">
        <div class="text-center mb-4">
          <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <Smartphone class="w-8 h-8 text-green-600" />
          </div>
          <h3 class="text-xl font-bold dark:text-white">Verify Your Payment</h3>
          <p class="text-sm text-slate-500 mt-1">Code sent to {{ phoneNumber }}</p>
        </div>
        
        <div class="mb-4">
          <label class="block text-sm font-medium mb-2">Enter OTP Code</label>
          <input 
            type="text" 
            v-model="otpCode" 
            placeholder="123456" 
            maxlength="6"
            class="w-full px-4 py-3 border rounded-xl text-center text-2xl tracking-widest focus:ring-2 focus:ring-green-500"
          />
        </div>
        
        <div class="flex gap-3">
          <button @click="showOTPModal = false" class="flex-1 py-3 border rounded-xl hover:bg-gray-50 transition-colors">
            Cancel
          </button>
          <button @click="verifyOTP" class="flex-1 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors">
            Verify & Pay
          </button>
        </div>
        
        <p class="text-center text-xs text-slate-400 mt-4">
          Didn't receive code? <button class="text-green-600">Resend</button>
        </p>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <button @click="goBack" class="flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-green-600">
          <ChevronLeft class="w-5 h-5" />
          EN
        </button>
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold">C</span>
          </div>
          <span class="text-xl font-bold dark:text-white">hapa</span>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Left Column - Payment Form (Only Wallets) -->
        <div class="lg:col-span-2">
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border p-6">
            <h2 class="text-xl font-bold mb-4 text-green-600">Chapa Checkout</h2>
            <p class="text-sm text-slate-500 mb-4">Select your payment method here</p>
            
            <!-- Only Wallets Payment Method -->
            <div class="mb-6">
              <div class="flex items-center gap-3 p-4 border rounded-xl bg-green-50 border-green-200">
                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                  <Wallet class="w-5 h-5 text-white" />
                </div>
                <div>
                  <p class="font-bold">Wallets</p>
                  <p class="text-xs text-slate-500">Chapa Wallet, Telebirr, CBE Birr</p>
                </div>
              </div>
            </div>
            
            <!-- Payment Details -->
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Phone Number</label>
                <input 
                  type="tel" 
                  v-model="phoneNumber" 
                  placeholder="09XXXXXXXX" 
                  class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-green-500 dark:bg-slate-900"
                />
              </div>
            </div>

            <!-- Pay Button -->
            <button 
              @click="processPayment" 
              :disabled="isProcessing || !hasSelectedCourses"
              class="w-full mt-6 py-4 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
            >
              <Lock class="w-4 h-4" />
              Pay {{ formatPrice(selectedTotal) }}
            </button>

            <!-- Security Badge -->
            <div class="mt-4 text-center">
              <p class="text-xs text-slate-400 flex items-center justify-center gap-1">
                <Shield class="w-3 h-3" />
                Secured By Chapa
              </p>
            </div>
          </div>
        </div>

        <!-- Right Column - Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-black dark:bg-slate-900 text-white rounded-xl shadow-sm border border-green-500/30 p-6 sticky top-24">
            <h3 class="font-bold text-lg mb-4 pb-3 border-b border-green-500/30">ORDER SUMMARY</h3>
            
            <!-- Payment Mode Selection -->
            <div class="mb-4">
              <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" value="all" v-model="selectedPaymentMode" class="w-4 h-4 text-green-500" />
                  <span class="text-sm">Pay All</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" value="selected" v-model="selectedPaymentMode" class="w-4 h-4 text-green-500" />
                  <span class="text-sm">Select Courses</span>
                </label>
              </div>
            </div>
            
            <div v-if="cartItems.length === 0" class="text-center py-8">
              <p class="text-slate-400">No items in cart</p>
            </div>
            
            <div v-else class="space-y-3 max-h-80 overflow-y-auto">
              <div v-for="item in cartItems" :key="item.id" class="pb-3 border-b border-green-500/20 last:border-0">
                <div class="flex gap-3">
                  <div v-if="selectedPaymentMode === 'selected'" class="flex-shrink-0 pt-1">
                    <input 
                      type="checkbox" 
                      :checked="selectedCourseIds.includes(item.id)"
                      @change="toggleCourseSelection(item.id)"
                      class="w-4 h-4 text-green-500 rounded border-green-500/50 focus:ring-green-500"
                    />
                  </div>
                  <img :src="item.image" class="w-12 h-12 rounded-lg object-cover" />
                  <div class="flex-1">
                    <p class="font-medium text-xs line-clamp-2">{{ item.title }}</p>
                    <p class="text-green-400 font-bold text-sm mt-1">{{ formatPrice(item.price) }}</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="pt-4 mt-2 border-t border-green-500/30">
              <div class="flex justify-between mb-2">
                <span>Subtotal</span>
                <span>{{ formatPrice(selectedTotal) }}</span>
              </div>
              <div class="flex justify-between mb-2">
                <span>Fee</span>
                <span class="text-green-400">Free</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-green-500/30 mt-2">
                <span class="font-bold">Total</span>
                <span class="text-xl font-bold text-green-400">{{ formatPrice(selectedTotal) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes spin {
  to { transform: rotate(360deg); }
}
.animate-spin {
  animation: spin 1s linear infinite;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>