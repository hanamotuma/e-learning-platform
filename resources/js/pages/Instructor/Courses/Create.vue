<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { 
  BookOpen, Upload, X, Save, ArrowLeft, ImageIcon, PlusCircle, Loader2
} from 'lucide-vue-next'

const props = defineProps({
    categories: { type: Array, default: () => [] }
})

// Form handling
const form = useForm({
    title: '',
    name:'',
    slug: '',
    description: '',
    category_id: '',
    price: '',
    thumbnail: null,
    status: 'draft',
    level: 'beginner',
    requirements: '',
    what_you_ll_learn: ''
})

// State
const thumbnailPreview = ref(null)
const isSubmitting = ref(false)
const showNewCategoryModal = ref(false)
const newCategoryName = ref('')
const isCreatingCategory = ref(false)
const localCategories = ref([...(props.categories || [])])
const submit =() =>{
    form.post('/categories')
}

// Generate slug
const generateSlug = () => {
    if (form.title) {
        form.slug = form.title
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '')
    }
}

// Handle thumbnail
const onThumbnailChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(file.type)) {
            alert('Please upload a valid image (JPEG, PNG, or WEBP)')
            return
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size should be less than 5MB')
            return
        }
        form.thumbnail = file
        const reader = new FileReader()
        reader.onload = (e) => {
            thumbnailPreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const removeThumbnail = () => {
    thumbnailPreview.value = null
    form.thumbnail = null
}

// Create new category
const createNewCategory = async () => {
    if (!newCategoryName.value.trim()) {
        alert('Please enter a category name')
        return
    }
    
    isCreatingCategory.value = true
     const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    try {
          const response = await fetch('/instructor/categories', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: newCategoryName.value })
        })
        
        const data = await response.json()
        
        if (response.ok && data.category) {
            // Add to local categories
            localCategories.value.push(data.category)
            // Auto-select the new category
            form.category_id = data.category.id
            // Close modal
            showNewCategoryModal.value = false
            newCategoryName.value = ''
            // Show success
            alert(`Category "${data.category.name}" created successfully!`)
        } else {
            alert(data.message || 'Failed to create category')
        }
    } catch (error) {
        console.error('Error creating category:', error)
        alert('Network error. Please try again.')
    } finally {
        isCreatingCategory.value = false
    }
}

// Submit course
const submitCourse = () => {
    if (!form.category_id) {
        alert('Please select a category')
        return
    }
    
    form.post(route('instructor.courses.store'), {
        preserveScroll: true,
        onStart: () => {
            isSubmitting.value = true
        },
        onSuccess: () => {
            router.visit(route('instructor.courses.index'))
        },
        onError: (errors) => {
            console.error('Form errors:', errors)
            isSubmitting.value = false
            let errorMsg = Object.values(errors).join('\n')
            alert('Error: ' + errorMsg)
        },
        onFinish: () => {
            isSubmitting.value = false
        }
    })
}

const cancel = () => {
    if (confirm('Unsaved changes will be lost. Are you sure?')) {
        router.visit(route('instructor.dashboard'))
    }
}

// Fetch categories on mount if needed
onMounted(async () => {
    if (localCategories.value.length === 0) {
        try {
            const response = await fetch('/api/categories')
            const data = await response.json()
            if (data.categories) {
                localCategories.value = data.categories
            }
        } catch (error) {
            console.error('Error fetching categories:', error)
        }
    }
})

const levelOptions = [
    { value: 'beginner', label: 'Beginner' },
    { value: 'intermediate', label: 'Intermediate' },
    { value: 'advanced', label: 'Advanced' },
    { value: 'all_levels', label: 'All Levels' }
]
</script>

