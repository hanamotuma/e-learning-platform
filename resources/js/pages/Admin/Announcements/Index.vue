<script setup>
import { ref, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { 
  Plus, Edit2, Trash2, Send, Calendar, Users, 
  Eye, Megaphone, Bell, Mail, CheckCircle, Clock
} from 'lucide-vue-next'
import axios from 'axios'

const announcements = ref([])
const isLoading = ref(true)
const showModal = ref(false)
const editingAnnouncement = ref(null)
const form = ref({
  title: '',
  content: '',
  type: 'general',
  send_email: false,
  send_notification: true,
  target_audience: 'all',
  publish_at: '',
  expires_at: ''
})
const stats = ref({
  total: 0,
  published: 0,
  scheduled: 0,
  expired: 0
})

const fetchAnnouncements = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('/admin/announcements/data')
    announcements.value = response.data.announcements || []
    stats.value = response.data.stats || stats.value
  } catch (error) {
    console.error('Error fetching announcements:', error)
  } finally {
    isLoading.value = false
  }
}

const saveAnnouncement = async () => {
  if (!form.value.title || !form.value.content) {
    alert('Please fill in all required fields')
    return
  }
  
  try {
    if (editingAnnouncement.value) {
      await axios.put(`/admin/announcements/${editingAnnouncement.value.id}`, form.value)
    } else {
      await axios.post('/admin/announcements', form.value)
    }
    showModal.value = false
    editingAnnouncement.value = null
    form.value = {
      title: '',
      content: '',
      type: 'general',
      send_email: false,
      send_notification: true,
      target_audience: 'all',
      publish_at: '',
      expires_at: ''
    }
    await fetchAnnouncements()
    alert(editingAnnouncement.value ? 'Announcement updated!' : 'Announcement created!')
  } catch (error) {
    alert('Error saving announcement')
  }
}

const editAnnouncement = (announcement) => {
  editingAnnouncement.value = announcement
  form.value = { ...announcement }
  showModal.value = true
}

const deleteAnnouncement = async (announcement) => {
  if (confirm(`Delete announcement "${announcement.title}"?`)) {
    try {
      await axios.delete(`/admin/announcements/${announcement.id}`)
      await fetchAnnouncements()
    } catch (error) {
      alert('Error deleting announcement')
    }
  }
}

const publishNow = async (announcement) => {
  if (confirm(`Publish "${announcement.title}" now?`)) {
    try {
      await axios.post(`/admin/announcements/${announcement.id}/publish`)
      await fetchAnnouncements()
    } catch (error) {
      alert('Error publishing announcement')
    }
  }
}

const formatDate = (date) => {
  if (!date) return 'Not scheduled'
  return new Date(date).toLocaleDateString()
}

const getTypeIcon = (type) => {
  const icons = {
    general: Megaphone,
    important: Bell,
    update: CheckCircle,
    maintenance: Clock
  }
  return icons[type] || Megaphone
}

const getAudienceLabel = (audience) => {
  const labels = {
    all: 'All Users',
    students: 'Students Only',
    instructors: 'Instructors Only',
    both: 'Students & Instructors'
  }
  return labels[audience] || 'All Users'
}

onMounted(() => {
  fetchAnnouncements()
})
</script>

