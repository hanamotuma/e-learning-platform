<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Plus, Edit2, Trash2, Copy, Tag, CheckCircle, XCircle, Calendar } from 'lucide-vue-next'
import axios from 'axios'

const coupons = ref([])
const isLoading = ref(true)
const stats = ref({
    total: 0,
    active: 0,
    expired: 0,
    total_used: 0
})

const fetchCoupons = async () => {
    isLoading.value = true
    try {
        const response = await axios.get('/admin/coupons/data')
        coupons.value = response.data.coupons || []
        stats.value = response.data.stats || stats.value
    } catch (error) {
        console.error('Error fetching coupons:', error)
    } finally {
        isLoading.value = false
    }
}

const deleteCoupon = async (id) => {
    if (confirm('Delete this coupon? This action cannot be undone.')) {
        try {
            await axios.delete(`/admin/coupons/${id}`)
            await fetchCoupons()
        } catch (error) {
            alert('Error deleting coupon')
        }
    }
}

const copyCode = (code) => {
    navigator.clipboard.writeText(code)
    alert('Coupon code copied!')
}

const formatDate = (date) => {
    if (!date) return 'No expiry'
    return new Date(date).toLocaleDateString()
}

const isExpired = (expiresAt) => {
    if (!expiresAt) return false
    return new Date(expiresAt) < new Date()
}

onMounted(() => {
    fetchCoupons()
})
</script>

<template>
    <Head title="Coupon Management | Admin" />
    
    <AdminLayout>
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold dark:text-white">Coupon Management</h1>
                    <p class="text-slate-500 mt-1">Create and manage discount coupons</p>
                </div>
                <Link href="/admin/coupons/create" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center gap-2">
                    <Plus class="w-4 h-4" />
                    Create Coupon
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Total Coupons</p>
                    <p class="text-2xl font-bold">{{ stats.total }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Active</p>
                    <p class="text-2xl font-bold text-green-600">{{ stats.active }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Expired</p>
                    <p class="text-2xl font-bold text-red-600">{{ stats.expired }}</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border">
                    <p class="text-xs text-slate-500">Total Used</p>
                    <p class="text-2xl font-bold text-purple-600">{{ stats.total_used }}</p>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600 mx-auto"></div>
                <p class="text-slate-500 mt-4">Loading coupons...</p>
            </div>

            <!-- Coupons Grid -->
            <div v-else-if="coupons.length === 0" class="bg-white dark:bg-slate-800 rounded-xl border p-12 text-center">
                <Tag class="w-16 h-16 text-slate-300 mx-auto mb-4" />
                <p class="text-slate-500 mb-2">No coupons created yet</p>
                <Link href="/admin/coupons/create" class="inline-block px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Create First Coupon
                </Link>
            </div>

            <!-- Coupons List -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="coupon in coupons" :key="coupon.id" class="bg-white dark:bg-slate-800 rounded-xl border hover:shadow-lg transition-all overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <Tag class="w-6 h-6 text-blue-600" />
                                <span class="text-xl font-mono font-bold">{{ coupon.code }}</span>
                            </div>
                            <div class="flex gap-2">
                                <button @click="copyCode(coupon.code)" class="p-2 text-slate-400 hover:text-blue-600 rounded-lg transition-colors" title="Copy code">
                                    <Copy class="w-4 h-4" />
                                </button>
                                <Link :href="`/admin/coupons/${coupon.id}/edit`" class="p-2 text-slate-400 hover:text-green-600 rounded-lg transition-colors" title="Edit">
                                    <Edit2 class="w-4 h-4" />
                                </Link>
                                <button @click="deleteCoupon(coupon.id)" class="p-2 text-slate-400 hover:text-red-600 rounded-lg transition-colors" title="Delete">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="text-3xl font-bold text-blue-600">
                                <span v-if="coupon.type === 'percentage'">{{ coupon.value }}% OFF</span>
                                <span v-else>{{ coupon.value }} ETB OFF</span>
                            </div>
                            <p class="text-sm text-slate-500 mt-1">
                                Min purchase: {{ coupon.min_purchase || 0 }} ETB
                            </p>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Used</span>
                                <span>{{ coupon.used_count || 0 }} / {{ coupon.max_uses || '∞' }}</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" :style="{ width: ((coupon.used_count || 0) / (coupon.max_uses || 1) * 100) + '%' }"></div>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Valid</span>
                                <span class="text-xs">
                                    {{ formatDate(coupon.starts_at) }} - {{ formatDate(coupon.expires_at) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t flex items-center justify-between">
                            <span :class="[
                                'px-2 py-1 text-xs rounded-full flex items-center gap-1',
                                coupon.is_active && !isExpired(coupon.expires_at) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                            ]">
                                <CheckCircle v-if="coupon.is_active && !isExpired(coupon.expires_at)" class="w-3 h-3" />
                                <XCircle v-else class="w-3 h-3" />
                                {{ coupon.is_active && !isExpired(coupon.expires_at) ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-xs text-slate-400 flex items-center gap-1">
                                <Calendar class="w-3 h-3" />
                                Created: {{ formatDate(coupon.created_at) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>