/* global hj */

import {setupAccessibleNavMenu} from './header/accessibleNavMenu';
import setupMobileTabsMenuScroll from './header/setupMobileTabsMenuScroll';
import {setupCloseNavMenuButton, setupDocumentClick, toggleNavElements} from './header/setupNavMenu';

export const setupHeader = () => {
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

  // Set the mobile tabs menu behavior on scroll.
  setupMobileTabsMenuScroll();

  // Close navbar elements when clicking outside of menu.
  setupDocumentClick();

  // Spoof click on nav menu toggle when clicking on nav menu close button.
  setupCloseNavMenuButton();

  // Setup keyboard accessibility in the navigation menu.
  setupAccessibleNavMenu();

  // Handle clicking on navigation elements.
  toggleNavElements();
};

