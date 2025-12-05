export const setupNavInteractions = () => {
  const searchInput = document.getElementById('search_input');
  const searchClear = document.querySelector('.nav-search-clear');

  // Close all menus on escape pressed
  document.onkeyup = event => {
    if (event.key === 'Escape') {
      document.body.click();
    }
  };

  // Track first focus on search input
  let searchFocused = false;
  if (searchInput) {
    searchInput.addEventListener('focus', () => {
      if (!searchFocused) {
        searchFocused = true;
      }
    });
  }

  // Focus and clear the search input when the search clear button is clicked.
  if (searchClear && searchInput) {
    searchClear.addEventListener('click', () => {
      searchInput.value = null;
      searchInput.focus();
    }, true);
  }
};
