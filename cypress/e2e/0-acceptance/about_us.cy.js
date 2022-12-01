
describe('check About Us page', () => {
  it('has a title', () => {
    cy.visit('/about-us-2')

    cy.get('h1').should('have.text', 'Who we are')
  })
});
