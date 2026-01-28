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
            <span class="sidebar-icon">📄</span>
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
        <h1>Report</h1>

        <div class="table-wrap" style="padding:18px;">
          <div style="max-width:100%; margin:0 auto; display:flex; flex-direction:column; gap:14px;">
            <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
              <div style="flex:1 1 100%; min-width:320px;">
                <label>Report name</label>
                <input data-cy="report-name" v-model="reportName" type="text" placeholder="Monthly Expense Report" />
              </div>
              <div style="flex:1; min-width:220px;">
                <label>Start date</label>
                <input data-cy="report-start" v-model="startDate" type="date" />
              </div>
              <div style="flex:1; min-width:220px;">
                <label>End date</label>
                <input data-cy="report-end" v-model="endDate" type="date" />
              </div>
              <div class="no-print" style="display:flex; gap:10px; justify-content:flex-end; flex:1 1 100%;">
                <button class="btn-ghost" type="button" :disabled="isLoading" @click.prevent="generateReport(false)">Preview</button>
                <button data-cy="report-generate" class="btn-primary" type="button" :disabled="isLoading" @click.prevent="generateReport(true)">
                  {{ isLoading ? 'Generating...' : 'Create PDF' }}
                </button>
              </div>
            </div>

            <p v-if="errorMsg" class="error-text">{{ errorMsg }}</p>

            <div v-if="reportData" id="report-print" class="report-card">
              <!-- Header -->
              <div class="report-header">
                <div>
                  <div class="report-title">{{ reportData.reportTitle }}</div>
                  <div class="report-meta">{{ formatRange(reportData.dateRange) }}</div>
                  <div class="report-meta">Generated: {{ formatDateTime(reportData.generatedAt) }}</div>
                  <div class="report-meta" v-if="reportData.user?.fullname">User: {{ reportData.user.fullname }}</div>
                </div>
              </div>

              <!-- Summary -->
              <div class="summary-grid">
                <div class="summary-card">
                  <div class="label">Total Expenses</div>
                  <div class="value negative">{{ formatCurrency(reportData.summary.totalExpenses) }}</div>
                </div>
                <div class="summary-card">
                  <div class="label">Total Income</div>
                  <div class="value positive">{{ formatCurrency(reportData.summary.totalIncome) }}</div>
                </div>
                <div class="summary-card">
                  <div class="label">Net Balance</div>
                  <div class="value" :class="reportData.summary.netBalance >= 0 ? 'positive' : 'negative'">
                    {{ formatCurrency(reportData.summary.netBalance) }}
                  </div>
                </div>
                <div class="summary-card">
                  <div class="label">Avg Daily Expense</div>
                  <div class="value">{{ formatCurrency(reportData.summary.avgDailyExpense) }}</div>
                </div>
                <div class="summary-card" v-if="reportData.summary.topCategory">
                  <div class="label">Top Category</div>
                  <div class="value">{{ reportData.summary.topCategory.name }}</div>
                  <div class="sub">{{ reportData.summary.topCategory.percent }}% of spending</div>
                </div>
              </div>

              <!-- Charts -->
              <div class="chart-grid">
                <div class="chart-card">
                  <div class="chart-title">Category Breakdown</div>
                  <div v-if="reportData.categoryBreakdown.labels.length" class="bars">
                    <div v-for="(label, idx) in reportData.categoryBreakdown.labels" :key="label" class="bar-row">
                      <div class="bar-label">{{ label }}</div>
                      <div class="bar-meter">
                        <div class="bar-fill" :style="{ width: categoryPercent(idx) + '%' }"></div>
                      </div>
                      <div class="bar-value">{{ formatCurrency(reportData.categoryBreakdown.values[idx]) }}</div>
                    </div>
                  </div>
                  <div v-else class="muted">No expenses in range.</div>
                </div>

                <div class="chart-card">
                  <div class="chart-title">Daily Trend</div>
                  <div v-if="reportData.dailyTrend.length" class="bars">
                    <div v-for="day in paginatedDailyTrend" :key="day.date" class="bar-row">
                      <div class="bar-label">{{ day.date }}</div>
                      <div class="bar-meter dual">
                        <div class="bar-fill expense" :style="{ width: trendPercent(day.expenses) + '%' }"></div>
                        <div class="bar-fill income" :style="{ width: trendPercent(day.income) + '%' }"></div>
                      </div>
                      <div class="bar-value">-{{ formatCurrency(day.expenses) }} / +{{ formatCurrency(day.income) }}</div>
                    </div>
                  </div>
                  <div v-else class="muted">No activity in range.</div>
                  <!-- Daily Trend Pagination Controls -->
                  <div v-if="reportData.dailyTrend.length > dailyTrendPerPage" class="no-print daily-trend-pagination" style="margin-top: 12px; display: flex; gap: 8px; align-items: center; justify-content: space-between; font-size: 13px;">
                    <div style="display: flex; gap: 8px; align-items: center;">
                      <label style="color: #9ca3af;">Show</label>
                      <div style="position: relative; display: inline-block;">
                        <select v-model.number="dailyTrendPerPage" style="padding: 4px 24px 4px 6px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.04); color: inherit; cursor: pointer; appearance: none; -webkit-appearance: none; -moz-appearance: none; padding-right: 24px;">
                          <option :value="5">5</option>
                          <option :value="10">10</option>
                          <option :value="20">20</option>
                          <option :value="365">365</option>
                        </select>
                        <span style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #9ca3af;">▼</span>
                      </div>
                      <label style="color: #9ca3af;">rows</label>
                      <span v-if="dailyTrendPaginationText" style="color: #9ca3af; margin-left: 8px;">{{ dailyTrendPaginationText }}</span>
                    </div>
                    <div style="display: flex; gap: 6px; align-items: center;">
                      <button v-if="dailyTrendCurrentPage > 1" @click="dailyTrendCurrentPage--" style="padding: 4px 8px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; color: inherit; cursor: pointer; font-size: 12px;">← Prev</button>
                      <span style="color: #9ca3af; min-width: 80px; text-align: center;">Page {{ dailyTrendCurrentPage }} of {{ dailyTrendTotalPages }}</span>
                      <button v-if="dailyTrendCurrentPage < dailyTrendTotalPages" @click="dailyTrendCurrentPage++" style="padding: 4px 8px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; color: inherit; cursor: pointer; font-size: 12px;">Next →</button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Transactions -->
              <div class="table-section">
                <div class="section-title">Transactions</div>
                <table class="report-table">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Description</th>
                      <th>Category</th>
                      <th class="align-right">Amount</th>
                      <th class="align-right">Balance</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="!reportData.transactions.length">
                      <td colspan="5">No transactions for this period.</td>
                    </tr>
                    <tr v-for="(tx, idx) in reportData.transactions" :key="idx">
                      <td>{{ tx.date }}</td>
                      <td>{{ tx.description }}</td>
                      <td>{{ tx.category }}</td>
                      <td class="align-right" :class="tx.amount < 0 ? 'negative' : 'positive'">{{ formatCurrency(tx.amount) }}</td>
                      <td class="align-right">{{ formatCurrency(tx.running_balance) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Insights -->
              <div class="insights" v-if="reportData.insights?.length">
                <div class="section-title">Insights</div>
                <ul>
                  <li v-for="(insight, i) in reportData.insights" :key="i">{{ insight }}</li>
                </ul>
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
import '../../css/report.css'
import SiteLogo from '../Components/SiteLogo.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import ThemeToggle from '../Components/ThemeToggle.vue'
import UserDropdown from '../Components/UserDropdown.vue'
import FlashToast from '../Components/FlashToast.vue'
import axios from 'axios'

const page = usePage()

const reportName = ref('')
const startDate = ref('')
const endDate = ref('')
const reportData = ref(null)
const isLoading = ref(false)
const errorMsg = ref('')
const dailyTrendPerPage = ref(10)
const dailyTrendCurrentPage = ref(1)

async function generateReport(shouldPrint = false) {
  try {
    errorMsg.value = ''
    isLoading.value = true
    // default range: last 30 days if empty
    const today = new Date()
    if (!endDate.value) endDate.value = today.toISOString().slice(0, 10)
    if (!startDate.value) {
      const d = new Date()
      d.setDate(d.getDate() - 29)
      startDate.value = d.toISOString().slice(0, 10)
    }

    // Validate date range on frontend (max 1 year / 365 days)
    const start = new Date(startDate.value)
    const end = new Date(endDate.value)
    const daysDiff = Math.floor((end - start) / (1000 * 60 * 60 * 24))
    if (daysDiff > 365) {
      errorMsg.value = 'Date range cannot exceed 1 year (365 days). Please select a shorter period.'
      isLoading.value = false
      return
    }

    const payload = {
      report_name: reportName.value || 'Expense Report',
      start_date: startDate.value,
      end_date: endDate.value,
    }
    const resp = await axios.post(route('report.generate'), payload)
    reportData.value = resp.data
    if (shouldPrint) {
      setTimeout(() => window.print(), 200)
    }
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Unable to generate report.'
  } finally {
    isLoading.value = false
  }
}

function formatCurrency(v) {
  const num = Number(v || 0)
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(num)
}
function formatRange(range) {
  return `${range.start} to ${range.end}`
}
function formatDateTime(v) {
  return new Date(v).toLocaleString()
}
function categoryPercent(idx) {
  if (!reportData.value?.categoryBreakdown?.values?.length) return 0
  const values = reportData.value.categoryBreakdown.values
  const max = Math.max(...values, 1)
  return Math.round((values[idx] / max) * 100)
}
function trendPercent(value) {
  if (!reportData.value?.dailyTrend?.length) return 0
  const max = Math.max(...reportData.value.dailyTrend.map(d => Math.max(d.expenses, d.income)), 1)
  return Math.round((value / max) * 100)
}

const paginatedDailyTrend = computed(() => {
  if (!reportData.value?.dailyTrend?.length) return []
  const start = (dailyTrendCurrentPage.value - 1) * dailyTrendPerPage.value
  const end = start + dailyTrendPerPage.value
  return reportData.value.dailyTrend.slice(start, end)
})

const dailyTrendTotalPages = computed(() => {
  if (!reportData.value?.dailyTrend?.length) return 1
  return Math.ceil(reportData.value.dailyTrend.length / dailyTrendPerPage.value)
})

const dailyTrendPaginationText = computed(() => {
  if (!reportData.value?.dailyTrend?.length || dailyTrendPerPage.value === 365) return ''
  const total = reportData.value.dailyTrend.length
  const from = (dailyTrendCurrentPage.value - 1) * dailyTrendPerPage.value + 1
  const to = Math.min(dailyTrendCurrentPage.value * dailyTrendPerPage.value, total)
  return `Showing ${from} to ${to} of ${total} rows`
})

function logout() {
  // Clear theme preference from localStorage so login page defaults to light mode
  try { localStorage.removeItem('theme') } catch (e) {}
  router.post(route('logout'))
}

const currentUrl = computed(() => typeof window !== 'undefined' ? window.location.pathname : '')
function isActive(path) { return currentUrl.value === path }
</script>