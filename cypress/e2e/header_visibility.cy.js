describe('Header Visibility Across Pages', () => {
  beforeEach(() => {
    // Create test user
    cy.request('POST', '/test/create-user').its('status').should('eq', 200);
    
    // Login
    cy.visit('http://thunderexpensetracker.com:8002/login');
    cy.get('input[placeholder="Username or Email"]').type('testuser');
    cy.get('input[placeholder="Password"]').type('TestPass_123');
    cy.get('input[type="checkbox"]#privacy').check({ force: true });
    cy.get('button.btn-signin').click();
    cy.wait(1500);
  });

  it('should display user name on Income page', () => {
    cy.visit('http://thunderexpensetracker.com:8002/income');
    cy.get('.client-name').should('be.visible');
    cy.get('.client-name').should('not.have.css', 'display', 'none');
    cy.get('.client-name').should('have.length.greaterThan', 0);
    cy.log('Income page - Name is visible');
  });

  it('should display user name on Report page', () => {
    cy.visit('http://thunderexpensetracker.com:8002/report');
    cy.wait(2000);  // Longer wait for page load
    cy.get('.expense-header').should('exist');
    cy.get('.client-name').should('be.visible');
    cy.get('.client-name').should('not.have.css', 'display', 'none');
    cy.get('.client-name').should('have.length.greaterThan', 0);
    cy.log('Report page - Name is visible');
  });

  it('should display user name on Calendar page', () => {
    cy.visit('http://thunderexpensetracker.com:8002/calendar');
    cy.wait(2000);  // Longer wait for page load
    cy.get('.expense-header').should('exist');
    cy.get('.client-name').should('be.visible');
    cy.get('.client-name').should('not.have.css', 'display', 'none');
    cy.get('.client-name').should('have.length.greaterThan', 0);
    cy.log('Calendar page - Name is visible');
  });

  it('should have correct header structure on all pages', () => {
    const pages = ['/income', '/report', '/calendar'];
    
    pages.forEach(page => {
      cy.visit(`http://thunderexpensetracker.com:8002${page}`);
      
      // Check header exists
      cy.get('.expense-header').should('exist');
      
      // Check all header elements exist
      cy.get('.expense-header').within(() => {
        cy.get('.client-name').should('exist');
        cy.get('.logout-btn').should('exist');
        cy.get('.theme-toggle').should('exist');
      });
      
      // Check header has proper flex layout
      cy.get('.expense-header').should('have.css', 'display', 'flex');
      
      // Get computed width of client-name to verify it has space
      cy.get('.client-name').then(($el) => {
        const width = $el.width();
        cy.log(`${page}: client-name width = ${width}px`);
        expect(width).to.be.greaterThan(0);
      });
    });
  });

  it('should show DOM structure on Report page', () => {
    cy.visit('http://thunderexpensetracker.com:8002/report');
    cy.wait(500);
    
    // Get outer HTML of header to see the structure
    cy.get('.expense-header').then(($header) => {
      cy.log('Header HTML: ' + $header[0].outerHTML.substring(0, 500));
    });

    // Check if client-name exists in DOM
    cy.document().then((doc) => {
      const names = doc.querySelectorAll('.client-name');
      cy.log(`Found ${names.length} .client-name elements`);
      names.forEach((el, i) => {
        cy.log(`Element ${i}: text="${el.textContent}", width=${el.offsetWidth}, height=${el.offsetHeight}`);
      });
    });

    // Check parent div
    cy.get('.expense-header > div').should('exist').then(($div) => {
      cy.log(`Parent div: width=${$div[0].offsetWidth}, height=${$div[0].offsetHeight}, display=${$div.css('display')}`);
    });
  });

  it('should show user name text content', () => {
    cy.visit('http://thunderexpensetracker.com:8002/income');
    cy.get('.client-name').invoke('text').then((text) => {
      cy.log('Income page name: ' + text);
      expect(text).to.not.be.empty;
    });

    cy.visit('http://thunderexpensetracker.com:8002/report');
    cy.get('.client-name').invoke('text').then((text) => {
      cy.log('Report page name: ' + text);
      expect(text).to.not.be.empty;
    });

    cy.visit('http://thunderexpensetracker.com:8002/calendar');
    cy.get('.client-name').invoke('text').then((text) => {
      cy.log('Calendar page name: ' + text);
      expect(text).to.not.be.empty;
    });
  });
});
