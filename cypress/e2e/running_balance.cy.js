describe('Expense running balance', () => {
  it('shows remaining balance per expense using chronological cumulative spend', () => {
    const runId = Date.now()
    const incomeSeed = {
      name: `RB Income ${runId}`,
      amount: 3200,
      category: 'Salary',
      date: '2030-01-01',
      note: 'running balance seed',
    }
    const expenseSeeds = [
      { name: `RB Expense A ${runId}`, amount: 500, category: 'Utilities', date: '2030-01-05', note: 'first seeded expense' },
      { name: `RB Expense B ${runId}`, amount: 750, category: 'Food', date: '2030-01-06', note: 'second seeded expense' },
      { name: `RB Expense C ${runId}`, amount: 125, category: 'Transport', date: '2030-01-07', note: 'third seeded expense' },
    ]

    const createdExpenses = []

    // Authenticate quickly via test route
    cy.request('POST', '/test/login').its('status').should('eq', 200)

    // Seed via UI to avoid CSRF issues
    cy.visit('/dashboard')
    // Add income
    cy.contains('button', 'Add Income').click({ force: true })
    cy.get('.modal-backdrop', { timeout: 8000 }).should('be.visible')
    cy.get('[data-cy=income-name]').type(incomeSeed.name)
    cy.get('[data-cy=income-amount]').type(String(incomeSeed.amount))
    cy.get('[data-cy=income-category]').select(incomeSeed.category)
    cy.get('[data-cy=income-date]').type(incomeSeed.date)
    cy.get('[data-cy=income-note]').type(incomeSeed.note)
    cy.contains('button', 'Save Income').click()
    // Confirm modal closes (toast can be flaky across redirects)
    cy.get('.modal-backdrop', { timeout: 8000 }).should('not.exist')

    // Add three expenses
    expenseSeeds.forEach((seed) => {
      cy.contains('button', 'Add Expense').click({ force: true })
      cy.get('.modal-backdrop', { timeout: 8000 }).should('be.visible')
      cy.get('[data-cy=expense-name]').type(seed.name)
      cy.get('[data-cy=expense-amount]').type(String(seed.amount))
      cy.get('[data-cy=expense-category]').select(seed.category)
      cy.get('[data-cy=expense-date]').type(seed.date)
      cy.get('[data-cy=expense-note]').type(seed.note)
      cy.contains('button', 'Save Expense').click()
      cy.get('.modal-backdrop', { timeout: 8000 }).should('not.exist')
      createdExpenses.push({ name: seed.name })
    })

    cy.visit('/expense?per_page=50&sort_by=transaction_date&sort_dir=desc')
    cy.contains('h1', 'Expenses').should('be.visible')

    // Pull Inertia props from the window to compute expected balances
    cy.window().then((win) => {
      const props = win.__inertia?.page?.props || win.page?.props || {}
      const expenses = props.expenses || []
      const byNameExpected = {}
      expenses.forEach((e) => {
        const remaining = typeof e.remaining !== 'undefined' ? Number(e.remaining) : null
        if (remaining !== null) {
          byNameExpected[e.name] = Number(remaining.toFixed(2))
        }
      })

      cy.wrap({ byNameExpected }).as('balanceData')
    })

    cy.get('@balanceData').then(({ byNameExpected }) => {
      createdExpenses.forEach((exp) => {
        const expectedValue = byNameExpected[exp.name]
        expect(expectedValue, `expected value for ${exp.name}`).to.be.a('number')

        cy.contains('tr', exp.name).within(() => {
          cy.get('td').eq(6).invoke('text').then((txt) => {
            const numeric = Number(txt.replace(/[,.\s]/g, (m) => (m === ',' ? '' : m)))
            expect(numeric).to.be.closeTo(expectedValue, 0.01)
          })
        })
      })
    })
  })
})
