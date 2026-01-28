// cypress/e2e/responsive-layout.cy.js

describe('Responsive Layout Tests', () => {
  beforeEach(() => {
    // Login
    cy.request('POST', '/test/login').its('status').should('eq', 200)
    cy.visit('/dashboard')
    cy.get('.expense-header').should('exist')
  })

  it('should display stat cards in 2x2 grid on mobile view (480px)', () => {
    // Set viewport to mobile
    cy.viewport(480, 800)
    
    // Wait for cards to render
    cy.get('.stat-cards', { timeout: 5000 }).should('exist')
    
    // Get all stat cards
    cy.get('.stat-card').then(($cards) => {
      expect($cards.length).to.equal(4)
      
      // Get unique top positions (rows)
      const topPositions = new Set()
      $cards.each((index, card) => {
        topPositions.add(Math.round(card.getBoundingClientRect().top))
      })
      
      // Must have exactly 2 different Y positions (2 rows, 2 cards each)
      expect(topPositions.size).to.equal(2)
      
      cy.log('✓ Mobile: Cards displayed in 2 rows × 2 columns grid')
    })
  })

  it('should display stat cards in 1 row on tablet view (768px)', () => {
    // Set viewport to tablet
    cy.viewport(768, 1024)
    
    // Wait for cards to render
    cy.get('.stat-cards', { timeout: 5000 }).should('exist')
    
    // Get all stat cards
    cy.get('.stat-card').then(($cards) => {
      expect($cards.length).to.equal(4)
      
      // All cards should be in the same row (same Y position)
      const card1Y = $cards.eq(0).offset().top
      const card2Y = $cards.eq(1).offset().top
      const card3Y = $cards.eq(2).offset().top
      const card4Y = $cards.eq(3).offset().top
      
      expect(card1Y).to.equal(card2Y)
      expect(card2Y).to.equal(card3Y)
      expect(card3Y).to.equal(card4Y)
      
      cy.log('✓ Tablet: 1 row × 4 columns layout confirmed')
    })
  })

  it('should display stat cards in 1 row on desktop view (1280px)', () => {
    // Set viewport to desktop
    cy.viewport(1280, 720)
    
    // Wait for cards to render
    cy.get('.stat-cards', { timeout: 5000 }).should('exist')
    
    // Get all stat cards
    cy.get('.stat-card').then(($cards) => {
      expect($cards.length).to.equal(4)
      
      // All cards should be in the same row
      const card1Y = $cards.eq(0).offset().top
      const card2Y = $cards.eq(1).offset().top
      const card3Y = $cards.eq(2).offset().top
      const card4Y = $cards.eq(3).offset().top
      
      expect(card1Y).to.equal(card2Y)
      expect(card2Y).to.equal(card3Y)
      expect(card3Y).to.equal(card4Y)
      
      cy.log('✓ Desktop: 1 row × 4 columns layout confirmed')
    })
  })

  it('should show hamburger menu on mobile (480px)', () => {
    cy.viewport(480, 800)
    
    // Burger menu should be visible
    cy.get('.burger').should('be.visible')
    cy.log('✓ Mobile: Hamburger menu is visible')
  })

  it('should show hamburger menu on tablet (768px)', () => {
    cy.viewport(768, 1024)
    
    // Burger menu should be visible on tablet
    cy.get('.burger').should('be.visible')
    cy.log('✓ Tablet: Hamburger menu is visible')
  })

  it('should not overlap logo and burger menu on mobile', () => {
    cy.viewport(480, 800)
    
    cy.get('.burger').then(($burger) => {
      cy.get('.site-logo').then(($logo) => {
        // Get positions
        const burgerRect = $burger[0].getBoundingClientRect()
        const logoRect = $logo[0].getBoundingClientRect()
        
        // Burger should be on the left
        expect(burgerRect.right).to.be.lessThan(logoRect.left)
        
        cy.log('✓ Mobile: Logo and burger menu do not overlap')
      })
    })
  })

  it('should have proper sidebar positioning on tablet (768px)', () => {
    cy.viewport(768, 1024)
    
    // Open sidebar by clicking burger
    cy.get('.burger').click()
    
    // Sidebar should be visible
    cy.get('.sidebar').should('be.visible')
    
    // Sidebar should start below header (not overlap)
    cy.get('.sidebar').then(($sidebar) => {
      const sidebarTop = $sidebar[0].getBoundingClientRect().top
      // Should be approximately 60px from top (header height)
      expect(sidebarTop).to.be.closeTo(60, 10)
      cy.log('✓ Tablet: Sidebar positioned below header without overlap')
    })
  })

  it('should have proper header padding on mobile (480px)', () => {
    cy.viewport(480, 800)
    
    cy.get('.expense-header').then(($header) => {
      const styles = window.getComputedStyle($header[0])
      const paddingLeft = parseFloat(styles.paddingLeft)
      
      // Should have left padding of 70px to accommodate burger menu
      expect(paddingLeft).to.be.closeTo(70, 5)
      cy.log('✓ Mobile: Header has proper left padding')
    })
  })

  it('should display pie chart responsively on mobile', () => {
    cy.viewport(480, 800)
    
    // Pie chart should exist and be visible
    cy.get('canvas').should('exist')
    cy.log('✓ Mobile: Pie chart is rendered')
  })

  it('should toggle sidebar on mobile', () => {
    cy.viewport(480, 800)
    
    // Sidebar should be hidden initially
    cy.get('.sidebar').should('not.have.class', 'sidebar-open')
    
    // Click burger to open
    cy.get('.burger').click()
    cy.get('html').should('have.class', 'sidebar-open')
    
    // Click burger again to close
    cy.get('.burger').click()
    cy.get('html').should('not.have.class', 'sidebar-open')
    
    cy.log('✓ Mobile: Sidebar toggle works correctly')
  })

  it('should close sidebar when clicking outside on mobile', () => {
    cy.viewport(480, 800)
    
    // Open sidebar
    cy.get('.burger').click()
    cy.get('html').should('have.class', 'sidebar-open')
    
    // Click outside sidebar (on the main content area)
    cy.get('.expense-main').click({ force: true })
    
    // Sidebar should close
    cy.get('html').should('not.have.class', 'sidebar-open')
    
    cy.log('✓ Mobile: Sidebar closes when clicking outside')
  })

  it('should close sidebar when clicking outside on tablet', () => {
    cy.viewport(768, 1024)
    
    // Open sidebar
    cy.get('.burger').click()
    cy.get('html').should('have.class', 'sidebar-open')
    
    // Click outside sidebar (on the main content area)
    cy.get('.expense-main').click({ force: true })
    
    // Sidebar should close
    cy.get('html').should('not.have.class', 'sidebar-open')
    
    cy.log('✓ Tablet: Sidebar closes when clicking outside')
  })

  it('should redirect to dashboard when logo is clicked on mobile', () => {
    cy.viewport(480, 800)
    
    // Navigate to a different page first
    cy.visit('/expense')
    cy.url().should('include', '/expense')
    
    // Click logo
    cy.get('.site-logo').click()
    
    // Should redirect to dashboard
    cy.url().should('include', '/dashboard')
    cy.log('✓ Mobile: Logo redirects to dashboard')
  })

  it('should redirect to dashboard when logo is clicked on tablet', () => {
    cy.viewport(768, 1024)
    
    // Navigate to a different page first
    cy.visit('/expense')
    cy.url().should('include', '/expense')
    
    // Click logo
    cy.get('.site-logo').click()
    
    // Should redirect to dashboard
    cy.url().should('include', '/dashboard')
    cy.log('✓ Tablet: Logo redirects to dashboard')
  })

  it('should redirect to dashboard when logo is clicked on desktop', () => {
    cy.viewport(1280, 720)
    
    // Navigate to a different page first
    cy.visit('/expense')
    cy.url().should('include', '/expense')
    
    // Click logo
    cy.get('.site-logo').click()
    
    // Should redirect to dashboard
    cy.url().should('include', '/dashboard')
    cy.log('✓ Desktop: Logo redirects to dashboard')
  })
})
