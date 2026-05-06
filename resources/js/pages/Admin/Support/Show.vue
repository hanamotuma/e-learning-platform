<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref, nextTick, onMounted, watch } from 'vue';

const props = defineProps({ ticket: Object });
const scrollContainer = ref(null);
const filePreviews = ref([]);

const form = useForm({
    message: '',
    attachments: [],
});

// Auto-scroll to bottom helper
const scrollToBottom = () => {
    nextTick(() => {
        if (scrollContainer.ref) {
            scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight;
        }
    });
};

onMounted(scrollToBottom);
watch(() => props.ticket.messages, scrollToBottom, { deep: true });

const changeStatus = (status) => {
    router.patch(route('admin.tickets.status.update', props.ticket.id), { status }, {
        preserveScroll: true,
    });
};

const handleFiles = (event) => {
    const files = Array.from(event.target.files);
    form.attachments = files;
    
    // Generate simple text previews for the UI
    filePreviews.value = files.map(file => ({
        name: file.name,
        size: (file.size / 1024).toFixed(1) + ' KB'
    }));
};

const removeFile = (index) => {
    const dt = new DataTransfer();
    const input = document.getElementById('file-input');
    const { files } = input;
    
    for (let i = 0; i < files.length; i++) {
        if (index !== i) dt.items.add(files[i]);
    }
    
    input.files = dt.files;
    handleFiles({ target: { files: dt.files } });
};

const submitReply = () => {
    form.post(route('admin.tickets.message.store', props.ticket.id), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            filePreviews.value = [];
            document.getElementById('file-input').value = null;
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="p-6 bg-slate-900 min-h-screen text-white max-w-5xl mx-auto flex flex-col h-screen">
        <div class="flex justify-between items-center mb-6 bg-slate-800 p-4 rounded-2xl border border-white/5">
            <div>
                <span class="text-violet-400 text-xs font-bold uppercase tracking-wider">Ticket #{{ ticket.id }}</span>
                <h1 class="text-2xl font-bold">{{ ticket.subject }}</h1>
            </div>
            
            <div class="flex items-center gap-4">
                <div :class="[
                    'px-3 py-1 rounded-full text-xs font-bold uppercase',
                    ticket.status === 'open' ? 'bg-emerald-500/20 text-emerald-400' : 
                    ticket.status === 'in_progress' ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-500/20 text-slate-400'
                ]">
                    {{ ticket.status.replace('_', ' ') }}
                </div>
                
                <select @change="changeStatus($event.target.value)" :value="ticket.status" 
                    class="bg-slate-700 rounded-xl px-4 py-2 border-none outline-none focus:ring-2 focus:ring-violet-500 cursor-pointer text-sm">
                    <option value="open">Mark as Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Mark Resolved</option>
                </select>
            </div>
        </div>

        <div ref="scrollContainer" class="flex-1 overflow-y-auto space-y-6 mb-6 pr-2 custom-scrollbar">
            <div v-for="msg in ticket.messages" :key="msg.id" 
                :class="['flex flex-col', msg.user_id === $page.props.auth.user.id ? 'items-end' : 'items-start']">
                
                <div :class="[
                    'max-w-[75%] p-4 rounded-2xl shadow-lg transition-all hover:scale-[1.01]',
                    msg.user_id === $page.props.auth.user.id ? 'bg-violet-600 rounded-tr-none' : 'bg-slate-800 rounded-tl-none border border-white/5'
                ]">
                    <p class="text-[10px] opacity-60 mb-2 font-bold uppercase tracking-widest">
                        {{ msg.user?.full_name || 'System' }}
                    </p>
                    
                    <p class="whitespace-pre-wrap text-sm leading-relaxed">{{ msg.message }}</p>
                    
                    <div v-if="msg.attachments" class="mt-4 pt-3 border-t border-white/10 flex flex-wrap gap-2">
                        <a v-for="(file, index) in (typeof msg.attachments === 'string' ? JSON.parse(msg.attachments) : msg.attachments)" 
                           :key="index" :href="`/storage/${file}`" target="_blank"
                           class="flex items-center gap-2 bg-black/20 hover:bg-black/40 px-3 py-1.5 rounded-lg text-xs transition border border-white/5">
                            <span>📎</span> File {{ index + 1 }}
                        </a>
                    </div>
                </div>
                <span class="text-[10px] text-slate-500 mt-2 px-2 italic">
                    {{ new Date(msg.created_at).toLocaleString() }}
                </span>
            </div>
        </div>

        <form @submit.prevent="submitReply" class="bg-slate-800 p-4 rounded-3xl border border-white/10 shadow-2xl">
            <div v-if="filePreviews.length" class="flex flex-wrap gap-2 mb-3">
                <div v-for="(file, index) in filePreviews" :key="index" 
                    class="bg-violet-500/20 border border-violet-500/30 px-3 py-1 rounded-full text-xs flex items-center gap-2">
                    <span class="truncate max-w-[150px]">{{ file.name }}</span>
                    <button @click="removeFile(index)" type="button" class="hover:text-red-400 text-lg">&times;</button>
                </div>
            </div>

            <div class="relative">
                <textarea v-model="form.message" 
                    class="w-full bg-slate-900/50 rounded-2xl p-4 text-white border border-white/5 mb-4 focus:ring-2 focus:ring-violet-500 outline-none resize-none min-h-[100px]" 
                    placeholder="Type your response here..." required></textarea>
                
                <div class="flex items-center justify-between gap-4">
                    <label class="cursor-pointer group flex items-center gap-2 text-slate-400 hover:text-white transition">
                        <div class="bg-slate-700 p-2 rounded-xl group-hover:bg-slate-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium">Attach Files</span>
                        <input id="file-input" type="file" multiple @change="handleFiles" class="hidden" />
                    </label>

                    <button type="submit" :disabled="form.processing" 
                        class="bg-violet-600 hover:bg-violet-500 disabled:opacity-50 px-8 py-2.5 rounded-xl font-bold transition-all transform active:scale-95 shadow-lg flex items-center gap-2">
                        <span v-if="form.processing" class="animate-spin text-lg">⏳</span>
                        {{ form.processing ? 'Sending...' : 'Send Response' }}
                    </button>
                </div>
            </div>

            <div v-if="form.progress" class="mt-4 w-full bg-slate-700 rounded-full h-1 overflow-hidden">
                <div class="bg-violet-500 h-full transition-all duration-300" :style="`width: ${form.progress.percentage}%`"></div>
            </div>
        </form>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
</style>