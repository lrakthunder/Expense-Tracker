<template>
  <div class="site-header-left">
    <button aria-label="Toggle menu" class="burger" @click="toggleSidebar" type="button">
      <span class="burger-bar"></span>
      <span class="burger-bar"></span>
      <span class="burger-bar"></span>
    </button>
    <Link href="/dashboard" class="logo-link">
      <img src="/custom%20login%20page/logo.png" alt="Company Logo" class="site-logo" />
    </Link>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'

function toggleSidebar() {
  try {
    document.documentElement.classList.toggle('sidebar-open')
  } catch (e) {
    // ignore in non-browser environments
  }
}

function closeSidebar() {
  try {
    document.documentElement.classList.remove('sidebar-open')
  } catch (e) {
    // ignore in non-browser environments
  }
}

function handleOutsideClick(event) {
  // Check if sidebar is open and click is on the overlay (expense-main when sidebar is open)
  if (document.documentElement.classList.contains('sidebar-open')) {
    const sidebar = document.querySelector('.sidebar')
    const burger = document.querySelector('.burger')
    
    // Close if clicked outside sidebar and not on burger button
    if (sidebar && !sidebar.contains(event.target) && burger && !burger.contains(event.target)) {
      closeSidebar()
    }
  }
}

onMounted(() => {
  // Add click listener to document for closing sidebar on outside click
  document.addEventListener('click', handleOutsideClick)
})

onUnmounted(() => {
  // Clean up event listener
  document.removeEventListener('click', handleOutsideClick)
})
</script>

<style scoped>
.site-header-left { display:flex; align-items:center; gap:10px }
.logo-link {
  display: flex;
  align-items: center;
  text-decoration: none;
  cursor: pointer;
}
.site-logo {
  height: 44px;
  max-height: 44px;
  width: auto;
  object-fit: contain;
  margin-left: 0;
}
.dark .site-logo { filter: invert(1) hue-rotate(180deg) contrast(1.05); }

.burger {
  display: none;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  background: transparent;
  padding: 6px;
  cursor: pointer;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
}
.burger:focus { outline: 2px solid rgba(59, 130, 246, 0.5) }
.burger-bar {
  display: block;
  height: 3px;
  width: 20px;
  background: #0f172a;
  border-radius: 2px;
  transition: all 150ms ease;
}
.dark .burger-bar { background: #e6eef8 }

/* show burger on small screens */
@media (max-width: 900px) {
  .burger { display:inline-flex }
}
</style>
