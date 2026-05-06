<script setup>
import { ref, computed, onMounted } from 'vue'; // Added onMounted
import { Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    notifications: Object
});

const page = usePage();

// 1. REAL-TIME LISTENER
onMounted(() => {
    window.Echo.private(`App.Models.User.${page.props.auth.user.id}`)
        // Change .notification to .listen for the Event class
        .listen('NotificationSent', (e) => {
            console.log('New Event Received:', e.notification);
            
            props.notifications.data.unshift({
                id: e.notification.id,
                title: e.notification.title,
                message: e.notification.message,
                type: e.notification.type,
                is_read: false,
                created_at: new Date().toISOString()
            });
        });
});
const activeFilter = ref('all');

const filteredNotifications = computed(() => {
    if (activeFilter.value === 'unread') {
        return props.notifications.data.filter(n => !n.is_read);
    }
    return props.notifications.data;
});

const markAsRead = (id) => {
    if (!id) return;
    router.post(route('admin.notifications.read', id), {}, { preserveScroll: true });
};

const markAllRead = () => {
    router.post(route('admin.notifications.markAllRead'), {}, { preserveScroll: true });
};

const deleteNotification = (id) => {
    if (confirm('Are you sure you want to remove this alert?')) {
        router.delete(route('admin.notifications.destroy', id), { preserveScroll: true });
    }
};

const getIcon = (type) => {
    const icons = {
        course: '📚',
        payment: '💳',
        ticket: '🎫',
        system: '⚙️',
        user: '👤'
    };
    return icons[type] || '🔔';
};
</script>

<template>
    <div class="min-h-screen bg-slate-50/50 p-4 md:p-8">
        <div class="max-w-5xl mx-auto">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Notification Center</h1>
                    <p class="text-slate-500 mt-1 text-sm">Real-time updates for your e-learning system.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <button 
                        v-if="notifications?.data?.some(n => !n.is_read)"
                        @click="markAllRead"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                    >
                        ✓ Mark all as read
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-1 mb-6 bg-slate-200/50 p-1 rounded-xl w-fit">
                <button @click="activeFilter = 'all'" :class="['px-6 py-2 rounded-lg text-sm font-bold transition-all', activeFilter === 'all' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700']">All</button>
                <button @click="activeFilter = 'unread'" :class="['px-6 py-2 rounded-lg text-sm font-bold transition-all', activeFilter === 'unread' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700']">Unread</button>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <template v-if="filteredNotifications.length > 0">
                    <div 
                        v-for="item in filteredNotifications" 
                        :key="item.id"
                        :class="['group relative p-5 border-b border-slate-100 last:border-0 transition-all hover:bg-slate-50', 
                                item.is_read ? 'opacity-80' : 'bg-white shadow-[inset_4px_0_0_0_#3b82f6]']"
                    >
                        <div class="flex items-start gap-4">
                            <div :class="['shrink-0 w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-sm', item.is_read ? 'bg-slate-100 grayscale' : 'bg-white border border-slate-100']">
                                {{ getIcon(item.type) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h3 :class="['text-sm font-bold truncate', item.is_read ? 'text-slate-600' : 'text-slate-900']">{{ item.title }}</h3>
                                    <span class="text-[11px] font-medium text-slate-400 uppercase">{{ new Date(item.created_at).toLocaleDateString() }}</span>
                                </div>
                                <p class="text-sm text-slate-500 mt-1 leading-relaxed">{{ item.message }}</p>

                                <div class="mt-4 flex items-center gap-6">
                                    <a v-if="item.action_url" :href="item.action_url" class="inline-flex items-center text-xs font-bold text-blue-600 hover:text-blue-800">View Details →</a>
                                    <button v-if="!item.is_read" @click="markAsRead(item.id)" class="text-xs font-bold text-slate-400 hover:text-blue-600">Mark as Seen</button>
                                </div>
                            </div>

                            <button @click="deleteNotification(item.id)" class="opacity-0 group-hover:opacity-100 p-2 text-slate-300 hover:text-red-500 transition-opacity">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>

                <div v-else class="py-24 text-center">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-slate-50 rounded-full mb-6">✨</div>
                    <h3 class="text-lg font-bold text-slate-900">All caught up!</h3>
                </div>
            </div>
        </div>
    </div>
</template>