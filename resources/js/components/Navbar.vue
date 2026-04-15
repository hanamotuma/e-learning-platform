<!-- resources/js/Components/Navbar.vue -->
<template>
  <nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-4">
        <!-- Logo -->
        <Link href="/" class="text-2xl font-bold text-indigo-600">
          EduLe
        </Link>
        
        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-8">
          <Link href="/" class="text-gray-700 hover:text-indigo-600">Home</Link>
          <Link href="/courses" class="text-gray-700 hover:text-indigo-600 font-semibold">All Course</Link>
          <Link href="/pages" class="text-gray-700 hover:text-indigo-600">Pages</Link>
          <Link href="/blog" class="text-gray-700 hover:text-indigo-600">Blog</Link>
          <Link href="/contact" class="text-gray-700 hover:text-indigo-600">Contact</Link>
        </div>
        
        <!-- Auth Buttons -->
        <div class="hidden md:flex items-center space-x-4">
          <Link href="/login" class="text-gray-700 hover:text-indigo-600">Sign In</Link>
          <Link href="/register" class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition">
            Sign Up
          </Link>
        </div>
        
        <!-- Mobile Menu Button -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>
      
      <!-- Mobile Menu -->
      <div v-if="mobileMenuOpen" class="md:hidden py-4 border-t">
        <div class="flex flex-col space-y-3">
          <Link href="/" class="text-gray-700 hover:text-indigo-600">Home</Link>
          <Link href="/courses" class="text-gray-700 hover:text-indigo-600">All Course</Link>
          <Link href="/pages" class="text-gray-700 hover:text-indigo-600">Pages</Link>
          <Link href="/blog" class="text-gray-700 hover:text-indigo-600">Blog</Link>
          <Link href="/contact" class="text-gray-700 hover:text-indigo-600">Contact</Link>
          <hr />
          <Link href="/login" class="text-gray-700 hover:text-indigo-600">Sign In</Link>
          <Link href="/register" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-center">
            Sign Up
          </Link>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const mobileMenuOpen = ref(false)
</script>