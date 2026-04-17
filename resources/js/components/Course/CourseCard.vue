<template>
  <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 border border-gray-100">
    <div class="h-48 bg-gradient-to-r from-indigo-500 to-purple-600 relative">
      <div class="absolute inset-0 flex items-center justify-center text-white text-4xl">
        🎓
      </div>
    </div>
    <div class="p-6">
      <div class="flex justify-between items-start mb-2">
        <h3 class="text-xl font-bold text-gray-800">{{ course.title }}</h3>
        <span class="text-sm bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">
          {{ course.category || 'General' }}
        </span>
      </div>
      <p class="text-gray-600 mb-4 line-clamp-2">{{ course.description || 'No description available' }}</p>
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-2">
          <span class="text-yellow-500">⭐</span>
          <span class="text-sm text-gray-600">{{ course.rating || '4.5' }}</span>
        </div>
        <span class="text-2xl font-bold text-indigo-600">
          {{ course.price === 0 ? 'Free' : `$${course.price || 49}` }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  course: {
    type: Object,
    required: true,
    default: () => ({
      title: 'Course Title',
      description: 'Course description',
      category: 'General',
      price: 49,
      rating: 4.5
    })
  }
});
</script>