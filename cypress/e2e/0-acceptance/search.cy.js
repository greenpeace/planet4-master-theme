describe('Search function', () => {
    it('show a search form', () => {
      // x-large size to show search input
      cy.viewport(1200, 768)
      cy.visit('/')

      cy.get('#search_form').click()
      cy.focused().type('climate{enter}')

      cy.contains('h1', 'for \'climate\'').should('be.visible')
      cy.contains('.search-result-item-headline', '#Climate').should('be.visible')
    })
})
