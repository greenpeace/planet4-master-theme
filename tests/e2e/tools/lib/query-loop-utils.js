import {searchAndInsertBlock} from './editor.js';
import {expect} from './test-utils.js';

export async function addListBlock(page, blockName, itemsPerPage, config = {}) {
  await searchAndInsertBlock({page}, blockName);

  if (config.layout) {
    await page.getByRole('radio', {name: config.layout}).check();
  }

  await page.getByRole('spinbutton', {name: 'Items per page'}).fill(String(itemsPerPage));

  if (config.category) {
    const editorSettings = page.getByRole('region', {name: 'Editor settings'});
    await editorSettings.getByRole('button', {name: 'Filters options'}).click();
    await page.getByLabel('Show Taxonomies').click();
    await editorSettings.getByLabel('Categories').fill(config.category);
    await editorSettings.locator(
      '.components-form-token-field__suggestion', {hasText: config.category}
    ).click();
    await expect(editorSettings.locator(
      '.components-form-token-field__token-text', {hasText: config.category})
    ).toBeVisible();
  }

  if (config.title) {
    await page.getByRole('document', {name: 'Block: Heading'}).fill(config.title);
  }
}

export async function addListBlockWithManualOverride(page, blockName, titles, overrideTitle) {
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

  await page.getByRole('document', {name: 'Block: Heading'}).fill(overrideTitle);
}

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
