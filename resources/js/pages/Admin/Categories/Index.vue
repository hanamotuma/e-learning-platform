<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{
  categories: {
    data: Array<any>
  }
}>()

const deleteCategory = (id: number) => {
  if (confirm('Delete this category?')) {
    router.delete(route('categories.destroy', id))
  }
}

const hasCategories = computed(() => (props.categories?.data?.length ?? 0) > 0)
</script>

<template>
  <div class="min-h-screen bg-[#0f0a24] text-white p-6">

    <!-- Header -->
    <div class="flex justify-between mb-6">
      <h1 class="text-2xl font-bold">Categories</h1>

      <Link
        :href="route('admin.categories.create')"
        class="bg-purple-600 px-4 py-2 rounded"
      >
        + Add Category
      </Link>
    </div>

    <!-- Table -->
    <div class="bg-[#16103a] p-4 rounded-xl">

      <table class="w-full text-left">
        <thead>
          <tr class="text-gray-400">
            <th>ID</th>
            <th>Icon</th> <!-- ✅ added -->
            <th>Name</th>
            <th>Description</th> <!-- ✅ added -->
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>

          <tr
            v-for="category in categories.data"
            :key="category.id"
            class="border-t border-white/10"
          >
            <td>{{ category.id }}</td>

            <!-- ✅ icon -->
            <td>
              <i v-if="category.icon" :class="category.icon"></i>
              <span v-else class="text-gray-500">—</span>
            </td>

            <td>{{ category.name }}</td>

            <!-- ✅ description -->
            <td>
              {{ category.description || '—' }}
            </td>

            <td class="flex gap-3">
              <Link
                :href="route('admin.categories.edit', category.id)"
                class="text-blue-400"
              >
                Edit
              </Link>

              <button
                @click="deleteCategory(category.id)"
                class="text-red-400"
              >
                Delete
              </button>
            </td>
          </tr>

          <!-- Empty state -->
          <tr v-if="!hasCategories">
            <td colspan="5" class="text-center py-6 text-gray-500">
              No categories found
            </td>
          </tr>

        </tbody>
      </table>

    </div>

  </div>
</template>