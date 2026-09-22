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
  <section class="admin-form-card">
    <!-- Header -->
    <div class="admin-form-header">
      <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">
        {{ mode === 'create' ? 'Add Record' : `Update Record` }}
      </h2>
      <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
        {{ mode === 'create' ? 'Create a new record' : 'Edit the selected record' }}
      </p>
    </div>

    <!-- Body -->
    <form class="admin-form-body" @submit.prevent="onSubmit">
      <div
        v-for="field in fields"
        :key="field.name"
        class="admin-form-field"
      >
        <label
          :for="field.name"
          class="shrink-0 text-xs font-bold text-slate-700 dark:text-slate-300"
        >
          {{ field.label }}
        </label>

        <select
          v-if="field.type === 'select'"
          :id="field.name"
          :value="getValue(field)"
          class="setting-input"
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
          class="setting-input resize-none"
          @input="setField(field.name, $event.target.value)"
        ></textarea>

        <input
          v-else
          :id="field.name"
          :type="field.type || 'text'"
          :value="getValue(field)"
          :placeholder="field.placeholder"
          class="setting-input"
          @input="setField(field.name, $event.target.value)"
        />
      </div>
    </form>

    <!-- Footer -->
    <div class="admin-form-footer">
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