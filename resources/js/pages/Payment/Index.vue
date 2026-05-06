<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-slate-900 py-12">
            <div class="max-w-2xl mx-auto px-4">
                <!-- Payment Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Complete Payment</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">Total Amount: <span class="text-2xl font-bold text-blue-600">ETB {{ formatPrice(total) }}</span></p>
                </div>

                <!-- Payment Method Selection -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex space-x-4">
                            <button 
                                @click="selectedMethod = 'telebirr'"
                                :class="[
                                    'flex-1 py-3 px-4 rounded-xl font-semibold transition-all',
                                    selectedMethod === 'telebirr' 
                                        ? 'bg-blue-600 text-white shadow-md' 
                                        : 'bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200'
                                ]"
                            >
                                <img src="/images/telebirr-logo.png" class="h-6 mx-auto mb-1" v-if="false" />
                                <span>Telebirr</span>
                            </button>
                            <button 
                                @click="selectedMethod = 'cbe'"
                                :class="[
                                    'flex-1 py-3 px-4 rounded-xl font-semibold transition-all',
                                    selectedMethod === 'cbe' 
                                        ? 'bg-blue-600 text-white shadow-md' 
                                        : 'bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200'
                                ]"
                            >
                                <span>CBE Birr</span>
                            </button>
                            <button 
                                @click="selectedMethod = 'chapa'"
                                :class="[
                                    'flex-1 py-3 px-4 rounded-xl font-semibold transition-all',
                                    selectedMethod === 'chapa' 
                                        ? 'bg-blue-600 text-white shadow-md' 
                                        : 'bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200'
                                ]"
                            >
                                <span>Chapa</span>
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Telebirr Form -->
                        <div v-if="selectedMethod === 'telebirr'" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Telebirr Phone Number
                                </label>
                                <input 
                                    v-model="telebirrForm.phone_number"
                                    type="tel" 
                                    placeholder="09XXXXXXXX"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white"
                                />
                                <p class="text-xs text-gray-500 mt-1">Enter your Telebirr registered phone number</p>
                            </div>
                            
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <Shield class="w-5 h-5 text-blue-600" />
                                    <span class="text-sm text-blue-600 dark:text-blue-400">Secure payment via Telebirr</span>
                                </div>
                            </div>
                            
                            <button 
                                @click="processTelebirrPayment"
                                :disabled="processing"
                                class="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50"
                            >
                                <span v-if="!processing">Pay with Telebirr</span>
                                <span v-else>Processing...</span>
                            </button>
                        </div>

                        <!-- CBE Birr Form -->
                        <div v-if="selectedMethod === 'cbe'" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    CBE Birr Account Number
                                </label>
                                <input 
                                    v-model="cbeForm.account_number"
                                    type="text" 
                                    placeholder="10XXXXXXXXXX"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    CBE Birr Phone Number
                                </label>
                                <input 
                                    v-model="cbeForm.phone_number"
                                    type="tel" 
                                    placeholder="09XXXXXXXX"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white"
                                />
                            </div>
                            
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <Shield class="w-5 h-5 text-blue-600" />
                                    <span class="text-sm text-blue-600 dark:text-blue-400">Secure payment via CBE Birr</span>
                                </div>
                            </div>
                            
                            <button 
                                @click="processCBEPayment"
                                :disabled="processing"
                                class="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50"
                            >
                                <span v-if="!processing">Pay with CBE Birr</span>
                                <span v-else>Processing...</span>
                            </button>
                        </div>

                        <!-- Chapa Form -->
                        <div v-if="selectedMethod === 'chapa'" class="space-y-4">
                            <div class="bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 p-6 rounded-lg text-center">
                                <CreditCard class="w-16 h-16 text-purple-600 mx-auto mb-3" />
                                <h3 class="text-lg font-semibold mb-2">Pay with Chapa</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    You will be redirected to Chapa secure payment gateway
                                </p>
                                <button 
                                    @click="processChapaPayment"
                                    :disabled="processing"
                                    class="w-full py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition disabled:opacity-50"
                                >
                                    <span v-if="!processing">Continue to Chapa</span>
                                    <span v-else>Initializing...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="mt-6 bg-white dark:bg-slate-800 rounded-xl p-4">
                    <h3 class="font-semibold mb-2 dark:text-white">Order Summary</h3>
                    <div v-for="item in cart" :key="item.id" class="flex justify-between text-sm py-2">
                        <span class="text-gray-600 dark:text-gray-400">{{ item.title }}</span>
                        <span class="font-medium dark:text-white">ETB {{ formatPrice(item.price) }}</span>
                    </div>
                    <div class="border-t pt-2 mt-2 flex justify-between font-bold">
                        <span>Total</span>
                        <span class="text-blue-600">ETB {{ formatPrice(total) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 max-w-md w-full mx-4 text-center">
                <div v-if="paymentSuccess" class="space-y-4">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                        <CheckCircle class="w-10 h-10 text-green-600" />
                    </div>
                    <h3 class="text-xl font-bold dark:text-white">Payment Successful!</h3>
                    <p class="text-gray-600 dark:text-gray-400">Transaction ID: {{ transactionId }}</p>
                    <p class="text-sm text-gray-500">Redirecting to dashboard...</p>
                </div>
                <div v-else class="space-y-4">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto">
                        <Loader2 class="w-10 h-10 text-blue-600 animate-spin" />
                    </div>
                    <h3 class="text-xl font-bold dark:text-white">Processing Payment</h3>
                    <p class="text-gray-600 dark:text-gray-400">Please wait while we process your payment...</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Shield, CreditCard, CheckCircle, Loader2 } from 'lucide-vue-next'

const props = defineProps({
    total: Number,
    method: String,
    cart: Array
})

const selectedMethod = ref(props.method || 'telebirr')
const processing = ref(false)
const showModal = ref(false)
const paymentSuccess = ref(false)
const transactionId = ref('')

const telebirrForm = ref({
    phone_number: ''
})

const cbeForm = ref({
    account_number: '',
    phone_number: ''
})

const formatPrice = (price) => {
    return new Intl.NumberFormat().format(price)
}

const processTelebirrPayment = async () => {
    if (!telebirrForm.value.phone_number) {
        alert('Please enter your Telebirr phone number')
        return
    }
    
    processing.value = true
    showModal.value = true
    
    try {
        const response = await axios.post('/api/payment/telebirr', telebirrForm.value)
        
        if (response.data.success) {
            paymentSuccess.value = true
            transactionId.value = response.data.transaction_id
            
            // Redirect to dashboard after 2 seconds
            setTimeout(() => {
                window.location.href = '/student/dashboard'
            }, 2000)
        }
    } catch (error) {
        console.error('Payment failed:', error)
        showModal.value = false
        alert('Payment failed. Please try again.')
    } finally {
        processing.value = false
    }
}

const processCBEPayment = async () => {
    if (!cbeForm.value.account_number || !cbeForm.value.phone_number) {
        alert('Please fill in all CBE Birr details')
        return
    }
    
    processing.value = true
    showModal.value = true
    
    try {
        const response = await axios.post('/api/payment/cbe', cbeForm.value)
        
        if (response.data.success) {
            paymentSuccess.value = true
            transactionId.value = response.data.transaction_id
            
            setTimeout(() => {
                window.location.href = '/student/dashboard'
            }, 2000)
        }
    } catch (error) {
        console.error('Payment failed:', error)
        showModal.value = false
        alert('Payment failed. Please try again.')
    } finally {
        processing.value = false
    }
}

const processChapaPayment = async () => {
    processing.value = true
    showModal.value = true
    
    try {
        const response = await axios.post('/api/payment/chapa/initialize')
        
        if (response.data.success) {
            paymentSuccess.value = true
            transactionId.value = response.data.transaction_id
            
            setTimeout(() => {
                window.location.href = '/student/dashboard'
            }, 2000)
        }
    } catch (error) {
        console.error('Payment failed:', error)
        showModal.value = false
        alert('Payment failed. Please try again.')
    } finally {
        processing.value = false
    }
}
</script>