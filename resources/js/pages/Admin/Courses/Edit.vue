<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ChevronLeft, Upload, Save, X, Trash2 } from 'lucide-vue-next'

const props = defineProps({
  course: Object,
  categories: Array
})

const form = useForm({
  title: props.course.title,
  description: props.course.description,
  what_you_will_learn: props.course.what_you_will_learn,
  requirements: props.course.requirements,
  price: props.course.price,
  category_id: props.course.category_id,
  difficulty_level: props.course.difficulty_level,
  image: null,
})

const imagePreview = ref(props.course.image || null)
const uploading = ref(false)

const onImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']
    if (!allowedTypes.includes(file.type)) {
      alert('Please upload JPG, PNG, or WEBP image')
      return
    }
    
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('Image size must be less than 5MB')
      return
    }
    
    form.image = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const removeImage = () => {
  imagePreview.value = null
  form.image = null
  const fileInput = document.getElementById('image_input')
  if (fileInput) fileInput.value = ''
}

const submit = () => {
  uploading.value = true
  
  form.post(`/admin/courses/${props.course.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      uploading.value = false
      router.get('/admin/courses')
    },
    onError: (errors) => {
      uploading.value = false
      alert('Error: ' + Object.values(errors).join(', '))
    }
  })
}

const goBack = () => {
  router.get('/admin/courses')
}
</script>

<template>
  <Head title="Edit Course | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <button @click="goBack" class="flex items-center gap-2 text-slate-600 mb-6 hover:text-blue-600">
        <ChevronLeft class="w-5 h-5" />
        Back to Courses
      </button>
      
      <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-6">
            <h1 class="text-2xl font-bold text-white">Edit Course</h1>
            <p class="text-blue-100 mt-1">Update course information and thumbnail</p>
          </div>

          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- Basic Information -->
            <div>
              <h2 class="text-lg font-bold mb-4">Basic Information</h2>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Course Title</label>
                  <input v-model="form.title" type="text" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" required />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Description</label>
                  <textarea v-model="form.description" rows="4" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" required></textarea>
                </div>
              </div>
            </div>

            <!-- Pricing & Category -->
            <div class="border-t pt-6">
              <h2 class="text-lg font-bold mb-4">Pricing & Category</h2>
              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Price (ETB)</label>
                  <input v-model="form.price" type="number" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Category</label>
                  <select v-model="form.category_id" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900">
                    <option value="">Select Category</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Difficulty Level</label>
                  <select v-model="form.difficulty_level" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900">
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Learning Outcomes -->
            <div class="border-t pt-6">
              <h2 class="text-lg font-bold mb-4">Learning Outcomes</h2>
              <div>
                <label class="block text-sm font-medium mb-1">What Students Will Learn</label>
                <textarea v-model="form.what_you_will_learn" rows="3" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" placeholder="One per line"></textarea>
              </div>
              <div class="mt-4">
                <label class="block text-sm font-medium mb-1">Requirements / Prerequisites</label>
                <textarea v-model="form.requirements" rows="2" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" placeholder="One per line"></textarea>
              </div>
            </div>

            <!-- Course Thumbnail - FIXED IMAGE UPLOAD -->
            <div class="border-t pt-6">
              <h2 class="text-lg font-bold mb-4">Course Thumbnail</h2>
              <div class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer hover:border-blue-500 transition-all" @click="$refs.imageInput.click()">
                <div v-if="imagePreview" class="relative inline-block">
                  <img :src="imagePreview" class="h-48 rounded-lg object-cover shadow-md" />
                  <button 
                    type="button" 
                    @click.stop="removeImage" 
                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600"
                  >
                    <X class="w-4 h-4" />
                  </button>
                </div>
                <div v-else>
                  <Upload class="w-12 h-12 mx-auto text-slate-400 mb-3" />
                  <p class="text-sm text-slate-500">Click to upload course thumbnail</p>
                  <p class="text-xs text-slate-400 mt-1">Recommended: 1280x720px, JPG or PNG (Max 5MB)</p>
                </div>
              </div>
              <input 
                type="file" 
                ref="imageInput" 
                @change="onImageChange" 
                accept="image/jpeg,image/png,image/jpg,image/webp" 
                class="hidden" 
                id="image_input"
              />
              <p v-if="form.errors.image" class="text-red-500 text-sm mt-2">{{ form.errors.image }}</p>
            </div>

            <!-- Submit Buttons -->
            <div class="border-t pt-6 flex gap-4">
              <button 
                type="submit" 
                :disabled="form.processing || uploading"
                class="flex-1 px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
              >
                <Save class="w-4 h-4" />
                {{ uploading ? 'Uploading...' : (form.processing ? 'Saving...' : 'Save Changes') }}
              </button>
              <button 
                type="button" 
                @click="goBack"
                class="px-6 py-3 border rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all font-medium"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>