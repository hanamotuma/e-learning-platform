<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Plus, Edit2, Trash2, Folder, X, Check } from 'lucide-vue-next'

const props = defineProps({
  categories: Array
})

const showModal = ref(false)
const editingCategory = ref(null)
const form = ref({
  name: '',
  description: '',
  icon: ''
})

const saveCategory = () => {
  if (editingCategory.value) {
    router.put(`/admin/categories/${editingCategory.value.id}`, form.value, {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false
        editingCategory.value = null
        form.value = { name: '', description: '', icon: '' }
      }
    })
  } else {
    router.post('/admin/categories', form.value, {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false
        form.value = { name: '', description: '', icon: '' }
      }
    })
  }
}

const editCategory = (category) => {
  editingCategory.value = category
  form.value = {
    name: category.name,
    description: category.description || '',
    icon: category.icon || ''
  }
  showModal.value = true
}

const deleteCategory = (category) => {
  if (confirm(`Delete "${category.name}"? This will also delete all associated courses.`)) {
    router.delete(`/admin/categories/${category.id}`)
  }
}

const closeModal = () => {
  showModal.value = false
  editingCategory.value = null
  form.value = { name: '', description: '', icon: '' }
}
</script>

<template>
  <Head title="Category Management | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold dark:text-white">Category Management</h1>
          <p class="text-slate-500 mt-1">Manage course categories and subcategories</p>
        </div>
        <button @click="showModal = true" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center gap-2">
          <Plus class="w-4 h-4" />
          Add Category
        </button>
      </div>
      
      <!-- Categories Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="category in categories" :key="category.id" class="bg-white dark:bg-slate-800 rounded-xl border hover:shadow-lg transition-all">
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="w-14 h-14 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center">
                <component :is="category.icon || 'Folder'" class="w-7 h-7 text-white" />
              </div>
              <div class="flex gap-2">
                <button @click="editCategory(category)" class="p-2 text-slate-400 hover:text-blue-600 rounded-lg">
                  <Edit2 class="w-4 h-4" />
                </button>
                <button @click="deleteCategory(category)" class="p-2 text-slate-400 hover:text-red-600 rounded-lg">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
            <h3 class="text-xl font-bold dark:text-white mb-2">{{ category.name }}</h3>
            <p class="text-sm text-slate-500 mb-4">{{ category.description || 'No description' }}</p>
            <div class="pt-4 border-t flex justify-between items-center">
              <span class="text-sm text-slate-500">{{ category.courses_count || 0 }} courses</span>
              <span class="text-xs text-blue-600">Slug: {{ category.slug }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Add/Edit Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-md">
          <div class="p-6 border-b">
            <h3 class="text-xl font-bold">{{ editingCategory ? 'Edit Category' : 'Add Category' }}</h3>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Category Name</label>
              <input v-model="form.name" type="text" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" placeholder="e.g., Web Development" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Description (Optional)</label>
              <textarea v-model="form.description" rows="3" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" placeholder="Describe this category..."></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Icon (Optional)</label>
              <input v-model="form.icon" type="text" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" placeholder="e.g., Code, Palette, Briefcase" />
              <p class="text-xs text-slate-400 mt-1">Use any Lucide icon name</p>
            </div>
          </div>
          <div class="p-6 border-t flex gap-3">
            <button @click="closeModal" class="flex-1 px-4 py-2 border rounded-lg hover:bg-slate-50">Cancel</button>
            <button @click="saveCategory" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>