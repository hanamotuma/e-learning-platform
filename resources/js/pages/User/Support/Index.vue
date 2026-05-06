<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ 
    tickets: Array 
});
</script>

<template>
    <div class="p-6 bg-[#0f1230] min-h-screen text-white">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold">My Support Tickets</h1>
                
                <Link :href="route('tickets.create')" class="bg-violet-600 hover:bg-violet-500 px-6 py-2 rounded-xl font-medium transition">
                    New Ticket
                </Link>
            </div>

            <div class="grid gap-4">
                <div v-for="ticket in tickets" :key="ticket.id" class="bg-white/5 border border-white/10 p-5 rounded-2xl flex items-center justify-between hover:bg-white/10 transition">
                    <div>
                        <h3 class="font-bold text-lg">{{ ticket.subject }}</h3>
                        <div class="flex gap-4 mt-1 text-sm text-slate-400">
                            <span>ID: #{{ ticket.id }}</span>
                            <span>Created: {{ new Date(ticket.created_at).toLocaleDateString() }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-6">
                        <span :class="[
                            'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider', 
                            ticket.priority === 'urgent' ? 'bg-red-500/20 text-red-400' : 'bg-blue-500/20 text-blue-400'
                        ]">
                            {{ ticket.priority }}
                        </span>

                        <span class="text-slate-300 font-medium capitalize">
                            {{ ticket.status ? ticket.status.replace('_', ' ') : 'Pending' }}
                        </span>

                        <Link :href="route('tickets.show', ticket.id)" class="text-violet-400 hover:text-violet-300 font-bold">
                            View →
                        </Link>
                    </div>
                </div>

                <div v-if="tickets.length === 0" class="text-center py-20 bg-white/5 rounded-3xl border border-dashed border-white/20">
                    <p class="text-slate-400">You haven't created any support tickets yet.</p>
                </div>
            </div>
        </div>
    </div>
</template>