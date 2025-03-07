/**
 * Class to handle keyboard accessibility in the navigation menu.
 */
class AccessibleNavMenu {
  /**
   * Initializes the event listeners.
   */
  constructor() {
    document.addEventListener('DOMContentLoaded', () => {
      const mainNav = document.querySelector('#nav-main-desktop');
      const mobileNav = document.querySelector('#nav-main');

      if (mainNav) {
        this.handleNavMenuItem(mainNav);
        this.handleAccessibleNavLink(mainNav);
        this.handleNavMenuSub(mainNav);
        this.handleNavMenuSubItems(mainNav);
      }
      if (mobileNav) {
        this.addKeyboardTrap(mobileNav);
        this.focusLogoOnMenuOpen(mobileNav);
        this.focusLogoOnMenuClose(mobileNav);
      }
    });
  }

  /**
   * Adds event listeners to handle submenu toggle on mouse hover.
   * @param {HTMLElement} mainNav The main nav selector.
   */
  handleNavMenuItem(mainNav) {
    mainNav.querySelectorAll('.nav-item, .nav-donate').forEach(button => {
      button.addEventListener('mouseenter', () => {
        const submenu = button.querySelector('.nav-submenu');
        if (submenu) {
          submenu.style.display = 'flex';
        }

        const accessibleLink = button.querySelector('.accessible-nav-link');
        if (accessibleLink) {
          accessibleLink.classList.add('rotate');
        }
      });
      button.addEventListener('mouseleave', () => {
        const submenu = button.querySelector('.nav-submenu');
        if (submenu) {
          submenu.style.display = 'none';
        }

        const accessibleLink = button.querySelector('.accessible-nav-link');
        if (accessibleLink) {
          accessibleLink.classList.remove('rotate');
        }
      });
    });
  }

  /**
   * Adds event listeners to handle submenu toggle.
   * @param {HTMLElement} mainNav The main nav selector.
   */
  handleAccessibleNavLink(mainNav) {
    mainNav.querySelectorAll('.accessible-nav-link').forEach(button => {
      button.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
          const submenu = button.closest('.nav-item, .nav-donate')?.querySelector('.nav-submenu');
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
        const parentIsDonateBtn = event.relatedTarget?.parentElement?.classList?.contains('nav-donate');
        const parentHasChildren = event.relatedTarget?.parentElement?.classList?.contains('menu-item-has-children');

        if (parentIsDonateBtn || parentHasChildren) {
          button.classList.remove('rotate');
          const submenu = button.closest('.nav-item, .nav-donate').querySelector('.nav-submenu');
          if (submenu) {
            submenu.style.display = 'none';
          }
        }
      });
    });
  }

  /**
   * Adds event listeners to hide submenu when focus leaves it, unless it moves to the associated toggle button.
   * @param {HTMLElement} mainNav The main nav selector.
   */
  handleNavMenuSub(mainNav) {
    mainNav.querySelectorAll('.nav-submenu').forEach(submenu => {
      submenu.addEventListener('focusout', event => {
        const button = submenu.closest('.nav-item, .nav-donate')?.querySelector('.accessible-nav-link');
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
  }

  /**
   * Adds event listeners to handle submenu keyboard navigation.
   * @param {HTMLElement} mainNav The main nav selector.
   */
  handleNavMenuSubItems(mainNav) {
    mainNav.querySelectorAll('.nav-submenu a').forEach(submenuLink => {
      submenuLink.addEventListener('focus', event => {
        const submenu = submenuLink.closest('.nav-submenu');
        const nextLink = submenuLink.nextElementSibling;
        const lastLink = submenu.querySelector('a:last-of-type');

        if (nextLink && event.key === 'Tab' && !event.shiftKey) {
          nextLink.focus();
        } else if (submenuLink === lastLink && !event.shiftKey) {
          submenu.querySelector('.accessible-nav-link')?.focus();
        }
      });
    });
  }

  /**
   * Adds event listeners to create a keyboard trap between the buttons.
   * @param {HTMLElement} mobileNav The mobile nav selector.
   */
  addKeyboardTrap(mobileNav) {
    const donateBtn = mobileNav.querySelector('.btn-donate');
    const closeBtn = mobileNav.querySelector('.nav-menu-close');
    const logo = mobileNav.querySelector('.site-logo');

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
  }

  /**
   * Adds event listeners to focus the logo when the menu is opened.
   * @param {HTMLElement} mobileNav The mobile nav selector.
   */
  focusLogoOnMenuOpen(mobileNav) {
    const hamburgerBtn = document.querySelector('.nav-menu-toggle');
    const logo = mobileNav.querySelector('.site-logo');

    if (!hamburgerBtn || !logo) {
      return;
    }

    hamburgerBtn.addEventListener('keydown', event => {
      if (event.key === 'Enter') {
        setTimeout(() => logo.focus(), 0);
      }
    });
  }

  /**
   * Adds event listeners to focus the logo when the menu is closed.
   * @param {HTMLElement} mobileNav The mobile nav selector.
   */
  focusLogoOnMenuClose(mobileNav) {
    const closeBtn = mobileNav.querySelector('.nav-menu-close');
    const hamburgerLogo = mobileNav.querySelector('.site-logo');
    const mainLogo = document.querySelector('#header .site-logo');

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
  }
}

// Initialize the accessible nav menu handler
new AccessibleNavMenu(); //NOSONAR