<template>
    <Head title="Create Course | Instructor Portal" />
    
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Header -->
            <div class="flex items-center space-x-4 mb-6">
                <Link :href="route('instructor.dashboard')" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl">
                    <ArrowLeft class="w-5 h-5 text-slate-600" />
                </Link>
                <div>
                    <h1 class="text-2xl font-black dark:text-white">Create New Course</h1>
                    <p class="text-sm text-slate-500">Share your knowledge with the world</p>
                </div>
            </div>
            
            <form @submit.prevent="submitCourse" class="space-y-6">
                <!-- Basic Info -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border p-6">
                    <h2 class="text-lg font-bold dark:text-white mb-4 flex items-center space-x-2">
                        <BookOpen class="w-5 h-5 text-blue-600" />
                        <span>Basic Information</span>
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Course Title *</label>
                            <input 
                                type="text" 
                                v-model="form.title" 
                                @input="generateSlug"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., Mastering Laravel 11"
                                required 
                            />
                            <p v-if="form.errors.title" class="text-xs text-red-500 mt-1">{{ form.errors.title }}</p>
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Description *</label>
                            <textarea 
                                v-model="form.description" 
                                rows="5"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl resize-none"
                                placeholder="What will students learn?"
                                required
                            ></textarea>
                            <p v-if="form.errors.description" class="text-xs text-red-500 mt-1">{{ form.errors.description }}</p>
                        </div>
                        
                        <!-- Category with Add Button -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Category *</label>
                            <div class="flex gap-2">
                                <select 
                                    v-model="form.category_id" 
                                    class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl focus:ring-2 focus:ring-blue-500"
                                    required
                                >
                                    <option value="">Select category</option>
                                    <option v-for="cat in localCategories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <button 
                                    type="button"
                                    @click="showNewCategoryModal = true"
                                    class="px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center gap-2 transition-colors"
                                >
                                    <PlusCircle class="w-4 h-4" />
                                    New
                                </button>
                            </div>
                            <p v-if="form.errors.category_id" class="text-xs text-red-500 mt-1">{{ form.errors.category_id }}</p>
                            <p v-if="localCategories.length === 0" class="text-xs text-yellow-500 mt-1">
                                No categories found. Click "New" to create one.
                            </p>
                        </div>
                        
                        <!-- Price and Level -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Price (ETB) *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-slate-500 font-medium">ብር</span>
                                    <input 
                                        type="number" 
                                        v-model="form.price" 
                                        step="1" 
                                        min="0"
                                        class="w-full pl-12 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl"
                                        placeholder="3500"
                                        required 
                                    />
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Set to 0 for free courses</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Level</label>
                                <select 
                                    v-model="form.level" 
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl"
                                >
                                    <option v-for="level in levelOptions" :key="level.value" :value="level.value">
                                        {{ level.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <select 
                                v-model="form.status" 
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl"
                            >
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                            <p class="text-xs text-slate-500 mt-1">Draft courses are only visible to you</p>
                        </div>
                    </div>
                </div>
                
                <!-- Thumbnail -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border p-6">
                    <h2 class="text-lg font-bold dark:text-white mb-4">Course Thumbnail</h2>
                    
                    <div class="flex flex-col items-center">
                        <div v-if="thumbnailPreview" class="relative mb-4">
                            <img :src="thumbnailPreview" class="w-full max-w-md rounded-2xl border" />
                            <button 
                                @click="removeThumbnail" 
                                class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                        <label v-else class="flex flex-col items-center justify-center w-full max-w-md h-48 border-2 border-dashed rounded-2xl cursor-pointer hover:bg-slate-50 transition-colors">
                            <Upload class="w-10 h-10 text-slate-400 mb-2" />
                            <p class="text-sm text-slate-500">Click to upload thumbnail</p>
                            <p class="text-xs text-slate-400">JPEG, PNG, WEBP (Max 5MB)</p>
                            <input type="file" class="hidden" accept="image/*" @change="onThumbnailChange" />
                        </label>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="flex justify-end gap-3">
                    <button 
                        type="button" 
                        @click="cancel" 
                        class="px-6 py-2.5 border rounded-xl hover:bg-slate-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        :disabled="isSubmitting"
                        class="px-8 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2"
                    >
                        <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                        <Save v-else class="w-4 h-4" />
                        <span>{{ isSubmitting ? 'Creating...' : 'Create Course' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- New Category Modal -->
    <div v-if="showNewCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 w-96 max-w-full mx-4 shadow-2xl">
            <h3 class="text-xl font-bold mb-4 dark:text-white">Create New Category</h3>
            
            <input 
                type="text" 
                v-model="newCategoryName" 
                placeholder="e.g., Web Development"
                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border rounded-xl mb-4 focus:ring-2 focus:ring-blue-500"
                @keyup.enter="createNewCategory"
                autofocus
            />
            
            <div class="flex justify-end gap-3">
                <button 
                    @click="showNewCategoryModal = false" 
                    class="px-4 py-2 border rounded-xl hover:bg-slate-50 transition-colors"
                >
                    Cancel
                </button>
                <button 
                    @click="createNewCategory" 
                    :disabled="isCreatingCategory"
                    class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2"
                >
                    <Loader2 v-if="isCreatingCategory" class="w-4 h-4 animate-spin" />
                    <span>{{ isCreatingCategory ? 'Creating...' : 'Create' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>