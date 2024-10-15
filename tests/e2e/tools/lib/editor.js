/**
 * @param {{Editor}} editor
 * @param {string}   panelTitle - Panel title
 * @return {Locator} Playwright Locator
 */
async function openComponentPanel({editor}, panelTitle) {
  await editor.openDocumentSettingsSidebar();
  const editorSettings = await editor.canvas.getByRole('region', {name: 'Editor settings'});
  await editorSettings.locator('.editor-sidebar__panel-tabs button').first().click();
  const panelButton = await editorSettings.getByRole('button', {name: panelTitle, exact: true});
  const panelExpanded = await panelButton.getAttribute('aria-expanded');
  if (panelExpanded === 'false') {
    await panelButton.click();
  }

  return editorSettings;
}

/**
 * Add a Category to a Post
 *
 * @param {{Editor}} editor
 * @param {string}   category - The category
 */
async function addCategory({editor}, category) {
  const editorSettings = await openComponentPanel({editor}, 'Categories');
  await editorSettings.getByRole('group', {name: 'Categories'}).getByRole('checkbox', {name: category}).click();
}

/**
 * Add a Tag to a Post
 *
 * @param {{Editor}} editor
 * @param {string}   tag    - The tag
 */
async function addTag({editor}, tag) {
  const editorSettings = await openComponentPanel({editor}, 'Tags');
  await editorSettings.getByRole('group', {name: 'Tags'}).getByRole('checkbox', {name: tag}).click();
}

/**
 * Add a Post Type to a Post
 *
 * @param {{Editor}} editor
 * @param {string}   postType - The post type (Story, Press Release, etc.)
 */
async function addPostType({editor}, postType) {
  const editorSettings = await openComponentPanel({editor}, 'Post Types');

  await editorSettings.getByLabel('Add new Post Type').type(postType);
  await editorSettings.getByRole('option', {name: postType}).click();
}

/**
 * Remove all Post Types from a Post
 *
 * @param {{Editor}} editor
 */
async function removeAllPostTypes({editor}) {
  const editorSettings = await openComponentPanel({editor}, 'Post Types');
  const buttons = await editorSettings.getByRole('button', {name: 'Remove Post Type'}).all();
  for (const button of buttons) {
    await button.click();
  }
}

export {
  openComponentPanel,
  addCategory,
  addTag,
  addPostType,
  removeAllPostTypes,
};
