<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({ lesson: Object });

const resourceForm = useForm({
    title: '',
    file: null
});

const uploadResource = () => {
    resourceForm.post(route('admin.resources.store', props.lesson.id), {
        onSuccess: () => resourceForm.reset()
    });
};

// Helper to sanitize storage path
const getFileUrl = (path) => {
    if (!path) return '';
    return `/storage/${path.replace(/^\//, '')}`;
};
</script>

<template>
    <div class="min-h-screen bg-[#0f0a24] p-8 text-white">
        <div class="max-w-5xl mx-auto mb-6">
            <Link :href="route('admin.courses.show', lesson.id)" class="text-sm text-purple-400 hover:text-purple-300">
                ← Back to Course Curriculum
            </Link>
            <h1 class="text-4xl font-extrabold mt-4">{{ lesson.title }}</h1>
        </div>

        <div class="max-w-5xl mx-auto grid lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="rounded-3xl overflow-hidden border border-white/10 shadow-2xl bg-black aspect-video flex items-center justify-center">
                    <video 
                        v-if="lesson.video_url" 
                        controls 
                        preload="metadata"
                        class="w-full h-full"
                    >
                        <source :src="getFileUrl(lesson.video_url)" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div v-else class="text-slate-500 text-center p-8">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        <p class="italic text-sm">No video uploaded for this lesson</p>
                    </div>
                </div>

                <div class="bg-white/5 p-6 rounded-2xl border border-white/10">
                    <h3 class="text-lg font-bold mb-3 text-purple-300">Lesson Content</h3>
                    <p class="text-slate-300 leading-relaxed">
                        {{ lesson.content || 'No text content provided for this lesson.' }}
                    </p>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-[#15193f] p-6 rounded-2xl border border-white/10 h-fit">
                    <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-5 bg-cyan-500 rounded-full"></span>
                        Resources
                    </h2>
                    
                    <ul class="space-y-3 mb-6">
                        <li v-for="res in lesson.resources" :key="res.id" class="flex justify-between items-center bg-white/5 p-3 rounded-xl border border-white/5 group">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-white">{{ res.title }}</span>
                                <span class="text-[10px] text-slate-500 uppercase">{{ res.file_type }}</span>
                            </div>
                            <button @click="$inertia.delete(route('admin.resources.destroy', res.id))" class="text-rose-500 opacity-0 group-hover:opacity-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </li>
                        <div v-if="!lesson.resources?.length" class="text-xs text-slate-500 italic text-center py-4">
                            No files attached.
                        </div>
                    </ul>

                    <form @submit.prevent="uploadResource" class="space-y-4 border-t border-white/10 pt-6">
                        <div>
                            <label class="text-[10px] uppercase font-bold text-slate-500 ml-1">Title</label>
                            <input v-model="resourceForm.title" placeholder="PDF Name" class="w-full bg-white/5 p-3 mt-1 rounded-xl border border-white/10 outline-none focus:border-cyan-500 transition">
                        </div>
                        <div>
                            <label class="text-[10px] uppercase font-bold text-slate-500 ml-1">File</label>
                            <input type="file" @input="resourceForm.file = $event.target.files[0]" class="block w-full text-xs text-slate-400 mt-1 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-cyan-500/10 file:text-cyan-500 hover:file:bg-cyan-500/20">
                        </div>
                        <button :disabled="resourceForm.processing" class="w-full bg-cyan-500 py-3 rounded-xl text-[#0f0a24] font-black uppercase tracking-widest text-xs transition hover:bg-cyan-400">
                            {{ resourceForm.processing ? 'Uploading...' : 'Upload Resource' }}
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</template>