<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Bell, BookOpen, Award, MessageCircle, Star, ChevronLeft, CheckCircle, X } from 'lucide-vue-next'
import axios from 'axios'

const notifications = ref([])
const isLoading = ref(true)

const fetchNotifications = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('/notifications/json')
    notifications.value = response.data.notifications?.data || []
  } catch (error) {
    console.error('Error fetching notifications', error)
  } finally {
    isLoading.value = false
  }
}

const markAsRead = async (id) => {
  try {
    await axios.post(`/notifications/${id}/read`)
    const notif = notifications.value.find(n => n.id === id)
    if (notif) notif.read_at = new Date().toISOString()
  } catch (error) {
    console.error('Error marking as read', error)
  }
}

const markAllAsRead = async () => {
  try {
    await axios.post('/notifications/mark-all-read')
    notifications.value.forEach(n => n.read_at = new Date().toISOString())
  } catch (error) {
    console.error('Error marking all as read', error)
  }
}

const deleteNotification = async (id) => {
  if (confirm('Delete this notification?')) {
    try {
      await axios.delete(`/notifications/${id}`)
      notifications.value = notifications.value.filter(n => n.id !== id)
    } catch (error) {
      console.error('Error deleting notification', error)
    }
  }
}

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  const now = new Date()
  const diff = Math.floor((now - d) / 1000 / 60)
  
  if (diff < 1) return 'Just now'
  if (diff < 60) return `${diff} minutes ago`
  if (diff < 1440) return `${Math.floor(diff / 60)} hours ago`
  if (diff < 43200) return `${Math.floor(diff / 1440)} days ago`
  return d.toLocaleDateString()
}

const getNotificationIcon = (type) => {
  const icons = {
    course_update: BookOpen,
    enrollment: Star,
    certificate_issued: Award,
    announcement: MessageCircle
  }
  return icons[type] || Bell
}

const goBack = () => {
  router.get('/student/dashboard')
}

onMounted(() => {
  fetchNotifications()
})
</script>

<template>
  <Head title="Notifications | LearnHub" />
  
  <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
    <div class="max-w-4xl mx-auto px-4 py-8 lg:py-12">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center space-x-4">
          <button @click="goBack" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
            <ChevronLeft class="w-5 h-5 text-slate-600 dark:text-slate-400" />
          </button>
          <div>
            <h1 class="text-2xl lg:text-3xl font-black dark:text-white">Notifications</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Stay updated with your learning journey</p>
          </div>
        </div>
        <button 
          v-if="notifications.some(n => !n.read_at)"
          @click="markAllAsRead"
          class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 font-medium"
        >
          Mark all as read
        </button>
      </div>

      <!-- Notifications List -->
      <div class="space-y-3">
        <div v-if="isLoading" class="text-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="text-slate-500 mt-4">Loading notifications...</p>
        </div>

        <div 
          v-for="notification in notifications" 
          :key="notification.id"
          :class="[
            'bg-white dark:bg-slate-900 rounded-2xl border transition-all',
            !notification.read_at 
              ? 'border-blue-300 dark:border-blue-700 bg-blue-50/30 dark:bg-blue-900/10' 
              : 'border-slate-200 dark:border-slate-800'
          ]"
        >
          <div class="p-5">
            <div class="flex items-start gap-4">
              <!-- Icon -->
              <div :class="[
                'w-12 h-12 rounded-xl flex items-center justify-center',
                !notification.read_at ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-slate-100 dark:bg-slate-800'
              ]">
                <component 
                  :is="getNotificationIcon(notification.type)"
                  :class="[
                    'w-6 h-6',
                    !notification.read_at ? 'text-blue-600' : 'text-slate-400'
                  ]"
                />
              </div>
              
              <!-- Content -->
              <div class="flex-1">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <h3 class="text-base font-bold dark:text-white">{{ notification.title }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">{{ notification.message }}</p>
                    <p class="text-xs text-slate-400 mt-2">{{ formatDate(notification.created_at) }}</p>
                  </div>
                  <div class="flex items-center space-x-2">
                    <button 
                      v-if="!notification.read_at"
                      @click="markAsRead(notification.id)"
                      class="px-3 py-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium"
                    >
                      Mark read
                    </button>
                    <button 
                      @click="deleteNotification(notification.id)"
                      class="p-1.5 text-slate-400 hover:text-red-500 transition-colors"
                    >
                      <X class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
              
              <!-- Unread Indicator -->
              <div v-if="!notification.read_at" class="flex-shrink-0">
                <div class="w-2.5 h-2.5 bg-blue-600 rounded-full"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!isLoading && notifications.length === 0" class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl border">
          <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
            <Bell class="w-10 h-10 text-slate-400" />
          </div>
          <h3 class="text-lg font-bold dark:text-white mb-2">No notifications yet</h3>
          <p class="text-sm text-slate-500">When you have notifications, they'll appear here</p>
          <button @click="goBack" class="inline-block mt-6 px-6 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
            Back to Dashboard
          </button>
        </div>
      </div>
    </div>
  </div>
</template>