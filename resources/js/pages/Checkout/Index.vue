<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { 
  CreditCard, Shield, Truck, ChevronLeft, Smartphone, Landmark, 
  Sparkles, CheckCircle, Upload, FileText, Copy, Check,
  Building2, Phone, User, Wallet, Banknote
} from 'lucide-vue-next'
import axios from 'axios'

const page = usePage()
const authUser = computed(() => page.props.auth?.user)

const cartItems = ref([])
const cartTotal = ref(0)
const selectedPaymentMethod = ref('telebirr')
const isProcessing = ref(false)
const paymentStatus = ref(null)
const showSuccess = ref(false)
const showPaymentForm = ref(true)
const screenshotFile = ref(null)
const screenshotPreview = ref(null)
const copySuccess = ref(false)

// TeleBirr form fields (FIXED: removed amount, PIN 6 digits)
const telebirrForm = ref({
  phoneNumber: '',
  pin: ''
})

// CBE Birr form fields (FIXED: removed amount, PIN 4 digits)
const cbeForm = ref({
  accountNumber: '',
  pin: ''
})

// Chapa form fields
const chapaForm = ref({
  email: '',
  phoneNumber: ''
})

// Credit Card form fields
const cardForm = ref({
  cardNumber: '',
  cardName: '',
  expiryDate: '',
  cvv: ''
})

// Upload option
const showUploadOption = ref(false)
const uploadForm = ref({
  bankName: 'telebirr',
  referenceNumber: '',
  screenshot: null
})

// Account details for each payment method
const accountDetails = {
  telebirr: {
    number: '0963055018',
    name: 'Hana Motuma',
    bank: 'TeleBirr',
    instruction: 'Send payment to this TeleBirr number'
  },
  cbe: {
    number: '1000123456789',
    name: 'Hana Motuma Turi',
    bank: 'CBE',
    instruction: 'Transfer to this CBE Birr account number'
  },
  chapa: {
    number: 'CHAPA-LEARNHUB-001',
    name: 'Hana Motuma',
    bank: 'Chapa',
    instruction: 'Pay with Chapa using the details below'
  },
  card: {
    number: 'XXXX-XXXX-XXXX-1234',
    name: 'Hana Motuma',
    bank: 'International Bank',
    instruction: 'Pay with credit/debit card'
  }
}

