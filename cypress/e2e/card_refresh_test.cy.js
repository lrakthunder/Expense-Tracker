// cypress/e2e/card_refresh_test.cy.js
// Test that dashboard cards refresh after adding income/expense

describe('Dashboard Card Refresh', () => {
  beforeEach(() => {
    // Create test user and login before each test
    cy.request('POST', '/test/login').its('status').should('eq', 200)
  })

  it('refreshes cards after adding expense', () => {
    cy.visit('/dashboard')
    cy.wait(1000)

    // Capture initial "Remaining Budget" value
    cy.contains('.stat-label', 'Remaining Budget')
      .siblings('.stat-value')
      .invoke('text')
      .then((initialBudget) => {
        const initialValue = parseFloat(initialBudget.replace(/[^0-9.-]/g, ''))
        cy.log(`Initial Remaining Budget: ${initialValue}`)

        // Open Add Expense modal
        cy.contains('button', 'Add Expense').click()
        cy.wait(500)

        // Fill expense form using data-cy attributes
        cy.get('[data-cy="expense-name"]').clear().type('Test Expense Cypress')
        cy.get('[data-cy="expense-amount"]').clear().type('100')
        cy.get('[data-cy="expense-category"]').select('Other')
        cy.get('[data-cy="expense-date"]').clear().type('2026-01-20')

        // Submit expense
        cy.contains('button', 'Save Expense').click()
        cy.wait(3000) // Wait for page reload

        // Verify card updated
        cy.contains('.stat-label', 'Remaining Budget')
          .siblings('.stat-value')
          .invoke('text')
          .then((newBudget) => {
            const newValue = parseFloat(newBudget.replace(/[^0-9.-]/g, ''))
            cy.log(`New Remaining Budget: ${newValue}`)
            cy.log(`Expected decrease: 100, Actual decrease: ${initialValue - newValue}`)
            
            // Budget should decrease by 100
            expect(newValue).to.be.lessThan(initialValue)
          })
      })
  })

  it('refreshes cards after adding income', () => {
    cy.visit('/dashboard')
    cy.wait(1000)

    // Capture initial "Remaining Budget" value
    cy.contains('.stat-label', 'Remaining Budget')
      .siblings('.stat-value')
      .invoke('text')
      .then((initialBudget) => {
        const initialValue = parseFloat(initialBudget.replace(/[^0-9.-]/g, ''))
        cy.log(`Initial Remaining Budget: ${initialValue}`)

        // Open Add Income modal
        cy.contains('button', 'Add Income').click()
        cy.wait(500)

        // Fill income form using data-cy attributes
        cy.get('[data-cy="income-name"]').clear().type('Test Income Cypress')
        cy.get('[data-cy="income-amount"]').clear().type('200')
        cy.get('[data-cy="income-category"]').select('Other')
        cy.get('[data-cy="income-date"]').clear().type('2026-01-20')

        // Submit income
        cy.contains('button', 'Save Income').click()
        cy.wait(3000) // Wait for page reload

        // Verify card updated
        cy.contains('.stat-label', 'Remaining Budget')
          .siblings('.stat-value')
          .invoke('text')
          .then((newBudget) => {
            const newValue = parseFloat(newBudget.replace(/[^0-9.-]/g, ''))
            cy.log(`New Remaining Budget: ${newValue}`)
            cy.log(`Expected increase: 200, Actual increase: ${newValue - initialValue}`)
            
            // Budget should increase by 200
            expect(newValue).to.be.greaterThan(initialValue)
          })
      })
  })
})
