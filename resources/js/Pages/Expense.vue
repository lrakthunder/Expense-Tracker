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
        <h1>Expenses</h1>
        <div class="actions-row" style="margin-bottom:12px; display:flex; gap:8px; align-items:center">
          <button class="btn-secondary" @click="showExpenseModal = true">Add Expense</button>
          <button v-if="selected.length" class="btn-danger" @click="confirmDeleteSelected">Delete Selected ({{ selected.length }})</button>
        </div>

        <div class="table-wrap">
          <table class="expense-table">
            <thead>
              <tr>
                <th style="width:32px"><input type="checkbox" @change="toggleSelectAll($event)" :checked="allSelected" /></th>
                <th style="cursor:pointer" @click.prevent="changeSort('created_at')">Created At <span v-if="sortBy=== 'created_at'">{{ sortDir==='asc'? '▲':'▼' }}</span></th>
                <th style="cursor:pointer" @click.prevent="changeSort('transaction_date')">Transaction Date <span v-if="sortBy=== 'transaction_date'">{{ sortDir==='asc'? '▲':'▼' }}</span></th>
                <th style="cursor:pointer" @click.prevent="changeSort('name')">Name <span v-if="sortBy=== 'name'">{{ sortDir==='asc'? '▲':'▼' }}</span></th>
                <th style="cursor:pointer" @click.prevent="changeSort('category')">Category <span v-if="sortBy=== 'category'">{{ sortDir==='asc'? '▲':'▼' }}</span></th>
                <th class="align-right" style="cursor:pointer" @click.prevent="changeSort('amount')">Expense <span v-if="sortBy=== 'amount'">{{ sortDir==='asc'? '▲':'▼' }}</span></th>
                <th class="align-right">Balance</th>
                <th class="align-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!expenses || expenses.length === 0">
                <td colspan="8">No expenses yet.</td>
              </tr>
              <tr v-for="(e, idx) in expenses" :key="e.id">
                <td><input type="checkbox" :value="e.id" v-model="selected" /></td>
                <td>{{ formatDate(e.created_at) }}</td>
                <td>{{ formatDate(e.transaction_date) }}</td>
                <td>{{ e.name || '-' }}</td>
                <td>{{ formatCategory(e.category) }}</td>
                <td class="align-right">{{ formatMoney(e.amount) }}</td>
                  <td class="align-right">{{ formatMoney(typeof e.remaining !== 'undefined' ? e.remaining : runningBalance(idx)) }}</td>
                  <td class="actions-cell">
                    <div class="actions-inner">
                      <button class="btn-ghost small" @click.prevent="editExpense(e)">Edit</button>
                      <button class="btn-danger small" @click.prevent="deleteExpense(e.id)">Delete</button>
                    </div>
                  </td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="totals-row">
                 <td colspan="5" class="totals-label">Starting Balance</td>
                 <td></td>
                <td class="align-right">{{ formatMoney(overallIncome) }}</td>
                <td></td>
              </tr>
              <tr class="totals-row">
                 <td colspan="5" class="totals-label">Total</td>
                <td class="align-right">{{ formatMoney(totalExpenses) }}</td>
                <td class="align-right">{{ formatMoney(overallBalance) }}</td>
                <td></td>
              </tr>
                        </tfoot>
          </table>
        </div>

        <div class="pagination-controls" style="margin-top:12px; display:flex; justify-content:space-between; align-items:center">
          <div style="display:flex; align-items:center; gap:8px;">
            <label style="font-size:13px; color:var(--muted);">Show</label>
            <select class="pagination-select" v-model.number="perPage" @change="changePerPage(perPage)">
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="20">20</option>
            </select>
            <span style="font-size:13px; color:var(--muted);">rows</span>
            <span v-if="pagination" style="font-size:13px; color:var(--muted); margin-left:12px;">
              Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} entries
            </span>
          </div>

          <div style="display:flex; align-items:center; gap:12px;">
            <button
              v-if="pagination && pagination.current_page > 1"
              @click.prevent="goToPage(pagination.current_page - 1)"
              class="pagination-btn"
            >
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M14.7 7.3a1 1 0 0 1 0 1.4L11.4 12l3.3 3.3a1 1 0 1 1-1.4 1.4l-4-4a1 1 0 0 1 0-1.4l4-4a1 1 0 0 1 1.4 0Z" />
              </svg>
              Prev
            </button>
            <span v-if="pagination"> Page {{ pagination.current_page }} of {{ pagination.last_page }} </span>
            <button
              v-if="pagination && pagination.current_page < pagination.last_page"
              @click.prevent="goToPage(pagination.current_page + 1)"
              class="pagination-btn"
            >
              Next
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M9.3 16.7a1 1 0 0 1 0-1.4L12.6 12 9.3 8.7a1 1 0 0 1 1.4-1.4l4 4a1 1 0 0 1 0 1.4l-4 4a1 1 0 0 1-1.4 0Z" />
              </svg>
            </button>
          </div>
        </div>

        <div v-if="showExpenseModal" class="modal-backdrop" @click.self="closeExpenseModal">
          <div class="modal">
            <h3>{{ expenseForm._id ? 'Edit Expense' : 'Add Expense' }}</h3>
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
              <textarea data-cy="expense-note" v-model="expenseForm.note" placeholder="Optional note" maxlength="50" @input="filterNoteInput"></textarea>

              <div class="modal-actions">
                <button type="button" class="btn-ghost" @click="closeExpenseModal">Cancel</button>
                <button type="submit" class="btn-primary">{{ expenseForm._id ? 'Save Changes' : 'Save Expense' }}</button>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import '../../css/expense.css'