onMounted(() => {
  const savedCart = sessionStorage.getItem('checkout_cart')
  if (savedCart) {
    cartItems.value = JSON.parse(savedCart)
    cartTotal.value = cartItems.value.reduce((sum, item) => sum + item.price, 0)
  } else {
    router.get('/')
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

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    screenshotFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      screenshotPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Process payment based on selected method (FIXED: updated validations)
const processPayment = async () => {
  isProcessing.value = true
  paymentStatus.value = 'processing'
  
  // Validate based on payment method
  if (selectedPaymentMethod.value === 'telebirr') {
    if (!telebirrForm.value.phoneNumber) {
      alert('Please enter your TeleBirr phone number')
      isProcessing.value = false
      return
    }
    if (!telebirrForm.value.pin || telebirrForm.value.pin.length !== 6) {
      alert('Please enter your 6-digit TeleBirr PIN')
      isProcessing.value = false
      return
    }
  } else if (selectedPaymentMethod.value === 'cbe') {
    if (!cbeForm.value.accountNumber) {
      alert('Please enter your CBE account number')
      isProcessing.value = false
      return
    }
    if (!cbeForm.value.pin || cbeForm.value.pin.length !== 4) {
      alert('Please enter your 4-digit CBE PIN')
      isProcessing.value = false
      return
    }
  } else if (selectedPaymentMethod.value === 'chapa') {
    if (!chapaForm.value.email) {
      alert('Please enter your email address')
      isProcessing.value = false
      return
    }
  } else if (selectedPaymentMethod.value === 'card') {
    if (!cardForm.value.cardNumber || !cardForm.value.cardName || !cardForm.value.expiryDate || !cardForm.value.cvv) {
      alert('Please fill all card details')
      isProcessing.value = false
      return
    }
  } else if (selectedPaymentMethod.value === 'upload') {
    if (!uploadForm.value.referenceNumber) {
      alert('Please enter the reference number')
      isProcessing.value = false
      return
    }
    if (!screenshotFile.value) {
      alert('Please upload payment screenshot')
      isProcessing.value = false
      return
    }
  }
  
  // Simulate payment processing
  await new Promise(resolve => setTimeout(resolve, 2000))
  
  const paymentInfo = {
    amount: cartTotal.value,
    items: cartItems.value,
    paymentMethod: selectedPaymentMethod.value,
    transactionId: 'TXN-' + Math.random().toString(36).substr(2, 10).toUpperCase(),
    date: new Date().toISOString(),
    status: 'success',
    accountNumber: accountDetails[selectedPaymentMethod.value === 'upload' ? 'telebirr' : selectedPaymentMethod.value]?.number
  }
  
  localStorage.setItem('last_payment', JSON.stringify(paymentInfo))
  sessionStorage.removeItem('checkout_cart')
  localStorage.removeItem('savedCart')
  
  paymentStatus.value = 'success'
  showSuccess.value = true
  
  setTimeout(() => {
    router.get('/payment/success')
  }, 1500)
}

const getPaymentIcon = () => {
  switch(selectedPaymentMethod.value) {
    case 'telebirr': return '📱'
    case 'cbe': return '🏦'
    case 'chapa': return '💳'
    case 'card': return '💳'
    case 'upload': return '📎'
    default: return '💳'
  }
}

// Watch for payment method change to reset forms
watch(selectedPaymentMethod, () => {
  showPaymentForm.value = true
  showUploadOption.value = false
})
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
        <p class="text-slate-500 dark:text-slate-400">Please wait while we process your payment...</p>
        <p class="text-sm text-slate-400 mt-2">Do not close this window</p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <button @click="goBack" class="flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-blue-600">
          <ChevronLeft class="w-5 h-5" />
          Back to Cart
        </button>
        <div class="flex items-center gap-2">
          <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold">L</div>
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

          <!-- Payment Method Selection -->
          <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border mb-6">
            <h2 class="text-xl font-bold mb-4 dark:text-white">Select Payment Method</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
              <button @click="selectedPaymentMethod = 'telebirr'" class="p-4 border rounded-xl text-center transition-all" :class="selectedPaymentMethod === 'telebirr' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'hover:border-blue-300'">
                <Smartphone class="w-6 h-6 mx-auto mb-2 text-green-600" />
                <span class="text-sm font-medium">TeleBirr</span>
              </button>
              
              <button @click="selectedPaymentMethod = 'cbe'" class="p-4 border rounded-xl text-center transition-all" :class="selectedPaymentMethod === 'cbe' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'hover:border-blue-300'">
                <Landmark class="w-6 h-6 mx-auto mb-2 text-blue-600" />
                <span class="text-sm font-medium">CBE Birr</span>
              </button>
              
              <button @click="selectedPaymentMethod = 'chapa'" class="p-4 border rounded-xl text-center transition-all" :class="selectedPaymentMethod === 'chapa' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'hover:border-blue-300'">
                <Sparkles class="w-6 h-6 mx-auto mb-2 text-purple-600" />
                <span class="text-sm font-medium">Chapa</span>
              </button>
              
              <button @click="selectedPaymentMethod = 'card'" class="p-4 border rounded-xl text-center transition-all" :class="selectedPaymentMethod === 'card' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'hover:border-blue-300'">
                <CreditCard class="w-6 h-6 mx-auto mb-2 text-purple-600" />
                <span class="text-sm font-medium">Card</span>
              </button>
              
              <button @click="selectedPaymentMethod = 'upload'" class="p-4 border rounded-xl text-center transition-all" :class="selectedPaymentMethod === 'upload' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'hover:border-blue-300'">
                <Upload class="w-6 h-6 mx-auto mb-2 text-orange-600" />
                <span class="text-sm font-medium">Upload</span>
              </button>
            </div>

            <!-- Account Details to Transfer To -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-4 mb-6">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold dark:text-white">📢 Send payment to:</p>
                <button @click="copyToClipboard(accountDetails[selectedPaymentMethod === 'upload' ? 'telebirr' : selectedPaymentMethod]?.number)" class="text-xs text-blue-600 flex items-center gap-1">
                  <Copy class="w-3 h-3" />
                  {{ copySuccess ? 'Copied!' : 'Copy' }}
                </button>
              </div>
              <p class="text-lg font-mono font-bold dark:text-white">{{ accountDetails[selectedPaymentMethod === 'upload' ? 'telebirr' : selectedPaymentMethod]?.number }}</p>
              <p class="text-sm">Account Name: <span class="font-semibold">{{ accountDetails[selectedPaymentMethod === 'upload' ? 'telebirr' : selectedPaymentMethod]?.name }}</span></p>
              <p class="text-xs text-slate-500 mt-2">{{ accountDetails[selectedPaymentMethod === 'upload' ? 'telebirr' : selectedPaymentMethod]?.instruction }}</p>
            </div>

            <!-- TeleBirr Form - FIXED: PIN 6 digits, no email, proper placeholder -->
            <div v-if="selectedPaymentMethod === 'telebirr'" class="space-y-4">
              <h3 class="font-bold text-lg mb-3">TeleBirr Payment</h3>
              <div>
                <label class="block text-sm font-medium mb-1">Your TeleBirr Phone Number</label>
                <input type="tel" v-model="telebirrForm.phoneNumber" placeholder="09XXXXXXXX" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">TeleBirr PIN (6 digits)</label>
                <input type="password" v-model="telebirrForm.pin" placeholder="Enter your 6-digit PIN" maxlength="6" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800" />
              </div>
              <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3">
                <p class="text-sm text-blue-600 dark:text-blue-400">💡 After sending payment, click the pay button below to confirm</p>
              </div>
            </div>

            <!-- CBE Birr Form - FIXED: PIN 4 digits, no email, proper placeholder -->
            <div v-if="selectedPaymentMethod === 'cbe'" class="space-y-4">
              <h3 class="font-bold text-lg mb-3">CBE Birr Payment</h3>
              <div>
                <label class="block text-sm font-medium mb-1">Your CBE Account Number</label>
                <input type="text" v-model="cbeForm.accountNumber" placeholder="1000XXXXXXXXX (13 digits)" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">CBE Birr PIN (4 digits)</label>
                <input type="password" v-model="cbeForm.pin" placeholder="Enter your 4-digit PIN" maxlength="4" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800" />
              </div>
              <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3">
                <p class="text-sm text-blue-600 dark:text-blue-400">💡 Your account number should start with 1000 and be 13 digits</p>
              </div>
            </div>

            <!-- Chapa Form -->
            <div v-if="selectedPaymentMethod === 'chapa'" class="space-y-4">
              <h3 class="font-bold text-lg mb-3">Chapa Payment</h3>
              <div>
                <label class="block text-sm font-medium mb-1">Email Address</label>
                <input type="email" v-model="chapaForm.email" placeholder="your@email.com" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Phone Number (Optional)</label>
                <input type="tel" v-model="chapaForm.phoneNumber" placeholder="09XXXXXXXX" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800" />
              </div>
              <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-3">
                <p class="text-sm text-purple-600 dark:text-purple-400">💡 You will receive a payment link via email</p>
              </div>
            </div>

            <!-- Card Form -->
            <div v-if="selectedPaymentMethod === 'card'" class="space-y-4">
              <h3 class="font-bold text-lg mb-3">Card Payment</h3>
              <div>
                <label class="block text-sm font-medium mb-1">Card Number</label>
                <input type="text" v-model="cardForm.cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Cardholder Name</label>
                <input type="text" v-model="cardForm.cardName" placeholder="HANA MOTUMA" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800" />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Expiry Date</label>
                  <input type="text" v-model="cardForm.expiryDate" placeholder="MM/YY" maxlength="5" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">CVV</label>
                  <input type="password" v-model="cardForm.cvv" placeholder="123" maxlength="4" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800" />
                </div>
              </div>
            </div>

            <!-- Upload Form -->
            <div v-if="selectedPaymentMethod === 'upload'" class="space-y-4">
              <h3 class="font-bold text-lg mb-3">Upload Payment Screenshot</h3>
              <div>
                <label class="block text-sm font-medium mb-1">Select Your Bank</label>
                <select v-model="uploadForm.bankName" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800">
                  <option value="telebirr">TeleBirr</option>
                  <option value="cbe">CBE Birr</option>
                  <option value="chapa">Chapa</option>
                  <option value="bank">Commercial Bank of Ethiopia</option>
                  <option value="dashen">Dashen Bank</option>
                  <option value="awash">Awash Bank</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Transaction Reference Number</label>
                <input type="text" v-model="uploadForm.referenceNumber" placeholder="Enter the reference number from your payment" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-800" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Upload Payment Screenshot</label>
                <div class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-800" @click="$refs.fileInput.click()">
                  <Upload class="w-10 h-10 mx-auto mb-2 text-slate-400" />
                  <p class="text-sm text-slate-500">Click to upload screenshot</p>
                  <p class="text-xs text-slate-400">PNG, JPG up to 5MB</p>
                </div>
                <input type="file" ref="fileInput" @change="handleFileUpload" accept="image/*" class="hidden" />
                <div v-if="screenshotPreview" class="mt-3">
                  <img :src="screenshotPreview" class="w-32 h-32 object-cover rounded-lg" />
                </div>
              </div>
              <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-3">
                <p class="text-sm text-orange-600 dark:text-orange-400">💡 After uploading screenshot, click pay button for confirmation</p>
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
                <span class="text-slate-500">Payment Fee</span>
                <span class="dark:text-white">Free</span>
              </div>
            </div>
            
            <div class="flex justify-between pt-4 mb-6">
              <span class="font-bold text-lg dark:text-white">Total</span>
              <span class="text-2xl font-bold text-blue-600">{{ formatPrice(cartTotal) }}</span>
            </div>
            
            <button 
              @click="processPayment" 
              :disabled="isProcessing"
              class="w-full py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
            >
              <span class="text-lg">{{ getPaymentIcon() }}</span>
              {{ isProcessing ? 'Processing...' : `Pay ${formatPrice(cartTotal)}` }}
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