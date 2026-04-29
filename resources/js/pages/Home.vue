<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

// --- INITIALIZATION ---
const page = usePage();

// --- AUTH & ROUTING LOGIC ---
const user = computed(() => page.props.auth?.user);
const canLogin = computed(() => page.props.canLogin ?? true);
const canRegister = computed(() => page.props.canRegister ?? true);

const dashboardRoute = computed(() => {
    if (!user.value) return route('login');
    
    // Switch based on role column in your database
    return user.value.role === 'admin' 
        ? route('admin.dashboard') 
        : route('dashboard'); 
});

// --- STATE MANAGEMENT ---
const mobileMenuOpen = ref(false)
const isScrolled = ref(false)
const scrollProgress = ref(0)
const selectedCategory = ref('All')
const searchQuery = ref('')
const cartCount = ref(0)
const activeSection = ref('home')
const isSubmitting = ref(false)
const isSubmitted = ref(false)
const selectedCourse = ref(null)
const isDarkMode = ref(false) 
const isLoading = ref(true) 
const maxPrice = ref(100)

// --- AI CHATBOT STATE ---
const isChatOpen = ref(false)
const chatInput = ref('')
const isTyping = ref(false)
const chatMessages = ref([
  { role: 'assistant', content: 'Hi there! I\'m your LearnHub guide. Looking for a specific course or career path today?' }
])
const chatScrollContainer = ref(null)

// Form State
const contactForm = ref({
  first_name: '',
  last_name: '',
  email: '',
  message: ''
})


// Dynamic Hero Content
const currentIndex = ref(0);
const phrases = [
  { main: "Master Your", highlight: "Future Craft." },
  { main: "Elevate Your", highlight: "Digital Skills." },
  { main: "Accelerate Your", highlight: "Dream Career." },
  { main: "Design Your", highlight: "Success Story." }
];

const heroImages = [
  "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=1200",
  "https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&q=80&w=1200",
  "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=1200",
  "https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&q=80&w=1200"
];

// --- CORE FUNCTIONS ---

const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value
  const html = document.documentElement
  if (isDarkMode.value) {
    html.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    html.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
  isDarkMode.value = !isDarkMode.value
  applyTheme(isDarkMode.value)
}

const initTheme = () => {
  const savedTheme = localStorage.getItem('theme')
  const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  
  const shouldBeDark = savedTheme === 'dark' || (!savedTheme && systemPrefersDark)
  
  isDarkMode.value = shouldBeDark
  applyTheme(shouldBeDark)
}

const updateScroll = () => {
  isScrolled.value = window.scrollY > 20
  const winScroll = document.documentElement.scrollTop
  const height = document.documentElement.scrollHeight - document.documentElement.clientHeight
  scrollProgress.value = (winScroll / height) * 100

  const sections = ['home', 'categories', 'courses', 'features', 'contact']
  for (const section of sections) {
    const el = document.getElementById(section)
    if (el) {
      const rect = el.getBoundingClientRect()
      if (rect.top <= 150 && rect.bottom >= 150) {
        activeSection.value = section
      }
    }
  }
}

const scrollTo = (id) => {
  mobileMenuOpen.value = false
  const el = document.getElementById(id)
  if (el) {
    const offset = 85 
    const elementPosition = el.getBoundingClientRect().top + window.pageYOffset
    const offsetPosition = elementPosition - offset

    window.scrollTo({
      top: offsetPosition,
      behavior: 'smooth'
    })
  }
}

// --- LIFECYCLE HOOKS ---
onMounted(() => {
  // Theme Sync
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    isDarkMode.value = true
    document.documentElement.classList.add('dark')
  }

  // Hero Carousel
  const heroInterval = setInterval(() => {
    currentIndex.value = (currentIndex.value + 1) % phrases.length;
  }, 4000);

  window.addEventListener('scroll', updateScroll)
  setTimeout(() => isLoading.value = false, 1200)

  // Cleanup interval on unmount implicitly handled if needed, 
  // but better to store it:
  onUnmounted(() => {
    clearInterval(heroInterval)
    window.removeEventListener('scroll', updateScroll)
  })
})

