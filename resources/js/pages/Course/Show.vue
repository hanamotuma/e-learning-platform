<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { 
  Star, Users, Clock, Play, CheckCircle, Video, Download, 
  Award, MessageCircle, CreditCard, ChevronLeft, 
  Heart, Shield, ChevronDown
} from 'lucide-vue-next'

const props = defineProps({
  course: Object,
  isEnrolled: Boolean,
  enrollment: Object,
  progress: Object,
  relatedCourses: Array
})

const page = usePage()
const authUser = computed(() => page.props.auth?.user)

const activeTab = ref('overview')
const isEnrolling = ref(false)
const isDarkMode = ref(false)
const isInWishlist = ref(false)
const expandedSections = ref([])

// Course data
const course = ref(props.course || {})

// Sections for curriculum
const sections = ref(props.course?.sections || [])

// Reviews
const reviews = ref(props.course?.reviews || [])

// Check if in wishlist
onMounted(() => {
  const savedCart = localStorage.getItem('savedCart')
  if (savedCart) {
    const cartIds = JSON.parse(savedCart)
    isInWishlist.value = cartIds.includes(course.value?.id)
  }
  
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'dark') {
    isDarkMode.value = true
    document.documentElement.classList.add('dark')
  }
  
  // Expand first section by default
  if (sections.value.length > 0 && !expandedSections.value.includes(sections.value[0]?.id)) {
    expandedSections.value.push(sections.value[0].id)
  }
})

const toggleSection = (sectionId) => {
  const index = expandedSections.value.indexOf(sectionId)
  if (index > -1) {
    expandedSections.value.splice(index, 1)
  } else {
    expandedSections.value.push(sectionId)
  }
}

const formatPrice = (price) => {
  if (!price) return '0 ETB'
  return new Intl.NumberFormat('en-US').format(price) + ' ETB'
}

const formatNumber = (num) => {
  if (!num) return '0'
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'k'
  return num.toString()
}

const goBack = () => {
  router.get('/')
}

const handleEnroll = () => {
  const courseId = props.course.id
  console.log('Enrollclicked for course:', courseId)
  if (!authUser.value) {
    sessionStorage.setItem('intended_course_id', course.value.id)
    sessionStorage.setItem('redirect_after_login', `/course/${course.value.id}`)
    sessionStorage.setItem('checkout_course', JSON.stringify({
      id:course.id,
      title: props.course.title,
      price: props.course.price,
      image: props.course.image,
      instructor: props.course.instructor
    }))
    router.get('/register')
    return
  }
  
  if (props.isEnrolled) {
    router.get(`/student/dashboard`)
    return
  }
  
  isEnrolling.value = true
  
  sessionStorage.setItem('checkout_course', JSON.stringify({
    id: course.id,
    title: props.course.title,
    price: props.course.price,
    image: props.course.image,
    instructor: props.course.instructor
  }))
  router.get(`/checkout/${props.course.id}`)
  
  
  const singleCart = [{
    id: props.course.id,
    title: props.course.title,
    price: props.course.price,
    image: props.course.image,
    instructor: props.course.instructor
  }]
  sessionStorage.setItem('checkout_cart', JSON.stringify(singleCart))
  
  router.get(`/checkout/${props.course.id}`)
  isEnrolling.value = false
}

const addToWishlist = () => {
  const savedCart = localStorage.getItem('savedCart')
  let cartIds = savedCart ? JSON.parse(savedCart) : []
  
  if (isInWishlist.value) {
    cartIds = cartIds.filter(id => id !== props.course.id)
    isInWishlist.value = false
  } else {
    cartIds.push(props.course.id)
    isInWishlist.value = true
  }
  localStorage.setItem('savedCart', JSON.stringify(cartIds))
}

const objectives = computed(() => {
  if (course.value?.what_you_will_learn) {
    return course.value.what_you_will_learn.split('\n')
  }
  return [
    'Master core concepts of ' + (course.value?.title || 'this course'),
    'Build real-world projects',
    'Get certified upon completion',
    'Learn from industry experts'
  ]
})

const averageRating = computed(() => course.value?.rating || 4.8)
const totalReviews = computed(() => course.value?.reviews || 0)
const totalStudents = computed(() => course.value?.students || 0)
const totalHours = computed(() => course.value?.hours || 0)
</script>

