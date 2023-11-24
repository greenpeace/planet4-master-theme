/**
 * @typedef {Object} Editor
 * @property {Object}   canvas     - Canvas
 * @typedef {Object} Locator
 * @property {Function} getByRole  - Get by role
 * @property {Function} getByLabel - Get by label
 */

/**
 * Open side panel settings for edited post/page/etc.
 *
 * @param {{Editor, Page}} editor
 * @return {Locator} Playwright Locator
 */
async function openPostSettingsPanel({editor, page}) {
  const topBar = await page.getByRole('region', {name: 'Editor top bar'});
  const settingsButton = await topBar.getByRole('button', {name: 'Settings', exact: true});
  const settingsExpanded = await settingsButton.getAttribute('aria-expanded');
  if (settingsExpanded === 'false') {
    await settingsButton.click();
  }

  const editorSettings = await editor.canvas.getByRole('region', {name: 'Editor settings'});
  await editorSettings.locator('.components-panel__header').getByRole('button', {name: /Post|Page/}).click();

  return editorSettings;
}

/**
 * @param {{Editor}} editor
 * @param {string}   panelTitle - Panel title
 * @return {Locator} Playwright Locator
 */
async function openComponentPanel({editor}, panelTitle) {
  const editorSettings = await editor.canvas.getByRole('region', {name: 'Editor settings'});
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

/**
 * Add a Featured image to a Post
 *
 * @param {{Editor}} editor
 * @param {number}   imageId - The image ID from the Image Library
 */
async function addFeaturedImage({editor}, imageId) {
  const editorSettings = await openComponentPanel({editor}, 'Featured image');

  await editorSettings.getByRole('button', {name: 'Set featured image'}).click();
  const imageModal = await editor.canvas.getByRole('dialog', {name: 'Featured image'});
  const mediaLibTab = await imageModal.getByRole('tab', {name: 'Media Library'});
  await mediaLibTab.click();
  await imageModal.getByRole('tabpanel', {name: 'Media Library'});

  await imageModal.locator(`[data-id="${imageId}"]`).click();
  await imageModal.getByRole('button', {name: 'Set featured image'}).click();
}

export {
  openPostSettingsPanel,
  openComponentPanel,
  addCategory,
  addTag,
  addPostType,
  removeAllPostTypes,
  addFeaturedImage,
};
