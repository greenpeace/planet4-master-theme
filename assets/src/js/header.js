/* global hj */

import setupAccessibleNavMenu from './header/setupAccessibleNavMenu';
import setupMobileTabs from './header/setupMobileTabs';
import setupCloseNavButtons from './header/setupCloseNavButtons';
import {closeInactiveNavElements, toggleNavElement} from './header/navElementFunctions';

const TOGGLE_ELEMENT_CLASSES = [
  '.navbar-dropdown-toggle',
  '.nav-menu-toggle',
  '.country-dropdown-toggle',
  '.country-selector-toggle',
  '.nav-search-toggle',
];

/**
 * Setup header behaviour (focus, clicks, accessibility, etc).
 */
export const setupHeader = () => {
  const toggleElements = [...document.querySelectorAll(TOGGLE_ELEMENT_CLASSES.join(','))];

  toggleElements.forEach(toggleElement => {
    toggleElement.onclick = event => {
      event.preventDefault();
      event.stopPropagation();

      toggleNavElement(toggleElement);
    };
  });

  document.onclick = closeInactiveNavElements;

  // Close all menus on escape pressed
  document.onkeyup = event => {
    if (event.key === 'Escape') {
      document.body.click();
    }
  };

  let searchFocused = false;
  const searchInput = document.getElementById('search_input');
  if (searchInput) {
    searchInput.addEventListener('focus', () => {
      if (!searchFocused) {
        if (hj) {
          hj('event', 'search');
        }
        searchFocused = true;
      }
    });
  }

  // Make sure navigation is accessible.
  setupAccessibleNavMenu();

  // Set up mobile tabs scrolling behaviour.
  setupMobileTabs();

  // Close nav elements if the user clicks on the corresponding close buttons.
  setupCloseNavButtons();
};

