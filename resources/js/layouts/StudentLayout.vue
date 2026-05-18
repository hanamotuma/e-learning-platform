<template>
  <div class="flex min-h-screen bg-slate-50 font-sans text-slate-900">
    
    <aside class="w-72 bg-white border-r border-slate-200 flex flex-col sticky top-0 h-screen transition-all">
      
      <!-- Logo - Make it clickable to home -->
      <Link href="/" class="p-6 flex items-center gap-3 hover:opacity-80 transition-opacity">
        <div class="bg-blue-600 p-2 rounded-xl">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <span class="text-xl font-black tracking-tight text-slate-800">Learn<span class="text-blue-600">Hub</span></span>
      </Link>

      <!-- Rest of your sidebar remains the same -->
      <div class="mx-4 mb-8 p-4 bg-slate-50 border border-slate-100 rounded-2xl flex items-center gap-3">
        <img :src="`https://ui-avatars.com/api/?name=${user?.name?.replace(/ /g, '+')}&background=2563eb&color=fff`" class="h-11 w-11 rounded-xl shadow-sm" alt="User">
        <div class="overflow-hidden">
          <p class="text-sm font-bold text-slate-900 truncate">{{ user?.name || 'Student' }}</p>
          <p class="text-[11px] text-slate-500 truncate font-medium">{{ user?.email }}</p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 space-y-1.5">
        <button @click="activeTab = 'dashboard'" :class="['flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all w-full text-left', activeTab === 'dashboard' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600']">
          <span class="text-lg">🏠</span> Dashboard
        </button>
        <button @click="activeTab = 'courses'" :class="['flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all w-full text-left', activeTab === 'courses' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600']">
          <span class="text-lg">📖</span> My Courses
        </button>
        <button @click="activeTab = 'certificates'" :class="['flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all w-full text-left', activeTab === 'certificates' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600']">
          <span class="text-lg">🎓</span> Certificates
        </button>
        <button @click="activeTab = 'settings'" :class="['flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all w-full text-left', activeTab === 'settings' ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600']">
          <span class="text-lg">⚙️</span> Settings
        </button>
      </nav>

      <div class="px-4 py-4 space-y-1 border-t border-slate-100">
        <button @click="logout" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl font-semibold transition-all w-full text-left">
          <span class="text-lg">🚪</span> Logout
        </button>
      </div>
    </aside>

    <main class="flex-1 overflow-y-auto">
      <div class="max-w-6xl mx-auto p-10">
        <!-- Dashboard Content -->
        <div v-if="activeTab === 'dashboard'">
          <!-- Your existing dashboard content -->
          <slot />
        </div>

        <!-- Settings Tab Content -->
        <div v-if="activeTab === 'settings'">
          <StudentSettings :user="user" />
        </div>

        <!-- Other tabs remain the same -->
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import StudentSettings from '@/components/StudentSettings.vue'

const props = defineProps({
  user: Object
})

const activeTab = ref('dashboard')

const logout = () => {
  if (confirm('Are you sure you want to logout?')) {
    router.post(route('logout'))
  }
}
</script>