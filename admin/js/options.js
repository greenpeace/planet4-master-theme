

function removeSpamButtonsBox () {
  const spamButtons = document.querySelectorAll('.checkforspam');

  spamButtons.forEach(box => {
    box.remove();
  });
}

// Show/hide old special pages when enabling or disabling the new IA.
function toggleNewIaPages() {
  const newIASetting = document.querySelector('#new_ia');
  const analyticalCookiesCheckbox = document.querySelector('#enable_analytical_cookies');

  if (!newIASetting && !analyticalCookiesCheckbox) {
    return;
  }

  if (newIASetting) {
    const actPageDropdown = document.querySelector('[data-fieldtype="act_page_dropdown"]');
    const explorePageDropdown = document.querySelector('[data-fieldtype="explore_page_dropdown"]');
    const issuesParentCategoryDropdown = document.querySelector('[data-fieldtype="category_select_taxonomy"]');

    // Needed for page reload when saving settings.
    actPageDropdown.classList.toggle('hidden', newIASetting.checked);
    explorePageDropdown.classList.toggle('hidden', newIASetting.checked);
    issuesParentCategoryDropdown.classList.toggle('hidden', newIASetting.checked);

    newIASetting.addEventListener('change', event => {
      const {checked} = event.currentTarget;
      actPageDropdown.classList.toggle('hidden', checked);
      explorePageDropdown.classList.toggle('hidden', checked);
      issuesParentCategoryDropdown.classList.toggle('hidden', checked);
    });
  }

  if (analyticalCookiesCheckbox) {
    const nameTextField = document.querySelector('.cmb2-id-analytical-cookies-name');
    const descriptionTextField = document.querySelector('.cmb2-id-analytical-cookies-description');

    // Needed for page reload when saving settings.
    nameTextField.classList.toggle('hidden', !analyticalCookiesCheckbox.checked);
    descriptionTextField.classList.toggle('hidden', !analyticalCookiesCheckbox.checked);

    analyticalCookiesCheckbox.addEventListener('change', event => {
      const {checked} = event.currentTarget;
      nameTextField.classList.toggle('hidden', !checked);
      descriptionTextField.classList.toggle('hidden', !checked);
    });
  }
}

// Control the elements of the Hubspot Reverse Proxy form.
function toggleHubspotReverseProxySaveButton() {
  const reverseProxy = document.querySelector('#hubspot_reverse_proxy');
  const saveButton = document.querySelector('#option_metabox input[type=submit]');
  const domain = document.querySelector('.cmb2-id-hubspot-reverse-proxy-domain');
  const p4Path = document.querySelector('.cmb2-id-hubspot-reverse-proxy-p4-path');
  const hubspotPath = document.querySelector('.cmb2-id-hubspot-reverse-proxy-hubspot-path');

  if (!reverseProxy || !saveButton || !domain || !p4Path || !hubspotPath) {
    return;
  }

  // Create and insert an alert message:
  let alertMessage = document.querySelector('#hubspot_reverse_alert');

  if (!alertMessage) {
    alertMessage = document.createElement('span');
    alertMessage.id = 'hubspot_reverse_alert';
    alertMessage.classList.add('hidden');
    alertMessage.innerHTML = 'WARNING: All the fields must be complete to save the changes!';
    alertMessage.style.marginInlineStart = '10px';
    saveButton.insertAdjacentElement('afterend', alertMessage);
  }

  // Enable/disable the save button, and show/hide the alert message:
  function toggleSaveButton() {
    const proxyEnabled = reverseProxy.checked;
    const fieldsEmpty =
        domain.querySelector('input').value.trim() === '' ||
        p4Path.querySelector('input').value.trim() === '' ||
        hubspotPath.querySelector('input').value.trim() === '';

    const shouldDisable = proxyEnabled && fieldsEmpty;
    saveButton.disabled = shouldDisable;
    alertMessage.classList.toggle('hidden', !shouldDisable);
  }

  // Show/hide the form fields:
  function toggleTextFields() {
    if (reverseProxy.checked) {
      domain.classList.remove('hidden');
      p4Path.classList.remove('hidden');
      hubspotPath.classList.remove('hidden');
    } else {
      domain.classList.add('hidden');
      p4Path.classList.add('hidden');
      hubspotPath.classList.add('hidden');
    }
  }

  toggleSaveButton();
  toggleTextFields();

  reverseProxy.addEventListener('change', () => {
    toggleTextFields();
    toggleSaveButton();
  });

  domain.querySelector('input').addEventListener('input', toggleSaveButton);
  p4Path.querySelector('input').addEventListener('input', toggleSaveButton);
  hubspotPath.querySelector('input').addEventListener('input', toggleSaveButton);
}

document.addEventListener('DOMContentLoaded', () => {
  removeSpamButtonsBox();
  toggleNewIaPages();
  toggleHubspotReverseProxySaveButton();
});
