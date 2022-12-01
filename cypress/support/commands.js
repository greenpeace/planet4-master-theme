// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })


Cypress.Commands.add('login', (username, password, { cacheSession = true }) => {
  const login = () => {
    cy.visit('/wp-login.php')

    cy.get('input#user_login').click()
    cy.wait(500)

    cy.focused().type(username)
    cy.get('input#user_pass').click()
    cy.focused().type(password + '{enter}')

    cy.url().should('contain', '/wp-admin/')
  }

  if (cacheSession) {
    cy.session(username, login)
  } else {
    login()
  }
})

Cypress.Commands.add('discardWelcomeGuide', () => {
    cy.get('#editor > div').then(() => {
      cy.window().then((window) => {
        const isWelcomeGuideActive = window.wp.data
            .select('core/edit-post')
            .isFeatureActive('welcomeGuide');
        if (isWelcomeGuideActive) {
            window.wp.data.dispatch('core/edit-post').toggleFeature('welcomeGuide');
            cy.reload();
        }
      })
    });
})

Cypress.Commands.add('logout', () => {
  cy.clearCookies()
  cy.clearLocalStorage()
})

Cypress.Commands.add('newPost', (postType) => {
    cy.visit(`/wp-admin/post-new.php?post_type=${postType}`)
})

Cypress.Commands.add('seedPost', (postType, data) => {
  cy.clearCookies()
  cy.login('admin', 'admin', {})
  cy.visit('/wp-admin/')

  let route
  switch (postType) {
    case 'campaign':
      route = `/wp-json/wp/v2/${postType}`
      break;
    default:
      route = `/wp-json/wp/v2/${postType}s`
  }

  let nonce
  cy.window().then((win) => {
    nonce = win.wpApiSettings.nonce
  }).then(() => {
    cy.request({
      method: 'POST',
      url: route,
      headers: {
        //'Authorization': `Basic ${btoa('admin:admin')}`
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce
      },
      body: {
        ...data
      }
    }).then((resp) => {
       expect(resp.status).to.eq(201);
       return resp;
    })
  })
})
