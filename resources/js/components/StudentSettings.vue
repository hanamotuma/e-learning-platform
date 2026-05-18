<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { User, Mail, Phone, Camera, Save, X, CheckCircle, AlertCircle } from 'lucide-vue-next'

const props = defineProps({
  user: Object
})

const showSuccess = ref(false)
const showError = ref(false)
const errorMessage = ref('')
const imagePreview = ref(props.user?.profile_picture_url || null)

const form = useForm({
  name: props.user?.name || '',
  email: props.user?.email || '',
  phone: props.user?.phone || '',
  bio: props.user?.bio || '',
  profile_picture: null
})

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg']
    if (!allowedTypes.includes(file.type)) {
      errorMessage.value = 'Please upload JPG or PNG image'
      showError.value = true
      setTimeout(() => showError.value = false, 3000)
      return
    }
    
    if (file.size > 5 * 1024 * 1024) {
      errorMessage.value = 'Image size must be less than 5MB'
      showError.value = true
      setTimeout(() => showError.value = false, 3000)
      return
    }
    
    form.profile_picture = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const updateProfile = () => {
  form.patch('/profile', {
    preserveScroll: true,
    onSuccess: () => {
      showSuccess.value = true
      setTimeout(() => {
        showSuccess.value = false
        router.reload()
      }, 2000)
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
  <div>
    <!-- Success Alert -->
    <div v-if="showSuccess" class="fixed top-24 right-4 z-50 bg-green-50 border border-green-200 rounded-xl p-4 shadow-lg animate-slide-down">
      <div class="flex items-center gap-3">
        <CheckCircle class="w-5 h-5 text-green-600" />
        <div>
          <p class="font-medium text-green-800">Profile Updated!</p>
          <p class="text-sm text-green-600">Your changes have been saved</p>
        </div>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="showError" class="fixed top-24 right-4 z-50 bg-red-50 border border-red-200 rounded-xl p-4 shadow-lg animate-slide-down">
      <div class="flex items-center gap-3">
        <AlertCircle class="w-5 h-5 text-red-600" />
        <div>
          <p class="font-medium text-red-800">Update Failed</p>
          <p class="text-sm text-red-600">{{ errorMessage }}</p>
        </div>
      </div>
    </div>

    <div class="max-w-3xl mx-auto">
      <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8">
          <h1 class="text-2xl font-bold text-white">Account Settings</h1>
          <p class="text-blue-100 mt-1">Update your profile information</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="updateProfile" class="p-6 space-y-6">
          <!-- Profile Picture -->
          <div class="flex items-center gap-6 pb-6 border-b">
            <div class="relative">
              <div class="w-24 h-24 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white text-3xl font-bold overflow-hidden">
                <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-cover" />
                <span v-else>{{ form.name?.charAt(0) || 'U' }}</span>
              </div>
              <label class="absolute bottom-0 right-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-700 transition-colors shadow-lg">
                <Camera class="w-4 h-4 text-white" />
                <input type="file" accept="image/*" class="hidden" @change="handleImageUpload" />
              </label>
            </div>
            <div>
              <p class="font-medium text-slate-800">Profile Picture</p>
              <p class="text-sm text-slate-500">JPG or PNG, max 5MB</p>
            </div>
          </div>

          <!-- Personal Information -->
          <div>
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
              <User class="w-5 h-5 text-blue-600" />
              Personal Information
            </h2>
            
            <div class="grid md:grid-cols-2 gap-5">
              <div>
                <label class="block text-sm font-medium mb-1">Full Name</label>
                <input 
                  type="text" 
                  v-model="form.name" 
                  class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Email Address</label>
                <input 
                  type="email" 
                  v-model="form.email" 
                  class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 bg-slate-50"
                  readonly
                />
                <p class="text-xs text-slate-400 mt-1">Email cannot be changed</p>
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Phone Number</label>
                <input 
                  type="tel" 
                  v-model="form.phone" 
                  class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500"
                  placeholder="09XXXXXXXX"
                />
              </div>
            </div>
          </div>

          <!-- Bio -->
          <div class="border-t pt-6">
            <h2 class="text-lg font-bold mb-4">About You</h2>
            <div>
              <label class="block text-sm font-medium mb-1">Bio</label>
              <textarea 
                v-model="form.bio" 
                rows="4" 
                class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 resize-none"
                placeholder="Tell us about yourself..."
              ></textarea>
            </div>
          </div>

          <!-- Save Button -->
          <div class="border-t pt-6 flex gap-4">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="flex-1 px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
            >
              <Save class="w-4 h-4" />
              {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </button>
            <button 
              type="button" 
              @click="activeTab = 'dashboard'"
              class="px-6 py-3 border rounded-xl hover:bg-slate-50 transition-all font-medium"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
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