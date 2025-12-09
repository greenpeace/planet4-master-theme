const updateGaAction = (element, elementName) => {
  element.dataset.gaAction = `${element.getAttribute('aria-expanded') === 'false' ? 'Open' : 'Close'} ${elementName}`;
};

/**
 * Propagate attributes to all search toggles
 *
 * @param {boolean} expanded Toggle is expanded
 */
export const setSearchToggles = expanded => {
  const toggles = document.querySelectorAll('.nav-search-toggle');
  const searchInput = document.getElementById('search_input');

  toggles.forEach(toggle => {
    toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    updateGaAction(toggle, 'search');
    toggle.classList.toggle('open', expanded);

    if (expanded && searchInput) {
      searchInput.focus();
    }
  });
};

/**
 * Toggle data-ga-action attribute used in GTM tracking.
 */
export const toggleGaActionAttributes = () => {
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
