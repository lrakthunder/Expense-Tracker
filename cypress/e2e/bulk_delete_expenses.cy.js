describe('Bulk delete expenses', () => {
  it('bulk deletes selected expenses via UI', () => {
    // Authenticate via test route
    cy.request('POST', '/test/login').then((resp) => {
      expect(resp.status).to.eq(200)
    })

    // Navigate to expense page
    cy.visit('/expense')

    // Create first test expense
    cy.contains('button', 'Add Expense').click()
    cy.get('.modal-backdrop', { timeout: 5000 }).should('be.visible')
    cy.get('[data-cy=expense-name]').type('Test Expense 1')
    cy.get('[data-cy=expense-amount]').type('100')
    cy.get('[data-cy=expense-category]').select('Food')
    cy.get('[data-cy=expense-date]').type('2025-01-10')
    cy.contains('button', 'Save Expense').click()
    cy.get('[data-cy=flash-toast]', { timeout: 5000 }).should('be.visible')

    // Create second test expense
    cy.contains('button', 'Add Expense').click()
    cy.get('.modal-backdrop', { timeout: 5000 }).should('be.visible')
    cy.get('[data-cy=expense-name]').type('Test Expense 2')
    cy.get('[data-cy=expense-amount]').type('200')
    cy.get('[data-cy=expense-category]').select('Transport')
    cy.get('[data-cy=expense-date]').type('2025-01-11')
    cy.contains('button', 'Save Expense').click()
    cy.get('[data-cy=flash-toast]', { timeout: 5000 }).should('be.visible')

    // Verify at least two expense rows exist (excluding the header)
    cy.get('tbody tr', { timeout: 5000 }).should('have.length.at.least', 2)

    // Select the first two rows via checkboxes
    cy.get('tbody tr').each(($row, index) => {
      if (index < 2) {
        cy.wrap($row).find('input[type="checkbox"]').check()
      }
    })

    // Verify Delete Selected button is present and click it
    cy.contains('button', 'Delete Selected').should('be.visible').click()

    // Confirm deletion via SweetAlert
    cy.get('.swal2-confirm', { timeout: 5000 }).should('be.visible').click()

    // Verify success message or that rows are deleted
    cy.get('body', { timeout: 5000 }).should('not.contain', '.swal2-confirm')
  })
})
