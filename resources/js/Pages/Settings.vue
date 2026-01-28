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

      <main class="expense-main settings-main">
        <FlashToast />

        <h1>Settings</h1>

        <div class="settings-grid">
          <section
            v-for="panel in panels"
            :key="panel.key"
            class="settings-panel"
          >
            <div class="panel-header" @click="toggleCollapse(panel.key)">
              <h2>{{ panel.title }}</h2>
              <div class="panel-actions">
                <button class="icon-btn" :title="`Add ${panel.title}`" @click.stop="openAdd(panel.key)">
                  <svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 5a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H6a1 1 0 1 1 0-2h5V6a1 1 0 0 1 1-1Z" />
                  </svg>
                </button>
                <button
                  class="icon-btn"
                  :title="`Refresh ${panel.title}`"
                  :disabled="loading[panel.key]"
                  @click.stop="refreshCategories(panel.key)"
                >
                  <svg class="icon-16" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 5a7 7 0 1 1-6.32 9.46 1 1 0 0 1 1.88-.68A5 5 0 1 0 12 7h-1a1 1 0 1 1 0-2h2.5a1 1 0 0 1 1 1V8a1 1 0 1 1-2 0Z" />
                  </svg>
                </button>
                <button
                  v-if="selected[panel.key].length"
                  class="icon-btn btn-danger-small"
                  :title="`Delete ${selected[panel.key].length} selected`"
                  @click.stop="confirmDeleteSelected(panel.key)"
                >
                  <svg class="icon-14" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9 4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1h4a1 1 0 1 1 0 2h-1v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6H4a1 1 0 1 1 0-2Zm2 2v13h2V6Zm-4 0v13h2V6Z" />
                  </svg>
                  <span class="badge-count">{{ selected[panel.key].length }}</span>
                </button>
                <button
                  class="icon-btn"
                  :title="collapsed[panel.key] ? 'Expand' : 'Collapse'"
                  @click.stop="toggleCollapse(panel.key)"
                >
                  <svg
                    class="icon-14 chevron"
                    :class="{ rotated: collapsed[panel.key] }"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                  >
                    <path d="M6.7 9.3a1 1 0 0 1 1.4 0L12 13.2l3.9-3.9a1 1 0 1 1 1.4 1.4l-4.6 4.6a1 1 0 0 1-1.4 0L6.7 10.7a1 1 0 0 1 0-1.4Z" />
                  </svg>
                </button>
              </div>
            </div>

            <transition name="fade">
              <div v-show="!collapsed[panel.key]" class="panel-body">
                <div v-if="addMode[panel.key]" class="add-row">
                  <input
                    :data-cy="`${panel.key}-category-input`"
                    v-model="newCategory[panel.key]"
                    :placeholder="editingId[panel.key] ? `Edit ${panel.key} category name` : `New ${panel.key} category name`"
                    type="text"
                  />
                  <div class="add-actions">
                    <button
                      class="btn-primary"
                      :disabled="saving[panel.key] || !newCategory[panel.key].trim()"
                      @click="submitCategory(panel.key)"
                    >
                      {{ saving[panel.key] ? 'Saving...' : (editingId[panel.key] ? 'Update' : 'Save') }}
                    </button>
                    <button class="btn-ghost" @click="cancelAdd(panel.key)">Cancel</button>
                  </div>
                </div>

                <p v-if="errors[panel.key]" class="error-text">{{ errors[panel.key] }}</p>

                <div class="table-wrap settings-table">
                  <table class="expense-table compact">
                    <thead>
                      <tr>
                        <th style="width: 32px"><input type="checkbox" @change="toggleSelectAll(panel.key, $event)" :checked="allSelectedOnPage(panel.key)" /></th>
                        <th style="width: 30%">Name</th>
                        <th style="width: 15%">Type</th>
                        <th style="width: 23%">Created</th>
                        <th style="width: 25%" class="align-right">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="loading[panel.key]">
                        <td colspan="5">Loading categories...</td>
                      </tr>
                      <tr v-else-if="!getList(panel.key).length">
                        <td colspan="5">No categories yet.</td>
                      </tr>
                      <tr v-for="cat in getPaginatedList(panel.key)" :key="cat.id">
                        <td><input type="checkbox" :value="cat.id" v-model="selected[panel.key]" :disabled="isDefaultCategory(cat.name)" /></td>
                        <td class="cat-name">{{ cat.name }}</td>
                        <td>
                          <span class="badge" :class="panel.key">{{ cat.type }}</span>
                        </td>
                        <td>{{ formatDate(cat.created_at) }}</td>
                        <td class="actions-cell">
                          <div class="actions-inner">
                            <button class="btn-ghost small" @click="editCategory(panel.key, cat)">Edit</button>
                            <button 
                              class="btn-danger small" 
                              :disabled="isDefaultCategory(cat.name)"
                              :title="isDefaultCategory(cat.name) ? 'Cannot delete default category' : 'Delete category'"
                              @click="deleteCategory(panel.key, cat.id)"
                            >
                              Delete
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div v-if="getList(panel.key).length > 0" class="pagination-controls" style="margin-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <label style="font-size: 13px; color: var(--muted);">Show</label>
                    <select class="pagination-select" :value="perPage[panel.key]" @change="changePerPage(panel.key, parseInt($event.target.value))">
                      <option :value="5">5</option>
                      <option :value="10">10</option>
                      <option :value="20">20</option>
                    </select>
                    <span style="font-size: 13px; color: var(--muted);">rows</span>
                    <span style="font-size: 13px; color: var(--muted); margin-left: 12px;">
                      {{ getShowingText(panel.key) }}
                    </span>
                  </div>

                  <div style="display: flex; align-items: center; gap: 12px;">
                    <button
                      v-if="currentPage[panel.key] > 1"
                      @click="goToPage(panel.key, currentPage[panel.key] - 1)"
                      class="pagination-btn"
                    >
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14.7 7.3a1 1 0 0 1 0 1.4L11.4 12l3.3 3.3a1 1 0 1 1-1.4 1.4l-4-4a1 1 0 0 1 0-1.4l4-4a1 1 0 0 1 1.4 0Z" />
                      </svg>
                      Prev
                    </button>
                    <span style="font-size: 13px; color: var(--muted);">Page {{ currentPage[panel.key] }} of {{ getTotalPages(panel.key) }}</span>
                    <button
                      v-if="currentPage[panel.key] < getTotalPages(panel.key)"
                      @click="goToPage(panel.key, currentPage[panel.key] + 1)"
                      class="pagination-btn"
                    >
                      Next
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9.3 16.7a1 1 0 0 1 0-1.4L12.6 12 9.3 8.7a1 1 0 0 1 1.4-1.4l4 4a1 1 0 0 1 0 1.4l-4 4a1 1 0 0 1-1.4 0Z" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </transition>
          </section>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import '../../css/expense.css';
