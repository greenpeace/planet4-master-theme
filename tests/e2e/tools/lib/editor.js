import {Locator} from '@playwright/test';
import {expect} from '../../tools/lib/test-utils.js';

/**
 * @param {{Page, Editor}} options    - Page and Editor object
 * @param {string}         panelTitle - Panel title
 *
 * @return {Locator} Playwright Locator
 */
async function openComponentPanel({page, editor}, panelTitle) {
  await editor.openDocumentSettingsSidebar();

  const editorSettings = await page.getByRole('region', {name: 'Editor settings'});
  await editorSettings.locator('.editor-sidebar__panel-tabs button').first().click();
  const panelButton = await editorSettings.getByRole('button', {name: panelTitle, exact: true});
  const panelExpanded = await panelButton.getAttribute('aria-expanded');
  if (panelExpanded === 'false') {
    await panelButton.click();
  }

  return editorSettings;
}

/**
 * Close the block inserter
 *
 * @param {{Page}} page
 */
const closeBlockInserter = async ({page}) => {
  const closeSidebar = await page.getByRole('button', {name: 'Close block inserter'});
  if (await closeSidebar.isVisible()) {
    await closeSidebar.click();
    await expect(closeSidebar).toBeHidden();
  }
};

/**
 * Insert new block into page using the block inserter
 *
 * @param {{Page}} page
 * @param {string} blockName - The name of the block.
 * @param {string} namespace - The namespace to search if it is needed.
 */
const searchAndInsertBlock = async ({page}, blockName, namespace = '') => {
  await page.getByRole('button', {name: 'Block Inserter', exact: true}).click();
  await page.getByPlaceholder('Search').fill(blockName);

  if (namespace !== '') {
    await page.locator(`button.editor-block-list-item-${namespace.toLowerCase()}[role="option"]`).click();
  }

  await page.getByRole('option', {name: blockName}).click();
};

/**
 * Insert new pattern into page using the block inserter
 *
 * @param {{Page}} page
 * @param {string} id   - The id of the pattern.
 */
const searchAndInsertPattern = async ({page}, id) => {
  await page.getByRole('button', {name: 'Block Inserter', exact: true}).click();
  await page.getByPlaceholder('Search').fill(id);
  await page.locator(`[id="${id}"]`).click();
};

export {openComponentPanel, searchAndInsertBlock, searchAndInsertPattern, closeBlockInserter};
