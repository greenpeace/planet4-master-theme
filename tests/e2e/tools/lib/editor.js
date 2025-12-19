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
  const getCloseButton = () => page.getByRole('button', {name: 'Close Block Inserter'});

  try {
    await expect(getCloseButton()).toBeVisible({timeout: 1000});
    await getCloseButton().click();
  } catch (error) {
    if (process.env.CI) {
      // eslint-disable-next-line no-console
      console.warn(
        '[closeBlockInserter] skipped:',
        error?.message?.split('\n')[0]
      );
    }
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

  let blockOption = page.getByRole('option', {name: blockName});

  if (namespace) {
    blockOption = page.locator(
      `button.editor-block-list-item-${namespace.toLowerCase()}[role="option"]`
    );
  }

  if (!page.isClosed()) {
    await page.evaluate(el => el.click(), await blockOption.elementHandle());
  }
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
 * @param {string} text
 */
const addHeadingOrParagraph = async ({page}, blockName, blockTag, text) => {
  await searchAndInsertBlock({page}, blockName, blockName.toLowerCase());

  const getNewBlock = () => page.getByRole('region', {name: 'Editor content'}).locator(`${blockTag}[contenteditable="true"]`).last();

  // Wait for Gutenberg to finish inserting and the block to become editable
  await page.waitForFunction(
    newBlockTag => {
      const region = document.querySelector('[role="region"][aria-label="Editor content"]');
      if (!region) {return false;}
      const blocks = Array.from(region.querySelectorAll(newBlockTag));
      return blocks.some(b => b.isContentEditable && b.offsetParent !== null);
    },
    blockTag,
    {timeout: 5000}
  );

  await closeBlockInserter({page});

  // Webkit hack to allow re-render
  if (page.context().browser()?.browserType().name() === 'webkit') {
    if (!page.isClosed()) {
      await page.evaluate(() => new Promise(requestAnimationFrame));
    }
  }

  const newBlock = getNewBlock();
  await newBlock.fill(text);
};

export {openComponentPanel, searchAndInsertBlock, searchAndInsertPattern, closeBlockInserter, addHeadingOrParagraph};
