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
 * Add a Featured image to a Post
 *
 * @param {{Editor}} editor
 * @param {number}   imageId - The image ID from the Image Library
 */
async function addFeaturedImage({editor}, imageId = 354) {
  const editorSettings = await openComponentPanel({editor}, 'Featured image');

  await editorSettings.getByRole('button', {name: 'Set featured image'}).click();
  const imageModal = await editor.canvas.getByRole('dialog', {name: 'Featured image'});
  const mediaLibTab = await imageModal.getByRole('tab', {name: 'Media Library'});
  await mediaLibTab.click();
  await imageModal.getByRole('tabpanel', {name: 'Media Library'});

  await imageModal.locator(`[data-id="${imageId}"]`).click();
  await imageModal.getByRole('button', {name: 'Set featured image'}).click();
}

export {addFeaturedImage};
