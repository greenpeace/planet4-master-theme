const spamButtons = document.querySelectorAll('.checkforspam');

spamButtons.forEach(box => {
  box.remove();
});

// Show/hide old special pages when enabling or disabling the new IA.
document.addEventListener('DOMContentLoaded', () => {
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
});
