describe('Add Income Flow', () => {
  it('creates R2M Salary and verifies DB', () => {
    // Use test-only login endpoint to authenticate
    cy.request('POST', '/test/login').then((resp) => {
      expect(resp.status).to.eq(200)
    })

    // Visit the dashboard (formerly /expense)
    cy.visit('/dashboard')

    // Open add income modal
    cy.contains('button', 'Add Income').click({ force: true })
    cy.get('.modal-backdrop', { timeout: 5000 }).should('be.visible')

    // Fill the form fields
    cy.get('[data-cy=income-name]', { timeout: 5000 }).type('R2M Salary')
    cy.get('[data-cy=income-amount]', { timeout: 5000 }).type('6000')
    cy.get('[data-cy=income-category]', { timeout: 5000 }).select('Salary')
    cy.get('[data-cy=income-date]', { timeout: 5000 }).type('2025-01-05')
    cy.get('[data-cy=income-note]', { timeout: 5000 }).type('Last Salary for this company')

    // Submit the form
    cy.contains('button', 'Save Income').click()

    // Wait for the success toast to appear (handles redirect/navigation)
    cy.get('[data-cy=flash-toast]', { timeout: 5000 }).should('be.visible').contains('Income added')

    // Verify DB persistence
    cy.request('GET', '/test/incomes/last').then((resp) => {
      expect(resp.status).to.eq(200)
      const income = resp.body
      expect(income.name).to.eq('R2M Salary')
      expect(Number(income.amount)).to.eq(6000)
      // Category is stored as array/JSON - parse if it's a string
      let category = income.category
      if (typeof category === 'string') {
        try { category = JSON.parse(category)[0] } catch (e) { }
      } else if (Array.isArray(category)) {
        category = category[0]
      }
      expect(category).to.eq('Salary')
      // cleanup test income
      cy.request('POST', '/test/cleanup/income')
    })
  })
})
