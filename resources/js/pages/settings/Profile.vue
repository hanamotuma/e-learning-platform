<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

// Profile picture preview
const profilePicturePreview = ref<string | null>(user?.profile_picture_url || null);
const selectedFile = ref<File | null>(null);

const form = useForm({
    name: user?.name || '',
    username: user?.username || '',
    email: user?.email || '',
    address: user?.address || '',
    city: user?.city || '',
    state: user?.state || '',
    zip_code: user?.zip_code || '',
    country: user?.country || '',
    phone: user?.phone || '',
    bio: user?.bio || '',
    interests: user?.interests || '',
    education: user?.education || '',
    profile_picture: null as File | null,
});

// CRITICAL: Use patch method - NOT put, NOT post
const submit = () => {
    console.log('Submitting with PATCH method');
    
    form.patch('/profile', {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Profile updated successfully');
            selectedFile.value = null;
            const fileInput = document.getElementById('profile_picture') as HTMLInputElement;
            if (fileInput) fileInput.value = '';
        },
        onError: (errors) => {
            console.error('Update failed:', errors);
        },
    });
};

const handleFileSelect = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        selectedFile.value = file;
        form.profile_picture = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            profilePicturePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeProfilePicture = () => {
    profilePicturePreview.value = null;
    selectedFile.value = null;
    form.profile_picture = null;
    const fileInput = document.getElementById('profile_picture') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Profile Picture Section -->
                    <div class="grid gap-2">
                        <Label>Profile Picture</Label>
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                    <img 
                                        v-if="profilePicturePreview" 
                                        :src="profilePicturePreview" 
                                        class="w-full h-full object-cover"
                                        alt="Profile preview"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-blue-600 text-white text-3xl font-bold">
                                        {{ form.name?.charAt(0) || user?.name?.charAt(0) || 'U' }}
                                    </div>
                                </div>
                                <button 
                                    v-if="profilePicturePreview"
                                    type="button"
                                    @click="removeProfilePicture"
                                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 text-sm"
                                >
                                    ×
                                </button>
                            </div>
                            <div>
                                <input 
                                    id="profile_picture"
                                    type="file" 
                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                    @change="handleFileSelect"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                />
                                <p class="text-xs text-gray-500 mt-1">Max file size: 5MB. Allowed: JPG, PNG, GIF, WEBP</p>
                                <InputError class="mt-2" :message="form.errors.profile_picture" />
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Full Name</Label>
                        <Input 
                            id="name" 
                            class="mt-1 block w-full" 
                            v-model="form.name" 
                            required 
                            autocomplete="name" 
                            placeholder="Full name" 
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="username">Username</Label>
                        <Input 
                            id="username"
                            class="mt-1 block w-full" 
                            v-model="form.username" 
                            autocomplete="username" 
                            placeholder="Username" 
                        />
                        <InputError class="mt-2" :message="form.errors.username" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="email"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone Number</Label>
                        <Input
                            id="phone"
                            type="tel"
                            class="mt-1 block w-full"
                            v-model="form.phone"
                            autocomplete="tel"
                            placeholder="Phone number"
                        />
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="bio">Bio</Label>
                        <textarea
                            id="bio"
                            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            v-model="form.bio"
                            rows="4"
                            placeholder="Tell us a little about yourself..."
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.bio" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="address">Address</Label>
                        <Input
                            id="address"
                            class="mt-1 block w-full"
                            v-model="form.address"
                            placeholder="Your address"
                        />
                        <InputError class="mt-2" :message="form.errors.address" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="city">City</Label>
                            <Input
                                id="city"
                                class="mt-1 block w-full"
                                v-model="form.city"
                                placeholder="City"
                            />
                            <InputError class="mt-2" :message="form.errors.city" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="state">State/Region</Label>
                            <Input
                                id="state"
                                class="mt-1 block w-full"
                                v-model="form.state"
                                placeholder="State or region"
                            />
                            <InputError class="mt-2" :message="form.errors.state" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="zip_code">ZIP/Postal Code</Label>
                            <Input
                                id="zip_code"
                                class="mt-1 block w-full"
                                v-model="form.zip_code"
                                placeholder="ZIP code"
                            />
                            <InputError class="mt-2" :message="form.errors.zip_code" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="country">Country</Label>
                            <Input
                                id="country"
                                class="mt-1 block w-full"
                                v-model="form.country"
                                placeholder="Country"
                            />
                            <InputError class="mt-2" :message="form.errors.country" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="education">Education</Label>
                        <Input
                            id="education"
                            class="mt-1 block w-full"
                            v-model="form.education"
                            placeholder="Your educational background"
                        />
                        <InputError class="mt-2" :message="form.errors.education" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="interests">Interests</Label>
                        <Input
                            id="interests"
                            class="mt-1 block w-full"
                            v-model="form.interests"
                            placeholder="What are you interested in learning?"
                        />
                        <InputError class="mt-2" :message="form.errors.interests" />
                    </div>

                    <div v-if="mustVerifyEmail && user && !user.email_verified_at">
                        <p class="mt-2 text-sm text-neutral-800">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="rounded-md text-sm text-neutral-600 underline hover:text-neutral-900 focus:outline-none focus:ring-2 focus:ring-offset-2"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing" type="submit">Save Changes</Button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-green-600">Saved successfully!</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>