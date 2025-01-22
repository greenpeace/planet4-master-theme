/* global bulkExportText */

jQuery(() => {
  jQuery('<option>').val('export')
    .text(bulkExportText)
    .appendTo('select[name=\'action\']');
});
