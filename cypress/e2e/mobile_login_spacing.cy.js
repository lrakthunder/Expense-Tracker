describe('Mobile Login Spacing Test', () => {
    beforeEach(() => {
        cy.viewport(375, 667) // iPhone SE
        cy.visit('/login')
    })

    it('should check mobile layout spacing and gaps', () => {
        // Wait for page to load
        cy.get('.login-bg').should('be.visible')
        
        // Log the header section dimensions
        cy.get('.left-section').then($el => {
            const rect = $el[0].getBoundingClientRect()
            cy.log(`Left section: height=${rect.height}px, margin-bottom=${$el.css('margin-bottom')}`)
        })
        
        // Log the login container position
        cy.get('.login-container').then($el => {
            const rect = $el[0].getBoundingClientRect()
            cy.log(`Login container: top=${rect.top}px, margin-top=${$el.css('margin-top')}`)
        })
        
        // Check logo and welcome are side by side
        cy.get('.header-row').should('be.visible')
        cy.get('.logo-image').should('be.visible')
        cy.get('.welcome-title').should('contain', 'Welcome')
        
        // Take screenshot for manual inspection
        cy.screenshot('mobile-login-spacing')
        
        // Log computed styles
        cy.get('.main-container').then($el => {
            cy.log(`Main container gap: ${$el.css('gap')}`)
        })
    })

    it('should measure gap between header and login card', () => {
        cy.get('.left-section').then($header => {
            const headerRect = $header[0].getBoundingClientRect()
            
            cy.get('.login-container').then($login => {
                const loginRect = $login[0].getBoundingClientRect()
                const gap = loginRect.top - headerRect.bottom
                
                cy.log(`Gap between header and login card: ${gap}px`)
                cy.log(`Header bottom: ${headerRect.bottom}px`)
                cy.log(`Login top: ${loginRect.top}px`)
                
                // Verify the gap is minimal (should be negative or very small)
                expect(gap).to.be.lessThan(5)
            })
        })
    })
})
