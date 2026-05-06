<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        status: string;
        phone?: string;
    }
}>();

const form = useForm({
    name: props.user.name ?? '',
    email: props.user.email ?? '',
    status: props.user.status ?? 'active',
    phone: props.user.phone ?? '',
    password: '', // Left blank unless updating
});

const submit = () => {
    // We use put() for updates
    form.put(route('admin.users.update', props.user.id));
};
</script>

<template>
    <Head :title="'Edit ' + (user.name || 'User')" />

    <div class="min-h-screen bg-[#09091a] p-6 text-white font-sans">
        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('admin.users.index')" class="text-slate-400 hover:text-white transition-colors">
                    ← Back
                </Link>
                <h1 class="text-3xl font-bold tracking-tight">Edit Profile: {{ user.name }}</h1>
            </div>

            <form @submit.prevent="submit" class="bg-[#11153a]/80 border border-white/10 rounded-[24px] p-8 shadow-2xl space-y-6 backdrop-blur-md">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300">Full Name</label>
                        <input v-model="form.name" type="text" 
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-violet-500 outline-none" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300">Email Address</label>
                        <input v-model="form.email" type="email" 
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-violet-500 outline-none" />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300">New Password (Leave blank to keep current)</label>
                    <input v-model="form.password" type="password" 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-violet-500 outline-none" />
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300">Account Status</label>
                    <select v-model="form.status" class="w-full bg-[#09091a] border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:ring-2 focus:ring-violet-500">
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="pt-4 flex gap-4">
                    <button type="submit" :disabled="form.processing"
                        class="flex-1 bg-cyan-600 hover:bg-cyan-700 py-4 rounded-xl font-bold transition-all shadow-lg shadow-cyan-900/20">
                        Update User Info
                    </button>
                    <Link :href="route('admin.users.index')" 
                        class="flex-1 bg-white/5 hover:bg-white/10 py-4 rounded-xl font-bold transition-all text-center">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>