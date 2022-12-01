describe('check homepage', () => {
  it('has a country selector', () => {
    cy.visit('/')
    cy.get('.country-selector-toggle-container').scrollIntoView()
    cy.get('.country-selector-toggle').first().click()
    cy.get('.countries-list').should('be.visible')
  })
})
