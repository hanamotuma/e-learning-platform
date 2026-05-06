<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Mail, Lock, Eye, EyeOff, AlertCircle } from 'lucide-vue-next'

const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: false
})

const submit = () => {
  form.post(route('login'), {
    onError: (errors) => {
      console.error('Login errors:', errors)
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
        <h2 class="text-2xl font-bold text-white">Welcome Back</h2>
        <p class="text-blue-100 mt-1">Sign in to continue learning</p>
      </div>

      <!-- Display General Errors -->
      <div v-if="form.errors.email && !form.errors.email.includes('email')" class="mx-6 mt-4">
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
          <div class="flex items-center space-x-2">
            <AlertCircle class="w-4 h-4 text-red-500" />
            <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="p-6 space-y-5">
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
                form.errors.email && !form.errors.email.includes('email') ? 'border-red-500 dark:border-red-500' : 'border-slate-200 dark:border-slate-700'
              ]"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Password</label>
          <div class="relative">
            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Enter your password"
              class="w-full pl-10 pr-12 py-3 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-900 dark:text-white"
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
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center space-x-2">
            <input v-model="form.remember" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
            <span class="text-sm text-slate-600 dark:text-slate-400">Remember me</span>
          </label>
          <a :href="route('password.request')" class="text-sm text-blue-600 hover:text-blue-700">Forgot password?</a>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
        >
          <div v-if="form.processing" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          <span>{{ form.processing ? 'Signing in...' : 'Sign In' }}</span>
        </button>

        <p class="text-center text-sm text-slate-500">
          Don't have an account?
          <a :href="route('register')" class="text-blue-600 hover:text-blue-700 font-medium">Sign up</a>
        </p>
      </form>

      <!-- Social Login -->
      <div class="px-6 pb-6">
        <div class="relative mb-4">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-white dark:bg-slate-800 text-slate-500">Or continue with</span>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <button class="flex items-center justify-center space-x-2 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">
            <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            <span class="text-sm">Google</span>
          </button>
          <button class="flex items-center justify-center space-x-2 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">
            <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/></svg>
            <span class="text-sm">Facebook</span>
          </button>
        </div>
      </div>
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