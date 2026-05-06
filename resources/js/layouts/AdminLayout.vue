<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <span class="text-xl font-bold">Admin Dashboard</span>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button class="p-1 rounded-full hover:bg-gray-100 focus:outline-none">
                                <span v-if="unreadCount > 0" 
                                      class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                                    {{ unreadCount }}
                                </span>
                                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const unreadCount = ref(0);

onMounted(() => {
    // 1. Get the current Admin ID from the auth prop
    const adminId = page.props.auth.user.id;

    // 2. Listen for the event we defined in the PaymentController
    window.Echo.private(`App.Models.User.${adminId}`)
        .listen('NotificationSent', (event) => {
            console.log('Real-time notification received:', event.notification);
            
            // Increment the counter
            unreadCount.value++;

            // Optional: Trigger a browser alert or toast
            alert(`New Payment: ${event.notification.message}`);
        });
});
</script>