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
        <h1>Income</h1>
        <div class="actions-row" style="margin-bottom:12px; display:flex; gap:8px; align-items:center">
          <button class="btn-primary" @click="showIncomeModal = true">Add Income</button>
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
                <th class="align-right" style="cursor:pointer" @click.prevent="changeSort('amount')">Income <span v-if="sortBy=== 'amount'">{{ sortDir==='asc'? '▲':'▼' }}</span></th>
                <th class="align-right">Balance</th>
                <th class="align-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!incomes || incomes.length === 0">
                <td colspan="8">No income yet.</td>
              </tr>
              <tr v-for="(e, idx) in incomes" :key="e.id">
                <td><input type="checkbox" :value="e.id" v-model="selected" /></td>
                <td>{{ formatDate(e.created_at) }}</td>
                <td>{{ formatDate(e.transaction_date) }}</td>
                <td>{{ e.name || '-' }}</td>
                <td>{{ formatCategory(e.category) }}</td>
                <td class="align-right">{{ formatMoney(e.amount) }}</td>
                  <td class="align-right">{{ formatMoney(typeof e.running_balance !== 'undefined' ? e.running_balance : runningBalance(idx)) }}</td>
                  <td class="actions-cell">
                    <div class="actions-inner">
                      <button class="btn-ghost small" @click.prevent="editIncome(e)">Edit</button>
                      <button class="btn-danger small" @click.prevent="deleteIncome(e.id)">Delete</button>
                    </div>
                  </td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="totals-row">
                 <td colspan="6" class="totals-label">Total Balance</td>
                <td class="align-right">{{ formatMoney(totalIncomes) }}</td>
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

        <div v-if="showIncomeModal" class="modal-backdrop" @click.self="closeIncomeModal">
          <div class="modal">
            <h3>{{ incomeForm._id ? 'Edit Income' : 'Add Income' }}</h3>
            <form @submit.prevent="submitIncome">
              <label>Name (optional)</label>
              <input data-cy="income-name" v-model="incomeForm.name" type="text" placeholder="Income name" />

              <label>Amount</label>
              <input data-cy="income-amount" v-model.number="incomeForm.amount" type="number" step="0.01" required />

              <label>Category</label>
              <select data-cy="income-category" v-model="incomeForm.category" required>
                <option v-for="c in incomeCategories" :key="c" :value="c">{{ c }}</option>
              </select>

              <label>Transaction date</label>
              <input data-cy="income-date" v-model="incomeForm.date" type="date" required />

              <label>Note</label>
              <textarea data-cy="income-note" v-model="incomeForm.note" placeholder="Optional note" maxlength="50" @input="filterNoteInput"></textarea>

              <div class="modal-actions">
                <button type="button" class="btn-ghost" @click="closeIncomeModal">Cancel</button>
                <button type="submit" class="btn-primary">{{ incomeForm._id ? 'Save Changes' : 'Save Income' }}</button>
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
import '../../css/income-table.css'
import SiteLogo from '../Components/SiteLogo.vue'
import { router, usePage, Link, useForm } from '@inertiajs/vue3'
import ThemeToggle from '../Components/ThemeToggle.vue'
import UserDropdown from '../Components/UserDropdown.vue'
import FlashToast from '../Components/FlashToast.vue'
import { computed, ref, watch, onMounted } from 'vue'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

const page = usePage()

// incomes provided by controller.list (server returns newest->oldest)
const incomes = computed(() => page.props.incomes || [])
const initialSortBy = computed(() => page.props.sort_by || 'transaction_date')
const initialSortDir = computed(() => page.props.sort_dir || 'desc')
const sortBy = ref(initialSortBy.value)
const sortDir = ref(initialSortDir.value)
const totalIncomes = computed(() => page.props.totalIncomes || 0)
const totalIncome = computed(() => page.props.totalIncome || 0)
const overallBalance = computed(() => page.props.overallBalance || 0)

// Pagination props provided by controller (may be null)
const pagination = computed(() => page.props.pagination || null)

const currentPage = computed(() => pagination.value ? pagination.value.current_page : 1)
const perPageFromProps = computed(() => pagination.value ? pagination.value.per_page : 5)

function goToPage(n) {
  const path = typeof window !== 'undefined' ? window.location.pathname : '/income'
  router.get(path, { page: n, per_page: perPage.value, sort_by: sortBy.value, sort_dir: sortDir.value }, { preserveState: true, replace: true })
}

const perPage = ref(perPageFromProps.value)
function changePerPage(n) {
  const path = typeof window !== 'undefined' ? window.location.pathname : '/income'
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
  const path = typeof window !== 'undefined' ? window.location.pathname : '/income'
  router.get(path, { per_page: perPage.value, page: 1, sort_by: sortBy.value, sort_dir: sortDir.value }, { preserveState: true, replace: true })
}

// UI for add-income modal (reusing dashboard modal behavior)
const showIncomeModal = ref(false)
const incomeCategories = computed(() => {
  const fromProps = page.props.incomeCategories
  return (fromProps && Array.isArray(fromProps) && fromProps.length > 0) ? fromProps : ['Salary', 'Interest', 'Other']
})
const incomeCount = ref(1)

