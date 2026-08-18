import {Locator} from '@playwright/test';
import {expect} from '../../tools/lib/test-utils.js';

/**
 * Check if block name is a registered Query loop block variation.
 *
 * @param {{Page}} page
 * @param {string} blockName
 */
const isBlockVariation = async (page, blockName) => {
  return page.evaluate(name => {
    return window.wp.blocks
      .getBlockVariations('core/query')
      .some(variation => variation.name === name);
  }, blockName);
};

/**
 * Insert a block variation.
 *
 * Query Loop variations (e.g. actions-list, posts-list) are not
 * registered blocks. They are variations of core/query, so they
 * need to be inserted as a core/query block with the variation
 * attributes and innerBlocks.
 *
 * @param {{Page}} page
 * @param {string} variationName
 *
 * @return {Promise<void>}
 */
const insertBlockVariation = async (page, variationName) => {
  await page.waitForFunction(
    name =>
      window.wp.blocks
        .getBlockVariations('core/query')
        .some(variation => variation.name === name),
    variationName
  );

  await page.evaluate(name => {
    const variation = window.wp.blocks
      .getBlockVariations('core/query')
      .find(item => item.name === name);

    if (!variation) {
      throw new Error(
        `Block variation "${name}" not found`
      );
    }

    /**
     * Creates WordPress block objects from an InnerBlocks template.
     *
     * Each template item is expected to contain a block name, optional attributes,
     * and optional nested inner blocks. Nested templates are processed recursively
     * so the resulting structure contains fully created WordPress block objects.
     *
     * @param {Array<Array>} blocks InnerBlocks template to convert.
     */
    const createBlocksFromTemplate = (blocks = []) =>
      blocks.map(([blockName, attributes = {}, innerBlocks = []]) =>
        window.wp.blocks.createBlock(
          blockName,
          attributes,
          createBlocksFromTemplate(innerBlocks)
        )
      );

    const block = window.wp.blocks.createBlock(
      'core/query',
      variation.attributes,
      createBlocksFromTemplate(variation.innerBlocks)
    );

    window.wp.data
      .dispatch('core/block-editor')
      .insertBlock(block);
  }, variationName);
};

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
 * Insert new block into page using the block inserter
 *
 * @param {{Page}} page
 * @param {string} blockName - The name of the block.
 *
 * @return {Promise<void>}   - Playwright Locator
 */
const searchAndInsertBlock = async ({editor, page}, blockName) => {
  if (await isBlockVariation(page, blockName)) {
    await insertBlockVariation(page, blockName);
    return;
  }

  await editor.insertBlock({name: blockName});
};

/**
 * Insert new pattern into page using the block inserter
 *
 * @param {{Page}} page
 * @param {string} patternName - The name of the pattern.
 */
const searchAndInsertPattern = async ({page}, patternName) => {
  await page.waitForFunction(
    name =>
      window.wp?.data
        ?.select('core')
        ?.getBlockPatterns()
        ?.some(pattern => pattern.name === name),
    patternName
  );

  await page.evaluate(name => {
    const pattern = window.wp.data
      .select('core')
      .getBlockPatterns()
      .find(
        item => item.name === name
      );

    if (!pattern) {
      throw new Error(
        `Pattern "${name}" was not found`
      );
    }

    const blocks = window.wp.blocks.parse(pattern.content);

    window.wp.data
      .dispatch('core/block-editor')
      .insertBlocks(blocks);
  }, patternName);
};

/**
 * Add headings and paragraphs to the page.
 * @param {{Page, Editor}} page
 * @param {Array}          blockContent
 */
const addContent = async ({page, editor}, blockContent) => {
  for (const {heading, paragraph} of blockContent) {
    await searchAndInsertBlock({editor, page}, 'core/heading');
    const h2Element = editor.canvas.locator('[data-type="core/heading"][contenteditable="true"]').last();
    await expect(h2Element).toBeVisible();
    await h2Element.click();
    await h2Element.fill(heading);

    await searchAndInsertBlock({editor, page}, 'core/paragraph');
    const pElement = editor.canvas.locator('[data-type="core/paragraph"][contenteditable="true"]').last();
    await expect(pElement).toBeVisible();
    await pElement.click();
    await pElement.fill(paragraph);
  }
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

/**
 * Closes the Gutenberg "Welcome to the editor" guide modal if present.
 *
 * @param {{Page}} page
 */
const closeWelcomeGuideIfPresent = async ({page}) => {
  const welcomeGuide = page.getByRole('dialog', {name: 'Welcome to the editor'});

  if (await welcomeGuide.isVisible().catch(() => false)) {
    await welcomeGuide.getByRole('button', {name: 'Close'}).click();
    await expect(welcomeGuide).toBeHidden();
  }
};

export {
  openComponentPanel,
  searchAndInsertBlock,
  searchAndInsertPattern,
  addContent,
  pickBlockStyle,
  openMetaBoxesTab,
  closeMetaBoxesTab,
  closeWelcomeGuideIfPresent,
};
