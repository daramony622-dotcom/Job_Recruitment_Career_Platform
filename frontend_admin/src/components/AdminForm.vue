<script setup>
import { ref } from 'vue'
import { Save, UserPlus, X } from 'lucide-vue-next'

const props = defineProps({
  mode: {
    type: String,
    default: 'create',
    validator: (v) => ['create', 'update'].includes(v),
  },
  fields: {
    type: Array,
    default: () => [],
  },
  initialValues: {
    type: Object,
    default: () => ({}),
  },
  submitting: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit', 'cancel'])

const form = ref({ ...props.initialValues })

function setField(name, value) {
  form.value[name] = value
}

function getValue(field) {
  return form.value[field.name] ?? field.default ?? ''
}

function onSubmit() {
  emit('submit', { ...form.value })
}
</script>

<template>
  <section class="rounded-2xl border border-slate-800 bg-slate-900 overflow-hidden">
    <!-- Header -->
    <div class="border-b border-slate-800 px-5 py-4">
      <h2 class="text-sm font-bold text-slate-200">
        {{ mode === 'create' ? 'Add Record' : `Update Record` }}
      </h2>
      <p class="mt-0.5 text-xs text-slate-500">
        {{ mode === 'create' ? 'Create a new record' : 'Edit the selected record' }}
      </p>
    </div>

    <!-- Body -->
    <form class="p-5 space-y-5" @submit.prevent="onSubmit">
      <div
        v-for="field in fields"
        :key="field.name"
        class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-4"
      >
        <label
          :for="field.name"
          class="sm:w-40 shrink-0 text-sm font-medium text-slate-300"
        >
          {{ field.label }}
        </label>

        <select
          v-if="field.type === 'select'"
          :id="field.name"
          :value="getValue(field)"
          class="flex-1 appearance-none rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600/60 focus:border-blue-600/60"
          @change="setField(field.name, $event.target.value)"
        >
          <option value="" disabled>{{ field.placeholder || 'Select an option' }}</option>
          <option
            v-for="opt in field.options"
            :key="opt.value"
            :value="opt.value"
          >
            {{ opt.label }}
          </option>
        </select>

        <textarea
          v-else-if="field.type === 'textarea'"
          :id="field.name"
          :value="getValue(field)"
          :placeholder="field.placeholder"
          :rows="field.rows || 3"
          class="flex-1 resize-none rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-slate-200 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-600/60 focus:border-blue-600/60"
          @input="setField(field.name, $event.target.value)"
        ></textarea>

        <input
          v-else
          :id="field.name"
          :type="field.type || 'text'"
          :value="getValue(field)"
          :placeholder="field.placeholder"
          class="flex-1 rounded-lg border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-slate-200 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-600/60 focus:border-blue-600/60"
          @input="setField(field.name, $event.target.value)"
        />
      </div>
    </form>

    <!-- Footer -->
    <div class="flex items-center justify-end gap-3 border-t border-slate-800 px-5 py-4">
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-lg border border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800 hover:text-slate-100 transition-colors"
        :disabled="submitting"
        @click="$emit('cancel')"
      >
        <X class="w-4 h-4" />
        {{ mode === 'create' ? 'Cancel' : 'Reset' }}
      </button>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        :disabled="submitting"
        @click="onSubmit"
      >
        <Save v-if="mode === 'update'" class="w-4 h-4" />
        <UserPlus v-else class="w-4 h-4" />
        {{ submitting ? 'Saving...' : mode === 'create' ? 'Create' : 'Save' }}
      </button>
    </div>
  </section>
</template>