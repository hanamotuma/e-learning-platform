<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Search, Eye, Ban, CheckCircle, Key, DollarSign, Plus, XCircle, UserPlus } from 'lucide-vue-next'

const props = defineProps({
  instructors: Object,
  filters: Object
})

const searchForm = ref({ search: props.filters?.search || '' })
const statusFilter = ref(props.filters?.status || '')

const searchInstructors = () => {
  router.get('/admin/instructors', { search: searchForm.value.search, status: statusFilter.value })
}

const toggleStatus = (instructor) => {
  if (confirm(`Are you sure you want to ${instructor.is_active ? 'suspend' : 'activate'} ${instructor.full_name}?`)) {
    router.post(`/admin/instructors/${instructor.id}/toggle-status`)
  }
}

const approveInstructor = (instructor) => {
  if (confirm(`Approve ${instructor.full_name} as an instructor?`)) {
    router.post(`/admin/instructors/${instructor.id}/approve`)
  }
}

const rejectInstructor = (instructor) => {
  const reason = prompt('Please provide a rejection reason:')
  if (reason) {
    router.post(`/admin/instructors/${instructor.id}/reject`, { reason })
  }
}

const formatDate = (date) => new Date(date).toLocaleDateString()
</script>

<template>
  <Head title="Instructor Management | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold dark:text-white">Instructor Management</h1>
          <p class="text-slate-500 mt-1">Manage all instructors and their applications</p>
        </div>
        <!-- FIXED: Proper Link to create page -->
        <Link href="/admin/instructors/create" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center gap-2">
          <UserPlus class="w-4 h-4" />
          Add Instructor
        </Link>
      </div>
      
      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border p-4 mb-6">
        <div class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-[200px]">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input v-model="searchForm.search" @keyup.enter="searchInstructors" type="text" placeholder="Search by name, email..." class="w-full pl-10 pr-4 py-2 border rounded-lg dark:bg-slate-900" />
            </div>
          </div>
          <select v-model="statusFilter" @change="searchInstructors" class="px-4 py-2 border rounded-lg dark:bg-slate-900">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="pending">Pending Approval</option>
          </select>
          <button @click="searchInstructors" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
        </div>
      </div>
      
      <!-- Instructors Table -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border overflow-hidden">
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-medium">Instructor</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Email</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Courses</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Joined</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="instructor in instructors.data" :key="instructor.id">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-r from-orange-500 to-red-600 flex items-center justify-center text-white font-bold">
                    {{ instructor.full_name?.charAt(0) }}
                  </div>
                  <div>
                    <p class="font-medium">{{ instructor.full_name }}</p>
                    <p class="text-sm text-slate-500">ID: #{{ instructor.id }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">{{ instructor.email }}</td>
              <td class="px-6 py-4">{{ instructor.courses_count || 0 }}</td>
              <td class="px-6 py-4">{{ formatDate(instructor.created_at) }}</td>
              <td class="px-6 py-4">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  !instructor.is_approved ? 'bg-yellow-100 text-yellow-700' :
                  instructor.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                ]">
                  {{ !instructor.is_approved ? 'Pending' : (instructor.is_active ? 'Active' : 'Suspended') }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex gap-2">
                  <Link :href="`/admin/instructors/${instructor.id}`" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                    <Eye class="w-4 h-4" />
                  </Link>
                  <button v-if="!instructor.is_approved" @click="approveInstructor(instructor)" class="p-2 text-green-600 hover:bg-green-50 rounded-lg">
                    <CheckCircle class="w-4 h-4" />
                  </button>
                  <button v-if="!instructor.is_approved" @click="rejectInstructor(instructor)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                    <XCircle class="w-4 h-4" />
                  </button>
                  <button v-if="instructor.is_approved" @click="toggleStatus(instructor)" class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg">
                    <Ban class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="p-4 border-t">
          <div class="flex justify-between items-center">
            <p class="text-sm text-slate-500">Showing {{ instructors.from || 0 }} to {{ instructors.to || 0 }} of {{ instructors.total }} results</p>
            <div class="flex gap-2">
              <Link v-for="link in instructors.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 rounded-lg border hover:bg-slate-50" :class="{ 'bg-blue-600 text-white border-blue-600': link.active }" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>