<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { 
  CreditCard, Shield, Truck, ChevronLeft, Smartphone, Landmark, 
  Sparkles, Upload, Copy, CheckCircle, Banknote, Wallet
} from 'lucide-vue-next'

const page = usePage()
const authUser = computed(() => page.props.auth?.user)

const cartItems = ref([])
const cartTotal = ref(0)
const selectedPaymentMethod = ref('telebirr')
const paymentMode = ref('together') // 'together' or 'separate'
const selectedCourses = ref([]) // For separate payment
const isProcessing = ref(false)
const copySuccess = ref(false)
const screenshotPreview = ref(null)
const screenshotFile = ref(null)

// Form fields
const telebirrForm = ref({ phoneNumber: '', pin: '' })
const cbeForm = ref({ accountNumber: '', pin: '' })
const chapaForm = ref({ email: '', phoneNumber: '' })
const cardForm = ref({ cardNumber: '', cardName: '', expiryDate: '', cvv: '' })
const uploadForm = ref({ bankName: 'telebirr', referenceNumber: '' })

onMounted(() => {
  const savedCart = sessionStorage.getItem('checkout_cart')
  if (savedCart) {
    cartItems.value = JSON.parse(savedCart)
    cartTotal.value = cartItems.value.reduce((sum, item) => sum + item.price, 0)
    // Initialize selectedCourses with all items for separate payment
    selectedCourses.value = cartItems.value.map(item => ({ ...item, selected: true }))
  } else {
    router.get('/')
  }
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US').format(price) + ' ETB'
}

const goBack = () => {
  sessionStorage.setItem('open_cart', 'true')
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

// Toggle course selection for separate payment
const toggleCourseSelection = (courseId) => {
  const course = selectedCourses.value.find(c => c.id === courseId)
  if (course) {
    course.selected = !course.selected
  }
}

// Get selected courses total
const selectedTotal = computed(() => {
  return selectedCourses.value
    .filter(c => c.selected)
    .reduce((sum, item) => sum + item.price, 0)
})

// Get count of selected courses
const selectedCount = computed(() => {
  return selectedCourses.value.filter(c => c.selected).length
})

// Get payment amount based on mode
const paymentAmount = computed(() => {
  if (paymentMode.value === 'together') {
    return cartTotal.value
  } else {
    return selectedTotal.value
  }
})

const processPayment = async () => {
  if (paymentMode.value === 'separate' && selectedCount.value === 0) {
    alert('Please select at least one course to pay for')
    isProcessing.value = false
    return
  }
  
  isProcessing.value = true
  
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
  
  await new Promise(resolve => setTimeout(resolve, 2000))
  
  if (paymentMode.value === 'separate') {
    // Pay for selected courses only
    const selectedItems = selectedCourses.value.filter(c => c.selected)
    const paymentInfo = {
      amount: selectedTotal.value,
      items: selectedItems,
      paymentMethod: selectedPaymentMethod.value,
      transactionId: 'TXN-' + Math.random().toString(36).substr(2, 10).toUpperCase(),
      date: new Date().toISOString(),
      status: 'success'
    }
    localStorage.setItem('last_payment', JSON.stringify(paymentInfo))
  } else {
    // Pay together for all courses
    const paymentInfo = {
      amount: cartTotal.value,
      items: cartItems.value,
      paymentMethod: selectedPaymentMethod.value,
      transactionId: 'TXN-' + Math.random().toString(36).substr(2, 10).toUpperCase(),
      date: new Date().toISOString(),
      status: 'success'
    }
    localStorage.setItem('last_payment', JSON.stringify(paymentInfo))
  }
  
  sessionStorage.removeItem('checkout_cart')
  localStorage.removeItem('savedCart')
  
  router.get('/payment/success')
}

const getPaymentIcon = () => {
  switch(selectedPaymentMethod.value) {
    case 'telebirr': return Smartphone
    case 'cbe': return Landmark
    case 'chapa': return Sparkles
    case 'card': return CreditCard
    case 'upload': return Upload
    default: return CreditCard
  }
}

const getAccountNumber = () => {
  switch(selectedPaymentMethod.value) {
    case 'telebirr': return '0963055018'
    case 'cbe': return '1000123456789'
    case 'chapa': return 'CHAPA-LEARNHUB-001'
    case 'card': return 'XXXX-XXXX-XXXX-1234'
    default: return '0963055018'
  }
}
</script>

<template>
  <Head title="Checkout | LearnHub" />
  
  <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <button @click="goBack" class="flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-blue-600">
          <ChevronLeft class="w-5 h-5" />
          Back to Cart
        </button>
        <div class="flex items-center gap-2">
          <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-xl">L</div>
          <span class="text-xl font-bold dark:text-white">Learn<span class="text-blue-600">Hub</span></span>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-8">
        <!-- Left Column -->
        <div class="lg:col-span-2">
          <!-- Order Summary with Radio Buttons on Courses -->
          <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border mb-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-bold dark:text-white">Order Summary</h2>
              <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" value="together" v-model="paymentMode" class="w-4 h-4 text-blue-600" />
                  <span class="text-sm dark:text-white">Pay All</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" value="separate" v-model="paymentMode" class="w-4 h-4 text-blue-600" />
                  <span class="text-sm dark:text-white">Select Courses</span>
                </label>
              </div>
            </div>
            
            <div class="space-y-4">
              <div v-for="item in cartItems" :key="item.id" class="flex gap-4 pb-4 border-b">
                <!-- Radio button for separate payment mode -->
                <div v-if="paymentMode === 'separate'" class="flex items-center">
                  <input 
                    type="checkbox" 
                    :checked="selectedCourses.find(c => c.id === item.id)?.selected"
                    @change="toggleCourseSelection(item.id)"
                    class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                  />
                </div>
                <img :src="item.image" class="w-20 h-20 rounded-lg object-cover" />
                <div class="flex-1">
                  <h3 class="font-bold dark:text-white">{{ item.title }}</h3>
                  <p class="text-sm text-slate-500">{{ item.instructor }}</p>
                  <p class="text-blue-600 font-bold mt-1">{{ formatPrice(item.price) }}</p>
                </div>
              </div>
            </div>
            
            <div v-if="paymentMode === 'separate' && selectedCount > 0" class="mt-4 pt-4 border-t">
              <div class="flex justify-between font-bold">
                <span class="dark:text-white">Selected ({{ selectedCount }}):</span>
                <span class="text-blue-600">{{ formatPrice(selectedTotal) }}</span>
              </div>
            </div>
          </div>

          <!-- Payment Method Selection -->
          <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border">
            <h2 class="text-xl font-bold mb-4 dark:text-white">Select Payment Method</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
              <button @click="selectedPaymentMethod = 'telebirr'" :class="['p-4 border rounded-xl text-center transition-all', selectedPaymentMethod === 'telebirr' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'hover:border-blue-300']">
                <Smartphone class="w-6 h-6 mx-auto mb-2 text-green-600" />
                <span class="text-sm font-medium">TeleBirr</span>
              </button>
              <button @click="selectedPaymentMethod = 'cbe'" :class="['p-4 border rounded-xl text-center transition-all', selectedPaymentMethod === 'cbe' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'hover:border-blue-300']">
                <Landmark class="w-6 h-6 mx-auto mb-2 text-blue-600" />
                <span class="text-sm font-medium">CBE Birr</span>
              </button>
              <button @click="selectedPaymentMethod = 'chapa'" :class="['p-4 border rounded-xl text-center transition-all', selectedPaymentMethod === 'chapa' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'hover:border-blue-300']">
                <Sparkles class="w-6 h-6 mx-auto mb-2 text-purple-600" />
                <span class="text-sm font-medium">Chapa</span>
              </button>
              <button @click="selectedPaymentMethod = 'card'" :class="['p-4 border rounded-xl text-center transition-all', selectedPaymentMethod === 'card' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'hover:border-blue-300']">
                <CreditCard class="w-6 h-6 mx-auto mb-2 text-purple-600" />
                <span class="text-sm font-medium">Card</span>
              </button>
              <button @click="selectedPaymentMethod = 'upload'" :class="['p-4 border rounded-xl text-center transition-all', selectedPaymentMethod === 'upload' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'hover:border-blue-300']">
                <Upload class="w-6 h-6 mx-auto mb-2 text-orange-600" />
                <span class="text-sm font-medium">Upload</span>
              </button>
            </div>

            <!-- Account Details (exclude upload) -->
            <div v-if="selectedPaymentMethod !== 'upload'" class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-4 mb-6">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold dark:text-white">📢 Send payment to:</p>
                <button @click="copyToClipboard(getAccountNumber())" class="text-xs text-blue-600 flex items-center gap-1">
                  <Copy class="w-3 h-3" />
                  {{ copySuccess ? 'Copied!' : 'Copy' }}
                </button>
              </div>
              <p class="text-lg font-mono font-bold dark:text-white">{{ getAccountNumber() }}</p>
              <p class="text-sm">Account Name: <span class="font-semibold">Hana Motuma</span></p>
            </div>

            <!-- TeleBirr Form -->
            <div v-if="selectedPaymentMethod === 'telebirr'" class="space-y-4">
              <h3 class="font-bold text-lg mb-3">TeleBirr Payment</h3>
              <div>
                <label class="block text-sm font-medium mb-1">Your TeleBirr Phone Number</label>
                <input type="tel" v-model="telebirrForm.phoneNumber" placeholder="09XXXXXXXX" autocomplete="off" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">TeleBirr PIN (6 digits)</label>
                <input type="password" v-model="telebirrForm.pin" placeholder="Enter your 6-digit PIN" maxlength="6" autocomplete="new-password" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500" />
              </div>
            </div>

            <!-- CBE Form -->
            <div v-if="selectedPaymentMethod === 'cbe'" class="space-y-4">
              <h3 class="font-bold text-lg mb-3">CBE Birr Payment</h3>
              <div>
                <label class="block text-sm font-medium mb-1">Your CBE Account Number</label>
                <input type="text" v-model="cbeForm.accountNumber" placeholder="1000XXXXXXXXX (13 digits)" autocomplete="off" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">CBE Birr PIN (4 digits)</label>
                <input type="password" v-model="cbeForm.pin" placeholder="Enter your 4-digit PIN" maxlength="4" autocomplete="new-password" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500" />
              </div>
            </div>

            <!-- Chapa Form -->
            <div v-if="selectedPaymentMethod === 'chapa'" class="space-y-4">
              <h3 class="font-bold text-lg mb-3">Chapa Payment</h3>
              <div>
                <label class="block text-sm font-medium mb-1">Email Address</label>
                <input type="email" v-model="chapaForm.email" placeholder="your@email.com" autocomplete="off" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Phone Number</label>
                <input type="tel" v-model="chapaForm.phoneNumber" placeholder="09XXXXXXXX" autocomplete="off" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500" />
              </div>
            </div>

            <!-- Card Form -->
            <div v-if="selectedPaymentMethod === 'card'" class="space-y-4">
              <h3 class="font-bold text-lg mb-3">Card Payment</h3>
              <div>
                <label class="block text-sm font-medium mb-1">Card Number</label>
                <input type="text" v-model="cardForm.cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="off" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Cardholder Name</label>
                <input type="text" v-model="cardForm.cardName" placeholder="HANA MOTUMA" autocomplete="off" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500" />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Expiry Date</label>
                  <input type="text" v-model="cardForm.expiryDate" placeholder="MM/YY" maxlength="5" autocomplete="off" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">CVV</label>
                  <input type="password" v-model="cardForm.cvv" placeholder="123" maxlength="4" autocomplete="new-password" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
            </div>

            <!-- Upload Form -->
            <div v-if="selectedPaymentMethod === 'upload'" class="space-y-4">
              <h3 class="font-bold text-lg mb-3">Upload Payment Screenshot</h3>
              <div>
                <label class="block text-sm font-medium mb-1">Select Your Bank</label>
                <select v-model="uploadForm.bankName" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500">
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
                <input type="text" v-model="uploadForm.referenceNumber" placeholder="Enter the reference number from your payment" autocomplete="off" class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500" />
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
            </div>
          </div>
        </div>

        <!-- Right Column - Payment Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-sm border sticky top-24">
            <h3 class="text-xl font-bold mb-4 dark:text-white">Payment Summary</h3>
            
            <div class="space-y-3 pb-4 border-b">
              <div class="flex justify-between">
                <span class="text-slate-500">{{ paymentMode === 'together' ? 'Subtotal' : 'Selected Courses' }} ({{ paymentMode === 'together' ? cartItems.length : selectedCount }} items)</span>
                <span class="dark:text-white">{{ formatPrice(paymentAmount) }}</span>
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
              <span class="text-2xl font-bold text-blue-600">{{ formatPrice(paymentAmount) }}</span>
            </div>
            
            <div v-if="paymentMode === 'separate' && selectedCount === 0" class="text-xs text-red-500 mb-4 text-center">
              Please select at least one course
            </div>
            
            <button @click="processPayment" :disabled="isProcessing || (paymentMode === 'separate' && selectedCount === 0)" class="w-full py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
              <component :is="getPaymentIcon()" class="w-5 h-5" />
              {{ isProcessing ? 'Processing...' : `Pay ${formatPrice(paymentAmount)}` }}
            </button>
            
            <div class="flex items-center justify-center gap-4 mt-4 text-xs text-slate-400">
              <span class="flex items-center gap-1"><Shield class="w-3 h-3" /> Secure Payment</span>
              <span class="flex items-center gap-1"><Truck class="w-3 h-3" /> Lifetime Access</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>