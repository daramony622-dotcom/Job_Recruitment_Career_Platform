<script setup>
import { computed } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
  currentPage: { type: Number, default: 1 },
  lastPage: { type: Number, default: 1 },
  total: { type: Number, default: 0 },
  perPage: { type: Number, default: 15 },
})

const emit = defineEmits(['update:page'])

const pages = computed(() => {
  const last = Math.max(1, props.lastPage)
  const current = Math.min(Math.max(1, props.currentPage), last)
  const start = Math.max(1, Math.min(current - 2, last - 4))
  const end = Math.min(last, start + 4)
  return Array.from({ length: end - start + 1 }, (_, index) => start + index)
})

const firstItem = computed(() => props.total ? ((props.currentPage - 1) * props.perPage) + 1 : 0)
const lastItem = computed(() => Math.min(props.currentPage * props.perPage, props.total))

function goTo(page) {
  if (page >= 1 && page <= props.lastPage && page !== props.currentPage) emit('update:page', page)
}
</script>

<template>
  <nav v-if="lastPage > 1 || total > 0" class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 text-xs sm:flex-row sm:items-center sm:justify-between dark:border-slate-800" aria-label="Pagination">
    <p class="text-slate-500 dark:text-slate-400">
      Showing <span class="font-semibold text-slate-700 dark:text-slate-200">{{ firstItem }}-{{ lastItem }}</span> of <span class="font-semibold text-slate-700 dark:text-slate-200">{{ total }}</span>
    </p>
    <div class="flex items-center gap-1">
      <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-blue-300 hover:text-blue-700 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:hover:border-blue-700 dark:hover:text-blue-300" :disabled="currentPage <= 1" aria-label="Previous page" @click="goTo(currentPage - 1)">
        <ChevronLeft class="h-4 w-4" />
      </button>
      <button v-for="page in pages" :key="page" type="button" class="h-8 min-w-8 rounded-lg px-2 font-semibold transition" :class="page === currentPage ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700 dark:text-slate-300 dark:hover:bg-blue-950/50 dark:hover:text-blue-300'" :aria-current="page === currentPage ? 'page' : undefined" @click="goTo(page)">
        {{ page }}
      </button>
      <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-blue-300 hover:text-blue-700 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:hover:border-blue-700 dark:hover:text-blue-300" :disabled="currentPage >= lastPage" aria-label="Next page" @click="goTo(currentPage + 1)">
        <ChevronRight class="h-4 w-4" />
      </button>
    </div>
  </nav>
</template>
