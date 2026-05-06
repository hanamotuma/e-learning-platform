<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ ticket: Object });

const form = useForm({
    message: '',
});

const sendReply = () => {
    form.post(route('tickets.message.store', props.ticket.id), {
        onSuccess: () => form.reset(),
        preserveScroll: true,
    });
};

// Helper function to handle JSON parsing of attachments safely
const parseAttachments = (attachments) => {
    if (!attachments) return [];
    try {
        return typeof attachments === 'string' ? JSON.parse(attachments) : attachments;
    } catch (e) {
        return [];
    }
};
</script>

<template>
    <div class="p-6 bg-[#0f1230] min-h-screen text-white">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <div class="flex items-center gap-3 text-slate-400 text-sm mb-2">
                    <span>Tickets</span> <span>/</span> <span>#{{ ticket.id }}</span>
                </div>
                <div class="flex justify-between items-start">
                    <h1 class="text-3xl font-bold">{{ ticket.subject }}</h1>
                    <span class="bg-violet-500/20 text-violet-400 px-4 py-1 rounded-full text-xs font-bold uppercase">
                        {{ ticket.status.replace('_', ' ') }}
                    </span>
                </div>
            </div>

            <div class="space-y-6 mb-8">
                <div v-for="msg in ticket.messages" :key="msg.id" 
                    :class="['flex flex-col', msg.user_id === $page.props.auth.user.id ? 'items-end' : 'items-start']">
                    
                    <div :class="['max-w-[80%] p-4 rounded-2xl', 
                        msg.user_id === $page.props.auth.user.id ? 'bg-violet-600 rounded-tr-none' : 'bg-white/10 rounded-tl-none border border-white/10']">
                        
                        <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ msg.message }}</p>

                        <div v-if="msg.attachments" class="mt-3 pt-3 border-t border-white/10 space-y-2">
                            <div v-for="(file, index) in parseAttachments(msg.attachments)" :key="index">
                                <a :href="`/storage/${file}`" 
                                   target="_blank" 
                                   class="flex items-center gap-2 text-xs text-cyan-400 hover:text-cyan-300 transition group">
                                    <span class="bg-cyan-400/10 p-1 rounded group-hover:bg-cyan-400/20">📎</span>
                                    <span class="underline underline-offset-2">View Attachment {{ index + 1 }}</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <span class="text-[10px] text-slate-500 mt-1 px-1">
                        {{ msg.user?.full_name }} • {{ new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                    </span>
                </div>
            </div>

            <div v-if="ticket.status !== 'closed'" class="mt-10">
                <form @submit.prevent="sendReply" class="relative">
                    <textarea v-model="form.message" placeholder="Reply to support..."
                        class="w-full bg-white/5 border border-white/10 rounded-3xl p-5 pr-32 text-white focus:ring-2 focus:ring-violet-500 outline-none resize-none"
                        rows="3"></textarea>
                    <button type="submit" :disabled="form.processing || !form.message"
                        class="absolute right-3 bottom-3 bg-violet-600 hover:bg-violet-500 px-6 py-2 rounded-2xl font-bold transition text-sm disabled:opacity-50">
                        Send
                    </button>
                </form>
            </div>
            
            <div v-else class="text-center p-6 bg-red-500/10 border border-red-500/20 rounded-3xl text-red-400 font-medium">
                This ticket has been closed. If you need more help, please open a new ticket.
            </div>
        </div>
    </div>
</template>