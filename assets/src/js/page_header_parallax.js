const isSmallWindow = () => window.innerWidth < 992;

const addPageHeaderParallax = pageHeaderImages => {
  const scrollPosition = window.scrollY;

  pageHeaderImages.forEach(pageHeaderImage => {
    const pageHeaderPosition = pageHeaderImage.scrollHeight;

    if (scrollPosition > pageHeaderPosition) {
      pageHeaderImage.style.transform = `translateY(${(scrollPosition - pageHeaderPosition) * 0.6}px)`;
    }
  });
};

const removePageHeaderParallax = pageHeaderImages => {
  pageHeaderImages.forEach(pageHeaderImage => {
    pageHeaderImage.style.transform = 'none';
  });
};

export const setupPageHeaderParallax = () => {
  const pageHeaderImages = document.querySelectorAll('.is-pattern-p4-page-header > .wp-block-media-text__media > img');

  const pageHeaderParallax = () => addPageHeaderParallax(pageHeaderImages);

  if (!isSmallWindow()) {
    window.addEventListener('scroll', pageHeaderParallax);
  }

  window.addEventListener('resize', () => {
    if (isSmallWindow()) {
      window.removeEventListener('scroll', pageHeaderParallax);
      removePageHeaderParallax(pageHeaderImages);
    } else {
      window.addEventListener('scroll', pageHeaderParallax);
    }
  });
};
