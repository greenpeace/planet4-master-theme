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

  // Functions and constants.
  const CATEGORY_FILTER = 'category';

  const updateFilter = event => {
    const {target: {id, value}} = event;
    const newUrl = new URL(window.location.href.split('/page/')[0]);

    if (value) {
      newUrl.searchParams.set(id, value);
    } else {
      newUrl.searchParams.delete(id);
    }
    window.location.href = newUrl.href;
  };

  // Category filter.
  document.getElementById(CATEGORY_FILTER).onchange = updateFilter;
};
