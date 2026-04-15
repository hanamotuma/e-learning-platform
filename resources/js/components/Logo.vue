<!-- resources/js/Components/Logo.vue -->
<template>
    <div class="flex items-center space-x-2">
        <div class="relative">
            <!-- Brain/Book hybrid icon -->
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 4C11.1634 4 4 11.1634 4 20C4 28.8366 11.1634 36 20 36C28.8366 36 36 28.8366 36 20C36 11.1634 28.8366 4 20 4Z" fill="url(#gradient)" stroke="#4F46E5" stroke-width="2"/>
                <path d="M14 20L18 24L26 16" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <defs>
                    <linearGradient id="gradient" x1="4" y1="4" x2="36" y2="36" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#6366F1"/>
                        <stop offset="1" stop-color="#8B5CF6"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>
        <div class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            EduMind
        </div>
    </div>
</template>