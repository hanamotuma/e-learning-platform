<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Search, Eye, RefreshCw, Download, DollarSign, TrendingUp } from 'lucide-vue-next'

const props = defineProps({
  payments: Object,
  stats: Object,
  filters: Object
})

const searchForm = ref({ search: props.filters?.search || '' })
const statusFilter = ref(props.filters?.status || '')

const searchPayments = () => {
  router.get('/admin/payments', { search: searchForm.value.search, status: statusFilter.value })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US').format(amount || 0) + ' ETB'
}

const formatDate = (date) => new Date(date).toLocaleDateString()
</script>

<template>
  <Head title="Payment Management | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold dark:text-white">Payment Management</h1>
          <p class="text-slate-500 mt-1">Track all transactions and platform revenue</p>
        </div>
        <Link href="/admin/payments/export" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 flex items-center gap-2">
          <Download class="w-4 h-4" />
          Export Reports
        </Link>
      </div>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Total Revenue</p>
              <p class="text-2xl font-bold text-emerald-600">{{ formatCurrency(stats?.total_revenue) }}</p>
            </div>
            <DollarSign class="w-10 h-10 text-emerald-500 opacity-50" />
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Platform Commission (10%)</p>
              <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(stats?.platform_commission) }}</p>
            </div>
            <TrendingUp class="w-10 h-10 text-blue-500 opacity-50" />
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Instructor Payouts (90%)</p>
              <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(stats?.instructor_payout) }}</p>
            </div>
            <RefreshCw class="w-10 h-10 text-purple-500 opacity-50" />
          </div>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border p-4 mb-6">
        <div class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-[200px]">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
              <input v-model="searchForm.search" @keyup.enter="searchPayments" type="text" placeholder="Search by transaction ID..." class="w-full pl-10 pr-4 py-2 border rounded-lg dark:bg-slate-900" />
            </div>
          </div>
          <select v-model="statusFilter" @change="searchPayments" class="px-4 py-2 border rounded-lg dark:bg-slate-900">
            <option value="">All Status</option>
            <option value="completed">Completed</option>
            <option value="pending">Pending</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
          </select>
          <button @click="searchPayments" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Search</button>
        </div>
      </div>
      
      <!-- Payments Table -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border overflow-hidden">
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-medium">Transaction ID</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Student</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Course</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Amount</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Commission</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Date</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="payment in payments.data" :key="payment.id">
              <td class="px-6 py-4 font-mono text-sm">{{ payment.transaction_id?.substring(0, 12) }}...</td>
              <td class="px-6 py-4">{{ payment.user?.email }}</td>
              <td class="px-6 py-4">{{ payment.course?.title }}</td>
              <td class="px-6 py-4 font-bold">{{ formatCurrency(payment.amount) }}</td>
              <td class="px-6 py-4">{{ formatCurrency(payment.amount * 0.1) }}</td>
              <td class="px-6 py-4">{{ formatDate(payment.created_at) }}</td>
              <td class="px-6 py-4">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  payment.status === 'completed' ? 'bg-green-100 text-green-700' :
                  payment.status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                  payment.status === 'refunded' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'
                ]">
                  {{ payment.status }}
                </span>
              </td>
              <td class="px-6 py-4">
                <Link :href="`/admin/payments/${payment.id}`" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                  <Eye class="w-4 h-4" />
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="p-4 border-t">
          <div class="flex justify-between items-center">
            <p class="text-sm text-slate-500">Showing {{ payments.from || 0 }} to {{ payments.to || 0 }} of {{ payments.total }} results</p>
            <div class="flex gap-2">
              <Link v-for="link in payments.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 rounded-lg border hover:bg-slate-50" :class="{ 'bg-blue-600 text-white border-blue-600': link.active }" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>