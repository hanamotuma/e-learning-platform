<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import InstructorLayout from '@/layouts/InstructorLayout.vue'
import { Star, Star as StarIcon, MessageCircle, ThumbsUp, Flag } from 'lucide-vue-next'
import axios from 'axios'

const reviews = ref([])
const isLoading = ref(true)

const fetchReviews = async () => {
    isLoading.value = true
    try {
        const response = await axios.get('/instructor/reviews/data')
        reviews.value = response.data.reviews || []
    } catch (error) {
        console.error('Error fetching reviews:', error)
    } finally {
        isLoading.value = false
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

onMounted(() => {
    fetchReviews()
})
</script>

<template>
    <Head title="Course Reviews | Instructor" />
    
    <InstructorLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold dark:text-white">Course Reviews</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">See what students are saying about your courses</p>
            </div>

            <div v-if="isLoading" class="text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600 mx-auto"></div>
            </div>

            <div v-else-if="reviews.length === 0" class="bg-white dark:bg-slate-800 rounded-xl border p-12 text-center">
                <MessageCircle class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                <p class="text-slate-500">No reviews yet for your courses</p>
            </div>

            <div v-else class="space-y-4">
                <div v-for="review in reviews" :key="review.id" class="bg-white dark:bg-slate-800 rounded-xl border p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex text-yellow-400">
                                    <span v-for="i in 5" :key="i">
                                        <StarIcon v-if="i <= review.rating" class="w-4 h-4 fill-yellow-400" />
                                        <StarIcon v-else class="w-4 h-4 text-slate-300" />
                                    </span>
                                </div>
                                <span class="text-sm text-slate-500">{{ review.user?.name || 'Anonymous' }}</span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-300 mb-2">{{ review.review }}</p>
                            <p class="text-sm text-blue-600">Course: {{ review.course?.title }}</p>
                            <p class="text-xs text-slate-400 mt-2">{{ formatDate(review.created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>