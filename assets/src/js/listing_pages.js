export const setupListingPages = () => {
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
};
