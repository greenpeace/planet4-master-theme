// Add pdf icon to pdf links
export const setupPDFIcon = () => {
  const links = [...document.querySelectorAll('a[href*=".pdf"]:not(.search-result-item-headline):not(.cover-card-heading)')];

  links.forEach(link => {
    // We don't want to show the icon in headings/titles,
    // or in links that are images
    const text = link.textContent || link.innerText;
    if (
      ['H1', 'H2', 'H3', 'H4', 'H5', 'H6'].includes(link.parentElement.nodeName)
      || link.querySelectorAll('img').length > 0
      || text.trim().length === 0
    ) {
      return;
    }

    link.classList.add('pdf-link');
  });
};
