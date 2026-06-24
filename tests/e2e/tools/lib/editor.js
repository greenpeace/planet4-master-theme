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

  if (await openSidebar.getAttribute('aria-expanded') === 'false') {
    await openSidebar.click();
  }

  const searchInput = page.getByPlaceholder('Search');

  await expect(searchInput).toBeVisible();
  await searchInput.clear();
  await searchInput.fill(blockName);

  const blocksList = page.getByRole('listbox', {name: 'Blocks'});
  await expect(blocksList).toBeVisible();

  // Get the chosen block.
  // If the block is Heading or Paragraph, the function getByRole (exact: true) has to be used
  // as WordPress 6.9 introduced the blocks Stretchy Heading and Stretchy Paragraph
  // which also adds an unusual character (/) to the CSS selectors of all the 4 blocks.
  const getBlockOption = () => {
    if (blockName === 'Heading' || blockName === 'Paragraph') {
      return blocksList.getByRole('option', {name: blockName, exact: true});
    }
    if (namespace) {
      return blocksList.locator(
        `button.editor-block-list-item-${namespace.toLowerCase()}[role="option"]`
      );
    }
    return blocksList.getByRole('option', {name: blockName});
  };

  const blockOption = getBlockOption();

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
const addHeadingOrParagraph = async ({page, editor}, blockName, blockTag, number, text) => {
  await searchAndInsertBlock({page}, blockName, blockName.toLowerCase());
  const newBlock = editor.canvas.locator(blockTag).nth(number);
  await expect(newBlock).toBeVisible();
  await closeBlockInserter({page});
  await newBlock.fill(text);
};

/**
 * Pick a specific block style from the sidebar.
 *
 * @param {{Page}} page
 * @param {string} style - The style that needs to be selected.
 */
const pickBlockStyle = async ({page}, style) => {
  await page.getByRole('tab', {name: 'Styles'}).click();
  const stylePicker = page.locator('.block-editor-block-styles__variants');
  await stylePicker.locator(`button[aria-label^="${style}"]`).click();
};

/**
 * Open the "Meta Boxes" tab at the bottom of the editor if needed.
 *
 * @param {{Page}} page
 */
const openMetaBoxesTab = async ({page}) => {
  const metaBoxesTab = page.getByRole('button', {name: 'Meta Boxes'});
  if (await metaBoxesTab.getAttribute('aria-expanded') === 'false') {
    await metaBoxesTab.locator('svg').click();
    await expect(metaBoxesTab).toHaveAttribute('aria-expanded', 'true');
  }
};

/**
 * Close the "Meta Boxes" tab at the bottom of the editor if needed.
 *
 * @param {{Page}} page
 */
const closeMetaBoxesTab = async ({page}) => {
  const metaBoxesTab = page.getByRole('button', {name: 'Meta Boxes'});
  if (await metaBoxesTab.getAttribute('aria-expanded') === 'true') {
    await metaBoxesTab.locator('svg').click();
    await expect(metaBoxesTab).toHaveAttribute('aria-expanded', 'false');
  }
};

export {
  openComponentPanel,
  searchAndInsertBlock,
  searchAndInsertPattern,
  closeBlockInserter,
  addHeadingOrParagraph,
  pickBlockStyle,
  openMetaBoxesTab,
  closeMetaBoxesTab,
};