import '../../css/expense-table.css';
import '../../css/settings.css';
import axios from 'axios'
import SiteLogo from '../Components/SiteLogo.vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import ThemeToggle from '../Components/ThemeToggle.vue'
import UserDropdown from '../Components/UserDropdown.vue'
import FlashToast from '../Components/FlashToast.vue'
import { computed, reactive, ref } from 'vue'

const page = usePage()

const panels = [
  { key: 'expense', title: 'Expense Category' },
  { key: 'income', title: 'Income Category' },
]

const expenseCategories = ref(page.props.expenseCategories || [])
const incomeCategories = ref(page.props.incomeCategories || [])
const collapsed = reactive({ expense: true, income: true })
const addMode = reactive({ expense: false, income: false })
const editingId = reactive({ expense: null, income: null })
const loading = reactive({ expense: false, income: false })
const saving = reactive({ expense: false, income: false })
const errors = reactive({ expense: '', income: '' })
const newCategory = reactive({ expense: '', income: '' })
const selected = reactive({ expense: [], income: [] })
const currentPage = reactive({ expense: 1, income: 1 })
const perPage = reactive({ expense: 5, income: 5 })

function targetList(type) {
  return type === 'expense' ? expenseCategories : incomeCategories
}

function getList(type) {
  return targetList(type).value || []
}

function setList(type, rows) {
  targetList(type).value = rows
}

function isDefaultCategory(categoryName) {
  const defaultCategories = ['Food', 'Transport', 'Utilities', 'Salary', 'Interest', 'Other']
  return defaultCategories.includes(categoryName)
}

function getPaginatedList(type) {
  const list = getList(type)
  const start = (currentPage[type] - 1) * perPage[type]
  return list.slice(start, start + perPage[type])
}

function getTotalPages(type) {
  return Math.ceil(getList(type).length / perPage[type])
}

