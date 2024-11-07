export const removeRelatedPostsSection = () => {
  // Remove Related Posts section when Query loop returns no posts.
  const section = document.querySelector('.post-articles-block');

  if (section && !section.querySelector('.p4-query-loop .wp-block-post-template')) {
    section.style.display = 'none';
  }
};
