
describe('check Act page', () => {
  it('has action cards', () => {
    cy.visit('/act')

    cy.get('.covers-block').should('be.visible')
    cy.get('.cover-card').should('be.visible')

    //cy.contains('.cover-card-tag', 'Consumption').should('be.visible')
    cy.contains('.cover-card-tag', 'renewables').should('be.visible')
    cy.contains('.cover-card-tag', 'Climate').should('be.visible')
  })
});
