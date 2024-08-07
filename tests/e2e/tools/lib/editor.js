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
  await editor.openDocumentSettingsSidebar();
  const editorSettings = await editor.canvas.getByRole('region', {name: 'Editor settings'});
  await editorSettings.locator('.edit-post-sidebar__panel-tabs button').first().click();
  const panelButton = await editorSettings.getByRole('button', {name: panelTitle, exact: true});
  const panelExpanded = await panelButton.getAttribute('aria-expanded');
  if (panelExpanded === 'false') {
    await panelButton.click();
  }

  return editorSettings;
}

export {openComponentPanel};
