<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { ChevronLeft, CreditCard, Shield, Truck, Smartphone, Sparkles, Copy } from 'lucide-vue-next'

const cartItems = ref([])
const cartTotal = ref(0)
const isProcessing = ref(false)
const copySuccess = ref(false)
const paymentStatus = ref(null)
const showSuccess = ref(false)

// Chapa payment form
const chapaForm = ref({
  email: '',
  phoneNumber: '',
  fullName: ''
})

onMounted(() => {
  // Get course from session storage
  const singleCourse = sessionStorage.getItem('checkout_course')
  if (singleCourse) {
    const course = JSON.parse(singleCourse)
    cartItems.value = [course]
    cartTotal.value = course.price
    
    // Pre-fill user email if logged in
    const page = usePage()
    if (page.props.auth?.user?.email) {
      chapaForm.value.email = page.props.auth.user.email
    }
    if (page.props.auth?.user?.name) {
      chapaForm.value.fullName = page.props.auth.user.name
    }
  } else {
    // Check for cart items
    const savedCart = sessionStorage.getItem('checkout_cart')
    if (savedCart) {
      cartItems.value = JSON.parse(savedCart)
      cartTotal.value = cartItems.value.reduce((sum, item) => sum + item.price, 0)
    } else {
      router.get('/')
    }
  }
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US').format(price) + ' ETB'
}

const goBack = () => {
  router.get('/')
}

const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text)
  copySuccess.value = true
  setTimeout(() => copySuccess.value = false, 2000)
}

const processChapaPayment = async () => {
  // Validate form
  if (!chapaForm.value.email) {
    alert('Please enter your email address')
    return
  }
  if (!chapaForm.value.fullName) {
    alert('Please enter your full name')
    return
  }
  
  isProcessing.value = true
  paymentStatus.value = 'processing'
  
  // Simulate Chapa payment processing
  await new Promise(resolve => setTimeout(resolve, 2000))
  
  const transactionId = 'CHAPA-' + Math.random().toString(36).substr(2, 10).toUpperCase()
  
  const paymentInfo = {
    amount: cartTotal.value,
    items: cartItems.value,
    paymentMethod: 'chapa',
    transactionId: transactionId,
    date: new Date().toISOString(),
    status: 'success',
    customerEmail: chapaForm.value.email,
    customerName: chapaForm.value.fullName
  }
  
  localStorage.setItem('last_payment', JSON.stringify(paymentInfo))
  sessionStorage.removeItem('checkout_cart')
  sessionStorage.removeItem('checkout_course')
  localStorage.removeItem('savedCart')
  
  paymentStatus.value = 'success'
  showSuccess.value = true
  
  setTimeout(() => {
    router.get('/payment/success')
  }, 1500)
}
</script>

