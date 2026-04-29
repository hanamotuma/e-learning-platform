<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { CreditCard, Paypal, Banknote, Lock, ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
    course: Object,
    existing_enrollment: Object
})

const paymentMethod = ref('stripe')
const processing = ref(false)

const submitEnrollment = () => {
    if (!paymentMethod.value) {
        alert('Please select a payment method')
        return
    }
    
    processing.value = true
    
    router.post(route('enroll.process', props.course.id), {
        payment_method: paymentMethod.value
    }, {
        onFinish: () => {
            processing.value = false
        },
        onError: (errors) => {
            console.error(errors)
        }
    })
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price)
}
</script>

<template>
    <Head :title="`Checkout - ${course.title}`" />
    
    <div class="min-h-screen bg-slate-50 py-12">
        <div class="max-w-6xl mx-auto px-4">
            
            <!-- Header -->
            <div class="mb-8">
                <Link :href="route('student.courses.show', course.id)" class="text-blue-600 hover:text-blue-700 flex items-center gap-2 mb-4">
                    <ArrowLeft class="w-4 h-4" />
                    Back to Course
                </Link>
                <h1 class="text-3xl font-black text-slate-900">Complete Your Enrollment</h1>
                <p class="text-slate-500 mt-2">Secure your spot in this course</p>
            </div>
            
            <div class="grid lg:grid-cols-3 gap-8">
                
                <!-- Checkout Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6">
                        <h2 class="text-xl font-bold text-slate-900 mb-6">Select Payment Method</h2>
                        
                        <div class="space-y-4">
                            <!-- Stripe -->
                            <label class="flex items-center p-4 border rounded-xl cursor-pointer transition-all"
                                :class="paymentMethod === 'stripe' ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" value="stripe" v-model="paymentMethod" class="w-4 h-4 text-blue-600" />
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center gap-2">
                                        <CreditCard class="w-5 h-5 text-blue-600" />
                                        <span class="font-semibold text-slate-900">Credit / Debit Card</span>
                                    </div>
                                    <p class="text-sm text-slate-500 mt-1">Pay securely with Stripe</p>
                                </div>
                                <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" class="h-6" alt="Stripe" />
                            </label>
                            
                            <!-- PayPal -->
                            <label class="flex items-center p-4 border rounded-xl cursor-pointer transition-all"
                                :class="paymentMethod === 'paypal' ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" value="paypal" v-model="paymentMethod" class="w-4 h-4 text-blue-600" />
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center gap-2">
                                        <Paypal class="w-5 h-5 text-blue-600" />
                                        <span class="font-semibold text-slate-900">PayPal</span>
                                    </div>
                                    <p class="text-sm text-slate-500 mt-1">Pay with your PayPal account</p>
                                </div>
                                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" class="h-6" alt="PayPal" />
                            </label>
                            
                            <!-- Manual / Bank Transfer -->
                            <label class="flex items-center p-4 border rounded-xl cursor-pointer transition-all"
                                :class="paymentMethod === 'manual' ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" value="manual" v-model="paymentMethod" class="w-4 h-4 text-blue-600" />
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center gap-2">
                                        <Banknote class="w-5 h-5 text-blue-600" />
                                        <span class="font-semibold text-slate-900">Bank Transfer</span>
                                    </div>
                                    <p class="text-sm text-slate-500 mt-1">Pay via bank transfer (manual verification)</p>
                                </div>
                            </label>
                        </div>
                        
                        <!-- Security Note -->
                        <div class="mt-6 p-4 bg-slate-50 rounded-xl flex items-center gap-3">
                            <Lock class="w-5 h-5 text-emerald-600" />
                            <p class="text-xs text-slate-600">Your payment information is secure. We never store your card details.</p>
                        </div>
                        
                        <!-- Submit Button -->
                        <button @click="submitEnrollment" 
                            :disabled="processing"
                            class="w-full mt-6 bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ processing ? 'Processing...' : `Pay ${formatPrice(course.price)}` }}
                        </button>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 sticky top-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Order Summary</h3>
                        
                        <div class="flex gap-4 mb-4">
                            <img :src="course.image ? `/storage/${course.image}` : 'https://placehold.co/80x80'" 
                                class="w-20 h-20 rounded-lg object-cover" />
                            <div>
                                <h4 class="font-semibold text-slate-900">{{ course.title }}</h4>
                                <p class="text-sm text-slate-500">{{ course.category?.name || 'Course' }}</p>
                            </div>
                        </div>
                        
                        <div class="border-t border-slate-100 pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Course Price</span>
                                <span class="font-medium text-slate-900">{{ formatPrice(course.price) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Tax</span>
                                <span class="font-medium text-slate-900">$0.00</span>
                            </div>
                            <div class="border-t border-slate-100 pt-2 mt-2">
                                <div class="flex justify-between font-bold">
                                    <span class="text-slate-900">Total</span>
                                    <span class="text-blue-600 text-xl">{{ formatPrice(course.price) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- What's included -->
                        <div class="mt-6 pt-4 border-t border-slate-100">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">What's included</p>
                            <ul class="space-y-2 text-sm text-slate-600">
                                <li class="flex items-center gap-2">✓ Full lifetime access</li>
                                <li class="flex items-center gap-2">✓ Certificate of completion</li>
                                <li class="flex items-center gap-2">✓ 30-day money-back guarantee</li>
                                <li class="flex items-center gap-2">✓ Access on mobile and TV</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>