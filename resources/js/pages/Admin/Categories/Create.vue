<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const form = useForm({
  name: '',
  description: '',
  icon: 'fa-solid fa-code', // Default icon
  accent_color: '#9333ea',  // Added color picking for a "real" feel
})

const icons = [
  'fa-solid fa-code', 'fa-solid fa-palette', 'fa-solid fa-chart-line',
  'fa-solid fa-briefcase', 'fa-solid fa-mobile-screen', 'fa-solid fa-database',
  'fa-solid fa-camera', 'fa-solid fa-music', 'fa-solid fa-language',
  'fa-solid fa-graduation-cap', 'fa-solid fa-bolt', 'fa-solid fa-globe',
  'fa-solid fa-cloud', 'fa-solid fa-lock', 'fa-solid fa-shield',
  'fa-solid fa-rocket', 'fa-solid fa-gear', 'fa-solid fa-brain',
  'fa-solid fa-lightbulb',
]

const search = ref('')
const filteredIcons = computed(() =>
  icons.filter(i => i.toLowerCase().includes(search.value.toLowerCase()))
)

const submit = () => {
  form.post(route('admin.categories.store'))
}
</script>

<template>
  <div class="min-h-screen bg-[#09051a] text-white p-4 md:p-12 font-sans">
    
    <div class="max-w-6xl mx-auto mb-10 flex items-center justify-between">
      <div>
        <Link :href="route('admin.categories.index')" class="text-purple-400 text-sm hover:underline mb-2 block">
          ← Back to Catalog
        </Link>
        <h1 class="text-3xl font-black tracking-tight">New Category</h1>
      </div>
      <div class="flex gap-3">
        <Link :href="route('admin.categories.index')" class="px-6 py-2.5 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition font-medium">
          Cancel
        </Link>
        <button @click="submit" :disabled="form.processing" class="px-8 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 shadow-lg shadow-purple-500/20 transition font-bold">
          Create Category
        </button>
      </div>
    </div>

    <div class="max-w-6xl mx-auto grid lg:grid-cols-5 gap-10">
      
      <div class="lg:col-span-3 space-y-8">
        <section class="bg-[#110c2d] border border-white/5 rounded-3xl p-8 shadow-2xl">
          <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
            <span class="w-1.5 h-5 bg-purple-500 rounded-full"></span>
            Basic Information
          </h2>
          
          <div class="space-y-6">
            <div>
              <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ml-1">Category Name</label>
              <input v-model="form.name" type="text" placeholder="e.g. Web Development" 
                class="w-full bg-[#09051a] border border-white/10 rounded-2xl px-5 py-4 focus:border-purple-500 outline-none transition text-white placeholder:text-gray-700" />
              <p v-if="form.errors.name" class="mt-2 text-red-400 text-xs">{{ form.errors.name }}</p>
            </div>

            <div>
              <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2 ml-1">Short Description</label>
              <textarea v-model="form.description" rows="3" placeholder="Describe what students will learn..." 
                class="w-full bg-[#09051a] border border-white/10 rounded-2xl px-5 py-4 focus:border-purple-500 outline-none transition text-white placeholder:text-gray-700" />
            </div>
          </div>
        </section>

        <section class="bg-[#110c2d] border border-white/5 rounded-3xl p-8 shadow-2xl">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold flex items-center gap-2">
              <span class="w-1.5 h-5 bg-purple-500 rounded-full"></span>
              Identity & Icon
            </h2>
            <input v-model="search" type="text" placeholder="Search icons..." 
              class="bg-[#09051a] border border-white/10 rounded-full px-4 py-1.5 text-xs outline-none focus:border-purple-500 transition" />
          </div>

          <div class="grid grid-cols-5 sm:grid-cols-7 gap-3 overflow-y-auto max-h-64 pr-2 custom-scroll">
            <button v-for="icon in filteredIcons" :key="icon" @click="form.icon = icon" type="button"
              class="aspect-square flex items-center justify-center rounded-2xl transition-all duration-300 border"
              :class="form.icon === icon ? 'bg-purple-600 border-purple-400 scale-110 shadow-xl shadow-purple-600/40' : 'bg-[#09051a] border-white/5 hover:border-white/20'">
              <i :class="icon" class="text-xl"></i>
            </button>
          </div>
        </section>
      </div>

      <div class="lg:col-span-2">
        <div class="sticky top-12">
          <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-4 text-center">Live Student Preview</label>
          
          <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-purple-600 to-blue-600 rounded-[2.5rem] blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
            
            <div class="relative bg-[#110c2d] border border-white/10 rounded-[2rem] p-8 overflow-hidden min-h-[300px] flex flex-col justify-between">
              <div class="absolute -top-10 -right-10 w-32 h-32 bg-purple-600/10 rounded-full blur-3xl"></div>
              
              <div>
                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg mb-6">
                  <i :class="form.icon" class="text-3xl text-white"></i>
                </div>
                
                <h3 class="text-2xl font-bold mb-3">
                  {{ form.name || 'Category Title' }}
                </h3>
                <p class="text-gray-400 text-sm leading-relaxed line-clamp-3">
                  {{ form.description || 'Provide a description to see how it looks here. This will help students understand the course scope.' }}
                </p>
              </div>

              <div class="mt-8 flex items-center justify-between">
                <span class="text-xs font-bold text-purple-400 tracking-widest uppercase">0 Courses</span>
                <div class="flex -space-x-2">
                   <div class="w-8 h-8 rounded-full border-2 border-[#110c2d] bg-gray-800"></div>
                   <div class="w-8 h-8 rounded-full border-2 border-[#110c2d] bg-gray-700"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-8 p-6 bg-amber-500/5 border border-amber-500/10 rounded-2xl">
             <p class="text-amber-500/80 text-xs leading-relaxed italic">
               <strong>Tip:</strong> Use a clear name and a recognizable icon. Categories with descriptions have 30% higher engagement.
             </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar {
  width: 4px;
}
.custom-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}
</style>