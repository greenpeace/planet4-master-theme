export const setupPageHeaderParallax = () => {
  const pageHeaders = document.querySelectorAll('.is-pattern-p4-page-header');

  const addPageHeaderParallax = () => {
    pageHeaders.forEach(pageHeader => {
      const pageHeaderImage = pageHeader.querySelector('.wp-block-media-text__media > img');
      const pageHeaderRect = pageHeader.getBoundingClientRect();

      // 100 to take into account the navbar + a bit of extra spacing
      pageHeaderImage.style.transform = `translate3d(0px, ${(pageHeaderRect.top > 100 ? 0 : -(pageHeaderRect.top - 100)) * 0.3}px, 0px)`;
    });
  };

  window.addEventListener('scroll', addPageHeaderParallax);
};
