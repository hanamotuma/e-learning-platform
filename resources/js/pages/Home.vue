<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Link , router, usePage} from '@inertiajs/vue3'
import axios from 'axios'
import { 
  BookOpen, Calendar, MessageCircle, Bell, Settings, ChevronRight, TrendingUp,
  Award, Clock, Play, LogOut, HelpCircle, FileText, Moon, Sun, Home,
  CheckCircle, Video, Users, X, Star, User, Search, Filter, ShoppingCart,
  Heart, Share2, ChevronLeft, ChevronDown, Menu, XCircle, Globe, Shield,
  Headphones, Zap, Target, BarChart3, Briefcase, Code, Palette, Megaphone,
  Database, Smartphone, Trash2, Plus, Minus, CreditCard, Gift, Truck
} from 'lucide-vue-next'

// Theme & UI state
const isDarkMode = ref(false)
const isMobileMenuOpen = ref(false)
const isScrolled = ref(false)
const scrollPercentage = ref(0)
const selectedCategory = ref('all')
const searchQuery = ref('')
const sortBy = ref('popular')
const currentPage = ref('home')
const showCartDropdown = ref(false)

// Cart state - Load from localStorage on initialization
const cartItems = ref([])

// Navigation items with their section IDs
const navItems = [
  { name: 'Home', id: 'home' },
  { name: 'Courses', id: 'courses' },
  { name: 'Categories', id: 'categories' },
  { name: 'Contact', id: 'contact' }
]

// Categories
const categories = [
  { id: 'all', name: 'All', icon: BookOpen },
  { id: 'development', name: 'Development', icon: Code },
  { id: 'design', name: 'Design', icon: Palette },
  { id: 'business', name: 'Business', icon: Briefcase },
  { id: 'marketing', name: 'Marketing', icon: Megaphone },
  { id: 'data', name: 'Data Science', icon: Database }
]


const featuredCourses = ref([])


  


const fetchCourses = async () => {
  try {
    const response = await axios.get('/api/courses')
    featuredCourses.value = response.data
    loadCartFromLocalStorage()
  } catch (error) {
    console.error('Error fetching courses:', error)
    // Fallback to sample data if API fails
    featuredCourses.value = getSampleCourses()
  }
}

onMounted(() => {
  initTheme()
  window.addEventListener('scroll', handleScroll)
  fetchCourses() // Call API to get courses
  const shouldOpenCart = sessionStorage.getItem('open_cart')
  if (shouldOpenCart === 'true') {
    currentPage.value = 'cart'
    sessionStorage.removeItem('open_cart')
  }
  document.addEventListener('click', handleClickOutside)
})

const getSampleCourses = () => {
  return [
    {
      id: 1,
      title: 'Complete Web Development Bootcamp',
      instructor: 'Dr. Angela Yu',
      price: 4990,
      originalPrice: 19990,
      rating: 4.8,
      reviews: 12450,
      students: 125000,
      hours: 45,
      image: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=500',
      category: 'development',
      badge: 'Bestseller',
      level: 'Beginner to Advanced',
    },
    // Add more sample courses...
  ]
}

// Function to save cart to localStorage
const saveCartToLocalStorage = () => {
  const cartData = cartItems.value.map(item => item.id)
  localStorage.setItem('savedCart', JSON.stringify(cartData))
}

// Function to load cart from localStorage
const loadCartFromLocalStorage = () => {
  const savedCart = localStorage.getItem('savedCart')
  if (savedCart) {
    const cartIds = JSON.parse(savedCart)
    cartItems.value = featuredCourses.value.filter(course => cartIds.includes(course.id))
    featuredCourses.value.forEach(course => {
      course.inCart = cartIds.includes(course.id)
    })
  }
}



// Cart total
const cartTotal = computed(() => {
  return cartItems.value.reduce((total, item) => total + item.price, 0)
})

const cartCount = computed(() => cartItems.value.length)

// Add to cart
const addToCart = (course) => {
  if (!cartItems.value.find(item => item.id === course.id)) {
    cartItems.value.push(course)
    course.inCart = true
    saveCartToLocalStorage()
  }
}