<template>
  <Head title="Checkout | LearnHub" />
  
  <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
    <!-- Processing Modal -->
    <div v-if="isProcessing && !showSuccess" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
      <div class="bg-white dark:bg-slate-900 rounded-2xl p-8 text-center max-w-md mx-4">
        <div class="w-20 h-20 mx-auto mb-4">
          <div class="animate-spin rounded-full h-20 w-20 border-b-4 border-blue-600"></div>
        </div>
        <h3 class="text-xl font-bold mb-2 dark:text-white">Processing Payment</h3>
        <p class="text-slate-500 dark:text-slate-400">Please wait while we process your Chapa payment...</p>
        <p class="text-sm text-slate-400 mt-2">Do not close this window</p>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccess" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
      <div class="bg-white dark:bg-slate-900 rounded-2xl p-8 text-center max-w-md mx-4">
        <div class="w-20 h-20 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
          <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <h3 class="text-xl font-bold mb-2 dark:text-white">Payment Successful!</h3>
        <p class="text-slate-500 dark:text-slate-400">Redirecting to success page...</p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <button @click="goBack" class="flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-blue-600">
          <ChevronLeft class="w-5 h-5" />
          Back to Course
        </button>
        <div class="flex items-center gap-2">
          <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-xl">L</div>
          <span class="text-xl font-bold dark:text-white">Learn<span class="text-blue-600">Hub</span></span>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-8">
        <!-- Checkout Form -->
        <div class="lg:col-span-2">
          <!-- Order Summary -->
          <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border mb-6">
            <h2 class="text-xl font-bold mb-4 dark:text-white">Order Summary</h2>
            <div class="space-y-4">
              <div v-for="item in cartItems" :key="item.id" class="flex gap-4 pb-4 border-b">
                <img :src="item.image" class="w-20 h-20 rounded-lg object-cover" />
                <div class="flex-1">
                  <h3 class="font-bold dark:text-white">{{ item.title }}</h3>
                  <p class="text-sm text-slate-500">{{ item.instructor }}</p>
                  <p class="text-blue-600 font-bold mt-1">{{ formatPrice(item.price) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Chapa Payment -->
          <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                <Sparkles class="w-6 h-6 text-purple-600" />
              </div>
              <div>
                <h2 class="text-xl font-bold dark:text-white">Chapa Payment</h2>
                <p class="text-sm text-slate-500">Secure payment with Chapa</p>
              </div>
            </div>

            <!-- Account Details -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-4 mb-6">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold dark:text-white">📢 Send payment to:</p>
                <button @click="copyToClipboard('CHAPA-LEARNHUB-001')" class="text-xs text-blue-600 flex items-center gap-1">
                  <Copy class="w-3 h-3" />
                  {{ copySuccess ? 'Copied!' : 'Copy' }}
                </button>
              </div>
              <p class="text-lg font-mono font-bold dark:text-white">CHAPA-LEARNHUB-001</p>
              <p class="text-sm">Account Name: <span class="font-semibold">Hana Motuma</span></p>
              <p class="text-xs text-slate-500 mt-2">Pay with Chapa using the details below</p>
            </div>

            <!-- Chapa Form -->
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Full Name</label>
                <input 
                  type="text" 
                  v-model="chapaForm.fullName" 
                  placeholder="Enter your full name" 
                  autocomplete="off"
                  class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800"
                />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Email Address</label>
                <input 
                  type="email" 
                  v-model="chapaForm.email" 
                  placeholder="your@email.com" 
                  autocomplete="off"
                  class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800"
                />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Phone Number</label>
                <input 
                  type="tel" 
                  v-model="chapaForm.phoneNumber" 
                  placeholder="09XXXXXXXX" 
                  autocomplete="off"
                  class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800"
                />
              </div>
              <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-3 mt-4">
                <p class="text-sm text-purple-600 dark:text-purple-400">💡 After filling your details, click the pay button below to complete your payment via Chapa</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Total -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border sticky top-24">
            <h3 class="text-xl font-bold mb-4 dark:text-white">Payment Summary</h3>
            
            <div class="space-y-3 pb-4 border-b">
              <div class="flex justify-between">
                <span class="text-slate-500">Subtotal ({{ cartItems.length }} items)</span>
                <span class="dark:text-white">{{ formatPrice(cartTotal) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Discount</span>
                <span class="text-green-600">- {{ formatPrice(0) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Chapa Fee</span>
                <span class="dark:text-white">Free</span>
              </div>
            </div>
            
            <div class="flex justify-between pt-4 mb-6">
              <span class="font-bold text-lg dark:text-white">Total</span>
              <span class="text-2xl font-bold text-blue-600">{{ formatPrice(cartTotal) }}</span>
            </div>
            
            <button 
              @click="processChapaPayment" 
              :disabled="isProcessing"
              class="w-full py-4 bg-purple-600 text-white font-bold rounded-xl hover:bg-purple-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
            >
              <Sparkles class="w-5 h-5" />
              {{ isProcessing ? 'Processing...' : `Pay ${formatPrice(cartTotal)} with Chapa` }}
            </button>
            
            <div class="flex items-center justify-center gap-4 mt-4 text-xs text-slate-400">
              <span class="flex items-center gap-1"><Shield class="w-3 h-3" /> Secure Payment</span>
              <span class="flex items-center gap-1"><Truck class="w-3 h-3" /> Lifetime Access</span>
            </div>
            
            <div class="mt-6 pt-4 border-t text-center">
              <p class="text-xs text-slate-400">🔒 Your payment is encrypted and secure</p>
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
</style>