describe('User Registration', () => {
  it('should register a new user successfully', () => {
    cy.visit('/');
    cy.contains('Register').click();
    cy.get('input[placeholder="First Name"]').type('jobai');
    cy.get('input[placeholder="Last Name"]').type('damba');
    cy.get('input[placeholder="Username"]').type('jdamba');
    cy.get('input[placeholder="Email"]').type('jdamba@gmail.com');
    cy.get('input[placeholder="Password"]').type('Zbsi@1234');
    cy.get('input[placeholder="Confirm Password"]').type('Zbsi@1234');
    cy.get('input[type="checkbox"][id="terms"]').check();
    cy.contains('button', 'Register').click();
    // After successful registration, should redirect to dashboard or show success
    cy.url({ timeout: 10000 }).should('match', /\/(dashboard|login)/);

    // Verify user was created via backend test route
    cy.request('GET', '/test/user?email=jdamba@gmail.com').then((resp) => {
      expect(resp.status).to.eq(200);
      expect(resp.body.email).to.eq('jdamba@gmail.com');
    });

    // Cleanup: remove the test user
    cy.request('POST', '/test/cleanup/user', { email: 'jdamba@gmail.com' })
  });
});
