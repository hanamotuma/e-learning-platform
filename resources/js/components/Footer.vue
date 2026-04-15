<!-- resources/js/Components/Footer.vue -->
<template>
  <footer class="bg-gray-900 text-gray-300 py-12">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
          <h3 class="text-white text-xl font-bold mb-4">EduLe</h3>
          <p class="text-sm">Learn from anywhere and build your bright career.</p>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-4">Quick Links</h4>
          <ul class="space-y-2 text-sm">
            <li><Link href="/about" class="hover:text-white">About Us</Link></li>
            <li><Link href="/contact" class="hover:text-white">Contact</Link></li>
            <li><Link href="/privacy" class="hover:text-white">Privacy Policy</Link></li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-4">Categories</h4>
          <ul class="space-y-2 text-sm">
            <li>Development</li>
            <li>Data Science</li>
            <li>Business</li>
            <li>Design</li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-4">Newsletter</h4>
          <div class="flex">
            <input type="email" placeholder="Your email" class="flex-1 px-3 py-2 rounded-l text-gray-900">
            <button class="bg-purple-600 px-4 py-2 rounded-r hover:bg-purple-700">Subscribe</button>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>