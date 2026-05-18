<!-- resources/js/Pages/Admin/Students/Index.vue -->
<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Search, Eye, Ban, CheckCircle, Key, DollarSign } from 'lucide-vue-next'

const props = defineProps({
  students: Object,
  filters: Object
})

const searchForm = ref({ search: props.filters?.search || '' })
const statusFilter = ref(props.filters?.status || '')

const searchStudents = () => {
  router.get('/admin/students', { search: searchForm.value.search, status: statusFilter.value })
}

const toggleStatus = (student) => {
  if (confirm(`Are you sure you want to ${student.is_active ? 'suspend' : 'activate'} ${student.full_name}?`)) {
    router.post(`/admin/students/${student.id}/toggle-status`)
  }
}

const resetPassword = (student) => {
  const newPassword = prompt(`Enter new password for ${student.full_name}:`)
  if (newPassword && newPassword.length >= 8) {
    router.post(`/admin/students/${student.id}/reset-password`, { password: newPassword, password_confirmation: newPassword })
  } else if (newPassword) {
    alert('Password must be at least 8 characters')
  }
}

const issueRefund = (student) => {
  alert('Refund feature - would show payment selection modal')
}

const formatDate = (date) => new Date(date).toLocaleDateString()
</script>

<template>
  <Head title="Student Management | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold dark:text-white">Student Management</h1>
          <p class="text-slate-500 mt-1">Manage all registered students</p>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border p-4 mb-6">
        <div class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-[200px]">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input v-model="searchForm.search" @keyup.enter="searchStudents" type="text" placeholder="Search by name, email..." class="w-full pl-10 pr-4 py-2 border rounded-lg dark:bg-slate-900" />
            </div>
          </div>
          <select v-model="statusFilter" @change="searchStudents" class="px-4 py-2 border rounded-lg dark:bg-slate-900">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
          </select>
          <button @click="searchStudents" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
        </div>
      </div>
      
      <!-- Students Table -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border overflow-hidden">
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-medium">Student</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Email</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Courses</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Joined</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="student in students.data" :key="student.id">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                    {{ student.full_name?.charAt(0) }}
                  </div>
                  <div>
                    <p class="font-medium">{{ student.full_name }}</p>
                    <p class="text-sm text-slate-500">{{ student.username }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">{{ student.email }}</td>
              <td class="px-6 py-4">{{ student.enrollments_count || 0 }}</td>
              <td class="px-6 py-4">{{ formatDate(student.created_at) }}</td>
              <td class="px-6 py-4">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  student.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                ]">
                  {{ student.is_active ? 'Active' : 'Suspended' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex gap-2">
                  <Link :href="`/admin/students/${student.id}`" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                    <Eye class="w-4 h-4" />
                  </Link>
                  <button @click="toggleStatus(student)" class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg">
                    <Ban class="w-4 h-4" />
                  </button>
                  <button @click="resetPassword(student)" class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg">
                    <Key class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="p-4 border-t">
          <div class="flex justify-between items-center">
            <p class="text-sm text-slate-500">Showing {{ students.from || 0 }} to {{ students.to || 0 }} of {{ students.total }} results</p>
            <div class="flex gap-2">
              <Link v-for="link in students.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 rounded-lg border hover:bg-slate-50" :class="{ 'bg-blue-600 text-white border-blue-600': link.active }" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>