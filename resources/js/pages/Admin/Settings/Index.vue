<script setup>
import { ref, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { 
  DollarSign, Globe, Mail, Lock, Shield, Bell, 
  Palette, Database, Users, Award, CreditCard, Save,
  Upload, Percent, Smartphone, Building, Clock
} from 'lucide-vue-next'
import axios from 'axios'

const activeTab = ref('general')
const isLoading = ref(true)
const isSaving = ref(false)
const settings = ref({
  // General Settings
  site_name: 'LearnHub',
  site_description: 'Online Learning Platform',
  contact_email: 'admin@learnhub.com',
  contact_phone: '',
  address: '',
  
  // Commission Settings
  platform_commission: 10,
  instructor_commission: 90,
  tax_rate: 0,
  
  // Payment Settings
  currency: 'ETB',
  currency_symbol: 'ETB',
  payment_methods: ['telebirr', 'cbe_birr', 'card'],
  chapa_secret_key: '',
  chapa_public_key: '',
  
  // Email Settings
  smtp_host: '',
  smtp_port: 587,
  smtp_username: '',
  smtp_password: '',
  mail_from_address: 'noreply@learnhub.com',
  mail_from_name: 'LearnHub',
  
  // Security Settings
  require_email_verification: true,
  max_login_attempts: 5,
  session_timeout: 120,
  
  // Appearance
  primary_color: '#4f46e5',
  logo_url: '',
  favicon_url: '',
  
  // Course Settings
  default_course_price: 499,
  max_quiz_attempts: 3,
  passing_score: 70,
  
  // Notification Settings
  email_notifications: true,
  push_notifications: true,
  admin_notification_email: ''
})

const fetchSettings = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('/admin/settings/data')
    settings.value = { ...settings.value, ...response.data }
  } catch (error) {
    console.error('Error fetching settings:', error)
  } finally {
    isLoading.value = false
  }
}

const saveSettings = async () => {
  isSaving.value = true
  try {
    await axios.post('/admin/settings', settings.value)
    alert('Settings saved successfully!')
  } catch (error) {
    alert('Error saving settings')
  } finally {
    isSaving.value = false
  }
}

const handleLogoUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      settings.value.logo_url = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

onMounted(() => {
  fetchSettings()
})
</script>

