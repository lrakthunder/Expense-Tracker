<template>
  <transition name="toast-fade">
    <div v-if="visible" data-cy="flash-toast" :class="['flash-toast', variant]" role="status" aria-live="polite">
      <div class="flash-content">{{ message }}</div>
      <button class="flash-close" @click="hide">✕</button>
    </div>
  </transition>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const visible = ref(false)
const message = ref('')
const variant = ref('success')
let timeoutId = null
const FLASH_KEY = 'thunder:flash'
let lastFlashId = null // prevent duplicate toasts

function show(msg, type = 'success') {
  // Create unique ID for this flash to avoid duplicate toasts
  const flashId = `${msg}:${type}:${Date.now()}`
  if (lastFlashId === flashId) return // Already showing this toast
  lastFlashId = flashId
  
  message.value = msg
  variant.value = type
  visible.value = true
  if (timeoutId) clearTimeout(timeoutId)
  timeoutId = setTimeout(() => { visible.value = false }, 4000)
}

function hide() {
  visible.value = false
  if (timeoutId) clearTimeout(timeoutId)
}

function saveFlashToSession(flash) {
  try {
    const payload = { flash, ts: Date.now() }
    sessionStorage.setItem(FLASH_KEY, JSON.stringify(payload))
  } catch (e) {}
}

function readFlashFromSession(maxAgeMs = 10000) {
  try {
    const raw = sessionStorage.getItem(FLASH_KEY)
    if (!raw) return null
    const obj = JSON.parse(raw)
    if (!obj || !obj.flash) return null
    if (Date.now() - (obj.ts || 0) > maxAgeMs) {
      sessionStorage.removeItem(FLASH_KEY)
      return null
    }
    return obj.flash
  } catch (e) { return null }
}

function clearSessionFlash() {
  try { sessionStorage.removeItem(FLASH_KEY) } catch (e) {}
}

function getFlashFromPage() {
  return page?.props?.value?.flash ?? page?.props?.flash
}

// watch flash props from Inertia and persist to session so it survives reload/navigation
const handleFlash = (flash) => {
  if (!flash) return
  saveFlashToSession(flash)
  if (flash.success) show(flash.success, 'success')
  if (flash.error) show(flash.error, 'error')
}

watch(() => getFlashFromPage(), (flash) => {
  handleFlash(flash)
}, { immediate: true })

// also watch the global Inertia page props (covers redirects/navigation)
// Note: avoid importing `@inertiajs/inertia` directly (may not be installed).
// Instead, listen for navigation finish events and read the global Inertia page
// object from `window.page` or `window.__inertia`.

onMounted(() => {
  // prefer server-sent flash, otherwise fallback to a recent session-stored flash
  const flash = getFlashFromPage() ?? readFlashFromSession()
  if (flash?.success) show(flash.success, 'success')
  if (flash?.error) show(flash.error, 'error')
  // once shown, clear the persisted flash so it doesn't reappear repeatedly
  clearSessionFlash()
  // listen for Inertia navigation finish events to pick up server-side flash after redirects
  const onFinish = (e) => {
    try {
      const flash = window.page?.props?.flash ?? window.__inertia?.page?.props?.flash
      handleFlash(flash)
    } catch (err) {}
  }
  window.addEventListener('inertia:finish', onFinish)
  
  // listen for custom flash-message events from components
  const onFlashMessage = (e) => {
    const { message, type } = e.detail
    show(message, type)
  }
  window.addEventListener('flash-message', onFlashMessage)
  
  // cleanup
  onBeforeUnmount(() => {
    window.removeEventListener('inertia:finish', onFinish)
    window.removeEventListener('flash-message', onFlashMessage)
  })
})
</script>

<style scoped>
.flash-toast { position: fixed; right: 24px; top: 80px; z-index: 9999; min-width: 260px; padding: 12px 14px; border-radius: 8px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 8px 24px rgba(0,0,0,0.2);} 
.flash-toast.success { background: #10b981; color: white; }
.flash-toast.error { background: #ef4444; color: white; }
.flash-content { flex: 1; margin-right: 8px; font-weight:600 }
.flash-close { background:transparent; border:none; color:inherit; font-weight:700; cursor:pointer }
.toast-fade-enter-active, .toast-fade-leave-active { transition: opacity 0.25s ease, transform 0.25s ease }
.toast-fade-enter-from { opacity: 0; transform: translateY(-6px) }
.toast-fade-enter-to { opacity: 1; transform: translateY(0) }
.toast-fade-leave-from { opacity:1; transform: translateY(0) }
.toast-fade-leave-to { opacity:0; transform: translateY(-6px) }

.dark .flash-toast.success { background: #059669 }
.dark .flash-toast.error { background: #dc2626 }
</style>