// Remove from cart
const removeFromCart = (course) => {
  const index = cartItems.value.findIndex(item => item.id === course.id)
  if (index !== -1) {
    cartItems.value.splice(index, 1)
    course.inCart = false
    saveCartToLocalStorage()
  }
}

// Suggested courses
const suggestedCourses = computed(() => {
  return featuredCourses.value.filter(course => 
    !cartItems.value.find(item => item.id === course.id)
  ).slice(0, 4)
})

watch(cartItems, () => {
  saveCartToLocalStorage()
}, { deep: true })

// Benefits
const benefits = [
  { icon: Globe, title: 'Global Community', description: 'Join 50M+ learners worldwide' },
  { icon: Shield, title: 'Lifetime Access', description: 'Learn at your own pace' },
  { icon: Headphones, title: '24/7 Support', description: 'Get help when you need it' },
  { icon: Award, title: 'Certificates', description: 'Shareable credentials' }
]

// Stats
const statsList = [
  { value: '50M+', label: 'Learners', icon: Users },
  { value: '5,000+', label: 'Courses', icon: BookOpen },
  { value: '100+', label: 'Countries', icon: Globe },
  { value: '85%', label: 'Career Growth', icon: TrendingUp }
]

// Computed filtered courses
const filteredCourses = computed(() => {
  let filtered = featuredCourses.value
  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(c => c.category === selectedCategory.value)
  }
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(c => 
      c.title.toLowerCase().includes(query) ||
      c.instructor.toLowerCase().includes(query)
    )
  }
  return filtered
})

// Sorted courses
const sortedCourses = computed(() => {
  const courses = [...filteredCourses.value]
  switch (sortBy.value) {
    case 'popular':
      return courses.sort((a, b) => b.students - a.students)
    case 'newest':
      return courses.sort((a, b) => new Date(b.date) - new Date(a.date))
    case 'highest-rated':
      return courses.sort((a, b) => b.rating - a.rating)
    case 'price-low':
      return courses.sort((a, b) => a.price - b.price)
    case 'price-high':
      return courses.sort((a, b) => b.price - a.price)
    default:
      return courses
  }
})

// Helper functions
const formatNumber = (num) => {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'k'
  return num.toString()
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US').format(price) + ' ETB'
}

const getCategoryName = (id) => {
  const category = categories.find(c => c.id === id)
  return category ? category.name : 'Featured'
}

// Theme functions
const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value
  if (isDarkMode.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

const initTheme = () => {
  const savedTheme = localStorage.getItem('theme')
  const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
    isDarkMode.value = true
    document.documentElement.classList.add('dark')
  }
}

// Scroll handler
const handleScroll = () => {
  isScrolled.value = window.scrollY > 50
  
  const h = document.documentElement;
  const b = document.body;
  const st = 'scrollTop';
  const sh = 'scrollHeight';
  const percent = (h[st] || b[st]) / ((h[sh] || b[sh]) - h.clientHeight) * 100;
  scrollPercentage.value = percent;
}

// Scroll to section
const scrollTo = (sectionId) => {
  isMobileMenuOpen.value = false
  showCartDropdown.value = false
  
  if (currentPage.value !== 'home') {
    currentPage.value = 'home'
    setTimeout(() => {
      const element = document.getElementById(sectionId)
      if (element) {
        const offset = 80
        const elementPosition = element.getBoundingClientRect().top + window.pageYOffset
        window.scrollTo({
          top: elementPosition - offset,
          behavior: 'smooth'
        })
      }
    }, 100)
    return
  }
  
  const element = document.getElementById(sectionId)
  if (element) {
    const offset = 80
    const elementPosition = element.getBoundingClientRect().top + window.pageYOffset
    window.scrollTo({
      top: elementPosition - offset,
      behavior: 'smooth'
    })
  }
}

const openCart = () => {
  showCartDropdown.value = !showCartDropdown.value
  if (currentPage.value === 'cart') {
    currentPage.value = 'home'
  } else {
    currentPage.value = 'cart'
    window.scrollTo({ top: 0, behavior: 'instant' })
  }
}

