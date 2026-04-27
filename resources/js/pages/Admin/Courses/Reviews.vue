<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    course: Object,
    reviews: Object // Paginated collection
});

const activeReview = ref(null);
const responseForm = useForm({
    message: ''
});

const toggleApproval = (id) => {
    router.patch(route('admin.reviews.toggle', id), {}, { preserveScroll: true });
};

const submitResponse = (id) => {
    responseForm.post(route('admin.reviews.respond', id), {
        onSuccess: () => {
            responseForm.reset();
            activeReview.value = null;
        }
    });
};

const deleteReview = (id) => {
    if(confirm('Are you sure?')) {
        router.delete(route('admin.reviews.destroy', id));
    }
};
</script>

<template>
    <div class="min-h-screen bg-[#09091a] text-white p-8">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold mb-2">Reviews for {{ course.title }}</h1>
            <p class="text-gray-400 mb-8">Manage student feedback and instructor responses.</p>

            <div class="space-y-6">
                <div v-for="review in reviews.data" :key="review.id" 
                    class="bg-[#16103a] border border-white/5 rounded-3xl p-6 shadow-xl">
                    
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-indigo-500/20 flex items-center justify-center font-bold text-indigo-400">
                                {{ review.user.name.charAt(0) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-white">{{ review.user.name }}</h3>
                                <div class="flex text-yellow-400 text-sm">
                                    <span v-for="i in 5" :key="i">
                                        {{ i <= review.rating ? '★' : '☆' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <button @click="toggleApproval(review.id)" 
                                :class="review.is_approved ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/50' : 'bg-amber-500/20 text-amber-400 border-amber-500/50'"
                                class="px-4 py-1.5 rounded-full text-xs font-black uppercase border transition">
                                {{ review.is_approved ? 'Approved' : 'Pending' }}
                            </button>
                            <button @click="deleteReview(review.id)" class="text-gray-500 hover:text-red-400 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>

                    <p class="text-gray-300 leading-relaxed mb-6 italic">
                        "{{ review.review }}"
                    </p>

                    <div v-if="review.instructor_response?.length" class="mb-6 space-y-3">
                        <div v-for="(res, i) in review.instructor_response" :key="i" 
                            class="ml-8 p-4 bg-white/5 border-l-2 border-indigo-500 rounded-r-xl text-sm">
                            <p class="text-indigo-400 font-bold mb-1">Instructor Response</p>
                            <p class="text-gray-400">{{ res.body }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button v-if="activeReview !== review.id" @click="activeReview = review.id" class="text-indigo-400 text-sm font-bold hover:underline">
                            + Write a response
                        </button>

                        <div v-else class="space-y-3">
                            <textarea v-model="responseForm.message" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-white outline-none focus:border-indigo-500" placeholder="Type your response..."></textarea>
                            <div class="flex gap-2">
                                <button @click="submitResponse(review.id)" class="bg-indigo-600 px-4 py-2 rounded-xl text-sm font-bold">Send Response</button>
                                <button @click="activeReview = null" class="text-gray-500 text-sm">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>