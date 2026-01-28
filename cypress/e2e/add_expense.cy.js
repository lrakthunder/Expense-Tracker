describe('Add Expense Flow', () => {
  it('creates Tire Change and verifies DB', () => {
    // Authenticate via test route
    cy.request('POST', '/test/login').then((resp) => {
      expect(resp.status).to.eq(200)
    })

    // Visit dashboard (formerly /expense)
    cy.visit('/dashboard')

    // Open add expense modal
    cy.contains('button', 'Add Expense').click({ force: true })
    cy.get('.modal-backdrop', { timeout: 5000 }).should('be.visible')

    // Fill the form fields
    cy.get('[data-cy=expense-name]', { timeout: 5000 }).type('Tire Change')
    cy.get('[data-cy=expense-amount]', { timeout: 5000 }).type('2000')
    cy.get('[data-cy=expense-category]', { timeout: 5000 }).select('Utilities')
    cy.get('[data-cy=expense-date]', { timeout: 5000 }).type('2025-01-05')
    cy.get('[data-cy=expense-note]', { timeout: 5000 }).type('Immediate')

    // Submit the form
    cy.contains('button', 'Save Expense').click()

    // Wait for the success toast to appear (handles redirect/navigation)
    cy.get('[data-cy=flash-toast]', { timeout: 5000 }).should('be.visible').contains('Expense added')

    // Verify DB persistence
    cy.request('GET', '/test/expenses/last').then((resp) => {
      expect(resp.status).to.eq(200)
      const expense = resp.body
      expect(expense.name).to.eq('Tire Change')
      expect(Number(expense.amount)).to.eq(2000)
      // Category is stored as array/JSON - parse if it's a string
      let category = expense.category
      if (typeof category === 'string') {
        try { category = JSON.parse(category)[0] } catch (e) { }
      } else if (Array.isArray(category)) {
        category = category[0]
      }
      expect(category).to.eq('Utilities')
      expect(expense.transaction_date).to.include('2025-01-05')
      expect(expense.note).to.eq('Immediate')
      // cleanup test expense
      cy.request('POST', '/test/cleanup/expense')
    })
  })
})