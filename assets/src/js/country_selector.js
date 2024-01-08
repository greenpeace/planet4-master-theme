// The script basically add/remove the tabindex attribute within the country selector
// It helps to improve SEO
export const setupCountrySelector = () => {
  const countries = document.querySelectorAll('.countries-list .countries > li');

  // Create a new MutationObserver with a country selector
  new MutationObserver(
    mutationList => {
      for (const mutation of mutationList) {
        if (mutation.target.className.includes('footer-country-selector') && mutation.attributeName === 'class') {
          const isOpen = mutation.target.className.includes('open');

          for (const countryItem of countries) {
            // This check applies not only to the parent link but also to children
            // In some cases, the country has also different link's languages
            if (countryItem.classList.contains('country-group')) {
              countryItem.setAttribute('tabIndex', isOpen ? 0 : -1);

              for (const link of countryItem.querySelectorAll('li')) {
                link.setAttribute('tabIndex', isOpen ? 0 : -1);
              }
            }
          }
        }
      }
    }
  ).observe(
    document.querySelector('.footer-country-selector'),
    {attributes: true, childList: true, subtree: true}
  );
};
