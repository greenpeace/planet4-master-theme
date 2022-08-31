export const setupPageHeaderParallax = () => {
  const pageHeaders = document.querySelectorAll('.is-pattern-p4-page-header');

  const addPageHeaderParallax = () => {
    pageHeaders.forEach(pageHeader => {
      const pageHeaderImage = pageHeader.querySelector('.wp-block-media-text__media > img');
      const pageHeaderRect = pageHeader.getBoundingClientRect();

      // 100 to take into account the navbar + a bit of extra spacing
      const pageHeaderTopPosition = Math.round(pageHeaderRect.top);
      if (pageHeaderTopPosition < 100) {
        pageHeaderImage.style.transform = `translate3d(0px, ${(100 - pageHeaderTopPosition) * 0.3}px, 0px)`;
      }
    });
  };

  window.addEventListener('scroll', () => setTimeout(addPageHeaderParallax, 100));
};
