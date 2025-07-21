/* global hj */

import {updateGaAction} from './setupGaAction';

/**
 * Update search attributes based on user interactions (click, keyboard, etc).
 *
 * @param {HTMLElement} element     Element that was toggled.
 * @param {boolean}     wasExpanded If toggle was expanded.
 */
export const updateSearchToggleAttributes = (element, wasExpanded) => {
  if (!element.classList.contains('nav-search-toggle')) {
    return;
  }

  // Propagate attributes to all search toggles.
  const toggles = document.querySelectorAll('.nav-search-toggle');
  toggles.forEach(toggle => {
    toggle.setAttribute('aria-expanded', !wasExpanded ? 'true' : 'false');
    updateGaAction(toggle, 'search');
    toggle.classList.toggle('open', !wasExpanded);
  });

  // We need to focus the search input when showing it.
  const searchInput = document.querySelector('#search_input');
  if (searchInput && wasExpanded) {
    searchInput.focus();
  }
};

/**
 * Send event to Hotjar on search input focus.
 */
export const sendHotjarEventOnSearchFocus = () => {
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
};
