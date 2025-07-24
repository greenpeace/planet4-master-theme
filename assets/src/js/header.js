/* global hj */

import {setupAccessibleNavMenu, updateNavMenuTabIndex} from './header/accessibleNavMenu';
import setupMobileTabsMenuScroll from './header/setupMobileTabsMenuScroll';
import {setSearchToggles, toggleGaActionAttributes} from './header/setupGaActions';
import {setupCloseNavMenuButton, setupDocumentClick, lockScrollWhenNavMenuOpen} from './header/setupNavMenu';

const toggleNavElement = element => {
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
  lockScrollWhenNavMenuOpen(element, wasExpanded);

  // Update tab index for keyboard navigation depending on burger menu being open or not.
  updateNavMenuTabIndex();

  // Toggle data-ga-action attribute used in GTM tracking.
  toggleGaActionAttributes();
};

export const setupHeader = () => {
  const toggleElementClasses = [
    '.navbar-dropdown-toggle',
    '.nav-menu-toggle',
    '.country-dropdown-toggle',
    '.country-selector-toggle',
    '.nav-search-toggle',
  ];

  const toggleElements = [...document.querySelectorAll(toggleElementClasses.join(','))];

  toggleElements.forEach(toggleElement => {
    toggleElement.onclick = event => {
      event.preventDefault();
      event.stopPropagation();

      toggleNavElement(toggleElement);
    };
  });

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

  // Set the mobile tabs menu behavior on scroll
  setupMobileTabsMenuScroll();

  // Close navbar elements when clicking outside of menu.
  setupDocumentClick();

  // Spoof click on nav menu toggle when clicking on nav menu close button.
  setupCloseNavMenuButton();

  // Setup keyboard accessibility in the navigation menu.
  setupAccessibleNavMenu();
};

