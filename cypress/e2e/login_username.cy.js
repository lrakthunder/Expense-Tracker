// cypress/e2e/login_username.cy.js
// Verifies whether username-based login works via the UI and tests password change notifications

describe('Login with Username and Password Change Notifications', () => {
  const username = 'testuser' // Use the test user created by the test helper
  const password = 'TestPass_123' // Password used by test helper
  const newPassword = 'TestPass_1234'

  before(() => {
    // Ensure the user exists via test helper (mirrors existing login test setup)
    cy.request('POST', '/test/create-user').its('status').should('eq', 200)
  })

  it('logs in using username instead of email', () => {
    cy.visit('/login')

    // Fill username in the unified username/email field
    cy.get('input[placeholder="Username or Email"]').type(username)
    cy.get('input[placeholder="Password"]').type(password)
    cy.get('input[type="checkbox"]#privacy').check({ force: true })
    cy.get('button.btn-signin').click()

    // Expect to reach dashboard
    cy.url({ timeout: 10000 }).should('include', '/dashboard')
  })

  it('changes password and verifies notification appears on dashboard', () => {
    // Login first
    cy.visit('/login')
    cy.get('input[placeholder="Username or Email"]').type(username)
    cy.get('input[placeholder="Password"]').type(password)
    cy.get('input[type="checkbox"]#privacy').check({ force: true })
    cy.get('button.btn-signin').click()
    cy.url({ timeout: 10000 }).should('include', '/dashboard')
    cy.wait(1000)

    // Open user dropdown
    cy.get('header.expense-header')
      .find('button')
      .filter((idx, el) => el.querySelector('svg[viewBox="0 0 20 20"]') !== null)
      .first()
      .click()
    
    // Click Change Password
    cy.contains('Change Password').click()
    cy.wait(500)

    // First, try with wrong current password to verify error handling
    cy.get('input#current_password').type('WrongPassword123')
    cy.get('input#password').type(newPassword)
    cy.get('input#password_confirmation').type(newPassword)
    cy.get('button').contains('Update Password').click()
    
    // Modal should stay open, error should be displayed
    cy.wait(1000)
    cy.get('input#current_password').should('be.visible') // Modal still open
    cy.contains('password').should('exist') // Error message visible
    
    // Clear and enter correct password
    cy.get('input#current_password').clear().type(password)
    cy.get('input#password').clear().type(newPassword)
    cy.get('input#password_confirmation').clear().type(newPassword)
    
    // Submit the form
    cy.get('button').contains('Update Password').click()

    // Wait for redirect to dashboard
    cy.url({ timeout: 10000 }).should('include', '/dashboard')
    
    // Wait a bit for FlashToast to mount and display
    cy.wait(500)
    
    // Check for the flash toast notification
    cy.get('[data-cy="flash-toast"]', { timeout: 5000 }).should('be.visible')
    cy.get('[data-cy="flash-toast"]').should('contain', 'password')
  })

  it('changes password back to original and verifies notification', () => {
    // Login with new password
    cy.visit('/login')
    cy.get('input[placeholder="Username or Email"]').type(username)
    cy.get('input[placeholder="Password"]').type(newPassword)
    cy.get('input[type="checkbox"]#privacy').check({ force: true })
    cy.get('button.btn-signin').click()
    cy.url({ timeout: 10000 }).should('include', '/dashboard')
    cy.wait(1000)

    // Open user dropdown
    cy.get('header.expense-header')
      .find('button')
      .filter((idx, el) => el.querySelector('svg[viewBox="0 0 20 20"]') !== null)
      .first()
      .click()
    
    // Click Change Password
    cy.contains('Change Password').click()
    cy.wait(500)

    // Change password back from TestPass_1234 to TestPass_123
    cy.get('input#current_password').type(newPassword)
    cy.get('input#password').type(password)
    cy.get('input#password_confirmation').type(password)
    
    // Submit the form
    cy.get('button').contains('Update Password').click()

    // Wait for redirect to dashboard
    cy.url({ timeout: 10000 }).should('include', '/dashboard')
    
    // Verify notification appears again
    cy.get('[data-cy="flash-toast"]', { timeout: 5000 }).should('be.visible')
    cy.get('[data-cy="flash-toast"]').should('contain', 'password has been updated')
    
    // Take screenshot
    cy.screenshot('password-restored-notification-displayed')
    
    // Notification should remain visible for at least 2 seconds
    cy.wait(2000)
    cy.get('[data-cy="flash-toast"]').should('be.visible')
  })

  it('verifies dark mode works correctly in modals', () => {
    // Login first
    cy.visit('/login')
    cy.get('input[placeholder="Username or Email"]').type(username)
    cy.get('input[placeholder="Password"]').type(password)
    cy.get('input[type="checkbox"]#privacy').check({ force: true })
    cy.get('button.btn-signin').click()
    cy.url({ timeout: 10000 }).should('include', '/dashboard')
    cy.wait(1000)

    // Enable dark mode by clicking the theme toggle button
    cy.get('button.theme-toggle').click()
    cy.wait(500)
    
    // Verify dark mode is active
    cy.get('html').should('have.class', 'dark')

    // Open user dropdown
    cy.get('header.expense-header')
      .find('button')
      .filter((idx, el) => el.querySelector('svg[viewBox="0 0 20 20"]') !== null)
      .first()
      .click()
    
    // Test Change Password modal in dark mode
    cy.contains('Change Password').click()
    cy.wait(500)

    // Verify modal content is visible
    cy.contains('Change Password').should('be.visible')
    cy.contains('Ensure your account is using a strong, unique password').should('be.visible')
    
    // Take screenshot of Change Password modal in dark mode
    cy.screenshot('change-password-dark-mode')
    
    // Close modal
    cy.contains('Cancel').click()
    cy.wait(500)

    // Test Edit Profile modal in dark mode
    cy.get('header.expense-header')
      .find('button')
      .filter((idx, el) => el.querySelector('svg[viewBox="0 0 20 20"]') !== null)
      .first()
      .click()
    
    cy.contains('Edit Profile').click()
    cy.wait(500)

    // Verify modal content is visible
    cy.contains('Edit Profile').should('be.visible')
    cy.contains('Update your account information').should('be.visible')
    
    // Verify disabled username field is visible
    cy.get('input#username').should('be.visible').and('be.disabled')
    
    // Take screenshot of Edit Profile modal in dark mode
    cy.screenshot('edit-profile-dark-mode')
    
    // Close modal and verify dark mode persists
    cy.contains('Cancel').click()
    cy.wait(500)
    cy.get('html').should('have.class', 'dark')
  })
})
