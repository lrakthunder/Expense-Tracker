describe('Expense running balance (sort-insensitive)', () => {
  it('shows proper running balance regardless of sort order', () => {
    const runId = Date.now()
    const testEmail = `cypress_rb_sort_${runId}@test.local`

    const incomes = [
      { name: `RB Sort Income 1 ${runId}`, amount: 5000, category: 'Salary', date: '2030-01-01', note: 'seed I1' },
      { name: `RB Sort Income 2 ${runId}`, amount: 2000, category: 'Salary', date: '2030-01-02', note: 'seed I2' },
    ]
    const expenses = [
      { name: `RB Sort Exp A ${runId}`, amount: 1000, category: 'Utilities', date: '2030-01-03', note: 'seed A' },
      { name: `RB Sort Exp B ${runId}`, amount: 500, category: 'Food', date: '2030-01-05', note: 'seed B' },
      { name: `RB Sort Exp C ${runId}`, amount: 300, category: 'Transport', date: '2030-01-07', note: 'seed C' },
    ]

    // Clean user
    cy.request({ method: 'POST', url: '/test/create-clean-user', body: { email: testEmail, name: `Cypress RB Sort ${runId}` } })
      .its('status').should('eq', 200)

    // Seed via UI
    cy.visit('/dashboard')

    incomes.forEach((inc) => {
      cy.contains('button', 'Add Income').click({ force: true })
      cy.get('.modal-backdrop', { timeout: 8000 }).should('be.visible')
      cy.get('[data-cy=income-name]').type(inc.name)
      cy.get('[data-cy=income-amount]').type(String(inc.amount))
      cy.get('[data-cy=income-category]').select(inc.category)
      cy.get('[data-cy=income-date]').type(inc.date)
      cy.get('[data-cy=income-note]').type(inc.note)
      cy.contains('button', 'Save Income').click()
      cy.get('.modal-backdrop', { timeout: 8000 }).should('not.exist')
    })

    expenses.forEach((exp) => {
      cy.contains('button', 'Add Expense').click({ force: true })
      cy.get('.modal-backdrop', { timeout: 8000 }).should('be.visible')
      cy.get('[data-cy=expense-name]').type(exp.name)
      cy.get('[data-cy=expense-amount]').type(String(exp.amount))
      cy.get('[data-cy=expense-category]').select(exp.category)
      cy.get('[data-cy=expense-date]').type(exp.date)
      cy.get('[data-cy=expense-note]').type(exp.note)
      cy.contains('button', 'Save Expense').click()
      cy.get('.modal-backdrop', { timeout: 8000 }).should('not.exist')
    })

    // Compute expected chronologically (oldest first)
    const totalIncome = incomes.reduce((s, i) => s + Number(i.amount), 0)
    const expChrono = [...expenses].sort((a, b) => new Date(a.date) - new Date(b.date))
    const expectedByName = {}
    let runningSpent = 0
    expChrono.forEach((e) => {
      runningSpent += Number(e.amount)
      expectedByName[e.name] = Number((totalIncome - runningSpent).toFixed(2))
    })

    // Visit expense page sorted by amount desc
    cy.visit('/expense?per_page=50&sort_by=amount&sort_dir=desc')
    cy.contains('h1', 'Expenses').should('be.visible')

    const assertBalances = () => {
      expenses.forEach((e) => {
        const expected = expectedByName[e.name]
        cy.contains('tr', e.name).within(() => {
          cy.get('td').eq(6).invoke('text').then((txt) => {
            const numeric = Number(String(txt).replace(/[,.\s]/g, (m) => (m === ',' ? '' : m)))
            expect(numeric).to.be.closeTo(expected, 0.01)
          })
        })
      })
    }

    assertBalances()

    // Change sort: Transaction Date asc
    cy.contains('th', 'Transaction Date').click({ force: true })
    cy.wait(500) // allow Inertia navigation
    assertBalances()

    // Change sort: Transaction Date desc
    cy.contains('th', 'Transaction Date').click({ force: true })
    cy.wait(500)
    assertBalances()

    // Validate totals row too
    cy.get('tfoot tr').within(() => {
      cy.get('td').eq(1).invoke('text').then((totalExpTxt) => {
        const totalExpenses = Number(String(totalExpTxt).replace(/[,.\s]/g, (m) => (m === ',' ? '' : m)))
        expect(totalExpenses).to.be.closeTo(expenses.reduce((s, e) => s + Number(e.amount), 0), 0.01)
      })
      cy.get('td').eq(2).invoke('text').then((overallBalanceTxt) => {
        const overallBalance = Number(String(overallBalanceTxt).replace(/[,.\s]/g, (m) => (m === ',' ? '' : m)))
        expect(overallBalance).to.be.closeTo(totalIncome - expenses.reduce((s, e) => s + Number(e.amount), 0), 0.01)
      })
    })
  })
})
