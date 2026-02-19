const {__} = wp.i18n;

const showHiddenRow = row => {
  if (!row) {
    return;
  }
  row.classList.remove('row-hidden');
};

const isModalSearch = () => {
  return document.getElementById('filtermodal') &&
    document.getElementById('filtermodal').style.display !== 'none';
};

const getSelectedFilters = isModal => {
  const filters_div_id = isModal ? 'filtermodal' : 'filter-sidebar-options';
  return [...document.querySelectorAll(
    `#${filters_div_id} input[name^="f["]:checked`
  )];
};

const addSelectedFiltersToForm = (isModal, idToRemove) => {
  const searchForm = document.getElementById('search_form');
  let selectedFilters = getSelectedFilters(isModal);

  if (idToRemove) {
    selectedFilters = selectedFilters.filter(selectedFilter => selectedFilter.value !== idToRemove);
  }

  selectedFilters.forEach(selectedFilter => {
    const selectedFilterCopy = selectedFilter.cloneNode(true);
    selectedFilterCopy.style.display = 'none';
    searchForm.appendChild(selectedFilterCopy);
  });
};

// Search page.
export const setupSearch = () => {
  // Needed form and inputs
  const searchForm = document.getElementById('search_form');
  const orderInput = document.getElementById('orderby');

  // Submit form on Sort change event.
  const orderSelect = document.getElementById('select_order');
  if (orderSelect) {
    orderSelect.onchange = () => {
      orderInput.value = orderSelect.value;
      addSelectedFiltersToForm(isModalSearch());
      searchForm.submit();
    };
  }

  // Submit form on filter click event.
  const filterInputs = [...document.querySelectorAll('input[name^="f["]:not(.modal-checkbox)')];
  filterInputs.forEach(filterInput => {
    filterInput.onclick = () => {
      addSelectedFiltersToForm(false);
      searchForm.submit();
    };
  });

  // Submit form on Apply filters button click event.
  const applyFiltersButton = document.querySelector('.applybtn');
  applyFiltersButton.onclick = () => {
    addSelectedFiltersToForm(true);
    searchForm.submit();
  };

  // Clear single selected filter.
  const activeFilterTags = [...document.querySelectorAll('.activefilter-tag')];
  activeFilterTags.forEach(activeFilterTag => {
    const filterId = activeFilterTag.dataset.id;

    activeFilterTag.onclick = () => {
      addSelectedFiltersToForm(false, filterId);
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
  const loadMoreButton = document.querySelector('.more-btn');
  const announce = document.getElementById('announce');
  if (loadMoreButton) {
    loadMoreButton.onclick = () => {
      const {total_posts, posts_per_load, current_page} = loadMoreButton.dataset;
      const nextPage = parseInt(current_page) + 1;
      loadMoreButton.dataset.current_page = nextPage;

      const url = new URL(document.documentElement.dataset.base + '/wp-json/planet4/v1/search/');
      url.searchParams.append('s', navSearchInput.value.trim());
      url.searchParams.append('paged', nextPage);
      url.searchParams.append('orderby', orderInput.value);
      getSelectedFilters(isModalSearch()).forEach(f => {
        url.searchParams.append(f.name, f.value);
      });

      fetch(url)
        .then(response => response.text())
        .then(html => {
          // Append the response at the bottom of the results and then show it.
          const searchResults = document.querySelector('.multiple-search-result .list-unstyled');
          searchResults.innerHTML += html;

          const hiddenRow = document.querySelector('.row-hidden:last-child');
          showHiddenRow(hiddenRow);

          // Indicate to screen reader users that more results have appeared.
          if (announce) {
            const message = document.createElement('p');
            message.textContent = __('More results loaded', 'planet4-master-theme');
            announce.appendChild(message);
          }

          if (posts_per_load * nextPage > total_posts) {
            loadMoreButton.style.display = 'none';
          }

          // Focus on newly loaded results for tab users.
          hiddenRow.querySelector('.search-result-item-headline').focus();
        }).catch(error => {
          console.log(error); //eslint-disable-line no-console
        });
    };
  }
};
