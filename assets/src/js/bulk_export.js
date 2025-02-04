/* global bulkExportText */

document.addEventListener('DOMContentLoaded', () => {
  jQuery(() => {
    jQuery('select[name=\'action\'], select[name=\'action2\']').each(function () {
      jQuery('<option>')
        .val('export')
        .text(bulkExportText)
        .appendTo(this);
    });
  });
});