<template>
  <Head :title="(course?.title || 'Course') + ' | LearnHub'" />
  
  <div class="min-h-screen bg-white dark:bg-slate-900">
    <!-- Back Button -->
    <div class="max-w-7xl mx-auto px-4 pt-24 pb-4">
      <button @click="goBack" class="flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:text-blue-600 transition-colors">
        <ChevronLeft class="w-5 h-5" />
        <span>Back to Home</span>
      </button>
    </div>

    <!-- Course Header -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white">
      <div class="max-w-7xl mx-auto px-4 py-8 lg:py-12">
        <div class="grid lg:grid-cols-3 gap-8">
          <div class="lg:col-span-2">
            <div class="flex items-center gap-2 mb-4">
              <span class="text-xs px-2 py-1 bg-blue-500/20 text-blue-300 rounded-full">{{ course?.badge || 'Featured' }}</span>
              <span class="text-xs px-2 py-1 bg-green-500/20 text-green-300 rounded-full">{{ course?.level || 'All Levels' }}</span>
            </div>
            <h1 class="text-2xl lg:text-4xl font-bold mb-4">{{ course?.title }}</h1>
            <p class="text-base lg:text-lg text-gray-300 mb-4">{{ course?.description }}</p>
            <div class="flex items-center gap-4 text-sm flex-wrap">
              <div class="flex items-center gap-1">
                <Star class="w-4 h-4 text-yellow-400 fill-yellow-400" />
                <span>{{ averageRating }}</span>
                <span class="text-gray-400">({{ formatNumber(totalReviews) }} reviews)</span>
              </div>
              <div class="flex items-center gap-1">
                <Users class="w-4 h-4" />
                <span>{{ formatNumber(totalStudents) }} students</span>
              </div>
              <div class="flex items-center gap-1">
                <Clock class="w-4 h-4" />
                <span>{{ totalHours }} total hours</span>
              </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                {{ (course?.instructor || 'I').charAt(0) }}
              </div>
              <span class="text-sm">Created by</span>
              <span class="font-semibold">{{ course?.instructor || 'Expert Instructor' }}</span>
            </div>
          </div>
          
          <!-- Enroll Card -->
          <div class="lg:sticky lg:top-24">
            <div class="bg-white dark:bg-slate-800 rounded-2xl overflow-hidden shadow-xl">
              <img :src="course?.image" class="w-full aspect-video object-cover" />
              <div class="p-6">
                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                  {{ formatPrice(course?.price || 0) }}
                  <span v-if="course?.originalPrice" class="text-sm text-slate-400 line-through ml-2">{{ formatPrice(course.originalPrice) }}</span>
                </div>
                
                <button 
                  @click="handleEnroll" 
                  :disabled="isEnrolling"
                  class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-colors mb-3 disabled:opacity-50 flex items-center justify-center gap-2"
                >
                  <CreditCard v-if="!isEnrolling" class="w-4 h-4" />
                  <div v-if="isEnrolling" class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                  {{ isEnrolling ? 'Processing...' : ((course?.price || 0) > 0 ? `Enroll Now - ${formatPrice(course?.price || 0)}` : 'Enroll for Free') }}
                </button>
                
                <button 
                  @click="addToWishlist" 
                  class="w-full py-3 border border-slate-300 dark:border-slate-600 rounded-xl font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors flex items-center justify-center gap-2"
                  :class="isInWishlist ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 border-blue-300' : 'text-slate-700 dark:text-slate-300'"
                >
                  <Heart class="w-4 h-4" :class="isInWishlist ? 'fill-blue-600 text-blue-600' : ''" />
                  {{ isInWishlist ? 'Added to Wishlist' : 'Add to Wishlist' }}
                </button>
                
                <div class="mt-4 text-center text-sm text-slate-500">
                  <span class="flex items-center justify-center gap-1"><Shield class="w-3 h-3" /> 30-Day Money-Back Guarantee</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="border-b sticky top-0 bg-white dark:bg-slate-900 z-10">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex gap-6 overflow-x-auto">
          <button @click="activeTab = 'overview'" :class="['py-4 font-medium border-b-2 transition-colors whitespace-nowrap', activeTab === 'overview' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 dark:text-slate-400']">
            Overview
          </button>
          <button @click="activeTab = 'curriculum'" :class="['py-4 font-medium border-b-2 transition-colors whitespace-nowrap', activeTab === 'curriculum' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 dark:text-slate-400']">
            Curriculum
          </button>
          <button @click="activeTab = 'reviews'" :class="['py-4 font-medium border-b-2 transition-colors whitespace-nowrap', activeTab === 'reviews' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 dark:text-slate-400']">
            Reviews
          </button>
        </div>
      </div>
    </div>

    <!-- Overview Tab -->
    <div v-if="activeTab === 'overview'" class="max-w-7xl mx-auto px-4 py-8">
      <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-6 mb-6">
            <h2 class="text-2xl font-bold mb-4 dark:text-white">What you'll learn</h2>
            <div class="grid md:grid-cols-2 gap-3">
              <div v-for="objective in objectives.slice(0, 6)" :key="objective" class="flex items-start gap-2">
                <CheckCircle class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" />
                <span class="dark:text-slate-300 text-sm">{{ objective }}</span>
              </div>
            </div>
          </div>
          <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-6">
            <h2 class="text-2xl font-bold mb-4 dark:text-white">Description</h2>
            <p class="dark:text-slate-300 leading-relaxed">{{ course?.description }}</p>
          </div>
        </div>
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-6 h-fit">
          <h3 class="font-bold mb-3 dark:text-white">This course includes:</h3>
          <ul class="space-y-3 text-sm">
            <li class="flex items-center gap-2 dark:text-slate-300"><Video class="w-4 h-4 text-blue-600" /> {{ totalHours }} hours on-demand video</li>
            <li class="flex items-center gap-2 dark:text-slate-300"><Download class="w-4 h-4 text-blue-600" /> Downloadable resources</li>
            <li class="flex items-center gap-2 dark:text-slate-300"><Award class="w-4 h-4 text-blue-600" /> Certificate of completion</li>
            <li class="flex items-center gap-2 dark:text-slate-300"><MessageCircle class="w-4 h-4 text-blue-600" /> Access on mobile and TV</li>
            <li class="flex items-center gap-2 dark:text-slate-300"><Users class="w-4 h-4 text-blue-600" /> Full lifetime access</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Curriculum Tab -->
    <div v-if="activeTab === 'curriculum'" class="max-w-7xl mx-auto px-4 py-8">
      <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-6">
        <h2 class="text-2xl font-bold mb-4 dark:text-white">Course Content</h2>
        <div v-if="sections.length === 0" class="text-center py-8 text-slate-500">
          Course curriculum is being prepared.
        </div>
        <div v-else class="space-y-3">
          <div v-for="section in sections" :key="section.id" class="border-b dark:border-slate-700 pb-3">
            <button @click="toggleSection(section.id)" class="w-full text-left flex justify-between items-center py-3 hover:bg-slate-100 dark:hover:bg-slate-700/50 px-3 rounded-lg transition-colors">
              <div>
                <span class="font-semibold dark:text-white">{{ section.title }}</span>
                <span class="text-sm text-slate-500 ml-2">{{ section.lessons?.length || 0 }} lectures</span>
              </div>
              <ChevronDown class="w-5 h-5 text-slate-400 transition-transform" :class="{ 'rotate-180': expandedSections.includes(section.id) }" />
            </button>
            <div v-show="expandedSections.includes(section.id)" class="pl-6 mt-2 space-y-2">
              <div v-for="lesson in section.lessons" :key="lesson.id" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700/30">
                <div class="flex items-center gap-3">
                  <Play class="w-4 h-4 text-blue-500" />
                  <span class="text-sm dark:text-slate-300">{{ lesson.title }}</span>
                </div>
                <span class="text-xs text-slate-500">{{ lesson.duration || '10 min' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reviews Tab -->
    <div v-if="activeTab === 'reviews'" class="max-w-7xl mx-auto px-4 py-8">
      <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-6">
        <div class="flex items-center gap-8 pb-6 border-b dark:border-slate-700 mb-6">
          <div class="text-center">
            <div class="text-5xl font-bold text-blue-600">{{ averageRating }}</div>
            <div class="flex text-yellow-400 text-sm mt-2">
              <span v-for="i in 5" :key="i">★</span>
            </div>
            <div class="text-sm text-slate-500 mt-1">{{ totalReviews }} reviews</div>
          </div>
          <div class="flex-1">
            <div v-if="reviews.length > 0" class="space-y-2">
              <div v-for="star in [5,4,3,2,1]" :key="star" class="flex items-center gap-3">
                <span class="text-sm w-8">{{ star }}★</span>
                <div class="flex-1 h-2 bg-slate-200 rounded-full">
                  <div class="h-full bg-yellow-400 rounded-full" :style="{ width: (reviews.filter(r => r.rating === star).length / reviews.length * 100) + '%' }"></div>
                </div>
                <span class="text-sm text-slate-500 w-12">{{ reviews.filter(r => r.rating === star).length }}</span>
              </div>
            </div>
            <div v-else class="text-center py-4 text-slate-500">No reviews yet</div>
          </div>
        </div>
        
        <div v-if="reviews.length > 0" class="space-y-6">
          <div v-for="review in reviews" :key="review.id" class="border-b dark:border-slate-700 pb-5 last:border-0">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold">
                {{ review.user?.name?.charAt(0) || 'U' }}
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between flex-wrap gap-2">
                  <div>
                    <p class="font-bold dark:text-white">{{ review.user?.name || 'Anonymous' }}</p>
                    <div class="flex items-center gap-1 text-yellow-400 text-sm mt-1">
                      <span v-for="i in review.rating" :key="i">★</span>
                      <span v-for="i in (5 - review.rating)" :key="i" class="text-slate-300">★</span>
                    </div>
                  </div>
                  <span class="text-xs text-slate-400">{{ new Date(review.date).toLocaleDateString() }}</span>
                </div>
                <p class="text-slate-600 dark:text-slate-300 mt-2">{{ review.comment }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="text-center py-8">
          <div class="text-5xl mb-4">⭐</div>
          <p class="text-slate-500">No reviews yet. Be the first to review this course!</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>