/* global mediaImportLabel */

jQuery(() =>{
  jQuery('.upload-php .wrap .page-title-action')
    .after(`<a href="upload.php?page=media-picker" class="add-new-h2">${mediaImportLabel}</a>`);
});
