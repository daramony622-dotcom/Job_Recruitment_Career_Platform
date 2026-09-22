<script setup>
import { computed, ref, watch } from 'vue'
import { Edit, Search, Trash2 } from 'lucide-vue-next'
import StatusBadge from './StatusBadge.vue'
import PaginationControls from './PaginationControls.vue'

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Users',
  },
  searchPlaceholder: {
    type: String,
    default: 'Search by name, email, role, status...',
  },
})

const emit = defineEmits(['edit', 'delete'])

const searchQuery = ref('')
const currentPage = ref(1)
const perPage = 15

const filteredItems = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return props.items
  return props.items.filter((item) =>
    [item.name, item.email, item.role, item.status].some((v) =>
      String(v ?? '').toLowerCase().includes(q),
    ),
  )
})

const pagedItems = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredItems.value.slice(start, start + perPage)
})

const lastPage = computed(() => Math.max(1, Math.ceil(filteredItems.value.length / perPage)))

watch(searchQuery, () => { currentPage.value = 1 })
watch(lastPage, (value) => {
  if (currentPage.value > value) currentPage.value = value
})

function onEdit(item) {
  emit('edit', item)
}

function onDelete(item) {
  emit('delete', item)
}
</script>

<template>
  <section class="admin-data-table overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-[#111a26]">
    <!-- Toolbar -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-200 px-4 sm:px-5 py-4 dark:border-slate-800"
    >
      <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">
        {{ title }}
        <span
          class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400"
        >
          {{ filteredItems.length }}
        </span>
      </h2>

      <div class="relative w-full sm:w-72">
        <Search class="pointer-events-none absolute left-3 top-1/2 w-4 h-4 -translate-y-1/2 text-slate-400" />
        <label for="admin-table-search" class="sr-only">Search {{ title }}</label>
        <input
          id="admin-table-search"
          v-model="searchQuery"
          type="search"
          :placeholder="searchPlaceholder"
          class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/15 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:placeholder:text-slate-500"
        />
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr
            class="border-b border-slate-200 bg-slate-50/80 text-[10px] uppercase font-extrabold tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/80 dark:text-slate-400"
          >
            <th class="px-4 py-2.5 font-bold">Name</th>
            <th class="px-4 py-2.5 font-bold">Email</th>
            <th class="px-4 py-2.5 font-bold">Role</th>
            <th class="px-4 py-2.5 font-bold">Status</th>
            <th class="px-4 py-2.5 font-bold text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-if="loading">
            <td colspan="5" class="px-4 py-8 text-center text-xs text-slate-500">
              Loading records...
            </td>
          </tr>
          <tr v-else-if="!filteredItems.length">
            <td colspan="5" class="px-4 py-8 text-center text-xs text-slate-500">
              No matching records found.
            </td>
          </tr>
          <tr
            v-for="(item, index) in pagedItems"
            :key="item.id ?? index"
            class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
          >
            <td class="px-4 py-2.5 font-bold text-slate-900 whitespace-nowrap dark:text-slate-100">
              {{ item.name }}
            </td>
            <td class="px-4 py-2.5 text-slate-600 whitespace-nowrap dark:text-slate-300 font-medium">
              {{ item.email }}
            </td>
            <td class="px-4 py-2.5 whitespace-nowrap">
              <span
                class="inline-flex rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-bold text-slate-600 capitalize dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300"
              >
                {{ item.role }}
              </span>
            </td>
            <td class="px-4 py-2.5 whitespace-nowrap">
              <StatusBadge :value="item.status" />
            </td>
            <td class="px-4 py-2.5 whitespace-nowrap">
              <div class="flex items-center justify-end gap-1">
                <button
                  class="rounded-lg p-1.5 text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition-colors dark:hover:bg-slate-800 dark:hover:text-cyan-400 cursor-pointer"
                  :aria-label="`Edit ${item.name}`"
                  @click="onEdit(item)"
                >
                  <Edit class="w-3.5 h-3.5" />
                </button>
                <button
                  class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors dark:hover:bg-slate-800 dark:hover:text-rose-400 cursor-pointer"
                  :aria-label="`Delete ${item.name}`"
                  @click="onDelete(item)"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <PaginationControls
      :current-page="currentPage"
      :last-page="lastPage"
      :total="filteredItems.length"
      :per-page="perPage"
      @update:page="currentPage = $event"
    />
  </section>
</template>