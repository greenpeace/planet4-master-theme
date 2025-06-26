/* global hj */

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
  if (element.classList.contains('nav-search-toggle') || element.classList.contains('navbar-search-toggle')) {
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
  const navbarSearchToggle = document.querySelector('.navbar-search-toggle');
  const navMenuToggle = document.querySelector('.nav-menu-toggle');

  if (countryDropdownToggle) {
    updateGaAction(countryDropdownToggle, 'Country Selector');
  }

  if (countrySelectorToggle) {
    updateGaAction(countrySelectorToggle, 'Country Selector');
  }

  if (navbarSearchToggle) {
    updateGaAction(navbarSearchToggle, 'Search');
  }

  if (navMenuToggle) {
    updateGaAction(navMenuToggle, 'Menu');
  }
};

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

const closeElement = (event, buttonClass) => {
  event.preventDefault();
  const closeButton = document.querySelector(buttonClass);
  closeButton.click();
};

/**
 * Set the mobile tabs menu behavior on scroll
 * Mobile tabs menu should hide when user scrolls down, and reappear when they scroll up
 */
const setMobileTabsMenuScroll = () => {
  const menu = document.getElementById('nav-mobile');
  if (!menu) {
    return;
  }

  const distToClose = 100;
  const distToOpen = 50;
  let lastScrollDir = null;
  let lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
  let lastScrollRef = lastScrollTop;

  // Check support for eventlistener opts (passive option)
  // Cf. https://github.com/WICG/EventListenerOptions/blob/gh-pages/explainer.md#feature-detection
  let supportsPassive = false;
  try {
    const opts = Object.defineProperty({}, 'passive', {
      get: () => {
        supportsPassive = true;
        return supportsPassive;
      },
    });
    window.addEventListener('testPassive', null, opts);
    window.removeEventListener('testPassive', null, opts);
  } catch (e) {} // eslint-disable-line no-empty

  /**
   * Get scroll direction, distance from the lastScrollTop, and distance from a reference point
   *
   * @return {Object} {scroll direction, top position, distance scrolled, distance scrolled from ref point}
   */
  const scrollData = () => {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    if (scrollTop === lastScrollTop) {
      return {};
    }

    const dir = scrollTop > lastScrollTop ? 'down' : 'up';
    const dist = Math.abs(scrollTop - lastScrollTop);
    const ref = Math.abs(scrollTop - lastScrollRef);
    lastScrollTop = Math.max(0, scrollTop);
    return {dir, pos: scrollTop, dist, ref};
  };

  /**
   * Show/hide tabs menu depending on scroll direction and distance scrolled from ref point
   * Update reference point and reference direction only on state change
   */
  const toggleMobileTabsMenu = () => {
    const {dir, pos, ref} = scrollData();
    if (!dir || dir === lastScrollDir) {
      return;
    }

    // Keep open, avoid glitches when scrolling up at max top
    if (pos < distToOpen) {
      lastScrollDir = 'up';
      lastScrollRef = lastScrollTop;
      menu.classList.remove('mobile-menu-hidden');
      return;
    }

    const menuItems = menu.querySelectorAll('.nav-link');
    // Hide
    if (dir === 'down' && ref >= distToClose) {
      lastScrollDir = dir;
      lastScrollRef = lastScrollTop;
      menu.classList.add('mobile-menu-hidden');
      if (menuItems && menuItems.length > 0) {
        menuItems.forEach(item => item.setAttribute('tabindex', -1));
      }
      return;
    }

    // Show
    if (dir === 'up' && ref >= distToOpen) {
      lastScrollDir = dir;
      lastScrollRef = lastScrollTop;
      menu.classList.remove('mobile-menu-hidden');
      if (menuItems && menuItems.length > 0) {
        menuItems.forEach(item => item.setAttribute('tabindex', 0));
      }
    }
  };

  ['touchmove', 'scroll'].forEach(eventName => {
    document.addEventListener(
      eventName,
      toggleMobileTabsMenu,
      supportsPassive ? {passive: true} : false
    );
  });
};

export const setupHeader = () => {
  const toggleElementClasses = [
    '.navbar-dropdown-toggle',
    '.nav-menu-toggle',
    '.country-dropdown-toggle',
    '.country-selector-toggle',
    '.navbar-search-toggle',
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

  document.onclick = closeInactiveNavElements;

  // Close all menus on escape pressed
  document.onkeyup = event => {
    if (event.key === 'Escape') {
      document.body.click();
    }
  };

  // Close the elements if the user clicks on the corresponding close buttons
  const closeNavbarButton = document.querySelector('.close-navbar-dropdown');
  if (closeNavbarButton) {
    closeNavbarButton.onclick = event => closeElement(event, '.navbar-dropdown-toggle');
  }

  const closeNavMenuButton = document.querySelector('.nav-menu-close');
  if (closeNavMenuButton) {
    closeNavMenuButton.onclick = event => closeElement(event, '.nav-menu-toggle');
  }

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

  setMobileTabsMenuScroll();
};

