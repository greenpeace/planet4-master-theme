/* global mediaImportLabel */

document.addEventListener('DOMContentLoaded', () => {
  const pageTitleAction = document.querySelector('.upload-php .wrap .page-title-action');

  if(pageTitleAction) {
    const mediaImportButton = document.createElement('a');
    mediaImportButton.href = 'upload.php?page=media-picker';
    mediaImportButton.className = 'add-new-h2';
    mediaImportButton.textContent = mediaImportLabel;
    pageTitleAction.insertAdjacentElement('afterend', mediaImportButton);
  }
});
