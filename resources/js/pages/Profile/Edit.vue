<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { 
  User, Mail, Phone, Camera, Trash2, Save, X, 
  CheckCircle, AlertCircle, GraduationCap, Heart, ArrowLeft
} from 'lucide-vue-next'

const props = defineProps({
    auth: Object,
    user: Object
})

// Initialize form with user data - removed address fields
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

// Profile picture preview
const profilePreview = ref(props.user?.profile_picture_url || null)
const showSuccess = ref(false)
const showError = ref(false)
const errorMessage = ref('')

// Options
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

// Handle profile picture change
const onFileChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
        if (!allowedTypes.includes(file.type)) {
            errorMessage.value = 'Please upload a valid image file (JPEG, PNG, GIF, or WEBP)'
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

// Remove profile picture
const removeProfilePicture = () => {
    profilePreview.value = null
    form.profile_picture = null
}

// Submit form
const submit = () => {
    form.put('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess.value = true
            setTimeout(() => showSuccess.value = false, 3000)
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors).join(', ')
            showError.value = true
            setTimeout(() => showError.value = false, 3000)
        }
    })
}

// Go back
const goBack = () => {
    router.get(route('student.dashboard'))
}

// User initials
const userInitials = computed(() => {
    const name = form.name || props.user?.name || 'Student'
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})
</script>

<template>
    <Head title="Student Profile | LearnHub" />
    
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white">
            <div class="max-w-4xl mx-auto px-4 py-8 lg:py-12">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-black">Student Profile</h1>
                        <p class="text-sm opacity-90 mt-1">Manage your personal information and learning preferences</p>
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
            <div class="flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl p-4 shadow-lg">
                <CheckCircle class="w-5 h-5 text-emerald-600" />
                <div>
                    <p class="font-medium text-emerald-800 dark:text-emerald-300">Profile Updated!</p>
                    <p class="text-sm text-emerald-600 dark:text-emerald-400">Your information has been saved successfully.</p>
                </div>
                <button @click="showSuccess = false" class="ml-2 text-emerald-600 hover:text-emerald-700">
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Error Alert -->
        <div v-if="showError" class="fixed top-24 right-4 z-50 animate-slide-down">
            <div class="flex items-center gap-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl p-4 shadow-lg">
                <AlertCircle class="w-5 h-5 text-red-600" />
                <div>
                    <p class="font-medium text-red-800 dark:text-red-300">Update Failed</p>
                    <p class="text-sm text-red-600 dark:text-red-400">{{ errorMessage }}</p>
                </div>
                <button @click="showError = false" class="ml-2 text-red-600 hover:text-red-700">
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 py-8">
            
            <!-- Profile Picture Section -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
                <h2 class="text-lg font-bold dark:text-white mb-4 flex items-center gap-2">
                    <Camera class="w-5 h-5 text-blue-600" />
                    Profile Picture
                </h2>
                
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <div class="relative">
                        <div v-if="profilePreview" class="w-28 h-28 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 ring-4 ring-blue-100 dark:ring-blue-900/30">
                            <img :src="profilePreview" class="w-full h-full object-cover" />
                        </div>
                        <div v-else class="w-28 h-28 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center ring-4 ring-blue-100 dark:ring-blue-900/30">
                            <span class="text-4xl font-bold text-white">{{ userInitials }}</span>
                        </div>
                        <label class="absolute -bottom-2 -right-2 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-700 transition-colors shadow-lg">
                            <Camera class="w-4 h-4 text-white" />
                            <input type="file" accept="image/*" class="hidden" @change="onFileChange" />
                        </label>
                        <button 
                            v-if="profilePreview"
                            @click="removeProfilePicture"
                            class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg"
                        >
                            <Trash2 class="w-4 h-4 text-white" />
                        </button>
                    </div>
                    
                    <div class="flex-1 text-center md:text-left">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Upload a new profile photo</p>
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG, GIF or WEBP. Max 5MB.</p>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Personal Information -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h2 class="text-lg font-bold dark:text-white mb-4 flex items-center gap-2">
                        <User class="w-5 h-5 text-blue-600" />
                        Personal Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                v-model="form.name"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white transition-all"
                                placeholder="Enter your full name"
                                required
                            />
                            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                v-model="form.username"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white transition-all"
                                placeholder="Enter username"
                                required
                            />
                            <p v-if="form.errors.username" class="text-xs text-red-500 mt-1">{{ form.errors.username }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                v-model="form.email"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white transition-all"
                                placeholder="Enter your email"
                                required
                            />
                            <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Phone Number
                            </label>
                            <input 
                                type="tel" 
                                v-model="form.phone"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white transition-all"
                                placeholder="Enter phone number"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Bio / About Me
                            </label>
                            <textarea 
                                v-model="form.bio"
                                rows="3"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white transition-all resize-none"
                                placeholder="Tell us a little about yourself, your learning goals, and what brings you to LearnHub..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Educational Information -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h2 class="text-lg font-bold dark:text-white mb-4 flex items-center gap-2">
                        <GraduationCap class="w-5 h-5 text-blue-600" />
                        Educational Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Education Level
                            </label>
                            <select 
                                v-model="form.education"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white transition-all cursor-pointer"
                            >
                                <option value="">Select education level</option>
                                <option v-for="level in educationLevels" :key="level" :value="level">
                                    {{ level }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Area of Interest
                            </label>
                            <select 
                                v-model="form.interests"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white transition-all cursor-pointer"
                            >
                                <option value="">Select your interest</option>
                                <option v-for="interest in interestOptions" :key="interest" :value="interest">
                                    {{ interest }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Learning Preferences -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h2 class="text-lg font-bold dark:text-white mb-4 flex items-center gap-2">
                        <Heart class="w-5 h-5 text-blue-600" />
                        Learning Preferences
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Weekly Learning Goal
                            </label>
                            <select class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white transition-all cursor-pointer">
                                <option>5-10 hours/week</option>
                                <option>10-15 hours/week</option>
                                <option>15-20 hours/week</option>
                                <option>20+ hours/week</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Preferred Learning Style
                            </label>
                            <select class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white transition-all cursor-pointer">
                                <option>Video Lectures</option>
                                <option>Hands-on Projects</option>
                                <option>Reading & Articles</option>
                                <option>Interactive Quizzes</option>
                                <option>Live Sessions</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-4">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="flex-1 md:flex-none px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        <Save v-if="!form.processing" class="w-4 h-4" />
                        <div v-if="form.processing" class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                        Update Profile
                    </button>
                    <button 
                        type="button" 
                        @click="goBack"
                        class="px-8 py-3 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
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