import {toggleGaActionAttribute} from './setupGaAction';
import lockScrollWhenNavMenuOpen from './lockScrollWhenNavMenuOpen';
import {updateSearchToggleAttributes} from './setupNavSearch';

/**
 * Close inactive nav elements when clicking on document body.
 *
 * @param {event} event Click event.
 */
export const closeInactiveNavElements = event => {
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

/**
 * Setup nav elements toggle behaviour.
 *
 * @param {HTMLElement} element Element that was toggled.
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

  // Update attributes if element is the search toggle.
  updateSearchToggleAttributes(element, wasExpanded);

  // Lock scroll when navigation menu is open
  lockScrollWhenNavMenuOpen(element, wasExpanded);

  // Toggle data-ga-action attribute used in GTM tracking.
  toggleGaActionAttribute();
};
