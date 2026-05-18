<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import InstructorLayout from '@/layouts/InstructorLayout.vue'
import { DollarSign, TrendingUp, CreditCard, Wallet, Calendar } from 'lucide-vue-next'

const props = defineProps({
    totalEarnings: Number,
    platformFee: Number,
    availableBalance: Number,
    transactions: Object
})

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US').format(amount || 0) + ' ETB'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}
</script>

<template>
    <Head title="Earnings | Instructor" />
    
    <InstructorLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-3xl font-bold dark:text-white">Earnings Overview</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Track your revenue and payouts</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Total Revenue</p>
                            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(totalEarnings) }}</p>
                        </div>
                        <DollarSign class="w-10 h-10 text-green-500 opacity-50" />
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Platform Fee (30%)</p>
                            <p class="text-2xl font-bold text-orange-600">{{ formatCurrency(platformFee) }}</p>
                        </div>
                        <TrendingUp class="w-10 h-10 text-orange-500 opacity-50" />
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Available Balance (70%)</p>
                            <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(availableBalance) }}</p>
                        </div>
                        <Wallet class="w-10 h-10 text-blue-500 opacity-50" />
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border overflow-hidden">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold">Transaction History</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium">Course</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Student</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Amount</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Date</th>
                                <th class="px-6 py-3 text-left text-sm font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="transaction in transactions?.data" :key="transaction.id">
                                <td class="px-6 py-4">{{ transaction.course?.title }}</td>
                                <td class="px-6 py-4">{{ transaction.user?.name }}</td>
                                <td class="px-6 py-4 font-bold text-green-600">{{ formatCurrency(transaction.amount) }}</td>
                                <td class="px-6 py-4">{{ formatDate(transaction.created_at) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Completed</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </InstructorLayout>
</template>