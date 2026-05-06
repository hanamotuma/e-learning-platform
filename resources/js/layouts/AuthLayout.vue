<template>
    <div class="min-h-screen bg-gray-50 dark:bg-slate-900">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-slate-800 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link :href="route('home')" class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">
                                L
                            </div>
                            <span class="font-bold text-xl dark:text-white">LearnHub</span>
                        </Link>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <Link :href="route('home')" class="text-gray-700 dark:text-gray-300 hover:text-blue-600">Home</Link>
                        <Link :href="route('my-courses')" class="text-gray-700 dark:text-gray-300 hover:text-blue-600">My Courses</Link>
                        
                        <div class="relative">
                            <button @click="toggleDropdown" class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white">
                                    {{ userInitial }}
                                </div>
                                <ChevronDown class="w-4 h-4 text-gray-600" />
                            </button>
                            
                            <div v-if="dropdownOpen" class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg py-2 z-50">
                                <Link :href="route('student.dashboard')" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-slate-700">
                                    Dashboard
                                </Link>
                                <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-slate-700">
                                    Profile
                                </Link>
                                <div class="border-t my-1"></div>
                                <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    Logout
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content -->
        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ChevronDown } from 'lucide-vue-next'

const page = usePage()
const user = computed(() => page.props.auth.user)
const userInitial = computed(() => user.value?.name?.charAt(0) || 'U')
const dropdownOpen = ref(false)

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value
}

// Close dropdown when clicking outside
if (typeof window !== 'undefined') {
    window.addEventListener('click', (e) => {
        if (!e.target.closest('.relative')) {
            dropdownOpen.value = false
        }
    })
}
</script>