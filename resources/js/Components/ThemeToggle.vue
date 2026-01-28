<template>
  <button 
    class="theme-toggle inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 transition" 
    :aria-pressed="isDark" 
    @click="toggle" 
    title="Toggle dark / light mode"
  >
    <span v-if="isDark" class="text-lg">🌙</span>
    <span v-else class="text-lg">☀️</span>
  </button>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const isDark = ref(false)

function applyTheme(dark) {
  document.documentElement.classList.toggle('dark', dark)
  try { localStorage.setItem('theme', dark ? 'dark' : 'light') } catch (e) {}
}

onMounted(() => {
  try {
    // Only apply dark mode for authenticated users
    const user = page.props?.auth?.user
    if (!user || !user.id) {
      // For unauthenticated pages (Login, Register), ensure light mode
      isDark.value = false
      applyTheme(false)
      return
    }

    // If server provided a preference for the authenticated user, respect it
    const serverPref = user.dark_mode
    if (typeof serverPref !== 'undefined' && serverPref !== null) {
      isDark.value = !!serverPref
      applyTheme(isDark.value)
      return
    }

    const stored = localStorage.getItem('theme')
    if (stored) {
      isDark.value = stored === 'dark'
      applyTheme(isDark.value)
      return
    }

    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      isDark.value = true
      applyTheme(true)
    }
  } catch (e) {
    // ignore localStorage or matchMedia errors
  }
})

async function toggle() {
  isDark.value = !isDark.value
  applyTheme(isDark.value)

  // If user is authenticated, persist preference server-side
  try {
    const user = page.props?.auth?.user
    if (user && user.id) {
      const tokenMeta = document.querySelector('meta[name="csrf-token"]')
      const csrf = tokenMeta ? tokenMeta.getAttribute('content') : null
      await fetch('/profile/theme', {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf || '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ dark_mode: isDark.value }),
      })
    }
  } catch (e) {
    // ignore network errors; localStorage still keeps preference
  }
}
</script>
