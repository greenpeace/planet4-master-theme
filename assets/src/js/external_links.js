export const setupExternalLinks = () => {
  const siteURL = window.location.host;

  const linkSelector = ['div.page-template', 'article'].map(sel=>`${sel} a:not(.btn):not(.cover-card-heading):not(.wp-block-button__link):not([href*="${siteURL}"]):not([href*=".pdf"]):not([href^="/"]):not([href^="#"]):not([href^="javascript:"])`).join(', ');
  const links = [...document.querySelectorAll(linkSelector)];

  links.forEach(link => {
    // We don't want to show the icon in headings/titles,
    // or in links that are images
    const text = link.textContent || link.innerText;
    if (['H1', 'H2', 'H3', 'H4', 'H5', 'H6'].includes(link.parentElement.nodeName) || text.trim().length === 0) {
      return;
    }

    link.target = '_blank';
    link.classList.add('external-link');
  });
};
