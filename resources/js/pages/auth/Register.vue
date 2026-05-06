<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { User, Mail, Lock, Eye, EyeOff, AlertCircle } from 'lucide-vue-next'

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const form = useForm({
  username: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const submit = () => {
  form.post(route('register'), {
    onSuccess: () => {
      // Check if there's a pending payment in session storage
      const pendingPayment = sessionStorage.getItem('pendingPayment')
      if (pendingPayment) {
        const paymentData = JSON.parse(pendingPayment)
        // Direct URL construction instead of route() to avoid errors
        window.location.href = `/payment/page?type=${paymentData.payment_type}&course_id=${paymentData.course_id || ''}`
        sessionStorage.removeItem('pendingPayment')
      } else {
        // Redirect to home if no pending payment
        window.location.href = '/'
      }
    },
    onError: (errors) => {
      console.error('Registration errors:', errors)
    }
  })
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white dark:from-slate-900 dark:to-slate-800 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
      <!-- Header -->
      <div class="bg-blue-600 px-6 py-8 text-center">
        <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <span class="text-3xl font-black text-white">L</span>
        </div>
        <h2 class="text-2xl font-bold text-white">Join LearnHub</h2>
        <p class="text-blue-100 mt-1">Start your learning journey today</p>
      </div>

      <!-- Display General Errors -->
      <div v-if="form.errors && Object.keys(form.errors).length > 0" class="mx-6 mt-4">
        <div v-for="(error, key) in form.errors" :key="key" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 mb-2">
          <div class="flex items-center space-x-2">
            <AlertCircle class="w-4 h-4 text-red-500" />
            <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="p-6 space-y-4">
        <!-- Username Field -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Username</label>
          <div class="relative">
            <User class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
            <input
              v-model="form.username"
              type="text"
              placeholder="username"
              :class="[
                'w-full pl-10 pr-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-900 dark:text-white transition-all',
                form.errors.username ? 'border-red-500 dark:border-red-500' : 'border-slate-200 dark:border-slate-700'
              ]"
            />
          </div>
          <p v-if="form.errors.username" class="text-red-500 text-sm mt-1">{{ form.errors.username }}</p>
        </div>

        <!-- Full Name Field -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Full Name</label>
          <div class="relative">
            <User class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
            <input
              v-model="form.name"
              type="text"
              placeholder="student name"
              :class="[
                'w-full pl-10 pr-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-900 dark:text-white transition-all',
                form.errors.name ? 'border-red-500 dark:border-red-500' : 'border-slate-200 dark:border-slate-700'
              ]"
            />
          </div>
          <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
        </div>

        <!-- Email Field -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email Address</label>
          <div class="relative">
            <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
            <input
              v-model="form.email"
              type="email"
              placeholder="you@example.com"
              :class="[
                'w-full pl-10 pr-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-900 dark:text-white transition-all',
                form.errors.email ? 'border-red-500 dark:border-red-500' : 'border-slate-200 dark:border-slate-700'
              ]"
            />
          </div>
          <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
        </div>

        <!-- Password Field -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Password</label>
          <div class="relative">
            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Create a password"
              :class="[
                'w-full pl-10 pr-12 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-900 dark:text-white transition-all',
                form.errors.password ? 'border-red-500 dark:border-red-500' : 'border-slate-200 dark:border-slate-700'
              ]"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2"
            >
              <EyeOff v-if="showPassword" class="w-5 h-5 text-slate-400" />
              <Eye v-else class="w-5 h-5 text-slate-400" />
            </button>
          </div>
          <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</p>
          <p class="text-xs text-slate-400 mt-1">Password must be at least 8 characters</p>
        </div>

        <!-- Confirm Password Field -->
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Confirm Password</label>
          <div class="relative">
            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
            <input
              v-model="form.password_confirmation"
              :type="showPasswordConfirmation ? 'text' : 'password'"
              placeholder="Confirm your password"
              :class="[
                'w-full pl-10 pr-12 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-900 dark:text-white transition-all',
                form.errors.password_confirmation ? 'border-red-500 dark:border-red-500' : 'border-slate-200 dark:border-slate-700'
              ]"
            />
            <button
              type="button"
              @click="showPasswordConfirmation = !showPasswordConfirmation"
              class="absolute right-3 top-1/2 -translate-y-1/2"
            >
              <EyeOff v-if="showPasswordConfirmation" class="w-5 h-5 text-slate-400" />
              <Eye v-else class="w-5 h-5 text-slate-400" />
            </button>
          </div>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
        >
          <div v-if="form.processing" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          <span>{{ form.processing ? 'Creating account...' : 'Create Free Account' }}</span>
        </button>

        <p class="text-center text-sm text-slate-500">
          Already have an account?
          <a :href="route('login')" class="text-blue-600 hover:text-blue-700 font-medium">Sign in</a>
        </p>

        <p class="text-xs text-center text-slate-400">
          By signing up, you agree to our Terms of Service and Privacy Policy
        </p>
      </form>
    </div>
  </div>
</template>

<style scoped>
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
.animate-spin {
  animation: spin 0.8s linear infinite;
}
</style>