import '../../css/expense-table.css'
import SiteLogo from '../Components/SiteLogo.vue'
import { router, usePage, Link, useForm } from '@inertiajs/vue3'
import ThemeToggle from '../Components/ThemeToggle.vue'
import UserDropdown from '../Components/UserDropdown.vue'
import FlashToast from '../Components/FlashToast.vue'
import { computed, ref, watch, onMounted } from 'vue'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

const page = usePage()

// expenses provided by controller.list (server returns newest->oldest)
const expenses = computed(() => page.props.expenses || [])
const initialSortBy = computed(() => page.props.sort_by || 'created_at')
const initialSortDir = computed(() => page.props.sort_dir || 'desc')
const sortBy = ref(initialSortBy.value)
const sortDir = ref(initialSortDir.value)
const totalExpenses = computed(() => page.props.totalExpenses || 0)
const totalIncome = computed(() => page.props.totalIncome || 0)
const overallIncome = computed(() => page.props.overallIncome || 0)
const overallBalance = computed(() => page.props.overallBalance || 0)

// Pagination props provided by controller (may be null)
const pagination = computed(() => page.props.pagination || null)

const currentPage = computed(() => pagination.value ? pagination.value.current_page : 1)
const perPageFromProps = computed(() => pagination.value ? pagination.value.per_page : 5)

function goToPage(n) {
  const path = typeof window !== 'undefined' ? window.location.pathname : '/expense'
  router.get(path, { page: n, per_page: perPage.value, sort_by: sortBy.value, sort_dir: sortDir.value }, { preserveState: true, replace: true })
}

const perPage = ref(perPageFromProps.value)
function changePerPage(n) {
  const path = typeof window !== 'undefined' ? window.location.pathname : '/expense'
  perPage.value = n
  router.get(path, { per_page: n, page: 1, sort_by: sortBy.value, sort_dir: sortDir.value }, { preserveState: true, replace: true })
}

function changeSort(column) {
  // toggle direction if same column
  if (sortBy.value === column) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = column
    sortDir.value = 'desc'
  }
  const path = typeof window !== 'undefined' ? window.location.pathname : '/expense'
  router.get(path, { per_page: perPage.value, page: 1, sort_by: sortBy.value, sort_dir: sortDir.value }, { preserveState: true, replace: true })
}

// UI for add-expense modal (reusing dashboard modal behavior)
const showExpenseModal = ref(false)
const expenseCategories = computed(() => {
  const fromProps = page.props.expenseCategories
  return (fromProps && Array.isArray(fromProps) && fromProps.length > 0) ? fromProps : ['Food', 'Transport', 'Utilities', 'Other']
})
const expenseCount = ref(1)
const expenseForm = useForm({ name: '', amount: null, category: '', date: '', note: '' })

// selection state for deletion checkboxes
const selected = ref([])
const allSelected = computed(() => expenses.value.length > 0 && selected.value.length === expenses.value.length)

function closeExpenseModal() {
  showExpenseModal.value = false
  expenseForm.reset()
  expenseForm.category = expenseCategories.value[0] || 'Other'
}

