<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import InstructorLayout from '@/layouts/InstructorLayout.vue'
import { User, Mail, Phone, Camera, Save, DollarSign, Bell, Lock, Eye, EyeOff } from 'lucide-vue-next'

const props = defineProps({
    user: Object
})

const showSuccess = ref(false)
const showError = ref(false)
const errorMessage = ref('')
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)

const profileForm = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    phone: props.user?.phone || '',
    bio: props.user?.bio || '',
    profile_picture: null
})

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: ''
})

const updateProfile = () => {
    profileForm.patch('/instructor/profile', {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess.value = true
            setTimeout(() => showSuccess.value = false, 2000)
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors).join(', ')
            showError.value = true
            setTimeout(() => showError.value = false, 3000)
        }
    })
}

const updatePassword = () => {
    passwordForm.put('/instructor/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset()
            showSuccess.value = true
            setTimeout(() => showSuccess.value = false, 2000)
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors).join(', ')
            showError.value = true
            setTimeout(() => showError.value = false, 3000)
        }
    })
}
</script>

<template>
    <Head title="Settings | Instructor" />
    
    <InstructorLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold dark:text-white">Settings</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Manage your account settings</p>
            </div>

            <!-- Success Message -->
            <div v-if="showSuccess" class="fixed top-24 right-4 z-50 bg-green-50 border border-green-200 rounded-xl p-4 shadow-lg animate-slide-down">
                <p class="text-green-600">Settings updated successfully!</p>
            </div>

            <div v-if="showError" class="fixed top-24 right-4 z-50 bg-red-50 border border-red-200 rounded-xl p-4 shadow-lg animate-slide-down">
                <p class="text-red-600">{{ errorMessage }}</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Profile Settings -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <User class="w-5 h-5 text-blue-600" />
                        Profile Settings
                    </h2>
                    
                    <form @submit.prevent="updateProfile" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Full Name</label>
                            <input v-model="profileForm.name" type="text" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Email Address</label>
                            <input v-model="profileForm.email" type="email" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900 bg-slate-50" readonly />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Phone Number</label>
                            <input v-model="profileForm.phone" type="tel" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" placeholder="09XXXXXXXX" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Bio</label>
                            <textarea v-model="profileForm.bio" rows="3" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" placeholder="Tell about yourself..."></textarea>
                        </div>
                        <button type="submit" :disabled="profileForm.processing" class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ profileForm.processing ? 'Saving...' : 'Save Profile' }}
                        </button>
                    </form>
                </div>

                <!-- Password Settings -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <Lock class="w-5 h-5 text-blue-600" />
                        Password Settings
                    </h2>
                    
                    <form @submit.prevent="updatePassword" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Current Password</label>
                            <div class="relative">
                                <input :type="showCurrentPassword ? 'text' : 'password'" v-model="passwordForm.current_password" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                                <button type="button" @click="showCurrentPassword = !showCurrentPassword" class="absolute right-3 top-2">
                                    <EyeOff v-if="showCurrentPassword" class="w-5 h-5" />
                                    <Eye v-else class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">New Password</label>
                            <div class="relative">
                                <input :type="showNewPassword ? 'text' : 'password'" v-model="passwordForm.password" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                                <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-3 top-2">
                                    <EyeOff v-if="showNewPassword" class="w-5 h-5" />
                                    <Eye v-else class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Confirm Password</label>
                            <input type="password" v-model="passwordForm.password_confirmation" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                        </div>
                        <button type="submit" :disabled="passwordForm.processing" class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>

<style scoped>
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-down {
    animation: slideDown 0.3s ease-out;
}
</style>