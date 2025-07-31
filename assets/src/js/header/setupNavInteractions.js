/* global hj */

export const setupNavInteractions = () => {
  // Close all menus on escape pressed
  document.onkeyup = event => {
    if (event.key === 'Escape') {
      document.body.click();
    }
  };

  // Track first focus on search input
  let searchFocused = false;
  const searchInput = document.getElementById('search_input');
  if (searchInput) {
    searchInput.addEventListener('focus', () => {
      if (!searchFocused) {
        if (hj) {
          hj('event', 'search');
        }
        searchFocused = true;
      }
    });
  }
};
