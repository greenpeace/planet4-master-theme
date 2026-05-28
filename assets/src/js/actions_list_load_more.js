/**
 * Handle the "load more" functionality for the Actions List block.
 * This should only be applied to the grid layout.
 */
export const setupActionsListLoadMore = () => {
  const gridBlocks = document.querySelectorAll('.actions-list.is-custom-layout-grid');

  if (!gridBlocks.length) {
    return;
  }

  let postsPerRow = 3;

  if (window.innerWidth >= 768 && window.innerWidth < 992) {
    // For medium screens we only show 2 posts per row.
    postsPerRow = 2;
  } else if (window.innerWidth < 768) {
    // For small screens we only show 1 post per row.
    postsPerRow = 1;
  }

  // Implement "load more" behaviour for grid layouts.
  gridBlocks.forEach(block => {
    const loadMoreButtonContainer = block.querySelector('.load-more-actions-container');
    if (!loadMoreButtonContainer) {
      return;
    }

    const posts = [...block.querySelectorAll('.wp-block-post')];

    if (posts && posts.length > postsPerRow * 2) {
      loadMoreButtonContainer.classList.add('d-flex');
      loadMoreButtonContainer.classList.remove('d-none');
    } else {
      loadMoreButtonContainer.classList.remove('d-flex');
      loadMoreButtonContainer.classList.add('d-none');
    }

    const loadMoreButton = loadMoreButtonContainer.querySelector('.wp-element-button');
    if (!loadMoreButton) {
      return;
    }
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