<template>
  <Head title="Platform Settings | Admin" />
  
  <AdminLayout>
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-3xl font-bold dark:text-white">Platform Settings</h1>
        <p class="text-slate-500 mt-1">Configure your platform settings</p>
      </div>
      
      <div v-if="isLoading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600 mx-auto"></div>
        <p class="text-slate-500 mt-4">Loading settings...</p>
      </div>
      
      <div v-else class="grid lg:grid-cols-4 gap-6">
        <!-- Sidebar Tabs -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-slate-800 rounded-xl border p-4 sticky top-24">
            <div class="space-y-1">
              <button @click="activeTab = 'general'" :class="[
                'w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all',
                activeTab === 'general' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20' : 'hover:bg-slate-50'
              ]">
                <Globe class="w-5 h-5" />
                <span>General</span>
              </button>
              <button @click="activeTab = 'commission'" :class="[
                'w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all',
                activeTab === 'commission' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20' : 'hover:bg-slate-50'
              ]">
                <Percent class="w-5 h-5" />
                <span>Commission</span>
              </button>
              <button @click="activeTab = 'payment'" :class="[
                'w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all',
                activeTab === 'payment' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20' : 'hover:bg-slate-50'
              ]">
                <CreditCard class="w-5 h-5" />
                <span>Payment</span>
              </button>
              <button @click="activeTab = 'email'" :class="[
                'w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all',
                activeTab === 'email' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20' : 'hover:bg-slate-50'
              ]">
                <Mail class="w-5 h-5" />
                <span>Email</span>
              </button>
              <button @click="activeTab = 'security'" :class="[
                'w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all',
                activeTab === 'security' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20' : 'hover:bg-slate-50'
              ]">
                <Shield class="w-5 h-5" />
                <span>Security</span>
              </button>
              <button @click="activeTab = 'appearance'" :class="[
                'w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all',
                activeTab === 'appearance' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20' : 'hover:bg-slate-50'
              ]">
                <Palette class="w-5 h-5" />
                <span>Appearance</span>
              </button>
              <button @click="activeTab = 'courses'" :class="[
                'w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all',
                activeTab === 'courses' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20' : 'hover:bg-slate-50'
              ]">
                <Award class="w-5 h-5" />
                <span>Course Settings</span>
              </button>
              <button @click="activeTab = 'notifications'" :class="[
                'w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all',
                activeTab === 'notifications' ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/20' : 'hover:bg-slate-50'
              ]">
                <Bell class="w-5 h-5" />
                <span>Notifications</span>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Settings Content -->
        <div class="lg:col-span-3">
          <div class="bg-white dark:bg-slate-800 rounded-xl border p-6">
            <!-- General Settings -->
            <div v-if="activeTab === 'general'">
              <h2 class="text-xl font-bold mb-6">General Settings</h2>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Site Name</label>
                  <input v-model="settings.site_name" type="text" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Site Description</label>
                  <textarea v-model="settings.site_description" rows="2" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900"></textarea>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Contact Email</label>
                  <input v-model="settings.contact_email" type="email" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Contact Phone</label>
                  <input v-model="settings.contact_phone" type="tel" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Address</label>
                  <textarea v-model="settings.address" rows="2" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900"></textarea>
                </div>
              </div>
            </div>
            
            <!-- Commission Settings -->
            <div v-if="activeTab === 'commission'">
              <h2 class="text-xl font-bold mb-6">Commission Settings</h2>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Platform Commission (%)</label>
                  <div class="flex items-center gap-2">
                    <input v-model="settings.platform_commission" type="number" class="w-32 px-4 py-2 border rounded-lg dark:bg-slate-900" />
                    <span class="text-slate-500">%</span>
                  </div>
                  <p class="text-xs text-slate-400 mt-1">Percentage deducted from each sale (default: 10%)</p>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Instructor Commission (%)</label>
                  <div class="flex items-center gap-2">
                    <input v-model="settings.instructor_commission" type="number" class="w-32 px-4 py-2 border rounded-lg dark:bg-slate-900" />
                    <span class="text-slate-500">%</span>
                  </div>
                  <p class="text-xs text-slate-400 mt-1">Instructor earnings after commission (default: 90%)</p>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Tax Rate (%)</label>
                  <div class="flex items-center gap-2">
                    <input v-model="settings.tax_rate" type="number" class="w-32 px-4 py-2 border rounded-lg dark:bg-slate-900" />
                    <span class="text-slate-500">%</span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Payment Settings -->
            <div v-if="activeTab === 'payment'">
              <h2 class="text-xl font-bold mb-6">Payment Settings</h2>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Currency</label>
                  <input v-model="settings.currency" type="text" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" placeholder="ETB" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Chapa Secret Key</label>
                  <input v-model="settings.chapa_secret_key" type="password" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Chapa Public Key</label>
                  <input v-model="settings.chapa_public_key" type="password" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
              </div>
            </div>
            
            <!-- Email Settings -->
            <div v-if="activeTab === 'email'">
              <h2 class="text-xl font-bold mb-6">Email Settings</h2>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium mb-1">SMTP Host</label>
                  <input v-model="settings.smtp_host" type="text" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium mb-1">SMTP Port</label>
                    <input v-model="settings.smtp_port" type="number" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1">SMTP Username</label>
                    <input v-model="settings.smtp_username" type="text" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">SMTP Password</label>
                  <input v-model="settings.smtp_password" type="password" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">From Email Address</label>
                  <input v-model="settings.mail_from_address" type="email" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
              </div>
            </div>
            
            <!-- Security Settings -->
            <div v-if="activeTab === 'security'">
              <h2 class="text-xl font-bold mb-6">Security Settings</h2>
              <div class="space-y-4">
                <label class="flex items-center gap-3">
                  <input v-model="settings.require_email_verification" type="checkbox" class="w-4 h-4" />
                  <span>Require email verification for new users</span>
                </label>
                <div>
                  <label class="block text-sm font-medium mb-1">Max Login Attempts</label>
                  <input v-model="settings.max_login_attempts" type="number" class="w-32 px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Session Timeout (minutes)</label>
                  <input v-model="settings.session_timeout" type="number" class="w-32 px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
              </div>
            </div>
            
            <!-- Appearance Settings -->
            <div v-if="activeTab === 'appearance'">
              <h2 class="text-xl font-bold mb-6">Appearance Settings</h2>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Primary Color</label>
                  <div class="flex items-center gap-3">
                    <input v-model="settings.primary_color" type="color" class="w-16 h-16 rounded-lg cursor-pointer" />
                    <input v-model="settings.primary_color" type="text" class="flex-1 px-4 py-2 border rounded-lg dark:bg-slate-900" />
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Logo</label>
                  <div class="flex items-center gap-4">
                    <div v-if="settings.logo_url" class="w-20 h-20 border rounded-lg overflow-hidden">
                      <img :src="settings.logo_url" class="w-full h-full object-cover" />
                    </div>
                    <button @click="$refs.logoInput.click()" class="px-4 py-2 border rounded-lg hover:bg-slate-50 flex items-center gap-2">
                      <Upload class="w-4 h-4" />
                      Upload Logo
                    </button>
                    <input ref="logoInput" type="file" accept="image/*" class="hidden" @change="handleLogoUpload" />
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Course Settings -->
            <div v-if="activeTab === 'courses'">
              <h2 class="text-xl font-bold mb-6">Course Settings</h2>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium mb-1">Default Course Price (ETB)</label>
                  <input v-model="settings.default_course_price" type="number" class="w-32 px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Max Quiz Attempts</label>
                  <input v-model="settings.max_quiz_attempts" type="number" class="w-32 px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Passing Score (%)</label>
                  <input v-model="settings.passing_score" type="number" class="w-32 px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
              </div>
            </div>
            
            <!-- Notification Settings -->
            <div v-if="activeTab === 'notifications'">
              <h2 class="text-xl font-bold mb-6">Notification Settings</h2>
              <div class="space-y-4">
                <label class="flex items-center gap-3">
                  <input v-model="settings.email_notifications" type="checkbox" class="w-4 h-4" />
                  <span>Enable email notifications</span>
                </label>
                <label class="flex items-center gap-3">
                  <input v-model="settings.push_notifications" type="checkbox" class="w-4 h-4" />
                  <span>Enable push notifications</span>
                </label>
                <div>
                  <label class="block text-sm font-medium mb-1">Admin Notification Email</label>
                  <input v-model="settings.admin_notification_email" type="email" class="w-full px-4 py-2 border rounded-lg dark:bg-slate-900" />
                </div>
              </div>
            </div>
            
            <!-- Save Button -->
            <div class="pt-6 border-t mt-6">
              <button @click="saveSettings" :disabled="isSaving" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <Save class="w-4 h-4" />
                {{ isSaving ? 'Saving...' : 'Save Settings' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>