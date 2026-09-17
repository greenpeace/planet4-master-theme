// Set up displaying the hamburger menu when the desktop menu is too large.

const dynamicHamburgerMenu = () => {
  if (window.innerWidth < 992) {
    return;
  }

  const header = document.querySelector('#header');
  const mainMobileNav = document.querySelector('#nav-main');

  if (!header || !mainMobileNav) {
    return;
  }

  const logo = header.querySelector('.site-logo');
  const mainDesktopNav = header.querySelector('#nav-main-desktop');
  const mainMobileNavBtn = header.querySelector('.nav-menu-toggle');
  const searchForm = header.querySelector('#search_form');

  if (!logo || !mainDesktopNav || !searchForm || !mainMobileNavBtn) {
    return;
  }

  const headerHeight = header.getBoundingClientRect().height;
  const logoHeight = logo.getBoundingClientRect().height;

  if (headerHeight <= logoHeight) {
    return;
  }

  mainDesktopNav.classList.remove('d-lg-flex');
  mainMobileNav.classList.remove('d-lg-none');
  mainMobileNavBtn.style.display = 'initial';
  logo.style.margin = '0';
};

export const setupDynamicHamburgerMenu = () => {
  let frame = null;

  const onResize = () => {
    if (frame) { return; }
    frame = requestAnimationFrame(() => {
      frame = null;
      dynamicHamburgerMenu();
    });
  };

  dynamicHamburgerMenu();
  window.addEventListener('resize', onResize);

  return () => {
    window.removeEventListener('resize', onResize);
    if (frame) { cancelAnimationFrame(frame); }
  };
};
