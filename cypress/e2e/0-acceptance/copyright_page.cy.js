
describe('check About Us page', () => {
  it('has a title', () => {
    cy.visit('/copyright')

    cy.get('h1').should('have.text', 'Copyright')
  })
});