// --- DATA & COMPUTED ---
const partners = ['Google', 'Meta', 'Netflix', 'Amazon', 'Microsoft', 'Adobe']
const categories = [
  { name: 'All', icon: '💎' },
  { name: 'Development', icon: '💻' },
  { name: 'Design', icon: '🎨' },
  { name: 'Business', icon: '📈' },
  { name: 'Marketing', icon: '🚀' },
  { name: 'AI & Data', icon: '🧠' }
]

const allCourses = [
  { id: 1, category: 'Development', title: 'Full-Stack Web Development 2026', author: 'Dr. Sarah Jenkins', price: 89.99, rating: 4.9, reviews: '2.4k', image: 'https://images.pexels.com/photos/1181244/pexels-photo-1181244.jpeg?auto=compress&cs=tinysrgb&w=600', tag: 'Bestseller', desc: 'Master the entire stack from database to deployment.' },
  { id: 2, category: 'Design', title: 'Advanced UI/UX Design Systems', author: 'Marcus Rhoades', price: 74.99, rating: 4.8, reviews: '1.8k', image: 'https://images.pexels.com/photos/196644/pexels-photo-196644.jpeg?auto=compress&cs=tinysrgb&w=600', tag: 'New', desc: 'Build scalable design systems for modern enterprise apps.' },
  { id: 3, category: 'AI & Data', title: 'AI & Machine Learning Masterclass', author: 'Prof. Alan Turing', price: 99.00, rating: 5.0, reviews: '950', image: 'https://images.pexels.com/photos/373543/pexels-photo-373543.jpeg?auto=compress&cs=tinysrgb&w=600', tag: 'Popular', desc: 'Dive deep into neural networks and predictive modeling.' },
  { id: 4, category: 'Business', title: 'Mastering Startup Strategy', author: 'Elena Fisher', price: 59.99, rating: 4.7, reviews: '1.2k', image: 'https://images.pexels.com/photos/3183150/pexels-photo-3183150.jpeg?auto=compress&cs=tinysrgb&w=600', tag: 'Finance', desc: 'The blueprint for scaling your vision to a unicorn.' },
  { id: 5, category: 'Marketing', title: 'Digital Growth Hacking 101', author: 'Gary V.', price: 45.00, rating: 4.6, reviews: '3.1k', image: 'https://images.pexels.com/photos/905163/pexels-photo-905163.jpeg?auto=compress&cs=tinysrgb&w=600', tag: 'Trending', desc: 'Unconventional strategies to explode your user base.' },
  { id: 6, category: 'Design', title: 'Motion Graphics with After Effects', author: 'Kevin Slide', price: 65.00, rating: 4.9, reviews: '820', image: 'https://images.pexels.com/photos/251225/pexels-photo-251225.jpeg?auto=compress&cs=tinysrgb&w=600', tag: 'Creative', desc: 'Animate like a pro using industry-standard tools.' },
]

const filteredCourses = computed(() => {
  let filtered = allCourses
  if (selectedCategory.value !== 'All') {
    filtered = filtered.filter(c => c.category === selectedCategory.value)
  }
  if (searchQuery.value) {
    filtered = filtered.filter(c => c.title.toLowerCase().includes(searchQuery.value.toLowerCase()))
  }
  return filtered.filter(c => c.price <= maxPrice.value)
})

// --- EVENT HANDLERS ---
const addToCart = (e) => {
  e.stopPropagation()
  cartCount.value++
}

const handleContact = () => {
  isSubmitting.value = true
  setTimeout(() => {
    isSubmitting.value = false
    isSubmitted.value = true
    contactForm.value = { first_name: '', last_name: '', email: '', message: '' }
    setTimeout(() => isSubmitted.value = false, 5000)
  }, 1500)
}

