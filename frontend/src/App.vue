<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { initTheme } from './composables/useTheme'
import { useAuth } from './composables/useAuth'
import LanguageSwitcher from './admin/LanguageSwitcher.vue'
import AdminLayout from './admin/AdminLayout.vue'

const { fetchCurrentUser, fetchProfileAvatar } = useAuth()
const route = useRoute()
const isAdminRoute = computed(() => Boolean(route.meta.admin))

onMounted(() => {
  initTheme()
  fetchCurrentUser()
  fetchProfileAvatar()
})
</script>
<template>
  <div>
    <div id="google_translate_element" class="google-translate-host" aria-hidden="true"></div>
    <AdminLayout v-if="isAdminRoute">
      <router-view v-slot="{ Component, route: currentRoute }">
        <Transition name="page" mode="out-in" appear>
          <component :is="Component" :key="currentRoute.path" />
        </Transition>
      </router-view>
    </AdminLayout>
    <router-view v-else v-slot="{ Component, route: currentRoute }">
      <Transition name="page" mode="out-in" appear>
        <component :is="Component" :key="currentRoute.path" />
      </Transition>
    </router-view>
  </div>
</template>
