<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string | null;
            email: string | null;
            status: string;
            created_at: string;
            avatar?: string;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        current_page: number;
        total: number;
    }
}>();

// Search logic with safe null handling
const search = ref('');
const filteredUsers = computed(() => {
    return props.users.data.filter(user => {
        const name = (user.name || '').toLowerCase();
        const email = (user.email || '').toLowerCase();
        const searchTerm = search.value.toLowerCase();

        return name.includes(searchTerm) || email.includes(searchTerm);
    });
});

const deleteUser = (id: number) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('admin.users.destroy', id));
    }
};

const getStatusClass = (status: string) => {
    const s = status?.toLowerCase() || 'active';
    switch (s) {
        case 'active': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        case 'pending': return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
        default: return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
    }
};
</script>

<template>
    <Head title="User Management" />

    <div class="min-h-screen bg-[#09091a] p-6 text-white font-sans">
        <div class="max-w-[1400px] mx-auto space-y-6">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-white">User Management</h1>
                    <p class="text-slate-400 text-sm mt-1">Manage student accounts and platform access.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">⌕</span>
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Search users..." 
                            class="bg-white/5 border border-white/10 rounded-xl pl-9 pr-4 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none w-64 transition-all"
                        />
                    </div>
                    <button class="bg-violet-600 hover:bg-violet-700 px-4 py-2 rounded-xl text-sm font-semibold transition-all shadow-lg shadow-violet-900/20">
                        + Add User
                    </button>
                </div>
            </div>

            <div class="bg-[#11153a]/80 border border-white/10 rounded-[24px] overflow-hidden shadow-2xl backdrop-blur-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/5 text-slate-400 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-semibold">User Info</th>
                                <th class="px-6 py-4 font-semibold">Status</th>
                                <th class="px-6 py-4 font-semibold">Joined Date</th>
                                <th class="px-6 py-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-white/[0.02] transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-500 to-cyan-500 flex items-center justify-center font-bold text-sm shadow-inner">
                                            {{ user.name?.charAt(0).toUpperCase() || '?' }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-200">{{ user.name || 'Unknown User' }}</div>
                                            <div class="text-xs text-slate-500">{{ user.email || 'No Email' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="getStatusClass(user.status)" class="px-3 py-1 rounded-full text-[10px] font-bold border uppercase tracking-widest transition-all">
                                        {{ user.status || 'Active' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-400">
                                    {{ user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link 
                                            :href="route('admin.users.edit', user.id)"
                                            class="p-2 hover:bg-white/10 rounded-lg text-slate-400 hover:text-cyan-400 transition-colors"
                                        >
                                            ✎
                                        </Link>
                                        <button 
                                            @click="deleteUser(user.id)" 
                                            class="p-2 hover:bg-white/10 rounded-lg text-slate-400 hover:text-rose-400 transition-colors"
                                        >
                                            🗑
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-white/10 flex items-center justify-between bg-white/[0.01]">
                    <div class="text-sm text-slate-500">
                        Showing <span class="text-white">{{ filteredUsers.length }}</span> of <span class="text-white">{{ users.total }}</span> users
                    </div>
                    <div class="flex gap-2">
                        <Link 
                            v-for="link in users.links" 
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="px-4 py-2 rounded-xl text-sm transition-all border"
                            :class="[
                                link.active ? 'bg-violet-600 border-violet-500 text-white shadow-md' : 'bg-white/5 border-white/10 text-slate-400 hover:bg-white/10',
                                !link.url ? 'opacity-30 cursor-not-allowed' : ''
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>