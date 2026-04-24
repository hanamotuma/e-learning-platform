<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Bell, BookOpen, FileText, Award, MessageCircle, Star, X, ChevronLeft } from 'lucide-vue-next'

const props = defineProps({
    notifications: {
        type: Object,
        required: true
    },
    unreadCount: {
        type: Number,
        default: 0
    }
})

const markAsRead = (id) => {
    router.post(`/notifications/${id}/read`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Refresh the page to show updated status
            router.reload()
        }
    })
}

const markAllAsRead = () => {
    router.post('/notifications/mark-all-read', {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload()
        }
    })
}

const deleteNotification = (id) => {
    if (confirm('Delete this notification?')) {
        router.delete(`/notifications/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload()
            }
        })
    }
}

const formatDate = (date) => {
    if (!date) return ''
    const d = new Date(date)
    const now = new Date()
    const diff = Math.floor((now - d) / 1000 / 60)
    
    if (diff < 1) return 'Just now'
    if (diff < 60) return `${diff} minute${diff > 1 ? 's' : ''} ago`
    if (diff < 1440) return `${Math.floor(diff / 60)} hour${Math.floor(diff / 60) > 1 ? 's' : ''} ago`
    if (diff < 43200) return `${Math.floor(diff / 1440)} day${Math.floor(diff / 1440) > 1 ? 's' : ''} ago`
    return d.toLocaleDateString()
}

const getNotificationIcon = (type) => {
    const icons = {
        course_update: BookOpen,
        assignment: FileText,
        grade: Award,
        announcement: MessageCircle,
        welcome: Star
    }
    return icons[type] || Bell
}

const getNotificationColor = (type) => {
    const colors = {
        course_update: 'blue',
        assignment: 'orange',
        grade: 'emerald',
        announcement: 'purple',
        welcome: 'yellow'
    }
    return colors[type] || 'slate'
}
</script>

<template>
    <Head title="Notifications | LearnHub" />
    
    <div class="min-h-screen bg-white dark:bg-slate-950">
        <div class="max-w-4xl mx-auto px-4 py-8 lg:py-12">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <Link :href="route('student.dashboard')" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        <ChevronLeft class="w-5 h-5 text-slate-600 dark:text-slate-400" />
                    </Link>
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-black dark:text-white">Notifications</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Stay updated with your learning journey</p>
                    </div>
                </div>
                <button 
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 font-medium"
                >
                    Mark all as read
                </button>
            </div>

            <!-- Notifications List -->
            <div class="space-y-3">
                <div 
                    v-for="notification in notifications.data" 
                    :key="notification.id"
                    :class="[
                        'bg-white dark:bg-slate-900 rounded-2xl border transition-all cursor-pointer hover:shadow-md',
                        !notification.read_at 
                            ? 'border-blue-300 dark:border-blue-700 bg-blue-50/30 dark:bg-blue-900/10' 
                            : 'border-slate-200 dark:border-slate-800'
                    ]"
                    @click="!notification.read_at && markAsRead(notification.id)"
                >
                    <div class="p-5">
                        <div class="flex items-start gap-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div :class="[
                                    'w-12 h-12 rounded-xl flex items-center justify-center',
                                    getNotificationColor(notification.type) === 'blue' ? 'bg-blue-100 dark:bg-blue-900/30' :
                                    getNotificationColor(notification.type) === 'orange' ? 'bg-orange-100 dark:bg-orange-900/30' :
                                    getNotificationColor(notification.type) === 'emerald' ? 'bg-emerald-100 dark:bg-emerald-900/30' :
                                    getNotificationColor(notification.type) === 'purple' ? 'bg-purple-100 dark:bg-purple-900/30' :
                                    'bg-slate-100 dark:bg-slate-700'
                                ]">
                                    <component 
                                        :is="getNotificationIcon(notification.type)"
                                        :class="[
                                            'w-6 h-6',
                                            getNotificationColor(notification.type) === 'blue' ? 'text-blue-600' :
                                            getNotificationColor(notification.type) === 'orange' ? 'text-orange-600' :
                                            getNotificationColor(notification.type) === 'emerald' ? 'text-emerald-600' :
                                            getNotificationColor(notification.type) === 'purple' ? 'text-purple-600' :
                                            'text-slate-600'
                                        ]"
                                    />
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-bold dark:text-white">{{ notification.title }}</h3>
                                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">{{ notification.message }}</p>
                                        <p class="text-xs text-slate-400 mt-2">{{ formatDate(notification.created_at) }}</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a 
                                            v-if="notification.action_url"
                                            :href="notification.action_url"
                                            class="px-3 py-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium"
                                            @click.stop
                                        >
                                            View →
                                        </a>
                                        <button 
                                            @click.stop="deleteNotification(notification.id)"
                                            class="p-1.5 text-slate-400 hover:text-red-500 transition-colors"
                                        >
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Unread Indicator -->
                            <div v-if="!notification.read_at" class="flex-shrink-0">
                                <div class="w-2.5 h-2.5 bg-blue-600 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="notifications.data?.length === 0" class="text-center py-16">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <Bell class="w-10 h-10 text-slate-400" />
                    </div>
                    <h3 class="text-lg font-bold dark:text-white mb-2">No notifications yet</h3>
                    <p class="text-sm text-slate-500">When you have notifications, they'll appear here</p>
                    <Link :href="route('student.dashboard')" class="inline-block mt-6 px-6 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors">
                        Back to Dashboard
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="notifications.last_page > 1" class="flex items-center justify-between mt-8 pt-4 border-t border-slate-200 dark:border-slate-800">
                <div class="text-sm text-slate-500">
                    Showing {{ notifications.from || 0 }} to {{ notifications.to || 0 }} of {{ notifications.total || 0 }}
                </div>
                <div class="flex items-center space-x-2">
                    <Link 
                        v-if="notifications.prev_page_url"
                        :href="notifications.prev_page_url"
                        class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                    >
                        Previous
                    </Link>
                    <span class="px-4 py-2 text-sm">
                        Page {{ notifications.current_page }} of {{ notifications.last_page }}
                    </span>
                    <Link 
                        v-if="notifications.next_page_url"
                        :href="notifications.next_page_url"
                        class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>