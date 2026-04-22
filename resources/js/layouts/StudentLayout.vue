<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="flex min-h-screen bg-slate-50 font-sans text-slate-900">
    
    <aside class="w-72 bg-white border-r border-slate-200 flex flex-col sticky top-0 h-screen transition-all">
      
      <div class="p-6 flex items-center gap-3">
        <div class="bg-blue-600 p-2 rounded-xl">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <span class="text-xl font-black tracking-tight text-slate-800">LearnHub</span>
      </div>

      <div class="mx-4 mb-8 p-4 bg-slate-50 border border-slate-100 rounded-2xl flex items-center gap-3">
        <img src="https://ui-avatars.com/api/?name=John+Carter&background=2563eb&color=fff" class="h-11 w-11 rounded-xl shadow-sm" alt="User">
        <div class="overflow-hidden">
          <p class="text-sm font-bold text-slate-900 truncate">John Carter</p>
          <p class="text-[11px] text-slate-500 truncate font-medium">john.carter@email.com</p>
        </div>
      </div>

      <nav class="flex-1 px-4 space-y-1.5">
        <Link href="/dashboard" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-100 transition-all">
          <span class="text-lg">🏠</span> Dashboard
        </Link>
        <Link href="/courses" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-blue-50 hover:text-blue-600 rounded-xl font-semibold transition-all group">
          <span class="text-lg group-hover:scale-110 transition-transform">📖</span> Courses
        </Link>
        <Link href="/assignments" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-blue-50 hover:text-blue-600 rounded-xl font-semibold transition-all">
          <span class="text-lg">📝</span> Assignments
        </Link>
        <Link href="/progress" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-blue-50 hover:text-blue-600 rounded-xl font-semibold transition-all">
          <span class="text-lg">📊</span> Progress
        </Link>
        <Link href="/discussions" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-blue-50 hover:text-blue-600 rounded-xl font-semibold transition-all">
          <span class="text-lg">💬</span> Discussions
        </Link>
        <Link href="/notifications" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-blue-50 hover:text-blue-600 rounded-xl font-semibold transition-all">
          <span class="text-lg">🔔</span> Notifications
        </Link>
      </nav>

      <div class="px-4 py-4 space-y-1 border-t border-slate-100">
        <Link href="/help" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-blue-600 font-semibold transition-all">
          <span class="text-lg">❓</span> AI Study Helper
        </Link>
        <Link href="/settings" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-blue-600 font-semibold transition-all">
          <span class="text-lg">⚙️</span> Settings
        </Link>
      </div>

      <div class="p-3 bg-slate-900 m-4 rounded-2xl flex items-center justify-between shadow-xl shadow-slate-200">
         <div class="flex items-center gap-2">
            <img src="https://ui-avatars.com/api/?name=JC&background=fff&color=000" class="h-8 w-8 rounded-lg" alt="Avatar">
            <span class="text-[10px] font-bold text-white uppercase tracking-widest">Settings</span>
         </div>
         <div class="flex gap-2.5">
            <button class="text-slate-400 hover:text-white transition-colors">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            </button>
            <button class="text-slate-400 hover:text-white transition-colors">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
            </button>
            <button class="text-slate-400 hover:text-white transition-colors">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </button>
         </div>
      </div>
    </aside>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-6xl mx-auto p-10">
            <slot />
        </div>
    </main>
  </div>
</template>

<style scoped>
/* Smooth hover transitions */
.transition-all {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>