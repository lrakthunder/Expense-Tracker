<template>
  <div class="expense-bg">
      <header class="expense-header">
        <SiteLogo />
      <div style="display: flex; align-items: center; gap: 12px;">
      <span class="client-name">{{ page.props.fullname }}</span>
      <ThemeToggle />
      <UserDropdown />
      </div>
    </header>
    <div class="expense-container">
      <aside class="sidebar">
        <nav class="sidebar-nav">
          <Link href="/dashboard" class="sidebar-link" :class="{ active: isActive('/dashboard') }">
            <span class="sidebar-icon">📊</span>
            <span class="sidebar-label">Dashboard</span>
          </Link>
          <Link href="/expense" class="sidebar-link" :class="{ active: isActive('/expense') }">
            <span class="sidebar-icon">💸</span>
            <span class="sidebar-label">Expenses</span>
          </Link>
          <Link href="/income" class="sidebar-link" :class="{ active: isActive('/income') }">
            <span class="sidebar-icon">💰</span>
            <span class="sidebar-label">Income</span>
          </Link>
          <Link href="/report" class="sidebar-link" :class="{ active: isActive('/report') }">
            <span class="sidebar-icon">📈</span>
            <span class="sidebar-label">Reports</span>
          </Link>
          <Link href="/calendar" class="sidebar-link" :class="{ active: isActive('/calendar') }">
            <span class="sidebar-icon">📅</span>
            <span class="sidebar-label">Calendar</span>
          </Link>
          <Link href="/settings" class="sidebar-link" :class="{ active: isActive('/settings') }">
            <span class="sidebar-icon">⚙️</span>
            <span class="sidebar-label">Settings</span>
          </Link>
        </nav>
      </aside>
      <main class="expense-main">
        <FlashToast />
        <h1>Calendar</h1>
        
        <div class="calendar-container">
          <div class="calendar-controls">
            <button class="btn-nav" @click="prevMonth">← Prev Month</button>
            <h2 class="month-year">{{ monthYear }}</h2>
            <button class="btn-nav" @click="nextMonth">Next Month →</button>
          </div>

          <div class="calendar-grid">
            <div class="day-header">Sun</div>
            <div class="day-header">Mon</div>
            <div class="day-header">Tue</div>
            <div class="day-header">Wed</div>
            <div class="day-header">Thu</div>
            <div class="day-header">Fri</div>
            <div class="day-header">Sat</div>

            <div
              v-for="day in calendarDays"
              :key="day.date"
              class="calendar-day"
              :class="{ 'other-month': !day.isCurrentMonth }"
            >
              <div class="day-number">{{ day.day }}</div>
              <div v-if="day.isCurrentMonth && getTrendForDate(day.date)" class="day-trend">
                <div class="trend-amount" :class="getTrendForDate(day.date).type">
                  {{ formatTrend(getTrendForDate(day.date).amount) }}
                </div>
              </div>
              <div v-else-if="day.isCurrentMonth" class="day-trend">
                <div class="trend-empty">—</div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import '../../css/expense.css'
import '../../css/calendar.css'
import SiteLogo from '../Components/SiteLogo.vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import ThemeToggle from '../Components/ThemeToggle.vue'
import UserDropdown from '../Components/UserDropdown.vue'
import FlashToast from '../Components/FlashToast.vue'
import { computed, ref, onMounted } from 'vue'
import axios from 'axios'

const page = usePage()
const user = page.props.auth?.user || {}
const currentMonth = ref(new Date())
const dailyData = ref({})

onMounted(async () => {
  await loadDailyData()
})

async function loadDailyData() {
  try {
    const year = currentMonth.value.getFullYear()
    const month = String(currentMonth.value.getMonth() + 1).padStart(2, '0')
    const startDate = `${year}-${month}-01`
    
    // Get last day of month
    const lastDay = new Date(year, currentMonth.value.getMonth() + 1, 0).getDate()
    const endDate = `${year}-${month}-${lastDay}`
    
    const response = await axios.post(route('api.daily-trend'), {
      start_date: startDate,
      end_date: endDate,
    })
    
    dailyData.value = response.data || {}
  } catch (error) {
    console.error('Failed to load daily data:', error)
  }
}

const monthYear = computed(() => {
  const options = { month: 'long', year: 'numeric' }
  return currentMonth.value.toLocaleDateString('en-US', options)
})

const calendarDays = computed(() => {
  const year = currentMonth.value.getFullYear()
  const month = currentMonth.value.getMonth()
  
  // First day of month
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  
  // Days from previous month
  const prevMonthDays = firstDay.getDay()
  const prevMonth = month === 0 ? 11 : month - 1
  const prevYear = month === 0 ? year - 1 : year
  const prevMonthLastDay = new Date(prevYear, month, 0).getDate()
  
  const days = []
  
  // Previous month days
  for (let i = prevMonthDays - 1; i >= 0; i--) {
    const day = prevMonthLastDay - i
    const date = `${prevYear}-${String(prevMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
    days.push({ day, date, isCurrentMonth: false })
  }
  
  // Current month days
  for (let day = 1; day <= lastDay.getDate(); day++) {
    const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
    days.push({ day, date, isCurrentMonth: true })
  }
  
  // Next month days
  const nextMonthDays = 42 - days.length
  for (let day = 1; day <= nextMonthDays; day++) {
    const nextMonth = month === 11 ? 0 : month + 1
    const nextYear = month === 11 ? year + 1 : year
    const date = `${nextYear}-${String(nextMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
    days.push({ day, date, isCurrentMonth: false })
  }
  
  return days
})

function getTrendForDate(dateStr) {
  const data = dailyData.value[dateStr]
  if (!data) return null
  
  const net = data.income - data.expenses
  return {
    amount: net,
    type: net >= 0 ? 'positive' : 'negative'
  }
}

function formatTrend(amount) {
  const num = Math.abs(amount)
  return (amount >= 0 ? '+' : '-') + new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num)
}

async function prevMonth() {
  currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1)
  await loadDailyData()
}

async function nextMonth() {
  currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1)
  await loadDailyData()
}

function logout() {
  // Clear theme preference from localStorage so login page defaults to light mode
  try { localStorage.removeItem('theme') } catch (e) {}
  router.post(route('logout'))
}

const currentUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return window.location.pathname
  }
  return ''
})

function isActive(path) {
  return currentUrl.value === path
}
</script>