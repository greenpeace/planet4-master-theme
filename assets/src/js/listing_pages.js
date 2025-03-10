const {__} = wp.i18n;
const gridSVG = `<svg viewBox="0 0 32 32" class="icon">
        <use xlink:href="http://www.planet4.test/wp-content/themes/planet4-master-theme/assets/build/sprite.symbol.svg#grid-view"></use>
      </svg>`;
const listSVG = `
    <svg viewBox="0 0 32 32" class="icon">
        <use xlink:href="http://www.planet4.test/wp-content/themes/planet4-master-theme/assets/build/sprite.symbol.svg#list-view"></use>
    </svg>`;

export const setupListingPages = () => {
  const listingPageContent = document.getElementById('listing-page-content');
  // If the current page is not a listing page, we do nothing.
  if (!listingPageContent) {
    return;
  }

  const toggleButton = document.querySelectorAll('.listing-page-title button')[1];

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
    window.history.pushState({}, '', newUrl);
    listingPageContent.classList.remove('wp-block-query--grid', 'wp-block-query--list');
    listingPageContent.classList.add(`wp-block-query--${layout}`);

    if (layout === 'list') {
      toggleButton.title = 'Grid View';
      toggleButton.innerHTML = gridSVG;
      toggleButton.onclick = () => switchViews('grid');
    }

    if (layout === 'grid') {
      toggleButton.title = 'List View';
      toggleButton.innerHTML = listSVG;
      toggleButton.onclick = () => switchViews('list');
    }
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

