const {__, sprintf} = wp.i18n;

export const setupExternalLinks = () => {
  const siteURL = window.location.host;

  const excludedClasses = ['.btn', '.cover-card-heading', '.wp-block-button__link', '.share-btn'];

  const excludedHrefPatterns = [
    `[href*="${siteURL}"]`,
    '[href*=".pdf"]',
    '[href^="/"]',
    '[href^="#"]',
    '[href^="javascript:"]',
    '[href^="mailto:"]',
    '[href^="tel:"]',
  ];

  const exclusions = [
    ...excludedClasses.map(cls => `:not(${cls})`),
    ...excludedHrefPatterns.map(attr => `:not(${attr})`),
  ].join('');

  const containers = ['.page-content', 'article', '.author-details'];

  const linkSelector = containers.map(sel => `${sel} a${exclusions}`).join(', ');

  const links = [...document.querySelectorAll(linkSelector)];

  links.forEach(link => {
    if (link.matches('.boxout *') || !link.href) {
      return;
    }
    // We don't want to show the icon in headings/titles,
    // or in links that are images
    const text = link.textContent || link.innerText;
    if (['H1', 'H2', 'H3', 'H4', 'H5', 'H6'].includes(link.parentElement.nodeName) || text.trim().length === 0) {
      return;
    }

    link.target = link.target ? link.target : '';
    link.classList.add('external-link');

    const url = new URL(link.href);
    const domain = url.hostname.replace('www.', '');

    link.title = sprintf(
      // translators: 1: URL domain
      __('This link will lead you to %1$s', 'planet4-master-theme'),
      domain
    );
  });
};
