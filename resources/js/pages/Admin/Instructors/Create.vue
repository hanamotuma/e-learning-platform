<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ChevronLeft, User, Mail, Lock, Briefcase, BookOpen, Save, AlertCircle, Eye, EyeOff, UserPlus } from 'lucide-vue-next'

const form = useForm({
    full_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    bio: '',
    expertise: ''
})

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const errorMessage = ref('')
const showError = ref(false)
const showSuccess = ref(false)

const submit = () => {
    form.post('/admin/instructors', {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess.value = true
            setTimeout(() => {
                showSuccess.value = false
                router.get('/admin/instructors')
            }, 1500)
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors).join(', ')
            showError.value = true
            setTimeout(() => showError.value = false, 3000)
        }
    })
}

const goBack = () => {
    router.get('/admin/instructors')
}

const generatePassword = () => {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*'
    let password = ''
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length))
    }
    form.password = password
    form.password_confirmation = password
}
</script>

<template>
    <Head title="Add Instructor | Admin" />
    
    <AdminLayout>
        <div class="p-6">
            <!-- Success Alert -->
            <div v-if="showSuccess" class="fixed top-24 right-4 z-50 bg-green-50 border border-green-200 rounded-xl p-4 shadow-lg animate-slide-down">
                <div class="flex items-center gap-3">
                    <CheckCircle class="w-5 h-5 text-green-600" />
                    <div>
                        <p class="font-medium text-green-800">Success!</p>
                        <p class="text-sm text-green-600">Instructor created successfully. Redirecting...</p>
                    </div>
                </div>
            </div>

            <!-- Error Alert -->
            <div v-if="showError" class="fixed top-24 right-4 z-50 bg-red-50 border border-red-200 rounded-xl p-4 shadow-lg animate-slide-down">
                <div class="flex items-center gap-3">
                    <AlertCircle class="w-5 h-5 text-red-600" />
                    <div>
                        <p class="font-medium text-red-800">Error</p>
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <button @click="goBack" class="flex items-center gap-2 text-slate-600 dark:text-slate-400 mb-6 hover:text-blue-600 transition-colors">
                <ChevronLeft class="w-5 h-5" />
                Back to Instructors
            </button>

            <!-- Main Form Container -->
            <div class="max-w-3xl mx-auto">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8">
                        <div class="flex items-center gap-3">
                            <UserPlus class="w-8 h-8 text-white" />
                            <div>
                                <h1 class="text-2xl font-bold text-white">Add New Instructor</h1>
                                <p class="text-blue-100 mt-1">Create instructor account and assign permissions</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Personal Information Section -->
                        <div>
                            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <User class="w-5 h-5 text-blue-600" />
                                Personal Information
                            </h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-white">Full Name *</label>
                                    <input 
                                        type="text" 
                                        v-model="form.full_name" 
                                        class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-all"
                                        placeholder="e.g., John Doe"
                                        required
                                    />
                                    <p v-if="form.errors.full_name" class="text-red-500 text-sm mt-1">{{ form.errors.full_name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-white">Email Address *</label>
                                    <div class="relative">
                                        <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                                        <input 
                                            type="email" 
                                            v-model="form.email" 
                                            class="w-full pl-10 pr-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-all"
                                            placeholder="instructor@example.com"
                                            required
                                        />
                                    </div>
                                    <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Account Security Section -->
                        <div class="border-t pt-6 dark:border-slate-700">
                            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <Lock class="w-5 h-5 text-blue-600" />
                                Account Security
                            </h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="block text-sm font-medium dark:text-white">Password *</label>
                                        <button type="button" @click="generatePassword" class="text-xs text-blue-600 hover:text-blue-700">Generate Strong Password</button>
                                    </div>
                                    <div class="relative">
                                        <input 
                                            :type="showPassword ? 'text' : 'password'"
                                            v-model="form.password" 
                                            class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-all pr-10"
                                            placeholder="Minimum 8 characters"
                                            required
                                        />
                                        <button 
                                            type="button"
                                            @click="showPassword = !showPassword"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                        >
                                            <Eye v-if="!showPassword" class="w-5 h-5" />
                                            <EyeOff v-else class="w-5 h-5" />
                                        </button>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">Password must be at least 8 characters</p>
                                    <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-white">Confirm Password *</label>
                                    <div class="relative">
                                        <input 
                                            :type="showPasswordConfirmation ? 'text' : 'password'"
                                            v-model="form.password_confirmation" 
                                            class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-all pr-10"
                                            placeholder="Re-enter password"
                                            required
                                        />
                                        <button 
                                            type="button"
                                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                        >
                                            <Eye v-if="!showPasswordConfirmation" class="w-5 h-5" />
                                            <EyeOff v-else class="w-5 h-5" />
                                        </button>
                                    </div>
                                    <p v-if="form.errors.password_confirmation" class="text-red-500 text-sm mt-1">{{ form.errors.password_confirmation }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information Section -->
                        <div class="border-t pt-6 dark:border-slate-700">
                            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <Briefcase class="w-5 h-5 text-blue-600" />
                                Professional Information
                            </h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-white">Bio / About</label>
                                    <textarea 
                                        v-model="form.bio" 
                                        rows="4" 
                                        class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white resize-none transition-all"
                                        placeholder="Tell about the instructor's experience, teaching philosophy, and background..."
                                    ></textarea>
                                    <p class="text-xs text-slate-400 mt-1">Maximum 500 characters</p>
                                    <p v-if="form.errors.bio" class="text-red-500 text-sm mt-1">{{ form.errors.bio }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-white">Expertise / Skills</label>
                                    <div class="relative">
                                        <BookOpen class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                                        <input 
                                            type="text"
                                            v-model="form.expertise" 
                                            class="w-full pl-10 pr-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900 dark:border-slate-700 dark:text-white transition-all"
                                            placeholder="e.g., Web Development, Python, UI/UX Design, React, Node.js"
                                        />
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">Separate multiple skills with commas</p>
                                    <p v-if="form.errors.expertise" class="text-red-500 text-sm mt-1">{{ form.errors.expertise }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="border-t pt-6 flex gap-4 dark:border-slate-700">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="flex-1 px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2 shadow-lg shadow-blue-500/20"
                            >
                                <Save class="w-4 h-4" />
                                {{ form.processing ? 'Creating Instructor...' : 'Create Instructor' }}
                            </button>
                            <button 
                                type="button" 
                                @click="goBack"
                                class="px-6 py-3 border rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all font-medium dark:text-white"
                            >
                                Cancel
                            </button>
                        </div>

                        <!-- Info Note -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
                            <p class="text-sm text-blue-600 dark:text-blue-400 flex items-center gap-2">
                                <AlertCircle class="w-4 h-4" />
                                The instructor will receive a welcome email with their login credentials.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-slide-down {
    animation: slideDown 0.3s ease-out;
}
</style>