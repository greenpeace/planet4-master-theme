document.addEventListener('DOMContentLoaded', () => {
  const personalDataTab = document.querySelector('#tab_personal-data');

  if (!personalDataTab) {
    return;
  }

  const retentionButtons = personalDataTab.querySelectorAll('#gform_setting_retention_policy input');
  const settingsPanel = personalDataTab.querySelector('.gform-settings-panel__content');
  const retentionDays = personalDataTab.querySelector('#retention\\[retain_entries_days\\]');
  const preventIp = personalDataTab.querySelector('#_gform_setting_preventIP');

  // Add a custom message to the "Personal Data > General Settings" panel
  if (settingsPanel) {
    const infoDiv = document.createElement('div');
    infoDiv.style.color = '#e54c3b';
    infoDiv.style.marginBottom = '20px';
    infoDiv.textContent = `In order to minimize the storage of personal data in Planet 4, these
      settings are managed automatically and cannot be changed manually. Ideally all submission
      data should be synced or exported to other systems.`;

    settingsPanel.prepend(infoDiv);
  }

  // Disable "Retention Policy" buttons if they exist
  if (retentionButtons?.length) {
    retentionButtons.forEach(button => button.disabled = true);
  }

  // Disable "Number of days to retain entries" input if it exists
  if (retentionDays) {
    retentionDays.disabled = true;
  }

  // Disable "Prevent the storage of IP addresses during form submission" input if it exists
  if (preventIp) {
    preventIp.disabled = true;
  }
});
