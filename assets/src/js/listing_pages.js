const {__} = wp.i18n;

(function() {
  const listingPageContent = document.getElementById('listing-page-content');
  // If the current page is not a listing page, we do nothing.
  if (!listingPageContent) {
    return;
  }

  const toggleButton = document.querySelector('.layout-toggle');

  const clearStorageAfter = 30 * 60 * 1000; // 30mins in milliseconds

  // Function to clear localStorage after a set time
  const clearLocalStorage = () => {
    setTimeout(() => {
      localStorage.removeItem('layout');
    }, clearStorageAfter);
  };

  const switchViews = layoutView => {
    let layout = layoutView;
    if (event?.target?.tagName && event?.target?.tagName.toLowerCase() === 'button') {
      layout = event.target.getAttribute('data-layout');
    }

    listingPageContent.classList.remove('wp-block-query--grid', 'wp-block-query--list');
    listingPageContent.classList.add(`wp-block-query--${layout}`);

    localStorage.setItem('layout', layout);
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
    const layout = localStorage.getItem('layout') || '';

    if (layout && toggleButton) {
      switchViews(layout);
    }
  };

  initLayout();

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
})();

