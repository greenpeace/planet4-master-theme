// Add pdf icon to pdf links
export const setupPDFIcon = () => {
  const links = [...document.querySelectorAll('a[href*=".pdf"]:not(.search-result-item-headline)')];

  links.forEach(link => {
    // We don't want to show the icon in headings/titles,
    // or in links that are images
    if (
      ['H1', 'H2', 'H3', 'H4', 'H5', 'H6'].includes(link.parentElement.nodeName)
      || link.querySelectorAll('img').length > 0
    ) {
      return;
    }

    link.classList.add('pdf-link');
  });
};
