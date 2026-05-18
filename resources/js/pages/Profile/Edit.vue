<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { User, Mail, Phone, Camera, AtSign, Trash2, Save, ArrowLeft, CheckCircle, AlertCircle, Award } from 'lucide-vue-next'

const props = defineProps({
    user: Object
})

// Initialize form with user data - keep original values
const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    username: props.user?.username || '',
    phone: props.user?.phone || '',
    bio: props.user?.bio || '',
    interests: props.user?.interests || '',
    education: props.user?.education || '',
    profile_picture: null,
})

const profilePreview = ref(props.user?.profile_picture_url || null)
const showSuccess = ref(false)
const showError = ref(false)
const errorMessage = ref('')
const isSaving = ref(false)

const interestOptions = [
    'Web Development', 'Mobile Development', 'UI/UX Design', 'Data Science',
    'Machine Learning', 'Artificial Intelligence', 'Cloud Computing', 'DevOps',
    'Cybersecurity', 'Game Development', 'Digital Marketing', 'Business',
    'Photography', 'Music Production', 'Digital Art', 'Animation'
]

const educationLevels = [
    'High School', 'Bachelor\'s Degree', 'Master\'s Degree', 'PhD',
    'Diploma', 'Certificate', 'Self-taught', 'Bootcamp Graduate'
]

const onFileChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
        if (!allowedTypes.includes(file.type)) {
            errorMessage.value = 'Please upload a valid image file'
            showError.value = true
            setTimeout(() => showError.value = false, 3000)
            return
        }
        
        if (file.size > 5 * 1024 * 1024) {
            errorMessage.value = 'Image size should be less than 5MB'
            showError.value = true
            setTimeout(() => showError.value = false, 3000)
            return
        }
        
        form.profile_picture = file
        const reader = new FileReader()
        reader.onload = (e) => {
            profilePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const removeProfilePicture = () => {
    profilePreview.value = null
    form.profile_picture = null
}

const submit = () => {
    isSaving.value = true
    
    form.put('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess.value = true
            isSaving.value = false
            setTimeout(() => {
                showSuccess.value = false
                router.get(`/profile/${props.user.id}`)
            }, 1500)
        },
        onError: (errors) => {
            // Show specific error messages
            if (errors.username) {
                errorMessage.value = 'Username already taken. Please choose another.'
            } else if (errors.email) {
                errorMessage.value = 'Email already taken. Please use another email.'
            } else {
                errorMessage.value = Object.values(errors).join(', ')
            }
            showError.value = true
            isSaving.value = false
            setTimeout(() => showError.value = false, 3000)
        }
    })
}

const goBack = () => {
    router.get(`/profile/${props.user.id}`)
}

const userInitials = computed(() => {
    const name = form.name || props.user?.name || 'Student'
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

onMounted(() => {
    const savedTheme = localStorage.getItem('theme')
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark')
    }
})
</script>

<template>
    <Head title="Profile Settings | LearnHub" />
    
    <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white py-8">
            <div class="max-w-4xl mx-auto px-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-black">Edit Profile</h1>
                        <p class="text-sm opacity-90 mt-1">Update your personal information</p>
                    </div>
                    <button @click="goBack" class="px-4 py-2 bg-white/20 rounded-xl hover:bg-white/30 transition-colors text-sm font-medium flex items-center gap-2">
                        <ArrowLeft class="w-4 h-4" />
                        Back to Dashboard
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Alert -->
        <div v-if="showSuccess" class="fixed top-24 right-4 z-50 animate-slide-down">
            <div class="flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 rounded-xl p-4 shadow-lg">
                <CheckCircle class="w-5 h-5 text-emerald-600" />
                <div>
                    <p class="font-medium text-emerald-800 dark:text-emerald-300">Profile Updated Successfully!</p>
                    <p class="text-sm text-emerald-600 dark:text-emerald-400">Redirecting to dashboard...</p>
                </div>
            </div>
        </div>

        <!-- Error Alert -->
        <div v-if="showError" class="fixed top-24 right-4 z-50 animate-slide-down">
            <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/30 border border-red-200 rounded-xl p-4 shadow-lg">
                <AlertCircle class="w-5 h-5 text-red-600" />
                <div>
                    <p class="font-medium text-red-800 dark:text-red-300">Update Failed</p>
                    <p class="text-sm text-red-600 dark:text-red-400">{{ errorMessage }}</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 py-8">
            <!-- Profile Header with Initials -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border p-6 mb-6 text-center">
                <div class="relative inline-block">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center mx-auto shadow-lg">
                        <span class="text-3xl font-bold text-white">{{ userInitials }}</span>
                    </div>
                    <label class="absolute bottom-0 right-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-700 transition-colors shadow-lg">
                        <Camera class="w-4 h-4 text-white" />
                        <input type="file" accept="image/*" class="hidden" @change="onFileChange" />
                    </label>
                </div>
                <h2 class="text-xl font-bold dark:text-white mt-3">{{ form.name }}</h2>
                <p class="text-sm text-slate-500">{{ form.email }}</p>
            </div>

            <!-- Profile Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border p-6">
                    <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <User class="w-5 h-5 text-blue-600" />
                        Personal Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium mb-1">Full Name</label>
                            <input type="text" v-model="form.name" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Username</label>
                            <input type="text" v-model="form.username" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl focus:ring-2 focus:ring-blue-500" />
                            <p class="text-xs text-slate-400 mt-1">Choose a unique username</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Email Address</label>
                            <input type="email" v-model="form.email" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Phone Number</label>
                            <input type="tel" v-model="form.phone" placeholder="09XXXXXXXX" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Bio / About Me</label>
                            <textarea v-model="form.bio" rows="3" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Tell us about yourself..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl border p-6">
                    <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <Award class="w-5 h-5 text-blue-600" />
                        Educational Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium mb-1">Education Level</label>
                            <select v-model="form.education" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl focus:ring-2 focus:ring-blue-500">
                                <option value="">Select education level</option>
                                <option v-for="level in educationLevels" :key="level" :value="level">{{ level }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Area of Interest</label>
                            <select v-model="form.interests" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl focus:ring-2 focus:ring-blue-500">
                                <option value="">Select your interest</option>
                                <option v-for="interest in interestOptions" :key="interest" :value="interest">{{ interest }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit" :disabled="isSaving" class="flex-1 md:flex-none px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                        <Save v-if="!isSaving" class="w-4 h-4" />
                        <div v-if="isSaving" class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                        {{ isSaving ? 'Saving...' : 'Update Profile' }}
                    </button>
                    <button type="button" @click="goBack" class="px-8 py-3 bg-slate-100 dark:bg-slate-800 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition-all">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-down { animation: slideDown 0.3s ease-out; }
</style>