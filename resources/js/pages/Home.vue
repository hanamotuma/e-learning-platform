<template>
  <div class="min-h-screen bg-[#F8FAFC]">
    <section class="bg-indigo-900 pt-20 pb-32 px-6 rounded-b-[60px] relative overflow-hidden">
      <div class="max-w-7xl mx-auto relative z-10">
        <h1 class="text-5xl md:text-6xl font-extrabold text-white mb-6">
          Level up your <span class="text-indigo-300">skills.</span>
        </h1>
        <p class="text-indigo-100 max-w-xl text-lg mb-8">
          Join 5,000+ students learning from industry experts. Your journey to mastery starts here.
        </p>
        <div class="flex gap-4">
          <button class="bg-white text-indigo-900 px-8 py-3 rounded-2xl font-bold hover:bg-indigo-50 transition">
            Explore Courses
          </button>
        </div>
      </div>
      <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-800 rounded-full -mr-20 -mt-20 blur-3xl opacity-50"></div>
    </section>

    <main class="max-w-7xl mx-auto -mt-16 px-6 pb-20">
      <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100 mb-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="relative flex-1 max-w-md">
            <input 
              v-model="search"
              type="text" 
              placeholder="What do you want to learn?"
              class="w-full pl-12 pr-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500"
            >
            <span class="absolute left-4 top-3.5 opacity-30">🔍</span>
          </div>

          <div class="flex flex-wrap gap-2">
            <button 
              v-for="cat in categories" 
              :key="cat"
              @click="activeCategory = cat"
              :class="activeCategory === cat ? 'bg-indigo-600 text-white' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
              class="px-5 py-2.5 rounded-xl text-sm font-semibold transition"
            >
              {{ cat }}
            </button>
          </div>
        </div>
      </div>

      <!-- Add this conditional rendering -->
      <div v-if="filteredCourses.length === 0" class="text-center py-20">
        <div class="text-6xl mb-4">📚</div>
        <h3 class="text-2xl font-semibold text-gray-700 mb-2">No courses found</h3>
        <p class="text-gray-500">Try adjusting your search or category filter</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <CourseCard 
          v-for="course in filteredCourses" 
          :key="course.id" 
          :course="course" 
        />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import CourseCard from '@/components/Course/CourseCard.vue';

const props = defineProps({
  courses: {
    type: Array,
    default: () => []
  }
});
// Temporary sample data for testing
const sampleCourses = [
  {
    id: 1,
    title: "Complete Web Development Bootcamp",
    description: "Learn HTML, CSS, JavaScript, and Vue.js from scratch",
    category: "Development",
    price: 89,
    rating: 4.8
  },
  {
    id: 2,
    title: "UI/UX Design Masterclass",
    description: "Master Figma, design systems, and user research",
    category: "UI/UX Design",
    price: 79,
    rating: 4.9
  },
  {
    id: 3,
    title: "Data Science Fundamentals",
    description: "Python, Pandas, and Machine Learning basics",
    category: "Data Science",
    price: 99,
    rating: 4.7
  }
];

// Use sample data if no courses are provided
const coursesToUse = computed(() => {
  return props.courses && props.courses.length > 0 ? props.courses : sampleCourses;
});
console.log('Courses received:', props.courses);

const search = ref('');
const activeCategory = ref('All Courses');
const categories = ['All Courses', 'UI/UX Design', 'Development', 'Data Science', 'Business'];

const filteredCourses = computed(() => {
    if (!props.courses || !Array.isArray(props.courses)) {
    return [];
  }
  return props.courses.filter(course => {
    const matchesSearch = course.title.toLowerCase().includes(search.value.toLowerCase());
    const matchesCategory = activeCategory.value === 'All Courses' || course.category === activeCategory.value;
    return matchesSearch && matchesCategory;
  });
});
</script>