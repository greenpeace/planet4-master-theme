/**
 * Class to handle keyboard accessibility in the navigation menu.
 */
class AccessibleNavMenu {
  static EVENT = {
    dom: 'DOMContentLoaded',
    keydown: 'keydown',
    focus: 'focus',
    focusout: 'focusout',
  };
  static EVENT_KEY = {
    enter: 'Enter',
    tab: 'Tab',
  };
  static DISPLAY = {
    flex: 'flex',
    none: 'none',
  };
  static CLASSNAME = {
    rotate: 'rotate',
    children: 'menu-item-has-children',
  };
  static SELECTORS = {
    accessible: '.accessible-nav-link',
    submenu: '.nav-submenu',
    submenu_link: '.nav-submenu a',
    nav_item: '.nav-item',
    last_link: 'a:last-of-type',
  };

  /**
   * Initializes the event listeners.
   */
  constructor() {
    document.addEventListener(AccessibleNavMenu.EVENT.dom, () => {
      this.handleNavMenuItem();
      this.handleNavMenuSub();
      this.handleNavMenuSubItems();
    });
  }

  /**
   * Adds event listeners to handle submenu toggle.
   */
  handleNavMenuItem() {
    document.querySelectorAll(AccessibleNavMenu.SELECTORS.accessible).forEach(button => {
      button.addEventListener(AccessibleNavMenu.EVENT.keydown, event => {
        if (event.key === AccessibleNavMenu.EVENT_KEY.enter) {
          const submenu = button.closest(AccessibleNavMenu.SELECTORS.nav_item).querySelector(AccessibleNavMenu.SELECTORS.submenu);
          if (submenu) {
            const display = window.getComputedStyle(submenu).display;
            if (display === AccessibleNavMenu.DISPLAY.none || display === '') {
              submenu.style.display = AccessibleNavMenu.DISPLAY.flex;
              button.classList.add(AccessibleNavMenu.CLASSNAME.rotate);
            } else {
              submenu.style.display = AccessibleNavMenu.DISPLAY.none;
              button.classList.remove(AccessibleNavMenu.CLASSNAME.rotate);
            }
          }
        }
      });
      button.addEventListener(AccessibleNavMenu.EVENT.focusout, event => {
        if (event.relatedTarget.parentElement.classList.contains(AccessibleNavMenu.CLASSNAME.children)) {
          button.classList.remove(AccessibleNavMenu.CLASSNAME.rotate);
          const submenu = button.closest(AccessibleNavMenu.SELECTORS.nav_item).querySelector(AccessibleNavMenu.SELECTORS.submenu);
          if (submenu) {
            submenu.style.display = AccessibleNavMenu.DISPLAY.none;
          }
        }
      });
    });
  }

  /**
   * Adds event listeners to hide submenu when focus leaves it, unless it moves to the associated toggle button.
   */
  handleNavMenuSub() {
    document.querySelectorAll(AccessibleNavMenu.SELECTORS.submenu).forEach(submenu => {
      submenu.addEventListener(AccessibleNavMenu.EVENT.focusout, event => {
        const button = submenu.closest(AccessibleNavMenu.SELECTORS.nav_item).querySelector(AccessibleNavMenu.SELECTORS.accessible);
        const newFocusElement = event.relatedTarget; // The next focused element

        // Only hide if focus is completely outside submenu and not back to .accessible-nav-link
        if (!submenu.contains(newFocusElement) && newFocusElement !== button) {
          submenu.style.display = AccessibleNavMenu.DISPLAY.none;
          if (button) {
            button.classList.remove(AccessibleNavMenu.CLASSNAME.rotate);
          }
        }
      });
    });
  }

  /**
   * Adds event listeners to handle submenu keyboard navigation.
   */
  handleNavMenuSubItems() {
    document.querySelectorAll(AccessibleNavMenu.SELECTORS.submenu_link).forEach(submenuLink => {
      submenuLink.addEventListener(AccessibleNavMenu.EVENT.focus, event => {
        const submenu = submenuLink.closest(AccessibleNavMenu.SELECTORS.submenu);
        const nextLink = submenuLink.nextElementSibling;
        const lastLink = submenu.querySelector(AccessibleNavMenu.SELECTORS.last_link);

        if (nextLink && event.key === AccessibleNavMenu.EVENT_KEY.tab && !event.shiftKey) {
          nextLink.focus();
        } else if (submenuLink === lastLink && !event.shiftKey) {
          submenu.querySelector(AccessibleNavMenu.SELECTORS.accessible)?.focus();
        }
      });
    });
  }
}

// Initialize the accessible nav menu handler
new AccessibleNavMenu();
