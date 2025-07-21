/**
 * Lock scroll when navigation menu is open.
 *
 * @param {HTMLElement} element     Element that was toggled.
 * @param {boolean}     wasExpanded If toggle was expanded.
 */
export default (element, wasExpanded) => {
  if (!element.classList.contains('nav-menu-toggle')) {
    return;
  }
  const htmlElement = document.getElementsByTagName('html')[0];
  htmlElement.style.overflowY = wasExpanded ? 'auto' : 'hidden';

  // Update tab index for keyboard navigation depending on burger menu being open or not.
  const burgerMenu = document.querySelector('.burger-menu');
  if (burgerMenu) {
    const tabbingItems = [
      burgerMenu.querySelector('.site-logo'),
      burgerMenu.querySelector('.btn-donate'),
      burgerMenu.querySelector('.nav-menu-close'),
      ...burgerMenu.querySelectorAll('.nav-link'),
      ...burgerMenu.querySelectorAll('.collapsable-btn'),
    ];
    tabbingItems.forEach(item => item.setAttribute('tabindex', burgerMenu.classList.contains('open') ? 0 : -1));
  }
};
