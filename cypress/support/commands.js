// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Programmatic login to create an authenticated session without using the UI.
Cypress.Commands.add('login', (email, password) => {
	// Fetch the login page to establish cookies, then read XSRF-TOKEN cookie for CSRF
	return cy.request('GET', '/login').then(() => {
		return cy.getCookie('XSRF-TOKEN').then((cookie) => {
			const token = cookie && cookie.value ? decodeURIComponent(cookie.value) : null

			const options = {
				method: 'POST',
				url: '/login',
				form: true,
				body: {
					email,
					password,
					_token: token,
				},
				headers: {
					'X-XSRF-TOKEN': token || '',
					'X-Requested-With': 'XMLHttpRequest',
				},
			}

			return cy.request(options)
		})
	})
})
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })