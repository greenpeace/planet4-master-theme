describe('Add a comment', () => {
  it('can have comments', () => {

    let postId
    let commentId
    let commentRandId
    let newCommentRandId
    cy.seedPost('post', {
      title: 'Post for comments',
      content: '<!-- wp:paragraph --><p>Random content</p><!-- /wp:paragraph -->',
      status: 'publish',
      categories: [2], // Energy
      "meta": {
        "p4-page-type": ['press'],
      }
    }).then((resp) => {
      postId = resp.body.id
/*    }).then(() => {
      commentRandId = Cypress._.uniqueId('comment_');
      cy.seedPost('comment', {
        "author_email": 'testuser1@planet4.test',
        "author_name": 'test user 1',
        content: `test comment ${commentRandId}`,
        "post": postId,
      })
    }).then((resp) => {
      commentId = resp.body.id*/
    }).then(() => {
      cy.logout()
      cy.visit('/?p=' + postId)

      newCommentRandId = Cypress._.uniqueId('comment_');
      cy.get('#commentform').scrollIntoView()
      cy.get('#commentform').should('be.visible')
      cy.get('#commentform').within(($form) => {
        cy.get('textarea[name="comment"]').type(`test comment ${newCommentRandId}`)
        cy.get('input[name="author"]').type('test user')
        cy.get('input[name="email"]').type('testuser@planet4.test')
        cy.get('#gdpr-comments-label').click()
        cy.get('button[type="submit"]').should('be.enabled')
        cy.get('button[type="submit"]').click()
      })
    }).then(() => {
      cy.location('pathname').should('include', `/${postId}`)
      cy.location('search').should('include', '?unapproved');
    }).then(() => {
      cy.login('admin', 'admin', {})
      cy.visit('wp-admin/edit-comments.php')
      cy.contains('.comments', `test comment ${newCommentRandId}`)
    })
  })
});