function getShowingText(type) {
  const total = getList(type).length
  if (total === 0) return 'Showing 0 to 0 of 0 entries'
  const from = (currentPage[type] - 1) * perPage[type] + 1
  const to = Math.min(currentPage[type] * perPage[type], total)
  return `Showing ${from} to ${to} of ${total} entries`
}

function changePerPage(type, num) {
  perPage[type] = num
  currentPage[type] = 1
  selected[type] = []
}

function goToPage(type, page) {
  const totalPages = getTotalPages(type)
  if (page >= 1 && page <= totalPages) {
    currentPage[type] = page
    selected[type] = []
  }
}

function toggleSelectAll(type, e) {
  if (e.target.checked) {
    selected[type] = getPaginatedList(type)
      .filter(cat => !isDefaultCategory(cat.name))
      .map(cat => cat.id)
  } else {
    selected[type] = []
  }
}

function allSelectedOnPage(type) {
  const paginatedList = getPaginatedList(type)
  const selectableItems = paginatedList.filter(cat => !isDefaultCategory(cat.name))
  return selectableItems.length > 0 && selected[type].length === selectableItems.length
}

async function confirmDeleteSelected(type) {
  if (selected[type].length === 0) return

  const dark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
  const Swal = (await import('sweetalert2')).default

  const result = await Swal.fire({
    title: `Delete ${selected[type].length} categor${selected[type].length > 1 ? 'ies' : 'y'}?`,
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete',
    cancelButtonText: 'Cancel',
    customClass: { popup: dark ? 'swal2-dark' : '' }
  })

  if (!result.isConfirmed) return

  try {
    for (const id of selected[type]) {
      const category = getList(type).find(cat => cat.id === id)
      if (category && !isDefaultCategory(category.name)) {
        await axios.delete(route('categories.destroy', id))
      }
    }
    selected[type] = []
    await refreshCategories(type)
  } catch (e) {
    errors[type] = 'Failed to delete some categories.'
  }
}

function toggleCollapse(type) {
  collapsed[type] = !collapsed[type]
}

function openAdd(type) {
  addMode[type] = true
  editingId[type] = null
  newCategory[type] = ''
  errors[type] = ''
}

function cancelAdd(type) {
  addMode[type] = false
  editingId[type] = null
  newCategory[type] = ''
  errors[type] = ''
}

async function refreshCategories(type) {
  loading[type] = true
  errors[type] = ''
  try {
    const { data } = await axios.get(route('categories.index', { type }))
    setList(type, data?.data || [])
  } catch (e) {
    errors[type] = 'Unable to refresh categories right now.'
  } finally {
    loading[type] = false
  }
}

async function submitCategory(type) {
  const name = (newCategory[type] || '').trim()
  if (!name) {
    errors[type] = 'Please enter a category name.'
    return
  }

  saving[type] = true
  errors[type] = ''

  try {
    if (editingId[type]) {
      // Update existing category
      await axios.patch(route('categories.update', editingId[type]), { name, type })
    } else {
      // Create new category
      await axios.post(route('categories.store'), { name, type })
    }
    newCategory[type] = ''
    editingId[type] = null
    addMode[type] = false
    await refreshCategories(type)
  } catch (e) {
    const message = e?.response?.data?.errors?.name?.[0] || e?.response?.data?.errors?.type?.[0] || 'Could not save category.'
    errors[type] = message
  } finally {
    saving[type] = false
  }
}

function editCategory(type, category) {
  editingId[type] = category.id
  newCategory[type] = category.name
  addMode[type] = true
  errors[type] = ''
  if (collapsed[type]) {
    collapsed[type] = false
  }
}

async function deleteCategory(type, id) {
  const dark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark')
  const Swal = (await import('sweetalert2')).default

  const result = await Swal.fire({
    title: 'Delete category?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete',
    cancelButtonText: 'Cancel',
    customClass: { popup: dark ? 'swal2-dark' : '' }
  })

  if (!result.isConfirmed) return

  try {
    await axios.delete(route('categories.destroy', id))
    await refreshCategories(type)
  } catch (e) {
    const errorMsg = e.response?.data?.message || 'Failed to delete category.'
    errors[type] = errorMsg
  }
}

function formatDate(value) {
  if (!value) return '-'
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: '2-digit',
    year: 'numeric',
  }).format(new Date(value))
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
