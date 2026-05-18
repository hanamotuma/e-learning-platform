<script setup>
import { ref, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { 
  Search, Star, Trash2, CheckCircle, XCircle, Eye, 
  MessageCircle, Flag, ThumbsUp, Filter, ChevronDown 
} from 'lucide-vue-next'
import axios from 'axios'

const reviews = ref([])
const isLoading = ref(true)
const stats = ref({
  total: 0,
  approved: 0,
  pending: 0,
  flagged: 0,
  average_rating: 0
})
const searchQuery = ref('')
const statusFilter = ref('all')
const ratingFilter = ref('all')
const showFilters = ref(false)

const fetchReviews = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('/admin/reviews/data', {
      params: {
        search: searchQuery.value,
        status: statusFilter.value,
        rating: ratingFilter.value
      }
    })
    reviews.value = response.data.reviews || []
    stats.value = response.data.stats || stats.value
  } catch (error) {
    console.error('Error fetching reviews:', error)
  } finally {
    isLoading.value = false
  }
}

const approveReview = async (review) => {
  if (confirm('Approve this review?')) {
    try {
      await axios.post(`/admin/reviews/${review.id}/approve`)
      await fetchReviews()
    } catch (error) {
      alert('Error approving review')
    }
  }
}

const rejectReview = async (review) => {
  if (confirm('Reject/Remove this review?')) {
    try {
      await axios.delete(`/admin/reviews/${review.id}`)
      await fetchReviews()
    } catch (error) {
      alert('Error removing review')
    }
  }
}

const flagReview = async (review) => {
  const reason = prompt('Why is this review inappropriate?')
  if (reason) {
    try {
      await axios.post(`/admin/reviews/${review.id}/flag`, { reason })
      await fetchReviews()
    } catch (error) {
      alert('Error flagging review')
    }
  }
}

const respondToReview = async (review) => {
  const response = prompt('Write your response to this review:')
  if (response) {
    try {
      await axios.post(`/admin/reviews/${review.id}/respond`, { response })
      await fetchReviews()
    } catch (error) {
      alert('Error sending response')
    }
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusBadge = (status) => {
  const badges = {
    approved: 'bg-green-100 text-green-700',
    pending: 'bg-yellow-100 text-yellow-700',
    flagged: 'bg-red-100 text-red-700',
    rejected: 'bg-gray-100 text-gray-700'
  }
  return badges[status] || 'bg-gray-100 text-gray-700'
}

onMounted(() => {
  fetchReviews()
})
</script>

<template>
  <Head title="Review Management | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-3xl font-bold dark:text-white">Review Management</h1>
        <p class="text-slate-500 mt-1">Moderate and manage all course reviews</p>
      </div>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Total Reviews</p>
          <p class="text-2xl font-bold">{{ stats.total }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Average Rating</p>
          <div class="flex items-center gap-1">
            <p class="text-2xl font-bold">{{ stats.average_rating }}</p>
            <Star class="w-5 h-5 text-yellow-400 fill-yellow-400" />
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Approved</p>
          <p class="text-2xl font-bold text-green-600">{{ stats.approved }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Pending</p>
          <p class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Flagged</p>
          <p class="text-2xl font-bold text-red-600">{{ stats.flagged }}</p>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-center">
          <div class="flex-1 min-w-[200px]">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input 
                v-model="searchQuery" 
                @keyup.enter="fetchReviews" 
                type="text" 
                placeholder="Search by course, student, or review text..." 
                class="w-full pl-10 pr-4 py-2 border rounded-lg dark:bg-slate-900" 
              />
            </div>
          </div>
          <select v-model="statusFilter" @change="fetchReviews" class="px-4 py-2 border rounded-lg dark:bg-slate-900">
            <option value="all">All Status</option>
            <option value="approved">Approved</option>
            <option value="pending">Pending</option>
            <option value="flagged">Flagged</option>
          </select>
          <select v-model="ratingFilter" @change="fetchReviews" class="px-4 py-2 border rounded-lg dark:bg-slate-900">
            <option value="all">All Ratings</option>
            <option value="5">5 Stars</option>
            <option value="4">4 Stars</option>
            <option value="3">3 Stars</option>
            <option value="2">2 Stars</option>
            <option value="1">1 Star</option>
          </select>
          <button @click="fetchReviews" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
        </div>
      </div>
      
      <!-- Reviews List -->
      <div class="space-y-4">
        <div v-if="isLoading" class="text-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600 mx-auto"></div>
          <p class="text-slate-500 mt-4">Loading reviews...</p>
        </div>
        
        <div v-else-if="reviews.length === 0" class="bg-white dark:bg-slate-800 rounded-xl border p-12 text-center">
          <MessageCircle class="w-16 h-16 text-slate-300 mx-auto mb-4" />
          <p class="text-slate-500">No reviews found</p>
        </div>
        
        <div v-for="review in reviews" :key="review.id" class="bg-white dark:bg-slate-800 rounded-xl border p-6 hover:shadow-md transition-all">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                {{ review.user?.full_name?.charAt(0) || 'U' }}
              </div>
              <div>
                <p class="font-bold dark:text-white">{{ review.user?.full_name || 'Anonymous' }}</p>
                <p class="text-sm text-slate-500">on {{ review.course?.title }}</p>
                <div class="flex items-center gap-1 mt-1">
                  <Star v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-slate-300'" />
                  <span class="text-xs text-slate-400 ml-2">{{ formatDate(review.created_at) }}</span>
                </div>
              </div>
            </div>
            <div class="flex gap-2">
              <span :class="['px-2 py-1 text-xs rounded-full', getStatusBadge(review.status)]">
                {{ review.status || 'pending' }}
              </span>
            </div>
          </div>
          
          <div class="mb-4">
            <p class="text-slate-600 dark:text-slate-300">{{ review.review }}</p>
          </div>
          
          <!-- Instructor Response -->
          <div v-if="review.instructor_response" class="bg-slate-50 dark:bg-slate-700/30 rounded-lg p-4 mb-4">
            <p class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Instructor Response:</p>
            <p class="text-sm">{{ review.instructor_response }}</p>
          </div>
          
          <!-- Actions -->
          <div class="flex gap-2 pt-4 border-t">
            <button v-if="review.status === 'pending'" @click="approveReview(review)" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
              <CheckCircle class="w-4 h-4" />
              Approve
            </button>
            <button @click="respondToReview(review)" class="px-4 py-2 border rounded-lg hover:bg-slate-50 flex items-center gap-2">
              <MessageCircle class="w-4 h-4" />
              Respond
            </button>
            <button @click="flagReview(review)" class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 flex items-center gap-2">
              <Flag class="w-4 h-4" />
              Flag
            </button>
            <button @click="rejectReview(review)" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>