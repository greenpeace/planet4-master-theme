import {searchAndInsertBlock} from './editor.js';
import {expect} from './test-utils.js';

/**
 * Adds and configures a Query List block in the block editor.
 *
 * @param {{Page, Editor}} options      - Page and Editor object
 * @param {string}         blockName    - The name of the block to search for and insert.
 * @param {number}         itemsPerPage - The number of items to display per page.
 * @param {Object}         [config={}]  - Optional configuration settings for the block.
 */
export async function addListBlock({page, editor}, blockName, itemsPerPage, config = {}) {
  await searchAndInsertBlock({page}, blockName);

  if (config.layout) {
    await page.getByRole('radio', {name: config.layout}).check();
  }

  await page.getByRole('spinbutton', {name: 'Items per page'}).fill(String(itemsPerPage));

  if (config.category) {
    const editorSettings = page.getByRole('region', {name: 'Editor settings'});
    await editorSettings.getByRole('button', {name: 'Filters options'}).click();
    await page.getByLabel('Show Taxonomies').click();
    await editorSettings.getByRole('combobox', {name: 'Categories', exact: true}).fill(config.category);
    await editorSettings.locator(
      '.components-form-token-field__suggestion', {hasText: config.category}
    ).click();
    await expect(editorSettings.locator(
      '.components-form-token-field__token-text', {hasText: config.category})
    ).toBeVisible();
  }

  if (config.title) {
    await editor.canvas.getByRole('document', {name: 'Block: Heading 2'}).fill(config.title);
  }
}

/**
 * Updates the existing Query Loop block with a Manual Override to select specific posts and override the default ones.
 *
 * @param {{Page, Editor}} options       - Page and Editor object
 * @param {string}         blockName     - The name of the block to search for and insert.
 * @param {string[]}       titles        - New Post titles to override existing ones.
 * @param {string}         overrideTitle - New Page title to override the default.
 */
export async function addListBlockWithManualOverride({page, editor}, blockName, titles, overrideTitle) {
  await searchAndInsertBlock({page}, blockName);

  await page.getByRole('spinbutton', {name: 'Items per page'}).fill('4');

  const editorSettings = page.getByRole('region', {name: 'Editor settings'});
  const manualOverridePanel = editorSettings.getByRole('button', {name: 'Manual override'});
  if (await manualOverridePanel.getAttribute('aria-expanded') === 'false') {
    await manualOverridePanel.click();
  }

  for (const title of titles) {
    await editorSettings.locator('.components-form-token-field__input').fill(title);
    await editorSettings.locator(
      '.components-form-token-field__suggestion', {hasText: title}
    ).first().click();
    await expect(editorSettings.locator(
      '.components-form-token-field__token-text', {hasText: title})
    ).toBeVisible();
  }

  await editor.canvas.getByRole('document', {name: 'Block: Heading 2'}).fill(overrideTitle);
}

/**
 * Validates test expectations for the Query Loop block.
 *
 * @param {import('@playwright/test').Page} page        - The Playwright page instance.
 * @param {Object}                          [config={}] - Optional configuration settings for the block.
 */
export async function checkListBlock(page, config = {}) {
  const block = page.locator('.p4-query-loop');

  if (config.layout) {
    await expect(block).toContainClass(`is-custom-layout-${config.layout}`);
  }
  if (config.title) {
    await expect(block.locator('h2.wp-block-heading')).toHaveText(config.title);
  }
  if (config.count !== undefined) {
    await expect(block.locator('.wp-block-post')).toHaveCount(config.count);
  }
  if (config.category) {
    const categorySelector = config.categoryLocator ?? '.wp-block-post-terms';
    for (const category of await block.locator(categorySelector).all()) {
      await expect(category).toHaveText(config.category);
    }
  }
  if (config.postTitles) {
    for (const title of config.postTitles) {
      await expect(block.locator('.wp-block-post-title', {hasText: title})).toBeVisible();
    }
  }
}
