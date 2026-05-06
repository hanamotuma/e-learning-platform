<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ tickets: Object });
</script>

<template>
    <div class="p-6 bg-slate-900 min-h-screen text-white">
        <h1 class="text-2xl font-bold mb-6">Support Tickets</h1>
        <div class="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-white/5 text-slate-400 text-sm">
                    <tr>
                        <th class="p-4">User</th>
                        <th class="p-4">Subject</th>
                        <th class="p-4">Priority</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <tr v-for="ticket in tickets.data" :key="ticket.id">
                        <td class="p-4">{{ ticket.user.name }}</td>
                        <td class="p-4">{{ ticket.subject }}</td>
                        <td class="p-4 uppercase text-xs font-bold">{{ ticket.priority }}</td>
                        <td class="p-4 text-sm">{{ ticket.status }}</td>
                        <td class="p-4">
                            <Link :href="route('admin.tickets.show', ticket.id)" class="text-violet-400 hover:underline">
                                View
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>