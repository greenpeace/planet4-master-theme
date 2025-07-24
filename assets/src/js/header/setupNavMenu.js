const NAV_MENU_TOGGLE_CLASS = '.nav-menu-toggle';

/**
 * Spoof click on nav menu toggle when clicking on nav menu close button.
 */
export const setupCloseNavMenuButton = () => {
  const closeNavMenuButton = document.querySelector('.nav-menu-close');
  const closeButton = document.querySelector(NAV_MENU_TOGGLE_CLASS);

  if (!closeNavMenuButton || !closeButton) {
    return;
  }

  closeNavMenuButton.onclick = event => {
    event.preventDefault();
    closeButton.click();
  };
};

/**
 * Close navigation elements when clicking outside of it.
 *
 * @param {event} event Click event.
 */
const closeInactiveNavElements = event => {
  let searchToggled = false;
  const clickedElement = event.target;

  const activeElements = [...document.querySelectorAll('button[aria-expanded="true"]')];

  activeElements.forEach(button => {
    const buttonTarget = button.dataset && button.dataset.bsTarget;

    if (button.classList.contains('nav-search-toggle')) {
      if (searchToggled) {
        return;
      }
      searchToggled = true;
    }

    const buttonTargetElement = document.querySelector(buttonTarget);

    if (buttonTargetElement && !buttonTargetElement.contains(clickedElement)) {
      // Spoof a click on the open menu's toggle to close that menu.
      button.click();
    }
  });
};
export const setupDocumentClick = () => document.onclick = closeInactiveNavElements;

/**
 * Lock scroll when navigation menu is open.
 *
 * @param {HTMLElement} element     Element that was toggled.
 * @param {boolean}     wasExpanded If toggle was expanded.
 */
export const lockScrollWhenNavMenuOpen = (element, wasExpanded) => {
  if (!element.classList.contains(NAV_MENU_TOGGLE_CLASS.substring(1))) {
    return;
  }
  const htmlElement = document.getElementsByTagName('html')[0];
  htmlElement.style.overflowY = wasExpanded ? 'auto' : 'hidden';
};
