<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const form = useForm({
  name: '',
  description: '',
  icon: '',
})

// 🔥 Icon database (expanded)
const icons = [
  'fa-solid fa-code',
  'fa-solid fa-palette',
  'fa-solid fa-chart-line',
  'fa-solid fa-briefcase',
  'fa-solid fa-mobile-screen',
  'fa-solid fa-database',
  'fa-solid fa-camera',
  'fa-solid fa-music',
  'fa-solid fa-language',
  'fa-solid fa-graduation-cap',
  'fa-solid fa-bolt',
  'fa-solid fa-globe',
  'fa-solid fa-cloud',
  'fa-solid fa-lock',
  'fa-solid fa-shield',
  'fa-solid fa-rocket',
  'fa-solid fa-gear',
  'fa-solid fa-brain',
  'fa-solid fa-lightbulb',
]

// 🔍 search
const search = ref('')

const filteredIcons = computed(() =>
  icons.filter(i => i.toLowerCase().includes(search.value.toLowerCase()))
)

const submit = () => {
  form.post(route('admin.categories.store'))
}
</script>

<template>
  <div class="min-h-screen bg-[#0f0a24] text-white flex items-center justify-center p-6">

    <div class="w-full max-w-3xl bg-[#16103a] rounded-2xl shadow-xl p-6 border border-white/10">

      <!-- HEADER -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold">Create Category</h1>
        <p class="text-gray-400 text-sm">Build a new category for your platform</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">

        <!-- NAME -->
        <input
          v-model="form.name"
          placeholder="Category name"
          class="w-full p-3 bg-[#0f0a24] border border-white/10 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none"
        />
        <p v-if="form.errors.name" class="text-red-400 text-sm">
          {{ form.errors.name }}
        </p>

        <!-- DESCRIPTION -->
        <textarea
          v-model="form.description"
          placeholder="Description"
          class="w-full p-3 bg-[#0f0a24] border border-white/10 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none"
        />

        <!-- ICON PICKER CARD -->
        <div class="bg-[#0f0a24] border border-white/10 rounded-2xl p-4">

          <!-- header -->
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm text-gray-300">Select Icon</h2>

            <!-- preview -->
            <div v-if="form.icon" class="flex items-center gap-2 text-purple-400">
              <i :class="form.icon"></i>
              <span class="text-xs">Selected</span>
            </div>
          </div>

          <!-- search -->
          <input
            v-model="search"
            placeholder="Search icons..."
            class="w-full mb-4 p-2 bg-[#16103a] border border-white/10 rounded-lg text-sm outline-none focus:ring-2 focus:ring-purple-500"
          />

          <!-- icon grid -->
          <div class="grid grid-cols-6 gap-3 max-h-56 overflow-y-auto pr-1">

            <div
              v-for="icon in filteredIcons"
              :key="icon"
              @click="form.icon = icon"
              class="flex items-center justify-center p-4 rounded-xl cursor-pointer transition-all duration-200 border"
              :class="form.icon === icon
                ? 'bg-purple-600 border-purple-400 scale-105 shadow-lg'
                : 'bg-[#16103a] border-white/10 hover:bg-white/10 hover:scale-105'"
            >
              <i :class="icon" class="text-lg"></i>
            </div>

          </div>

        </div>

        <!-- BUTTONS -->
        <div class="flex gap-3">
          <button
            type="submit"
            class="bg-purple-600 hover:bg-purple-700 px-6 py-2 rounded-xl font-semibold transition"
            :disabled="form.processing"
          >
            Save Category
          </button>

          <Link
            :href="route('admin.categories.index')"
            class="px-6 py-2 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10"
          >
            Cancel
          </Link>
        </div>

      </form>

    </div>
  </div>
</template>