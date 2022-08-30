export const setupPageHeaderParallax = () => {
  const pageHeaders = document.querySelectorAll('.is-pattern-p4-page-header');

  const addPageHeaderParallax = () => {
    pageHeaders.forEach(pageHeader => {
      const pageHeaderImage = pageHeader.querySelector('.wp-block-media-text__media > img');
      const pageHeaderRect = pageHeader.getBoundingClientRect();

      // 100 to take into account the navbar + a bit of extra spacing
      if (pageHeaderRect.top < 100) {
        pageHeaderImage.style.transform = `translateY(${(100 - pageHeaderRect.top) * 0.3}px)`;
      }
    });
  };

  window.addEventListener('scroll', () => setTimeout(addPageHeaderParallax, 100));
};
