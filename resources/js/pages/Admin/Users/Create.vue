<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    status: 'active',
    role: 'student', // Default role
});

const submit = () => {
    form.post(route('admin.users.store'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Create User" />

    <div class="min-h-screen bg-[#09091a] p-6 text-white font-sans">
        <div class="max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('admin.users.index')" class="text-slate-400 hover:text-white transition-colors">
                    ← Back
                </Link>
                <h1 class="text-3xl font-bold tracking-tight">Create New User</h1>
            </div>

            <form @submit.prevent="submit" class="bg-[#11153a]/80 border border-white/10 rounded-[24px] p-8 shadow-2xl space-y-6 backdrop-blur-md">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300">Full Name</label>
                    <input v-model="form.name" type="text" 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-violet-500 outline-none transition-all"
                        :class="{ 'border-rose-500': form.errors.name }" />
                    <p v-if="form.errors.name" class="text-rose-500 text-xs mt-1">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300">Email Address</label>
                    <input v-model="form.email" type="email" 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-violet-500 outline-none transition-all"
                        :class="{ 'border-rose-500': form.errors.email }" />
                    <p v-if="form.errors.email" class="text-rose-500 text-xs mt-1">{{ form.errors.email }}</p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300">Password</label>
                    <input v-model="form.password" type="password" 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-violet-500 outline-none transition-all"
                        :class="{ 'border-rose-500': form.errors.password }" />
                    <p v-if="form.errors.password" class="text-rose-500 text-xs mt-1">{{ form.errors.password }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300">Status</label>
                        <select v-model="form.status" class="w-full bg-[#09091a] border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:ring-2 focus:ring-violet-500">
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300">User Role</label>
                        <select v-model="form.role" class="w-full bg-[#09091a] border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:ring-2 focus:ring-violet-500">
                            <option value="student">Student</option>
                            <option value="instructor">Instructor</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" :disabled="form.processing"
                        class="w-full bg-violet-600 hover:bg-violet-700 disabled:opacity-50 py-4 rounded-xl font-bold transition-all shadow-lg shadow-violet-900/40">
                        {{ form.processing ? 'Saving...' : 'Create User Account' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>