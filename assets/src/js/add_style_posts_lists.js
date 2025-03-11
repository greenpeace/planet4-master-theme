export const addMarginToPTag = () => {
  const postsListBlocks = document.querySelectorAll('.posts-list');

  postsListBlocks.forEach(block => {
    const wpGroup = block.querySelector('.wp-block-group');

    if (wpGroup) {
      const pTag = wpGroup.nextElementSibling;

      if (pTag && pTag.tagName.toLowerCase() === 'p') {
        const marginTop = pTag.style.marginTop;
        const marginBottom = pTag.style.marginBottom;

        pTag.style.marginTop = marginTop ? marginTop : '24px';
        pTag.style.marginBottom = marginBottom ? marginBottom : '36px';
      }
    }
  });
};
