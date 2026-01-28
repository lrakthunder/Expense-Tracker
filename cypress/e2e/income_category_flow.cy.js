// cypress/e2e/income_category_flow.cy.js

describe('Income Category Flow - Debug Test', () => {
  const testEmail = 'lrakthunder@gmail.com'
  const testPassword = 'Gwapoakodiba_123'
  const testCategoryName = 'Test Income Category'

  beforeEach(() => {
    // Create fresh test user
    cy.request('POST', '/test/create-user').then((resp) => {
      expect(resp.status).to.eq(200)
    })

    // Login
    cy.visit('/login')
    cy.get('input[placeholder="Email"]').type(testEmail)
    cy.get('input[placeholder="Password"]').type(testPassword)
    cy.get('input[type="checkbox"]#privacy').check({ force: true })
    cy.get('button.btn-signin').click()

    // Wait for dashboard
    cy.url({ timeout: 10000 }).should('include', '/dashboard')
    cy.get('.expense-header').should('exist')
  })

  it('should add a custom income category in settings and see it in Add Income modal on Dashboard', () => {
    // Debug: Check what categories exist before adding
    cy.request('GET', '/test/debug/categories').then((resp) => {
      cy.log('Before adding custom category:')
      cy.log('Income categories: ' + JSON.stringify(resp.body.income_categories))
      cy.log('Expense categories: ' + JSON.stringify(resp.body.expense_categories))
    })

    // Navigate to settings
    cy.visit('/settings')
    cy.url().should('include', '/settings')

    // Find and expand Income Category panel - click on the whole header which has the collapse button
    cy.contains('h2', 'Income Category').click()

    // Click the add button (+) - find it within Income Category panel
    cy.contains('h2', 'Income Category').closest('.settings-panel').within(() => {
      cy.get('button').eq(0).click() // Click the + button (first button)
    })

    // Type the category name
    cy.get('input[data-cy="income-category-input"]').type(testCategoryName)

    // Click Save button
    cy.get('button').contains('Save').click()

    // Wait for save and refresh
    cy.wait(1000)

    // Verify category appears in the list
    cy.contains(testCategoryName).should('exist')

    // Debug: Check what categories exist after adding
    cy.request('GET', '/test/debug/categories').then((resp) => {
      cy.log('After adding custom category:')
      cy.log('Income categories: ' + JSON.stringify(resp.body.income_categories))
      expect(resp.body.income_categories).to.include(testCategoryName)
    })

    // Now navigate to Dashboard to check if it appears in Add Income modal
    cy.visit('/dashboard')
    cy.get('.expense-header').should('exist')

    // Click "Add Income" button
    cy.get('button.btn-primary').contains('Add Income').click()

    // Get the income category dropdown and log available options
    cy.get('select[data-cy="income-category"]').then(($select) => {
      const options = $select.find('option').map((i, el) => el.value).get()
      cy.log('Available income categories in Dashboard modal: ' + options.join(', '))
      expect(options).to.include(testCategoryName)
    })

    // Select the custom category from dropdown
    cy.get('select[data-cy="income-category"]').select(testCategoryName)

    // Verify it's selected
    cy.get('select[data-cy="income-category"]').should('have.value', testCategoryName)
  })

  it('should display default income categories on Dashboard if no custom ones added', () => {
    // Don't add any custom categories, just go to Dashboard
    cy.visit('/dashboard')

    // Click "Add Income" button
    cy.get('button.btn-primary').contains('Add Income').click()

    // Check income category dropdown has options
    cy.get('select[data-cy="income-category"]').should('exist')

    // Log the available categories for debugging
    cy.get('select[data-cy="income-category"]').then(($select) => {
      const options = $select.find('option').map((i, el) => el.value).get()
      cy.log('Available default income categories: ' + options.join(', '))
      expect(options.length).to.be.greaterThan(0)
    })
  })

  it('should verify income categories across all pages (Dashboard, Income page)', () => {
    // Add custom income category
    cy.visit('/settings')
    cy.contains('h2', 'Income Category').click()

    cy.contains('h2', 'Income Category').closest('.settings-panel').within(() => {
      cy.get('button').eq(0).click()
    })

    const customCat = 'Custom Income - ' + Date.now()
    cy.get('input[data-cy="income-category-input"]').type(customCat)
    cy.get('button').contains('Save').click()

    cy.wait(1000)

    // Check Dashboard
    cy.visit('/dashboard')
    cy.get('button.btn-primary').contains('Add Income').click()
    cy.get('select[data-cy="income-category"]').then(($select) => {
      const options = $select.find('option').map((i, el) => el.value).get()
      cy.log('Dashboard categories: ' + options.join(', '))
      expect(options).to.include('Salary')
    })

    // Close modal and go to Income page
    cy.get('button.btn-ghost').contains('Cancel').click()
    cy.visit('/income')

    // Check if Add Income button exists and click it
    cy.get('button.btn-primary').contains('Add Income').click()

    // Verify categories in Income page as well
    cy.get('select[data-cy="income-category"]').then(($select) => {
      const options = $select.find('option').map((i, el) => el.value).get()
      cy.log('Income page categories: ' + options.join(', '))
      expect(options.length).to.be.greaterThan(2)
    })
  })
})
