import {createRoot} from 'react-dom/client';
import ListingPagePosts from './Components/ListingPage/ListingPagePosts';

(function() {
  const listingPageContent = document.getElementById('listing-page-content');

  // If the current page is not a listing page, we do nothing.
  if (!listingPageContent) {
    return;
  }

  const listingPageFilters = document.getElementById('listing-page-filters');
  const listingPageLayoutToggle = document.getElementById('listing-page-layout-toggle');

  createRoot(listingPageContent).render(
    <ListingPagePosts
      filtersContainer={listingPageFilters}
      layoutToggleContainer={listingPageLayoutToggle}
    />
  );
})();
