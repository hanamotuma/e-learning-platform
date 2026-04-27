<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    certificate: Object
});

const printCertificate = () => {
    window.print();
};
</script>

<template>
    <Head title="Your Certificate" />

    <div class="min-h-screen bg-[#0f1129] p-8 flex flex-col items-center font-sans">
        <div class="mb-8 flex gap-4 no-print">
            <a :href="route('admin.certificates.download', certificate.id)" 
               target="_blank"
               class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-full font-bold transition-all shadow-lg shadow-indigo-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download Official PDF
            </a>
            <button @click="printCertificate" 
                    class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-8 py-3 rounded-full font-bold transition-all backdrop-blur-md border border-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>
        </div>

        <div id="certificate-print-area" class="bg-[#fdfdfd] text-slate-900 w-full max-w-[1100px] aspect-[1.414/1] p-2 relative shadow-2xl border border-slate-300">
            
            <div class="h-full w-full border-[20px] border-double border-slate-100 p-1 relative overflow-hidden">
                
                <div class="absolute inset-0 opacity-[0.03] pointer-events-none flex items-center justify-center">
                    <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse">
                                <path d="M 100 0 L 0 0 0 100" fill="none" stroke="currentColor" stroke-width="1"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)" />
                    </svg>
                </div>

                <div class="h-full w-full border-2 border-indigo-900/10 p-16 flex flex-col items-center justify-between relative">
                    
                    <div class="text-center">
                        <div class="flex justify-center mb-6">
                            <div class="bg-indigo-900 text-white p-3 rounded-lg font-black text-2xl tracking-tighter shadow-xl">
                                PRO<span class="text-indigo-400">LEARN</span>
                            </div>
                        </div>
                        <h3 class="text-indigo-900 font-semibold tracking-[0.4em] uppercase text-sm mb-2">Academic Achievement</h3>
                        <div class="w-16 h-0.5 bg-indigo-900 mx-auto opacity-20"></div>
                    </div>

                    <div class="text-center flex flex-col items-center">
                        <p class="text-slate-500 italic font-serif text-lg mb-4">This is to certify that</p>
                        
                        <h1 class="text-6xl font-serif font-bold mb-6 text-slate-800 tracking-tight">
                            {{ $page.props.auth.user.name }}
                        </h1>
                        
                        <p class="text-lg text-slate-600 max-w-2xl leading-relaxed mb-8">
                            has demonstrated exceptional proficiency and successfully completed all 
                            prescribed academic requirements for the professional certification in
                        </p>

                        <h2 class="text-4xl font-extrabold text-indigo-950 uppercase tracking-wide">
                            {{ certificate.enrollment?.course?.title || 'Advanced Master Course' }}
                        </h2>
                    </div>

                    <div class="w-full flex justify-between items-end mt-12 px-8">
                        
                        <div class="text-center w-48">
                            <div class="border-b border-slate-300 pb-2 mb-2">
                                <p class="font-bold text-slate-800">{{ certificate.issued_at_human || 'April 25, 2026' }}</p>
                            </div>
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Issue Date</p>
                        </div>

                        <div class="relative flex items-center justify-center">
                            <div class="w-32 h-32 rounded-full border-4 border-amber-200 bg-amber-50 flex items-center justify-center shadow-inner">
                                <div class="w-24 h-24 rounded-full border border-amber-300 flex items-center justify-center text-amber-600 text-center leading-none">
                                    <div class="text-[10px] font-bold uppercase tracking-tighter">
                                        Verified<br>
                                        <span class="text-lg">★</span><br>
                                        Secure
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center w-48">
                            <div class="border-b border-slate-300 pb-1 mb-2">
                                <p class="font-serif italic text-2xl text-indigo-900 select-none">Bereket Bogale</p>
                            </div>
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Program Director</p>
                        </div>
                    </div>

                    <div class="mt-12 flex justify-between w-full text-[9px] uppercase tracking-[0.2em] text-slate-400 font-bold">
                        <span>Verification ID: {{ certificate.certificate_number }}</span>
                        <span>Verify at: prolearn.app/verify</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600;800&display=swap');

#certificate-print-area {
    font-family: 'Inter', sans-serif;
}

h1, h2, .font-serif {
    font-family: 'Playfair Display', serif;
}

@media print {
    @page {
        size: landscape;
        margin: 0;
    }
    .no-print { display: none; }
    body { background: white; p: 0; }
    .min-h-screen { background: white; padding: 0; }
    #certificate-print-area { 
        box-shadow: none; 
        border: none;
        max-width: 100%;
        width: 100vw;
        height: 100vh;
    }
}
</style>