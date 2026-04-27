<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-indigo-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link href="/" class="flex items-center space-x-2">
                            <Logo />
                        </Link>
                        
                        <div class="hidden md:flex items-center space-x-8 ml-10">
                            <Link href="/courses" class="text-gray-700 hover:text-indigo-600 transition">Browse Courses</Link>
                            <Link href="/my-courses" class="text-gray-700 hover:text-indigo-600 transition">My Courses</Link>
                            <Link href="/certificates" class="text-gray-700 hover:text-indigo-600 transition">Certificates</Link>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <div class="hidden md:block">
                            <div class="relative">
                                <input type="text" placeholder="Search courses..." 
                                       class="w-64 px-4 py-2 rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                                <SearchIcon class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" />
                            </div>
                        </div>
                        
                        <!-- Notifications -->
                        <button class="relative p-2 rounded-full hover:bg-gray-100">
                            <BellIcon class="h-6 w-6 text-gray-600" />
                            <span class="absolute top-1 right-1 h-2 w-2 bg-red-500 rounded-full"></span>
                        </button>
                        
                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                                <img :src="user.avatar || '/default-avatar.png'" 
                                     class="h-8 w-8 rounded-full object-cover">
                                <span class="text-gray-700">{{ user.full_name }}</span>
                                <ChevronDownIcon class="h-4 w-4 text-gray-600" />
                            </button>
                            
                            <div x-show="open" @click.away="open = false" 
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                                <Link href="/profile" class="block px-4 py-2 hover:bg-gray-100">Profile</Link>
                                <Link href="/settings" class="block px-4 py-2 hover:bg-gray-100">Settings</Link>
                                <div class="border-t my-1"></div>
                                <Link href="/logout" method="post" as="button" 
                                      class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">
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
        
        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <Logo />
                        <p class="mt-4 text-gray-600">Empowering minds through quality education. Learn anytime, anywhere.</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><Link href="/about" class="text-gray-600 hover:text-indigo-600">About Us</Link></li>
                            <li><Link href="/contact" class="text-gray-600 hover:text-indigo-600">Contact</Link></li>
                            <li><Link href="/faq" class="text-gray-600 hover:text-indigo-600">FAQ</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4">Resources</h3>
                        <ul class="space-y-2">
                            <li><Link href="/blog" class="text-gray-600 hover:text-indigo-600">Blog</Link></li>
                            <li><Link href="/support" class="text-gray-600 hover:text-indigo-600">Support</Link></li>
                            <li><Link href="/community" class="text-gray-600 hover:text-indigo-600">Community</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4">Newsletter</h3>
                        <div class="flex">
                            <input type="email" placeholder="Your email" 
                                   class="flex-1 px-4 py-2 rounded-l-lg border border-gray-300 focus:border-indigo-500">
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded-r-lg hover:bg-indigo-700">
                                Subscribe
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import Logo from '/resources/js/Components/Logo.vue';const user = {
    full_name: 'Hana Motuma',
    avatar: null
};
</script>