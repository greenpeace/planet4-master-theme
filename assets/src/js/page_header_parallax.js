export const setupPageHeaderParallax = () => {
  const pageHeaders = document.querySelectorAll('.is-pattern-p4-page-header');

  const isSmallWindow = () => window.innerWidth < 992;

  const addPageHeaderParallax = () => {
    pageHeaders.forEach(pageHeader => {
      const pageHeaderImage = pageHeader.querySelector('.wp-block-media-text__media > img');
      const pageHeaderRect = pageHeader.getBoundingClientRect();

      // 100 to take into account the navbar + a bit of extra spacing
      if (pageHeaderRect.top < 100) {
        pageHeaderImage.style.transform = `translateY(${(100 - pageHeaderRect.top) * 0.6}px)`;
      }
    });
  };

  const removePageHeaderParallax = () => {
    pageHeaders.forEach(pageHeader => {
      const pageHeaderImage = pageHeader.querySelector('.wp-block-media-text__media > img');
      pageHeaderImage.style.transform = 'none';
    });
  };

  if (!isSmallWindow()) {
    window.addEventListener('scroll', addPageHeaderParallax);
  }

  window.addEventListener('resize', () => {
    if (isSmallWindow()) {
      window.removeEventListener('scroll', addPageHeaderParallax);
      removePageHeaderParallax();
    } else {
      window.addEventListener('scroll', addPageHeaderParallax);
    }
  });
};
