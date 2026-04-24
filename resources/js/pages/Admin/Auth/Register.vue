<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  password: ''
})

const submit = () => {
  form.post(route('admin.register.submit'))
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-[#0f0a24] text-white">

    <form @submit.prevent="submit" class="w-96 p-6 bg-[#16103a] rounded-xl space-y-4">

      <h1 class="text-xl font-bold">Admin Register</h1>

      <input v-model="form.name" placeholder="Name"
        class="w-full p-3 bg-black/40 rounded" />

      <input v-model="form.email" placeholder="Email"
        class="w-full p-3 bg-black/40 rounded" />

      <input v-model="form.password" type="password" placeholder="Password"
        class="w-full p-3 bg-black/40 rounded" />

      <button class="w-full bg-green-600 py-2 rounded">
        Register
      </button>

    </form>

  </div>
</template>