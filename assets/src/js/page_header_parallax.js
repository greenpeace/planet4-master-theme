export const setupPageHeaderParallax = () => {
  const pageHeaderImages = document.querySelectorAll('.is-pattern-p4-page-header > .wp-block-media-text__media > img');

  window.addEventListener('scroll', () => {
    const scrollPosition = window.scrollY;

    pageHeaderImages.forEach(pageHeaderImage => {
      const pageHeaderPosition = pageHeaderImage.scrollHeight;

      if (scrollPosition > pageHeaderPosition) {
        pageHeaderImage.style.transform = `translateY(${(scrollPosition - pageHeaderPosition) * 0.6}px)`;
      }
    });
  });
};
