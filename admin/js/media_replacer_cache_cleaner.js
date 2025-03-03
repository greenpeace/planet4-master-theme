/* global cacheCleaner */

/**
 * JavaScript implementation for forcing images to update after replacing,
 * by introducing an extra parameter to the URL.
 */
document.addEventListener('DOMContentLoaded', () => {
  const cleaner = '?cache_cleaner=' + Date.now();

  // Check if we are on the media library page
  if (window.location.href.indexOf('upload.php') > -1) {
    const selectors = '.attachments-wrapper img, .edit-attachment-frame img, #the-list img';
    const images = document.querySelectorAll(selectors);

    images.forEach(img => {
      if (cacheCleaner.some(substring => img.getAttribute('src').includes(substring))) {
        img.setAttribute('src', img.getAttribute('src') + cleaner);
      }
    });
  }

  // Check if we are on the media edit page
  if (window.location.href.indexOf('post.php') > -1) {
    const images = document.querySelectorAll('.wp_attachment_holder img');
    const thumbLinks = document.querySelectorAll('#sm-attachment-metabox .sm-view-link');

    images.forEach(img => {
      if (cacheCleaner.some(substring => img.getAttribute('src').includes(substring))) {
        img.setAttribute('src', img.getAttribute('src') + cleaner);
      }
    });
    thumbLinks.forEach(thumb => {
      thumb.setAttribute('href', thumb.getAttribute('href') + cleaner);
    });
  }
});
