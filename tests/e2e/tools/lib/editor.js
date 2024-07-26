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
  await editorSettings.locator('.editor-sidebar__panel-tabs button').first().click();
  await editorSettings.getByLabel('button', {name: panelTitle, exact: true});
  return editorSettings;
}

export {openComponentPanel};
