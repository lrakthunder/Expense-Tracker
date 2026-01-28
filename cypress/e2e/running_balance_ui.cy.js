describe('Expense running balance (UI-derived)', () => {
  it('computes expected from table and validates balance column', () => {
    const runId = Date.now()
    const testEmail = `cypress_rb_${runId}@test.local`
    
    const income = { name: `RB Income ${runId}`, amount: 3200, category: 'Salary', date: '2030-01-01', note: 'rb-seed' }
    const seeds = [
      { name: `RB Expense A ${runId}`, amount: 500, category: 'Utilities', date: '2030-01-05', note: 'seed A' },
      { name: `RB Expense B ${runId}`, amount: 750, category: 'Food', date: '2030-01-06', note: 'seed B' },
      { name: `RB Expense C ${runId}`, amount: 125, category: 'Transport', date: '2030-01-07', note: 'seed C' },
    ]

    // Create a fresh clean test user via testing route
    cy.request({
      method: 'POST',
      url: '/test/create-clean-user',
      body: { email: testEmail, name: `Cypress RB ${runId}` }
    }).its('status').should('eq', 200)

    cy.visit('/dashboard')

    // Add income via modal
    cy.contains('button', 'Add Income').click({ force: true })
    cy.get('.modal-backdrop', { timeout: 8000 }).should('be.visible')
    cy.get('[data-cy=income-name]').type(income.name)
    cy.get('[data-cy=income-amount]').type(String(income.amount))
    cy.get('[data-cy=income-category]').select(income.category)
    cy.get('[data-cy=income-date]').type(income.date)
    cy.get('[data-cy=income-note]').type(income.note)
    cy.contains('button', 'Save Income').click()
    cy.get('.modal-backdrop', { timeout: 8000 }).should('not.exist')

    // Add expenses via modal
    seeds.forEach((s) => {
      cy.contains('button', 'Add Expense').click({ force: true })
      cy.get('.modal-backdrop', { timeout: 8000 }).should('be.visible')
      cy.get('[data-cy=expense-name]').type(s.name)
      cy.get('[data-cy=expense-amount]').type(String(s.amount))
      cy.get('[data-cy=expense-category]').select(s.category)
      cy.get('[data-cy=expense-date]').type(s.date)
      cy.get('[data-cy=expense-note]').type(s.note)
      cy.contains('button', 'Save Expense').click()
      cy.get('.modal-backdrop', { timeout: 8000 }).should('not.exist')
    })

    cy.visit('/expense?per_page=50&sort_by=transaction_date&sort_dir=desc')
    cy.contains('h1', 'Expenses').should('be.visible')

    // Parse totals from footer; income = expenses + balance
    cy.get('tfoot tr').within(() => {
      cy.get('td').eq(1).invoke('text').as('totalExpensesTxt')
      cy.get('td').eq(2).invoke('text').as('overallBalanceTxt')
    })

    cy.get('@totalExpensesTxt').then((expensesTxt) => {
      const totalExpenses = Number(String(expensesTxt).replace(/[,.\s]/g, (m) => (m === ',' ? '' : m)))
      cy.get('@overallBalanceTxt').then((balanceTxt) => {
        const overallBalance = Number(String(balanceTxt).replace(/[,.\s]/g, (m) => (m === ',' ? '' : m)))
        const totalIncome = totalExpenses + overallBalance

        cy.get('tbody tr').then(($rows) => {
          const names = seeds.map((s) => s.name)
          names.forEach((name) => {
            let rowIndex = -1
            for (let i = 0; i < $rows.length; i++) {
              if ($rows[i].innerText.includes(name)) { rowIndex = i; break }
            }
            expect(rowIndex, `row index for ${name}`).to.be.gte(0)

            let futureSum = 0
            for (let j = rowIndex; j < $rows.length; j++) {
              const cells = $rows[j].querySelectorAll('td')
              const amountTxt = cells[5]?.innerText || '0'
              futureSum += Number(String(amountTxt).replace(/[,.\s]/g, (m) => (m === ',' ? '' : m)))
            }
            const expectedValue = Number((totalIncome - futureSum).toFixed(2))

            const balanceTxt = $rows[rowIndex].querySelectorAll('td')[6]?.innerText || '0'
            const numeric = Number(String(balanceTxt).replace(/[,.\s]/g, (m) => (m === ',' ? '' : m)))
            expect(numeric).to.be.closeTo(expectedValue, 0.01)
          })
        })
      })
    })
  })
})
