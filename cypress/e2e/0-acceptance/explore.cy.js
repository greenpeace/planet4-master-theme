describe('check Explore page', () => {
  it('has Explore page', () => {
    cy.visit('/explore')
    cy.get('h1').should('have.text', 'Justice for people and planet')
    cy.get('.split-two-column').first().scrollIntoView()

    cy.get('.split-two-column').first().within(() => {
      cy.contains('a', 'Energy').should('be.visible')
      cy.contains('.split-two-column-item-tag', '#renewables').should('be.visible')
    })
  })
})
