document.addEventListener('DOMContentLoaded', () => {
  // Select all retention policy radio buttons
  const retentionButtons = document.querySelectorAll('#gform_setting_retention_policy input');
  const retentionDays = document.querySelector('#retention\\[retain_entries_days\\]');

  // Disable "Retention Policy" buttons if they exist
  if (retentionButtons?.length) {
    retentionButtons.forEach(button => button.disabled = true);
  }

  // Disable "Number of days to retain entries" input if it exists
  if (retentionDays) {
    retentionDays.disabled = true;
  }
});
