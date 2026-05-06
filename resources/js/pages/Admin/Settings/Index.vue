<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ settings: Object });

const activeTab = ref('general');

const tabs = [
    { id: 'general', name: 'General', icon: '🌐' },
    { id: 'payments', name: 'Payments', icon: '💳' },
    { id: 'ai', name: 'AI & Automation', icon: '🤖' },
];

const form = useForm({
    app_name: props.settings.app_name,
    chapa_public_key: props.settings.chapa_public_key,
    gemini_api_key: props.settings.gemini_api_key,
});
</script>

<template>
    <Head title="System Settings" />

    <div class="p-4 md:p-8 max-w-5xl mx-auto min-h-screen">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
            <div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">System Configuration</h1>
                <p class="text-slate-400 mt-1">Manage platform-wide variables and integration states.</p>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-emerald-500 text-xs font-bold uppercase tracking-widest">Production Mode</span>
            </div>
        </div>

        <div class="flex gap-2 mb-8 bg-white/5 p-1.5 rounded-2xl border border-white/5 w-fit">
            <button 
                v-for="tab in tabs" 
                :key="tab.id"
                @click="activeTab = tab.id"
                class="px-6 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 flex items-center gap-2"
                :class="activeTab === tab.id ? 'bg-white/10 text-white shadow-xl' : 'text-slate-400 hover:text-slate-200'"
            >
                <span>{{ tab.icon }}</span>
                {{ tab.name }}
            </button>
        </div>

        <div class="grid gap-8">
            
            <div v-if="activeTab === 'general'" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="bg-white/5 border border-white/10 rounded-[2rem] p-8 backdrop-blur-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-white">Platform Identity</h2>
                        <span class="text-[10px] bg-white/10 text-slate-400 px-2 py-1 rounded-md uppercase">Core Config</span>
                    </div>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">App Name</label>
                            <div class="relative">
                                <input v-model="form.app_name" readonly class="w-full bg-black/40 border border-white/10 rounded-2xl p-4 text-white focus:ring-0 cursor-not-allowed opacity-80" />
                                <span class="absolute right-4 top-4 text-slate-600 italic text-xs">Locked</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'payments'" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="bg-white/5 border border-white/10 rounded-[2rem] p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-blue-500/20 flex items-center justify-center text-xl">💳</div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Chapa Gateway</h2>
                            <p class="text-slate-400 text-sm">Handles enrollment and payouts.</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="p-4 bg-black/20 border border-white/5 rounded-2xl">
                            <label class="text-[10px] font-black text-slate-500 uppercase">Public Key</label>
                            <div class="text-slate-300 font-mono text-sm truncate mt-1">
                                {{ form.chapa_public_key ? '••••••••••••••••' + form.chapa_public_key.slice(-4) : 'Not Configured' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'ai'" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="bg-white/5 border border-white/10 rounded-[2rem] p-8 border-l-purple-500/50 border-l-4">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                            <span class="animate-pulse">✨</span> Google Gemini AI
                        </h2>
                        <div class="px-3 py-1 bg-purple-500/20 text-purple-400 text-[10px] font-bold rounded-full border border-purple-500/30">Active</div>
                    </div>
                    <div class="p-6 bg-black/40 border border-white/10 rounded-2xl">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-3">Model Integration Key</label>
                        <input :value="form.gemini_api_key ? 'HIDDEN_FOR_SECURITY_MASK' : ''" type="password" readonly class="w-full bg-transparent border-none p-0 text-purple-300 font-mono tracking-tighter" />
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-12 flex items-start gap-4 p-6 bg-amber-500/5 border border-amber-500/20 rounded-[2rem]">
            <span class="text-2xl">⚠️</span>
            <div>
                <h4 class="text-amber-500 font-bold text-sm">Strict Security Environment</h4>
                <p class="text-slate-400 text-xs leading-relaxed mt-1">
                    Environment variables (<code>.env</code>) are restricted from write-access via the dashboard to prevent unauthorized API injection. 
                    Contact the system administrator to modify these values.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-in {
    animation: fadeIn 0.5s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>