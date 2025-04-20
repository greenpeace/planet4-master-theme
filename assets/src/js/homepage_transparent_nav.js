export const setupTransparentNavHomepage = () => {
  let isScrolled = false;
  ready(() => {
    if (!onHomePage()) {return;}
    if (getYPosition() > 0) {
      isScrolled = true;
      document.body.classList.add('scrolled');
    }
    window.addEventListener('scroll', trackScrollAsBodyClass);
  });

  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  function onHomePage() {
    return document.body.classList.contains('home');
  }

  function getYPosition() {
    return (
      window.pageYOffset ||
    document.documentElement.scrollTop ||
    document.body.scrollTop ||
    0
    );
  }

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
};
