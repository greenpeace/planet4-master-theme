export const removeNoPostText = () => {
  const postTitle = document.querySelector('.p4-query-loop .wp-block-heading');
  const postDescription = document.querySelector('.p4-query-loop p');

  if (!postTitle || !postDescription || postTitle.innerHTML !== '' || postDescription.innerHTML !== '') {
    return;
  }

  const noResultsText = document.querySelector('.p4-query-loop .wp-block-query-no-results');
  noResultsText.style.display = 'none';
};
