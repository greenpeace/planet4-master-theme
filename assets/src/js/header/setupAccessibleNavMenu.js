// Reusable classes.
const ACCESSIBLE_NAV_LINK_CLASS = '.accessible-nav-link';
const NAV_ITEM_CLASS = '.nav-item';
const NAV_DONATE_CLASS = '.nav-donate';
const NAV_SUBMENU_CLASS = '.nav-submenu';
const SITE_LOGO_CLASS = '.site-logo';
const NAV_MENU_CLOSE_CLASS = '.nav-menu-close';
const MOBILE_NAV_ID = '#nav-main';
const PAGE_WRAPPER_ID = '#content';
const NAV_MENU_TOGGLE_CLASS = '.nav-menu-toggle';

/**
 * Function to handle keyboard accessibility in the navigation menu.
 */
export const setupAccessibleNavMenu = () => {
  const mainNav = document.querySelector('#nav-main-desktop');
  const mobileNav = document.querySelector(MOBILE_NAV_ID);

  if (!mainNav && !mobileNav) {
    return;
  }

  if (mainNav) {
    /**
     * Adds event listeners to handle submenu toggle on mouse hover.
     */
    const handleNavMenuItem = () => mainNav.querySelectorAll(`${NAV_ITEM_CLASS}, ${NAV_DONATE_CLASS}`).forEach(button => {
      button.addEventListener('mouseenter', () => {
        const submenu = button.querySelector(NAV_SUBMENU_CLASS);
        if (submenu) {
          submenu.style.display = 'flex';
        }

        const accessibleLink = button.querySelector(ACCESSIBLE_NAV_LINK_CLASS);
        if (accessibleLink) {
          accessibleLink.classList.add('rotate');
        }
      });
      button.addEventListener('mouseleave', () => {
        const submenu = button.querySelector(NAV_SUBMENU_CLASS);
        if (submenu) {
          submenu.style.display = 'none';
        }

        const accessibleLink = button.querySelector(ACCESSIBLE_NAV_LINK_CLASS);
        if (accessibleLink) {
          accessibleLink.classList.remove('rotate');
        }
      });
    });

    /**
     * Adds event listeners to handle submenu toggle.
     */
    const handleAccessibleNavLink = () => mainNav.querySelectorAll(ACCESSIBLE_NAV_LINK_CLASS).forEach(button => {
      button.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
          const submenu = button.closest(`${NAV_ITEM_CLASS}, ${NAV_DONATE_CLASS}`)?.querySelector(NAV_SUBMENU_CLASS);
          if (submenu) {
            const display = window.getComputedStyle(submenu).display;
            if (display === 'none' || display === '') {
              submenu.style.display = 'flex';
              button.classList.add('rotate');
            } else {
              submenu.style.display = 'none';
              button.classList.remove('rotate');
            }
          }
        }
      });
      button.addEventListener('focusout', event => {
        const parentIsDonateBtn = event.relatedTarget?.parentElement?.classList?.contains(NAV_DONATE_CLASS.substring(1));
        const parentHasChildren = event.relatedTarget?.parentElement?.classList?.contains('menu-item-has-children');

        if (parentIsDonateBtn || parentHasChildren) {
          button.classList.remove('rotate');
          const submenu = button.closest(`${NAV_ITEM_CLASS}, ${NAV_DONATE_CLASS}`).querySelector(NAV_SUBMENU_CLASS);
          if (submenu) {
            submenu.style.display = 'none';
          }
        }
      });
    });

    /**
     * Adds event listeners to hide submenu when focus leaves it, unless it moves to the associated toggle button.
     */
    const handleNavMenuSub = () => mainNav.querySelectorAll(NAV_SUBMENU_CLASS).forEach(submenu => {
      submenu.addEventListener('focusout', event => {
        const button = submenu.closest(`${NAV_ITEM_CLASS}, ${NAV_DONATE_CLASS}`)?.querySelector(ACCESSIBLE_NAV_LINK_CLASS);
        const newFocusElement = event.relatedTarget; // The next focused element

        // Only hide if focus is completely outside submenu and not back to .accessible-nav-link
        if (!submenu.contains(newFocusElement) && newFocusElement !== button) {
          submenu.style.display = 'none';
          if (button) {
            button.classList.remove('rotate');
          }
        }
      });
    });

    /**
     * Adds event listeners to handle submenu keyboard navigation.
     */
    const handleNavMenuSubItems = () => mainNav.querySelectorAll(`${NAV_SUBMENU_CLASS} a`).forEach(submenuLink => {
      submenuLink.addEventListener('focus', event => {
        const submenu = submenuLink.closest(NAV_SUBMENU_CLASS);
        const nextLink = submenuLink.nextElementSibling;
        const lastLink = submenu.querySelector('a:last-of-type');

        if (nextLink && event.key === 'Tab' && !event.shiftKey) {
          nextLink.focus();
        } else if (submenuLink === lastLink && !event.shiftKey) {
          submenu.querySelector(ACCESSIBLE_NAV_LINK_CLASS)?.focus();
        }
      });
    });

    handleNavMenuItem();
    handleAccessibleNavLink();
    handleNavMenuSub();
    handleNavMenuSubItems();
  }

  if (mobileNav) {
    const doc = mobileNav.ownerDocument;
    let lastFocusedElement = null;
    const isMobileMenuOpen = () => mobileNav.classList.contains('open');
    /**
     * Adds event listeners to create a keyboard trap between the buttons.
     */
    const addKeyboardTrap = () => {
      const focusableSelectors =
    'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

      const focusableElements = Array.from(
        mobileNav.querySelectorAll(focusableSelectors)
      );

      if (!focusableElements.length) {
        return;
      }

      const firstEl = focusableElements[0];
      const lastEl = focusableElements[focusableElements.length - 1];

      mobileNav.addEventListener('keydown', event => {
        if (event.key !== 'Tab') {
          return;
        }

        if (event.shiftKey && doc.activeElement === firstEl) {
          event.preventDefault();
          lastEl.focus({preventScroll: true});
        }

        if (!event.shiftKey && doc.activeElement === lastEl) {
          event.preventDefault();
          firstEl.focus({preventScroll: true});
        }
      });
    };

    /**
     * Adds event listeners to focus the logo when the menu is opened.
     */
    const focusLogoOnMenuOpen = () => {
      const hamburgerBtn = document.querySelector('.nav-menu-toggle');
      const logo = mobileNav.querySelector(SITE_LOGO_CLASS);

      if (!hamburgerBtn || !logo) {
        return;
      }

      hamburgerBtn.addEventListener('click', () => {
        lastFocusedElement = doc.activeElement;

        // Wait for CSS class to apply
        requestAnimationFrame(() => {
          if (!isMobileMenuOpen()) {
            return;
          }

          syncMobileNavAria(true);
          logo.focus();
        });
      });
    };

    /**
     * Adds event listeners to focus the logo when the menu is closed.
     */
    const focusLogoOnMenuClose = () => {
      const closeBtn = mobileNav.querySelector(NAV_MENU_CLOSE_CLASS);
      const toggleBtn = document.querySelector(NAV_MENU_TOGGLE_CLASS);

      if (!closeBtn || !toggleBtn) {
        return;
      }

      closeBtn.addEventListener('click', () => {
        const page = document.querySelector(PAGE_WRAPPER_ID);
        page.removeAttribute('aria-hidden');
        page.inert = false;


        requestAnimationFrame(() => {
          (lastFocusedElement || toggleBtn).focus();
          mobileNav.setAttribute('aria-hidden', 'true');
          toggleBtn.setAttribute('aria-expanded', 'false');
        });
      });

    };

    addKeyboardTrap();
    focusLogoOnMenuOpen();
    focusLogoOnMenuClose();
  }
};

