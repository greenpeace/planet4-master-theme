/* global hj */

import setupAccessibleNavMenu from './header/setupAccessibleNavMenu';
import setupMobileTabsMenuScroll from './header/setupMobileTabsMenuScroll';
import {setupCloseNavMenuButton, setupDocumentClick} from './header/setupCloseMenu';

const updateGaAction = (element, elementName) => {
  element.dataset.gaAction = `${element.getAttribute('aria-expanded') === 'false' ? 'Open' : 'Close'} ${elementName}`;
};

/**
 * Propagate attributes to all search toggles
 *
 * @param {boolean} expanded Toggle is expanded
 */
const setSearchToggles = expanded => {
  const toggles = document.querySelectorAll('.nav-search-toggle');
  toggles.forEach(toggle => {
    toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    updateGaAction(toggle, 'search');
    toggle.classList.toggle('open', expanded);
  });
};

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
  if (element.classList.contains('nav-menu-toggle')) {
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
  }

  // Toggle data-ga-action attribute used in GTM tracking.
  const countryDropdownToggle = document.querySelector('.country-dropdown-toggle');
  const countrySelectorToggle = document.querySelector('.country-selector-toggle');
  const navMenuToggle = document.querySelector('.nav-menu-toggle');

  if (countryDropdownToggle) {
    updateGaAction(countryDropdownToggle, 'Country Selector');
  }

  if (countrySelectorToggle) {
    updateGaAction(countrySelectorToggle, 'Country Selector');
  }

  if (navMenuToggle) {
    updateGaAction(navMenuToggle, 'Menu');
  }
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

