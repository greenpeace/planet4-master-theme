export const setupPageHeaderParallax = () => {
  const pageHeaders = [...document.querySelectorAll('.is-pattern-p4-page-header > .wp-block-media-text__media > img')];

  window.addEventListener('scroll', () => {
    const scrollPosition = window.scrollY;
    pageHeaders.forEach(pageHeader => pageHeader.style.transform = 'translateY(' + scrollPosition * 0.6 + 'px)');
  });
};
