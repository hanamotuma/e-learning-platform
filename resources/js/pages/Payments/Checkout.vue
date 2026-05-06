<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import { CreditCard, CheckCircle, ShieldCheck } from 'lucide-vue-next';

const props = defineProps({
    course: Object,
    totalAmount: Number,
    isFree: Boolean,
});

const form = useForm({
    course_id: props.course?.id,
    payment_method: props.isFree ? 'free' : 'Chapa', 
});

const submitPayment = () => {
    if (!props.course?.id) return;

    form.post(route('payments.process'), {
        preserveScroll: true,
        onStart: () => console.log('Initiating transaction...'),
        onError: (err) => console.error('Submission Error:', err),
    });
};
</script>

<template>
    <Head :title="`Checkout - ${course?.title ?? 'Processing...'}`" />
    
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-12 px-4">
        <div class="max-w-4xl mx-auto grid md:grid-cols-5 gap-8">
            
            <div class="md:col-span-3 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 shadow-sm border border-slate-100 dark:border-slate-800">
                    <h2 class="text-2xl font-black mb-6 dark:text-white flex items-center gap-3">
                        <CreditCard class="text-blue-600" /> Payment Method
                    </h2>

                    <div v-if="!isFree" class="grid grid-cols-1 gap-4">
                        <div 
                            @click="form.payment_method = 'Chapa'"
                            :class="[
                                'p-5 rounded-2xl border-2 cursor-pointer transition-all flex items-center justify-between',
                                form.payment_method === 'Chapa' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-100 dark:border-slate-800'
                            ]"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm text-2xl">💳</div>
                                <div>
                                    <p class="font-bold dark:text-white">Chapa</p>
                                    <p class="text-xs text-slate-500">Telebirr, CBE Birr, M-Pesa</p>
                                </div>
                            </div>
                            <CheckCircle v-if="form.payment_method === 'Chapa'" class="text-blue-600 w-5 h-5" />
                        </div>
                    </div>

                    <div v-else class="p-6 bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-900/30 rounded-2xl mb-6">
                        <p class="text-green-800 dark:text-green-300 font-bold">This course is free!</p>
                        <p class="text-sm text-green-600 dark:text-green-400 mt-1">Zero payment details are required to complete the enrollment.</p>
                    </div>

                    <div class="mt-8 pt-8 border-t dark:border-slate-800">
                        <button 
                            type="button"
                            @click="submitPayment"
                            :disabled="form.processing"
                            class="w-full py-5 bg-blue-600 hover:bg-slate-900 text-white rounded-2xl font-black text-lg shadow-xl shadow-blue-600/20 transition-all disabled:opacity-50 flex items-center justify-center gap-3"
                        >
                            <span v-if="form.processing" class="animate-spin text-2xl">⏳</span>
                            <span v-else>
                                {{ isFree ? '🚀 Enroll Now' : `Complete Payment — ${course?.price ?? 0} ETB` }}
                            </span>
                        </button>
                        
                        <p class="text-center text-slate-400 text-xs mt-4 flex items-center justify-center gap-2">
                            <ShieldCheck class="w-4 h-4" /> Secure 256-bit SSL Connection
                        </p>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-6 shadow-sm border border-slate-100 dark:border-slate-800 sticky top-6">
                    <h3 class="font-black mb-4 uppercase tracking-widest text-xs text-slate-400">Order Summary</h3>
                    
                    <img v-if="course?.image" :src="'/storage/' + course.image" class="w-full h-32 object-cover rounded-2xl mb-4" />
                    
                    <h4 class="font-bold text-lg leading-tight dark:text-white mb-2">{{ course?.title ?? 'Course Title' }}</h4>
                    <p class="text-slate-500 text-sm mb-6">Full lifetime access</p>
                    
                    <div class="space-y-3 pt-4 border-t dark:border-slate-800">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-bold dark:text-white">{{ course?.price ?? 0 }} ETB</span>
                        </div>
                        <div class="flex justify-between text-xl font-black pt-4 dark:text-white border-t border-dashed dark:border-slate-700">
                            <span>Total</span>
                            <span class="text-blue-600">{{ course?.price ?? 0 }} ETB</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>