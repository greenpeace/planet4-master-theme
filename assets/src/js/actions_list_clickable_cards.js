/**
 * Enhance the tabbing functionality of the Actions list block by making the elements
 * in each card accessible through tabbing.
 *
 * Also ensures some elements don't have a duplicate focus.
 *
 * @function setupClickabelActionsListElements
 */

export const setupClickabelActionsListElements = () => {
  const liElements = document.querySelectorAll('.actions-list ul li:not(.carousel-li)');

  liElements.forEach(li => {
    const titleLink = li.querySelector('.wp-block-post-title > a');

    if (!titleLink) {
      return;
    }

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
    const NO_KEYBOARD_ACCESS_ELEMENTS = [titleLink, imageLink, tagLink, li];

    NO_KEYBOARD_ACCESS_ELEMENTS.forEach(element => element?.setAttribute('tabindex', -1));
  });
};
