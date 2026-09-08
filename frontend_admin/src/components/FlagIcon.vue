<script setup>
defineProps({
  country: {
    type: String,
    required: true,
    validator: (value) => ['usa', 'cambodia'].includes(value)
  }
})
</script>

<template>
  <svg
    class="flag-icon"
    viewBox="0 0 30 20"
    role="img"
    :aria-label="country === 'usa' ? 'United States flag' : 'Cambodia flag'"
  >
    <template v-if="country === 'usa'">
      <rect width="30" height="20" fill="#ffffff" />
      <path d="M0 0h30v2H0zm0 4h30v2H0zm0 4h30v2H0zm0 4h30v2H0zm0 4h30v2H0z" fill="#b22234" />
      <rect width="13" height="10.8" fill="#3c3b6e" />
      <path d="M1.4 1.5h1v1h-1zm3 0h1v1h-1zm3 0h1v1h-1zm3 0h1v1h-1zM2.9 3.5h1v1h-1zm3 0h1v1h-1zm3 0h1v1h-1zm3 0h1v1h-1zM1.4 5.5h1v1h-1zm3 0h1v1h-1zm3 0h1v1h-1zm3 0h1v1h-1zM2.9 7.5h1v1h-1zm3 0h1v1h-1zm3 0h1v1h-1zm3 0h1v1h-1z" fill="#ffffff" />
    </template>
    <template v-else>
      <rect width="30" height="20" fill="#032ea1" />
      <rect y="5" width="30" height="10" fill="#e00025" />
      <path
        d="M7.5 14.2h15v1.1h-15zm1.7-1.2h11.6v1.2H9.2zm1.2-1.2h9.2v1.2h-9.2zm1-1.2h7.2v1.2h-7.2zm-1.2 1.2h1.2v2.4h-1.2zm7.3 0h1.2v2.4h-1.2zM12.6 8.8h4.8l1 1.8h-6.8zM14 7.3h2l.7 1.5h-3.4z"
        fill="#ffffff"
      />
    </template>
    <rect x="0.5" y="0.5" width="29" height="19" rx="2" fill="none" stroke="rgba(15,23,42,.18)" />
  </svg>
</template>
