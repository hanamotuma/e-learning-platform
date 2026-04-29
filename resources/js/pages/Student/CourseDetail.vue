<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { 
  Play, 
  Clock, 
  Star, 
  Users, 
  BookOpen,
  CheckCircle,
  ArrowLeft
} from 'lucide-vue-next'

const props = defineProps({
    course: Object,
    isEnrolled: {
        type: Boolean,
        default: false
    },
    enrollment: {
        type: Object,
        default: null
    }
})

const activeTab = ref('overview')

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price)
}

const formatDuration = (weeks) => {
    return `${weeks} week${weeks > 1 ? 's' : ''}`
}
</script>

<template>
    <Head :title="course.title" />
    
    <div class="min-h-screen bg-slate-50">
        
        <!-- Course Header -->
        <div class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <Link :href="route('student.courses')" class="text-blue-600 hover:text-blue-700 flex items-center gap-2 mb-4">
                    <ArrowLeft class="w-4 h-4" />
                    Back to Courses
                </Link>
                
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Course Info -->
                    <div class="lg:col-span-2">
                        <h1 class="text-3xl lg:text-4xl font-black text-slate-900 mb-4">{{ course.title }}</h1>
                        <p class="text-lg text-slate-600 mb-4">{{ course.description }}</p>
                        
                        <div class="flex flex-wrap items-center gap-4 text-sm">
                            <div class="flex items-center gap-1 text-yellow-500">
                                <Star class="w-4 h-4 fill-yellow-500" />
                                <span class="font-medium text-slate-700">{{ course.rating || 4.8 }}</span>
                                <span class="text-slate-500">({{ course.reviews_count || 0 }} reviews)</span>
                            </div>
                            <div class="flex items-center gap-1 text-slate-600">
                                <Users class="w-4 h-4" />
                                <span>{{ course.students_count || 0 }} students</span>
                            </div>
                            <div class="flex items-cfenter gap-1 text-slate-600">
                                <Clock class="w-4 h-4" />
                                <span>{{ course.duration_weeks ? formatDuration(course.duration_weeks) : 'Self-paced' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pricing Card with ENROLLMENT BUTTON -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                            <!-- Course Image -->
                            <img 
                                v-if="course.image"
                                :src="`/storage/${course.image}`" 
                                class="w-full h-48 object-cover rounded-xl mb-4"
                            />
                            
                            <!-- Price -->
                            <div class="text-center mb-4">
                                <span class="text-4xl font-black text-slate-900">{{ formatPrice(course.price) }}</span>
                            </div>
                            
                            <!-- ENROLLMENT BUTTON - This is what you need to add -->
                            <Link 
                                v-if="!isEnrolled"
                                :href="route('enroll.show', course.id)"
                                class="block w-full bg-blue-600 text-white text-center py-3 rounded-xl font-bold hover:bg-blue-700 transition-all"
                            >
                                Enroll Now
                            </Link>
                            
                            <Link 
                                v-else
                                :href="route('student.courses.show', course.id)"
                                class="block w-full bg-emerald-600 text-white text-center py-3 rounded-xl font-bold hover:bg-emerald-700 transition-all flex items-center justify-center gap-2"
                            >
                                <Play class="w-4 h-4" />
                                Continue Learning
                            </Link>
                            
                            <!-- Already Enrolled Badge -->
                            <div v-if="isEnrolled" class="mt-3 text-center">
                                <span class="text-xs text-emerald-600 flex items-center justify-center gap-1">
                                    <CheckCircle class="w-3 h-3" />
                                    You are enrolled in this course
                                </span>
                            </div>
                            
                            <p class="text-xs text-slate-500 text-center mt-4">
                                30-day money-back guarantee
                            </p>
                            
                            <div class="border-t border-slate-100 mt-4 pt-4">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">This course includes:</p>
                                <ul class="space-y-2 text-sm text-slate-600">
                                    <li class="flex items-center gap-2">✓ Full lifetime access</li>
                                    <li class="flex items-center gap-2">✓ Certificate of completion</li>
                                    <li class="flex items-center gap-2">✓ Access on mobile and TV</li>
                                    <li class="flex items-center gap-2">✓ Downloadable resources</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Course Content Tabs -->
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex gap-4 border-b border-slate-200 mb-6">
                <button 
                    @click="activeTab = 'overview'"
                    :class="[
                        'px-4 py-2 font-medium transition-colors',
                        activeTab === 'overview' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-500 hover:text-slate-700'
                    ]"
                >
                    Overview
                </button>
                <button 
                    @click="activeTab = 'curriculum'"
                    :class="[
                        'px-4 py-2 font-medium transition-colors',
                        activeTab === 'curriculum' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-500 hover:text-slate-700'
                    ]"
                >
                    Curriculum
                </button>
                <button 
                    @click="activeTab = 'reviews'"
                    :class="[
                        'px-4 py-2 font-medium transition-colors',
                        activeTab === 'reviews' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-slate-500 hover:text-slate-700'
                    ]"
                >
                    Reviews
                </button>
            </div>
            
            <!-- Overview Tab -->
            <div v-if="activeTab === 'overview'" class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="text-xl font-bold text-slate-900 mb-4">What you'll learn</h2>
                <p class="text-slate-600">{{ course.what_you_will_learn || course.description }}</p>
                
                <h2 class="text-xl font-bold text-slate-900 mt-6 mb-4">Requirements</h2>
                <p class="text-slate-600">{{ course.requirements || 'No specific requirements' }}</p>
            </div>
            
            <!-- Curriculum Tab -->
            <div v-if="activeTab === 'curriculum'" class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="text-xl font-bold text-slate-900 mb-4">Course Curriculum</h2>
                <div v-if="course.sections?.length">
                    <div v-for="section in course.sections" :key="section.id" class="mb-4">
                        <div class="bg-slate-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-slate-900">{{ section.title }}</h3>
                            <p class="text-sm text-slate-500 mt-1">{{ section.lessons?.length || 0 }} lessons</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-slate-500">Curriculum coming soon...</p>
            </div>
            
            <!-- Reviews Tab -->
            <div v-if="activeTab === 'reviews'" class="bg-white rounded-2xl border border-slate-200 p-6">
                <h2 class="text-xl font-bold text-slate-900 mb-4">Student Reviews</h2>
                <p class="text-slate-500">No reviews yet. Be the first to review!</p>
            </div>
        </div>
    </div>
</template>