/**
 * The script basically add/remove the tabindex attribute within the country selector
 * It helps to improve SEO
 * @return {void}
 */
export const setupCountrySelector = () => {
  const targetNode = document.querySelector('#country-selector');
  const countriesList = document.querySelector('.countries-list');
  const footerMenu = document.querySelector('.footer-menu');
  const footerSocialMedia = document.querySelector('.footer-social-media');

  if (!targetNode || !countriesList || !footerMenu || !footerSocialMedia) {
    return;
  }

  const largeAndUpScreens = '(min-width: 992px)';
  const countriesListTransitionDuration = (
    parseFloat(getComputedStyle(countriesList).transitionDuration) * 1000
  ) || 500;

  /**
   * This funtion re-order the elements from the footer
   * @param {*} evt
   */
  const switchOrders = (evt = null) => {
    const event = evt ? evt : window.matchMedia(largeAndUpScreens);
    if (event.matches) {
      footerMenu.parentNode.prepend(footerMenu);
    } else {
      footerSocialMedia.parentNode.prepend(footerSocialMedia);
    }
  };

  // Create an observer instance linked to the callback function
  const observer = new MutationObserver(
    // Callback function to execute when mutations are observed
    mutationList => {
      // Set default visibility
      for (const mutation of mutationList) {
        if (mutation.attributeName === 'class') {
          if (targetNode.classList.contains('open')) {
            countriesList.style.visibility = 'visible';
          } else {
            setTimeout(() => {
              countriesList.style.visibility = 'hidden';
            }, countriesListTransitionDuration);
          }
        }
      }
    }
  );

  // Start observing the target node for configured mutations
  observer.observe(targetNode, {attributes: true, childList: true, subtree: true});

  window.matchMedia(largeAndUpScreens).addEventListener('change', event => {
    switchOrders(event);
  });

  // Set default order
  switchOrders();
};
