export const setupPostAuthorDescription = () => {
  const postAuthorDescriptionRemainder = document.querySelector('.post-author-description-remainder');

  if (postAuthorDescriptionRemainder) {
    const showMoreButton = document.querySelector('.post-author-description-show-more');
    const showLessButton = document.querySelector('.post-author-description-show-less');
    const ellipsis = document.querySelector('.post-author-description-ellipsis');

    showMoreButton.onclick = () => {
      postAuthorDescriptionRemainder.classList.remove('hidden');
      showLessButton.classList.remove('hidden');
      showMoreButton.classList.add('hidden');
      ellipsis.classList.add('hidden');
    };

    showLessButton.onclick = () => {
      postAuthorDescriptionRemainder.classList.add('hidden');
      showLessButton.classList.add('hidden');
      showMoreButton.classList.remove('hidden');
      ellipsis.classList.remove('hidden');
    };
  }
};
