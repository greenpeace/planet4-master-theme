export const setupClickabelActionsListCards = () => {
  const liElements = document.querySelectorAll('.actions-list ul li:not(.carousel-li)');

  liElements.forEach(li => {
    const linkElement = li.querySelector('.wp-block-read-more');
    if (linkElement) {
      const url = linkElement.getAttribute('href');
      li.tabIndex = 0;
      const anchor = document.createElement('a');
      anchor.setAttribute('href', url);
      anchor.classList.add('actions-list-links');

      while (li.firstChild) {
        anchor.appendChild(li.firstChild);
      }

      li.appendChild(anchor);
    }
  });
};
