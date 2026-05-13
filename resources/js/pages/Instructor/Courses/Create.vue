<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ChevronLeft, Save, Upload, X, Plus, Trash2 } from 'lucide-vue-next'

const props = defineProps({
    categories: Array
})

const form = useForm({
    title: '',
    description: '',
    what_you_will_learn: '',
    requirements: '',
    price: 0,
    category_id: '',
    difficulty_level: 'beginner',
    image: null,
    video: null,
})

const imagePreview = ref(null)

const onImageChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.image = file
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const submit = () => {
    form.post('/instructor/courses', {
        onSuccess: () => {
            router.get('/instructor/courses')
        }
    })
}

const goBack = () => {
    router.get('/instructor/dashboard')
}
</script>

<template>
    <Head title="Create Course | Instructor" />
    
    <div class="min-h-screen bg-gray-50 dark:bg-slate-950 py-8">
        <div class="max-w-4xl mx-auto px-4">
            <button @click="goBack" class="flex items-center gap-2 text-slate-600 mb-6">
                <ChevronLeft class="w-5 h-5" /> Back to Dashboard
            </button>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border p-6">
                <h1 class="text-2xl font-bold mb-6">Create New Course</h1>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium mb-1">Course Title</label>
                        <input type="text" v-model="form.title" class="w-full px-4 py-2 border rounded-xl" required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea v-model="form.description" rows="4" class="w-full px-4 py-2 border rounded-xl" required></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">What will students learn?</label>
                        <textarea v-model="form.what_you_will_learn" rows="3" class="w-full px-4 py-2 border rounded-xl" placeholder="One per line"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Requirements</label>
                        <textarea v-model="form.requirements" rows="2" class="w-full px-4 py-2 border rounded-xl" placeholder="One per line"></textarea>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Price (ETB)</label>
                            <input type="number" v-model="form.price" class="w-full px-4 py-2 border rounded-xl" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Difficulty Level</label>
                            <select v-model="form.difficulty_level" class="w-full px-4 py-2 border rounded-xl">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Category</label>
                        <select v-model="form.category_id" class="w-full px-4 py-2 border rounded-xl">
                            <option value="">Select Category</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Course Thumbnail</label>
                        <div class="border-2 border-dashed rounded-xl p-4 text-center cursor-pointer" @click="$refs.imageInput.click()">
                            <div v-if="imagePreview" class="mb-2">
                                <img :src="imagePreview" class="h-32 mx-auto rounded-lg object-cover" />
                            </div>
                            <Upload v-else class="w-10 h-10 mx-auto text-slate-400 mb-2" />
                            <p class="text-sm text-slate-500">Click to upload image</p>
                        </div>
                        <input type="file" ref="imageInput" @change="onImageChange" accept="image/*" class="hidden" />
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ form.processing ? 'Creating...' : 'Create Course' }}
                        </button>
                        <button type="button" @click="goBack" class="px-6 py-2 border rounded-lg hover:bg-slate-50">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>