const closeCart = () => {
  currentPage.value = 'home'
  showCartDropdown.value = false
}

// Course click handler
const goToCourse = (course) => {
  const courseId = course.id
  console.log('Course clicked:', course.id, course.title)
  sessionStorage.setItem('intended_course_id', courseId)
  sessionStorage.setItem('redirect_after_login', `/course/${courseId}`)
  router.get(`/course/${courseId}`)
}

// Browse all courses button handler
const browseAllCourses = () => {
  const page = usePage()
  const isLoggedIn = !!page.props.auth?.user
  
  if (!isLoggedIn) {
    sessionStorage.setItem('redirect_after_login', '/courses')
    router.get(route('login'))
  } else {
    router.get('/courses')
  }
}

// Proceed to checkout from cart
const proceedToCheckout = () => {
  if (cartItems.value.length === 0) {
    showCartMessage('Your cart is empty')
    return
  }
  
  sessionStorage.setItem('checkout_cart', JSON.stringify(cartItems.value))
  
  const page = usePage()
  const isLoggedIn = !!page.props.auth?.user
  
  if (!isLoggedIn) {
    sessionStorage.setItem('redirect_after_checkout', 'true')
    sessionStorage.setItem('redirect_after_login', '/checkout')
    router.get(route('register'))
  } else {
    router.get('/checkout')
  }
}

// Get started button handler
const getStarted = () => {
  const page = usePage()
  const isLoggedIn = !!page.props.auth?.user
  
  if (!isLoggedIn) {
    sessionStorage.setItem('redirect_after_login', '/courses')
    router.get(route('register'))
  } else {
    router.get('/courses')
  }
}

// Logout function
const logout = () => {
  if (confirm('Are you sure you want to logout?')) {
    router.post(route('logout'))
  }
}

// Toast message for cart
const toastMessage = ref('')
const showToast = ref(false)

const showCartMessage = (message) => {
  toastMessage.value = message
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 2000)
}

// Override addToCart with toast
const addToCartWithToast = (course, event) => {
  if (event) event.stopPropagation()
  
  if (!cartItems.value.find(item => item.id === course.id)) {
    cartItems.value.push(course)
    course.inCart = true
    saveCartToLocalStorage()
    showCartMessage(`${course.title} added to cart!`)
  } else {
    showCartMessage(`${course.title} is already in cart`)
  }
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  const cartButton = document.querySelector('.cart-button')
  const cartDropdown = document.querySelector('.cart-dropdown')
  if (showCartDropdown.value && cartDropdown && !cartDropdown.contains(event.target) && !cartButton?.contains(event.target)) {
    showCartDropdown.value = false
  }
}

