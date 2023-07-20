const spamButtons = document.querySelectorAll('.checkforspam');

spamButtons.forEach(box => {
  box.remove();
});

document.addEventListener('DOMContentLoaded', () => {
  const newIASetting = document.querySelector('#new_ia');

  if (!newIASetting) {
    return;
  }

  const actPageDropdown = document.querySelector('[data-fieldtype="act_page_dropdown"]');
  const explorePageDropdown = document.querySelector('[data-fieldtype="explore_page_dropdown"]');
  const issuesParentCategoryDropdown = document.querySelector('[data-fieldtype="category_select_taxonomy"]');

  newIASetting.addEventListener('change', event => {
    const {checked} = event.currentTarget;
    actPageDropdown.classList.toggle('hidden', checked);
    explorePageDropdown.classList.toggle('hidden', checked);
    issuesParentCategoryDropdown.classList.toggle('hidden', checked);
  });
});
