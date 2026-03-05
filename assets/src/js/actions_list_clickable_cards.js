/**
 * Update the tabbing functionality of the Actions list block by making only the
 * card and the take action button focusable.
 *
 * @function setupClickabelActionsListCards
 */

export const setupClickableActionsListCards = () => {
  // Add tracking clicks in GA4/Mixpanel
  const setupAnalytics = (element, action) => {
    if(element) {
      element.setAttribute('data-ga-category', 'Actions List');
      element.setAttribute('data-ga-action', action);
      element.setAttribute('data-ga-label', 'n/a');
    }
  };

  const liElements = document.querySelectorAll('.actions-list ul li:not(.carousel-li)');

  liElements.forEach(li => {
    setupAnalytics(li.querySelector('figure'), 'Image');
    setupAnalytics(li.querySelector('.btn-primary'), 'Call to Action');

    const titleLink = li.querySelector('.wp-block-post-title > a');

    if (!titleLink) {
      return;
    }

    setupAnalytics(titleLink, 'Title');

    // Add overlay to make whole card clickable
    const url = titleLink.getAttribute('href');
    const anchor = document.createElement('a');
    anchor.setAttribute('href', url);
    anchor.classList.add('actions-list-links');

    while (li.firstChild) {
      anchor.appendChild(li.firstChild);
    }

    li.appendChild(anchor);

    // Remove keyboard access for the image, tag, title, and the card itself.
    const imageLink = li.querySelector('.wp-block-post-featured-image > a');
    const tagLink = li.querySelector('.taxonomy-post_tag > a');
    const postTerms = li.querySelector('.wp-block-post-terms > a');
    const NO_KEYBOARD_ACCESS_ELEMENTS = [titleLink, imageLink, tagLink, li, postTerms];

    NO_KEYBOARD_ACCESS_ELEMENTS.forEach(element => element?.setAttribute('tabindex', -1));
  });
};
