describe('check PHP warnings', () => {
  it('check PHP warnings on homepage', () => {
    cy.visit('/')
    cy.get('html:root').then(($el) => {
      expect($el).not.to.have.html('<b>Warning</b>:')
    })
  })
})
