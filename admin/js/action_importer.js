// Adds a link to the Import Action page.

document.addEventListener('DOMContentLoaded', () => {
  const pageTitleAction = document.querySelector('.wp-admin.post-type-p4_action .page-title-action');

  if(pageTitleAction) {
    const actionImportButton = document.createElement('a');
    actionImportButton.href = 'edit.php?post_type=p4_action&page=import-action';
    actionImportButton.className = 'add-new-h2';
    actionImportButton.textContent = 'Import Action';
    pageTitleAction.after(actionImportButton);
  }
});
