<!-- resources/js/Pages/Admin/Withdrawals/Index.vue -->
<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { CheckCircle, XCircle, Clock, DollarSign, AlertCircle } from 'lucide-vue-next'

const props = defineProps({
  withdrawals: Object,
  stats: Object,
  filters: Object
})

const statusFilter = ref(props.filters?.status || '')

const filterWithdrawals = () => {
  router.get('/admin/withdrawals', { status: statusFilter.value })
}

const approveWithdrawal = (withdrawal) => {
  if (confirm(`Approve withdrawal of ${formatCurrency(withdrawal.amount)} for ${withdrawal.user?.full_name}?`)) {
    router.post(`/admin/withdrawals/${withdrawal.id}/approve`)
  }
}

const markAsPaid = (withdrawal) => {
  if (confirm(`Mark this withdrawal as paid?`)) {
    router.post(`/admin/withdrawals/${withdrawal.id}/paid`)
  }
}

const rejectWithdrawal = (withdrawal) => {
  const reason = prompt('Please provide a rejection reason:')
  if (reason) {
    router.post(`/admin/withdrawals/${withdrawal.id}/reject`, { reason })
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US').format(amount || 0) + ' ETB'
}

const formatDate = (date) => new Date(date).toLocaleDateString()
</script>

<template>
  <Head title="Withdrawal Management | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-3xl font-bold dark:text-white">Withdrawal Management</h1>
        <p class="text-slate-500 mt-1">Approve and manage instructor payout requests</p>
      </div>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Pending Withdrawals</p>
              <p class="text-2xl font-bold text-yellow-600">{{ formatCurrency(stats?.total_requested) }}</p>
            </div>
            <Clock class="w-10 h-10 text-yellow-500 opacity-50" />
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Total Paid Out</p>
              <p class="text-2xl font-bold text-green-600">{{ formatCurrency(stats?.total_paid) }}</p>
            </div>
            <DollarSign class="w-10 h-10 text-green-500 opacity-50" />
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-slate-500">Pending Requests</p>
              <p class="text-2xl font-bold text-orange-600">{{ stats?.pending_count || 0 }}</p>
            </div>
            <AlertCircle class="w-10 h-10 text-orange-500 opacity-50" />
          </div>
        </div>
      </div>
      
      <!-- Filter -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border p-4 mb-6">
        <div class="flex gap-4">
          <select v-model="statusFilter" @change="filterWithdrawals" class="px-4 py-2 border rounded-lg dark:bg-slate-900">
            <option value="">All Requests</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="paid">Paid</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
      </div>
      
      <!-- Withdrawals Table -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border overflow-hidden">
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
              <th class="px-6 py-3 text-left text-sm font-medium">Instructor</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Amount</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Payment Method</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Requested</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
              <th class="px-6 py-3 text-left text-sm font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="withdrawal in withdrawals.data" :key="withdrawal.id">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-r from-orange-500 to-red-600 flex items-center justify-center text-white font-bold">
                    {{ withdrawal.user?.full_name?.charAt(0) }}
                  </div>
                  <div>
                    <p class="font-medium">{{ withdrawal.user?.full_name }}</p>
                    <p class="text-sm text-slate-500">{{ withdrawal.user?.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 font-bold text-emerald-600">{{ formatCurrency(withdrawal.amount) }}</td>
              <td class="px-6 py-4">{{ withdrawal.payment_method || 'Bank Transfer' }}</td>
              <td class="px-6 py-4">{{ formatDate(withdrawal.created_at) }}</td>
              <td class="px-6 py-4">
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  withdrawal.status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                  withdrawal.status === 'approved' ? 'bg-blue-100 text-blue-700' :
                  withdrawal.status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                ]">
                  {{ withdrawal.status }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex gap-2">
                  <button v-if="withdrawal.status === 'pending'" @click="approveWithdrawal(withdrawal)" class="p-2 text-green-600 hover:bg-green-50 rounded-lg">
                    <CheckCircle class="w-4 h-4" />
                  </button>
                  <button v-if="withdrawal.status === 'approved'" @click="markAsPaid(withdrawal)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                    <DollarSign class="w-4 h-4" />
                  </button>
                  <button v-if="withdrawal.status === 'pending'" @click="rejectWithdrawal(withdrawal)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                    <XCircle class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="p-4 border-t">
          <div class="flex justify-between items-center">
            <p class="text-sm text-slate-500">Showing {{ withdrawals.from || 0 }} to {{ withdrawals.to || 0 }} of {{ withdrawals.total }} results</p>
            <div class="flex gap-2">
              <Link v-for="link in withdrawals.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 rounded-lg border hover:bg-slate-50" :class="{ 'bg-blue-600 text-white border-blue-600': link.active }" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>