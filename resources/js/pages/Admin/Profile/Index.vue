<script setup>
import { ref, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { User, Mail, Calendar, Shield, ArrowLeft, Edit } from 'lucide-vue-next'

const props = defineProps({
    admin: Object
})

const goBack = () => {
    router.get('/admin/dashboard')
}

const formatDate = (date) => {
    if (!date) return 'Not available'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const getRoleBadge = (role) => {
    const badges = {
        super_admin: 'bg-purple-100 text-purple-700',
        manager: 'bg-blue-100 text-blue-700',
        support: 'bg-green-100 text-green-700'
    }
    return badges[role] || 'bg-gray-100 text-gray-700'
}
</script>

<template>
    <Head title="Admin Profile | LearnHub" />
    
    <AdminLayout>
        <div class="p-6">
            <button @click="goBack" class="flex items-center gap-2 text-slate-600 dark:text-slate-400 mb-6 hover:text-blue-600 transition-colors">
                <ArrowLeft class="w-5 h-5" />
                Back to Dashboard
            </button>

            <div class="max-w-2xl mx-auto">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-8 text-center">
                        <div class="w-24 h-24 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-4xl font-bold text-white">{{ admin?.name?.charAt(0) || 'A' }}</span>
                        </div>
                        <h1 class="text-2xl font-bold text-white">{{ admin?.name || 'Administrator' }}</h1>
                        <p class="text-blue-100 mt-1">Administrator Account</p>
                    </div>

                    <!-- Profile Info -->
                    <div class="p-6 space-y-5">
                        <div class="flex items-center gap-3 pb-3 border-b">
                            <User class="w-5 h-5 text-blue-600" />
                            <div>
                                <p class="text-xs text-slate-500">Full Name</p>
                                <p class="font-medium dark:text-white">{{ admin?.name || 'Not set' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pb-3 border-b">
                            <Mail class="w-5 h-5 text-blue-600" />
                            <div>
                                <p class="text-xs text-slate-500">Email Address</p>
                                <p class="font-medium dark:text-white">{{ admin?.email || 'Not set' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pb-3 border-b">
                            <Shield class="w-5 h-5 text-blue-600" />
                            <div>
                                <p class="text-xs text-slate-500">Role</p>
                                <span :class="['px-2 py-1 text-xs rounded-full inline-block', getRoleBadge(admin?.role)]">
                                    {{ admin?.role || 'Administrator' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pb-3 border-b">
                            <Calendar class="w-5 h-5 text-blue-600" />
                            <div>
                                <p class="text-xs text-slate-500">Joined Date</p>
                                <p class="font-medium dark:text-white">{{ formatDate(admin?.created_at) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <Calendar class="w-5 h-5 text-blue-600" />
                            <div>
                                <p class="text-xs text-slate-500">Last Login</p>
                                <p class="font-medium dark:text-white">{{ formatDate(admin?.last_login_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>