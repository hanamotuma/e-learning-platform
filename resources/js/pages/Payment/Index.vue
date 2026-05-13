<template>
  <div class="max-w-4xl mx-auto py-10 px-4">
    <h1 class="text-3xl font-bold mb-6">Payment Checkout</h1>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4">Your Courses</h2>
      <div v-for="item in cart" :key="item.id" class="flex justify-between py-2 border-b">
        <span>{{ item.title }}</span>
        <span class="font-bold">{{ item.price }} ETB</span>
      </div>
      <div class="text-right mt-4 font-bold text-xl">
        Total: {{ total }} ETB
      </div>
    </div>

    <button 
      @click="initiateChapaPayment"
      :disabled="isProcessing"
      class="w-full bg-blue-600 text-white py-4 rounded-lg font-bold hover:bg-blue-700 disabled:opacity-50"
    >
      {{ isProcessing ? 'Redirecting to Chapa...' : 'Pay ' + total + ' ETB with Chapa' }}
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    total: Number,
    cart: Array
});

const isProcessing = ref(false);

const initiateChapaPayment = async () => {
    isProcessing.value = true;
    try {
        // Send request to your Laravel route
        const response = await axios.post('/payment/initialize-chapa');
        
        if (response.data.success) {
            // Realistic redirect to Chapa's official gateway
            window.location.href = response.data.checkout_url;
        } else {
            alert('Payment initialization failed: ' + response.data.message);
            isProcessing.value = false;
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
        isProcessing.value = false;
    }
};
</script>