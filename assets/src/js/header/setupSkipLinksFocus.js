/**
 * Move the focused element on click/tap.
 *
 * @param {string} trigger The link
 * @param {string} target  The destination
 */
function moveFocusOnActivate(trigger, target) {
  const link = document.querySelector(trigger);
  const destination = document.querySelector(target);

  if (!link || !destination) {return;}

  link.addEventListener('click', () => {
    destination.setAttribute('tabindex', '-1');
    destination.focus();
  });
};

/**
 * Set up changing the focused element when a Skip Link is clicked.
 */
export const setupSkipLinksFocus = () => {
  const skipLinksContainer = '.skip-links';
  const globalSkipLinks = ['#header', '#footer', '#content'];
  const searchSkipLink = '#search-results';

  globalSkipLinks.forEach(link => {
    moveFocusOnActivate(`${skipLinksContainer} a[href="${link}"]`, link);
  });

  if (document.querySelector('body.search-results')) {
    moveFocusOnActivate(`.skip-link[href="${searchSkipLink}"]`, searchSkipLink);
  }
};
