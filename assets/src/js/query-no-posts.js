export const removeNoPostText = () => {
  const postTitle = document.querySelector('.p4-query-loop .wp-block-heading');
  const postDescription = document.querySelector('.p4-query-loop p');
  const noResultsText = document.querySelector('.p4-query-loop .wp-block-query-no-results');

  if (!noResultsText || postTitle?.innerHTML !== '' || postDescription?.innerHTML !== '') {
    return;
  }

  noResultsText.style.display = 'none';
};