const sendMessage = async () => {
  if (!chatInput.value.trim()) return
  const userMsg = chatInput.value
  chatMessages.value.push({ role: 'user', content: userMsg })
  chatInput.value = ''
  isTyping.value = true
  await nextTick()
  chatScrollContainer.value.scrollTop = chatScrollContainer.value.scrollHeight

  setTimeout(async () => {
    let reply = "That's a great choice! We have several experts in that field."
    if(userMsg.toLowerCase().match(/price|cheap|cost/)) {
        reply = "Looking for a deal? Our Marketing courses start as low as $45.00!"
    } else if (userMsg.toLowerCase().includes('design')) {
        reply = "Our Design catalog is elite. I highly recommend 'Advanced UI/UX Design Systems'."
    }
    chatMessages.value.push({ role: 'assistant', content: reply })
    isTyping.value = false
    await nextTick()
    chatScrollContainer.value.scrollTop = chatScrollContainer.value.scrollHeight
  }, 1500)
}
</script>
<template>
  <Head title="LearnHub | Elite Learning Platform" />

  <!-- Progress Bar -->
  <div class="fixed top-0 left-0 h-1 bg-blue-600 z-[120] transition-all duration-150" :style="{ width: scrollProgress + '%' }"></div>

  <!-- Theme Toggle Button -->
  <button @click="toggleTheme" class="fixed bottom-6 left-6 z-[150] w-14 h-14 bg-white dark:bg-slate-800 shadow-2xl rounded-2xl flex items-center justify-center text-xl hover:rotate-12 transition-all border border-slate-100 dark:border-slate-700">
    <span v-if="isDarkMode">☀️</span>
    <span v-else>🌙</span>
  </button>

  <!-- AI Chatbot -->
  <div class="fixed bottom-6 right-6 z-[150] flex flex-col items-end">
    <transition name="list">
      <div v-if="isChatOpen" class="w-80 sm:w-96 h-[550px] bg-white dark:bg-slate-900 shadow-2xl rounded-[2.5rem] border border-slate-100 dark:border-slate-800 mb-4 flex flex-col overflow-hidden">
        <div class="p-6 bg-blue-600 text-white flex justify-between items-center">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-xl">🤖</div>
            <div>
                <p class="font-black text-xs uppercase tracking-widest leading-none">LearnHub AI</p>
                <p class="text-[10px] opacity-70">Always Online</p>
            </div>
          </div>
          <button @click="isChatOpen = false" class="hover:rotate-90 transition-transform">✕</button>
        </div>
        
        <div ref="chatScrollContainer" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/50 dark:bg-slate-950/50">
          <div v-for="(msg, idx) in chatMessages" :key="idx" 
               :class="['max-w-[85%] p-4 rounded-2xl text-sm leading-relaxed shadow-sm', msg.role === 'user' ? 'bg-blue-600 text-white ml-auto rounded-tr-none' : 'bg-white dark:bg-slate-800 dark:text-slate-200 mr-auto rounded-tl-none border border-slate-100 dark:border-slate-700']">
            {{ msg.content }}
          </div>
          <div v-if="isTyping" class="bg-white dark:bg-slate-800 p-4 rounded-2xl w-16 flex space-x-1 shadow-sm border border-slate-100 dark:border-slate-700">
             <span class="w-1.5 h-1.5 bg-blue-600 rounded-full animate-bounce"></span>
             <span class="w-1.5 h-1.5 bg-blue-600 rounded-full animate-bounce [animation-delay:0.2s]"></span>
             <span class="w-1.5 h-1.5 bg-blue-600 rounded-full animate-bounce [animation-delay:0.4s]"></span>
          </div>
        </div>

        <div class="p-4 border-t dark:border-slate-800 bg-white dark:bg-slate-900">
          <form @submit.prevent="sendMessage" class="relative">
            <input v-model="chatInput" placeholder="Ask about prices or categories..." class="w-full pl-4 pr-12 py-4 bg-slate-100 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-blue-600 dark:text-white" />
            <button type="submit" class="absolute right-2 top-2 w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center hover:scale-105 transition-transform">↑</button>
          </form>
        </div>
      </div>
    </transition>

    <button @click="isChatOpen = !isChatOpen" class="w-16 h-16 bg-blue-600 text-white shadow-2xl rounded-2xl flex items-center justify-center text-2xl hover:scale-110 transition-all hover:rotate-12">
      <span v-if="!isChatOpen">💬</span>
      <span v-else>✕</span>
    </button>
  </div>

  <!-- Course Modal -->
  <div v-if="selectedCourse" class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6">
    <div @click="selectedCourse = null" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[3rem] overflow-hidden shadow-2xl relative animate-in zoom-in duration-300">
      <button @click="selectedCourse = null" class="absolute top-6 right-6 w-10 h-10 bg-white dark:bg-slate-800 rounded-full shadow-lg flex items-center justify-center font-bold z-10 hover:bg-red-50 hover:text-red-500 transition-colors">✕</button>
      <div class="flex flex-col md:flex-row">
        <img :src="selectedCourse.image" class="w-full md:w-1/2 h-64 md:h-auto object-cover" />
        <div class="p-8 md:p-12 flex flex-col justify-center">
          <span class="text-blue-600 font-black text-xs uppercase tracking-widest mb-2">{{ selectedCourse.category }}</span>
          <h2 class="text-3xl font-black mb-4 leading-tight dark:text-white">{{ selectedCourse.title }}</h2>
          <p class="text-slate-500 mb-8">{{ selectedCourse.desc }}</p>
          <div class="flex items-center justify-between mt-auto">
            <span class="text-4xl font-black dark:text-white">${{ selectedCourse.price }}</span>
            <button @click="addToCart" class="px-8 py-4 bg-blue-600 text-white font-black rounded-2xl hover:bg-slate-900 transition-all">Enroll Now</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="min-h-screen bg-[#FDFDFF] dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans selection:bg-blue-600 selection:text-white overflow-x-hidden">
    
    <!-- Header -->
    <header :class="['fixed top-0 left-0 right-0 z-[100] transition-all duration-500', isScrolled ? 'bg-white/90 dark:bg-slate-950/90 backdrop-blur-md py-3 shadow-sm border-b dark:border-slate-800' : 'bg-transparent py-6 border-transparent']">
      <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        <div class="flex items-center space-x-10">
          <button @click="scrollTo('home')" class="flex items-center space-x-2 group">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg shadow-blue-200 group-hover:rotate-12 transition-transform duration-300">L</div>
            <span class="text-2xl font-black tracking-tighter">Learn<span class="text-blue-600">Hub</span></span>
          </button>

          <div class="hidden lg:flex items-center space-x-1">
            <button v-for="item in ['Home', 'Categories', 'Courses', 'Features', 'Contact']" :key="item" 
              @click="scrollTo(item.toLowerCase())"
              :class="[activeSection === item.toLowerCase() ? 'text-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-slate-600 dark:text-slate-400']"
              class="px-4 py-2 text-sm font-bold hover:text-blue-600 rounded-full hover:bg-blue-50 transition-all duration-300">
              {{ item }}
            </button>
          </div>
        </div>

        <div class="flex items-center space-x-4">
          <div class="hidden md:flex relative group">
            <input v-model="searchQuery" @keyup.enter="triggerSearch" type="text" placeholder="Search courses..." class="pl-10 pr-12 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-full text-sm focus:ring-2 focus:ring-blue-600 w-48 lg:w-64 focus:w-80 transition-all duration-500 dark:text-white" />
            <span class="absolute left-4 top-2.5 text-slate-400">🔍</span>
          </div>

          <button class="relative p-2 text-slate-600 dark:text-slate-300 hover:text-blue-600 transition-transform active:scale-90">
            <span class="text-xl">🛒</span>
            <span v-if="cartCount > 0" class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center animate-bounce">{{ cartCount }}</span>
          </button>

          <div class="hidden sm:flex items-center space-x-3">
