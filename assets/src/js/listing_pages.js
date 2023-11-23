export const setupListingPages = () => {
  const listViewToggle = document.querySelector('.list-view-toggle');
  const gridViewToggle = document.querySelector('.grid-view-toggle');

  const gridView = document.getElementById('grid-view');
  const listView = document.getElementById('list-view');

  if (!gridView || !listView || !listViewToggle || !gridViewToggle) {
    return;
  }

  const switchViews = () => {
    listView.classList.toggle('d-none');
    gridView.classList.toggle('d-none');
    gridViewToggle.classList.toggle('d-none');
    listViewToggle.classList.toggle('d-none');
  };

  listViewToggle.onclick = switchViews;
  gridViewToggle.onclick = switchViews;
};