function submitExpense() {
  if (!expenseForm.name) {
    expenseForm.name = `expense${expenseCount.value}`
  }

  if (expenseForm._id) {
    // updating existing expense
    router.patch(route('expense.update', expenseForm._id), expenseForm, {
      onSuccess: () => {
        delete expenseForm._id
        closeExpenseModal()
        window.location.reload()
      }
    })
  } else {
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
}

function filterNoteInput(event) {
    const allowedChars = /[^a-zA-Z0-9\s\-,@%.()]/g
    expenseForm.note = event.target.value.replace(allowedChars, '')
}

function toggleSelectAll(e) {
  if (e.target.checked) {
    selected.value = expenses.value.map(x => x.id)
  } else {
    selected.value = []
  }
}

async function confirmDeleteSelected() {
  if (!selected.value.length) return

  const dark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
  const result = await Swal.fire({
    title: `Delete ${selected.value.length} selected expense(s)?`,
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete',
    cancelButtonText: 'Cancel',
    customClass: { popup: dark ? 'swal2-dark' : '' }
  })

  if (!result.isConfirmed) return

  // Attempt to post to a bulk-delete route
  router.post(route('expense.bulkDelete'), { ids: selected.value }, {
    onSuccess: () => {
      selected.value = []
      Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false, customClass: { popup: dark ? 'swal2-dark' : '' } })
    }
  })
}

function editExpense(e) {
  // populate form and open modal for editing
  expenseForm.name = e.name || ''
  expenseForm.amount = e.amount || null
  expenseForm.category = e.category || expenseCategories[0]
  expenseForm.date = e.transaction_date || ''
  expenseForm.note = e.note || ''
  // store id on the form for update if necessary
  expenseForm._id = e.id
  showExpenseModal.value = true
}

async function deleteExpense(id) {
  const dark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
  const result = await Swal.fire({
    title: 'Delete this expense?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete',
    cancelButtonText: 'Cancel',
    customClass: { popup: dark ? 'swal2-dark' : '' }
  })
  if (!result.isConfirmed) return

  router.post(route('expense.bulkDelete'), { ids: [id] }, {
    onSuccess: () => {
      selected.value = selected.value.filter(x => x !== id)
      Swal.fire({ icon: 'success', title: 'Deleted', timer: 900, showConfirmButton: false, customClass: { popup: dark ? 'swal2-dark' : '' } })
    }
  })
}

// Compute running balances per expense using chronological order (oldest first).
// This ensures each expense reduces the remaining balance correctly regardless
// of how the UI orders the rows (e.g. newest-first display).
function runningBalance(idx) {
  // Fallback running balance when server doesn't provide `remaining`.
  // Use the server-returned `expenses` list (newest-first) so index matches rendered rows.
  const income = (totalIncome && totalIncome.value) ? Number(totalIncome.value) : 0
  const list = Array.isArray(expenses.value) ? expenses.value : []
  if (!list.length) return income
  let futureSum = 0
  for (let i = idx; i < list.length; i++) {
    futureSum += Number(list[i]?.amount || 0)
  }
  const balance = income - futureSum
  return Number.isFinite(Number(balance)) ? Number(balance) : 0
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

function formatDate(val) {
  if (!val) return '-'
  try {
    const d = new Date(val)
    if (isNaN(d.getTime())) return val
    return d.toLocaleDateString()
  } catch (e) {
    return val
  }
}

function formatMoney(n) {
  if (n == null) return '-'
  return Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatCategory(cat) {
  if (!cat) return '-'
  try {
    let s = ''
    if (typeof cat === 'string') {
      // try to parse JSON strings like "[\"Other\"]"
      try {
        const parsed = JSON.parse(cat)
        if (Array.isArray(parsed)) s = parsed.join(', ')
        else s = String(parsed)
      } catch (e) {
        s = cat
      }
    } else if (Array.isArray(cat)) {
      s = cat.join(', ')
    } else {
      s = String(cat)
    }

    // Remove any character except letters, numbers, spaces, commas and dashes
    s = String(s).replace(/[^A-Za-z0-9\-, ]+/g, '')
    // normalize whitespace and commas
    s = s.replace(/\s+/g, ' ').trim()
    s = s.replace(/\s*,\s*/g, ', ')
    if (!s) return '-'
    return s
  } catch (e) { return String(cat).replace(/[^A-Za-z0-9\-, ]+/g, '').trim() || '-' }
}
</script>

<style scoped>
.pagination-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 12px 18px;
  background: #2b5d9e;
  border: 2px solid #1e4578;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 700;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(43, 93, 158, 0.35);
  transition: all 0.2s ease;
}

.pagination-btn svg {
  width: 16px;
  height: 16px;
  fill: currentColor;
  stroke: currentColor;
}

.pagination-btn:hover {
  background: #3a6fb8;
  border-color: #2855a0;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(43, 93, 158, 0.45);
}

.pagination-btn:active {
  transform: translateY(0);
}

:deep(.dark) .pagination-btn {
  background: #3b82f6;
  border-color: #2563eb;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

:deep(.dark) .pagination-btn:hover {
  background: #60a5fa;
  border-color: #3b82f6;
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.5);
}
</style>


