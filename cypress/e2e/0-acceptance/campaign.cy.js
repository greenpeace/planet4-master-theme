
describe('check campaign basics', () => {
  it('can create a new campaign', () => {
    cy.login('admin', 'admin', {})

    // Create campaign
    cy.visit('/wp-admin/post-new.php?post_type=campaign')
    cy.discardWelcomeGuide()

    cy.get('h1[aria-label="Add title"]').click()
    cy.focused().type('Test campaign title')

    cy.get('p[aria-label="Add default block"]').click()
    cy.focused().type('Test campaign paragraph')

    cy.contains('button', 'Publish').click()
    cy.contains('.editor-post-publish-panel button', 'Publish').click()

    cy.get('.components-snackbar')
      .should('be.visible')
      .should('contain', 'Page published')

    // Check publication
    cy.contains('.components-snackbar a', 'View Campaign').click()

    cy.get('body')
      .should('have.class', 'single-campaign')
      .should('have.attr', 'data-post-type', 'campaign')

    cy.get('h1.page-header-title')
      .should('be.visible')
      .should('have.text', 'Test campaign title')
    cy.get('div.page-content p')
      .should('be.visible')
      .should('have.text', 'Test campaign paragraph')
  })
})
