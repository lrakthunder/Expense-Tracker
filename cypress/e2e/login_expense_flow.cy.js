// cypress/e2e/login_expense_flow.cy.js

describe('Login and Expense Page Flow', () => {
  it('should login and redirect to expense page', () => {
    // Create test user first
    cy.request('POST', '/test/create-user').then((resp) => {
      expect(resp.status).to.eq(200)
    })

    // Now login via the UI
    cy.visit('/login')
    cy.get('input[placeholder="Email"]').type('lrakthunder@gmail.com')
    cy.get('input[placeholder="Password"]').type('Gwapoakodiba_123')
    cy.get('input[type="checkbox"]#privacy').check({ force: true })
    cy.get('button.btn-signin').click()

    // Wait for redirect and check URL
    cy.url({ timeout: 10000 }).should('include', '/dashboard')

    // Check header elements
    cy.get('.expense-header').should('exist')
    cy.get('.site-logo').should('be.visible')
    cy.get('.client-name').should('be.visible')
    cy.get('.logout-btn').should('be.visible')
  })
})
