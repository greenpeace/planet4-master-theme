/* global bulkExportText */

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('select[name="action"], select[name="action2"]').forEach(select => {
    const opt = document.createElement('option');
    opt.value = 'export';
    opt.textContent = bulkExportText;
    select.appendChild(opt);
  });
});
