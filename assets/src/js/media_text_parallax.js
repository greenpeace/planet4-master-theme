export const setupMediaAndTextParallax = () => {
  const blocks = document.querySelectorAll('.wp-block-media-text.is-style-parallax');

  const addParallax = () => {
    blocks.forEach(block => {
      const blockImage = block.querySelector('.wp-block-media-text__media > img');
      const blockRect = block.getBoundingClientRect();

      // 100 to take into account the navbar + a bit of extra spacing
      blockImage.style.transform = `translate3d(0px, ${(blockRect.top > 100 ? 0 : -(blockRect.top - 100)) * 0.3}px, 0px)`;
    });
  };

  window.addEventListener('scroll', addParallax);
};
