import {setSearchToggles, toggleGaActionAttributes} from './setupGaActions';
import {updateNavMenuTabIndex} from './accessibleNavMenu';

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
 * Function to handle clicking on a navigation element.
 *
 * @param {HTMLElement} element The element that has been clicked.
 */
export const toggleNavElement = element => {
  const target = element.dataset.bsTarget;
  const wasExpanded = element.getAttribute('aria-expanded') === 'true';

  if (!target) {
    throw new Error('Missing `data-bs-target` attribute: specify the container to be toggled');
  }

  const toggleClass = element.dataset.bsToggle;
  if (!toggleClass) {
    throw new Error('Missing `data-bs-toggle` attribute: specify the class to toggle');
  }

  // Toggle visibility of the target specified via data-bs-target.
  const targetElement = document.querySelector(target);
  targetElement.classList.toggle(toggleClass);
  element.classList.toggle(toggleClass);

  // Toggle aria-expanded attribute
  element.setAttribute('aria-expanded', wasExpanded ? 'false' : 'true');

  // Propagate attributes to all search toggles
  if (element.classList.contains('nav-search-toggle')) {
    setSearchToggles(!wasExpanded);
  }

  // We need to focus the search input when showing it
  const searchInput = document.querySelector('#search_input');
  if (element.classList.contains('nav-search-toggle')) {
    if (wasExpanded) {
      searchInput.focus();
    }
  }

  // Lock scroll when navigation menu is open
  if (element.classList.contains(NAV_MENU_TOGGLE_CLASS.substring(1))) {
    const htmlElement = document.getElementsByTagName('html')[0];
    htmlElement.style.overflowY = wasExpanded ? 'auto' : 'hidden';
  }

  // Update tab index for keyboard navigation depending on burger menu being open or not.
  updateNavMenuTabIndex();

  // Toggle data-ga-action attribute used in GTM tracking.
  toggleGaActionAttributes();
};
