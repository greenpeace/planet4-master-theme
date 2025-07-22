// Reusable classes.
const ACCESSIBLE_NAV_LINK_CLASS = '.accessible-nav-link';
const NAV_ITEM_CLASS = '.nav-item';
const NAV_DONATE_CLASS = '.nav-donate';
const NAV_SUBMENU_CLASS = '.nav-submenu';
const SITE_LOGO_CLASS = '.site-logo';
const NAV_MENU_CLOSE_CLASS = '.nav-menu-close';

/**
 * Function to handle keyboard accessibility in the navigation menu.
 */
export default () => {
  const mainNav = document.querySelector('#nav-main-desktop');
  const mobileNav = document.querySelector('#nav-main');

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
    /**
     * Adds event listeners to create a keyboard trap between the buttons.
     */
    const addKeyboardTrap = () => {
      const donateBtn = mobileNav.querySelector('.btn-donate');
      const closeBtn = mobileNav.querySelector(NAV_MENU_CLOSE_CLASS);
      const logo = mobileNav.querySelector(SITE_LOGO_CLASS);

      closeBtn.addEventListener('keydown', event => {
        if (event.key === 'Tab' && event.shiftKey) {
          event.preventDefault();
          setTimeout(() => donateBtn.focus(), 5);
        }
        if (event.key === 'Tab') {
          setTimeout(() => logo.focus(), 0);
        }
      });
      logo.addEventListener('keydown', event => {
        if (event.key === 'Tab' && event.shiftKey) {
          setTimeout(() => closeBtn.focus(), 0);
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

      hamburgerBtn.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
          setTimeout(() => logo.focus(), 0);
        }
      });
    };

    /**
     * Adds event listeners to focus the logo when the menu is closed.
     */
    const focusLogoOnMenuClose = () => {
      const closeBtn = mobileNav.querySelector(NAV_MENU_CLOSE_CLASS);
      const hamburgerLogo = mobileNav.querySelector(SITE_LOGO_CLASS);
      const mainLogo = document.querySelector(`#header ${SITE_LOGO_CLASS}`);

      if (!mainLogo || !hamburgerLogo || !closeBtn) {
        return;
      }

      closeBtn.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
          setTimeout(() => mainLogo.focus(), 0);
        }
        if (event.key === 'Tab' && event.shiftKey) {
          setTimeout(() => hamburgerLogo.focus(), 0);
        }
      });
    };

    addKeyboardTrap();
    focusLogoOnMenuOpen();
    focusLogoOnMenuClose();
  }
};
