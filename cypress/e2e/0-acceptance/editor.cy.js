describe('check Editor basics', () => {

  beforeEach(() => {
    cy.login('admin', 'admin', {});
  })

  it('create a new post', () => {
    cy.visit('/wp-admin/post-new.php?post_type=post');
    cy.discardWelcomeGuide();

    cy.get('h1[aria-label="Add title"]').click({force: true})
    cy.focused().type('Test title')

    cy.get('p[aria-label="Add default block"]').click()
    cy.focused().type('Test paragraph')

    // Publish
    cy.contains('button', 'Publish').click()
    cy.contains('.editor-post-publish-panel button', 'Publish').click()

    cy.get('.components-snackbar')
      .should('be.visible')
      .should('contain', 'Post published')

    cy.contains('.components-snackbar a', 'View Post').click()

    cy.get('body')
      .should('have.class', 'single-post')
      .should('have.attr', 'data-post-type', 'post')

    cy.get('h1.page-header-title')
      .should('be.visible')
      .should('have.text', 'Test title')
    cy.get('div.post-content p')
      .should('be.visible')
      .should('have.text', 'Test paragraph')
  });

  it('add a youtube video in a post', () => {
    cy.visit('/wp-admin/post-new.php?post_type=post');
    cy.discardWelcomeGuide();

    cy.get('h1[aria-label="Add title"]').click()
    cy.focused().type('Test video')

    cy.get('p[aria-label="Add default block"]').click()
    cy.get('button[aria-label="Toggle block inserter"]').click()

    cy.get('.block-editor-inserter__menu').should('be.visible')
    cy.get('[class$="embed/youtube"]').click()

    cy.get('input[aria-label="YouTube URL"]').click()
    cy.focused().type('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    cy.get('input[aria-label="YouTube URL"] ~ button[type="submit"]').click()
    cy.wait(1000)

    // Publish
    cy.contains('button', 'Publish').click()
    cy.contains('.editor-post-publish-panel button', 'Publish').click()

    cy.get('.components-snackbar')
      .should('be.visible')
      .should('contain', 'Post published')

    cy.contains('.components-snackbar a', 'View Post').click()

    cy.get('h1.page-header-title')
      .should('be.visible')
      .should('have.text', 'Test video')
    cy.get('lite-youtube')
      .should('be.visible')
  });
});
