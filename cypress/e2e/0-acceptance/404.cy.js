
describe('check the 404 page looks nice', () => {
  it('send http status code 404', () => {
    cy.request({
      method: 'GET',
      url: '/thispagereallywillnotexist',
      failOnStatusCode: false
    })
      .should((response) => {
        expect(response.status).to.eq(404)
      })
  })

  it('use 404 page options', () => {
    cy.visit('/thispagereallywillnotexist', {
      failOnStatusCode: false
    })

    // get 404_page_text wp option
    // cy.get('body').contains('')
    // get 404_page_bg_image wp option
    // cy.get('img[src=""]')
    cy.get('input[placeholder="Search"]')
  })
});
