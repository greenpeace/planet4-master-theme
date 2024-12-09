const {__} = wp.i18n;

export const setupListingPages = () => {
  // Setup behaviour for list/grid toggle.
  const listViewToggle = document.querySelector('.list-view-toggle');
  const gridViewToggle = document.querySelector('.grid-view-toggle');

  const listingPageContent = document.getElementById('listing-page-content');

  if (!listingPageContent || !listViewToggle || !gridViewToggle) {
    return;
  }

  const switchViews = () => {
    listingPageContent.classList.toggle('wp-block-query--list');
    listingPageContent.classList.toggle('wp-block-query--grid');
    gridViewToggle.classList.toggle('d-none');
    listViewToggle.classList.toggle('d-none');
  };

  listViewToggle.onclick = switchViews;
  gridViewToggle.onclick = switchViews;

  // Setup filters for the News & Stories page.
  const filters = document.querySelector('.listing-page-filters');
  if (!filters) {
    return;
  }

  const AVAILABLE_FILTERS = ['category', 'post-type'];

  const updateFilters = () => {
    const newUrl = new URL(window.location.href.split('/page/')[0]);

    AVAILABLE_FILTERS.forEach(filter => {
      const {value} = document.getElementById(filter);
      if (value) {
        newUrl.searchParams.set(filter, value);
      } else {
        newUrl.searchParams.delete(filter);
      }
    });

    window.location.href = newUrl.href;
  };

  document.getElementById('apply-filters').onclick = updateFilters;

  // Add 'No posts found' text when needed.
  if (!listingPageContent.querySelector('.wp-block-post-template')) {
    const noPostsFound = document.createElement('p');
    noPostsFound.classList.add('listing-page-no-posts-found');
    noPostsFound.innerHTML = __('No posts found!', 'planet4-master-theme');
    listingPageContent.appendChild(noPostsFound);
  }
};
