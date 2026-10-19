// Constants
const ARROW_DIRECTIONS = ['prev', 'next'];
const LAYOUTS = {
  carousel: 'carousel',
  grid: 'grid',
  list: 'list',
};
const BUTTONS_CLASS = '.wp-block-buttons';
const CONTROLS_CLASS = `.${LAYOUTS.carousel}-control`;
const BLOCK_CLASSNAMES = {
  actionsList: 'actions-list',
  postsList: 'posts-list',
};

/**
 * This function removes the arrows, in one of these two cases:
 * the layout isn't carousel, or it doesn't have enough items to scroll.
 *
 * @param {string} layout - The layout of the Query Loop block: can be grid, list, or carousel.
 *
 * @return {void}
 */
const removeArrows = layout => ARROW_DIRECTIONS.forEach(direction => {
  const controlBtn = layout.querySelector(`${BUTTONS_CLASS} ${CONTROLS_CLASS}-${direction}`);
  if (controlBtn && controlBtn.parentNode) {
    controlBtn.parentNode.removeChild(controlBtn);
  }
});

/**
 * This function is used to setup the various custom layouts that we
 * created for the Query Loop block for our Actions List and Posts List blocks.
 *
 * The available layouts are grid, list, and carousel.
 *
 * @return {void}
 */
export const setupQueryLoopCarousel = () => {
  for (const layout of document.querySelectorAll('[class*="is-custom-layout-"]')) {
    const hasValidLayout = layout && Object.keys(LAYOUTS).find(l => layout.className.includes(l));
    if (!hasValidLayout) {
      return;
    }

    // This is for the carousel layout, we need to setup the arrows and indicators.
    // Or hide them if there are not enough items to scroll.
    if (layout.className.includes(LAYOUTS.carousel)) {
      const list = layout.querySelector('.wp-block-post-template'); // > ul
      const isPostsList = layout.className.includes(BLOCK_CLASSNAMES.postsList);
      const isActionsList = layout.className.includes(BLOCK_CLASSNAMES.actionsList);
      if (!list || (!isActionsList && !isPostsList)) {
        return;
      }
    } else {
      // This is for the grid or list layouts, we need to remove the arrows.
      removeArrows(layout);
    }
  }
};
