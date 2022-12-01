
describe('check cookie banner renders', () => {
  it('render cookie banner', () => {
    cy.clearCookies()
    cy.visit('/')

    cy.get('#set-cookie').should('be.visible')

    cy.contains('#set-cookie button', 'Accept all cookies').click()
    cy.get('#set-cookie').should('not.be.visible')
  })
})
