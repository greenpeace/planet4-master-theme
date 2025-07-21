import {updateGaAction} from './setupGaAction';

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

/**
 * Focus search input when clicking on toggle.
 *
 * @param {boolean} wasExpanded If toggle was expanded.
 */
const focusSearchInput = wasExpanded => {
  const searchInput = document.querySelector('#search_input');
  if (searchInput && wasExpanded) {
    searchInput.focus();
  }
};

/**
 * Update search attributes based on user interactions (click, keyboard, etc).
 *
 * @param {HTMLElement} element     Element that was toggled.
 * @param {boolean}     wasExpanded If toggle was expanded.
 */
export default (element, wasExpanded) => {
  if (!element.classList.contains('nav-search-toggle')) {
    return;
  }
  // Propagate attributes to all search toggles.
  setSearchToggles(!wasExpanded);

  // We need to focus the search input when showing it.
  focusSearchInput(wasExpanded);
};
