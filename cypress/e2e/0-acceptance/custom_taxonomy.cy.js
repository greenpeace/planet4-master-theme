
describe('check Custom taxonomy page', () => {
  it('has a Story page', () => {
    cy.visit('/story');

    cy.get('h1').should('have.text', 'Story');
    cy.contains('Nikos').click();
    cy.url().should('include', '/author/nroussos');
  })

  it('saves a post', () => {
    const slug = Cypress._.uniqueId('slug_');

    cy.seedPost('post', {
      title: slug,
      content: "<!-- wp:paragraph --><p>Test content</p><!-- /wp:paragraph -->",
      status: 'publish',
      categories: [4], //'people'
      "meta": {
        "p4-page-type": ['story'],
        "p4_author_override": 'FooBarAuthor',
      }
    }).then((resp) => {
      expect(resp).to.have.property('body')
      cy.visit('/story/' + resp.body.id);
      cy.contains('FooBarAuthor');
    });

  })
});
