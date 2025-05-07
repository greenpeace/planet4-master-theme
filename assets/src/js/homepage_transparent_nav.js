/**
 * Handles the transition between the transparent styles and default styles for the
 * Navigation Menu when it is scrolled.
 */
export const setupTransparentNavHomepage = () => {
  let isScrolled = false;

  if (!document.body.classList.contains('home')) {
    return;
  }

  /**
   * Returns the current vertical scroll position of the window.
   *
   * @return {number} The number of pixels the document is currently scrolled vertically.
   */
  function getYPosition() {
    return (
      window.pageYOffset ||
    document.documentElement.scrollTop ||
    document.body.scrollTop ||
    0
    );
  }

  /**
   * Tracks the scroll position and adds or removes the `scrolled` class on the `<body>`
   * element based on whether the user has scrolled past the top of the page.
   */
  function trackScrollAsBodyClass() {
    const yTransitionPoint = 1; // in case people don't scroll ALL the way up
    const yPosition = getYPosition();

    if (yPosition > yTransitionPoint && !isScrolled) {
      isScrolled = true;
      document.body.classList.add('scrolled');
    } else if (yPosition === 0 && isScrolled) {
      isScrolled = false;
      document.body.classList.remove('scrolled');
    }
  }

  if (getYPosition() > 0) {
    isScrolled = true;
    document.body.classList.add('scrolled');
  }
  window.addEventListener('scroll', trackScrollAsBodyClass);
};
