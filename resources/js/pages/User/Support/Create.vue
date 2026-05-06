<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    subject: '',
    priority: 'medium',
    message: '',
});

const submit = () => {
    form.post(route('tickets.store'));
};
</script>

<template>
    <div class="p-6 bg-[#0f1230] min-h-screen text-white">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-2xl font-bold mb-6 text-center">Create New Ticket</h1>
            
            <form @submit.prevent="submit" class="bg-white/5 border border-white/10 p-8 rounded-3xl space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-400">Subject</label>
                    <input v-model="form.subject" type="text" placeholder="Brief summary of your issue"
                        class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:ring-2 focus:ring-violet-500 outline-none" />
                    <div v-if="form.errors.subject" class="text-red-400 text-xs mt-1">{{ form.errors.subject }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-400">Priority Level</label>
                    <select v-model="form.priority" class="w-full bg-[#1a1d3d] border border-white/10 rounded-xl p-3 text-white focus:ring-2 focus:ring-violet-500 outline-none">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-slate-400">Describe your issue</label>
                    <textarea v-model="form.message" rows="5" placeholder="Tell us more so we can help you better..."
                        class="w-full bg-white/5 border border-white/10 rounded-xl p-3 text-white focus:ring-2 focus:ring-violet-500 outline-none"></textarea>
                    <div v-if="form.errors.message" class="text-red-400 text-xs mt-1">{{ form.errors.message }}</div>
                </div>

                <button type="submit" :disabled="form.processing" 
                    class="w-full bg-violet-600 hover:bg-violet-500 py-3 rounded-xl font-bold transition-all transform active:scale-[0.98]">
                    {{ form.processing ? 'Submitting...' : 'Submit Ticket' }}
                </button>
            </form>
        </div>
    </div>
</template>