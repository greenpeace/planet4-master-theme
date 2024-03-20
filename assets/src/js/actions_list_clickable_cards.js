export const setupClickabelActionsListCards = () => {
  const liElements = document.querySelectorAll('.actions-list ul li');

  liElements.forEach(li => {
    const linkElement = li.querySelector('.wp-block-read-more');
    if (linkElement) {
      const url = linkElement.getAttribute('href');

      li.tabIndex = 0;
      li.addEventListener('click', () => {
        window.location.href = url;
      });
    }
  });
};
