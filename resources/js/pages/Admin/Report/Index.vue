<script setup>
import { useForm, usePage } from '@inertiajs/vue3'; // Added usePage
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue'; // Import the Layout

const props = defineProps({ 
    reports: Object 
});

// Access flash messages from the session
const page = usePage();
const flashSuccess = computed(() => page.props.flash.success);

const form = useForm({
    title: '',
    report_type: 'course',
    parameters: {}
});

const submit = () => {
    form.post(route('admin.reports.store'), {
        onSuccess: () => {
            form.reset();
        },
        preserveScroll: true
    });
};
</script>

<template>
    <AdminLayout> <div class="p-6 max-w-6xl mx-auto">
            
            <div v-if="flashSuccess" class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
                {{ flashSuccess }}
            </div>

            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Admin Reports</h2>
                    <div class="text-sm text-gray-500">Manage and generate system data exports</div>
                </div>
            </div>

            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-10">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-600 mb-4">Generate New Report</h3>
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-1">
                        <input v-model="form.title" type="text" placeholder="Report Title (e.g. May Payments)" 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required />
                    </div>
                    
                    <div class="md:col-span-1">
                        <select v-model="form.report_type" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="course">Course Report</option>
                            <option value="progress">Student Progress</option>
                            <option value="payment">Payments & Revenue</option> <option value="admin">Admin Logs</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 flex gap-3">
                        <button :disabled="form.processing" type="submit" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 disabled:opacity-50">
                            <span v-if="form.processing">Processing...</span>
                            <span v-else>🚀 Generate Report</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="p-4 font-semibold text-gray-600">Title</th>
                            <th class="p-4 font-semibold text-gray-600">Type</th>
                            <th class="p-4 font-semibold text-gray-600">Status</th>
                            <th class="p-4 font-semibold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="report in reports.data" :key="report.id" class="hover:bg-gray-50 transition">
                            <td class="p-4">
                                <div class="font-medium text-gray-900">{{ report.title }}</div>
                                <div class="text-xs text-gray-400">{{ new Date(report.created_at).toLocaleDateString() }}</div>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded uppercase tracking-tighter">
                                    {{ report.report_type }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span v-if="report.status === 'pending'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    ⏳ Pending
                                </span>
                                <span v-else-if="report.status === 'processing'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 animate-pulse">
                                    ⚙️ Processing
                                </span>
                                <span v-else-if="report.status === 'completed'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ✅ Completed
                                </span>
                                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    ❌ Failed
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <a v-if="report.status === 'completed'" 
                                   :href="route('admin.reports.enrollment.download', { report: report.id })" 
                                   class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-md font-semibold text-sm transition">
                                    Download
                                </a>
                                <span v-else class="text-gray-400 text-sm italic">
                                    Processing...
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>