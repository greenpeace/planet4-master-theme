describe('check header', () => {
  it('has a header page', () => {
    cy.visit('/act')
    cy.get('.page-header').should('be.visible')
    cy.get('.page-header-title').should('be.visible')
  })
})
