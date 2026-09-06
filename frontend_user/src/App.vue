<script setup>
import { onMounted } from 'vue'
import { initTheme } from './composables/useTheme'
import { useAuth } from './composables/useAuth'

const { fetchCurrentUser } = useAuth()

onMounted(() => {
  initTheme()
  fetchCurrentUser()
})
</script>

<template>
  <router-view />
</template>