// Get initial category from incomeCategories
const getInitialCategory = () => {
  const cats = incomeCategories.value
  return (cats && cats.length > 0) ? cats[0] : 'Salary'
}

const incomeForm = useForm({ 
  name: '', 
  amount: null, 
  category: getInitialCategory(), 
  date: '', 
  note: '' 
})

// selection state for deletion checkboxes
const selected = ref([])
const allSelected = computed(() => incomes.value.length > 0 && selected.value.length === incomes.value.length)

function closeIncomeModal() {
  showIncomeModal.value = false
  incomeForm.reset()
  incomeForm.category = incomeCategories.value[0] || 'Other'
}

function submitIncome() {
  if (!incomeForm.name) {
    incomeForm.name = `income${incomeCount.value}`
  }

  if (incomeForm._id) {
    // updating existing income
    router.patch(route('income.update', incomeForm._id), incomeForm, {
      onSuccess: () => {
        delete incomeForm._id
        closeIncomeModal()
        window.location.reload()
      }
    })
  } else {
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
}

function filterNoteInput(event) {
    const allowedChars = /[^a-zA-Z0-9\s\-,@%.()]/g
    incomeForm.note = event.target.value.replace(allowedChars, '')
}

function toggleSelectAll(e) {
  if (e.target.checked) {
    selected.value = incomes.value.map(x => x.id)
  } else {
    selected.value = []
  }
}

async function confirmDeleteSelected() {
  if (!selected.value.length) return

  const dark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
  const result = await Swal.fire({
    title: `Delete ${selected.value.length} selected income(s)?`,
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete',
    cancelButtonText: 'Cancel',
    customClass: { popup: dark ? 'swal2-dark' : '' }
  })

  if (!result.isConfirmed) return

  // Attempt to post to a bulk-delete route
  router.post(route('income.bulkDelete'), { ids: selected.value }, {
    onSuccess: () => {
      selected.value = []
      Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false, customClass: { popup: dark ? 'swal2-dark' : '' } })
    }
  })
}

function editIncome(e) {
  // populate form and open modal for editing
  incomeForm.name = e.name || ''
  incomeForm.amount = e.amount || null
  // Normalize category which may be stored as JSON string/array or plain string
  try {
    let cat = e.category
    console.log('[Income.vue] editIncome called, id:', e?.id, 'raw category:', e?.category)
    if (typeof cat === 'string') {
      try {
        const parsed = JSON.parse(cat)
        if (Array.isArray(parsed)) cat = parsed[0] ?? ''
      } catch (err) {
        // keep plain string
      }
    } else if (Array.isArray(cat)) {
      cat = cat[0] ?? ''
    }
    console.log('[Income.vue] editIncome - normalized category:', cat)
    console.log('[Income.vue] editIncome - incomeForm.category before:', incomeForm.category)
    // Ensure category is a string
    const catStr = cat == null ? '' : String(cat).trim()
    const availableCategories = incomeCategories.value || []
    
    // Set the category to the normalized value or first available category
    incomeForm.category = catStr || (availableCategories.length > 0 ? availableCategories[0] : 'Salary')
    console.log('[Income.vue] editIncome - incomeForm.category after:', incomeForm.category)
  } catch (err) {
    const availableCategories = incomeCategories.value || []
    incomeForm.category = availableCategories.length > 0 ? availableCategories[0] : 'Salary'
  }
  incomeForm.date = e.transaction_date || ''
  incomeForm.note = e.note || ''
  // store id on the form for update if necessary
  incomeForm._id = e.id
  showIncomeModal.value = true
}

async function deleteIncome(id) {
  const dark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
  const result = await Swal.fire({
    title: 'Delete this income?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete',
    cancelButtonText: 'Cancel',
    customClass: { popup: dark ? 'swal2-dark' : '' }
  })
  if (!result.isConfirmed) return

  router.post(route('income.bulkDelete'), { ids: [id] }, {
    onSuccess: () => {
      selected.value = selected.value.filter(x => x !== id)
      Swal.fire({ icon: 'success', title: 'Deleted', timer: 900, showConfirmButton: false, customClass: { popup: dark ? 'swal2-dark' : '' } })
    }
  })
}

// Compute running balances per income using chronological order (oldest first).
// This ensures each income increases the remaining balance correctly regardless
// of how the UI orders the rows (e.g. newest-first display).
function runningBalance(idx) {
  // Fallback running balance when server doesn't provide `remaining`.
  // Use the server-returned `incomes` list (newest-first) so index matches rendered rows.
  const list = Array.isArray(incomes.value) ? incomes.value : []
  if (!list.length) return 0
  let futureSum = 0
  for (let i = idx; i < list.length; i++) {
    futureSum += Number(list[i]?.amount || 0)
  }
  return Number.isFinite(Number(futureSum)) ? Number(futureSum) : 0
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
      // try to parse JSON strings like '["Other"]'
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
