<template>
  <AuthenticatedLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-slate-900 py-12">
      <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Checkout</h1>
          </div>
          
          <div class="p-6">
            <div class="mb-8">
              <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
              <div v-for="item in cart" :key="item.id" class="flex justify-between py-3 border-b">
                <div>
                  <p class="font-medium">{{ item.title }}</p>
                  <p class="text-sm text-gray-500">{{ item.instructor }}</p>
                </div>
                <p class="font-semibold">ETB {{ formatPrice(item.price) }}</p>
              </div>
              <div class="flex justify-between pt-4 font-bold">
                <span>Total</span>
                <span class="text-xl text-blue-600">ETB {{ formatPrice(total) }}</span>
              </div>
            </div>
            
            <form @submit.prevent="processPayment">
              <h2 class="text-lg font-semibold mb-4">Payment Method</h2>
              <div class="space-y-3">
                <label class="flex items-center p-4 border rounded-lg cursor-pointer">
                  <input type="radio" v-model="paymentMethod" value="telebirr" class="mr-3">
                  <span>Telebirr</span>
                </label>
                <label class="flex items-center p-4 border rounded-lg cursor-pointer">
                  <input type="radio" v-model="paymentMethod" value="cbe_birr" class="mr-3">
                  <span>CBE Birr</span>
                </label>
              </div>
              
              <button type="submit" class="w-full mt-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">
                Proceed to Payment
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'

const props = defineProps({
  cart: Array,
  total: Number
})

const paymentMethod = ref('telebirr')

const formatPrice = (price) => {
  return new Intl.NumberFormat().format(price)
}

const processPayment = () => {
  router.post('/checkout/process', { payment_method: paymentMethod.value })
}
</script>