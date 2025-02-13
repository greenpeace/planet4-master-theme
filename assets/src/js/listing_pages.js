const {__} = wp.i18n;

export const setupListingPages = () => {
  const listingPageContent = document.getElementById('listing-page-content');
  // If the current page is not a listing page, we do nothing.
  if (!listingPageContent) {
    return;
  }

  // Setup behaviour for list/grid toggle.
  const listViewToggle = document.querySelector('.list-view-toggle');
  const gridViewToggle = document.querySelector('.grid-view-toggle');

  if (!listViewToggle && !gridViewToggle) {
    return;
  }

  listingPageContent.classList.toggle('wp-block-query--list', gridViewToggle);
  listingPageContent.classList.toggle('wp-block-query--grid', listViewToggle);

  const switchViews = layout => {
    const newUrl = new URL(window.location.href);
    newUrl.searchParams.set('layout', layout);
    window.location.href = newUrl.href;
  };

  if (listViewToggle) {
    listViewToggle.onclick = () => switchViews('list');
  } else {
    gridViewToggle.onclick = () => switchViews('grid');
  }

  // Setup filters for the News & Stories page.
  const filters = document.querySelector('.listing-page-filters');
  if (!filters) {
    return;
  }

  const AVAILABLE_FILTERS = ['category', 'post-type'];

  const updateFilters = () => {
    const newUrl = new URL(window.location.href.replace(/\/page\/\d/, '/'));

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
