describe('Footer', () => {
  it('check social media links', () => {
    cy.visit('/act')
    cy.get('.site-footer').scrollIntoView()

    cy.get('.site-footer--minimal').should('not.exist')

    cy.get('.footer-social-media').should('be.visible')
    cy.contains('a', 'Facebook').should('be.visible')
    cy.contains('a', 'Twitter').should('be.visible')
    cy.contains('a', 'YouTube').should('be.visible')
    cy.contains('a', 'Instagram').should('be.visible')

    cy.get('.site-footer .footer-menu').should('be.visible')
    cy.contains('a', 'News')
      .should('have.attr', 'href', 'https://www-dev.greenpeace.org/defaultcontent/?s=&orderby=relevant&f%5Bctype%5D%5BPost%5D=3')
    cy.contains('a', 'Jobs')
      .should('have.attr', 'href', 'https://www.linkedin.com/jobs/greenpeace-jobs/')
    cy.contains('a', 'Press Center')
      .should('have.attr', 'href')
      .then(href => { expect(href.endsWith('/press-center/')).to.be.true; });
    cy.contains('a', 'Sitemap')
      .should('have.attr', 'href')
      .then(href => { expect(href.endsWith('/sitemap/')).to.be.true; })
    cy.contains('a', 'Privacy and Cookies')
      .should('have.attr', 'href')
      .then(href => { expect(href.endsWith('/privacy-and-cookies/')).to.be.true; })
    cy.contains('a', 'Community Policy')
      .should('have.attr', 'href')
      .then(href => { expect(href.endsWith('/community-policy/')).to.be.true; })
    cy.contains('a', 'Copyright')
      .should('have.attr', 'href')
      .then(href => { expect(href.endsWith('/copyright/')).to.be.true })
    cy.contains('a', 'Search the Archive')
      .should('have.attr', 'href', 'http://www.greenpeace.org/international/en/System-templates/Search-results/?adv=true')
  })

  it('check footer for campaigns', () => {
    const slug = Cypress._.uniqueId('campaign_');

    cy.seedPost('campaign', {
      title: slug,
      content: "<!-- wp:paragraph --><p>Campaign footer test</p><!-- /wp:paragraph -->",
      status: 'publish',
      "meta": {
        "theme": 'plastic-new',
        "campaign_footer_theme": 'default',
        "campaign_logo": 'greenpeace',
        "campaign_nav_color": '#ff513c',
        "campaign_nav_type": 'planet4',
      }
    }).then((resp) => {
      expect(resp).to.have.property('body')
      cy.visit(resp.body.link);

      cy.get('.site-footer').scrollIntoView()
      cy.get('.footer-social-media').should('be.visible')
      cy.contains('a', 'Facebook').should('be.visible')
      cy.contains('a', 'Twitter').should('be.visible')
      cy.contains('a', 'YouTube').should('be.visible')
      cy.contains('a', 'Instagram').should('be.visible')
    });

  })

  it('check footer for campaigns, minimal version', () => {
    const slug = Cypress._.uniqueId('campaign_');

    cy.seedPost('campaign', {
      title: slug,
      content: "<!-- wp:paragraph --><p>Campaign minimal footer test</p><!-- /wp:paragraph -->",
      status: 'publish',
      "meta": {
        "theme": 'climate-new',
        "campaign_footer_theme": 'white',
        "campaign_logo": 'greenpeace',
        "campaign_nav_type": 'minimal',
        "footer_menu_color": '#007eff',
      }
    }).then((resp) => {
      expect(resp).to.have.property('body')
      cy.visit(resp.body.link);

      cy.get('.site-footer--minimal').scrollIntoView()
      cy.get('.site-footer--minimal .footer-menu').should('not.exist')
      cy.get('.site-footer--minimal .footer-social-media').should('be.visible')
    });

  })
})
