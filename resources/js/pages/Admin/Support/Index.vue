<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { 
  Search, MessageCircle, CheckCircle, Clock, AlertCircle,
  Send, Eye, Filter, ChevronDown, XCircle, Reply, User
} from 'lucide-vue-next'
import axios from 'axios'

const tickets = ref([])
const isLoading = ref(true)
const selectedTicket = ref(null)
const showReplyModal = ref(false)
const replyMessage = ref('')
const replyTicketId = ref(null)
const stats = ref({
  open: 0,
  in_progress: 0,
  resolved: 0,
  urgent: 0
})
const searchQuery = ref('')
const priorityFilter = ref('all')
const statusFilter = ref('all')

const fetchTickets = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('/admin/support/data', {
      params: {
        search: searchQuery.value,
        priority: priorityFilter.value,
        status: statusFilter.value
      }
    })
    tickets.value = response.data.tickets || []
    stats.value = response.data.stats || stats.value
  } catch (error) {
    console.error('Error fetching tickets:', error)
  } finally {
    isLoading.value = false
  }
}

const openReplyModal = (ticket) => {
  replyTicketId.value = ticket.id
  replyMessage.value = ''
  showReplyModal.value = true
}

const sendReply = async () => {
  if (!replyMessage.value.trim()) {
    alert('Please enter a reply message')
    return
  }
  
  try {
    await axios.post(`/admin/support/${replyTicketId.value}/reply`, {
      message: replyMessage.value
    })
    showReplyModal.value = false
    replyMessage.value = ''
    await fetchTickets()
    alert('Reply sent successfully')
  } catch (error) {
    alert('Error sending reply')
  }
}

const updateTicketStatus = async (ticketId, status) => {
  try {
    await axios.put(`/admin/support/${ticketId}/status`, { status })
    await fetchTickets()
  } catch (error) {
    alert('Error updating status')
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getPriorityColor = (priority) => {
  const colors = {
    low: 'bg-blue-100 text-blue-700',
    medium: 'bg-yellow-100 text-yellow-700',
    high: 'bg-orange-100 text-orange-700',
    urgent: 'bg-red-100 text-red-700'
  }
  return colors[priority] || 'bg-gray-100 text-gray-700'
}

const getStatusColor = (status) => {
  const colors = {
    open: 'bg-red-100 text-red-700',
    in_progress: 'bg-yellow-100 text-yellow-700',
    resolved: 'bg-green-100 text-green-700',
    closed: 'bg-gray-100 text-gray-700'
  }
  return colors[status] || 'bg-gray-100 text-gray-700'
}

onMounted(() => {
  fetchTickets()
})
</script>

<template>
  <Head title="Support Tickets | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-3xl font-bold dark:text-white">Support Tickets</h1>
        <p class="text-slate-500 mt-1">Manage student and instructor support requests</p>
      </div>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Open Tickets</p>
          <p class="text-2xl font-bold text-red-600">{{ stats.open }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">In Progress</p>
          <p class="text-2xl font-bold text-yellow-600">{{ stats.in_progress }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Resolved</p>
          <p class="text-2xl font-bold text-green-600">{{ stats.resolved }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
          <p class="text-xs text-slate-500">Urgent</p>
          <p class="text-2xl font-bold text-orange-600">{{ stats.urgent }}</p>
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
                @keyup.enter="fetchTickets" 
                type="text" 
                placeholder="Search by subject, user email..." 
                class="w-full pl-10 pr-4 py-2 border rounded-lg dark:bg-slate-900" 
              />
            </div>
          </div>
          <select v-model="statusFilter" @change="fetchTickets" class="px-4 py-2 border rounded-lg dark:bg-slate-900">
            <option value="all">All Status</option>
            <option value="open">Open</option>
            <option value="in_progress">In Progress</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
          </select>
          <select v-model="priorityFilter" @change="fetchTickets" class="px-4 py-2 border rounded-lg dark:bg-slate-900">
            <option value="all">All Priority</option>
            <option value="urgent">Urgent</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
          <button @click="fetchTickets" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
        </div>
      </div>
      
      <!-- Tickets Table -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-700/50">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-medium">Ticket</th>
                <th class="px-6 py-3 text-left text-sm font-medium">User</th>
                <th class="px-6 py-3 text-left text-sm font-medium">Priority</th>
                <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
                <th class="px-6 py-3 text-left text-sm font-medium">Created</th>
                <th class="px-6 py-3 text-left text-sm font-medium">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="ticket in tickets" :key="ticket.id">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium">{{ ticket.subject }}</p>
                    <p class="text-sm text-slate-500">#{{ ticket.id }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <User class="w-4 h-4 text-slate-400" />
                    <span>{{ ticket.user?.email || 'Unknown' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="['px-2 py-1 text-xs rounded-full', getPriorityColor(ticket.priority)]">
                    {{ ticket.priority }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span :class="['px-2 py-1 text-xs rounded-full', getStatusColor(ticket.status)]">
                    {{ ticket.status?.replace('_', ' ') }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">{{ formatDate(ticket.created_at) }}</td>
                <td class="px-6 py-4">
                  <div class="flex gap-2">
                    <Link :href="`/admin/support/${ticket.id}`" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                      <Eye class="w-4 h-4" />
                    </Link>
                    <button @click="openReplyModal(ticket)" class="p-2 text-green-600 hover:bg-green-50 rounded-lg">
                      <Reply class="w-4 h-4" />
                    </button>
                    <select @change="updateTicketStatus(ticket.id, $event.target.value)" :value="ticket.status" class="px-2 py-1 text-sm border rounded-lg">
                      <option value="open">Open</option>
                      <option value="in_progress">In Progress</option>
                      <option value="resolved">Resolved</option>
                      <option value="closed">Closed</option>
                    </select>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="p-4 border-t">
          <div class="flex justify-between items-center">
            <p class="text-sm text-slate-500">Showing {{ tickets.length }} tickets</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Reply Modal -->
    <div v-if="showReplyModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-lg">
        <div class="p-6 border-b">
          <h3 class="text-xl font-bold">Reply to Ticket</h3>
        </div>
        <div class="p-6">
          <textarea 
            v-model="replyMessage" 
            rows="5" 
            class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" 
            placeholder="Type your response here..."
          ></textarea>
        </div>
        <div class="p-6 border-t flex gap-3">
          <button @click="showReplyModal = false" class="flex-1 px-4 py-2 border rounded-lg hover:bg-slate-50">Cancel</button>
          <button @click="sendReply" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2">
            <Send class="w-4 h-4" />
            Send Reply
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>