onMounted(() => {
  initTheme()
  window.addEventListener('scroll', handleScroll)
  loadCartFromLocalStorage()
  const shouldOpenCart = sessionStorage.getItem('open_cart')
  if (shouldOpenCart === 'true') {
    currentPage.value = 'cart'
    sessionStorage.removeItem('open_cart')
  }
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div :class="{ 'dark': isDarkMode }" class="min-h-screen bg-white dark:bg-slate-900">
    <!-- Toast Notification -->
    <div v-if="showToast" class="fixed top-24 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-xl shadow-lg animate-fade-in">
      {{ toastMessage }}
    </div>

    <!-- Progress Bar -->
    <div class="fixed top-0 left-0 h-1 bg-blue-600 z-50 transition-all duration-150" 
         :style="{ width: scrollPercentage + '%' }"></div>

    <!-- Theme Toggle -->
    <button @click="toggleTheme" class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-all duration-300 border border-slate-200 dark:border-slate-700">
      <Sun v-if="isDarkMode" class="w-5 h-5 text-yellow-500" />
      <Moon v-else class="w-5 h-5 text-blue-600" />
    </button>

    <!-- Mobile Menu Button -->
    <!-- In the mobile menu, change this -->
<Link href="/student/dashboard" class="block w-full text-center py-3 bg-blue-600 text-white font-bold rounded-xl">
    Dashboard
</Link>
    <!-- Header -->
    <header :class="[
      'fixed top-0 left-0 right-0 z-40 transition-all duration-500',
      isScrolled ? 'bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-md py-3' : 'bg-white dark:bg-slate-900 py-5 border-b border-slate-100 dark:border-slate-800'
    ]">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <!-- Logo -->
          <button @click="scrollTo('home')" class="flex items-center space-x-2 group shrink-0">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-md group-hover:scale-105 transition-transform duration-300">
              L
            </div>
            <span class="text-2xl font-black tracking-tighter text-slate-800 dark:text-white">Learn<span class="text-blue-600">Hub</span></span>
          </button>

          <!-- Desktop Navigation -->
          <div class="hidden lg:flex items-center space-x-8">
            <button v-for="item in navItems" :key="item.name"
              @click="scrollTo(item.id)"
              class="text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition-colors">
              {{ item.name }}
            </button>
          </div>

          <!-- Right side - Search, Cart, Auth -->
          <div class="hidden lg:flex items-center space-x-6">
            <!-- Search Bar -->
            <div class="relative">
              <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Search courses..." 
                class="w-64 px-5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white"
              />
              <Search class="absolute right-4 top-2.5 w-5 h-5 text-slate-400" />
            </div>

            <!-- Cart Button -->
            <div class="relative cart-button">
              <button @click="openCart" class="relative p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors">
                <ShoppingCart class="w-5 h-5 text-slate-600 dark:text-slate-300" />
                <span v-if="cartCount > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                  {{ cartCount }}
                </span>
              </button>
            </div>

            <!-- Conditionally show Auth buttons OR User menu based on login status -->
            <template v-if="!$page.props.auth?.user">
              <!-- Auth Buttons - Show when NOT logged in -->
              <div class="flex items-center space-x-3">
                <Link :href="route('login')" class="px-5 py-2 text-slate-600 dark:text-slate-300 font-medium hover:text-blue-600 transition-colors">
                  Log In
                </Link>
                <Link :href="route('register')" class="px-6 py-2.5 bg-blue-600 text-white font-bold rounded-full hover:bg-blue-700 hover:shadow-md transition-all duration-300">
                  Sign Up
                </Link>
              </div>
            </template>

            <template v-else>
              <!-- User Menu - Show when logged in -->
              <div class="flex items-center space-x-4">
                
                <!-- Notification Bell -->
<Link :href="route('notifications.index')" class="relative p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors">
    <Bell class="w-5 h-5 text-slate-600 dark:text-slate-300" />
    <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"></span>
</Link>

               <!-- Dashboard Button -->
<Link :href="'/student/dashboard'" class="flex items-center space-x-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-full hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-all">
  <User class="w-5 h-5 text-blue-600" />
  <span class="text-sm font-medium text-blue-600 dark:text-blue-400">Dashboard</span>
</Link>

              
           <!-- Profile Avatar - Fix this link -->
<Link :href="`/profile/${$page.props.auth.user.user_id || $page.props.auth.user.id}`" class="flex items-center space-x-2">
    <div class="w-9 h-9 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md cursor-pointer hover:scale-105 transition-transform">
        {{ $page.props.auth.user.name?.charAt(0).toUpperCase() || 'U' }}
    </div>
</Link>

              </div>
            </template>
          </div>
        </div>
      </div>
    </header>

    <!-- Mobile Menu -->
    <div v-if="isMobileMenuOpen" class="fixed inset-0 z-30 lg:hidden">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="isMobileMenuOpen = false"></div>
      <div class="absolute top-0 right-0 w-80 h-full bg-white dark:bg-slate-900 shadow-2xl p-6 pt-24">
        <div class="space-y-4">
          <button v-for="item in navItems" :key="item.name"
            @click="scrollTo(item.id)"
            class="block w-full text-left py-3 px-4 text-slate-600 dark:text-slate-300 font-medium hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
            {{ item.name }}
          </button>
          <div class="pt-4 space-y-3 border-t border-slate-200 dark:border-slate-700">
            <template v-if="!$page.props.auth?.user">
              <Link :href="route('login')" class="block w-full text-center py-3 text-slate-600 dark:text-slate-300 font-medium border border-slate-200 dark:border-slate-700 rounded-xl">
                Log In
              </Link>
              <Link :href="route('register')" class="block w-full text-center py-3 bg-blue-600 text-white font-bold rounded-xl">
                Sign Up Free
              </Link>
            </template>
            <template v-else>
              <!-- Change this Dashboard button -->
<Link href="/student/dashboard" class="flex items-center space-x-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-full hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-all">
    <LayoutDashboard class="w-5 h-5 text-blue-600" />
    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">Dashboard</span>
</Link>
              <Link :href="route('profile.edit')" class="block w-full text-center py-3 text-slate-600 dark:text-slate-300 font-medium border border-slate-200 dark:border-slate-700 rounded-xl">
                My Profile
              </Link>
              <button @click="logout" class="block w-full text-center py-3 text-red-600 font-medium border border-red-200 rounded-xl">
                Logout
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>
    
    <main>
      <!-- Cart Page -->
      <div v-if="currentPage === 'cart'" class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
          <div>
            <button @click="closeCart" class="text-blue-600 hover:text-blue-700 flex items-center space-x-2 mb-4">
              <ChevronLeft class="w-5 h-5" /> Back to Home
            </button>
            <h1 class="text-3xl font-black text-slate-800 dark:text-white">Course Cart</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">{{ cartCount }} {{ cartCount === 1 ? 'course' : 'courses' }} in cart</p>
          </div>
        </div>

        <div>
          <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
              <div v-if="cartCount === 0" class="bg-slate-50 dark:bg-slate-800 rounded-2xl p-12 text-center">
                <ShoppingCart class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Your cart is empty</h3>
                <p class="text-slate-500 mb-6">Looks like you haven't added any courses yet</p>
                <button @click="closeCart" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700">Browse Courses</button>
              </div>
              
              <div v-else class="space-y-4">
                <div v-for="course in cartItems" :key="course.id" class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row gap-4">
                  <img :src="course.image" class="w-full sm:w-32 h-32 object-cover rounded-xl" />
                  <div class="flex-1">
                    <div class="flex items-start justify-between">
                      <div>
                        <h3 class="font-bold text-lg dark:text-white">{{ course.title }}</h3>
                        <p class="text-sm text-slate-500">{{ course.instructor }}</p>
                        <div class="flex items-center space-x-3 mt-2">
                          <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 px-2 py-1 rounded">{{ course.badge }}</span>
                          <div class="flex items-center space-x-1">
                            <Star class="w-3 h-3 text-yellow-400 fill-yellow-400" />
                            <span class="text-sm">{{ course.rating }}</span>
                          </div>
                        </div>
                      </div>
                      <div class="text-right">
                        <div class="text-xl font-black text-blue-600">{{ formatPrice(course.price) }}</div>
                        <div class="text-sm text-slate-400 line-through">{{ formatPrice(course.originalPrice) }}</div>
                      </div>
                    </div>
                    <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100 dark:border-slate-700">
                      <button @click="removeFromCart(course)" class="text-red-500 hover:text-red-600 text-sm flex items-center space-x-1">
                        <Trash2 class="w-4 h-4" />
                        <span>Remove</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="cartCount > 0" class="lg:col-span-1">
              <div class="bg-slate-50 dark:bg-slate-800 rounded-2xl p-6 sticky top-32">
                <h3 class="text-xl font-bold dark:text-white mb-4">Order Summary</h3>
                <div class="space-y-3 pb-4 border-b border-slate-200 dark:border-slate-700">
                  <div class="flex justify-between">
                    <span class="text-slate-500">Subtotal ({{ cartCount }} items)</span>
                    <span class="dark:text-white">{{ formatPrice(cartTotal) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-500">Discount</span>
                    <span class="text-green-600">- {{ formatPrice(0) }}</span>
                  </div>
                </div>
                <div class="flex justify-between pt-4 mb-6">
                  <span class="font-bold dark:text-white">Total</span>
                  <span class="text-2xl font-black text-blue-600">{{ formatPrice(cartTotal) }}</span>
                </div>
                <button @click="proceedToCheckout" class="w-full py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all flex items-center justify-center space-x-2">
                  <CreditCard class="w-4 h-4" />
                  <span>Proceed to Checkout</span>
                </button>
                <div class="flex items-center justify-center space-x-4 mt-4 text-xs text-slate-400">
                  <span class="flex items-center space-x-1"><Shield class="w-3 h-3" /> Secure Payment</span>
                  <span class="flex items-center space-x-1"><Truck class="w-3 h-3" /> Lifetime Access</span>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-16">
            <h2 class="text-2xl font-bold dark:text-white mb-6">You might also like</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
              <div v-for="course in suggestedCourses" :key="course.id" class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm border border-slate-100 dark:border-slate-700">
                <img :src="course.image" class="w-full h-36 object-cover" />
                <div class="p-3">
                  <h4 class="font-semibold text-sm dark:text-white line-clamp-2">{{ course.title }}</h4>
                  <p class="text-xs text-slate-500 mt-1">{{ course.instructor }}</p>
                  <div class="flex items-center justify-between mt-2">
                    <span class="text-sm font-bold text-blue-600">{{ formatPrice(course.price) }}</span>
                    <button @click="addToCartWithToast(course, $event)" class="text-xs bg-blue-600 text-white px-3 py-1 rounded-full hover:bg-blue-700">Add to Cart</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Home Page Content -->
      <div v-if="currentPage === 'home'">
        <!-- Hero Section -->
        <section id="home" class="relative min-h-[90vh] flex items-center overflow-hidden pt-20">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-white dark:from-slate-800 dark:to-slate-900 -z-10"></div>
          <div class="absolute top-20 right-0 w-96 h-96 bg-blue-200 rounded-full filter blur-3xl opacity-30 -z-10"></div>
          <div class="absolute bottom-20 left-0 w-80 h-80 bg-blue-300 rounded-full filter blur-3xl opacity-20 -z-10"></div>
          
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
              <div class="text-center lg:text-left">
                <div class="inline-flex items-center space-x-2 bg-blue-100 dark:bg-blue-900/30 px-4 py-2 rounded-full mb-6">
                  <Zap class="w-4 h-4 text-blue-600" />
                  <span class="text-sm font-semibold text-blue-600">Powering the future of learning</span>
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-800 dark:text-white leading-tight">
                  Learn Without<br />
                  <span class="text-blue-600">Limits</span>
                </h1>
                <p class="text-lg text-slate-500 dark:text-slate-400 mt-6 max-w-lg mx-auto lg:mx-0">
                  Start, switch, or advance your career with 5,000+ courses taught by expert instructors.
                </p>
                
                <div class="relative mt-8 max-w-lg mx-auto lg:mx-0">
                  <input 
                    v-model="searchQuery"
                    type="text" 
                    placeholder="What do you want to learn?" 
                    class="w-full px-6 py-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full text-slate-800 dark:text-white shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  <button @click="scrollTo('courses')" class="absolute right-2 top-2 px-6 py-2 bg-blue-600 text-white font-bold rounded-full hover:bg-blue-700 transition-all">
                    Search
                  </button>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-center lg:justify-start gap-4 text-sm text-slate-500">
                  <span>Trusted by:</span>
                  <div class="flex space-x-6">
                    <span class="font-bold text-slate-700 dark:text-slate-300">Google</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">Microsoft</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">Amazon</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">Netflix</span>
                  </div>
                </div>
              </div>
              
              <div class="hidden lg:block relative">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                  <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600" class="w-full h-auto rounded-2xl" />
                  <div class="absolute inset-0 bg-gradient-to-t from-blue-600/20 to-transparent"></div>
                </div>
                <div class="absolute -bottom-6 -left-6 bg-white dark:bg-slate-800 rounded-xl shadow-lg p-4 flex items-center space-x-3">
                  <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <Users class="w-6 h-6 text-blue-600" />
                  </div>
                  <div>
                    <div class="font-bold text-slate-800 dark:text-white">50M+ Learners</div>
                    <div class="text-sm text-slate-500">Join our community</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Stats Section -->
        <section class="py-16 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
              <div v-for="stat in statsList" :key="stat.label" class="text-center">
                <component :is="stat.icon" class="w-8 h-8 text-blue-600 mx-auto mb-3" />
                <div class="text-3xl font-black dark:text-white">{{ stat.value }}</div>
                <div class="text-sm text-slate-500">{{ stat.label }}</div>
              </div>
            </div>
          </div>
        </section>

        <!-- Categories Section -->
        <section id="categories" class="py-20 bg-slate-50 dark:bg-slate-800/30">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
              <h2 class="text-3xl sm:text-4xl font-black dark:text-white">Browse <span class="text-blue-600">Top Categories</span></h2>
              <p class="text-slate-500 dark:text-slate-400 mt-4">Choose from 5,000+ courses taught by expert instructors</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
              <button v-for="cat in categories" :key="cat.id"
                @click="selectedCategory = cat.id"
                :class="[
                  'p-6 rounded-2xl text-center transition-all duration-300',
                  selectedCategory === cat.id 
                    ? 'bg-blue-600 text-white shadow-md scale-105' 
                    : 'bg-white dark:bg-slate-900 hover:shadow-md dark:hover:bg-slate-800 border border-slate-100 dark:border-slate-700'
                ]">
                <component :is="cat.icon" class="w-8 h-8 mx-auto mb-3" :class="selectedCategory === cat.id ? 'text-white' : 'text-blue-600'" />
                <span class="font-semibold text-sm">{{ cat.name }}</span>
              </button>
            </div>
          </div>
        </section>

        <!-- Courses Section -->
        <section id="courses" class="py-20 bg-white dark:bg-slate-900">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-12">
              <div>
                <h2 class="text-3xl sm:text-4xl font-black dark:text-white">
                  {{ selectedCategory === 'all' ? 'Featured' : getCategoryName(selectedCategory) }} 
                  <span class="text-blue-600">Courses</span>
                </h2>
                <p class="text-slate-500 dark:text-slate-400 mt-2">{{ filteredCourses.length }} courses found</p>
              </div>
              <div class="flex items-center space-x-2 mt-4 sm:mt-0">
                <Filter class="w-4 h-4 text-slate-400" />
                <select v-model="sortBy" class="bg-transparent border-none text-sm dark:text-white focus:outline-none">
                  <option value="popular">Most Popular</option>
                  <option value="newest">Newest</option>
                  <option value="highest-rated">Highest Rated</option>
                  <option value="price-low">Price: Low to High</option>
                  <option value="price-high">Price: High to Low</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              <div v-for="course in sortedCourses.slice(0, 12)" :key="course.id"
                  @click="goToCourse(course)"
  class="group bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer border border-slate-100 dark:border-slate-700">                
                <div class="relative overflow-hidden h-48">
                  <img :src="course.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                  <span class="absolute top-3 left-3 px-2 py-1 bg-blue-600 text-white text-xs font-semibold rounded-lg">{{ course.badge }}</span>
                </div>

                <div class="p-5">
                  <div class="flex items-center space-x-2 mb-2">
                    <Star class="w-4 h-4 text-yellow-400 fill-yellow-400" />
                    <span class="font-semibold text-sm dark:text-white">{{ course.rating }}</span>
                    <span class="text-xs text-slate-400">({{ formatNumber(course.reviews) }} reviews)</span>
                  </div>
                  
                  <h3 class="font-bold text-lg dark:text-white mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                    {{ course.title }}
                  </h3>
                  
                  <p class="text-sm text-slate-500 mb-3">{{ course.instructor }}</p>
                  
                  <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2 text-xs text-slate-500">
                      <Clock class="w-3 h-3" />
                      <span>{{ course.hours }} hours</span>
                    </div>
                    <div class="flex items-center space-x-2 text-xs text-slate-500">
                      <Users class="w-3 h-3" />
                      <span>{{ formatNumber(course.students) }} students</span>
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700">
                    <div>
                      <span class="text-xl font-black text-blue-600 dark:text-blue-400">{{ formatPrice(course.price) }}</span>
                      <span class="text-sm text-slate-400 line-through ml-2">{{ formatPrice(course.originalPrice) }}</span>
                    </div>
                    <button @click.stop="addToCartWithToast(course, $event)" class="w-10 h-10 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all">
                      <ShoppingCart class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="text-center mt-12">
              <Link :href="route('courses.index')" class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-full hover:bg-blue-700 hover:shadow-md transition-all duration-300 inline-flex items-center gap-2">
    Browse All Courses
    <ChevronRight class="w-4 h-4" />
</Link>
            </div>
          </div>
        </section>

        <!-- Benefits Section -->
        <section class="py-20 bg-blue-600">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
              <h2 class="text-3xl sm:text-4xl font-black text-white">Why Learn with <span class="text-yellow-300">LearnHub?</span></h2>
              <p class="text-blue-100 mt-4">Join millions of learners worldwide</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
              <div v-for="benefit in benefits" :key="benefit.title" class="text-center text-white">
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                  <component :is="benefit.icon" class="w-8 h-8" />
                </div>
                <h3 class="font-bold text-xl mb-2">{{ benefit.title }}</h3>
                <p class="text-blue-100 text-sm">{{ benefit.description }}</p>
              </div>
            </div>
          </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-20 bg-white dark:bg-slate-900">
          <div class="max-w-4xl mx-auto text-center px-4">
            <h2 class="text-3xl sm:text-4xl font-black dark:text-white mb-4">Ready to Start Your Learning Journey?</h2>
            <p class="text-slate-500 dark:text-slate-400 mb-8">Join 50 million+ learners and start a new skill today</p>
            <button @click="getStarted" class="inline-block px-8 py-4 bg-blue-600 text-white font-bold rounded-full text-lg hover:bg-blue-700 hover:shadow-md transition-all duration-300">
              Get Started for Free
            </button>
          </div>
        </section>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
          <div>
            <h3 class="font-bold text-lg mb-4">LearnHub</h3>
            <ul class="space-y-2 text-sm text-slate-400">
              <li><a href="#" class="hover:text-white transition">About Us</a></li>
              <li><a href="#" class="hover:text-white transition">Careers</a></li>
              <li><a href="#" class="hover:text-white transition">Press</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-bold text-lg mb-4">Learn</h3>
            <ul class="space-y-2 text-sm text-slate-400">
              <li><a href="#" class="hover:text-white transition">Categories</a></li>
              <li><a href="#" class="hover:text-white transition">Courses</a></li>
              <li><a href="#" class="hover:text-white transition">Certification</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-bold text-lg mb-4">Support</h3>
            <ul class="space-y-2 text-sm text-slate-400">
              <li><a href="#" class="hover:text-white transition">Help Center</a></li>
              <li><a href="#" class="hover:text-white transition">Contact Us</a></li>
              <li><a href="#" class="hover:text-white transition">FAQs</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-bold text-lg mb-4">Legal</h3>
            <ul class="space-y-2 text-sm text-slate-400">
              <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
              <li><a href="#" class="hover:text-white transition">Terms of Service</a></li>
              <li><a href="#" class="hover:text-white transition">Cookie Policy</a></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-slate-800 pt-8 text-center text-sm text-slate-400">
          <p>&copy; 2026 LearnHub. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.8s ease-out forwards;
}

.animate-fade-in-delay {
  animation: fade-in 0.8s ease-out 0.3s forwards;
  opacity: 0;
}

.animate-fade-in-delay-2 {
  animation: fade-in 0.8s ease-out 0.6s forwards;
  opacity: 0;
}

.animate-fade-in-delay-3 {
  animation: fade-in 0.8s ease-out 0.9s forwards;
  opacity: 0;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>