/**
 * Handle the "load more" functionality for the Actions List block.
 * This should only be applied to the grid layout.
 */
export const setupActionsListLoadMore = () => {
  const gridBlocks = document.querySelectorAll('.actions-list.is-custom-layout-grid');
  let postsPerRow = 3;
  // For medium screens we only show 2 posts per row.
  if (window.innerWidth >= 768 && window.innerWidth < 992) {
    postsPerRow = 2;
  }

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
    if (!posts || posts.length <= postsPerRow * 2) {
      loadMoreButtonContainer.classList.add('d-none');
      return;
    }

    const loadMoreButton = loadMoreButtonContainer.querySelector('button');
    loadMoreButton.onclick = () => {
      const hiddenPosts = posts.filter(post => window.getComputedStyle(post).display === 'none');
      if (hiddenPosts.length <= postsPerRow) {
        hiddenPosts.forEach(post => post.style.display = 'flex');
        loadMoreButtonContainer.classList.add('d-none');
      } else {
        hiddenPosts.slice(0, postsPerRow).forEach(post => post.style.display = 'flex');
      }
    };
  });
};
