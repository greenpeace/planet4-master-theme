/**
 * Handle the "load more" functionality for the Actions List block.
 * This should only be applied to the grid layout.
 */
export const setupActionsListLoadMore = () => {
  const gridBlocks = document.querySelectorAll('.actions-list.is-custom-layout-grid');

  if (!gridBlocks.length) {
    return;
  }

  // Implement "load more" behaviour for grid layouts.
  gridBlocks.forEach(block => {
    const loadMoreButtonContainer = block.querySelector('.load-more-actions-container');
    if (!loadMoreButtonContainer) {
      return;
    }

    const posts = [...block.querySelectorAll('.wp-block-post')];
    if (!posts || posts.length <= 6) {
      loadMoreButtonContainer.classList.add('d-none');
      return;
    }

    const loadMoreButton = loadMoreButtonContainer.querySelector('button');
    loadMoreButton.onclick = () => {
      const hiddenPosts = posts.filter(post => window.getComputedStyle(post).display === 'none');
      if (hiddenPosts.length <= 3) {
        hiddenPosts.forEach(post => post.style.display = 'flex');
        loadMoreButtonContainer.classList.add('d-none');
      } else {
        hiddenPosts.slice(0, 3).forEach(post => post.style.display = 'flex');
      }
    };
  });
};