/**
 * Function to update tab index for keyboard navigation depending on burger menu being open or not.
 */
export const updateNavMenuTabIndex = () => {
  const menu = document.querySelector('.burger-menu');
  if (!menu) {
    return;
  }

  const tabbingItems = [
    menu.querySelector(SITE_LOGO_CLASS),
    menu.querySelector('.btn-donate'),
    menu.querySelector(NAV_MENU_CLOSE_CLASS),
    ...menu.querySelectorAll('.nav-link'),
    ...menu.querySelectorAll('.collapsable-btn'),
  ];
  tabbingItems.forEach(item => item.setAttribute('tabindex', menu.classList.contains('open') ? 0 : -1));
};

/**
 * Function to update aria attributes for mobile navigation.
 * @param {boolean} isOpen - Whether the mobile navigation is open.
 */
const syncMobileNavAria = isOpen => {
  const mobileNav = document.querySelector(MOBILE_NAV_ID);
  const page = document.querySelector(PAGE_WRAPPER_ID);
  const toggleBtn = document.querySelector(NAV_MENU_TOGGLE_CLASS);

  if (!mobileNav || !page || !toggleBtn) {
    return;
  }

  // Mobile menu
  if (isOpen) {
    mobileNav.removeAttribute('aria-hidden');
  } else {
    mobileNav.setAttribute('aria-hidden', 'true');
  }

  // Page behind
  if (isOpen) {
    page.setAttribute('aria-hidden', 'true');
    page.inert = true;
  } else {
    page.removeAttribute('aria-hidden');
    page.inert = false;
  }

  // Toggle button state
  toggleBtn.setAttribute('aria-expanded', String(isOpen));
};