<template>
  <Head title="Announcements | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold dark:text-white">Platform Announcements</h1>
          <p class="text-slate-500 mt-1">Create and manage announcements for users</p>
        </div>
        <button @click="showModal = true" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center gap-2">
          <Plus class="w-4 h-4" />
          New Announcement
        </button>
      </div>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Total</p>
          <p class="text-2xl font-bold">{{ stats.total }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Published</p>
          <p class="text-2xl font-bold text-green-600">{{ stats.published }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Scheduled</p>
          <p class="text-2xl font-bold text-yellow-600">{{ stats.scheduled }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Expired</p>
          <p class="text-2xl font-bold text-gray-600">{{ stats.expired }}</p>
        </div>
      </div>
      
      <!-- Announcements List -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600 mx-auto"></div>
        <p class="text-slate-500 mt-4">Loading announcements...</p>
      </div>
      
      <div v-else class="space-y-4">
        <div v-for="announcement in announcements" :key="announcement.id" class="bg-white dark:bg-slate-800 rounded-xl border p-6 hover:shadow-md transition-all">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                <component :is="getTypeIcon(announcement.type)" class="w-6 h-6 text-white" />
              </div>
              <div>
                <h3 class="text-lg font-bold dark:text-white">{{ announcement.title }}</h3>
                <div class="flex flex-wrap gap-3 mt-1 text-sm text-slate-500">
                  <span class="flex items-center gap-1">
                    <Calendar class="w-3 h-3" />
                    {{ formatDate(announcement.publish_at) }}
                  </span>
                  <span class="flex items-center gap-1">
                    <Users class="w-3 h-3" />
                    {{ getAudienceLabel(announcement.target_audience) }}
                  </span>
                </div>
              </div>
            </div>
            <div class="flex gap-2">
              <button v-if="announcement.status === 'scheduled'" @click="publishNow(announcement)" class="p-2 text-green-600 hover:bg-green-50 rounded-lg">
                <Send class="w-4 h-4" />
              </button>
              <button @click="editAnnouncement(announcement)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                <Edit2 class="w-4 h-4" />
              </button>
              <button @click="deleteAnnouncement(announcement)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
          
          <div class="pl-16">
            <p class="text-slate-600 dark:text-slate-300 mb-3">{{ announcement.content }}</p>
            <div class="flex gap-2">
              <span :class="[
                'px-2 py-1 text-xs rounded-full',
                announcement.status === 'published' ? 'bg-green-100 text-green-700' :
                announcement.status === 'scheduled' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700'
              ]">
                {{ announcement.status }}
              </span>
              <span v-if="announcement.send_email" class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 flex items-center gap-1">
                <Mail class="w-3 h-3" /> Email Sent
              </span>
              <span v-if="announcement.send_notification" class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700 flex items-center gap-1">
                <Bell class="w-3 h-3" /> Push
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
          <h3 class="text-xl font-bold">{{ editingAnnouncement ? 'Edit Announcement' : 'Create Announcement' }}</h3>
        </div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Title *</label>
            <input v-model="form.title" type="text" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" placeholder="Announcement title" />
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Content *</label>
            <textarea v-model="form.content" rows="4" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" placeholder="Announcement details..."></textarea>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Type</label>
              <select v-model="form.type" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900">
                <option value="general">General</option>
                <option value="important">Important</option>
                <option value="update">Platform Update</option>
                <option value="maintenance">Maintenance</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Target Audience</label>
              <select v-model="form.target_audience" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900">
                <option value="all">All Users</option>
                <option value="students">Students Only</option>
                <option value="instructors">Instructors Only</option>
                <option value="both">Students & Instructors</option>
              </select>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Publish Date</label>
              <input v-model="form.publish_at" type="datetime-local" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Expiry Date (Optional)</label>
              <input v-model="form.expires_at" type="datetime-local" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
            </div>
          </div>
          
          <div class="space-y-2">
            <label class="flex items-center gap-3">
              <input v-model="form.send_notification" type="checkbox" class="w-4 h-4" />
              <span class="text-sm">Send push notification to users</span>
            </label>
            <label class="flex items-center gap-3">
              <input v-model="form.send_email" type="checkbox" class="w-4 h-4" />
              <span class="text-sm">Send email to users</span>
            </label>
          </div>
        </div>
        <div class="p-6 border-t flex gap-3">
          <button @click="showModal = false" class="flex-1 px-4 py-2 border rounded-lg hover:bg-slate-50">Cancel</button>
          <button @click="saveAnnouncement" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>