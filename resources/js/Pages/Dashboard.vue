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
                <div class="groups-container">
                    <section class="group-top">
                        <section class="stat-cards">
                    <div class="stat-card">
                        <div class="stat-label">Today's Spending</div>
                        <div class="stat-value">{{ stats.today.toFixed(2) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Spent this Month</div>
                        <div class="stat-value">{{ stats.thisMonth.toFixed(2) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Remaining Budget</div>
                        <div class="stat-value">{{ stats.remainingBudget.toFixed(2) }}</div>
                    </div>
                    <!-- <div class="stat-card">
                        <div class="stat-label">Compared to Last Month</div>
                        <div class="stat-value">{{ stats.comparedToLastMonth.toFixed(2) }}</div>
                    </div> -->
                        </section>
                        <div class="actions-row">
                    <button class="btn-primary" @click="showIncomeModal = true">Add Income</button>
                    <button class="btn-secondary" @click="showExpenseModal = true">Add Expense</button>
                        </div>
                    </section>

                    <!-- Placeholder group below: ready to be split into two columns -->
                    <section class="group-below">
                        <div class="two-column-placeholder">
                            <!-- left column -->
                                <div class="col-left">
                                    <PieChart :labels="expenseByCategoryLabels" :values="expenseByCategoryValues" title="Expenses by Category" />
                                </div>
                            <!-- right column -->
                                <div class="col-right">
                                    <BarChart :labels="monthlyLabels" :values="monthlyValues" title="Monthly Expenses" />
                                </div>
                        </div>
                    </section>
                </div>

                <!-- Modals -->
                <div v-if="showIncomeModal" class="modal-backdrop" @click.self="closeIncomeModal">
                    <div class="modal">
                        <h3>Add Income</h3>
                        <form @submit.prevent="submitIncome">
                            <label>Name (optional)</label>
                            <input data-cy="income-name" v-model="incomeForm.name" type="text" placeholder="Income name" />

                            <label>Amount</label>
                            <input data-cy="income-amount" v-model.number="incomeForm.amount" type="number" step="0.01" required />

                            <label>Category</label>
                            <select data-cy="income-category" v-model="incomeForm.category">
                                <option v-for="c in incomeCategories" :key="c" :value="c">{{ c }}</option>
                            </select>

                            <label>Transaction date</label>
                            <input data-cy="income-date" v-model="incomeForm.date" type="date" required />

                            <label>Note</label>
                            <textarea data-cy="income-note" v-model="incomeForm.note" placeholder="Optional note" maxlength="50" @input="filterNoteInput($event, 'income')"></textarea>

                            <div class="modal-actions">
                                <button type="button" class="btn-ghost" @click="closeIncomeModal">Cancel</button>
                                <button type="submit" class="btn-primary">Save Income</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div v-if="showExpenseModal" class="modal-backdrop" @click.self="closeExpenseModal">
                    <div class="modal">
                        <h3>Add Expense</h3>
                        <form @submit.prevent="submitExpense">
                            <label>Name (optional)</label>
                            <input data-cy="expense-name" v-model="expenseForm.name" type="text" placeholder="Expense name" />

                            <label>Amount</label>
                            <input data-cy="expense-amount" v-model.number="expenseForm.amount" type="number" step="0.01" required />

                            <label>Category</label>
                            <select data-cy="expense-category" v-model="expenseForm.category">
                                <option v-for="c in expenseCategories" :key="c" :value="c">{{ c }}</option>
                            </select>

                            <label>Transaction date</label>
                            <input data-cy="expense-date" v-model="expenseForm.date" type="date" required />

                            <label>Note</label>
                            <textarea data-cy="expense-note" v-model="expenseForm.note" placeholder="Optional note" maxlength="50" @input="filterNoteInput($event, 'expense')"></textarea>

                            <div class="modal-actions">
                                <button type="button" class="btn-ghost" @click="closeExpenseModal">Cancel</button>
                                <button type="submit" class="btn-primary">Save Expense</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Expense content goes here -->
            </main>
        </div>
    </div>
</template>

<script setup>
import '../../css/expense.css'
import SiteLogo from '../Components/SiteLogo.vue'
import UserDropdown from '../Components/UserDropdown.vue'
import { router, usePage, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()
const user = page.props.auth?.user || {}
const stats = page.props.stats || { today: 0, thisMonth: 0, remainingBudget: 0, comparedToLastMonth: 0 }

import PieChart from '../Components/PieChart.vue'
import BarChart from '../Components/BarChart.vue'
import ThemeToggle from '../Components/ThemeToggle.vue'
import FlashToast from '../Components/FlashToast.vue'

// Prepare reactive props for charts. Backend can provide these under page props:
// - page.props.expensesByCategory => object: { categoryName: total, ... }
// - page.props.monthlyExpenses => array: [{ month: '2025-01', total: 123 }, ...]
const expensesByCategoryRaw = computed(() => page.props.expensesByCategory || {})
const expenseByCategoryLabels = computed(() => Object.keys(expensesByCategoryRaw.value || {}))
const expenseByCategoryValues = computed(() => Object.values(expensesByCategoryRaw.value || {}))

const monthlyRaw = computed(() => page.props.monthlyExpenses || [])
const monthlyLabels = computed(() => Array.isArray(monthlyRaw.value) ? monthlyRaw.value.map(m => m.month) : Object.keys(monthlyRaw.value || {}))
const monthlyValues = computed(() => Array.isArray(monthlyRaw.value) ? monthlyRaw.value.map(m => m.total) : Object.values(monthlyRaw.value || {}))

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

// UI state for modals and forms
const showIncomeModal = ref(false)
const showExpenseModal = ref(false)

const incomeCategories = computed(() => {
  const fromProps = page.props.incomeCategories
  return (fromProps && Array.isArray(fromProps) && fromProps.length > 0) ? fromProps : ['Salary', 'Interest', 'Other']
})
const expenseCategories = computed(() => {
  const fromProps = page.props.expenseCategories
  return (fromProps && Array.isArray(fromProps) && fromProps.length > 0) ? fromProps : ['Food', 'Transport', 'Utilities', 'Other']
})

const incomeCount = ref(1)
const expenseCount = ref(1)

const incomeForm = useForm({ name: '', amount: null, category: '', date: '', note: '' })
const expenseForm = useForm({ name: '', amount: null, category: '', date: '', note: '' })

function closeIncomeModal() {
    showIncomeModal.value = false
    incomeForm.reset()
    incomeForm.category = incomeCategories.value[0] || 'Other'
}

function closeExpenseModal() {
    showExpenseModal.value = false
    expenseForm.reset()
    expenseForm.category = expenseCategories.value[0] || 'Other'
}

function submitIncome() {
    // default name if empty
    if (!incomeForm.name) {
        incomeForm.name = `income${incomeCount.value}`
    }

    // post to backend (route may be defined later)
    router.post(route('income.store'), incomeForm, {
        onSuccess: () => {
            incomeCount.value++
            closeIncomeModal()
            window.location.reload()
        },
        onError: () => {
            // keep modal open for correction
        }
    })
}

function submitExpense() {
    if (!expenseForm.name) {
        expenseForm.name = `expense${expenseCount.value}`
    }

    router.post(route('expense.store'), expenseForm, {
        onSuccess: () => {
            expenseCount.value++
            closeExpenseModal()
            window.location.reload()
        },
        onError: () => {
            // keep modal open for correction
        }
    })
}

function filterNoteInput(event, formType) {
    const allowedChars = /[^a-zA-Z0-9\s\-,@%.()]/g
    const filtered = event.target.value.replace(allowedChars, '')
    if (formType === 'income') {
        incomeForm.note = filtered
    } else if (formType === 'expense') {
        expenseForm.note = filtered
    }
}
</script>
