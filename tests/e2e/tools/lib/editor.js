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
  const inserter = page.locator('.editor-inserter-sidebar');

  if (await inserter.isVisible()) {
    await page.keyboard.press('Escape');
  }
};

/**
 * Insert new block into page using the block inserter
 *
 * @param {{Page}} page
 * @param {string} blockName - The name of the block.
 * @param {string} namespace - The namespace to search if it is needed.
 *
 * @return {Promise<void>}   - Playwright Locator
 */
const searchAndInsertBlock = async ({page}, blockName, namespace = '') => {
  const openSidebar = await page.getByRole('button', {name: 'Block Inserter', exact: true});
  const searchInput = page.getByPlaceholder('Search');

  if (await openSidebar.getAttribute('aria-expanded') === 'false') {
    await openSidebar.click();
    await expect(searchInput).toBeVisible();
  }

  await searchInput.fill('');
  await searchInput.fill(blockName);

  const blocksList = page.getByRole('listbox', {name: 'Blocks'});
  await expect(blocksList).toBeVisible();

  let blockOption = blocksList.getByRole('option', {name: blockName});

  if (namespace) {
    blockOption = blocksList.locator(
      `button.editor-block-list-item-${namespace.toLowerCase()}[role="option"]`
    );
  }

  await expect(blockOption).toBeVisible();
  await blockOption.click();
};

/**
 * Insert new pattern into page using the block inserter
 *
 * @param {{Page}} page
 * @param {string} id   - The id of the pattern.
 */
const searchAndInsertPattern = async ({page}, id) => {
  await page.getByRole('button', {name: 'Block Inserter', exact: true}).click({force: true});
  await page.getByPlaceholder('Search').fill(id);
  await page.locator(`[id="${id}"]`).click();
};

/**
 * @param {{Page}} page
 * @param {string} blockName
 * @param {string} blockTag
 * @param {number} number
 * @param {string} text
 */
const addHeadingOrParagraph = async ({page}, blockName, blockTag, number, text) => {
  await searchAndInsertBlock({page}, blockName, blockName.toLowerCase());
  const newBlock = page.getByRole('region', {name: 'Editor content'}).locator(blockTag).nth(number);
  await expect(newBlock).toBeVisible();
  await closeBlockInserter({page});
  await newBlock.fill(text);
};

export {openComponentPanel, searchAndInsertBlock, searchAndInsertPattern, closeBlockInserter, addHeadingOrParagraph};
