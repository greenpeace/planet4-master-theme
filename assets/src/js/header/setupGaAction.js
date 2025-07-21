/**
 * Update data-ga-action attribute used in GTM tracking.
 *
 * @param {HTMLElement} element     Element that was toggled.
 * @param {string}      elementName Name of the element to be used in the attribute value.
 */
export const updateGaAction = (element, elementName) => {
  element.dataset.gaAction = `${element.getAttribute('aria-expanded') === 'false' ? 'Open' : 'Close'} ${elementName}`;
};

/**
 * Update data-ga-action attribute used in GTM tracking for various elements.
 */
export const toggleGaActionAttribute = () => {
  const countryDropdownToggle = document.querySelector('.country-dropdown-toggle');
  const countrySelectorToggle = document.querySelector('.country-selector-toggle');
  const navMenuToggle = document.querySelector('.nav-menu-toggle');

  if (!countryDropdownToggle && !countrySelectorToggle && !navMenuToggle) {
    return;
  }

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
