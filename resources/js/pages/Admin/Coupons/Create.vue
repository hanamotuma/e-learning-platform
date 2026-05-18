<script setup>
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ChevronLeft, Save, Tag } from 'lucide-vue-next'

const form = useForm({
    code: '',
    type: 'percentage',
    value: '',
    min_purchase: '',
    max_uses: '',
    starts_at: '',
    expires_at: '',
    is_active: true
})

const submit = () => {
    form.post('/admin/coupons', {
        preserveScroll: true,
        onSuccess: () => {
            router.get('/admin/coupons')
        },
        onError: (errors) => {
            alert('Error: ' + Object.values(errors).join(', '))
        }
    })
}

const goBack = () => {
    router.get('/admin/coupons')
}
</script>

<template>
    <Head title="Create Coupon | Admin" />
    
    <AdminLayout>
        <div class="p-6">
            <button @click="goBack" class="flex items-center gap-2 text-slate-600 dark:text-slate-400 mb-6 hover:text-blue-600 transition-colors">
                <ChevronLeft class="w-5 h-5" />
                Back to Coupons
            </button>

            <div class="max-w-2xl mx-auto">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8">
                        <div class="flex items-center gap-3">
                            <Tag class="w-8 h-8 text-white" />
                            <div>
                                <h1 class="text-2xl font-bold text-white">Create Coupon</h1>
                                <p class="text-blue-100 mt-1">Create a new discount coupon</p>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-medium mb-1">Coupon Code *</label>
                            <input 
                                v-model="form.code" 
                                type="text" 
                                class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900 uppercase"
                                placeholder="SAVE20"
                                required
                            />
                            <p v-if="form.errors.code" class="text-red-500 text-sm mt-1">{{ form.errors.code }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Discount Type</label>
                                <select v-model="form.type" class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900">
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed Amount (ETB)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Discount Value *</label>
                                <input 
                                    v-model="form.value" 
                                    type="number" 
                                    class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900"
                                    placeholder="20"
                                    required
                                />
                                <p v-if="form.errors.value" class="text-red-500 text-sm mt-1">{{ form.errors.value }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Min Purchase (ETB)</label>
                                <input 
                                    v-model="form.min_purchase" 
                                    type="number" 
                                    class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900"
                                    placeholder="0"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Max Uses</label>
                                <input 
                                    v-model="form.max_uses" 
                                    type="number" 
                                    class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900"
                                    placeholder="Unlimited"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Start Date</label>
                                <input 
                                    v-model="form.starts_at" 
                                    type="date" 
                                    class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Expiry Date</label>
                                <input 
                                    v-model="form.expires_at" 
                                    type="date" 
                                    class="w-full px-4 py-2.5 border rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-slate-900"
                                />
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <input 
                                v-model="form.is_active" 
                                type="checkbox" 
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label class="text-sm font-medium">Active</label>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="flex-1 px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <Save class="w-4 h-4" />
                                {{ form.processing ? 'Saving...' : 'Save Coupon' }}
                            </button>
                            <button 
                                type="button" 
                                @click="goBack"
                                class="px-6 py-3 border rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all font-medium"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>