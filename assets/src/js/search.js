/* global localizations */

const showHiddenRow = row => {
  if (!row) {
    return;
  }
  row.classList.remove('row-hidden');
  row.style.display = 'block';
};

// Search page.
export const setupSearch = ($) => {
  const isSearch = document.body.classList.contains('search');
  if (!isSearch) {
    return;
  }

  // Needed form and inputs
  const searchForm = document.getElementById('search_form');
  const orderInput = document.getElementById('orderby');

  // Submit form on Sort change event.
  const orderSelect = document.getElementById('select_order');
  if (orderSelect) {
    orderSelect.onchange = () => {
      orderInput.value = orderSelect.value;
      searchForm.submit();
    };
  }

  // Submit form on Filter click event or on Apply button click event.
  const filterInputs = [...document.querySelectorAll('input[name^="f["]:not(.modal-checkbox), .applybtn')];
  filterInputs.forEach(filterInput => {
    filterInput.onclick = () => searchForm.submit();
  });

  // Add all selected filters to the form submit.
  searchForm.onsubmit = () => {
    const isModalOpen = document.querySelector('.filter-modal.show');

    const selectedFilters = [...document.querySelectorAll(
      `input[name^="f["]${isModalOpen ? '.modal-checkbox' : ':not(.modal-checkbox)'}:checked`
    )];

    selectedFilters.forEach(selectedFilter => {
      const selectedFilterCopy = selectedFilter.cloneNode(true);
      selectedFilterCopy.style.display = 'none';
      searchForm.appendChild(selectedFilterCopy);
    });
  };

  // Clear single selected filter.
  const activeFilterTags = [...document.querySelectorAll('.activefilter-tag')];
  activeFilterTags.forEach(activeFilterTag => {
    const filterId = activeFilterTag.dataset.id;
    const correspondingFilterInput = document.querySelector(`.p4-custom-control-input[value=${filterId}]`);

    activeFilterTag.onclick = () => {
      correspondingFilterInput.checked = false;
      searchForm.submit();
    };
  });

  // Clear all selected filters.
  const clearAllButton = document.querySelector('.clearall');
  if (clearAllButton) {
    clearAllButton.onclick = () => {
      const selectedFilters = [...document.querySelectorAll('input[name^="f["]:checked')];
      selectedFilters.forEach(selectedFilter => {
        selectedFilter.checked = false;
      });
      searchForm.submit();
    };
  }

  // Add click event for load more button in blocks.
  const navSearchInput = document.getElementById('search_input');
  const loadMoreButton = document.querySelector('.btn-load-more-click-scroll');
  if (loadMoreButton) {
    loadMoreButton.onclick = () => {
      if (loadMoreButton.classList.contains('btn-load-more-async')) {
        const { total_posts, posts_per_load, current_page } = loadMoreButton.dataset;
        const nextPage = parseInt(current_page) + 1;
        loadMoreButton.dataset.current_page = nextPage;

        $.ajax({
          url: localizations.ajaxurl,
          type: 'GET',
          data: {
            action:          'get_paged_posts',
            'search-action': 'get_paged_posts',
            search_query:    navSearchInput.value.trim(),
            paged:           nextPage,
            orderby:         orderInput.value,
            'query-string':  decodeURIComponent(location.search).substring(1) // Ignore the ? in the search url (first char).
          },
          dataType: 'html'
        }).done((response) => {
          // Append the response at the bottom of the results and then show it.
          const searchResults = document.querySelector('.multiple-search-result .list-unstyled');

          // The response is an HTML string that we need to convert to a DOM node before appending it.
          const nodeResponse = document.createRange().createContextualFragment(response);
          searchResults.appendChild(nodeResponse);

          const hiddenRow = document.querySelector('.row-hidden:last-child');
          showHiddenRow(hiddenRow);

          if (posts_per_load * nextPage > total_posts) {
            loadMoreButton.style.display = 'none';
          }
        }).fail(( jqXHR, textStatus, errorThrown ) => {
          console.log(errorThrown); //eslint-disable-line no-console
        });
      } else {
        const hiddenRows = [...document.querySelectorAll('.row-hidden')];

        if (hiddenRows.length === 1) {
          loadMoreButton.style.display = 'none';
        }

        showHiddenRow(hiddenRows[0]);
      }
    };
  }

  // Reveal more results just by scrolling down the first 'show_scroll_times' times.
  let loadMoreCount = 0;
  let loadedMore = false;
  window.onscroll = () => {
    if (loadMoreButton) {
      let elementTop      = loadMoreButton.offsetTop,
        elementHeight     = loadMoreButton.clientHeight,
        windowHeight      = window.innerHeight,
        windowScroll      = window.scrollY,
        loadEarlierOffset = 250;

      const { posts_per_load, total_posts } = loadMoreButton.dataset;

      if (loadMoreCount < localizations.show_scroll_times) {
        // If next page has not loaded then load next page as soon as scrolling
        // reaches 'loadEarlierOffset' pixels before the Load more button.
        if (!loadedMore
            && windowScroll > (elementTop + elementHeight - windowHeight - loadEarlierOffset)
            && (loadMoreCount + 1) * posts_per_load < total_posts) {

          loadMoreCount += 1;
          loadMoreButton.click();
          loadedMore = true;

          // Add a throttle to avoid multiple scroll events from firing together.
          setTimeout(() => {
            loadedMore = false;
          }, 500);
        }
        if (windowScroll > (elementTop + elementHeight - windowHeight)) {
          const hiddenRows = [...document.querySelectorAll('.row-hidden')];
          hiddenRows.forEach(showHiddenRow);
        }
      }
      return false;
    }
  };
};
