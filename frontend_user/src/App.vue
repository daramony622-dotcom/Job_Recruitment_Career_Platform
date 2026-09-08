<script setup>
import { onMounted } from 'vue'
import { initTheme } from './composables/useTheme'
import { useAuth } from './composables/useAuth'

const { fetchCurrentUser, fetchProfileAvatar } = useAuth()

onMounted(() => {
  initTheme()
  fetchCurrentUser()
  fetchProfileAvatar()
})
</script>

<template>
  <div>
    <div id="google_translate_element" class="google-translate-host" aria-hidden="true"></div>
    <router-view v-slot="{ Component, route }">
      <Transition name="page" mode="out-in" appear>
        <component :is="Component" :key="route.path" />
      </Transition>
    </router-view>
  </div>
</template>
