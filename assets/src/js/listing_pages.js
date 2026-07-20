import {createRoot} from 'react-dom/client';
import ListingPagePosts from './Components/ListingPagePosts';

const {__} = wp.i18n;

(function() {
  const listingPageContent = document.getElementById('listing-page-content');

  // If the current page is not a listing page, we do nothing.
  if (!listingPageContent) {
    return;
  }

  const listingPageFilters = document.getElementById('listing-page-filters');

  createRoot(listingPageContent).render(
    <ListingPagePosts filtersContainer={listingPageFilters} />
  );

  const toggleButton = document.querySelector('.layout-toggle');

  const clearStorageAfter = 30 * 60 * 1000; // 30mins in milliseconds

  // Function to clear localStorage after a set time
  const clearLocalStorage = () => {
    setTimeout(() => {
      try {
        localStorage.removeItem('layout');
      } catch (e) {
        if (typeof Sentry !== 'undefined') {
          // eslint-disable-next-line no-undef
          Sentry.captureException('localStorage.removeItem failed:', e);
        }
      }
    }, clearStorageAfter);
  };

  const switchViews = layoutView => {
    let layout = layoutView;
    if (event?.target?.tagName && event?.target?.tagName.toLowerCase() === 'button') {
      layout = event.target.getAttribute('data-layout');
    }

    listingPageContent.classList.remove('wp-block-query--grid', 'wp-block-query--list');
    listingPageContent.classList.add(`wp-block-query--${layout}`);

    try {
      localStorage.setItem('layout', layout);
    } catch (e) {
      if (typeof Sentry !== 'undefined') {
        // eslint-disable-next-line no-undef
        Sentry.captureException('localStorage.setItem failed:', e);
      }
    }

    clearLocalStorage();

    if (layout === 'list') {
      toggleButton.title = __('Grid View', 'planet4-master-theme');
      toggleButton.classList.remove('layout-toggle-grid', 'layout-toggle-list');
      toggleButton.classList.add('layout-toggle-grid');
      toggleButton.setAttribute('data-layout', 'grid');
      toggleButton.setAttribute('aria-label', __('Switch to grid view', 'planet4-master-theme'));
    }

    if (layout === 'grid') {
      toggleButton.title = __('List View', 'planet4-master-theme');
      toggleButton.classList.remove('layout-toggle-grid', 'layout-toggle-list');
      toggleButton.classList.add('layout-toggle-list');
      toggleButton.setAttribute('data-layout', 'list');
      toggleButton.setAttribute('aria-label', __('Switch to list view', 'planet4-master-theme'));
    }
  };

  if (toggleButton) {
    toggleButton.onclick = switchViews;
  }

  const initLayout = () => {
    let layout = '';
    try {
      layout = localStorage.getItem('layout') || '';
    } catch (e) {
      if (typeof Sentry !== 'undefined') {
        // eslint-disable-next-line no-undef
        Sentry.captureException('localStorage.getItem failed:', e);
      }
    }

    if (layout && toggleButton) {
      switchViews(layout);
    }
  };

  initLayout();
})();