<template v-if="user">
    <Link 
      :href="dashboardRoute" 
      class="text-sm font-bold px-4 hover:text-blue-600 transition-colors"
    >
      Dashboard
    </Link>
  </template>

  <template v-else>
    <Link 
      v-if="canLogin" 
      :href="route('login')" 
      class="text-sm font-bold px-4 hover:text-blue-600 transition-colors"
    >
      Log In
    </Link>
    
    <Link 
      v-if="canRegister" 
      :href="route('register')" 
      class="px-6 py-2.5 bg-slate-900 dark:bg-blue-600 text-white text-sm font-bold rounded-full hover:bg-blue-600 shadow-xl transition-all hover:-translate-y-1"
    >
      Join Free
    </Link>
  </template>
          </div>

          <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 text-2xl" :class="{'rotate-90': mobileMenuOpen}">
            {{ mobileMenuOpen ? '✕' : '☰' }}
          </button>
        </div>
      </nav>
    </header>

    <!-- Partners Bar -->
    <div class="bg-white dark:bg-slate-950 py-4 border-b border-slate-50 dark:border-slate-900 mt-24">
        <div class="max-w-7xl mx-auto px-6 overflow-hidden relative">
            <div class="flex space-x-12 whitespace-nowrap animate-marquee items-center opacity-30 dark:invert">
                <span v-for="brand in [...partners, ...partners]" :key="brand" class="text-2xl font-black grayscale">{{ brand }}</span>
            </div>
        </div>
    </div>

    <main>
      <!-- Hero Section -->
      <section id="home" class="pt-24 pb-32 px-6 relative overflow-hidden min-h-[90vh] flex items-center">
        <div class="absolute top-0 left-1/4 w-[600px] h-[600px] bg-blue-400/20 dark:bg-blue-600/10 rounded-full blur-[120px] -z-10 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-purple-400/10 dark:bg-purple-600/5 rounded-full blur-[100px] -z-10"></div>

        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-20 items-center">
          
          <div class="z-10 text-center lg:text-left space-y-8">
            <div class="inline-flex items-center space-x-3 bg-white/50 dark:bg-slate-900/50 backdrop-blur-md px-4 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-transform hover:scale-105">
              <span class="flex h-3 w-3 rounded-full bg-blue-600 animate-ping"></span>
              <span class="text-[10px] font-black text-slate-600 dark:text-slate-300 uppercase tracking-[0.2em]">Live Learning 2026 Edition</span>
            </div>

            <div class="h-[180px] md:h-[280px] flex flex-col justify-center">
              <transition name="hero-fade" mode="out-in">
                <h1 :key="currentIndex" class="text-7xl md:text-9xl font-black tracking-tighter leading-[0.85] dark:text-white">
                  {{ phrases[currentIndex].main }} <br/>
                  <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-400">
                    {{ phrases[currentIndex].highlight }}
                  </span>
                </h1>
              </transition>
            </div>

            <p class="text-xl text-slate-500 dark:text-slate-400 max-w-lg mx-auto lg:mx-0 leading-relaxed font-medium">
              Don't just learn. Evolve. Access <span class="text-slate-900 dark:text-white font-bold">1,200+ elite certifications</span> taught by global industry pioneers.
            </p>

            <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-6 items-center">
              <button @click="scrollTo('courses')" class="group relative px-10 py-5 bg-blue-600 text-white font-black rounded-2xl overflow-hidden transition-all hover:scale-105 active:scale-95 shadow-2xl shadow-blue-500/30">
                <span class="relative z-10">Explore All Courses</span>
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 group-hover:opacity-0 transition-opacity"></div>
              </button>

              <div class="flex -space-x-4 items-center group cursor-pointer">
                <img v-for="i in 4" :key="i" :src="`https://picsum.photos/seed/${i+20}/100/100`" class="w-14 h-14 rounded-2xl border-4 border-white dark:border-slate-950 object-cover rotate-3 group-hover:rotate-0 transition-all duration-500" />
                <div class="pl-8 text-left">
                  <p class="text-sm font-black dark:text-white">Join 50k+ Students</p>
                  <div class="flex text-amber-400 text-xs mt-1">
                    <span v-for="s in 5" :key="s">★</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="relative group">
            <div class="relative z-10 rounded-[4rem] overflow-hidden aspect-[4/5] border-[12px] border-white dark:border-slate-900 shadow-2xl transition-transform duration-700 group-hover:scale-[1.01]">
              <transition name="hero-fade" mode="out-in">
                <img :key="currentIndex" :src="heroImages[currentIndex]" class="w-full h-full object-cover" alt="Learning Platform" />
              </transition>
              <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 via-transparent to-transparent opacity-60"></div>
            </div>

            <div class="absolute -top-10 -right-10 bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl p-6 rounded-[2.5rem] shadow-2xl border border-white/50 dark:border-slate-700 z-20 animate-float">
              <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl">🚀</div>
                <div>
                  <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest leading-none mb-1">Growth</p>
                  <p class="font-black text-xl dark:text-white">+145% Salary</p>
                </div>
              </div>
            </div>

            <div class="absolute -bottom-10 -left-10 bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl p-6 rounded-[2.5rem] shadow-2xl border border-white/50 dark:border-slate-700 z-20 animate-float-delayed">
              <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl">✔</div>
                <div>
                  <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest leading-none mb-1">Verified</p>
                  <p class="font-black text-xl dark:text-white">Expert Tutors</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Categories Section -->
      <section id="categories" class="py-20 bg-white dark:bg-slate-950">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h2 class="text-4xl font-black mb-12 dark:text-white">Explore by <span class="text-blue-600">Category</span></h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <button v-for="cat in categories" :key="cat.name" 
                    @click="selectedCategory = cat.name; scrollTo('courses')"
                    :class="selectedCategory === cat.name ? 'bg-blue-600 text-white scale-105 shadow-xl' : 'bg-slate-50 dark:bg-slate-900 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-800'"
                    class="p-8 rounded-[2rem] transition-all duration-300 group">
                    <span class="text-4xl block mb-4 group-hover:scale-125 transition-transform">{{ cat.icon }}</span>
                    <p class="font-black text-sm uppercase tracking-widest">{{ cat.name }}</p>
                </button>
            </div>
        </div>
      </section>

      <!-- Courses Section -->
      <section id="courses" class="py-24 bg-slate-50 dark:bg-slate-900/50 scroll-mt-20">
        <div class="max-w-7xl mx-auto px-6">
          <div class="flex flex-col md:flex-row md:items-center justify-between mb-12 gap-8">
            <div>
                <h2 class="text-4xl font-black dark:text-white mb-2">{{ selectedCategory }} Courses</h2>
                <p class="text-slate-500 text-sm">Showing {{ filteredCourses.length }} elite programs</p>
            </div>
            
            <div class="bg-white dark:bg-slate-800 p-6 rounded-[2rem] shadow-sm border dark:border-slate-700 min-w-[300px]">
                <div class="flex justify-between mb-2">
                    <span class="text-xs font-black uppercase dark:text-slate-400">Max Price</span>
                    <span class="text-sm font-black text-blue-600">${{ maxPrice }}</span>
                </div>
                <input type="range" v-model="maxPrice" min="40" max="100" class="w-full h-2 bg-slate-100 rounded-lg appearance-none cursor-pointer accent-blue-600" />
            </div>
          </div>

          <div v-if="isLoading" class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
            <div v-for="i in 3" :key="i" class="bg-white dark:bg-slate-800 rounded-[2.5rem] h-96 animate-pulse p-8">
                <div class="bg-slate-200 dark:bg-slate-700 h-48 rounded-[2rem] mb-6"></div>
                <div class="bg-slate-200 dark:bg-slate-700 h-6 w-3/4 rounded mb-4"></div>
                <div class="bg-slate-200 dark:bg-slate-700 h-6 w-1/2 rounded"></div>
            </div>
          </div>

          <transition-group v-else name="list" tag="div" class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
            <div v-for="course in filteredCourses" :key="course.id" @click="selectedCourse = course" class="bg-white dark:bg-slate-800 rounded-[2.5rem] border border-slate-100 dark:border-slate-700 overflow-hidden hover:shadow-[0_20px_50px_rgba(8,112,184,0.1)] transition-all duration-500 group cursor-pointer">
              <div class="relative h-64 overflow-hidden">
                <img :src="course.image" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" />
                <div class="absolute top-6 left-6 bg-white/95 backdrop-blur px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-tighter shadow-sm">{{ course.tag }}</div>
              </div>
              <div class="p-8">
                <div class="flex items-center justify-between mb-4">
                  <span class="text-blue-600 font-black text-[10px] uppercase tracking-widest">{{ course.category }}</span>
                  <div class="flex items-center text-yellow-400 text-sm">★ <span class="text-slate-900 dark:text-slate-200 ml-1 font-bold">{{ course.rating }}</span></div>
                </div>
                <h3 class="text-xl font-black mb-2 leading-tight group-hover:text-blue-600 transition-colors dark:text-white">{{ course.title }}</h3>
                <p class="text-slate-400 text-sm mb-8">Prof. {{ course.author }}</p>
                <div class="flex items-center justify-between pt-6 border-t border-slate-50 dark:border-slate-700">
                  <span class="text-3xl font-black dark:text-white">${{ course.price }}</span>
                  <button @click="addToCart" class="w-12 h-12 bg-slate-900 dark:bg-blue-600 text-white rounded-2xl flex items-center justify-center hover:bg-blue-600 hover:-rotate-12 transition-all">＋</button>
                </div>
              </div>
            </div>
          </transition-group>
        </div>
      </section>

      <!-- Features Section -->
      <section id="features" class="py-24 bg-white dark:bg-slate-950 scroll-mt-20 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-20 items-center">
          <div class="grid grid-cols-2 gap-6 relative">
            <div class="absolute -z-10 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-blue-100 dark:bg-blue-600/20 blur-[100px] opacity-50"></div>
            <div class="p-8 bg-blue-50/50 dark:bg-blue-900/10 backdrop-blur rounded-[2.5rem] mt-10 border border-blue-100 dark:border-blue-800">
              <span class="text-4xl block mb-4">📱</span>
              <h4 class="font-black mb-2 dark:text-white">Mobile Ready</h4>
              <p class="text-xs text-slate-500">Learn on the go with our top-rated apps.</p>
            </div>
            <div class="p-8 bg-purple-50/50 dark:bg-purple-900/10 backdrop-blur rounded-[2.5rem] border border-purple-100 dark:border-purple-800">
              <span class="text-4xl block mb-4">♾️</span>
              <h4 class="font-black mb-2 dark:text-white">Lifetime Access</h4>
              <p class="text-xs text-slate-500">Buy once, enjoy updates forever.</p>
            </div>
             <div class="p-8 bg-emerald-50/50 dark:bg-emerald-900/10 backdrop-blur rounded-[2.5rem] mt-10 border border-emerald-100 dark:border-emerald-800">
              <span class="text-4xl block mb-4">📜</span>
              <h4 class="font-black mb-2 dark:text-white">Certifications</h4>
              <p class="text-xs text-slate-500">Earn recognized certificates.</p>
            </div>
            <div class="p-8 bg-orange-50/50 dark:bg-orange-900/10 backdrop-blur rounded-[2.5rem] border border-orange-100 dark:border-orange-800">
              <span class="text-4xl block mb-4">👥</span>
              <h4 class="font-black mb-2 dark:text-white">Community</h4>
              <p class="text-xs text-slate-500">Join 24/7 student forums.</p>
            </div>
          </div>
          <div>
            <h2 class="text-5xl font-black mb-8 leading-tight dark:text-white">Beyond just <span class="text-blue-600">Video.</span></h2>
            <p class="text-slate-500 mb-8 leading-relaxed text-lg">We provide a complete ecosystem designed for mastery.</p>
            <ul class="space-y-5">
              <li v-for="text in ['Downloadable Resources', 'Interactive Live Quizzes', '1-on-1 Mentor Support']" :key="text" class="flex items-center space-x-4 group">
                <span class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 flex items-center justify-center font-bold">✓</span>
                <span class="font-bold text-slate-700 dark:text-slate-300">{{ text }}</span>
              </li>
            </ul>
          </div>
        </div>
      </section>

      <!-- Contact Section -->
      <section id="contact" class="py-24 max-w-7xl mx-auto px-6 scroll-mt-20">
        <div class="bg-slate-900 rounded-[4rem] overflow-hidden flex flex-col lg:flex-row shadow-2xl">
          <div class="lg:w-1/2 p-12 lg:p-20 text-white">
            <h2 class="text-4xl lg:text-5xl font-black mb-6">Let's build your <span class="text-blue-500">future</span>.</h2>
            <p class="text-slate-400 mb-12">Support team is available 24/7. Connect with us on social media for daily updates.</p>
            
            <div class="grid grid-cols-2 gap-4 mb-12">
                <a v-for="social in socialLinks" :key="social.name" :href="social.url" 
                   class="flex items-center space-x-4 p-4 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 transition-all group">
                    <div :class="['w-10 h-10 rounded-xl flex items-center justify-center text-xl shadow-lg', social.color]">
                        {{ social.icon }}
                    </div>
                    <span class="font-bold text-sm">{{ social.name }}</span>
                </a>
            </div>

            <div class="space-y-8">
              <div class="flex items-center space-x-6 group">
                <div class="w-14 h-14 bg-slate-800 rounded-2xl flex items-center justify-center text-2xl group-hover:bg-blue-600 transition-colors">📧</div>
                <div><p class="text-xs text-slate-500 font-bold uppercase">Email Us</p><p class="font-bold text-lg">support@learnhub.com</p></div>
              </div>
            </div>
          </div>
          <div class="lg:w-1/2 bg-white dark:bg-slate-800 m-4 lg:m-8 rounded-[3rem] p-10 lg:p-16 flex flex-col justify-center">
            <form v-if="!isSubmitted" @submit.prevent="handleContact" class="space-y-4">
              <div class="grid md:grid-cols-2 gap-4">
                <input v-model="contactForm.first_name" required type="text" placeholder="First Name" class="w-full p-5 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl dark:text-white shadow-inner" />
                <input v-model="contactForm.last_name" required type="text" placeholder="Last Name" class="w-full p-5 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl dark:text-white shadow-inner" />
              </div>
              <input v-model="contactForm.email" required type="email" placeholder="Email" class="w-full p-5 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl dark:text-white shadow-inner" />
              <textarea v-model="contactForm.message" required rows="4" placeholder="How can we help?" class="w-full p-5 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl dark:text-white shadow-inner resize-none"></textarea>
              <button :disabled="isSubmitting" class="w-full py-6 bg-blue-600 text-white font-black rounded-2xl shadow-xl hover:bg-slate-900 transition-all uppercase text-xs tracking-[0.2em] disabled:opacity-50">
                {{ isSubmitting ? 'Sending...' : 'Send Message' }}
              </button>
            </form>
            <div v-else class="text-center py-10">
               <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-6 animate-bounce">✓</div>
               <h3 class="text-2xl font-black mb-2 dark:text-white">Message Sent!</h3>
               <p class="text-slate-500">We'll get back to you within 2 hours.</p>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-slate-950 pt-20 pb-10 border-t border-slate-100 dark:border-slate-900">
      <div class="max-w-7xl mx-auto px-6">
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-12 mb-20">
          
          <div class="col-span-2 lg:col-span-2 space-y-6">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white font-black text-2xl shadow-lg shadow-blue-500/20">L</div>
              <span class="text-3xl font-black dark:text-white tracking-tighter">Learn<span class="text-blue-600">Hub</span></span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed max-w-sm">
              The elite ecosystem for professional growth. Start learning, stay ahead, and master your future.
            </p>
            <div class="flex items-center space-x-2 bg-slate-50 dark:bg-slate-900 p-2 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-inner max-w-xs">
              <input type="email" placeholder="Email for updates" class="bg-transparent border-none w-full px-3 text-sm focus:ring-0 dark:text-white" />
              <button class="bg-blue-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-blue-700 transition-colors">Join</button>
            </div>
          </div>

          <div v-for="section in [
            { title: 'Platform', links: ['Courses', 'Live Mentoring', 'Certification', 'Enterprise'] },
            { title: 'Company', links: ['About Us', 'Success Stories', 'Join our Team', 'Contact'] },
            { title: 'Legal', links: ['Accessibility', 'Privacy', 'User Agreement', 'Support'] }
          ]" :key="section.title" class="col-span-1">
            <h4 class="font-black text-slate-900 dark:text-white mb-6 text-[11px] uppercase tracking-[0.2em] opacity-80">{{ section.title }}</h4>
            <ul class="space-y-4 text-[13px] text-slate-600 dark:text-slate-400 font-medium">
              <li v-for="link in section.links" :key="link">
                <a href="#" class="hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200 hover:pl-1 block">{{ link }}</a>
              </li>
            </ul>
          </div>
        </div>

        <div class="pt-10 border-t border-slate-100 dark:border-slate-900 flex flex-col md:flex-row justify-between items-center gap-6">
          <p class="text-slate-400 text-[11px] font-bold uppercase tracking-widest italic">
            © 2026 LearnHub Education Global
          </p>

          <div class="flex items-center space-x-8 text-[11px] font-bold text-slate-400 uppercase tracking-widest">
            <span class="flex items-center space-x-2"><span>🛡️</span> <span>Verified Secure</span></span>
            <span class="flex items-center space-x-2"><span>🌍</span> <span>Global Access</span></span>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
html {
  scroll-behavior: smooth;
}

@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

.animate-marquee {
    animation: marquee 30s linear infinite;
}

.list-enter-active, .list-leave-active {
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
.list-enter-from, .list-leave-to {
  opacity: 0;
  transform: translateY(30px) scale(0.95);
}

@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
  100% { transform: translateY(0px); }
}

.animate-float {
  animation: float 4s ease-in-out infinite;
}

::-webkit-scrollbar {
  width: 8px;
}

.hero-fade-enter-active,
.hero-fade-leave-active {
  transition: opacity 0.8s ease, transform 0.8s ease;
}

.hero-fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.hero-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>