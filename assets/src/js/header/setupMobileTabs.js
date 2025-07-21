/**
 * Set the mobile tabs menu behavior on scroll
 * Mobile tabs menu should hide when user scrolls down, and reappear when they scroll up
 */
export default () => {
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
  } catch (e) {
    // eslint-disable-next-line no-console
    console.log(e);
  }

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
