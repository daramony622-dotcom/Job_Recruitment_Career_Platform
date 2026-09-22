<script setup>
defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <div class="space-y-4">
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">
          Browse by Category
        </h2>
        <p class="text-xs text-slate-500 dark:text-slate-400">
          Find opportunities in your field
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="cat in categories"
        :key="cat.id || cat.title"
        class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4 hover:shadow-md transition cursor-pointer"
      >
        <div
          :class="[
            cat.color ||
              'bg-blue-50 text-blue-600 dark:bg-slate-800 dark:text-blue-400',
          ]"
          class="p-3 rounded-xl font-bold text-lg flex items-center justify-center w-12 h-12"
        >
          <component :is="cat.icon" v-if="cat.icon" class="w-6 h-6" />
          <span v-else>📁</span>
        </div>
        <div>
          <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm">
            {{ cat.title }}
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            {{ cat.roles }} open roles
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
