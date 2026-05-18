<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

// Auto-generate username from name
watch(() => form.name, (newName) => {
    if (newName && !form.username) {
        const generatedUsername = newName.toLowerCase().replace(/[^a-z0-9]/g, '');
        form.username = generatedUsername;
    }
});

const submit = () => {
    form.post(route('register'), {
        onSuccess: () => {
            // Clear any pending checkout data
            sessionStorage.removeItem('redirect_after_checkout');
            sessionStorage.removeItem('redirect_after_login');
        },
        onError: (errors) => {
            console.error('Registration errors:', errors);
        }
    });
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white dark:from-slate-900 dark:to-slate-800 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-blue-600 px-6 py-8 text-center">
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl font-black text-white">L</span>
                </div>
                <h2 class="text-2xl font-bold text-white">Join LearnHub</h2>
                <p class="text-blue-100 mt-1">Start your learning journey today</p>
            </div>

            <form @submit.prevent="submit" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Username</label>
                    <input 
                        v-model="form.username" 
                        type="text" 
                        placeholder="username" 
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900"
                    />
                    <p v-if="form.errors.username" class="text-red-500 text-sm mt-1">{{ form.errors.username }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Full Name</label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        placeholder="Full name" 
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900"
                    />
                    <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Email Address</label>
                    <input 
                        v-model="form.email" 
                        type="email" 
                        placeholder="you@example.com" 
                        class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900"
                    />
                    <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <div class="relative">
                        <input 
                            v-model="form.password" 
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Create a password" 
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900"
                        />
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2">
                            <span v-if="showPassword">👁️</span>
                            <span v-else>👁️‍🗨️</span>
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Confirm Password</label>
                    <div class="relative">
                        <input 
                            v-model="form.password_confirmation" 
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            placeholder="Confirm your password" 
                            class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900"
                        />
                        <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation" class="absolute right-3 top-1/2 -translate-y-1/2">
                            <span v-if="showPasswordConfirmation">👁️</span>
                            <span v-else>👁️‍🗨️</span>
                        </button>
                    </div>
                </div>

                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50"
                >
                    {{ form.processing ? 'Creating account...' : 'Create Free Account' }}
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